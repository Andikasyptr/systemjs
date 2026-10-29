<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsGalleryController extends Controller
{
    public function index()
    {
        return view('cms.gallery', [
            'items' => GalleryItem::orderBy('sort_order')->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|max:100',
            'caption'    => 'nullable|max:255',
            'category'   => 'nullable|in:sepatu,tas,helm',
            'sort_order' => 'required|integer|min:0',
            'image'      => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $data['image_path'] = $request->file('image')->store('gallery', 'public');
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');

        GalleryItem::create($data);
        return back()->with('success', 'Foto ditambahkan ke galeri.');
    }

    public function update(Request $request, GalleryItem $gallery)
    {
        $data = $request->validate([
            'title'      => 'required|max:100',
            'caption'    => 'nullable|max:255',
            'category'   => 'nullable|in:sepatu,tas,helm',
            'sort_order' => 'required|integer|min:0',
            'image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($gallery->image_path);
            $data['image_path'] = $request->file('image')->store('gallery', 'public');
        }
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');

        $gallery->update($data);
        return back()->with('success', 'Galeri diperbarui.');
    }

    public function destroy(GalleryItem $gallery)
    {
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();
        return back()->with('success', 'Foto dihapus.');
    }
}