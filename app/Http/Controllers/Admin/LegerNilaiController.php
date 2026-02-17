<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\NilaiMapelSiswa;
use App\Models\DataMapel;
use Illuminate\Http\Request;

class LegerNilaiController extends Controller
{
    /**
     * INDEX – daftar kelas (dipakai view langkah 4)
     */
    public function index()
    {
        $kelas = DataKelas::withCount('siswa')
            ->with(['wali.pengguna'])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.rapor.leger.index', compact('kelas'));
    }

    /**
     * DETAIL LEGER PER KELAS
     */
    public function detail($kelasId)
    {
        $kelas = DataKelas::with(['wali.pengguna'])->findOrFail($kelasId);

        // semua siswa di kelas
        $siswa = DataSiswa::where('data_kelas_id', $kelasId)
            ->orderBy('nama_siswa')
            ->get();

        // semua mapel yang ada nilainya di kelas tsb
        $mapel = DataMapel::whereIn(
            'id',
            NilaiMapelSiswa::where('data_kelas_id', $kelasId)
                ->pluck('data_mapel_id')
                ->unique()
        )->orderBy('nama_mapel')->get();

        // nilai: [siswa_id][mapel_id]
        $nilai = NilaiMapelSiswa::where('data_kelas_id', $kelasId)->get()
            ->groupBy(['data_siswa_id', 'data_mapel_id']);

        return view('admin.rapor.leger.detail', compact(
            'kelas',
            'siswa',
            'mapel',
            'nilai'
        ));
    }
}
