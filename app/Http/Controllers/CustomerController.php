<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::withCount('orders')->with('branch')
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%")
                ->orWhere('member_code', 'like', "%$s%")))
            ->latest()->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.form', ['customer' => null, 'branches' => Branch::all()]);
    }

    public function store(Request $request)
    {
        Customer::create($this->rules($request) + ['member_code' => Customer::generateCode()]);
        return redirect()->route('customers.index')->with('success', 'Pelanggan ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', ['customer' => $customer, 'branches' => Branch::all()]);
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->rules($request));
        return redirect()->route('customers.index')->with('success', 'Data pelanggan diperbarui.');
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'name'      => 'required|max:100',
            'phone'     => 'required|max:20',
            'address'   => 'nullable',
            'branch_id' => 'nullable|exists:branches,id',
        ]);
    }
}