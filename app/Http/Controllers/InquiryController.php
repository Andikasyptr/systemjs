<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index()
    {
        return view('inquiries.index', ['inquiries' => Inquiry::latest()->paginate(15)]);
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $inquiry->update($request->validate(['reply' => 'required']) + ['status' => 'dibalas']);
        return back()->with('success', 'Balasan disimpan. Klik tombol WA untuk mengirim.');
    }
}