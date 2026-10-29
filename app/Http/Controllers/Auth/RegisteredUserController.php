<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['phone' => Customer::normalizePhone($request->phone)]);

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'phone'    => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => 'customer', // dipaksa customer, tidak dari input
        ]);

        // Tautkan ke data pelanggan yang sudah diinput admin (nomor HP sama)
        $customer = Customer::where('phone', $request->phone)->whereNull('user_id')->first();

        if ($customer) {
            $customer->update(['user_id' => $user->id]);
        } else {
            Customer::create([
                'user_id'     => $user->id,
                'member_code' => Customer::generateCode(),
                'name'        => $user->name,
                'phone'       => $user->phone,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        // Kembali ke tujuan semula (mis. form pesan), bukan selalu dashboard
        return redirect()->intended(route('dashboard', absolute: false));
    }
}