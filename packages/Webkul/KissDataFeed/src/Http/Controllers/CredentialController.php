<?php

namespace Webkul\KissDataFeed\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\KissDataFeed\DataGrids\CredentialDataGrid;
use Webkul\KissDataFeed\Http\Requests\CredentialRequest;
use Webkul\KissDataFeed\Repositories\CredentialRepository;

class CredentialController extends Controller
{
    public function __construct(
        protected CredentialRepository $credentialRepository
    ) {}

    public function index()
    {
        if (request()->ajax()) {
            return app(CredentialDataGrid::class)->toJson();
        }

        return view('kiss_datafeed::credentials.index');
    }

    public function store(CredentialRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['api_url'] = rtrim($data['api_url'], '/');

        $validationError = $this->validateApiCredentials($data['api_url'], $data['client_id'], $data['client_secret']);

        if ($validationError) {
            return new JsonResponse([
                'errors' => ['api_url' => [$validationError]],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data['active'] = true;

        try {
            $credential = $this->credentialRepository->create($data);

            session()->flash('success', trans('kiss_datafeed::app.credentials.create-success'));
        } catch (\Exception $e) {
            return new JsonResponse([
                'errors' => ['api_url' => [$e->getMessage()]],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'redirect_url' => route('kiss_datafeed.credentials.edit', $credential->id),
        ]);
    }

    public function edit(int $id)
    {
        $credential = $this->credentialRepository->find($id);

        if (! $credential) {
            abort(404);
        }

        return view('kiss_datafeed::credentials.edit', compact('credential'));
    }

    public function update(CredentialRequest $request, int $id)
    {
        $credential = $this->credentialRepository->find($id);

        if (! $credential) {
            abort(404);
        }

        $data = $request->validated();
        $data['api_url'] = rtrim($data['api_url'], '/');

        $secret = $data['client_secret'] ?? $credential->client_secret;

        if (empty($data['client_secret'])) {
            unset($data['client_secret']);
        }

        $validationError = $this->validateApiCredentials($data['api_url'], $data['client_id'], $secret);

        if ($validationError) {
            return redirect()->route('kiss_datafeed.credentials.edit', $id)
                ->withErrors(['api_url' => $validationError])
                ->withInput();
        }

        if (request()->has('active')) {
            $data['active'] = (bool) request()->input('active');
        }

        $this->credentialRepository->update($data, $id);

        session()->flash('success', trans('kiss_datafeed::app.credentials.update-success'));

        return redirect()->route('kiss_datafeed.credentials.edit', $id);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->credentialRepository->delete($id);

        return new JsonResponse([
            'message' => trans('kiss_datafeed::app.credentials.delete-success'),
        ]);
    }

    /**
     * Test API credentials by calling POST {api_url}/auth/token.
     * Returns error message string on failure, null on success.
     */
    protected function validateApiCredentials(string $apiUrl, string $clientId, string $clientSecret): ?string
    {
        if ($this->isInternalUrl($apiUrl)) {
            return trans('kiss_datafeed::app.credentials.invalid-url');
        }

        try {
            $response = Http::timeout(10)->asForm()->post($apiUrl.'/auth/token', [
                'grant_type'    => 'client_credentials',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
            ]);

            if (! $response->successful()) {
                return trans('kiss_datafeed::app.credentials.invalid-credentials');
            }
        } catch (\Exception $e) {
            return trans('kiss_datafeed::app.credentials.connection-error');
        }

        return null;
    }

    /**
     * Check if a URL resolves to a private/internal IP address.
     */
    protected function isInternalUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (empty($host)) {
            return true;
        }

        $ip = gethostbyname($host);

        // gethostbyname returns the hostname on failure
        if ($ip === $host && ! filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }

        return ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
