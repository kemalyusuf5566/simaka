<?php

namespace App\Http\Controllers\Guru\Kokurikuler;

use App\Http\Controllers\Controller;
use App\Models\KkKelompok;
use App\Models\KkKegiatan;
use App\Models\KkKelompokKegiatan;
use App\Models\KkCapaianAkhir;
use App\Models\KkNilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NilaiKokurikulerController extends Controller
{
    private function assertKoordinator(KkKelompok $kelompok): void
    {
        $user = Auth::user();
        if (!$user || (int) $kelompok->koordinator_id !== (int) $user->id) {
            abort(403, 'Anda bukan koordinator kelompok ini');
        }
    }

    public function index(Request $request, KkKelompok $kelompok, KkKegiatan $kegiatan)
    {
        $this->assertKoordinator($kelompok);

        // pastikan kegiatan ini memang dipilih oleh kelompok (ambil pivotnya)
        $pivot = KkKelompokKegiatan::where('kk_kelompok_id', $kelompok->id)
            ->where('kk_kegiatan_id', $kegiatan->id)
            ->firstOrFail();

        // daftar capaian akhir untuk dropdown (capaian profil)
        $capaianAkhir = KkCapaianAkhir::with('dimensi')
            ->where('kk_kelompok_kegiatan_id', $pivot->id)
            ->orderBy('kk_dimensi_id')
            ->orderBy('id')
            ->get();

        // capaian yang sedang dipilih (kalau kosong, default ke item pertama)
        $selectedCapaianId = (int) $request->get('kk_capaian_akhir_id', 0);
        if ($selectedCapaianId <= 0) {
            $selectedCapaianId = (int) ($capaianAkhir->first()->id ?? 0);
        }

        // anggota kelompok + siswa
        $anggota = $kelompok->anggota()->with('siswa')->get();

        /**
         * INI KUNCI:
         * nilaiRows harus dibedakan per capaian profil.
         * Jadi ketika dropdown ganti -> data nilai yang ditampilkan ikut ganti.
         */
        $nilaiRows = KkNilai::where('kk_kelompok_id', $kelompok->id)
            ->where('kk_kegiatan_id', $kegiatan->id)
            ->when($selectedCapaianId > 0, fn($q) => $q->where('kk_capaian_akhir_id', $selectedCapaianId))
            ->get()
            ->keyBy('data_siswa_id');

        $opsiPredikat = [
            'SB' => 'Sangat Baik',
            'B'  => 'Baik',
            'C'  => 'Cukup',
            'PB' => 'Perlu Bimbingan',
        ];

        return view('guru.kokurikuler.nilai.index', [
            'kelompok'          => $kelompok->load(['kelas', 'koordinator']),
            'kegiatan'          => $kegiatan,
            'pivot'             => $pivot,
            'capaianAkhir'      => $capaianAkhir,
            'selectedCapaianId' => $selectedCapaianId,
            'anggota'           => $anggota,
            'nilaiRows'         => $nilaiRows,
            'opsiPredikat'      => $opsiPredikat,
        ]);
    }

    public function update(Request $request, KkKelompok $kelompok, KkKegiatan $kegiatan)
    {
        $this->assertKoordinator($kelompok);

        $request->validate([
            'nilai' => 'required|array',
        ]);

        foreach ($request->input('nilai', []) as $siswaId => $row) {
            $kkCapaianAkhirId = isset($row['kk_capaian_akhir_id']) ? (int) $row['kk_capaian_akhir_id'] : 0;
            $predikat         = $row['predikat'] ?? null;

            // kalau capaian tidak dipilih, skip
            if ($kkCapaianAkhirId <= 0) {
                continue;
            }

            /**
             * Kunci record harus menyertakan kk_capaian_akhir_id
             * supaya capaian profil 1 & 2 tersimpan masing-masing,
             * tidak saling overwrite.
             *
             * NOTE:
             * Pastikan UNIQUE di database sudah mencakup:
             * (kk_kelompok_id, kk_kegiatan_id, data_siswa_id, kk_capaian_akhir_id)
             */
            KkNilai::updateOrCreate(
                [
                    'kk_kelompok_id'      => $kelompok->id,
                    'kk_kegiatan_id'      => $kegiatan->id,
                    'data_siswa_id'       => (int) $siswaId,
                    'kk_capaian_akhir_id' => $kkCapaianAkhirId,
                ],
                [
                    'predikat' => $predikat ?: null,
                ]
            );
        }

        return back()->with('success', 'Nilai kokurikuler berhasil disimpan.');
    }
}
