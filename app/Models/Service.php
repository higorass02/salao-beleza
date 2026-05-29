<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
        'active',
        'provider_percentage',
        'include_house_fee',
    ];

    protected $casts = [
        'active'              => 'boolean',
        'price'               => 'decimal:2',
        'provider_percentage' => 'decimal:2',
        'include_house_fee'   => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
