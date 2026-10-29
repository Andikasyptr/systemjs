<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public const CATEGORIES = ['Bahan & Chemical', 'Gaji', 'Sewa', 'Listrik & Air', 'Transport', 'Marketing', 'Lainnya'];

    public function index()
    {
        return view('expenses.index', [
            'expenses'   => Expense::with('branch')->latest('spent_at')->paginate(15),
            'branches'   => Branch::all(),
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        Expense::create($request->validate([
            'spent_at'    => 'required|date',
            'category'    => 'required|in:' . implode(',', self::CATEGORIES),
            'description' => 'required|max:255',
            'amount'      => 'required|integer|min:1',
            'branch_id'   => 'nullable|exists:branches,id',
        ]) + ['created_by' => auth()->id()]);

        return back()->with('success', 'Pengeluaran dicatat.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return back()->with('success', 'Dihapus.');
    }
}