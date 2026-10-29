<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    public function user()   { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function orders() { return $this->hasMany(Order::class); }

    public static function generateCode(): string
    {
        return 'JS-' . str_pad((int) static::max('id') + 1, 5, '0', STR_PAD_LEFT);
    }

    // Seragamkan nomor HP ke format 08xxxx (buang spasi, tanda hubung, +62 jadi 0)
    public static function normalizePhone(?string $phone): string
    {
        $n = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($n, '62')) {
            $n = '0' . substr($n, 2);
        }

        return $n;
    }

    // Tier membership berdasarkan total belanja
    public function getTierAttribute(): string
    {
        return match (true) {
            $this->total_spent >= 1500000 => 'Gold',
            $this->total_spent >= 500000  => 'Silver',
            default                       => 'Reguler',
        };
    }

    public function getDiscountPercentAttribute(): int
    {
        return match ($this->tier) {
            'Gold'   => 10,
            'Silver' => 5,
            default  => 0,
        };
    }
}