<?php

namespace App\Http\Controllers\Guru\WaliKelas\Rapor;

use App\Http\Controllers\Controller;
use App\Models\DataKelas;
use App\Models\DataMapel;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use App\Models\NilaiMapelSiswa;
use Illuminate\Support\Facades\Auth;

class LegerNilaiController extends Controller
{
    /**
     * INDEX: tabel kelas (seperti screenshot: Nama Kelas, Wali Kelas, Tingkat, Jumlah Siswa, Aksi Detail)
     */
    public function index()
    {
        $user = Auth::user();

        // List kelas yang diwalikan (pagination biar UI "asli")
        $kelas = DataKelas::withCount('siswa')
            ->with(['wali.pengguna']) // aman karena relasi sudah ada di model DataKelas
            ->where('wali_kelas_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('guru.wali_kelas.rapor.leger.index', compact('kelas'));
    }

    /**
     * DETAIL: tabel leger lengkap per kelas + tombol Excel/PDF (UI)
     */
    public function detail($kelasId)
    {
        $user = Auth::user();

        // Kelas harus kelas yang diwalikan user
        $kelas = DataKelas::withCount('siswa')
            ->with(['wali.pengguna'])
            ->where('id', $kelasId)
            ->where('wali_kelas_id', $user->id)
            ->firstOrFail();

        // Tahun pelajaran aktif + semester aktif
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        // Semua mapel (11 mapel)
        $mapel = DataMapel::orderBy('id')->get();

        // Semua siswa di kelas ini
        $siswa = DataSiswa::where('data_kelas_id', $kelas->id)
            ->orderBy('nama_siswa')
            ->get();

        // Ambil nilai untuk kelas ini, tahun aktif, semester aktif
        // key: data_siswa_id -> data_mapel_id -> nilai_angka
        $nilai = NilaiMapelSiswa::where('data_kelas_id', $kelas->id)
            ->where('data_tahun_pelajaran_id', $tahunAktif->id)
            ->where('semester', $tahunAktif->semester)
            ->get()
            ->groupBy('data_siswa_id');

        // Build rows: nilai per mapel + total + rata + ranking
        $rows = [];
        foreach ($siswa as $s) {
            $nilaiSiswa = $nilai->get($s->id, collect());

            // Map nilai by mapel_id
            $nilaiByMapel = $nilaiSiswa->keyBy('data_mapel_id');

            $total = 0;
            $countNilai = 0;

            $nilaiMapel = [];
            foreach ($mapel as $m) {
                $v = $nilaiByMapel->get($m->id)->nilai_angka ?? null;

                $nilaiMapel[$m->id] = $v;

                if (is_numeric($v)) {
                    $total += (int) $v;
                    $countNilai++;
                }
            }

            $rata = $countNilai > 0 ? round($total / $countNilai, 1) : 0;

            $rows[] = [
                'siswa' => $s,
                'nilai' => $nilaiMapel,
                'total' => $total,
                'rata'  => $rata,
                'rank'  => null, // isi setelah sorting
            ];
        }

        // Ranking: urut total desc, kalau sama urut rata desc, kalau sama urut nama asc
        usort($rows, function ($a, $b) {
            if ($b['total'] !== $a['total']) return $b['total'] <=> $a['total'];
            if ($b['rata'] !== $a['rata']) return $b['rata'] <=> $a['rata'];
            return strcmp($a['siswa']->nama_siswa ?? '', $b['siswa']->nama_siswa ?? '');
        });

        // Set rank (simple ranking 1..n)
        $rank = 1;
        foreach ($rows as $i => $row) {
            $rows[$i]['rank'] = $rank++;
        }

        return view('guru.wali_kelas.rapor.leger.detail', compact('kelas', 'tahunAktif', 'mapel', 'rows'));
    }
}
