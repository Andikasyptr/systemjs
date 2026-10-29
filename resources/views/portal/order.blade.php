@extends('layouts.panel')
@section('title', 'Pesan Cucian')
@section('content')

<a href="{{ route('portal.orders') }}" class="text-sm text-neutral-600 hover:text-red-600">← Pesanan Saya</a>

<form method="POST" action="{{ route('portal.order.store') }}" enctype="multipart/form-data" class="space-y-4"
  x-data="{
    services: {{ $services->map(fn ($s) => ['id' => $s->id, 'price' => $s->price, 'days' => $s->duration_days])->toJson() }},
    pct: {{ $customer->discount_percent }},
    items: [{ service_id: '{{ $selectedId ?? '' }}', item_name: '', qty: 1 }],
    previews: [],
    tooMany: false,
    add() { this.items.push({ service_id: '', item_name: '', qty: 1 }) },
    find(id) { return this.services.find(s => s.id == id) },
    line(i) { const s = this.find(i.service_id); return s ? s.price * i.qty : 0 },
    get subtotal() { return this.items.reduce((t, i) => t + this.line(i), 0) },
    get discount() { return Math.floor(this.subtotal * this.pct / 100) },
    get total() { return this.subtotal - this.discount },
    get days() { return Math.max(0, ...this.items.map(i => this.find(i.service_id)?.days || 0)) },
    fmt(n) { return new Intl.NumberFormat('id-ID').format(n) },
    pick(e) {
      const files = Array.from(e.target.files);
      this.tooMany = files.length > 5;
      if (this.tooMany) { e.target.value = ''; this.previews = []; return; }
      this.previews = files.map(f => URL.createObjectURL(f));
    }
  }">
  @csrf

  <div class="card">
    <label class="label">Antar ke cabang</label>
    <select name="branch_id" class="input" required>
      @foreach ($branches as $b)
        <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }} — {{ $b->address }}</option>
      @endforeach
    </select>
  </div>

  <div class="card space-y-3">
    <h3 class="font-bold">Treatment yang dipilih</h3>

    <template x-for="(item, idx) in items" :key="idx">
      <div class="grid gap-3 rounded-xl border border-neutral-200 p-3 md:grid-cols-12 md:border-0 md:p-0">
        <div class="md:col-span-4">
          <select :name="`items[${idx}][service_id]`" x-model="item.service_id" class="input" required>
            <option value="">- pilih treatment -</option>
            @foreach ($services as $s)
              <option value="{{ $s->id }}">[{{ ucfirst($s->category) }}] {{ $s->name }} — Rp {{ number_format($s->price, 0, ',', '.') }}</option>
            @endforeach
          </select>
        </div>
        <div class="md:col-span-4">
          <input :name="`items[${idx}][item_name]`" x-model="item.item_name" class="input" placeholder="Nama barang (mis. Nike AF1 putih)" required>
        </div>
        <div class="md:col-span-1">
          <input type="number" min="1" max="20" :name="`items[${idx}][qty]`" x-model="item.qty" class="input" required>
        </div>
        <div class="flex items-center text-sm font-medium md:col-span-2" x-text="'Rp ' + fmt(line(item))"></div>
        <div class="md:col-span-1">
          <button type="button" class="btn-danger" @click="items.splice(idx, 1)" x-show="items.length > 1">✕</button>
        </div>
      </div>
    </template>

    <button type="button" class="btn-light" @click="add()">+ Tambah treatment / barang</button>
  </div>

  {{-- Foto barang --}}
  <div class="card space-y-3">
    <div>
      <h3 class="font-bold">Foto barang</h3>
      <p class="text-xs text-neutral-500">
        Satu foto cukup jika barangnya sama dengan beberapa treatment. Jika barangnya berbeda, pilih beberapa foto sekaligus (maks 5 foto, 4 MB per foto).
      </p>
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
    <p x-show="previews.length" x-cloak class="text-xs text-neutral-500">
      <span x-text="previews.length"></span> foto dipilih. Pilih ulang untuk mengganti.
    </p>
  </div>

  <div class="card"><label class="label">Catatan (opsional)</label>
    <textarea name="notes" rows="2" class="input" placeholder="Mis. noda kopi di bagian depan.">{{ old('notes') }}</textarea></div>

  <div class="card space-y-1 text-sm">
    <div class="flex justify-between"><span>Subtotal</span><span x-text="'Rp ' + fmt(subtotal)"></span></div>
    <div class="flex justify-between text-red-600"><span>Diskon member ({{ $customer->tier }} <span x-text="pct"></span>%)</span><span x-text="'- Rp ' + fmt(discount)"></span></div>
    <div class="flex justify-between border-t pt-2 text-lg font-extrabold"><span>Estimasi total</span><span class="text-red-600" x-text="'Rp ' + fmt(total)"></span></div>
    <p class="text-xs text-neutral-500" x-show="days > 0">Estimasi selesai ±<span x-text="days"></span> hari setelah barang diterima.</p>
    <p class="pt-2 text-xs text-neutral-500">Harga final dikonfirmasi admin setelah kondisi barang dicek. Pembayaran dilakukan di kasir atau via QRIS.</p>
  </div>

  <button class="btn w-full py-3" :disabled="tooMany">Kirim Pesanan</button>
</form>
@endsection