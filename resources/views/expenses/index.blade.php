@extends('layouts.panel')
@section('title', 'Pengeluaran')
@section('content')
<form method="POST" action="{{ route('expenses.store') }}" class="card grid gap-4 md:grid-cols-6">
  @csrf
  <div><label class="label">Tanggal</label><input type="date" name="spent_at" value="{{ date('Y-m-d') }}" class="input" required></div>
  <div><label class="label">Kategori</label><select name="category" class="input">@foreach ($categories as $c)<option>{{ $c }}</option>@endforeach</select></div>
  <div><label class="label">Cabang</label><select name="branch_id" class="input"><option value="">Semua</option>@foreach ($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select></div>
  <div class="md:col-span-2"><label class="label">Keterangan</label><input name="description" class="input" required></div>
  <div><label class="label">Jumlah</label><input type="number" name="amount" class="input" required></div>
  <div class="md:col-span-6"><button class="btn">Catat Pengeluaran</button></div>
</form>

<div class="card overflow-x-auto p-0">
  <table class="min-w-full divide-y">
    <thead class="bg-slate-50"><tr><th class="th">Tanggal</th><th class="th">Kategori</th><th class="th">Cabang</th><th class="th">Keterangan</th><th class="th text-right">Jumlah</th><th class="th"></th></tr></thead>
    <tbody class="divide-y">
      @foreach ($expenses as $e)
        <tr><td class="td">{{ $e->spent_at->format('d/m/Y') }}</td><td class="td">{{ $e->category }}</td><td class="td">{{ $e->branch->name ?? 'Semua' }}</td><td class="td">{{ $e->description }}</td>
          <td class="td text-right">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
          <td class="td text-right"><form method="POST" action="{{ route('expenses.destroy', $e) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn-danger">Hapus</button></form></td></tr>
      @endforeach
    </tbody>
  </table>
</div>
{{ $expenses->links() }}
@endsection