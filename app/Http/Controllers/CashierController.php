<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class CashierController extends Controller
{
    public function recap(Request $request)
    {
        $branchId = auth()->user()->branchScope();
        $date     = $request->input('date', today()->toDateString());

        $payments = Payment::with('order.customer', 'receiver')
            ->whereDate('paid_at', $date)
            ->when($branchId, fn ($q) => $q->whereHas('order', fn ($o) => $o->where('branch_id', $branchId)))
            ->latest('paid_at')->get();

        $byMethod = $payments->groupBy('method')->map->sum('amount');

        $unpaid = Order::visibleTo(auth()->user())->with('customer')
            ->whereNotIn('status', ['batal', 'menunggu'])
            ->where('payment_status', '!=', 'lunas')
            ->latest()->get();

        return view('cashier.recap', compact('date', 'payments', 'byMethod', 'unpaid'));
    }
}