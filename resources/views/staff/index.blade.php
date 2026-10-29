@extends('layouts.panel')
@section('title', 'Akun ' . ucfirst($target))
@section('content')
@php $isOwner = auth()->user()->role === 'owner'; @endphp

<form method="POST" action="{{ route('staff.store') }}" class="card grid gap-4 md:grid-cols-6">
  @csrf
  <div><label class="label">Nama</label><input name="name" class="input" value="{{ old('name') }}" required></div>
  <div><label class="label">Email</label><input type="email" name="email" class="input" value="{{ old('email') }}" required></div>
  <div><label class="label">No. HP</label><input name="phone" class="input" value="{{ old('phone') }}"></div>
  <div><label class="label">Password</label><input type="password" name="password" class="input" required></div>

  @if ($isOwner)
    <div>
      <label class="label">Cabang</label>
      <select name="branch_id" class="input" required>
        <option value="">- pilih -</option>
        @foreach ($branches as $b)
          <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->is_main ? 'Pusat' : 'Cabang' }} · {{ $b->name }}</option>
        @endforeach
      </select>
    </div>
  @else
    <div>
      <label class="label">Cabang</label>
      <input class="input bg-neutral-100" value="{{ auth()->user()->branch->name ?? 'Semua cabang' }}" disabled>
    </div>
  @endif

  <div class="flex items-end"><button class="btn w-full">Tambah {{ ucfirst($target) }}</button></div>
</form>

<div class="card overflow-x-auto p-0">
  <table class="min-w-full divide-y">
    <thead class="bg-neutral-50"><tr><th class="th">Nama</th><th class="th">Email</th><th class="th">HP</th><th class="th">Cabang</th><th class="th">Status</th><th class="th"></th></tr></thead>
    <tbody class="divide-y">
      @forelse ($users as $u)
        <tr>
          <td class="td font-medium">{{ $u->name }}</td>
          <td class="td">{{ $u->email }}</td>
          <td class="td">{{ $u->phone }}</td>
          <td class="td">
            @if ($u->branch)
              <span class="badge {{ $u->branch->is_main ? 'bg-red-600 text-white' : 'bg-neutral-900 text-white' }}">{{ $u->branch->is_main ? 'Pusat' : 'Cabang' }}</span>
              <span class="text-xs text-neutral-500">{{ $u->branch->name }}</span>
            @else
              <span class="badge bg-amber-100 text-amber-700">Belum diatur</span>
            @endif
          </td>
          <td class="td"><span class="badge {{ $u->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-neutral-200 text-neutral-600' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
          <td class="td text-right">
            <form method="POST" action="{{ route('staff.toggle', $u) }}">@csrf @method('PATCH')
              <button class="btn-light">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="td text-center text-neutral-500">Belum ada akun.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection