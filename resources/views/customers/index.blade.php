@extends('layouts.panel')
@section('title', 'Data Pelanggan')
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
  <form class="flex gap-2"><input name="q" value="{{ request('q') }}" placeholder="Cari nama / HP / kode" class="input w-64"><button class="btn-light">Cari</button></form>
  @if (auth()->user()->role === 'admin')<a href="{{ route('customers.create') }}" class="btn">+ Pelanggan Baru</a>@endif
</div>

<div class="card overflow-x-auto p-0">
  <table class="min-w-full divide-y">
    <thead class="bg-slate-50"><tr><th class="th">Kode</th><th class="th">Nama</th><th class="th">HP</th><th class="th">Alamat</th><th class="th">Tier</th><th class="th">Poin</th><th class="th">Order</th><th class="th"></th></tr></thead>
    <tbody class="divide-y">
      @foreach ($customers as $c)
        <tr>
          <td class="td font-mono text-xs">{{ $c->member_code }}</td>
          <td class="td font-medium">{{ $c->name }}</td>
          <td class="td">{{ $c->phone }}</td>
          <td class="td max-w-xs truncate">{{ $c->address }}</td>
          <td class="td"><span class="badge {{ $c->tier === 'Gold' ? 'bg-amber-100 text-amber-700' : ($c->tier === 'Silver' ? 'bg-slate-200 text-slate-700' : 'bg-slate-100 text-slate-500') }}">{{ $c->tier }}</span></td>
          <td class="td">{{ $c->points }}</td>
          <td class="td">{{ $c->orders_count }}</td>
          <td class="td text-right">@if (auth()->user()->role === 'admin')<a href="{{ route('customers.edit', $c) }}" class="btn-light">Edit</a>@endif</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
{{ $customers->links() }}
@endsection