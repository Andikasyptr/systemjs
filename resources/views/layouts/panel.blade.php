<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Panel') - Clean Shoes Jejak Suci</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-neutral-100 text-neutral-800" x-data="{ open: false }">
@php
  $role = auth()->user()->role;
  $menu = match ($role) {
    'owner' => [
        ['Dashboard','dashboard'], ['Pesanan','orders.index'], ['Pelanggan','customers.index'],
        ['Akun Admin','staff.index'], ['Pengeluaran','expenses.index'], ['Laporan Keuangan','reports.index'],
        ['Rekap Pembayaran','cashier.recap'], ['Tampilan Website','cms.settings'],
    ],
    'admin' => [
        ['Dashboard','dashboard'], ['Buat Pesanan','orders.create'], ['Pesanan & Pembayaran','orders.index'],
        ['Rekap Pembayaran','cashier.recap'], ['Pelanggan','customers.index'], ['Layanan & Harga','services.index'],
        ['Akun Kasir','staff.index'], ['Pertanyaan','inquiries.index'], ['Tampilan Website','cms.settings'],
    ],
    'kasir' => [['Dashboard','dashboard'], ['Pembayaran','orders.index'], ['Rekap Pembayaran','cashier.recap']],
    default => [
        ['Akun Saya','portal.index'],
        ['Pesanan Saya','portal.orders'],
        ['Pesan Cucian','portal.order.create'],
    ],
  };
@endphp

<div class="flex min-h-screen">
  {{-- Sidebar --}}
  <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
         class="fixed inset-y-0 left-0 z-40 w-64 transform overflow-y-auto bg-neutral-950 text-neutral-100 transition lg:static lg:translate-x-0">
    <div class="border-b border-neutral-800 px-5 py-5">
      <a href="{{ route('home') }}" class="block leading-none">
        <span class="block text-xl font-black uppercase tracking-tight">👟 Clean Shoes</span>
        <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.3em] text-red-500">Jejak Suci</span>
      </a>
      <p class="mt-3 text-xs uppercase tracking-wider text-neutral-400">{{ $role }}</p>
      @if (auth()->user()->branch)
        <p class="mt-1 text-xs font-semibold text-red-400">📍 {{ auth()->user()->branch->name }}</p>
      @endif
    </div>
    <nav class="space-y-1 p-3">
      @foreach ($menu as [$label, $route])
        @php
          $active = match ($route) {
            'cms.settings'  => request()->routeIs('cms.*'),
            'portal.orders' => request()->routeIs('portal.orders', 'portal.show'),
            'orders.index'  => request()->routeIs('orders.index', 'orders.show'),
            default         => request()->routeIs($route),
          };
        @endphp
        <a href="{{ route($route) }}"
           class="block rounded-lg px-3 py-2 text-sm font-medium {{ $active ? 'bg-red-600 text-white' : 'text-neutral-300 hover:bg-neutral-800 hover:text-white' }}">
          {{ $label }}
        </a>
      @endforeach
    </nav>
  </aside>

  <div class="flex min-w-0 flex-1 flex-col">
    <header class="flex items-center justify-between bg-white px-4 py-3 shadow-sm">
      <button @click="open = !open" class="rounded-lg p-2 hover:bg-neutral-100 lg:hidden">☰</button>
      <h1 class="text-lg font-semibold">@yield('title')</h1>
      <div class="flex items-center gap-3 text-sm">
        <a href="{{ route('profile.edit') }}" class="text-neutral-600 hover:text-red-600">{{ auth()->user()->name }}</a>
        <form method="POST" action="{{ route('logout') }}">@csrf
          <button class="btn-light">Keluar</button>
        </form>
      </div>
    </header>

    <main class="flex-1 space-y-4 p-4 md:p-6">
      @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-800 ring-1 ring-emerald-200">{{ session('success') }}</div>
      @endif
      @if ($errors->any())
        <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">
          <ul class="list-inside list-disc">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif
      @yield('content')
    </main>
  </div>
</div>
</body>
</html>