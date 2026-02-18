<?php

namespace App\Http\Controllers\Guru\WaliKelas\Rapor;

use App\Http\Controllers\Controller;
use App\Models\DataSekolah;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class RaporKelengkapanPdfController extends Controller
{
    public function show($siswaId)
    {
        $userId = Auth::id();

        $siswa = DataSiswa::with(['kelas.wali.pengguna'])
            ->findOrFail($siswaId);

        // Kunci akses: siswa harus berada di kelas wali kelas yang login
        $isWali = optional($siswa->kelas?->wali)->pengguna_id === $userId;
        if (!$isWali) abort(403, 'Anda tidak berhak mencetak rapor siswa ini.');

        $sekolah = DataSekolah::first();
        $tahun   = DataTahunPelajaran::where('status_aktif', 1)->first();

        // Pakai blade admin (persis)
        $pdf = Pdf::loadView('admin.rapor.pdf.kelengkapan', compact('siswa', 'sekolah', 'tahun'))
            ->setPaper('A4');

        return $pdf->stream('KELENGKAPAN_RAPOR_' . $siswa->nama_siswa . '.pdf');
    }
}
