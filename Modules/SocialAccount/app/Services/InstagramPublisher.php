<?php

namespace Modules\SocialAccount\Services;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Modules\SocialAccount\Exceptions\InstagramAuthException;
use Modules\SocialAccount\Models\SocialAccount;
use RuntimeException;

class InstagramPublisher
{
    private const GRAPH_URL = 'https://graph.instagram.com';

    public function __construct(private readonly HttpClient $http) {}

    /**
     * Menerbitkan satu foto, atau carousel bila ada lebih dari satu URL.
     *
     * @param  list<string>  $imageUrls
     * @return string ID media Instagram
     */
    public function publish(SocialAccount $account, array $imageUrls, string $caption): string
    {
        if ($imageUrls === []) {
            throw new RuntimeException('Postingan tidak memiliki foto.');
        }

        $userId = $account->provider_account_id ?: 'me';

        $containerId = count($imageUrls) === 1
            ? $this->createContainer($account, $userId, ['image_url' => $imageUrls[0], 'caption' => $caption])
            : $this->createCarousel($account, $userId, $imageUrls, $caption);

        $this->waitUntilReady($account, $containerId);

        $response = $this->request($account)
            ->post("/{$userId}/media_publish", ['creation_id' => $containerId]);

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

    private function createCarousel(SocialAccount $account, string $userId, array $imageUrls, string $caption): string
    {
        $children = array_map(
            fn (string $url) => $this->createContainer($account, $userId, [
                'image_url' => $url,
                'is_carousel_item' => 'true',
            ]),
            $imageUrls
        );

        foreach ($children as $childId) {
            $this->waitUntilReady($account, $childId);
        }

        return $this->createContainer($account, $userId, [
            'media_type' => 'CAROUSEL',
            'children' => implode(',', $children),
            'caption' => $caption,
        ]);
    }

    private function createContainer(SocialAccount $account, string $userId, array $params): string
    {
        return (string) $this->data($this->request($account)->post("/{$userId}/media", $params), 'id');
    }

    private function waitUntilReady(SocialAccount $account, string $containerId): void
    {
        $attempts = (int) config('social-account.instagram.status_poll_attempts', 10);
        $seconds = (int) config('social-account.instagram.status_poll_seconds', 3);

        for ($i = 0; $i < $attempts; $i++) {
            $status = $this->data(
                $this->request($account)->get("/{$containerId}", ['fields' => 'status_code']),
                'status_code'
            );

            if ($status === 'FINISHED') {
                return;
            }

            if (in_array($status, ['ERROR', 'EXPIRED'], true)) {
                throw new RuntimeException("Instagram gagal memproses media (status {$status}).");
            }

            if ($seconds > 0) {
                sleep($seconds);
            }
        }

        throw new RuntimeException('Instagram belum selesai memproses media. Coba jadwalkan ulang.');
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
