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
            ->when($request->branch, fn ($q, $b) => $q->where('branch_id', $b))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%")
                ->orWhere('member_code', 'like', "%$s%")))
            ->latest()->paginate(15)->withQueryString();

        // Jumlah pelanggan per cabang
        $branches = Branch::withCount('customers')->orderByDesc('is_main')->orderBy('id')->get();

        return view('customers.index', [
            'customers'  => $customers,
            'branches'   => $branches,
            'total'      => Customer::count(),
            'noBranch'   => Customer::whereNull('branch_id')->count(),
        ]);
    }

    public function create()
    {
        return view('customers.form', ['customer' => null, 'branches' => Branch::orderByDesc('is_main')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->rules($request);
        $data['phone'] = Customer::normalizePhone($data['phone']);

        Customer::create($data + ['member_code' => Customer::generateCode()]);

        return redirect()->route('customers.index')->with('success', 'Pelanggan ditambahkan.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.form', ['customer' => $customer, 'branches' => Branch::orderByDesc('is_main')->get()]);
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $this->rules($request);
        $data['phone'] = Customer::normalizePhone($data['phone']);

        $customer->update($data);

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