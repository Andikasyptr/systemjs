@extends('layouts.panel')
@section('title', 'Buat Pesanan')
@section('content')
<form method="POST" action="{{ route('orders.store') }}" enctype="multipart/form-data" class="space-y-4"
  x-data="{
    services: {{ $services->map(fn ($s) => ['id' => $s->id, 'price' => $s->price, 'days' => $s->duration_days])->toJson() }},
    items: [{ service_id: '', item_name: '', qty: 1 }],
    previews: [],
    tooMany: false,
    add() { this.items.push({ service_id: '', item_name: '', qty: 1 }) },
    price(i) { const s = this.services.find(x => x.id == i.service_id); return s ? s.price * i.qty : 0 },
    get total() { return this.items.reduce((t, i) => t + this.price(i), 0) },
    fmt(n) { return new Intl.NumberFormat('id-ID').format(n) },
    pick(e) {
      const files = Array.from(e.target.files);
      this.tooMany = files.length > 5;
      if (this.tooMany) { e.target.value = ''; this.previews = []; return; }
      this.previews = files.map(f => URL.createObjectURL(f));
    }
  }">
  @csrf

  <div class="card grid gap-4 md:grid-cols-3">
    <div>
      <label class="label">Pelanggan</label>
      <select name="customer_id" class="input" required>
        <option value="">- pilih -</option>
        @foreach ($customers as $c)
          <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }} · {{ $c->phone }} ({{ $c->tier }}){{ $c->user_id ? ' ✓ akun' : '' }}</option>
        @endforeach
      </select>
      <a href="{{ route('customers.create') }}" class="mt-1 inline-block text-xs text-red-600 underline">+ pelanggan baru</a>
    </div>

    <div>
      <label class="label">Cabang</label>
      <select name="branch_id" class="input {{ $branches->count() === 1 ? 'bg-neutral-100' : '' }}" required>
        @foreach ($branches as $b)
          <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
        @endforeach
      </select>
      @if ($branches->count() === 1)
        <p class="mt-1 text-xs text-neutral-500">Terkunci sesuai cabang akun Anda.</p>
      @endif
    </div>

    <div>
      <label class="label">Estimasi Selesai</label>
      <input type="date" name="estimated_done_at" value="{{ old('estimated_done_at') }}" class="input">
    </div>
  </div>

  <div class="card space-y-3">
    <h3 class="font-semibold">Item Cucian</h3>
    <template x-for="(item, idx) in items" :key="idx">
      <div class="grid gap-3 md:grid-cols-12">
        <div class="md:col-span-4">
          <select :name="`items[${idx}][service_id]`" x-model="item.service_id" class="input" required>
            <option value="">- treatment -</option>
            @foreach ($services as $s)
              <option value="{{ $s->id }}">{{ $s->name }} — Rp {{ number_format($s->price, 0, ',', '.') }}</option>
            @endforeach
          </select>
        </div>
        <div class="md:col-span-4">
          <input :name="`items[${idx}][item_name]`" x-model="item.item_name" placeholder="Nama barang (mis. Nike AF1 putih)" class="input" required>
        </div>
        <div class="md:col-span-1">
          <input type="number" min="1" :name="`items[${idx}][qty]`" x-model="item.qty" class="input" required>
        </div>
        <div class="flex items-center text-sm font-medium md:col-span-2" x-text="'Rp ' + fmt(price(item))"></div>
        <div class="md:col-span-1">
          <button type="button" class="btn-danger" @click="items.splice(idx, 1)" x-show="items.length > 1">✕</button>
        </div>
      </div>
    </template>
    <button type="button" class="btn-light" @click="add()">+ Tambah item</button>
    <p class="text-right text-lg font-bold">Subtotal: <span class="text-red-600" x-text="'Rp ' + fmt(total)"></span></p>
    <p class="text-right text-xs text-neutral-500">Diskon member dihitung otomatis saat disimpan.</p>
  </div>

  {{-- Foto barang --}}
  <div class="card space-y-3">
    <div>
      <h3 class="font-semibold">Foto barang</h3>
      <p class="text-xs text-neutral-500">Satu foto berlaku untuk semua treatment di pesanan ini. Pilih beberapa foto sekaligus jika barangnya berbeda (maks 5 foto, 4 MB per foto).</p>
    </div>

    <label class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-neutral-300 p-6 text-center hover:border-red-400 hover:bg-red-50">
      <span class="text-3xl">📷</span>
      <span class="text-sm font-semibold">Ketuk untuk pilih foto</span>
      <span class="text-xs text-neutral-500">JPG, PNG, atau WEBP</span>
      <input type="file" name="photos[]" accept="image/*" multiple class="sr-only" @change="pick($event)">
    </label>

    <p x-show="tooMany" x-cloak class="text-sm text-red-600">Maksimal 5 foto. Silakan pilih ulang.</p>

    <div x-show="previews.length" x-cloak class="grid grid-cols-3 gap-2 sm:grid-cols-5">
      <template x-for="(src, i) in previews" :key="i">
        <img :src="src" class="aspect-square w-full rounded-lg border object-cover" alt="Pratinjau foto">
      </template>
    </div>
  </div>

  <div class="card">
    <label class="label">Catatan</label>
    <textarea name="notes" rows="2" class="input">{{ old('notes') }}</textarea>
  </div>

  <button class="btn" :disabled="tooMany">Simpan Pesanan</button>
</form>
@endsection