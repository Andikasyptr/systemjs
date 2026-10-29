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
        $scope = auth()->user()->branchScope();

        return view('expenses.index', [
            'expenses'   => Expense::with('branch')
                ->when($scope !== null, fn ($q) => $q->where('branch_id', $scope))
                ->latest('spent_at')->paginate(15),
            'branches'   => $scope !== null ? Branch::where('id', $scope)->get() : Branch::all(),
            'categories' => self::CATEGORIES,
            'locked'     => $scope !== null,
        ]);
    }

    public function store(Request $request)
    {
        $scope = auth()->user()->branchScope();

        $data = $request->validate([
            'spent_at'    => 'required|date',
            'category'    => 'required|in:' . implode(',', self::CATEGORIES),
            'description' => 'required|max:255',
            'amount'      => 'required|integer|min:1',
            'branch_id'   => 'nullable|exists:branches,id',
        ]);

        if ($scope !== null) {
            $data['branch_id'] = $scope;
        }

        Expense::create($data + ['created_by' => auth()->id()]);

        return back()->with('success', 'Pengeluaran dicatat.');
    }

    public function destroy(Expense $expense)
    {
        $scope = auth()->user()->branchScope();
        abort_if($scope !== null && (int) $expense->branch_id !== $scope, 403);

        $expense->delete();

        return back()->with('success', 'Dihapus.');
    }
}