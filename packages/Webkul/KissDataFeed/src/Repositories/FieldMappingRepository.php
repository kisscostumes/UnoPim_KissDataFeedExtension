<?php

namespace Webkul\KissDataFeed\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\KissDataFeed\Contracts\FieldMapping;

class FieldMappingRepository extends Repository
{
    public function model(): string
    {
        return FieldMapping::class;
    }
}
