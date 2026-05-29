<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashEntry extends Model
{
    protected $fillable = [
        'date',
        'performed_at',
        'client_name',
        'service_name',
        'service_value',
        'provider_percentage',
        'include_house_fee',
        'paid_to',
        'notes',
    ];

    protected $casts = [
        'date'                => 'date',
        'service_value'       => 'decimal:2',
        'provider_percentage' => 'decimal:2',
        'include_house_fee'   => 'boolean',
    ];
}
