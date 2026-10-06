<?php

namespace Modules\SocialAccount\Services;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class FacebookGraphService
{
    private const PAGE_FIELDS = 'id,name,username,picture.type(large){url},access_token,tasks';

    // Task Halaman yang dibutuhkan untuk menerbitkan postingan.
    private const REQUIRED_TASK = 'CREATE_CONTENT';

    public function __construct(private readonly HttpClient $http) {}

    public function isConfigured(): bool
    {
        return (bool) ($this->config('client_id') && $this->config('client_secret') && $this->config('redirect_uri'));
    }

    public function scopes(): array
    {
        return $this->config('scopes', []);
    }

    public function redirect(string $state): RedirectResponse
    {
        $params = [
            'client_id' => $this->config('client_id'),
            'redirect_uri' => $this->config('redirect_uri'),
            'state' => $state,
            'response_type' => 'code',
            'scope' => implode(',', $this->scopes()),
        ];

        return redirect()->away($this->facebookUrl('dialog/oauth').'?'.http_build_query($params));
    }

    public function exchangeCode(string $code): string
    {
        return $this->requestToken([
            'client_id' => $this->config('client_id'),
            'client_secret' => $this->config('client_secret'),
            'redirect_uri' => $this->config('redirect_uri'),
            'code' => $code,
        ], 'Respons OAuth Facebook tidak berisi access_token.');
    }

    public function exchangeToLongLived(string $shortLivedToken): string
    {
        return $this->requestToken([
            'grant_type' => 'fb_exchange_token',
            'client_id' => $this->config('client_id'),
            'client_secret' => $this->config('client_secret'),
            'fb_exchange_token' => $shortLivedToken,
        ], 'Gagal menukar token Facebook menjadi long-lived.');
    }

    /**
     * Izin yang benar-benar disetujui pengguna. Pengguna bisa menolak sebagian izin di dialog.
     *
     * @return array<int, string>
     */
    public function grantedPermissions(string $userToken): array
    {
        $response = $this->graph($userToken)->get('/me/permissions')->throw();

        return collect($response->json('data', []))
            ->where('status', 'granted')
            ->pluck('permission')
            ->values()
            ->all();
    }

    /**
     * Halaman yang bisa dikelola pengguna beserta token Halaman masing-masing.
     * Token Halaman dari token pengguna long-lived tidak kedaluwarsa.
     *
     * @return array<int, array{id: string, name: string, username: ?string, avatar_url: ?string, access_token: string}>
     */
    public function pages(string $userToken): array
    {
        $response = $this->graph($userToken)
            ->get('/me/accounts', ['fields' => self::PAGE_FIELDS, 'limit' => 100])
            ->throw();

        return collect($response->json('data', []))
            // Page yang tidak mengirim `tasks` tetap ditawarkan; hanya yang jelas tanpa hak membuat konten yang dibuang.
            ->filter(fn (array $page) => isset($page['id'], $page['access_token'])
                && (! isset($page['tasks']) || in_array(self::REQUIRED_TASK, $page['tasks'], true)))
            ->map(fn (array $page) => [
                'id' => (string) $page['id'],
                'name' => (string) ($page['name'] ?? $page['id']),
                'username' => $page['username'] ?? null,
                'avatar_url' => $page['picture']['data']['url'] ?? null,
                'access_token' => $page['access_token'],
            ])
            ->values()
            ->all();
    }

    private function requestToken(array $payload, string $failureMessage): string
    {
        // Dikirim sebagai form POST agar secret dan token tidak masuk ke URL maupun pesan error.
        $response = $this->graph()->asForm()->post('/oauth/access_token', $payload)->throw();

        $token = $response->json('access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException($failureMessage);
        }

        return $token;
    }

    private function graph(?string $token = null): PendingRequest
    {
        $request = $this->http
            ->baseUrl($this->graphUrl())
            ->timeout(30)
            ->connectTimeout(10);

        return $token ? $request->withToken($token) : $request;
    }

    private function graphUrl(): string
    {
        return 'https://graph.facebook.com/'.$this->config('graph_version', 'v26.0');
    }

    private function facebookUrl(string $path): string
    {
        return 'https://www.facebook.com/'.$this->config('graph_version', 'v26.0').'/'.$path;
    }

    private function config(string $key, mixed $default = null): mixed
    {
        return config("social-account.facebook.$key", $default);
    }
}
