<?php

namespace Webkul\KissDataFeed\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\KissDataFeed\Contracts\DataMapping;

class DataMappingRepository extends Repository
{
    public function model(): string
    {
        return DataMapping::class;
    }
}
