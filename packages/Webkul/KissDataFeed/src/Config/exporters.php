<?php

return [
    'kissDataFeedProduct' => [
        'title'    => 'kiss_datafeed::app.export.product_title',
        'exporter' => 'Webkul\KissDataFeed\Helpers\Exporters\ProductExporter',
        'source'   => 'Webkul\Product\Repositories\ProductRepository',
        'filters'  => [
            'fields' => [
                [
                    'name'       => 'credential_id',
                    'title'      => 'kiss_datafeed::app.export.credential',
                    'required'   => true,
                    'validation' => 'required',
                    'type'       => 'select',
                    'async'      => true,
                    'track_by'   => 'id',
                    'label_by'   => 'label',
                    'list_route' => 'kiss_datafeed.credential.fetch-all',
                ],
            ],
        ],
    ],
];
