<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }
}