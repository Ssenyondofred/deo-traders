<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    // Ganti pengecekan menggunakan method isAdmin()
    if (!auth()->user()->isAdmin()) {
        abort(403, 'Akses ditolak. Hanya Admin yang dapat mengakses halaman ini.');
    }

    return $next($request);
}
}