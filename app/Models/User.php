<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'is_admin',
        'employee_id',
        'must_change_password',
        'notifications_enabled',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'     => 'datetime',
            'password'              => 'hashed',
            'is_admin'              => 'boolean',
            'must_change_password'  => 'boolean',
            'notifications_enabled' => 'boolean',
        ];
    }

    public function isCollaborator(): bool
    {
        return $this->employee_id !== null;
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
