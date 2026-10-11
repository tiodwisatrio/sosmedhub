<?php

namespace Modules\SocialAccount\Services;

use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Modules\SocialAccount\Exceptions\FacebookAuthException;
use Modules\SocialAccount\Models\SocialAccount;
use RuntimeException;

/**
 * Panggilan tingkat rendah ke Graph API Facebook untuk menerbitkan ke Page. Berbeda dengan
 * Instagram, foto diunggah langsung dari server (tanpa URL publik) dan hasilnya langsung
 * jadi, jadi tidak ada container yang perlu ditunggu. Urutan diatur oleh FacebookPublicationRunner.
 */
class FacebookPublisher
{
    public function __construct(private readonly HttpClient $http) {}

    /**
     * Mengunggah satu foto. Bila $published true, foto langsung menjadi postingan Page.
     *
     * @return array{id: string, post_id: ?string} ID foto, dan ID postingan bila langsung terbit
     */
    public function uploadPhoto(SocialAccount $account, string $path, ?string $message, bool $published): array
    {
        $payload = ['published' => $published ? 'true' : 'false'];

        if ($published && $message !== null && $message !== '') {
            $payload['message'] = $message;
        }

        $response = $this->request($account)
            ->attach('source', fopen($path, 'r'), basename($path))
            ->post("/{$account->provider_account_id}/photos", $payload);

        return [
            'id' => (string) $this->data($response, 'id'),
            'post_id' => $response->json('post_id'),
        ];
    }

    /**
     * Menerbitkan postingan Feed berisi beberapa foto yang sudah diunggah (published=false).
     *
     * @param  list<string>  $photoIds
     * @return string ID postingan
     */
    public function publishPost(SocialAccount $account, ?string $message, array $photoIds): string
    {
        $payload = [];

        if ($message !== null && $message !== '') {
            $payload['message'] = $message;
        }

        foreach ($photoIds as $index => $photoId) {
            $payload["attached_media[{$index}]"] = json_encode(['media_fbid' => $photoId]);
        }

        $response = $this->request($account)
            ->asForm()
            ->post("/{$account->provider_account_id}/feed", $payload);

        return (string) $this->data($response, 'id');
    }

    /**
     * Menerbitkan satu foto yang sudah diunggah (published=false) sebagai Story Page.
     *
     * @return string ID postingan Story
     */
    public function publishPhotoStory(SocialAccount $account, string $photoId): string
    {
        $response = $this->request($account)
            ->asForm()
            ->post("/{$account->provider_account_id}/photo_stories", ['photo_id' => $photoId]);

        return (string) $this->data($response, 'post_id');
    }

    /**
     * Membuka sesi unggah video ke edge video_stories atau video_reels. Video baru ada di
     * Facebook setelah uploadVideo().
     *
     * @return array{video_id: string, upload_url: string}
     */
    public function startVideoUpload(SocialAccount $account, string $edge = 'video_stories'): array
    {
        $response = $this->request($account)
            ->post("/{$account->provider_account_id}/{$edge}", ['upload_phase' => 'start']);

        $videoId = (string) $this->data($response, 'video_id');

        return [
            'video_id' => $videoId,
            'upload_url' => (string) ($response->json('upload_url') ?: $this->uploadUrl($videoId)),
        ];
    }

    /**
     * Mengunggah file video langsung dari server ke rupload.facebook.com (tanpa URL publik).
     */
    public function uploadVideo(SocialAccount $account, string $uploadUrl, string $path): void
    {
        if (parse_url($uploadUrl, PHP_URL_HOST) !== 'rupload.facebook.com') {
            throw new RuntimeException('Alamat unggah video Facebook tidak valid.');
        }

        $stream = fopen($path, 'r');

        if ($stream === false) {
            throw new RuntimeException('Video tidak bisa dibaca dari server.');
        }

        $response = $this->http
            ->withHeaders([
                'Authorization' => 'OAuth '.$account->access_token,
                'offset' => '0',
                'file_size' => (string) filesize($path),
            ])
            ->timeout(120)
            ->connectTimeout(10)
            ->withBody(Utils::streamFor($stream), 'application/octet-stream')
            ->post($uploadUrl);

        if ($response->failed() || $response->json('success') !== true) {
            $message = $response->json('error.message') ?? $response->json('debug_info.message') ?? 'HTTP '.$response->status();

            if ((int) $response->json('error.code') === 190) {
                throw new FacebookAuthException("Token Facebook ditolak: {$message}");
            }

            throw new RuntimeException("Facebook menolak unggahan video: {$message}");
        }
    }

