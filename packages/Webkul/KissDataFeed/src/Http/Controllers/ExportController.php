<?php

namespace Webkul\KissDataFeed\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\DataTransfer\Helpers\Export;
use Webkul\DataTransfer\Jobs\Export\ExportTrackBatch;
use Webkul\DataTransfer\Repositories\JobInstancesRepository;
use Webkul\DataTransfer\Repositories\JobTrackRepository;
use Webkul\KissDataFeed\Repositories\CredentialRepository;

class ExportController extends Controller
{
    public function __construct(
        protected CredentialRepository $credentialRepository,
        protected JobInstancesRepository $jobInstancesRepository,
        protected JobTrackRepository $jobTrackRepository
    ) {}

    public function index()
    {
        $credentials = $this->credentialRepository->findWhere(['active' => true]);

        $attributeFamilies = DB::table('attribute_families')
            ->select('id', 'code')
            ->orderBy('code')
            ->get();

        $categories = DB::table('categories')
            ->select('id', 'code')
            ->orderBy('code')
            ->get();

        return view('kiss_datafeed::export.index', compact('credentials', 'attributeFamilies', 'categories'));
    }

    public function run(): JsonResponse
    {
        $validated = request()->validate([
            'credential_id'       => 'required|integer',
            'status'              => 'nullable|in:active,inactive',
            'attribute_family_id' => 'nullable|integer|exists:attribute_families,id',
            'category'            => 'nullable|string|max:255',
        ]);

        $credential = $this->credentialRepository->find($validated['credential_id']);

        if (! $credential || ! $credential->active) {
            return new JsonResponse([
                'error' => trans('kiss_datafeed::app.export.invalid-credential'),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $filterFields = [
            ['name' => 'credential_id', 'value' => $validated['credential_id']],
        ];

        if (! empty($validated['status'])) {
            $filterFields[] = ['name' => 'status', 'value' => $validated['status']];
        }

        if (! empty($validated['attribute_family_id'])) {
            $filterFields[] = ['name' => 'attribute_family_id', 'value' => $validated['attribute_family_id']];
        }

        if (! empty($validated['category'])) {
            $filterFields[] = ['name' => 'category', 'value' => $validated['category']];
        }

        $jobInstance = $this->jobInstancesRepository->create([
            'code'                => 'kiss_datafeed_export_'.time(),
            'entity_type'         => 'kissDataFeedProduct',
            'type'                => 'export',
            'action'              => 'fetch',
            'validation_strategy' => 'skip',
            'filters'             => [
                'fields' => $filterFields,
            ],
        ]);

        $userId = auth()->guard('admin')->user()->id;

        $jobTrack = $this->jobTrackRepository->create([
            'action'              => 'export',
            'validation_strategy' => 'skip',
            'type'                => 'export',
            'state'               => Export::STATE_PENDING,
            'allowed_errors'      => 0,
            'meta'                => $jobInstance->toJson(),
            'job_instances_id'    => $jobInstance->id,
            'user_id'             => $userId,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        ExportTrackBatch::dispatch($jobTrack);

        return new JsonResponse([
            'message'      => trans('kiss_datafeed::app.export.export-queued'),
            'job_track_id' => $jobTrack->id,
            'redirect_url' => route('admin.settings.data_transfer.tracker.view', $jobTrack->id),
        ]);
    }
}
