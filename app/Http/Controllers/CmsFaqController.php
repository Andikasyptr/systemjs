<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class CmsFaqController extends Controller
{
    public function index()
    {
        return view('cms.faqs', [
            'faqs' => Faq::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Faq::create($this->rules($request));
        return back()->with('success', 'FAQ ditambahkan.');
    }

    public function update(Request $request, Faq $faq)
    {
        $faq->update($this->rules($request));
        return back()->with('success', 'FAQ diperbarui.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return back()->with('success', 'FAQ dihapus.');
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'question'   => 'required|max:255',
            'answer'     => 'required|max:2000',
            'sort_order' => 'required|integer|min:0',
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}