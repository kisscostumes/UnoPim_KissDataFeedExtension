<?php

namespace Webkul\KissDataFeed\Services;

use Illuminate\Support\Facades\Http;
use Webkul\KissDataFeed\Models\CredentialConfig;
use Webkul\KissDataFeed\Repositories\CredentialRepository;

class KissApiAuthService
{
    public function __construct(
        protected CredentialRepository $credentialRepository
    ) {}

    /**
     * Get a valid Bearer token for the given credential.
     * Returns cached token if still valid (with 5-min buffer).
     * Otherwise requests a new token from /auth/token.
     */
    public function getToken(CredentialConfig $credential): string
    {
        if ($this->isTokenValid($credential)) {
            return $credential->access_token;
        }

        $tokenData = $this->requestNewToken($credential);

        $this->credentialRepository->update([
            'access_token'     => $tokenData['access_token'],
            'token_expires_at' => now()->addSeconds($tokenData['expires_in']),
        ], $credential->id);

        $credential->refresh();

        return $credential->access_token;
    }

    /**
     * Request a fresh token from the API.
     *
     * @return array{access_token: string, token_type: string, expires_in: int}
     *
     * @throws \RuntimeException
     */
    public function requestNewToken(CredentialConfig $credential): array
    {
        $host = parse_url($credential->api_url, PHP_URL_HOST);
        $ip = $host ? gethostbyname($host) : null;

        if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            throw new \RuntimeException('API URL resolves to a private or reserved IP address.');
        }

        $response = Http::timeout(10)->asForm()->post($credential->api_url.'/auth/token', [
            'grant_type'    => 'client_credentials',
            'client_id'     => $credential->client_id,
            'client_secret' => $credential->client_secret,
        ]);

        if (! $response->successful()) {
            $error = $response->json('error', 'unknown_error');

            throw new \RuntimeException("Token request failed: {$error}");
        }

        $data = $response->json();

        if (empty($data['access_token'])) {
            throw new \RuntimeException('Token response missing access_token');
        }

        return [
            'access_token' => $data['access_token'],
            'token_type'   => $data['token_type'] ?? 'Bearer',
            'expires_in'   => $data['expires_in'] ?? 3600,
        ];
    }

    /**
     * Check if current token is still valid (expires_at > now + 5 minutes).
     */
    public function isTokenValid(CredentialConfig $credential): bool
    {
        if (empty($credential->access_token) || empty($credential->token_expires_at)) {
            return false;
        }

        return $credential->token_expires_at->isAfter(now()->addMinutes(5));
    }
}
