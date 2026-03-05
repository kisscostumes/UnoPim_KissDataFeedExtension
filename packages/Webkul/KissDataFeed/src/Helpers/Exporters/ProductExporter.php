<?php

namespace Webkul\KissDataFeed\Helpers\Exporters;

use Illuminate\Support\Facades\Log;
use Webkul\DataTransfer\Contracts\JobTrackBatch as JobTrackBatchContract;
use Webkul\DataTransfer\Helpers\Export as ExportHelper;
use Webkul\DataTransfer\Helpers\Exporters\AbstractExporter;
use Webkul\DataTransfer\Jobs\Export\File\FlatItemBuffer as FileExportFileBuffer;
use Webkul\DataTransfer\Repositories\JobTrackBatchRepository;
use Webkul\KissDataFeed\Repositories\CredentialRepository;
use Webkul\KissDataFeed\Repositories\FieldMappingRepository;
use Webkul\KissDataFeed\Services\ProductExportService;
use Webkul\Product\Repositories\ProductRepository;

class ProductExporter extends AbstractExporter
{
    public const BATCH_SIZE = 25;

    protected bool $exportsFile = false;

    public function __construct(
        protected JobTrackBatchRepository $exportBatchRepository,
        protected FileExportFileBuffer $exportFileBuffer,
        protected CredentialRepository $credentialRepository,
        protected FieldMappingRepository $fieldMappingRepository,
        protected ProductRepository $productRepository,
        protected ProductExportService $productExportService
    ) {
        parent::__construct($exportBatchRepository, $exportFileBuffer);
    }

    public function exportBatch(JobTrackBatchContract $batch, $filePath): bool
    {
        $credentialId = $this->getFilterValue('credential_id');

        $credential = $this->credentialRepository->find($credentialId);

        if (! $credential || ! $credential->active) {
            Log::error('Kiss DataFeed: Invalid or inactive credential for export');
            $this->updateBatchState($batch->id, ExportHelper::STATE_PROCESSED);

            return false;
        }

        $fieldMapping = $this->fieldMappingRepository->findOneWhere(['credential_id' => $credentialId]);
        $mapping = $fieldMapping->mapping ?? [];
        $defaults = $fieldMapping->defaults ?? [];

        $skus = array_column($batch->data, 'sku');
        $products = $this->productRepository
            ->whereIn('sku', $skus)
            ->get();

        $missingCount = count($skus) - $products->count();

        $created = 0;
        $updated = 0;
        $failed = 0;

        foreach ($products as $index => $product) {
            // Throttle to stay under API rate limit (500 req/min for admin)
            // Each product makes ~2 requests, 300ms delay = ~400 req/min
            if ($index > 0) {
                usleep(300000);
            }

            try {
                $result = $this->productExportService->exportSingleProduct($credential, $product, $mapping, $defaults);

                match ($result['action']) {
                    'created' => $created++,
                    'updated' => $updated++,
                    default   => $failed++,
                };
            } catch (\Throwable $e) {
                Log::error("Kiss DataFeed: Failed to export SKU {$product->sku}: {$e->getMessage()}");
                $failed++;
            }
        }

        Log::info("Kiss DataFeed batch complete: created={$created}, updated={$updated}, failed={$failed}, missing={$missingCount}");

        $this->createdItemsCount += $created + $updated;
        $this->skippedItemsCount += $failed + $missingCount;

        $this->updateBatchState($batch->id, ExportHelper::STATE_PROCESSED);

        return true;
    }

    /**
     * Override to filter products based on job filters.
     */
    protected function getResults()
    {
        $query = $this->source->where('type', 'simple');

        $status = $this->getFilterValue('status');
        if ($status) {
            $query->where('status', $status === 'active' ? 1 : 0);
        }

        $familyId = $this->getFilterValue('attribute_family_id');
        if ($familyId) {
            $query->where('attribute_family_id', $familyId);
        }

        $category = $this->getFilterValue('category');
        if ($category) {
            $query->whereJsonContains('values->categories', $category);
        }

        return $query->get()->getIterator();
    }

    /**
     * Extract a filter value from the job instance filters.
     */
    protected function getFilterValue(string $name): mixed
    {
        $filters = $this->getFilters();
        $fields = $filters['fields'] ?? [];

        foreach ($fields as $field) {
            if (($field['name'] ?? '') === $name) {
                return $field['value'] ?? null;
            }
        }

        return null;
    }
}
