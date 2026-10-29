<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month    = $request->input('month', now()->format('Y-m'));
        $branchId = $request->input('branch_id');
        $start    = Carbon::parse($month . '-01')->startOfMonth();
        $end      = $start->copy()->endOfMonth();

        $orders = fn () => Order::where('status', '!=', 'batal')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $grossSales = $orders()->whereBetween('created_at', [$start, $end])->sum('subtotal');
        $discounts  = $orders()->whereBetween('created_at', [$start, $end])->sum('discount');
        $netSales   = $grossSales - $discounts;

        $expenses = Expense::whereBetween('spent_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('category, SUM(amount) as total')->groupBy('category')->pluck('total', 'category');
        $totalExpense = $expenses->sum();
        $netProfit    = $netSales - $totalExpense;

        $cashIn = Payment::whereBetween('paid_at', [$start, $end])
            ->when($branchId, fn ($q) => $q->whereHas('order', fn ($o) => $o->where('branch_id', $branchId)))
            ->selectRaw('method, SUM(amount) as total')->groupBy('method')->pluck('total', 'method');
        $totalCashIn = $cashIn->sum();

        $receivable = $orders()->where('payment_status', '!=', 'lunas')
            ->selectRaw('COALESCE(SUM(total - paid_amount),0) as s')->value('s');

        return view('reports.index', [
            'month' => $month, 'branchId' => $branchId, 'branches' => Branch::all(),
            'grossSales' => $grossSales, 'discounts' => $discounts, 'netSales' => $netSales,
            'expenses' => $expenses, 'totalExpense' => $totalExpense, 'netProfit' => $netProfit,
            'cashIn' => $cashIn, 'totalCashIn' => $totalCashIn, 'receivable' => $receivable,
        ]);
    }
}