<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $guarded = [];

    protected $casts = ['spent_at' => 'date'];

    public function branch()  { return $this->belongsTo(Branch::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}