<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
  public function handle(Request $request, Closure $next, string ...$roles)
{
    $user = $request->user();

    if (!$user || !$user->is_active) {
        auth()->logout();
        return redirect()->route('login')->withErrors(['email' => 'Akun nonaktif.']);
    }

    abort_unless(in_array($user->role, $roles), 403, 'Anda tidak punya akses.');

    return $next($request);
}
}
