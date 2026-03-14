<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Middleware role berbasis tabel `peran` dan `pengguna`.
     *
     * Pemakaian di route:
     * - middleware(['auth', 'role:admin'])
     * - middleware(['auth', 'role:guru_mapel'])
     * - middleware(['auth', 'role:wali_kelas'])
     * - middleware(['auth', 'role:pembina_ekskul'])
     * - middleware(['auth', 'role:koordinator_p5'])
     *
     * Catatan:
     * - Role utama diambil dari pengguna->peran->nama_peran
     * - Jika nanti pakai multi-role (pengguna_peran), tinggal aktifkan blok tambahan di bawah.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        // Pastikan relasi peran ada
        $userRole = $user->peran->nama_peran ?? null;

        // ===== 1) CEK ROLE UTAMA =====
        if ($userRole === $role) {
            return $next($request);
        }

        // Cek role tambahan jika model User menyediakan helper hasRole()
        if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
            return $next($request);
        }

        // ===== 2) CEK MULTI-ROLE (OPSIONAL) =====
        // Jika kamu sudah pakai pivot `pengguna_peran` untuk role tambahan,
        // pastikan Model User/Pengguna punya relasi peranTambahan():
        // public function peranTambahan() { return $this->belongsToMany(Peran::class, 'pengguna_peran', 'pengguna_id', 'peran_id'); }
        //
        // Lalu aktifkan blok ini:
        //
        // try {
        //     if (method_exists($user, 'peranTambahan')) {
        //         $hasExtraRole = $user->peranTambahan()->where('nama_peran', $role)->exists();
        //         if ($hasExtraRole) {
        //             return $next($request);
        //         }
        //     }
        // } catch (\Throwable $e) {
        //     // kalau relasi belum siap, abaikan
        // }

        if ($request->expectsJson()) {
            abort(403, 'Anda tidak memiliki akses untuk halaman ini.');
        }

        return redirect()
            ->route('dashboard')
            ->with('error', 'Akses Anda ke modul ini sudah tidak tersedia.');
    }
}
