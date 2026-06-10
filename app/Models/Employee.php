<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'role',
        'active',
        'birth_day',
        'birth_month',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function hasUser(): bool
    {
        return $this->user()->exists();
    }
}
