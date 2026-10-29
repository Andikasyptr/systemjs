<?php
namespace App\Support;

class Wa
{
    public static function link(string $phone, string $text): string
    {
        $n = preg_replace('/\D/', '', $phone);
        if (str_starts_with($n, '0')) $n = '62' . substr($n, 1);
        return "https://wa.me/{$n}?text=" . rawurlencode($text);
    }
}