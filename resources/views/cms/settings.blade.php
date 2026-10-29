@extends('layouts.panel')
@section('title', 'Tampilan Website')
@section('content')
@include('cms._tabs')

<form method="POST" action="{{ route('cms.settings.update') }}" class="space-y-4">
  @csrf @method('PUT')

  @foreach ($groups as $group => $fields)
    <div class="card space-y-4">
      <h3 class="font-bold">{{ $group }}</h3>
      <div class="grid gap-4 md:grid-cols-2">
        @foreach ($fields as $key => [$label, $type, $default])
          <div class="{{ $type === 'textarea' ? 'md:col-span-2' : '' }}">
            @if ($type === 'toggle')
              <label class="flex items-center gap-2 pt-6 text-sm font-medium">
                <input type="checkbox" name="{{ $key }}" value="1" @checked($values[$key] == '1') class="rounded text-red-600 focus:ring-red-500">
                {{ $label }}
              </label>
            @else
              <label class="label" for="{{ $key }}">{{ $label }}</label>
              @if ($type === 'textarea')
                <textarea id="{{ $key }}" name="{{ $key }}" rows="3" class="input">{{ old($key, $values[$key]) }}</textarea>
              @else
                <input id="{{ $key }}" name="{{ $key }}" type="{{ $type === 'number' ? 'number' : 'text' }}"
                       value="{{ old($key, $values[$key]) }}" class="input">
              @endif
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @endforeach

  <div class="sticky bottom-4">
    <button class="btn shadow-lg">Simpan Perubahan</button>
  </div>
</form>
@endsection