<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return view('services.index', [
            'services' => Service::orderBy('category')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Service::create($request->validate([
            'name'          => 'required|max:100',
            'category'      => 'required|in:sepatu,tas,helm',
            'price'         => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:1',
            'description'   => 'nullable',
        ]));

        return back()->with('success', 'Layanan ditambahkan.');
    }

    public function update(Request $request, Service $service)
    {
        $service->update($request->validate([
            'name'          => 'required|max:100',
            'price'         => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:1',
        ]) + ['is_active' => $request->boolean('is_active')]);

        return back()->with('success', 'Layanan diperbarui.');
    }

    public function destroy(Service $service)
    {
        if (OrderItem::where('service_id', $service->id)->exists()) {
            $service->update(['is_active' => false]);
            return back()->with('success', 'Layanan sudah pernah dipakai, jadi hanya dinonaktifkan.');
        }

        $service->delete();
        return back()->with('success', 'Layanan dihapus.');
    }
}