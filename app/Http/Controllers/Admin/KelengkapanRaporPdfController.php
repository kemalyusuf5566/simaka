<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSiswa;
use App\Models\DataSekolah;
use App\Models\DataTahunPelajaran;
use Barryvdh\DomPDF\Facade\Pdf;

class KelengkapanRaporPdfController extends Controller
{
    /**
     * PDF KELENGKAPAN RAPOR
     * URL:
     * /admin/rapor/pdf/kelengkapan/{siswa}
     */
    public function show($siswaId)
    {
        // =========================
        // AMBIL DATA WAJIB
        // =========================
        $siswa = DataSiswa::with([
            'kelas',
            'kelas.wali.pengguna',
        ])->findOrFail($siswaId);

        $sekolah = DataSekolah::first();
        $tahun   = DataTahunPelajaran::where('status_aktif', 1)->first();

        // =========================
        // RENDER PDF (WAJIB)
        // =========================
        $pdf = Pdf::loadView(
            'admin.rapor.pdf.kelengkapan',
            compact('siswa', 'sekolah', 'tahun')
        )
            // F4 PORTRAIT (STABIL, TIDAK HANCUR)
            ->setPaper([0, 0, 609.45, 935.43], 'portrait');

        // =========================
        // OUTPUT PDF
        // =========================
        return $pdf->stream(
            'KELENGKAPAN_RAPOR_' . ($siswa->nama_siswa ?? 'SISWA') . '.pdf'
        );
    }
}
