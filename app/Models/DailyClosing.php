<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClosing extends Model
{
    protected $fillable = [
        'date',
        'status',
        'total_value',
        'provider_total',
        'store_total',
        'house_fee_total',
        'closed_at',
    ];

    protected $casts = [
        'date'            => 'date',
        'total_value'     => 'decimal:2',
        'provider_total'  => 'decimal:2',
        'store_total'     => 'decimal:2',
        'house_fee_total' => 'decimal:2',
        'closed_at'       => 'datetime',
    ];

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }
}
