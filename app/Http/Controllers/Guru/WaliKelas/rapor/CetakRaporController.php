<?php

namespace App\Http\Controllers\Guru\WaliKelas\Rapor;

use App\Http\Controllers\Controller;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Support\Facades\Auth;

class CetakRaporController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Ambil kelas yang wali-nya adalah user login (sesuai relasi kamu: kelas.wali.pengguna)
        $kelas = DataKelas::with(['wali.pengguna'])
            ->whereHas('wali', function ($q) use ($userId) {
                $q->where('pengguna_id', $userId);
            })
            ->first();

        if (!$kelas) {
            abort(403, 'Anda tidak ditetapkan sebagai Wali Kelas.');
        }

        // Tahun + semester aktif (buat dropdown/info di view)
        $tahun = DataTahunPelajaran::where('status_aktif', 1)->first();
        $semester = $tahun?->semester ?? 'Ganjil';

        return view('guru.wali_kelas.rapor.cetak.index', compact('kelas', 'tahun', 'semester'));
    }

    public function detail($kelasId)
    {
        $userId = Auth::id();

        // Kunci kelas: hanya kelas wali kelas sendiri yang boleh dibuka
        $kelas = DataKelas::with(['wali.pengguna'])
            ->where('id', $kelasId)
            ->whereHas('wali', function ($q) use ($userId) {
                $q->where('pengguna_id', $userId);
            })
            ->firstOrFail();

        $siswa = DataSiswa::where('data_kelas_id', $kelas->id)
            ->orderBy('nama_siswa')
            ->get();

        $tahun = DataTahunPelajaran::where('status_aktif', 1)->first();
        $semester = $tahun?->semester ?? 'Ganjil';

        return view('guru.wali_kelas.rapor.cetak.detail', compact('kelas', 'siswa', 'tahun', 'semester'));
    }
}
