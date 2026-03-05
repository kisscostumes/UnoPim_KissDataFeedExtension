<?php

namespace Webkul\KissDataFeed\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\KissDataFeed\Contracts\CredentialConfig;

class CredentialRepository extends Repository
{
    public function model(): string
    {
        return CredentialConfig::class;
    }
}
