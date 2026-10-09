<?php

namespace Modules\SocialAccount\Services;

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
