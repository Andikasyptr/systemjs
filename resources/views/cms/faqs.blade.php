@extends('layouts.panel')
@section('title', 'FAQ')
@section('content')
@include('cms._tabs')

<form method="POST" action="{{ route('cms.faqs.store') }}" class="card space-y-3">
  @csrf
  <h3 class="font-semibold">Tambah FAQ</h3>
  <div><label class="label">Pertanyaan</label><input name="question" class="input" required></div>
  <div><label class="label">Jawaban</label><textarea name="answer" rows="3" class="input" required></textarea></div>
  <div class="flex flex-wrap items-end gap-4">
    <div class="w-28"><label class="label">Urutan</label><input type="number" name="sort_order" value="{{ ($faqs->max('sort_order') ?? 0) + 1 }}" class="input"></div>
    <label class="flex items-center gap-2 pb-2 text-sm"><input type="checkbox" name="is_active" value="1" checked class="rounded text-red-600"> Tampilkan</label>
    <button class="btn">+ Tambah</button>
  </div>
</form>

@foreach ($faqs as $f)
  <div class="card">
    <form method="POST" action="{{ route('cms.faqs.update', $f) }}" class="space-y-3">
      @csrf @method('PUT')
      <div><label class="label">Pertanyaan</label><input name="question" value="{{ $f->question }}" class="input" required></div>
      <div><label class="label">Jawaban</label><textarea name="answer" rows="3" class="input" required>{{ $f->answer }}</textarea></div>
      <div class="flex flex-wrap items-end gap-4">
        <div class="w-28"><label class="label">Urutan</label><input type="number" name="sort_order" value="{{ $f->sort_order }}" class="input"></div>
        <label class="flex items-center gap-2 pb-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked($f->is_active) class="rounded text-red-600"> Tampilkan</label>
        <button class="btn">Simpan</button>
        <button type="submit" form="del-faq-{{ $f->id }}" class="btn-danger" onclick="return confirm('Hapus FAQ ini?')">Hapus</button>
      </div>
    </form>
    <form id="del-faq-{{ $f->id }}" method="POST" action="{{ route('cms.faqs.destroy', $f) }}">@csrf @method('DELETE')</form>
  </div>
@endforeach
@endsection