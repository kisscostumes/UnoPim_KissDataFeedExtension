<?php

namespace Webkul\KissDataFeed\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\KissDataFeed\Contracts\DataMapping as DataMappingContract;

class DataMapping extends Model implements DataMappingContract
{
    protected $table = 'kiss_datafeed_data_mappings';

    protected $fillable = [
        'credential_id',
        'sku',
        'entity_type',
        'external_exists',
        'last_exported_at',
        'last_export_status',
        'last_error',
    ];

    protected $casts = [
        'external_exists'  => 'boolean',
        'last_exported_at' => 'datetime',
    ];

    public function credential()
    {
        return $this->belongsTo(CredentialConfigProxy::modelClass(), 'credential_id');
    }
}
