<?php

return [
    [
        'key'   => 'kiss_datafeed',
        'name'  => 'kiss_datafeed::app.acl.kiss-datafeed',
        'route' => 'kiss_datafeed.credentials.index',
        'sort'  => 10,
    ],
    [
        'key'   => 'kiss_datafeed.credentials',
        'name'  => 'kiss_datafeed::app.acl.credentials',
        'route' => 'kiss_datafeed.credentials.index',
        'sort'  => 1,
    ],
    [
        'key'   => 'kiss_datafeed.credentials.create',
        'name'  => 'kiss_datafeed::app.acl.create',
        'route' => 'kiss_datafeed.credentials.store',
        'sort'  => 1,
    ],
    [
        'key'   => 'kiss_datafeed.credentials.edit',
        'name'  => 'kiss_datafeed::app.acl.edit',
        'route' => ['kiss_datafeed.credentials.edit', 'kiss_datafeed.credentials.update'],
        'sort'  => 2,
    ],
    [
        'key'   => 'kiss_datafeed.credentials.delete',
        'name'  => 'kiss_datafeed::app.acl.delete',
        'route' => 'kiss_datafeed.credentials.destroy',
        'sort'  => 3,
    ],
    [
        'key'   => 'kiss_datafeed.mapping',
        'name'  => 'kiss_datafeed::app.acl.mapping',
        'route' => 'kiss_datafeed.mapping.select',
        'sort'  => 2,
    ],
    [
        'key'   => 'kiss_datafeed.export',
        'name'  => 'kiss_datafeed::app.acl.export',
        'route' => ['kiss_datafeed.export.index', 'kiss_datafeed.export.run'],
        'sort'  => 3,
    ],
];
