@extends('layouts.panel')
@section('title', 'Galeri')
@section('content')
@include('cms._tabs')

<form method="POST" action="{{ route('cms.gallery.store') }}" enctype="multipart/form-data" class="card grid gap-4 md:grid-cols-4">
  @csrf
  <div><label class="label">Judul</label><input name="title" class="input" placeholder="Nike AF1 sebelum-sesudah" required></div>
  <div><label class="label">Keterangan</label><input name="caption" class="input"></div>
  <div><label class="label">Kategori</label>
    <select name="category" class="input"><option value="">Tanpa kategori</option><option value="sepatu">Sepatu</option><option value="tas">Tas</option><option value="helm">Helm</option></select></div>
  <div><label class="label">Urutan</label><input type="number" name="sort_order" value="{{ ($items->max('sort_order') ?? 0) + 1 }}" class="input"></div>
  <div class="md:col-span-2"><label class="label">Foto (jpg, png, webp, maks 3 MB)</label><input type="file" name="image" accept="image/*" class="input bg-white" required></div>
  <label class="flex items-center gap-2 pt-6 text-sm"><input type="checkbox" name="is_active" value="1" checked class="rounded text-red-600"> Tampilkan</label>
  <div class="pt-5"><button class="btn">Upload Foto</button></div>
</form>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
  @foreach ($items as $g)
    <div class="card space-y-3">
      <img src="{{ $g->url }}" alt="{{ $g->title }}" class="h-44 w-full rounded-lg object-cover">
      <form method="POST" action="{{ route('cms.gallery.update', $g) }}" enctype="multipart/form-data" class="space-y-3">
        @csrf @method('PUT')
        <input name="title" value="{{ $g->title }}" class="input" required>
        <input name="caption" value="{{ $g->caption }}" class="input" placeholder="Keterangan">
        <div class="grid grid-cols-2 gap-2">
          <select name="category" class="input">
            <option value="">Tanpa kategori</option>
            @foreach (['sepatu', 'tas', 'helm'] as $c)<option value="{{ $c }}" @selected($g->category === $c)>{{ ucfirst($c) }}</option>@endforeach
          </select>
          <input type="number" name="sort_order" value="{{ $g->sort_order }}" class="input">
        </div>
        <input type="file" name="image" accept="image/*" class="input bg-white text-xs">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($g->is_active) class="rounded text-red-600"> Tampilkan</label>
        <div class="flex gap-2">
          <button class="btn">Simpan</button>
          <button type="submit" form="del-gal-{{ $g->id }}" class="btn-danger" onclick="return confirm('Hapus foto ini?')">Hapus</button>
        </div>
      </form>
      <form id="del-gal-{{ $g->id }}" method="POST" action="{{ route('cms.gallery.destroy', $g) }}">@csrf @method('DELETE')</form>
    </div>
  @endforeach
</div>
@endsection