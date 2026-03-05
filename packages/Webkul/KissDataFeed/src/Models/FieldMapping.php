<?php

namespace Webkul\KissDataFeed\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\KissDataFeed\Contracts\FieldMapping as FieldMappingContract;

class FieldMapping extends Model implements FieldMappingContract
{
    protected $table = 'kiss_datafeed_field_mappings';

    protected $fillable = [
        'credential_id',
        'mapping',
        'defaults',
    ];

    protected $casts = [
        'mapping'  => 'json',
        'defaults' => 'json',
    ];

    public function credential()
    {
        return $this->belongsTo(CredentialConfigProxy::modelClass(), 'credential_id');
    }
}
