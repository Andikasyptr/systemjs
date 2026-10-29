<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PortalController extends Controller
{
    private function resolveCustomer(): Customer
    {
        $user     = auth()->user();
        $phone    = Customer::normalizePhone($user->phone);
        $customer = $user->customer;

        $real = $phone
            ? Customer::where('phone', $phone)->whereNull('user_id')->first()
            : null;

        if ($real) {
            if ($customer && $customer->orders()->doesntExist()) {
                $customer->delete();
            }
            $real->update(['user_id' => $user->id]);

            return $real;
        }

        return $customer ?? Customer::create([
                'user_id'     => $user->id,
                'member_code' => Customer::generateCode(),
                'name'        => $user->name,
                'phone'       => $phone ?: (string) $user->phone,
            ]);
    }

    public function index()
    {
        $customer = $this->resolveCustomer();
        $orders   = $customer->orders()->with('items', 'branch')->latest()->get();

        $nextTarget = match ($customer->tier) {
            'Reguler' => 500000,
            'Silver'  => 1500000,
            default   => null,
        };

        return view('portal.index', [
            'customer'   => $customer,
            'orders'     => $orders,
            'active'     => $orders->whereNotIn('status', ['diambil', 'batal']),
            'nextTarget' => $nextTarget,
            'nextTier'   => $customer->tier === 'Reguler' ? 'Silver' : 'Gold',
            'progress'   => $nextTarget
                ? min(100, (int) round($customer->total_spent / $nextTarget * 100))
                : 100,
        ]);
    }

    public function orders(Request $request)
    {
        $customer = $this->resolveCustomer();
        $tab      = $request->input('tab', 'semua');

        $all = $customer->orders()->with('items', 'branch')->latest()->get();

        $counts = [
            'semua'    => $all->count(),
            'berjalan' => $all->whereNotIn('status', ['diambil', 'batal'])->count(),
            'selesai'  => $all->where('status', 'diambil')->count(),
            'batal'    => $all->where('status', 'batal')->count(),
        ];

        $orders = match ($tab) {
            'berjalan' => $all->whereNotIn('status', ['diambil', 'batal']),
            'selesai'  => $all->where('status', 'diambil'),
            'batal'    => $all->where('status', 'batal'),
            default    => $all,
        };

        return view('portal.orders', compact('customer', 'orders', 'counts', 'tab'));
    }

   public function show(Order $order)
{
    $customer = $this->resolveCustomer();
    abort_unless($order->customer_id === $customer->id, 403);

    $order->load('items.service', 'payments', 'branch', 'photos');

    return view('portal.show', compact('order', 'customer'));
}

    public function create(Request $request)
    {
        $customer = $this->resolveCustomer();
        $services = Service::where('is_active', true)->orderBy('category')->orderBy('name')->get();

        return view('portal.order', [
            'customer'   => $customer,
            'branches'   => Branch::orderByDesc('is_main')->orderBy('id')->get(),
            'services'   => $services,
            // Treatment yang diklik dari halaman depan (hanya jika layanan aktif)
            'selectedId' => $services->firstWhere('id', (int) $request->query('service'))?->id,
        ]);
    }

    public function store(Request $request)
{
    $customer = $this->resolveCustomer();

    $data = $request->validate([
        'branch_id'          => 'required|exists:branches,id',
        'notes'              => 'nullable|string|max:500',
        'items'              => 'required|array|min:1|max:20',
        'items.*.service_id' => ['required', Rule::exists('services', 'id')->where('is_active', true)],
        'items.*.item_name'  => 'required|string|max:100',
        'items.*.qty'        => 'required|integer|min:1|max:20',
        'photos'             => 'nullable|array|max:5',
        'photos.*'           => 'image|mimes:jpg,jpeg,png,webp|max:4096',
    ], [
        'photos.max'   => 'Maksimal 5 foto.',
        'photos.*.max' => 'Ukuran tiap foto maksimal 4 MB.',
    ]);

    $order = DB::transaction(function () use ($data, $customer, $request) {
        $order = Order::create([
            'invoice_no'  => Order::generateInvoice(),
            'customer_id' => $customer->id,
            'branch_id'   => $data['branch_id'],
            'created_by'  => auth()->id(),
            'status'      => 'menunggu',
            'source'      => 'online',
            'notes'       => $data['notes'] ?? null,
        ]);

        $subtotal = 0;
        $maxDays  = 0;

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
            $maxDays   = max($maxDays, $svc->duration_days);
        }

        // Foto barang (satu foto berlaku untuk semua treatment di pesanan ini)
        foreach ($request->file('photos', []) as $file) {
            $order->photos()->create(['path' => $file->store('order-photos', 'public')]);
        }

        $discount = intdiv($subtotal * $customer->discount_percent, 100);

        $order->update([
            'subtotal'          => $subtotal,
            'discount'          => $discount,
            'total'             => $subtotal - $discount,
            'estimated_done_at' => now()->addDays($maxDays)->toDateString(),
        ]);

        return $order;
    });

    return redirect()->route('portal.show', $order)
        ->with('success', 'Pesanan terkirim! Admin akan mengonfirmasi lewat WhatsApp. Silakan antar barang ke cabang yang dipilih.');
}

    public function cancel(Order $order)
    {
        $customer = $this->resolveCustomer();
        abort_unless($order->customer_id === $customer->id, 403);

        if ($order->status !== 'menunggu') {
            return back()->withErrors(['order' => 'Pesanan sudah diproses, hubungi admin untuk pembatalan.']);
        }

        $order->update(['status' => 'batal']);

        return redirect()->route('portal.orders')->with('success', 'Pesanan dibatalkan.');
    }
}