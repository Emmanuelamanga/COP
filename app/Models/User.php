<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_active'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function cases()
    {
        return $this->hasMany(CaseModel::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isVerifier()
    {
        return $this->role === 'verifier' || $this->isAdmin();
    }

    public function isApprover()
    {
        return $this->role === 'approver' || $this->isAdmin();
    }
}
