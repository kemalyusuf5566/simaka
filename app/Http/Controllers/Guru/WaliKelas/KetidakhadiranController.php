<?php

namespace App\Http\Controllers\Guru\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\DataKelas;
use App\Models\DataKetidakhadiran;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KetidakhadiranController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $kelas = DataKelas::withCount('siswa')
            ->where('wali_kelas_id', $user->id)
            ->get();

        // ✅ KUNCI: nama wali ambil dari user login (kolom: nama)
        $namaWali = $user->nama ?? '-';

        return view('guru.wali_kelas.ketidakhadiran.index', compact('kelas', 'namaWali'));
    }

    public function kelola($kelasId)
    {
        $user = Auth::user();

        $kelas = DataKelas::where('id', $kelasId)
            ->where('wali_kelas_id', $user->id)
            ->firstOrFail();

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        $siswa = DataSiswa::where('data_kelas_id', $kelas->id)->get();

        $data = DataKetidakhadiran::where('data_tahun_pelajaran_id', $tahunAktif->id)
            ->where('semester', $tahunAktif->semester)
            ->whereIn('data_siswa_id', $siswa->pluck('id'))
            ->get()
            ->keyBy('data_siswa_id');

        // ✅ KUNCI: nama wali ambil dari user login (kolom: nama)
        $namaWali = $user->nama ?? '-';

        return view('guru.wali_kelas.ketidakhadiran.kelola', compact(
            'kelas',
            'tahunAktif',
            'siswa',
            'data',
            'namaWali'
        ));
    }

    public function update(Request $request, $kelasId)
    {
        $user = Auth::user();

        $kelas = DataKelas::where('id', $kelasId)
            ->where('wali_kelas_id', $user->id)
            ->firstOrFail();

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        $sakit = $request->input('sakit', []);
        $izin  = $request->input('izin', []);
        $tk    = $request->input('tanpa_keterangan', []);

        foreach ($sakit as $siswaId => $val) {
            DataKetidakhadiran::updateOrCreate(
                [
                    'data_siswa_id' => $siswaId,
                    'data_tahun_pelajaran_id' => $tahunAktif->id,
                    'semester' => $tahunAktif->semester,
                ],
                [
                    'sakit' => (int)($val ?? 0),
                    'izin' => (int)($izin[$siswaId] ?? 0),
                    'tanpa_keterangan' => (int)($tk[$siswaId] ?? 0),
                ]
            );
        }

        return back()->with('success', 'Ketidakhadiran berhasil disimpan.');
    }
}
