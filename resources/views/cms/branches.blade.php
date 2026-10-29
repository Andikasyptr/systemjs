@extends('layouts.panel')
@section('title', 'Cabang')
@section('content')
@include('cms._tabs')

<form method="POST" action="{{ route('cms.branches.store') }}" enctype="multipart/form-data" class="card grid gap-4 md:grid-cols-6">
  @csrf
  <div class="md:col-span-2"><label class="label">Nama cabang</label><input name="name" class="input" required></div>
  <div class="md:col-span-4"><label class="label">Alamat lengkap</label><input name="address" class="input" required></div>
  <div class="md:col-span-2"><label class="label">No. WhatsApp cabang</label><input name="whatsapp" class="input" placeholder="0812xxxxxxx"></div>
  <div class="md:col-span-2"><label class="label">Instagram cabang (tanpa @)</label><input name="instagram" class="input" placeholder="jejaksuci.cikarang"></div>
  <div><label class="label">Telepon lain</label><input name="phone" class="input"></div>
  <label class="flex items-center gap-2 pt-6 text-sm"><input type="checkbox" name="is_main" value="1" class="rounded text-red-600"> Cabang pusat</label>
  <div class="md:col-span-3"><label class="label">Gambar QRIS cabang (jpg, png, webp, maks 3 MB)</label><input type="file" name="qris" accept="image/*" class="input bg-white"></div>
  <div class="md:col-span-6"><button class="btn">+ Tambah Cabang</button></div>
</form>

@foreach ($branches as $b)
  <div class="card">
    <form method="POST" action="{{ route('cms.branches.update', $b) }}" enctype="multipart/form-data" class="grid gap-4 md:grid-cols-6">
      @csrf @method('PUT')
      <div class="md:col-span-2"><label class="label">Nama cabang</label><input name="name" value="{{ $b->name }}" class="input" required></div>
      <div class="md:col-span-4"><label class="label">Alamat lengkap</label><input name="address" value="{{ $b->address }}" class="input" required></div>
      <div class="md:col-span-2"><label class="label">No. WhatsApp cabang</label><input name="whatsapp" value="{{ $b->whatsapp }}" class="input" placeholder="0812xxxxxxx"></div>
      <div class="md:col-span-2"><label class="label">Instagram cabang (tanpa @)</label><input name="instagram" value="{{ $b->instagram }}" class="input" placeholder="jejaksuci.cikarang"></div>
      <div><label class="label">Telepon lain</label><input name="phone" value="{{ $b->phone }}" class="input"></div>
      <label class="flex items-center gap-2 pt-6 text-sm"><input type="checkbox" name="is_main" value="1" @checked($b->is_main) class="rounded text-red-600"> Cabang pusat</label>

      {{-- QRIS cabang --}}
      <div class="md:col-span-6 rounded-xl bg-neutral-50 p-4">
        <p class="label">QRIS pembayaran cabang ini</p>
        <div class="flex flex-wrap items-start gap-4">
          @if ($b->qris_url)
            <img src="{{ $b->qris_url }}" alt="QRIS {{ $b->name }}" class="h-36 w-36 rounded-lg border bg-white object-contain p-1">
          @else
            <div class="grid h-36 w-36 place-items-center rounded-lg border-2 border-dashed border-neutral-300 text-center text-xs text-neutral-400">Belum ada QRIS</div>
          @endif
          <div class="space-y-2">
            <input type="file" name="qris" accept="image/*" class="input bg-white text-xs">
            <p class="text-xs text-neutral-500">Unggah gambar baru untuk mengganti.</p>
            @if ($b->qris_url)
              <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_qris" value="1" class="rounded text-red-600"> Hapus QRIS</label>
            @endif
          </div>
        </div>
      </div>

      <div class="flex gap-2 md:col-span-6">
        <button class="btn">Simpan</button>
        <button type="submit" form="del-branch-{{ $b->id }}" class="btn-danger" onclick="return confirm('Hapus cabang ini?')">Hapus</button>
      </div>
    </form>
    <form id="del-branch-{{ $b->id }}" method="POST" action="{{ route('cms.branches.destroy', $b) }}">@csrf @method('DELETE')</form>
  </div>
@endforeach
@endsection