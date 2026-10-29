@extends('layouts.panel')
@section('title', 'Keunggulan')
@section('content')
@include('cms._tabs')

<form method="POST" action="{{ route('cms.features.store') }}" class="card grid gap-4 md:grid-cols-6">
  @csrf
  <div><label class="label">Ikon (emoji)</label><input name="icon" value="✨" class="input" required></div>
  <div class="md:col-span-2"><label class="label">Judul</label><input name="title" class="input" required></div>
  <div class="md:col-span-3"><label class="label">Deskripsi singkat</label><input name="description" class="input" required></div>
  <div><label class="label">Urutan</label><input type="number" name="sort_order" value="{{ ($features->max('sort_order') ?? 0) + 1 }}" class="input"></div>
  <label class="flex items-center gap-2 pt-6 text-sm"><input type="checkbox" name="is_active" value="1" checked class="rounded text-red-600"> Tampilkan</label>
  <div class="pt-5"><button class="btn">+ Tambah</button></div>
</form>

@foreach ($features as $f)
  <div class="card">
    <form method="POST" action="{{ route('cms.features.update', $f) }}" class="grid gap-4 md:grid-cols-6">
      @csrf @method('PUT')
      <div><label class="label">Ikon</label><input name="icon" value="{{ $f->icon }}" class="input" required></div>
      <div class="md:col-span-2"><label class="label">Judul</label><input name="title" value="{{ $f->title }}" class="input" required></div>
      <div class="md:col-span-3"><label class="label">Deskripsi</label><input name="description" value="{{ $f->description }}" class="input" required></div>
      <div><label class="label">Urutan</label><input type="number" name="sort_order" value="{{ $f->sort_order }}" class="input"></div>
      <label class="flex items-center gap-2 pt-6 text-sm"><input type="checkbox" name="is_active" value="1" @checked($f->is_active) class="rounded text-red-600"> Tampilkan</label>
      <div class="flex gap-2 pt-5 md:col-span-2">
        <button class="btn">Simpan</button>
        <button type="submit" form="del-feat-{{ $f->id }}" class="btn-danger" onclick="return confirm('Hapus?')">Hapus</button>
      </div>
    </form>
    <form id="del-feat-{{ $f->id }}" method="POST" action="{{ route('cms.features.destroy', $f) }}">@csrf @method('DELETE')</form>
  </div>
@endforeach
@endsection