<?php

return [
    [
        'key'   => 'kiss_datafeed',
        'name'  => 'kiss_datafeed::app.title',
        'route' => 'kiss_datafeed.credentials.index',
        'sort'  => 10,
        'icon'  => 'icon-export',
    ],
    [
        'key'   => 'kiss_datafeed.credentials',
        'name'  => 'kiss_datafeed::app.credentials.title',
        'route' => 'kiss_datafeed.credentials.index',
        'sort'  => 1,
    ],
    [
        'key'        => 'kiss_datafeed.mapping',
        'name'       => 'kiss_datafeed::app.mapping.title',
        'route'      => 'kiss_datafeed.mapping.select',
        'sort'       => 2,
    ],
    [
        'key'   => 'kiss_datafeed.export',
        'name'  => 'kiss_datafeed::app.export.title',
        'route' => 'kiss_datafeed.export.index',
        'sort'  => 3,
    ],
];
