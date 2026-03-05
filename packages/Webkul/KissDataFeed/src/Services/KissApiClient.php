<?php

namespace Webkul\KissDataFeed\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Webkul\KissDataFeed\Exceptions\ApiConflictException;
use Webkul\KissDataFeed\Exceptions\ApiNotFoundException;
use Webkul\KissDataFeed\Models\CredentialConfig;
use Webkul\KissDataFeed\Repositories\CredentialRepository;

class KissApiClient
{
    public function __construct(
        protected KissApiAuthService $authService,
        protected CredentialRepository $credentialRepository
    ) {}

    /**
     * GET /admin/products/{sku}
     * Returns product array or null if 404.
     */
    public function getProduct(CredentialConfig $credential, string $sku): ?array
    {
        try {
            return $this->request('GET', $credential, '/admin/products/'.urlencode($sku));
        } catch (ApiNotFoundException) {
            return null;
        }
    }

    /**
     * POST /admin/products
     * Creates a new product. Returns created product array.
     */
    public function createProduct(CredentialConfig $credential, array $productData): array
    {
        return $this->request('POST', $credential, '/admin/products', $productData);
    }

    /**
     * PUT /admin/products/{sku}
     * Full replacement update. Returns updated product array.
     */
    public function updateProduct(CredentialConfig $credential, string $sku, array $productData): array
    {
        return $this->request('PUT', $credential, '/admin/products/'.urlencode($sku), $productData);
    }

    /**
     * DELETE /admin/products/{sku}
     * Returns true on success.
     */
    public function deleteProduct(CredentialConfig $credential, string $sku): bool
    {
        $this->request('DELETE', $credential, '/admin/products/'.urlencode($sku));

        return true;
    }

    /**
     * Make an authenticated HTTP request.
     * Auto-refreshes token on 401.
     *
     * @throws ApiNotFoundException
     * @throws ApiConflictException
     * @throws \RuntimeException
     */
    protected function request(string $method, CredentialConfig $credential, string $path, ?array $body = null): array
    {
        $token = $this->authService->getToken($credential);
        $response = $this->sendRequest($method, $credential->api_url.$path, $token, $body);

        // On 401, clear token, re-authenticate once, and retry
        if ($response->status() === 401) {
            $this->credentialRepository->update([
                'access_token'     => null,
                'token_expires_at' => null,
            ], $credential->id);

            $credential->refresh();

            $token = $this->authService->getToken($credential);
            $response = $this->sendRequest($method, $credential->api_url.$path, $token, $body);
        }

        return $this->handleResponse($response);
    }

    protected function sendRequest(string $method, string $url, string $token, ?array $body): Response
    {
        if (! app()->environment('local', 'development')) {
            $host = parse_url($url, PHP_URL_HOST);
            $ip = $host ? gethostbyname($host) : null;

            if (! $ip || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new \RuntimeException('API URL resolves to a private or reserved IP address.');
            }
        }

        $pending = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer '.$token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])
            ->retry(3, 100, function (\Exception $exception) {
                // Don't retry on 4xx client errors
                if ($exception instanceof \Illuminate\Http\Client\RequestException
                    && $exception->response->status() >= 400
                    && $exception->response->status() < 500) {
                    return false;
                }

                return true;
            }, throw: false);

        return match (strtoupper($method)) {
            'GET'    => $pending->get($url),
            'POST'   => $pending->post($url, $body),
            'PUT'    => $pending->put($url, $body),
            'DELETE' => $pending->delete($url),
            default  => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
        };
    }

    /**
     * @throws ApiNotFoundException
     * @throws ApiConflictException
     * @throws \RuntimeException
     */
    protected function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            // 204 No Content (DELETE)
            if ($response->status() === 204) {
                return [];
            }

            return $response->json();
        }

        $error = $response->json('error', 'Unknown API error');

        Log::error("Kiss DataFeed API response: status={$response->status()}, body={$response->body()}");

        throw match ($response->status()) {
            404 => new ApiNotFoundException($error),
            409 => new ApiConflictException($error),
            default => new \RuntimeException("DataFeed API error ({$response->status()}): {$error}"),
        };
    }
}
