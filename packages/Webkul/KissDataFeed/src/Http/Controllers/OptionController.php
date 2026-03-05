<?php

namespace Webkul\KissDataFeed\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\KissDataFeed\Repositories\CredentialRepository;

class OptionController extends Controller
{
    public function __construct(
        protected CredentialRepository $credentialRepository,
        protected AttributeRepository $attributeRepository
    ) {}

    public function listAttributes(): JsonResponse
    {
        $query = request()->get('query', '');
        $page = request()->get('page', 1);
        $entityName = request()->get('entityName');

        $builder = $this->attributeRepository->newQuery();

        if (! empty($entityName)) {
            $types = json_decode($entityName, true);

            if (is_array($types)) {
                $builder->whereIn('type', $types);
            }
        }

        if (! empty($query)) {
            $builder->where('code', 'LIKE', '%'.$query.'%');
        }

        $attributes = $builder->orderBy('id')->paginate(20, ['*'], 'page', $page);

        $currentLocaleCode = core()->getRequestedLocaleCode();

        $options = [];

        foreach ($attributes as $attribute) {
            $translatedLabel = $attribute->translate($currentLocaleCode)?->name;

            $options[] = [
                'id'    => $attribute->id,
                'code'  => $attribute->code,
                'type'  => $attribute->type,
                'label' => ! empty($translatedLabel) ? $translatedLabel : "[{$attribute->code}]",
            ];
        }

        return new JsonResponse([
            'options'  => $options,
            'page'     => $attributes->currentPage(),
            'lastPage' => $attributes->lastPage(),
        ]);
    }

    public function listCredentials(): JsonResponse
    {
        $credentials = $this->credentialRepository->findWhere(['active' => true]);

        return new JsonResponse($credentials->map(fn ($c) => [
            'id'    => $c->id,
            'label' => $c->api_url.' ('.$c->client_id.')',
        ])->values());
    }
}
