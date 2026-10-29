<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $order->authorizeFor(auth()->user());

        $data = $request->validate([
            'amount' => 'required|integer|min:1',
            'method' => 'required|in:tunai,transfer,qris',
            'note'   => 'nullable|max:255',
        ]);

        if ($order->status === 'batal') {
            return back()->withErrors(['amount' => 'Pesanan sudah dibatalkan.']);
        }
        if ($order->status === 'menunggu') {
            return back()->withErrors(['amount' => 'Pesanan belum dikonfirmasi admin.']);
        }
        if ($order->payment_status === 'lunas') {
            return back()->withErrors(['amount' => 'Pesanan ini sudah lunas.']);
        }

        try {
            DB::transaction(function () use ($order, $data) {
                $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

                $remaining = $order->total - $order->paid_amount;
                if ($data['amount'] > $remaining) {
                    throw new \RuntimeException('Nominal melebihi sisa tagihan (Rp ' . number_format($remaining, 0, ',', '.') . ').');
                }

                $order->payments()->create($data + [
                    'received_by' => auth()->id(),
                    'paid_at'     => now(),
                ]);

                $order->paid_amount += $data['amount'];
                $order->payment_status = $order->paid_amount >= $order->total ? 'lunas' : 'sebagian';
                $order->save();

                if ($order->payment_status === 'lunas') {
                    $order->customer->increment('total_spent', $order->total);
                    $order->customer->increment('points', intdiv($order->total, 10000));
                }
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return back()->with('success', 'Pembayaran dicatat.');
    }
}