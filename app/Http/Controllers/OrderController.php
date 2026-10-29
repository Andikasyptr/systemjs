<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::visibleTo(auth()->user())->with('customer', 'branch')
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->payment, fn ($q, $v) => $q->where('payment_status', $v))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('invoice_no', 'like', "%$s%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$s%"))))
            ->latest()->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $scope = auth()->user()->branchScope();
        abort_if($scope === 0, 403, 'Akun Anda belum diatur cabangnya. Hubungi owner.');

        return view('orders.create', [
            'customers' => Customer::orderBy('name')->get(),
            'branches'  => $scope !== null ? Branch::where('id', $scope)->get() : Branch::orderByDesc('is_main')->orderBy('id')->get(),
            'services'  => Service::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $scope = auth()->user()->branchScope();
        abort_if($scope === 0, 403, 'Akun Anda belum diatur cabangnya. Hubungi owner.');

        $data = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'branch_id'          => 'required|exists:branches,id',
            'estimated_done_at'  => 'nullable|date',
            'notes'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.item_name'  => 'required|string|max:100',
            'items.*.qty'        => 'required|integer|min:1',
            'photos'             => 'nullable|array|max:5',
            'photos.*'           => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        // Admin cabang selalu mencatat ke cabangnya sendiri
        if ($scope !== null) {
            $data['branch_id'] = $scope;
        }

        $order = DB::transaction(function () use ($data, $request) {
            $customer = Customer::findOrFail($data['customer_id']);

            $order = Order::create([
                'invoice_no'        => Order::generateInvoice(),
                'customer_id'       => $customer->id,
                'branch_id'         => $data['branch_id'],
                'created_by'        => auth()->id(),
                'status'            => 'diterima',
                'source'            => 'admin',
                'estimated_done_at' => $data['estimated_done_at'] ?? null,
                'notes'             => $data['notes'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $row) {
                $svc  = Service::findOrFail($row['service_id']);
                $line = $svc->price * $row['qty'];

                $order->items()->create([
                    'service_id' => $svc->id,
                    'item_name'  => $row['item_name'],
                    'qty'        => $row['qty'],
                    'price'      => $svc->price,
                    'subtotal'   => $line,
                ]);
                $subtotal += $line;
            }

            foreach ($request->file('photos', []) as $file) {
                $order->photos()->create(['path' => $file->store('order-photos', 'public')]);
            }

            $discount = intdiv($subtotal * $customer->discount_percent, 100);
            $order->update(['subtotal' => $subtotal, 'discount' => $discount, 'total' => $subtotal - $discount]);

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Pesanan dibuat. Kirim info WA ke pelanggan.');
    }

    public function show(Order $order)
    {
        $order->authorizeFor(auth()->user());
        $order->load('items.service', 'payments.receiver', 'customer', 'branch', 'creator', 'photos');

        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $order->authorizeFor(auth()->user());

        $data = $request->validate([
            'status'            => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
            'estimated_done_at' => 'nullable|date',
        ]);

        $order->update([
            'status'            => $data['status'],
            'estimated_done_at' => $data['estimated_done_at'] ?? $order->estimated_done_at,
        ]);

        return back()->with('success', 'Status pesanan diperbarui.');
    }
}