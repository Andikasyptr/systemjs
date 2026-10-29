<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = ['paid_at' => 'datetime'];

    public function order()    { return $this->belongsTo(Order::class); }
    public function receiver() { return $this->belongsTo(User::class, 'received_by'); }
}