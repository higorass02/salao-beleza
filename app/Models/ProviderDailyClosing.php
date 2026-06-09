<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDailyClosing extends Model
{
    protected $fillable = [
        'date',
        'employee_id',
        'total_services',
        'house_fee',
        'provider_share',
        'received_by_provider',
        'received_by_store',
        'net',
        'closed_at',
    ];

    protected $casts = [
        'date'                 => 'date',
        'total_services'       => 'decimal:2',
        'house_fee'            => 'decimal:2',
        'provider_share'       => 'decimal:2',
        'received_by_provider' => 'decimal:2',
        'received_by_store'    => 'decimal:2',
        'net'                  => 'decimal:2',
        'closed_at'            => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
