@extends('layouts.panel')
@section('title', $customer ? 'Edit Pelanggan' : 'Pelanggan Baru')
@section('content')
<form method="POST" action="{{ $customer ? route('customers.update', $customer) : route('customers.store') }}" class="card max-w-xl space-y-4">
  @csrf @if ($customer) @method('PUT') @endif
  <div><label class="label">Nama</label><input name="name" class="input" value="{{ old('name', $customer->name ?? '') }}" required></div>
  <div><label class="label">No. HP / WhatsApp</label><input name="phone" class="input" value="{{ old('phone', $customer->phone ?? '') }}" required></div>
  <div><label class="label">Alamat</label><textarea name="address" rows="3" class="input">{{ old('address', $customer->address ?? '') }}</textarea></div>
  <div><label class="label">Cabang</label>
    <select name="branch_id" class="input"><option value="">-</option>
      @foreach ($branches as $b)<option value="{{ $b->id }}" @selected(old('branch_id', $customer->branch_id ?? '') == $b->id)>{{ $b->name }}</option>@endforeach
    </select></div>
  <div class="flex gap-2"><button class="btn">Simpan</button><a href="{{ route('customers.index') }}" class="btn-light">Batal</a></div>
</form>
@endsection