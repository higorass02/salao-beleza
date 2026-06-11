<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashEntry extends Model
{
    protected $fillable = [
        'date',
        'employee_id',
        'performed_at',
        'client_name',
        'service_name',
        'service_value',
        'include_house_fee',
        'paid_to',
        'notes',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    protected $casts = [
        'date'                => 'date',
        'service_value'     => 'decimal:2',
        'include_house_fee' => 'boolean',
    ];
}
