<?php

namespace Modules\SocialAccount\Services;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Modules\SocialAccount\Exceptions\InstagramAuthException;
use Modules\SocialAccount\Models\SocialAccount;
use RuntimeException;

/**
 * Panggilan tingkat rendah ke Instagram API: membuat container media, memeriksa statusnya,
 * dan menerbitkannya. Urutan dan penantian diatur oleh PublicationRunner.
 */
class InstagramPublisher
{
    private const GRAPH_URL = 'https://graph.instagram.com';

    public function __construct(private readonly HttpClient $http) {}

    /**
     * Membuat container media (foto, video Reels, Story, atau carousel).
     *
     * @param  array<string, scalar>  $params  image_url, video_url, media_type, caption, dan seterusnya
     * @return string ID container
     */
    public function createContainer(SocialAccount $account, array $params): string
    {
        $response = $this->request($account)->post("/{$this->userId($account)}/media", $params);

        return (string) $this->data($response, 'id');
    }

    /**
     * @return array{code: string, detail: ?string} code: IN_PROGRESS, FINISHED, ERROR, EXPIRED, PUBLISHED
     */
    public function containerStatus(SocialAccount $account, string $containerId): array
    {
        $response = $this->request($account)->get("/{$containerId}", ['fields' => 'status_code,status']);

        return [
            'code' => (string) $this->data($response, 'status_code'),
            'detail' => $response->json('status'),
        ];
    }

    /**
     * @return string ID media di Instagram
     */
    public function publishContainer(SocialAccount $account, string $containerId): string
    {
        $response = $this->request($account)
            ->post("/{$this->userId($account)}/media_publish", ['creation_id' => $containerId]);

        return (string) $this->data($response, 'id');
    }

    /**
     * Memperpanjang token long-lived. Token harus berumur > 24 jam dan belum kedaluwarsa.
     */
    public function refreshToken(SocialAccount $account): void
    {
        $response = $this->http()
            ->get('/refresh_access_token', [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $account->access_token,
            ]);

        $token = $this->data($response, 'access_token');
        $expiresIn = (int) $response->json('expires_in', 60 * 24 * 60 * 60);

        $account->update([
            'access_token' => $token,
            'token_expires_at' => now()->addSeconds($expiresIn),
            'status' => SocialAccount::STATUS_ACTIVE,
        ]);
    }

    private function userId(SocialAccount $account): string
    {
        return $account->provider_account_id ?: 'me';
    }

    private function data(Response $response, string $key): mixed
    {
        if ($response->failed()) {
            $message = $response->json('error.message') ?? 'HTTP '.$response->status();

            if ((int) $response->json('error.code') === 190) {
                throw new InstagramAuthException("Token Instagram ditolak: {$message}");
            }

            throw new RuntimeException("Instagram menolak permintaan: {$message}");
        }

        $value = $response->json($key);

        if ($value === null) {
            throw new RuntimeException("Respons Instagram tidak berisi {$key}.");
        }

        return $value;
    }

    private function request(SocialAccount $account): PendingRequest
    {
        return $this->http()->withToken((string) $account->access_token);
    }

    private function http(): PendingRequest
    {
        $version = config('social-account.instagram.graph_version', 'v22.0');

        return $this->http
            ->baseUrl(self::GRAPH_URL.'/'.$version)
            ->asForm()
            ->timeout(30)
            ->connectTimeout(10);
    }
}
