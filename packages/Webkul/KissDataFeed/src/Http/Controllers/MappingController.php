<?php

namespace Webkul\KissDataFeed\Http\Controllers;

use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\KissDataFeed\Repositories\CredentialRepository;
use Webkul\KissDataFeed\Repositories\FieldMappingRepository;

class MappingController extends Controller
{
    /**
     * The 22 mappable DataFeed API fields with labels and types.
     */
    public const MAPPING_FIELDS = [
        ['name' => 'sku',                   'label' => 'SKU',                    'types' => ['text']],
        ['name' => 'name',                  'label' => 'Name',                   'types' => ['text']],
        ['name' => 'description',           'label' => 'Description',            'types' => ['text', 'textarea']],
        ['name' => 'washingInstructions',   'label' => 'Washing Instructions',   'types' => ['text', 'textarea']],
        ['name' => 'gender',               'label' => 'Gender',                  'types' => ['text', 'select']],
        ['name' => 'audience',             'label' => 'Audience',                'types' => ['text', 'select']],
        ['name' => 'mainColour',           'label' => 'Main Colour',             'types' => ['text', 'select']],
        ['name' => 'barcode',              'label' => 'Barcode',                 'types' => ['text']],
        ['name' => 'photo',                'label' => 'Photo URL',               'types' => ['text', 'image']],
        ['name' => 'theme',                'label' => 'Theme',                   'types' => ['text', 'select']],
        ['name' => 'packagedLength_in_cm', 'label' => 'Packaged Length (cm)',    'types' => ['text', 'price']],
        ['name' => 'packagedWidth_in_cm',  'label' => 'Packaged Width (cm)',     'types' => ['text', 'price']],
        ['name' => 'packagedHeight_in_cm', 'label' => 'Packaged Height (cm)',    'types' => ['text', 'price']],
        ['name' => 'packagedWeight_in_g',  'label' => 'Packaged Weight (g)',     'types' => ['text', 'price']],
        ['name' => 'cartonLength_in_cm',   'label' => 'Carton Length (cm)',      'types' => ['text', 'price']],
        ['name' => 'cartonWidth_in_cm',    'label' => 'Carton Width (cm)',       'types' => ['text', 'price']],
        ['name' => 'cartonHeight_in_cm',   'label' => 'Carton Height (cm)',      'types' => ['text', 'price']],
        ['name' => 'cartonWeight_in_g',    'label' => 'Carton Weight (g)',       'types' => ['text', 'price']],
        ['name' => 'cartonQty',            'label' => 'Carton Qty',             'types' => ['text', 'price']],
        ['name' => 'amazonASIN',           'label' => 'Amazon ASIN',             'types' => ['text']],
        ['name' => 'countryOfOrigin',      'label' => 'Country of Origin',       'types' => ['text', 'select']],
        ['name' => 'status',               'label' => 'Status',                  'types' => ['text', 'select']],
    ];

    public function __construct(
        protected FieldMappingRepository $fieldMappingRepository,
        protected CredentialRepository $credentialRepository,
        protected AttributeRepository $attributeRepository
    ) {}

    public function select()
    {
        $credentials = $this->credentialRepository->findWhere(['active' => true]);

        return view('kiss_datafeed::mapping.select', compact('credentials'));
    }

    public function index(int $credentialId)
    {
        $credential = $this->credentialRepository->find($credentialId);

        if (! $credential) {
            abort(404);
        }

        $fieldMapping = $this->fieldMappingRepository->findOneWhere(['credential_id' => $credentialId]);

        $currentMappings = $fieldMapping->mapping ?? [];
        $currentDefaults = $fieldMapping->defaults ?? [];

        $mappingFields = self::MAPPING_FIELDS;

        $currentLocaleCode = core()->getRequestedLocaleCode();

        $attributes = $this->attributeRepository->all()->map(function ($attribute) use ($currentLocaleCode) {
            $translatedLabel = $attribute->translate($currentLocaleCode)?->name;

            return [
                'code'  => $attribute->code,
                'label' => ! empty($translatedLabel) ? $translatedLabel : "[{$attribute->code}]",
                'type'  => $attribute->type,
            ];
        })->sortBy('label')->values();

        return view('kiss_datafeed::mapping.index', compact(
            'credentialId',
            'credential',
            'mappingFields',
            'currentMappings',
            'currentDefaults',
            'attributes'
        ));
    }

    public function store()
    {
        $data = request()->except(['_token', '_method']);
        $credentialId = $data['credential_id'];

        unset($data['credential_id']);

        $mappings = [];
        $defaults = [];

        foreach (self::MAPPING_FIELDS as $field) {
            $fieldName = $field['name'];

            if (! empty($data[$fieldName])) {
                $mappings[$fieldName] = $data[$fieldName];
            }

            if (! empty($data['default_'.$fieldName])) {
                $defaults[$fieldName] = $data['default_'.$fieldName];
            }
        }

        $existing = $this->fieldMappingRepository->findOneWhere(['credential_id' => $credentialId]);

        if ($existing) {
            $this->fieldMappingRepository->update([
                'mapping'  => $mappings,
                'defaults' => $defaults,
            ], $existing->id);
        } else {
            $this->fieldMappingRepository->create([
                'credential_id' => $credentialId,
                'mapping'        => $mappings,
                'defaults'       => $defaults,
            ]);
        }

        session()->flash('success', trans('kiss_datafeed::app.mapping.save-success'));

        return redirect()->route('kiss_datafeed.mapping.index', $credentialId);
    }
}
