<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Branch extends Model
{
    protected $guarded = [];

    protected $casts = ['is_main' => 'boolean'];

    // URL gambar QRIS cabang (null jika belum diunggah)
    public function getQrisUrlAttribute(): ?string
{
    return $this->qris ? Storage::disk('public')->url($this->qris) : null;
}

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}