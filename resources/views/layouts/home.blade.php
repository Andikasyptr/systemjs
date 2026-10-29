@extends('layouts.public')
@section('content')
<section class="bg-gradient-to-br from-teal-700 to-teal-900 py-20 text-center text-white">
  <div class="mx-auto max-w-3xl px-4">
    <h1 class="text-4xl font-extrabold md:text-5xl">Sepatu, Tas & Helm Bersih Seperti Baru</h1>
    <p class="mt-4 text-lg text-teal-100">Jasa cuci profesional di Jonggol dan Cikarang. Daftar member, kumpulkan poin, dapatkan diskon.</p>
    <div class="mt-8 flex justify-center gap-3">
      <a href="#layanan" class="rounded-lg bg-white px-6 py-3 font-semibold text-teal-800 hover:bg-teal-50">Lihat Layanan</a>
      <a href="{{ route('register') }}" class="rounded-lg border border-white px-6 py-3 font-semibold hover:bg-white/10">Daftar Member</a>
    </div>
  </div>
</section>

<section id="layanan" class="mx-auto max-w-6xl px-4 py-16">
  <h2 class="text-center text-3xl font-bold">Layanan & Harga</h2>
  <div class="mt-10 grid gap-6 md:grid-cols-3">
    @foreach (['sepatu' => '👟', 'tas' => '👜', 'helm' => '⛑️'] as $cat => $icon)
      <div class="card">
        <h3 class="text-xl font-semibold capitalize">{{ $icon }} Cuci {{ $cat }}</h3>
        <ul class="mt-4 divide-y">
          @forelse ($services[$cat] ?? [] as $s)
            <li class="flex items-center justify-between py-3 text-sm">
              <span>{{ $s->name }}<br><small class="text-slate-500">±{{ $s->duration_days }} hari</small></span>
              <b class="text-teal-700">Rp {{ number_format($s->price, 0, ',', '.') }}</b>
            </li>
          @empty
            <li class="py-3 text-sm text-slate-500">Segera hadir.</li>
          @endforelse
        </ul>
      </div>
    @endforeach
  </div>
</section>

<section id="member" class="bg-teal-50 py-16">
  <div class="mx-auto max-w-5xl px-4 text-center">
    <h2 class="text-3xl font-bold">Membership Jejak Suci</h2>
    <div class="mt-8 grid gap-4 md:grid-cols-3">
      <div class="card"><p class="text-lg font-bold">Reguler</p><p class="mt-2 text-sm text-slate-600">Gratis daftar. Kumpulkan poin tiap transaksi.</p></div>
      <div class="card ring-2 ring-slate-400"><p class="text-lg font-bold">Silver</p><p class="mt-2 text-sm text-slate-600">Total belanja Rp 500.000 · Diskon 5%</p></div>
      <div class="card ring-2 ring-amber-400"><p class="text-lg font-bold">Gold</p><p class="mt-2 text-sm text-slate-600">Total belanja Rp 1.500.000 · Diskon 10%</p></div>
    </div>
  </div>
</section>

<section class="mx-auto grid max-w-6xl gap-8 px-4 py-16 md:grid-cols-2">
  <div>
    <h2 class="text-3xl font-bold">Cabang Kami</h2>
    <div class="mt-6 space-y-4">
      @foreach ($branches as $b)
        <div class="card"><p class="font-semibold">{{ $b->name }}</p><p class="text-sm text-slate-600">{{ $b->address }}</p></div>
      @endforeach
    </div>
  </div>
  <div id="kontak">
    <h2 class="text-3xl font-bold">Ada Pertanyaan?</h2>
    @if (session('success'))
      <p class="mt-4 rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('success') }}</p>
    @endif
    <form method="POST" action="{{ route('inquiry.store') }}" class="card mt-6 space-y-4">
      @csrf
      <div><label class="label">Nama</label><input name="name" class="input" value="{{ old('name') }}" required></div>
      <div><label class="label">No. WhatsApp</label><input name="phone" class="input" value="{{ old('phone') }}" required></div>
      <div><label class="label">Pertanyaan</label><textarea name="message" rows="4" class="input" required>{{ old('message') }}</textarea></div>
      @if ($errors->any())<p class="text-sm text-rose-600">{{ $errors->first() }}</p>@endif
      <button class="btn w-full">Kirim</button>
    </form>
  </div>
</section>
@endsection