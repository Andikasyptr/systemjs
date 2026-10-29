<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'customer') {
            return redirect()->route('portal.index');
        }

        $branchId = $user->branchScope();
        $orders   = fn () => Order::visibleTo($user);

        $income = Payment::whereDate('paid_at', today())
            ->when($branchId, fn ($q) => $q->whereHas('order', fn ($o) => $o->where('branch_id', $branchId)))
            ->sum('amount');

        $stats = [
            'Menunggu konfirmasi' => $orders()->where('status', 'menunggu')->count(),
            'Order hari ini'      => $orders()->whereDate('created_at', today())->count(),
            'Pemasukan hari ini'  => 'Rp ' . number_format($income, 0, ',', '.'),
            'Sedang diproses'     => $orders()->whereIn('status', ['diterima', 'diproses'])->count(),
            'Belum lunas'         => $orders()->whereNotIn('status', ['batal', 'menunggu'])->where('payment_status', '!=', 'lunas')->count(),
            'Pertanyaan baru'     => Inquiry::where('status', 'baru')->count(),
        ];

        return view('dashboard', compact('stats'));
    }
}