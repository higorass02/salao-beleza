<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'apelido',
        'email',
        'phone',
        'notes',
        'birth_day',
        'birth_month',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
