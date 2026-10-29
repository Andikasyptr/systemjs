<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    private function target(): string
    {
        return auth()->user()->role === 'owner' ? 'admin' : 'kasir';
    }

    public function index()
    {
        $me = auth()->user();

        $users = User::with('branch')
            ->where('role', $this->target())
            ->when($me->role === 'admin' && $me->branch_id, fn ($q) => $q->where('branch_id', $me->branch_id))
            ->latest()->get();

        return view('staff.index', [
            'users'    => $users,
            'target'   => $this->target(),
            'branches' => Branch::orderByDesc('is_main')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $me = auth()->user();

        $data = $request->validate([
            'name'      => 'required|max:100',
            'email'     => 'required|email|unique:users',
            'phone'     => 'nullable|max:20',
            'password'  => 'required|min:8',
            'branch_id' => $me->role === 'owner' ? 'required|exists:branches,id' : 'nullable',
        ]);

        // Owner memilih cabang admin; kasir otomatis ikut cabang admin yang membuatnya
        $data['branch_id'] = $me->role === 'owner' ? $data['branch_id'] : $me->branch_id;

        User::create($data + [
            'role'              => $this->target(),
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'Akun ' . $this->target() . ' berhasil dibuat.');
    }

    public function toggle(User $user)
    {
        $me = auth()->user();

        abort_unless($user->role === $this->target(), 403);
        abort_if($me->role === 'admin' && $me->branch_id && $user->branch_id !== $me->branch_id, 403);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status akun diperbarui.');
    }
}