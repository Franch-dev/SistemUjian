<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     * @param string $roles Daftar role yang diizinkan dipisah tanda pipe (|), contoh: 'admin' atau 'guru|admin'
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => 'unauthenticated',
                'message' => 'Token tidak ditemukan atau sesi sudah kadaluarsa.',
            ], 401);
        }

        $allowedRoles = explode('|', $roles);
        $userRole = null;

        if ($user instanceof \App\Models\Admin) {
            $userRole = 'admin';
        } elseif ($user instanceof \App\Models\Guru) {
            $userRole = 'guru';
        } elseif ($user instanceof \App\Models\Murid) {
            $userRole = 'murid';
        }

        if (!in_array($userRole, $allowedRoles, true)) {
            return response()->json([
                'status'  => 'forbidden',
                'message' => "Akses ditolak: Anda tidak memiliki wewenang role [{$roles}].",
            ], 403);
        }

        return $next($request);
    }
}