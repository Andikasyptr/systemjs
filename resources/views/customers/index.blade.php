@extends('layouts.panel')
@section('title', 'Data Pelanggan')
@section('content')
@php $role = auth()->user()->role; @endphp

{{-- Jumlah pelanggan per cabang --}}
<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
  <div class="card">
    <p class="text-xs uppercase text-neutral-500">Total pelanggan</p>
    <p class="mt-1 text-3xl font-extrabold">{{ $total }}</p>
  </div>
  @foreach ($branches as $b)
    <a href="{{ route('customers.index', ['branch' => $b->id]) }}"
       class="card transition hover:ring-red-400 {{ request('branch') == $b->id ? 'ring-2 ring-red-600' : '' }}">
      <p class="text-xs uppercase text-neutral-500">Member {{ $b->is_main ? 'Pusat' : 'Cabang' }}</p>
      <p class="mt-1 text-3xl font-extrabold text-red-600">{{ $b->customers_count }}</p>
      <p class="truncate text-xs text-neutral-500">{{ $b->name }}</p>
    </a>
  @endforeach
  @if ($noBranch > 0)
    <a href="{{ route('customers.index') }}" class="card">
      <p class="text-xs uppercase text-neutral-500">Belum pilih cabang</p>
      <p class="mt-1 text-3xl font-extrabold text-amber-600">{{ $noBranch }}</p>
    </a>
  @endif
</div>

<div class="flex flex-wrap items-center justify-between gap-3">
  <form class="flex flex-wrap gap-2">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama / HP / kode" class="input w-64">
    <select name="branch" class="input w-56">
      <option value="">Semua cabang</option>
      @foreach ($branches as $b)
        <option value="{{ $b->id }}" @selected(request('branch') == $b->id)>{{ $b->name }}</option>
      @endforeach
    </select>
    <button class="btn-light">Cari</button>
  </form>
  @if ($role === 'admin')<a href="{{ route('customers.create') }}" class="btn">+ Pelanggan Baru</a>@endif
</div>

<div class="card overflow-x-auto p-0">
  <table class="min-w-full divide-y">
    <thead class="bg-neutral-50"><tr>
      <th class="th">Kode</th><th class="th">Nama</th><th class="th">HP</th><th class="th">Alamat</th><th class="th">Cabang</th>
      <th class="th">Tier</th><th class="th">Poin</th><th class="th">Order</th><th class="th">Akun</th><th class="th"></th>
    </tr></thead>
    <tbody class="divide-y">
      @foreach ($customers as $c)
        <tr>
          <td class="td font-mono text-xs">{{ $c->member_code }}</td>
          <td class="td font-medium">{{ $c->name }}</td>
          <td class="td">{{ $c->phone }}</td>
          <td class="td max-w-xs truncate">{{ $c->address ?: '-' }}</td>
          <td class="td">
            @if ($c->branch)
              <span class="badge {{ $c->branch->is_main ? 'bg-red-600 text-white' : 'bg-neutral-900 text-white' }}">{{ $c->branch->is_main ? 'Pusat' : 'Cabang' }}</span>
              <span class="block text-xs text-neutral-500">{{ $c->branch->name }}</span>
            @else
              <span class="badge bg-amber-100 text-amber-700">Belum dipilih</span>
            @endif
          </td>
          <td class="td"><span class="badge {{ $c->tier === 'Gold' ? 'bg-amber-100 text-amber-700' : ($c->tier === 'Silver' ? 'bg-neutral-200 text-neutral-700' : 'bg-neutral-100 text-neutral-500') }}">{{ $c->tier }}</span></td>
          <td class="td">{{ $c->points }}</td>
          <td class="td">{{ $c->orders_count }}</td>
          <td class="td">
            @if ($c->user_id)<span class="badge bg-emerald-100 text-emerald-700">Terhubung</span>
            @else<span class="badge bg-neutral-100 text-neutral-500">Belum daftar</span>@endif
          </td>
          <td class="td text-right">@if ($role === 'admin')<a href="{{ route('customers.edit', $c) }}" class="btn-light">Edit</a>@endif</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
{{ $customers->links() }}
@endsection