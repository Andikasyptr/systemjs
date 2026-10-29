<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrderPhoto extends Model
{
    protected $guarded = [];

    public function order() { return $this->belongsTo(Order::class); }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}