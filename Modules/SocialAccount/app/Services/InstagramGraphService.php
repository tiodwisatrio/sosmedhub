<?php

namespace Modules\SocialAccount\Services;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class InstagramGraphService
{
    private const AUTHORIZE_URL = 'https://www.instagram.com/oauth/authorize';

    private const TOKEN_URL = 'https://api.instagram.com/oauth/access_token';

    private const GRAPH_URL = 'https://graph.instagram.com';

    private const DEFAULT_FIELDS = 'user_id,username,account_type,profile_picture_url';

    public function __construct(private readonly HttpClient $http) {}

    public function isConfigured(): bool
    {
        return (bool) ($this->config('client_id') && $this->config('client_secret') && $this->config('redirect_uri'));
    }

    public function redirect(?string $state = null): RedirectResponse
    {
        $params = [
            'enable_fb_login' => '0',
            'force_authentication' => '1',
            'client_id' => $this->config('client_id'),
            'redirect_uri' => $this->config('redirect_uri'),
            'response_type' => 'code',
            'scope' => implode(',', $this->config('scopes', [])),
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return redirect()->away(self::AUTHORIZE_URL.'?'.http_build_query($params));
    }

    public function exchangeToken(string $code): array
    {
        $response = $this->tokenRequest()
            ->asForm()
            ->post(self::TOKEN_URL, [
                'client_id' => $this->config('client_id'),
                'client_secret' => $this->config('client_secret'),
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->config('redirect_uri'),
                'code' => $code,
            ])
            ->throw();

        $data = $response->json('data.0') ?? $response->json();

        if (! isset($data['access_token'])) {
            throw new RuntimeException('Respons OAuth Instagram tidak berisi access_token.');
        }

        return $data;
    }

    public function fetchProfile(string $accessToken): array
    {
        $response = $this->graphRequest($accessToken)
            ->get(self::GRAPH_URL.'/me', [
                'fields' => self::DEFAULT_FIELDS,
            ])
            ->throw();

        return array_filter($response->json() ?? []);
    }

    /**
     * @return array{access_token: string, expires_in: int}
     */
    public function exchangeToLongLived(string $shortLivedToken): array
    {
        $response = $this->http()->get(self::GRAPH_URL.'/access_token', [
            'grant_type' => 'ig_exchange_token',
            'client_secret' => $this->config('client_secret'),
            'access_token' => $shortLivedToken,
        ])->throw();

        $token = $response->json('access_token');

        if (! is_string($token)) {
            throw new RuntimeException('Gagal menukar token Instagram menjadi long-lived.');
        }

        return [
            'access_token' => $token,
            'expires_in' => (int) $response->json('expires_in', 60 * 24 * 60 * 60),
        ];
    }

    private function config(string $key, mixed $default = null): mixed
    {
        return config("social-account.instagram.$key", $default);
    }

    private function tokenRequest(): PendingRequest
    {
        return $this->http()
            ->asForm()
            ->timeout(30)
            ->connectTimeout(10);
    }

    private function graphRequest(string $accessToken): PendingRequest
    {
        return $this->http()
            ->withToken($accessToken)
            ->timeout(30)
            ->connectTimeout(10);
    }

    private function http(): PendingRequest
    {
        return $this->http->baseUrl(self::GRAPH_URL);
    }
}
