<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    private function target(): string
    {
        return auth()->user()->role === 'owner' ? 'admin' : 'kasir';
    }

    // Pastikan akun yang diubah sesuai jenis dan cabang pengelola
    private function guard(User $user): void
    {
        $scope = auth()->user()->branchScope();

        abort_unless($user->role === $this->target(), 403);
        abort_if($scope !== null && (int) $user->branch_id !== $scope, 403);
    }

    public function index()
    {
        $scope = auth()->user()->branchScope();

        return view('staff.index', [
            'users'    => User::with('branch')
                ->where('role', $this->target())
                ->when($scope !== null, fn ($q) => $q->where('branch_id', $scope))
                ->latest()->get(),
            'target'   => $this->target(),
            'branches' => $scope !== null
                ? Branch::where('id', $scope)->get()
                : Branch::orderByDesc('is_main')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $me    = auth()->user();
        $scope = $me->branchScope();

        $data = $request->validate([
            'name'      => 'required|max:100',
            'email'     => 'required|email|unique:users',
            'phone'     => 'nullable|max:20',
            'password'  => 'required|min:8',
            'branch_id' => $me->role === 'owner' ? 'required|exists:branches,id' : 'nullable',
        ]);

        if ($scope !== null) {
            $data['branch_id'] = $scope;
        }

        User::create($data + [
            'role'              => $this->target(),
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        return back()->with('success', 'Akun ' . $this->target() . ' berhasil dibuat.');
    }

    public function update(Request $request, User $user)
    {
        $this->guard($user);

        $me    = auth()->user();
        $scope = $me->branchScope();

        $data = $request->validate([
            'name'      => 'required|max:100',
            'email'     => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone'     => 'nullable|max:20',
            'password'  => 'nullable|min:8',
            'branch_id' => $me->role === 'owner' ? 'required|exists:branches,id' : 'nullable',
        ]);

        if ($scope !== null) {
            $data['branch_id'] = $scope;
        }

        // Password kosong = tidak diganti
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('success', 'Akun diperbarui.');
    }

    public function toggle(User $user)
    {
        $this->guard($user);

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status akun diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->guard($user);

        // Akun yang punya riwayat transaksi tidak dihapus agar pembukuan tetap utuh
        if (Order::where('created_by', $user->id)->exists() || Payment::where('received_by', $user->id)->exists()) {
            return back()->withErrors(['staff' => 'Akun ini sudah punya riwayat pesanan/pembayaran, tidak bisa dihapus. Nonaktifkan saja.']);
        }

        $user->delete();

        return back()->with('success', 'Akun dihapus.');
    }
}