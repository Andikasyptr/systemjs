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
        return $this->qris_path ? Storage::disk('public')->url($this->qris_path) : null;
    }
}