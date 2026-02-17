<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataSekolah;
use App\Models\DataTahunPelajaran;
use App\Models\DataMapel;
use App\Models\NilaiMapelSiswa;
use App\Models\DataKetidakhadiran;
use App\Models\CatatanWaliKelas;
use App\Models\EkskulAnggota;
use App\Models\KkNilai;
use App\Models\TujuanPembelajaran;
use Barryvdh\DomPDF\Facade\Pdf;

class CetakRaporController extends Controller
{
    /**
     * ==============================
     * INDEX CETAK RAPOR (PER KELAS)
     * URL: /admin/rapor/cetak
     * ==============================
     */
    public function index()
    {
        $kelas = DataKelas::with(['wali.pengguna'])
            ->withCount('siswa')
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('admin.rapor.cetak.index', compact('kelas'));
    }

    /**
     * ==============================
     * DETAIL CETAK RAPOR (PER KELAS)
     * URL: /admin/rapor/cetak/{kelas}
     * ==============================
     */
    public function detail($kelasId)
    {
        // NOTE: relasi kamu sebelumnya: 'wali.pengguna'
        // aku biarkan agar tidak merusak struktur yang sudah ada.
        $kelas = DataKelas::with(['wali.pengguna'])->findOrFail($kelasId);

        $siswa = DataSiswa::where('data_kelas_id', $kelasId)
            ->orderBy('nama_siswa')
            ->get();

        $tahun = DataTahunPelajaran::where('status_aktif', 1)->first();
        $semester = $tahun?->semester ?? 'Ganjil'; // FIX: di DB kamu semester adalah 'Ganjil'/'Genap'

        // FIX: sebelumnya semester tidak ikut dikirim ke view
        return view('admin.rapor.cetak.detail', compact(
            'kelas',
            'siswa',
            'tahun',
            'semester'
        ));
    }

    /**
     * ==============================
     * PDF KELENGKAPAN RAPOR
     * ==============================
     */
    public function kelengkapan($siswaId)
    {
        $siswa = DataSiswa::with([
            'kelas',
            'kelas.wali.pengguna',
        ])->findOrFail($siswaId);

        $sekolah = DataSekolah::first();
        $tahun   = DataTahunPelajaran::where('status_aktif', 1)->first();

        $pdf = Pdf::loadView(
            'admin.rapor.pdf.kelengkapan',
            compact('siswa', 'sekolah', 'tahun')
        )->setPaper('A4');

        return $pdf->stream('KELENGKAPAN_RAPOR_' . $siswa->nama_siswa . '.pdf');
    }

    /**
     * ==============================
     * PDF RAPOR SEMESTER
     * ==============================
     * URL contoh:
     * /admin/rapor/semester/{siswaId}?semester=Genap&data_tahun_pelajaran_id=2
     */
    public function semester(Request $request, $siswaId)
    {
        $siswa = DataSiswa::with([
            'kelas',
            'kelas.wali.pengguna',
        ])->findOrFail($siswaId);

        $sekolah = DataSekolah::first();

        // Tahun pelajaran aktif, atau pakai query jika ada
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();
        $tahun = $request->filled('data_tahun_pelajaran_id')
            ? DataTahunPelajaran::findOrFail($request->get('data_tahun_pelajaran_id'))
            : $tahunAktif;

        if (!$tahun) {
            abort(404, 'Tahun pelajaran tidak ditemukan.');
        }

        // Semester pakai query jika ada, kalau tidak ikut tahun aktif
        $semester = $request->get('semester', $tahun->semester ?: 'Ganjil');
        $semester = $this->normalizeSemester($semester);

        // Ambil paket data rapor (mapel tidak double, ekskul/kokurikuler terpisah, absensi & catatan muncul)
        $rapor = $this->buildRaporData($siswa, $tahun, $semester);

        $pdf = Pdf::loadView(
            'admin.rapor.pdf.semester',
            [
                'siswa'      => $siswa,
                'sekolah'    => $sekolah,
                'tahun'      => $tahun,
                'semester'   => $semester,

                'rowsUmum'   => $rapor['rowsUmum'],
                'rowsPilihan' => $rapor['rowsPilihan'],

                'ekskul'     => $rapor['ekskul'],
                'kokurikuler' => $rapor['kokurikuler'],

                'absensi'    => $rapor['absensi'],
                'catatan'    => $rapor['catatan'],
            ]
        )->setPaper('F4', 'portrait');

        return $pdf->stream('RAPOR_SEMESTER_' . $siswa->nama_siswa . '.pdf');
    }

    /**
     * =========================================================
     * HELPER: normalisasi semester agar pasti 'Ganjil' / 'Genap'
     * =========================================================
     */
    private function normalizeSemester($semester): string
    {
        $s = strtolower(trim((string) $semester));

        // kalau ada yang ngirim 1/2 atau ganjil/genap campur
        if ($s === '1' || $s === 'ganjil') return 'Ganjil';
        if ($s === '2' || $s === 'genap')  return 'Genap';

        // fallback aman
        return 'Ganjil';
    }

