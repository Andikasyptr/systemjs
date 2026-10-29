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

<div class="card overflow-x-auto p-0" x-data="{ edit: null }" @keydown.escape.window="edit = null">
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
          <td class="td">
            <div class="flex justify-end gap-2">
              <button type="button" class="btn-light"
                      @click="edit = { id: {{ $u->id }}, name: @js($u->name), email: @js($u->email), phone: @js($u->phone), branch_id: '{{ $u->branch_id }}', action: '{{ route('staff.update', $u) }}' }">Edit</button>

              <form method="POST" action="{{ route('staff.toggle', $u) }}">@csrf @method('PATCH')
                <button class="btn-light">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
              </form>

              <form method="POST" action="{{ route('staff.destroy', $u) }}" onsubmit="return confirm('Hapus akun {{ $u->name }}? Tindakan ini tidak bisa dibatalkan.')">
                @csrf @method('DELETE')
                <button class="btn-danger">Hapus</button>
              </form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="td text-center text-neutral-500">Belum ada akun.</td></tr>
      @endforelse
    </tbody>
  </table>

  {{-- Popup edit --}}
  <div x-show="edit" x-cloak x-transition.opacity @click="edit = null"
       class="fixed inset-0 z-50 grid place-items-center overflow-y-auto bg-black/70 p-4">
    <form method="POST" :action="edit?.action" @click.stop class="w-full max-w-md space-y-3 rounded-2xl bg-white p-6 shadow-2xl">
      @csrf @method('PUT')
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-extrabold">Edit Akun {{ ucfirst($target) }}</h3>
        <button type="button" @click="edit = null" class="text-2xl leading-none text-neutral-400 hover:text-neutral-800" aria-label="Tutup">✕</button>
      </div>

      <div><label class="label">Nama</label><input name="name" x-model="edit.name" class="input" required></div>
      <div><label class="label">Email</label><input type="email" name="email" x-model="edit.email" class="input" required></div>
      <div><label class="label">No. HP</label><input name="phone" x-model="edit.phone" class="input"></div>

      @if ($isOwner)
        <div>
          <label class="label">Cabang</label>
          <select name="branch_id" x-model="edit.branch_id" class="input" required>
            <option value="">- pilih -</option>
            @foreach ($branches as $b)
              <option value="{{ $b->id }}">{{ $b->is_main ? 'Pusat' : 'Cabang' }} · {{ $b->name }}</option>
            @endforeach
          </select>
        </div>
      @endif

      <div>
        <label class="label">Password baru <span class="font-normal text-neutral-400">(kosongkan jika tidak diganti)</span></label>
        <input type="password" name="password" class="input" minlength="8" autocomplete="new-password">
      </div>

      <div class="flex gap-2 pt-2">
        <button class="btn flex-1">Simpan Perubahan</button>
        <button type="button" @click="edit = null" class="btn-light">Batal</button>
      </div>
    </form>
  </div>
</div>
@endsection