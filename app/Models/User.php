<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function isOwner(): bool    { return $this->role === 'owner'; }
    public function isAdmin(): bool    { return $this->role === 'admin'; }
    public function isKasir(): bool    { return $this->role === 'kasir'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }

    // null = boleh melihat semua cabang (owner, atau staf yang belum diberi cabang)
    public function branchScope(): ?int
{
    if ($this->role === 'owner') {
        return $this->branch_id ? (int) $this->branch_id : null;
    }

    return (int) $this->branch_id;
}
}