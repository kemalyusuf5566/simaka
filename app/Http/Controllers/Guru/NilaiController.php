<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DataSiswa;
use App\Models\DataKelas;
use App\Models\DataMapel;
use App\Models\DataTahunPelajaran;
use App\Models\NilaiMapelSiswa;

class NilaiController extends Controller
{
    /**
     * Tampilkan form input nilai per kelas & mapel
     * Guru masuk ke sini
     */
    public function index($pembelajaranId)
    {
        $pembelajaran = \App\Models\DataPembelajaran::with(['kelas', 'mapel'])
            ->findOrFail($pembelajaranId);

        $kelas = $pembelajaran->kelas;
        $mapel = $pembelajaran->mapel;

        $tahunAktif = \App\Models\DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        $siswa = \App\Models\DataSiswa::where('data_kelas_id', $kelas->id)
            ->orderBy('nama_siswa')
            ->get();

        // bagian query nilai kamu LANJUTKAN PERSIS seperti sebelumnya
        // cuma ganti pakai $kelas->id dan $mapel->id
    }


    /**
     * Simpan / update nilai siswa
     */
    public function store(Request $request)
    {
        $request->validate([
            'data_kelas_id' => 'required|exists:data_kelas,id',
            'data_mapel_id' => 'required|exists:data_mapel,id',
            'data_tahun_pelajaran_id' => 'required|exists:data_tahun_pelajaran,id',
            'semester' => 'required|in:Ganjil,Genap',
            'nilai' => 'required|array',
        ]);

        foreach ($request->nilai as $siswaId => $row) {

            NilaiMapelSiswa::updateOrCreate(
                [
                    'data_siswa_id' => $siswaId,
                    'data_mapel_id' => $request->data_mapel_id,
                    'data_tahun_pelajaran_id' => $request->data_tahun_pelajaran_id,
                    'semester' => $request->semester,
                ],
                [
                    'data_kelas_id' => $request->data_kelas_id,
                    'nilai_angka' => $row['nilai_angka'] ?? null,
                    'predikat' => $row['predikat'] ?? null,
                    'deskripsi' => $row['deskripsi'] ?? null,
                ]
            );
        }

        return redirect()
            ->back()
            ->with('success', 'Nilai berhasil disimpan');
    }
}
