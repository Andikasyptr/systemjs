<?php

namespace App\Http\Controllers;

use App\Models\Feature;
use Illuminate\Http\Request;

class CmsFeatureController extends Controller
{
    public function index()
    {
        return view('cms.features', [
            'features' => Feature::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Feature::create($this->rules($request));
        return back()->with('success', 'Keunggulan ditambahkan.');
    }

    public function update(Request $request, Feature $feature)
    {
        $feature->update($this->rules($request));
        return back()->with('success', 'Keunggulan diperbarui.');
    }

    public function destroy(Feature $feature)
    {
        $feature->delete();
        return back()->with('success', 'Keunggulan dihapus.');
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'icon'        => 'required|max:10',
            'title'       => 'required|max:100',
            'description' => 'required|max:255',
            'sort_order'  => 'required|integer|min:0',
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}