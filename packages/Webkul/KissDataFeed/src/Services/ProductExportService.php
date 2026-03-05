<?php

namespace Webkul\KissDataFeed\Services;

use Illuminate\Support\Facades\Log;
use Webkul\KissDataFeed\Exceptions\ApiConflictException;
use Webkul\KissDataFeed\Http\Controllers\MappingController;
use Webkul\KissDataFeed\Models\CredentialConfig;
use Webkul\KissDataFeed\Repositories\DataMappingRepository;
use Webkul\KissDataFeed\Repositories\FieldMappingRepository;
use Webkul\Product\Models\Product;
use Webkul\Product\Repositories\ProductRepository;

class ProductExportService
{
    /**
     * All 22 API field names (must all be present in payload).
     */
    protected const ALL_API_FIELDS = [
        'sku', 'name', 'description', 'washingInstructions', 'gender', 'audience',
        'mainColour', 'barcode', 'photo', 'theme',
        'packagedLength_in_cm', 'packagedWidth_in_cm', 'packagedHeight_in_cm', 'packagedWeight_in_g',
        'cartonLength_in_cm', 'cartonWidth_in_cm', 'cartonHeight_in_cm', 'cartonWeight_in_g', 'cartonQty',
        'amazonASIN', 'countryOfOrigin', 'status',
    ];

    /**
     * Decimal fields that require float coercion.
     */
    protected const DECIMAL_FIELDS = [
        'packagedLength_in_cm', 'packagedWidth_in_cm', 'packagedHeight_in_cm', 'packagedWeight_in_g',
        'cartonLength_in_cm', 'cartonWidth_in_cm', 'cartonHeight_in_cm', 'cartonWeight_in_g', 'cartonQty',
    ];

    public function __construct(
        protected KissApiClient $apiClient,
        protected FieldMappingRepository $fieldMappingRepository,
        protected DataMappingRepository $dataMappingRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Export all qualifying products for a credential.
     *
     * @return array{created: int, updated: int, failed: int, errors: array}
     */
    public function exportAll(CredentialConfig $credential, array $filters = []): array
    {
        $fieldMapping = $this->fieldMappingRepository->findOneWhere(['credential_id' => $credential->id]);
        $mapping = $fieldMapping->mapping ?? [];
        $defaults = $fieldMapping->defaults ?? [];

        $query = $this->productRepository->where('type', 'simple');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status'] === 'active' ? 1 : 0);
        }

        if (! empty($filters['attribute_family_id'])) {
            $query->where('attribute_family_id', $filters['attribute_family_id']);
        }

        if (! empty($filters['category'])) {
            $query->whereJsonContains('values->categories', $filters['category']);
        }

        $summary = ['created' => 0, 'updated' => 0, 'failed' => 0, 'errors' => []];

        $query->chunk(50, function ($products) use ($credential, $mapping, $defaults, &$summary) {
            foreach ($products as $product) {
                $result = $this->exportSingleProduct($credential, $product, $mapping, $defaults);

                match ($result['action']) {
                    'created' => $summary['created']++,
                    'updated' => $summary['updated']++,
                    default   => $summary['failed']++,
                };

                if ($result['error']) {
                    $summary['errors'][] = "{$product->sku}: {$result['error']}";
                }
            }
        });

        return $summary;
    }

    /**
     * Export a single product. Resolves field mapping from credential.
     *
     * @return array{status: string, action: string|null, error: string|null}
     */
    public function exportProduct(CredentialConfig $credential, Product $product): array
    {
        $fieldMapping = $this->fieldMappingRepository->findOneWhere(['credential_id' => $credential->id]);

        return $this->exportSingleProduct(
            $credential,
            $product,
            $fieldMapping->mapping ?? [],
            $fieldMapping->defaults ?? []
        );
    }

