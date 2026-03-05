<?php

namespace Webkul\KissDataFeed\Providers;

use Webkul\Core\Providers\CoreModuleServiceProvider;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    protected $models = [
        \Webkul\KissDataFeed\Models\CredentialConfig::class,
        \Webkul\KissDataFeed\Models\FieldMapping::class,
        \Webkul\KissDataFeed\Models\DataMapping::class,
    ];
}
