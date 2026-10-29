<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Clean Shoes Jejak Suci')</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-800">
  <header class="sticky top-0 z-30 border-b bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
      <a href="{{ route('home') }}" class="text-lg font-bold text-teal-700">👟 Clean Shoes Jejak Suci</a>
      <nav class="flex items-center gap-4 text-sm font-medium">
        <a href="{{ route('home') }}#layanan" class="hidden hover:text-teal-700 sm:inline">Layanan</a>
        <a href="{{ route('home') }}#member" class="hidden hover:text-teal-700 sm:inline">Membership</a>
        <a href="{{ route('track') }}" class="hover:text-teal-700">Cek Pesanan</a>
        @auth
          <a href="{{ route('dashboard') }}" class="btn">Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="hover:text-teal-700">Masuk</a>
          <a href="{{ route('register') }}" class="btn">Daftar Member</a>
        @endauth
      </nav>
    </div>
  </header>
  @yield('content')
  <footer class="mt-16 bg-teal-950 py-8 text-center text-sm text-teal-200">
    © {{ date('Y') }} Clean Shoes Jejak Suci · Jonggol & Cikarang
  </footer>
</body>
</html>