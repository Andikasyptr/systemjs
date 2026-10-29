<div class="flex flex-wrap items-center justify-between gap-3">
  <div class="inline-flex flex-wrap gap-1 rounded-xl bg-white p-1 ring-1 ring-neutral-200">
    @foreach ([
      ['Teks & Kontak', 'cms.settings',       'cms.settings*'],
      ['Cabang',        'cms.branches.index', 'cms.branches.*'],
      ['Keunggulan',    'cms.features.index', 'cms.features.*'],
      ['Galeri',        'cms.gallery.index',  'cms.gallery.*'],
      ['FAQ',           'cms.faqs.index',     'cms.faqs.*'],
    ] as [$label, $route, $pattern])
      <a href="{{ route($route) }}"
         class="rounded-lg px-4 py-2 text-sm font-semibold {{ request()->routeIs($pattern) ? 'bg-red-600 text-white' : 'text-neutral-600 hover:bg-neutral-100' }}">{{ $label }}</a>
    @endforeach
  </div>
  <a href="{{ route('home') }}" target="_blank" class="btn-light">Lihat Website ↗</a>
</div>