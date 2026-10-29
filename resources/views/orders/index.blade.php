@extends('layouts.panel')
@section('title', auth()->user()->role === 'kasir' ? 'Pembayaran' : 'Pesanan')
@section('content')
<form class="card flex flex-wrap gap-3">
  <input name="q" value="{{ request('q') }}" placeholder="Invoice / nama" class="input w-56">
  <select name="status" class="input w-44"><option value="">Semua status</option>
    @foreach (\App\Models\Order::STATUSES as $k => $v)<option value="{{ $k }}" @selected(request('status') == $k)>{{ $v }}</option>@endforeach</select>
  <select name="payment" class="input w-44"><option value="">Semua pembayaran</option>
    @foreach (['belum_bayar' => 'Belum bayar', 'sebagian' => 'Sebagian', 'lunas' => 'Lunas'] as $k => $v)<option value="{{ $k }}" @selected(request('payment') == $k)>{{ $v }}</option>@endforeach</select>
  <button class="btn">Filter</button>
</form>

<div class="card overflow-x-auto p-0">
  <table class="min-w-full divide-y">
    <thead class="bg-slate-50"><tr><th class="th">Invoice</th><th class="th">Pelanggan</th><th class="th">Cabang</th><th class="th">Total</th><th class="th">Status</th><th class="th">Bayar</th><th class="th"></th></tr></thead>
    <tbody class="divide-y">
      @foreach ($orders as $o)
        <tr>
          <td class="td font-mono text-xs">{{ $o->invoice_no }}<br><span class="text-slate-400">{{ $o->created_at->format('d/m/y H:i') }}</span></td>
          <td class="td font-medium">{{ $o->customer->name }}</td>
          <td class="td text-xs">{{ $o->branch->name }}</td>
          <td class="td">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
          <td class="td"><span class="badge {{ $o->status_color }}">{{ \App\Models\Order::STATUSES[$o->status] }}</span></td>
          <td class="td"><span class="badge {{ $o->payment_color }}">{{ str_replace('_', ' ', $o->payment_status) }}</span></td>
          <td class="td text-right"><a href="{{ route('orders.show', $o) }}" class="btn-light">Detail</a></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
{{ $orders->links() }}
@endsection