    /**
     * =========================================================
     * HELPER: bangun data rapor lengkap untuk 1 siswa
     * - Mapel ditarik dari master data_mapel (jadi tampil semua, tidak cuma matematika)
     * - Nilai keyed by data_mapel_id (tidak double)
     * - Capaian kompetensi ambil dari TP / deskripsi tinggi-rendah
     * - Ekskul & Kokurikuler dipisah
     * - Absensi & catatan wali kelas muncul
     * =========================================================
     */
    private function buildRaporData(DataSiswa $siswa, DataTahunPelajaran $tahun, string $semester): array
    {
        // MASTER MAPEL (agar tidak hanya mapel yg ada nilainya)
        $mapelUmum = DataMapel::where('kelompok_mapel', 'Mata Pelajaran Umum')
            ->orderByRaw('COALESCE(urutan_cetak, 999) ASC')
            ->orderBy('id', 'ASC')
            ->get();

        $mapelPilihan = DataMapel::where('kelompok_mapel', 'Mata Pelajaran Pilihan')
            ->orderByRaw('COALESCE(urutan_cetak, 999) ASC')
            ->orderBy('id', 'ASC')
            ->get();

        // NILAI MAPEL (keyBy mapel_id supaya tidak dobel)
        $nilaiMapel = NilaiMapelSiswa::where('data_siswa_id', $siswa->id)
            ->where('data_tahun_pelajaran_id', $tahun->id)
            ->where('semester', $semester)
            ->get()
            ->keyBy('data_mapel_id');

        // TP untuk capaian (optimal/perlu)
        $nilaiIds = $nilaiMapel->pluck('id')->values();
        $tpByNilai = collect();

        if ($nilaiIds->count() > 0) {
            $tpByNilai = TujuanPembelajaran::with(['tujuanPembelajaran'])
                ->whereIn('nilai_mapel_siswa_id', $nilaiIds)
                ->get()
                ->groupBy('nilai_mapel_siswa_id');
        }

        $predikatFromAngka = function ($angka) {
            if ($angka === null) return '-';
            if (!is_numeric($angka)) return '-';

            $n = (int) $angka;
            if ($n >= 90) return 'A';
            if ($n >= 80) return 'B';
            if ($n >= 70) return 'C';
            return 'D';
        };

        $buildCapaian = function ($nilai) use ($tpByNilai) {
            if (!$nilai) return '-';

            $rows = $tpByNilai->get($nilai->id, collect());

            $optimal = [];
            $perlu = [];

            foreach ($rows as $r) {
                $tujuan = optional($r->tujuanPembelajaran)->tujuan;
                if (!$tujuan) continue;

                if ($r->kategori === 'optimal') $optimal[] = $tujuan;
                if ($r->kategori === 'perlu')   $perlu[]   = $tujuan;
            }

            // fallback kalau TP kosong: pakai deskripsi_tinggi/rendah
            if (count($optimal) === 0 && count($perlu) === 0) {
                $tinggi = trim((string) $nilai->deskripsi_tinggi);
                $rendah = trim((string) $nilai->deskripsi_rendah);

                $parts = [];
                if ($tinggi !== '') $parts[] = "Mencapai Kompetensi dengan sangat baik dalam hal {$tinggi}";
                if ($rendah !== '') $parts[] = "Perlu peningkatan dalam hal {$rendah}";

                return count($parts) ? implode(". ", $parts) . "." : '-';
            }

            $parts = [];
            if (count($optimal)) $parts[] = "Mencapai Kompetensi dengan sangat baik dalam hal " . implode(", ", $optimal);
            if (count($perlu))   $parts[] = "Perlu peningkatan dalam hal " . implode(", ", $perlu);

            return implode(". ", $parts) . ".";
        };

        $makeRows = function ($mapelList) use ($nilaiMapel, $predikatFromAngka, $buildCapaian) {
            return $mapelList->map(function ($m) use ($nilaiMapel, $predikatFromAngka, $buildCapaian) {
                $n = $nilaiMapel->get($m->id);

                $angka = $n?->nilai_angka;
                $nilaiTampil = ($angka === null) ? '-' : (is_numeric($angka) ? (int) $angka : '-');

                // Kalau kamu mau nilai "0" dianggap belum isi => tampil '-'
                // $nilaiTampil = ($angka === null || (int)$angka === 0) ? '-' : (int)$angka;

                return (object) [
                    'mapel_id'   => $m->id,
                    'nama_mapel' => $m->nama_mapel,
                    'singkatan'  => $m->singkatan,
                    'nilai_akhir' => $nilaiTampil,
                    'predikat'   => $n?->predikat ?? $predikatFromAngka($angka),
                    'capaian'    => $buildCapaian($n),
                ];
            });
        };

        $rowsUmum    = $makeRows($mapelUmum);
        $rowsPilihan = $makeRows($mapelPilihan);

        // EKSKUL (terpisah)
        $ekskul = EkskulAnggota::with(['ekskul'])
            ->where('data_siswa_id', $siswa->id)
            ->get();

        // KOKURIKULER (terpisah)
        // Catatan: struktur kk_nilai kamu belum ada semester/tapel, jadi ditampilkan semua data yang ada.
        $kokurikuler = KkNilai::with(['kegiatan', 'capaianAkhir'])
            ->where('data_siswa_id', $siswa->id)
            ->get();

        // ABSENSI
        $absensi = DataKetidakhadiran::where('data_siswa_id', $siswa->id)
            ->where('data_tahun_pelajaran_id', $tahun->id)
            ->where('semester', $semester)
            ->first();

        // CATATAN WALI KELAS
        $catatan = CatatanWaliKelas::where('data_siswa_id', $siswa->id)
            ->where('data_tahun_pelajaran_id', $tahun->id)
            ->where('semester', $semester)
            ->first();

        return [
            'rowsUmum'     => $rowsUmum,
            'rowsPilihan'  => $rowsPilihan,
            'ekskul'       => $ekskul,
            'kokurikuler'  => $kokurikuler,
            'absensi'      => $absensi,
            'catatan'      => $catatan,
        ];
    }
}
