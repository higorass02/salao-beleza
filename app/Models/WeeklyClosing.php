<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyClosing extends Model
{
    protected $fillable = [
        'week_start',
        'week_end',
        'total_value',
        'provider_total',
        'store_total',
        'house_fee_total',
        'days_summary',
        'closed_at',
    ];

    protected $casts = [
        'week_start'      => 'date',
        'week_end'        => 'date',
        'total_value'     => 'decimal:2',
        'provider_total'  => 'decimal:2',
        'store_total'     => 'decimal:2',
        'house_fee_total' => 'decimal:2',
        'days_summary'    => 'array',
        'closed_at'       => 'datetime',
    ];
}