    /**
     * Internal: export a single product with pre-resolved mapping.
     *
     * @return array{status: string, action: string|null, error: string|null}
     */
    public function exportSingleProduct(CredentialConfig $credential, Product $product, array $mapping, array $defaults): array
    {
        $sku = $product->sku;

        try {
            $payload = $this->transformProduct($product, $mapping, $defaults);

            $existing = $this->apiClient->getProduct($credential, $sku);

            if ($existing) {
                $this->apiClient->updateProduct($credential, $sku, $payload);
                $action = 'updated';
            } else {
                $this->apiClient->createProduct($credential, $payload);
                $action = 'created';
            }

            $this->recordResult($credential->id, $sku, true, 'success', null);

            return ['status' => 'success', 'action' => $action, 'error' => null];
        } catch (ApiConflictException $e) {
            // 409 on create — product exists, try update instead
            try {
                $payload = $payload ?? $this->transformProduct($product, $mapping, $defaults);
                $this->apiClient->updateProduct($credential, $sku, $payload);
                $this->recordResult($credential->id, $sku, true, 'success', null);

                return ['status' => 'success', 'action' => 'updated', 'error' => null];
            } catch (\Exception $retryException) {
                $this->recordResult($credential->id, $sku, false, 'failed', $retryException->getMessage());

                return ['status' => 'failed', 'action' => null, 'error' => $retryException->getMessage()];
            }
        } catch (\Exception $e) {
            Log::error("Kiss DataFeed export failed for SKU {$sku}: {$e->getMessage()}");
            $this->recordResult($credential->id, $sku, false, 'failed', $e->getMessage());

            return ['status' => 'failed', 'action' => null, 'error' => $e->getMessage()];
        }
    }

    /**
     * Transform a UnoPim product to a DataFeed API payload using field mapping.
     * Iterates over ALL 22 API fields to ensure complete payload.
     */
    public function transformProduct(Product $product, array $mapping, array $defaults = []): array
    {
        $payload = [];

        foreach (self::ALL_API_FIELDS as $apiField) {
            if (! empty($mapping[$apiField])) {
                $payload[$apiField] = $this->getProductValue($product, $mapping[$apiField]);
            } elseif (isset($defaults[$apiField])) {
                $payload[$apiField] = $defaults[$apiField];
            } else {
                $payload[$apiField] = $this->getFieldDefault($apiField);
            }
        }

        // SKU always from product, never mapped
        $payload['sku'] = $product->sku;

        // Type coercion for decimal fields
        foreach (self::DECIMAL_FIELDS as $field) {
            $payload[$field] = (float) ($payload[$field] ?? 0);
        }

        // Child arrays null for MVP
        $payload['images'] = null;
        $payload['accessories'] = null;
        $payload['substitutions'] = null;
        $payload['compositions'] = null;
        $payload['prices'] = null;

        return $payload;
    }

    /**
     * Get a product attribute value from the values JSON.
     * Searches common, locale_specific, channel_specific, channel_locale_specific scopes.
     */
    protected function getProductValue(Product $product, string $attributeCode): mixed
    {
        $values = $product->values ?? [];

        // Common scope
        if (isset($values['common'][$attributeCode])) {
            return $values['common'][$attributeCode];
        }

        // Locale-specific (use first available locale)
        if (isset($values['locale_specific'])) {
            foreach ($values['locale_specific'] as $locale => $attrs) {
                if (isset($attrs[$attributeCode])) {
                    return $attrs[$attributeCode];
                }
            }
        }

        // Channel-specific (use first available channel)
        if (isset($values['channel_specific'])) {
            foreach ($values['channel_specific'] as $channel => $attrs) {
                if (isset($attrs[$attributeCode])) {
                    return $attrs[$attributeCode];
                }
            }
        }

        // Channel-locale-specific (use first available)
        if (isset($values['channel_locale_specific'])) {
            foreach ($values['channel_locale_specific'] as $channel => $locales) {
                foreach ($locales as $locale => $attrs) {
                    if (isset($attrs[$attributeCode])) {
                        return $attrs[$attributeCode];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get type-appropriate default for an unmapped field.
     */
    protected function getFieldDefault(string $apiField): mixed
    {
        if (in_array($apiField, self::DECIMAL_FIELDS)) {
            return 0.00;
        }

        return '';
    }

    /**
     * Record export result in data_mappings table.
     */
    protected function recordResult(int $credentialId, string $sku, bool $exists, string $status, ?string $error): void
    {
        $existing = $this->dataMappingRepository->findOneWhere([
            'credential_id' => $credentialId,
            'sku'           => $sku,
            'entity_type'   => 'product',
        ]);

        $data = [
            'credential_id'      => $credentialId,
            'sku'                => $sku,
            'entity_type'        => 'product',
            'external_exists'    => $exists,
            'last_exported_at'   => now(),
            'last_export_status' => $status,
            'last_error'         => $error,
        ];

        if ($existing) {
            $this->dataMappingRepository->update($data, $existing->id);
        } else {
            $this->dataMappingRepository->create($data);
        }
    }
}
