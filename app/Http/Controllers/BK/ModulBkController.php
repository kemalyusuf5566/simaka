<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\DataBk;
use Illuminate\View\View;

class ModulBkController extends Controller
{
    public function dashboard(): View
    {
        $statusOptions = DataBk::statusOptions();
        $statusCounts = [];

        foreach ($statusOptions as $status) {
            $statusCounts[$status] = DataBk::where('status', $status)->count();
        }

        return view('bk.dashboard', [
            'totalKasus' => DataBk::count(),
            'statusCounts' => $statusCounts,
            'modul' => $this->modules(),
        ]);
    }

    public function sikap(): View
    {
        return $this->renderModule('sikap');
    }

    public function pelanggaran(): View
    {
        return $this->renderModule('pelanggaran');
    }

    public function pembinaan(): View
    {
        return $this->renderModule('pembinaan');
    }

    public function homeVisit(): View
    {
        return $this->renderModule('home_visit');
    }

    public function pengunduranDiri(): View
    {
        return $this->renderModule('pengunduran_diri');
    }

    public function perjanjianSiswa(): View
    {
        return $this->renderModule('perjanjian_siswa');
    }

    public function peminatanSiswa(): View
    {
        return $this->renderModule('peminatan_siswa');
    }

    public function absensiBulanan(): View
    {
        return $this->renderModule('absensi_bulanan');
    }

    public function pemanggilanOrangTua(): View
    {
        return $this->renderModule('pemanggilan_ortu');
    }

    private function renderModule(string $key): View
    {
        $modules = $this->modules();
        abort_unless(isset($modules[$key]), 404);

        return view('bk.modul', [
            'modulKey' => $key,
            'modul' => $modules[$key],
        ]);
    }

    private function modules(): array
    {
        return [
            'sikap' => [
                'title' => 'Sikap Siswa',
                'desc' => 'Penilaian sikap, catatan perkembangan sikap, dan rekap per periode.',
                'scope' => [
                    'Input indikator sikap',
                    'Riwayat perubahan sikap per siswa',
                    'Rekap sikap per kelas',
                ],
            ],
            'pelanggaran' => [
                'title' => 'Daftar Pelanggaran',
                'desc' => 'Pencatatan pelanggaran siswa beserta poin dan tindak lanjut awal.',
                'scope' => [
                    'Daftar jenis pelanggaran',
                    'Input kejadian pelanggaran',
                    'Akumulasi poin pelanggaran',
                ],
            ],
            'pembinaan' => [
                'title' => 'Laporan Pembinaan Siswa',
                'desc' => 'Dokumentasi proses pembinaan dari awal sampai tindak lanjut.',
                'scope' => [
                    'Rencana pembinaan',
                    'Catatan sesi pembinaan',
                    'Status hasil pembinaan',
                ],
            ],
            'home_visit' => [
                'title' => 'Laporan Home Visit',
                'desc' => 'Laporan kunjungan rumah dan hasil observasi BK.',
                'scope' => [
                    'Data kunjungan',
                    'Ringkasan kondisi siswa',
                    'Rekomendasi tindak lanjut',
                ],
            ],
            'pengunduran_diri' => [
                'title' => 'Laporan Pengunduran Diri',
                'desc' => 'Administrasi dan riwayat pengunduran diri siswa.',
                'scope' => [
                    'Data pengajuan',
                    'Status verifikasi',
                    'Arsip dokumen pengunduran diri',
                ],
            ],
            'perjanjian_siswa' => [
                'title' => 'Perjanjian Siswa',
                'desc' => 'Perjanjian siswa/orang tua sebagai komitmen tindak lanjut pembinaan.',
                'scope' => [
                    'Template perjanjian',
                    'Riwayat penandatanganan',
                    'Monitoring kepatuhan',
                ],
            ],
            'peminatan_siswa' => [
                'title' => 'Peminatan Siswa',
                'desc' => 'Pemetaan minat dan rekomendasi peminatan siswa.',
                'scope' => [
                    'Data minat siswa',
                    'Riwayat konseling peminatan',
                    'Ringkasan rekomendasi',
                ],
            ],
            'absensi_bulanan' => [
                'title' => 'Absensi Bulanan',
                'desc' => 'Rekap absensi bulanan untuk monitoring BK (integrasi data absensi).',
                'scope' => [
                    'Rekap kehadiran bulanan',
                    'Deteksi siswa dengan ketidakhadiran tinggi',
                    'Aksi tindak lanjut BK',
                ],
            ],
            'pemanggilan_ortu' => [
                'title' => 'Laporan Pemanggilan Orang Tua',
                'desc' => 'Dokumentasi pemanggilan orang tua dan hasil pertemuan.',
                'scope' => [
                    'Surat panggilan',
                    'Jadwal pertemuan',
                    'Notulen hasil pembahasan',
                ],
            ],
        ];
    }
}

