<?php

namespace Webkul\KissDataFeed\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\HistoryControl\Contracts\HistoryAuditable as HistoryContract;
use Webkul\HistoryControl\Traits\HistoryTrait;
use Webkul\KissDataFeed\Contracts\CredentialConfig as CredentialConfigContract;

class CredentialConfig extends Model implements CredentialConfigContract, HistoryContract
{
    use HistoryTrait;

    protected $table = 'kiss_datafeed_credentials';

    protected $historyTags = ['kiss_datafeed_credentials'];

    protected $auditExclude = ['client_secret', 'access_token'];

    protected $fillable = [
        'api_url',
        'client_id',
        'client_secret',
        'access_token',
        'token_expires_at',
        'active',
    ];

    protected $casts = [
        'client_secret'    => 'encrypted',
        'access_token'     => 'encrypted',
        'token_expires_at' => 'datetime',
        'active'           => 'boolean',
    ];
}
