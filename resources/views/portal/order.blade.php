@extends('layouts.panel')
@section('title', 'Pesan Cucian')
@section('content')

<form method="POST" action="{{ route('portal.order.store') }}" enctype="multipart/form-data" class="space-y-4"
  x-data="{
    services: {{ $services->map(fn ($s) => ['id' => $s->id, 'price' => $s->price, 'days' => $s->duration_days])->toJson() }},
    pct: {{ $customer->discount_percent }},
    items: [{ service_id: '{{ $selectedId ?? '' }}', item_name: '', qty: 1 }],
    previews: [],
    tooLarge: false,
    add() { this.items.push({ service_id: '', item_name: '', qty: 1 }) },
    find(id) { return this.services.find(s => s.id == id) },
    line(i) { const s = this.find(i.service_id); return s ? s.price * i.qty : 0 },
    get subtotal() { return this.items.reduce((t, i) => t + this.line(i), 0) },
    get discount() { return Math.floor(this.subtotal * this.pct / 100) },
    get total() { return this.subtotal - this.discount },
    get days() { return Math.max(2, ...this.items.map(i => this.find(i.service_id)?.days || 2)) },
    fmt(n) { return new Intl.NumberFormat('id-ID').format(n) },
    pick(e) {
      const file = e.target.files[0];
      if (!file) {
        this.previews = [];
        this.tooLarge = false;
        return;
      }
      
      // Batas maksimal 1 MB (1 * 1024 * 1024 bytes)
      if (file.size > 1 * 1024 * 1024) {
        this.tooLarge = true;
        e.target.value = ''; 
        this.previews = [];
        return;
      }

      this.tooLarge = false;
      this.previews = [URL.createObjectURL(file)];
    }
  }">
  @csrf

  {{-- 1. Cabang --}}
  <div class="card !p-4">
    <p class="mb-2 text-sm font-bold">Antar ke cabang</p>
    <select name="branch_id" class="input !py-3" required>
      @foreach ($branches as $b)
        <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>{{ $b->name }}</option>
      @endforeach
    </select>
    <p class="mt-2 text-xs text-neutral-500">Pesanan masuk ke admin cabang yang dipilih, dan pembayaran QRIS memakai QRIS cabang itu.</p>
  </div>

  {{-- 2. Treatment --}}
  <div class="space-y-3">
    <p class="text-sm font-bold">2. Treatment & barang</p>

    <template x-for="(item, idx) in items" :key="idx">
      <div class="card space-y-3 !p-4">
        <div class="flex items-center justify-between">
          <span class="text-xs font-bold uppercase tracking-wide text-neutral-500" x-text="'Barang ' + (idx + 1)"></span>
          <button type="button" @click="items.splice(idx, 1)" x-show="items.length > 1"
                  class="grid h-8 w-8 place-items-center rounded-full bg-neutral-100 text-neutral-600" aria-label="Hapus">✕</button>
        </div>

        <select :name="`items[${idx}][service_id]`" x-model="item.service_id" class="input !py-3" required>
          <option value="">Pilih treatment</option>
          @foreach ($services as $s)
            <option value="{{ $s->id }}">[{{ ucfirst($s->category) }}] {{ $s->name }} — Rp {{ number_format($s->price, 0, ',', '.') }}</option>
          @endforeach
        </select>

        <input :name="`items[${idx}][item_name]`" x-model="item.item_name" class="input !py-3" placeholder="Nama barang (mis. Nike AF1 putih)" required>

        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center rounded-xl border border-neutral-300">
            <button type="button" @click="item.qty = Math.max(1, item.qty - 1)" class="h-11 w-11 text-xl font-bold text-red-600">−</button>
            <span class="w-10 text-center font-semibold" x-text="item.qty"></span>
            <button type="button" @click="item.qty = Math.min(20, item.qty + 1)" class="h-11 w-11 text-xl font-bold text-red-600">+</button>
          </div>
          <input type="hidden" :name="`items[${idx}][qty]`" :value="item.qty">
          <p class="text-right text-base font-extrabold text-red-600" x-text="'Rp ' + fmt(line(item))"></p>
        </div>
      </div>
    </template>

    <button type="button" @click="add()" class="w-full rounded-2xl border-2 border-dashed border-neutral-300 py-3 text-sm font-semibold text-neutral-600 active:bg-neutral-50">+ Tambah treatment / barang</button>
  </div>

  {{-- 3. Foto --}}
  <div class="card space-y-3 !p-4">
    <div>
      <p class="text-sm font-bold">3. Foto barang</p>
      <p class="text-xs text-neutral-500">
        💡 <strong class="text-neutral-700">Cukup 1 foto saja!</strong> Letakkan dan kumpulkan semua barang dalam satu frame foto berdampingan (<strong class="text-red-600">maksimal 1 MB</strong>).
      </p>
    </div>

    <label class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-neutral-300 p-6 text-center active:bg-red-50">
      <span class="text-3xl">📷</span>
      <span class="text-sm font-semibold">Ambil / pilih foto kumpulan barang</span>
      <input type="file" name="photos[]" accept="image/*" class="sr-only" @change="pick($event)" required>
    </label>

    {{-- Pesan Error jika ukuran melebihi 1MB --}}
    <p x-show="tooLarge" x-cloak class="text-sm font-semibold text-red-600 bg-red-50 p-3 rounded-lg border border-red-200">
      ⚠️ Ukuran gambar melebihi 1 MB. Mohon kompres atau pilih foto lain yang ukurannya lebih kecil.
    </p>

    <div x-show="previews.length" x-cloak class="grid grid-cols-1 gap-2">
      <template x-for="(src, i) in previews" :key="i">
        <img :src="src" class="max-h-60 w-full rounded-lg border object-contain bg-neutral-50 p-1" alt="Pratinjau foto barang">
      </template>
    </div>
  </div>

  {{-- 4. Catatan --}}
  <div class="card !p-4">
    <p class="mb-2 text-sm font-bold">4. Catatan <span class="font-normal text-neutral-400">(opsional)</span></p>
    <textarea name="notes" rows="2" class="input" placeholder="Mis. noda kopi di bagian depan.">{{ old('notes') }}</textarea>
  </div>

  {{-- Ringkasan --}}
  <div class="card space-y-1 !p-4 text-sm">
    <div class="flex justify-between"><span>Subtotal</span><span x-text="'Rp ' + fmt(subtotal)"></span></div>
    <div class="flex justify-between text-red-600"><span>Diskon {{ $customer->tier }} (<span x-text="pct"></span>%)</span><span x-text="'- Rp ' + fmt(discount)"></span></div>
    <p class="pt-1 text-xs text-neutral-500" x-show="days > 0">Estimasi selesai ±<span x-text="days"></span> hari setelah barang diterima.</p>
    <p class="text-xs text-neutral-500">Harga final dikonfirmasi admin setelah kondisi barang dicek.</p>
  </div>

  {{-- Bar bawah: total + kirim --}}
  <div class="sticky bottom-[4.5rem] z-20 -mx-4 border-t border-neutral-200 bg-white/95 p-3 backdrop-blur sm:mx-0 sm:rounded-2xl sm:border lg:bottom-4">
    <div class="mx-auto flex max-w-3xl items-center gap-3">
      <div class="min-w-0 flex-1">
        <p class="text-[11px] uppercase text-neutral-500">Estimasi total</p>
        <p class="truncate text-xl font-black text-red-600" x-text="'Rp ' + fmt(total)"></p>
      </div>
      <button class="btn shrink-0 !px-6 !py-3 text-base disabled:opacity-50" :disabled="subtotal === 0 || tooLarge">Kirim Pesanan</button>
    </div>
  </div>
</form>
@endsection