<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'employee_id',
        'service_id',
        'starts_at',
        'ends_at',
        'status',
        'notes',
        'paid_to',
    ];

    protected $casts = [
        'starts_at' => 'datetime:Y-m-d H:i:s',
        'ends_at'   => 'datetime:Y-m-d H:i:s',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
