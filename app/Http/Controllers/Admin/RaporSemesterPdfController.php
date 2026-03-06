<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CatatanWaliKelas;
use App\Models\DataKetidakhadiran;
use App\Models\DataMapel;
use App\Models\DataSekolah;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use App\Models\EkskulAnggota;
use App\Models\KkNilai;
use App\Models\NilaiMapelSiswa;
use Barryvdh\DomPDF\Facade\Pdf;

class RaporSemesterPdfController extends Controller
{
    public function show($siswaId)
    {
        // ================= SISWA =================
        $siswa = DataSiswa::with([
            'kelas',
            'kelas.wali.pengguna', // tabel pengguna (wali kelas)
        ])->findOrFail($siswaId);

        // ================= TAHUN AKTIF =================
        $tahun = DataTahunPelajaran::where('status_aktif', 1)->first();
        if (!$tahun) {
            abort(404, 'Tahun pelajaran aktif belum diset.');
        }

        // ================= SEMESTER (Wajib dikirim ke blade) =================
        // Di DB kamu: semester = 'Ganjil' / 'Genap'
        $semester = $tahun->semester ?? 'Ganjil';

        // ================= SEKOLAH =================
        $sekolah = DataSekolah::first();

        // ================= NILAI MAPEL =================
        // Filter mapel mengikuti tingkat + jurusan kelas siswa.
        $kelasTingkat = (string)($siswa->kelas?->tingkat ?? '');
        $mapelTingkat = $this->resolveMapelTingkat($kelasTingkat);
        $fase = $this->resolveFase($kelasTingkat);
        $kelasJurusanId = $siswa->kelas?->jurusan_id;

        $allMapel = DataMapel::query()
            ->when(
                $mapelTingkat !== null,
                fn($q) => $q->whereIn('tingkat', ['SEMUA', $mapelTingkat])
            )
            ->when(
                $kelasJurusanId,
                function ($q) use ($kelasJurusanId) {
                    $q->where(function ($w) use ($kelasJurusanId) {
                        $w->whereNull('jurusan_id')
                            ->orWhere('jurusan_id', $kelasJurusanId);
                    });
                },
                fn($q) => $q->whereNull('jurusan_id')
            )
            ->orderByRaw('COALESCE(urutan_cetak, 9999) ASC')
            ->orderBy('nama_mapel')
            ->get();

        $nilaiMapel = NilaiMapelSiswa::where('data_siswa_id', $siswaId)
            ->where('data_kelas_id', $siswa->data_kelas_id)
            ->where('data_tahun_pelajaran_id', $tahun->id)
            ->where('semester', $semester)
            ->get()
            ->keyBy('data_mapel_id');

        $mapelUmum = [];
        $mapelPilihan = [];
        $mulok = []; // kalau nanti ada kelompok "Mulok", tinggal isi dari sini.

        foreach ($allMapel as $m) {
            $n = $nilaiMapel->get($m->id);

            // nilai akhir -> pakai nilai_angka (di DB kamu namanya nilai_angka)
            $nilaiAkhir = $n?->nilai_angka;

            // capaian kompetensi -> kalau kolom deskripsi null, tetap '-'
            // kamu bisa pilih mau pakai deskripsi / deskripsi_tinggi+rendah
            $capaian = $n?->deskripsi;
            if (!$capaian) {
                // kalau deskripsi utama kosong, coba gabung tinggi/rendah kalau ada
                $tinggi = trim((string)($n?->deskripsi_tinggi ?? ''));
                $rendah = trim((string)($n?->deskripsi_rendah ?? ''));
                if ($tinggi !== '' || $rendah !== '') {
                    $capaian = trim(($tinggi !== '' ? "Kekuatan: {$tinggi}" : '') . ($rendah !== '' ? "\nPerlu Ditingkatkan: {$rendah}" : ''));
                }
            }

            $row = [
                'nama'    => $m->nama_mapel,
                'nilai'   => ($nilaiAkhir !== null ? $nilaiAkhir : '-'),
                'capaian' => ($capaian ? $capaian : '-'),
                'kelompok' => $m->kelompok_mapel,
            ];

            // Kelompok non-umum digabung ke blok mapel pilihan/kejuruan.
            if (in_array(($m->kelompok_mapel ?? ''), ['Mata Pelajaran Pilihan', 'Mata Pelajaran Kejuruan'], true)) {
                $mapelPilihan[] = $row;
            } else {
                $mapelUmum[] = $row;
            }
        }

        // ================= EKSKUL (ambil dari ekskul_anggota) =================
        // tampilkan predikat & deskripsi, walau null -> '-'
        $ekskul = EkskulAnggota::with(['ekskul'])
            ->where('data_siswa_id', $siswaId)
            ->orderBy('data_ekstrakurikuler_id')
            ->get();

        // ================= ABSENSI (data_ketidakhadiran) =================
        $absensi = DataKetidakhadiran::where('data_siswa_id', $siswaId)
            ->where('data_tahun_pelajaran_id', $tahun->id)
            ->where('semester', $semester)
            ->first();

        // ================= CATATAN WALI (catatan_wali_kelas) =================
        $catatan = CatatanWaliKelas::where('data_siswa_id', $siswaId)
            ->where('data_tahun_pelajaran_id', $tahun->id)
            ->where('semester', $semester)
            ->first();

        // ================= KOKURIKULER (KK NILAI) =================
        // Ambil kegiatan kokurikuler dari kk_nilai untuk siswa (jika ada)
        $kokurikulerRows = KkNilai::with([
            'kegiatan',
            'kkCapaianAkhir',
        ])
            ->where('data_siswa_id', $siswaId)
            ->orderBy('kk_kegiatan_id')
            ->get();

        // buat text paragraf (kalau kosong -> '-')
        $kokurikulerText = '-';
        if ($kokurikulerRows->count() > 0) {
            $lines = [];
            foreach ($kokurikulerRows as $kn) {
                $keg  = $kn->kegiatan->nama_kegiatan ?? $kn->kegiatan->tema ?? 'Kegiatan';
                $cap  = $kn->capaianAkhir->capaian ?? null;
                $pred = $kn->predikat ?? null;
                $desk = trim((string)($kn->deskripsi ?? ''));

                $txt = $keg;
                if ($cap)  $txt .= " ({$cap})";
                if ($pred) $txt .= " - {$pred}";
                if ($desk !== '') $txt .= ": {$desk}";
                $lines[] = $txt;
            }
            $kokurikulerText = implode("\n", $lines);
        }

        // ================= KENAIKAN / KELULUSAN (khusus semester Genap) =================
        $statusAkhir = null;
        $labelStatusAkhir = null;

        if ($semester === 'Genap') {
            $tingkat = (string)($siswa->kelas?->tingkat ?? '');
            // SMA: kelas 12 genap -> kelulusan
            $labelStatusAkhir = in_array(strtoupper($tingkat), ['12', 'XII'], true) ? 'Kelulusan' : 'Kenaikan Kelas';

            $statusAkhir = $this->resolveStatusAkhirText($catatan?->status_kenaikan_kelas, $tingkat);
        }

        // ====== GABUNGKAN mapelUmum + mapelPilihan supaya blade lama tetap jalan ======
        $nilaiMapel = collect();

        foreach ($mapelUmum as $m) {
            $nilaiMapel->push((object)[
                'mapel' => (object)['nama_mapel' => $m['nama']],
                'nilai_angka' => $m['nilai'] === '-' ? null : $m['nilai'],
                'deskripsi' => $m['capaian'] === '-' ? null : $m['capaian'],
            ]);
        }

        foreach ($mapelPilihan as $m) {
            $nilaiMapel->push((object)[
                'mapel' => (object)['nama_mapel' => $m['nama']],
                'nilai_angka' => $m['nilai'] === '-' ? null : $m['nilai'],
                'deskripsi' => $m['capaian'] === '-' ? null : $m['capaian'],
            ]);
        }

        // ================= PDF =================
        $pdf = Pdf::loadView(
            'admin.rapor.pdf.rapor-semester',
            compact(
                'siswa',
                'sekolah',
                'tahun',
                'semester',
                'mapelUmum',
                'mapelPilihan',
                'mulok',
                'ekskul',
                'absensi',
                'catatan',
                'kokurikulerText',
                'labelStatusAkhir',
                'statusAkhir',
                'fase',
                'nilaiMapel'
            )
        )->setPaper('A4', 'portrait');

        return $pdf->stream('RAPOR_SEMESTER_' . $siswa->nama_siswa . '.pdf');
    }

