@extends('layouts.panel')
@section('title', 'Laporan Keuangan')
@section('content')
@php $rp = fn($n) => 'Rp ' . number_format($n, 0, ',', '.'); @endphp
<form class="card flex flex-wrap gap-3">
  <input type="month" name="month" value="{{ $month }}" class="input w-48">
  <select name="branch_id" class="input w-64"><option value="">Semua cabang</option>
    @foreach ($branches as $b)<option value="{{ $b->id }}" @selected($branchId == $b->id)>{{ $b->name }}</option>@endforeach</select>
  <button class="btn">Tampilkan</button>
  <button type="button" onclick="window.print()" class="btn-light">Cetak</button>
</form>

<div class="grid gap-4 lg:grid-cols-2">
  <div class="card">
    <h3 class="mb-3 text-lg font-bold">Laporan Laba Rugi</h3>
    <table class="w-full text-sm">
      <tr><td class="py-1.5">Penjualan kotor</td><td class="text-right">{{ $rp($grossSales) }}</td></tr>
      <tr><td class="py-1.5">Diskon member</td><td class="text-right text-rose-600">({{ $rp($discounts) }})</td></tr>
      <tr class="border-t font-semibold"><td class="py-1.5">Pendapatan bersih</td><td class="text-right">{{ $rp($netSales) }}</td></tr>
      <tr><td colspan="2" class="pt-3 font-semibold">Beban operasional</td></tr>
      @foreach ($expenses as $cat => $amt)
        <tr><td class="py-1 pl-4">{{ $cat }}</td><td class="text-right">({{ $rp($amt) }})</td></tr>
      @endforeach
      <tr class="border-t font-semibold"><td class="py-1.5">Total beban</td><td class="text-right">({{ $rp($totalExpense) }})</td></tr>
      <tr class="border-t-2 text-base font-bold {{ $netProfit >= 0 ? 'text-emerald-700' : 'text-rose-700' }}"><td class="py-2">LABA / (RUGI) BERSIH</td><td class="text-right">{{ $rp($netProfit) }}</td></tr>
    </table>
  </div>

  <div class="space-y-4">
    <div class="card">
      <h3 class="mb-3 text-lg font-bold">Laporan Arus Kas</h3>
      <table class="w-full text-sm">
        @foreach (['tunai', 'transfer', 'qris'] as $m)
          <tr><td class="py-1 uppercase">Kas masuk - {{ $m }}</td><td class="text-right">{{ $rp($cashIn[$m] ?? 0) }}</td></tr>
        @endforeach
        <tr class="border-t font-semibold"><td class="py-1.5">Total kas masuk</td><td class="text-right">{{ $rp($totalCashIn) }}</td></tr>
        <tr><td class="py-1.5">Kas keluar (pengeluaran)</td><td class="text-right">({{ $rp($totalExpense) }})</td></tr>
        <tr class="border-t-2 font-bold"><td class="py-2">Kenaikan / (penurunan) kas</td><td class="text-right">{{ $rp($totalCashIn - $totalExpense) }}</td></tr>
      </table>
    </div>
    <div class="card">
      <h3 class="text-lg font-bold">Piutang Usaha</h3>
      <p class="mt-2 text-3xl font-bold text-amber-600">{{ $rp($receivable) }}</p>
      <p class="text-xs text-slate-500">Total tagihan pesanan yang belum lunas (semua periode).</p>
    </div>
  </div>
</div>
@endsection