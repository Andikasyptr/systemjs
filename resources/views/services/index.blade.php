@extends('layouts.panel')
@section('title', 'Layanan & Harga')
@section('content')
<form method="POST" action="{{ route('services.store') }}" class="card grid gap-4 md:grid-cols-5">
  @csrf
  <div class="md:col-span-2"><label class="label">Nama Treatment</label><input name="name" class="input" required></div>
  <div><label class="label">Kategori</label>
    <select name="category" class="input"><option value="sepatu">Sepatu</option><option value="tas">Tas</option><option value="helm">Helm</option></select></div>
  <div><label class="label">Harga (Rp)</label><input type="number" name="price" class="input" required></div>
  <div><label class="label">Durasi (hari)</label><input type="number" name="duration_days" value="3" class="input" required></div>
  <div class="md:col-span-4"><label class="label">Deskripsi</label><input name="description" class="input"></div>
  <div class="flex items-end"><button class="btn w-full">Tambah</button></div>
</form>

<div class="card overflow-x-auto p-0">
  <table class="min-w-full divide-y">
    <thead class="bg-slate-50"><tr><th class="th">Nama</th><th class="th">Kategori</th><th class="th">Harga</th><th class="th">Hari</th><th class="th">Aktif</th><th class="th"></th></tr></thead>
    <tbody class="divide-y">
      @foreach ($services as $s)
        <tr>
          <form method="POST" action="{{ route('services.update', $s) }}">@csrf @method('PUT')
            <td class="td"><input name="name" value="{{ $s->name }}" class="input"></td>
            <td class="td capitalize">{{ $s->category }}</td>
            <td class="td"><input type="number" name="price" value="{{ $s->price }}" class="input w-32"></td>
            <td class="td"><input type="number" name="duration_days" value="{{ $s->duration_days }}" class="input w-20"></td>
            <td class="td"><input type="checkbox" name="is_active" value="1" @checked($s->is_active) class="rounded text-teal-600"></td>
            <td class="td">
              <div class="flex gap-2">
                <button class="btn">Simpan</button>
          </form>
                <form method="POST" action="{{ route('services.destroy', $s) }}" onsubmit="return confirm('Hapus layanan?')">@csrf @method('DELETE')
                  <button class="btn-danger">Hapus</button>
                </form>
              </div>
            </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
@endsection