    /**
     * Status unggahan video Story. Facebook baru memproses video setelah finish, jadi yang
     * ditunggu sebelum finish hanyalah unggahan selesai (video_status upload_complete).
     *
     * @return array{uploaded: bool, failed: bool, published: bool, error: ?string}
     */
    public function videoStatus(SocialAccount $account, string $videoId): array
    {
        $response = $this->request($account)->get("/{$videoId}", ['fields' => 'status']);

        $this->data($response, 'status');

        $code = (string) $response->json('status.video_status');
        $uploading = (string) $response->json('status.uploading_phase.status');
        $publishStatus = (string) $response->json('status.publishing_phase.publish_status');
        $processing = (string) $response->json('status.processing_phase.status');
        $error = $response->json('status.processing_phase.error.message')
            ?? $response->json('status.uploading_phase.error.message');

        return [
            'uploaded' => in_array($uploading, ['complete', 'completed'], true) || in_array($code, ['upload_complete', 'processing', 'ready'], true),
            'failed' => in_array($code, ['error', 'expired', 'upload_failed'], true) || $uploading === 'error' || $processing === 'error',
            'published' => $publishStatus === 'published' || $code === 'ready',
            'error' => is_string($error) ? $error : null,
        ];
    }

    /**
     * Menerbitkan video Story yang sudah selesai diproses.
     *
     * @return string ID postingan Story
     */
    public function finishVideoStory(SocialAccount $account, string $videoId): string
    {
        $response = $this->request($account)
            ->post("/{$account->provider_account_id}/video_stories", ['video_id' => $videoId, 'upload_phase' => 'finish']);

        return (string) $this->data($response, 'post_id');
    }

    /**
     * Menerbitkan Reels yang sudah diunggah. Facebook hanya membalas success; ID Reels adalah video_id.
     */
    public function finishReel(SocialAccount $account, string $videoId, ?string $description): void
    {
        $payload = ['video_id' => $videoId, 'upload_phase' => 'finish', 'video_state' => 'PUBLISHED'];

        if ($description !== null && $description !== '') {
            $payload['description'] = $description;
        }

        $response = $this->request($account)
            ->post("/{$account->provider_account_id}/video_reels", $payload);

        if ($this->data($response, 'success') !== true) {
            throw new RuntimeException('Facebook tidak mengonfirmasi penerbitan Reels.');
        }
    }

    private function uploadUrl(string $videoId): string
    {
        $version = config('social-account.facebook.graph_version', 'v26.0');

        return "https://rupload.facebook.com/video-upload/{$version}/{$videoId}";
    }

    private function data(Response $response, string $key): mixed
    {
        if ($response->failed()) {
            $message = $response->json('error.message') ?? 'HTTP '.$response->status();

            if ((int) $response->json('error.code') === 190) {
                throw new FacebookAuthException("Token Facebook ditolak: {$message}");
            }

            throw new RuntimeException("Facebook menolak permintaan: {$message}");
        }

        $value = $response->json($key);

        if ($value === null) {
            throw new RuntimeException("Respons Facebook tidak berisi {$key}.");
        }

        return $value;
    }

    private function request(SocialAccount $account): PendingRequest
    {
        $version = config('social-account.facebook.graph_version', 'v26.0');

        return $this->http
            ->baseUrl("https://graph.facebook.com/{$version}")
            ->withToken((string) $account->access_token)
            ->timeout(60)
            ->connectTimeout(10);
    }
}
