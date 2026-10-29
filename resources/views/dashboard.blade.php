@extends('layouts.panel')
@section('title', 'Dashboard')
@section('content')
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
  @foreach ($stats as $label => $value)
    <div class="card">
      <p class="text-xs font-medium uppercase text-slate-500">{{ $label }}</p>
      <p class="mt-2 text-2xl font-bold text-teal-700">{{ $value }}</p>
    </div>
  @endforeach
</div>
@endsection