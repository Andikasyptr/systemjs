<?php

namespace App\Models;

use App\Support\Wa;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    protected $casts = ['estimated_done_at' => 'date'];

    public const STATUSES = [
        'menunggu' => 'Menunggu Konfirmasi',
        'diterima' => 'Diterima',
        'diproses' => 'Sedang Dicuci',
        'selesai'  => 'Selesai (Siap Diambil)',
        'diambil'  => 'Sudah Diambil',
        'batal'    => 'Dibatalkan',
    ];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function branch()   { return $this->belongsTo(Branch::class); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }
    public function items()    { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function getRemainingAttribute(): int
    {
        return $this->total - $this->paid_amount;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'menunggu' => 'bg-yellow-100 text-yellow-800',
            'diterima' => 'bg-neutral-100 text-neutral-700',
            'diproses' => 'bg-amber-100 text-amber-700',
            'selesai'  => 'bg-red-100 text-red-700',
            'diambil'  => 'bg-neutral-900 text-white',
            default    => 'bg-red-600 text-white',
        };
    }

    public function getPaymentColorAttribute(): string
    {
        return match ($this->payment_status) {
            'lunas'    => 'bg-emerald-100 text-emerald-700',
            'sebagian' => 'bg-amber-100 text-amber-700',
            default    => 'bg-red-100 text-red-700',
        };
    }

    public static function generateInvoice(): string
    {
        $n = static::whereDate('created_at', today())->count() + 1;
        return 'INV-' . now()->format('ymd') . '-' . str_pad($n, 3, '0', STR_PAD_LEFT);
    }

    public function waLink(string $type): string
    {
        $c     = $this->customer;
        $total = 'Rp ' . number_format($this->total, 0, ',', '.');
        $sisa  = 'Rp ' . number_format($this->remaining, 0, ',', '.');
        $est   = $this->estimated_done_at?->format('d M Y') ?? '-';
        $head  = "Halo kak {$c->name} 👋\nPesanan *{$this->invoice_no}* di {$this->branch->name}";

        $text = match ($type) {
            'diterima' => "{$head} sudah kami terima.\nTotal: {$total}\nEstimasi selesai: {$est}",
            'diproses' => "{$head} sedang dalam proses pencucian ya kak. Estimasi selesai: {$est}.",
            'selesai'  => "{$head} sudah SELESAI dan siap diambil.\nSisa pembayaran: {$sisa}",
            'tagihan'  => "{$head}\nTotal {$total}, sisa tagihan {$sisa}. Mohon konfirmasi pembayarannya ya kak 🙏",
            'lunas'    => "{$head} sudah LUNAS. Terima kasih sudah mencuci di Jejak Suci 🙏",
            default    => $head,
        };

        return Wa::link($c->phone, $text);
    }

    public function photos() { return $this->hasMany(OrderPhoto::class); }

    // Batasi pesanan sesuai cabang staf yang login
public function scopeVisibleTo($query, User $user)
{
    $branchId = $user->branchScope();

    return $branchId ? $query->where('branch_id', $branchId) : $query;
}

// 403 jika staf membuka pesanan cabang lain
public function authorizeFor(User $user): void
{
    $branchId = $user->branchScope();

    abort_if($branchId && $this->branch_id !== $branchId, 403, 'Pesanan ini milik cabang lain.');
}
}