    private function resolveMapelTingkat(string $tingkat): ?string
    {
        $normalized = strtoupper(trim($tingkat));

        return match ($normalized) {
            '10', 'X' => 'X',
            '11', 'XI' => 'XI',
            '12', 'XII' => 'XII',
            default => null,
        };
    }

    private function resolveFase(string $tingkat): string
    {
        $normalized = strtoupper(trim($tingkat));

        return match ($normalized) {
            '10', 'X' => 'E',
            '11', 'XI', '12', 'XII' => 'F',
            default => '-',
        };
    }

    private function resolveStatusAkhirText(?string $statusRaw, string $tingkat): string
    {
        $status = strtolower(trim((string)$statusRaw));
        if ($status === '') {
            return '-';
        }

        $normalizedTingkat = strtoupper(trim($tingkat));
        $angkaTingkat = is_numeric($tingkat) ? (int)$tingkat : null;

        return match ($status) {
            'lulus' => 'Berdasarkan hasil pembelajaran yang dicapai peserta didik ditetapkan LULUS.',
            'tidak lulus', 'tidak_lulus' => 'Berdasarkan hasil pembelajaran yang dicapai peserta didik ditetapkan BELUM LULUS.',
            'naik' => ($angkaTingkat !== null)
                ? 'Berdasarkan hasil pembelajaran yang dicapai peserta didik ditetapkan naik ke kelas ' . ($angkaTingkat + 1) . '.'
                : 'Berdasarkan hasil pembelajaran yang dicapai peserta didik ditetapkan NAIK KELAS.',
            'tidak naik', 'tidak_naik' => ($angkaTingkat !== null)
                ? 'Berdasarkan hasil pembelajaran yang dicapai peserta didik ditetapkan tetap di kelas ' . $angkaTingkat . '.'
                : 'Berdasarkan hasil pembelajaran yang dicapai peserta didik ditetapkan TIDAK NAIK KELAS.',
            default => in_array($normalizedTingkat, ['12', 'XII'], true)
                ? 'Berdasarkan hasil pembelajaran yang dicapai peserta didik ditetapkan LULUS.'
                : $statusRaw,
        };
    }
}
