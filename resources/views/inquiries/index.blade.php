@extends('layouts.panel')
@section('title', 'Pertanyaan Pelanggan')
@section('content')
@foreach ($inquiries as $q)
  <div class="card space-y-3">
    <div class="flex justify-between">
      <div><p class="font-semibold">{{ $q->name }} · {{ $q->phone }}</p><p class="text-xs text-slate-500">{{ $q->created_at->diffForHumans() }}</p></div>
      <span class="badge {{ $q->status === 'baru' ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">{{ $q->status }}</span>
    </div>
    <p class="rounded-lg bg-slate-50 p-3 text-sm">{{ $q->message }}</p>
    <form method="POST" action="{{ route('inquiries.update', $q) }}" class="space-y-2">@csrf @method('PATCH')
      <textarea name="reply" rows="2" class="input" placeholder="Tulis balasan...">{{ $q->reply }}</textarea>
      <div class="flex gap-2">
        <button class="btn">Simpan Balasan</button>
        @if ($q->reply)<a target="_blank" class="btn-wa" href="{{ \App\Support\Wa::link($q->phone, $q->reply) }}">Kirim via WA</a>@endif
      </div>
    </form>
  </div>
@endforeach
{{ $inquiries->links() }}
@endsection