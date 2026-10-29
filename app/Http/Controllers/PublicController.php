<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Faq;
use App\Models\Feature;
use App\Models\GalleryItem;
use App\Models\Inquiry;
use App\Models\Order;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        return view('welcome', [
            'site'     => Setting::forSite(),
            'services' => Service::where('is_active', true)->orderBy('price')->get()->groupBy('category'),
            'branches' => Branch::orderByDesc('is_main')->orderBy('id')->get(),
            'features' => Feature::where('is_active', true)->orderBy('sort_order')->get(),
            'faqs'     => Faq::where('is_active', true)->orderBy('sort_order')->get(),
            'gallery'  => GalleryItem::where('is_active', true)->orderBy('sort_order')->orderByDesc('id')->get(),
        ]);
    }

    public function storeInquiry(Request $request)
    {
        Inquiry::create($request->validate([
            'name'    => 'required|max:100',
            'phone'   => 'required|max:20',
            'message' => 'required|max:1000',
        ]));

        return back()->with('success', 'Terima kasih! Pertanyaan Anda sudah kami terima.');
    }

    public function track(Request $request)
    {
        $order = null;

        if ($request->filled(['invoice', 'phone'])) {
            $order = Order::with('items', 'customer', 'branch')
                ->where('invoice_no', $request->invoice)
                ->whereHas('customer', fn ($q) => $q->where('phone', $request->phone))
                ->first();
        }

        return view('track', compact('order'));
    }
}