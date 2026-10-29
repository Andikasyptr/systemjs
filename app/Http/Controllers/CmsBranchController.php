<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsBranchController extends Controller
{
    public function index()
    {
        return view('cms.branches', [
            'branches' => Branch::orderByDesc('is_main')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->rules($request);

        if ($request->hasFile('qris')) {
            $data['qris_path'] = $request->file('qris')->store('qris', 'public');
        }

        Branch::create($data);

        return back()->with('success', 'Cabang ditambahkan.');
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $this->rules($request);

        if ($request->hasFile('qris')) {
            if ($branch->qris_path) {
                Storage::disk('public')->delete($branch->qris_path);
            }
            $data['qris_path'] = $request->file('qris')->store('qris', 'public');
        } elseif ($request->boolean('remove_qris') && $branch->qris_path) {
            Storage::disk('public')->delete($branch->qris_path);
            $data['qris_path'] = null;
        }

        $branch->update($data);

        return back()->with('success', 'Cabang diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        if (Order::where('branch_id', $branch->id)->exists()) {
            return back()->withErrors(['branch' => 'Cabang ini sudah punya pesanan, tidak bisa dihapus.']);
        }

        if ($branch->qris_path) {
            Storage::disk('public')->delete($branch->qris_path);
        }
        $branch->delete();

        return back()->with('success', 'Cabang dihapus.');
    }

    private function rules(Request $request): array
    {
        $data = $request->validate([
            'name'      => 'required|max:100',
            'address'   => 'required|max:255',
            'phone'     => 'nullable|max:20',
            'whatsapp'  => 'nullable|max:20',
            'instagram' => 'nullable|max:50',
            'qris'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        unset($data['qris']); // file ditangani terpisah

        if (! empty($data['whatsapp'])) {
            $n = preg_replace('/\D/', '', $data['whatsapp']);
            if (str_starts_with($n, '0')) {
                $n = '62' . substr($n, 1);
            }
            $data['whatsapp'] = $n;
        }

        if (! empty($data['instagram'])) {
            $data['instagram'] = ltrim(basename(trim($data['instagram'])), '@');
        }

        return $data + ['is_main' => $request->boolean('is_main')];
    }
}