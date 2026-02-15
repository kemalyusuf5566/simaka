<?php

namespace App\Http\Controllers\Guru\Kokurikuler;

use App\Http\Controllers\Controller;
use App\Models\KkKelompok;
use App\Models\KkKegiatan;
use App\Models\KkNilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeskripsiKokurikulerController extends Controller
{
    private function assertKoordinator(KkKelompok $kelompok): void
    {
        $user = Auth::user();

        if (!$user || (int) $kelompok->koordinator_id !== (int) $user->id) {
            abort(403, 'Anda bukan koordinator kelompok ini');
        }
    }

    /**
     * Halaman Deskripsi Capaian Kokurikuler
     * - 1 deskripsi per siswa (ditampilkan dari record kk_nilai pertama yg ditemukan)
     */
    public function index(KkKelompok $kelompok, KkKegiatan $kegiatan)
    {
        $this->assertKoordinator($kelompok);

        // anggota kelompok + relasi siswa
        $anggota = $kelompok->anggota()->with('siswa')->get();

        /**
         * Ambil semua nilai yg tersimpan untuk kelompok+kegiatan ini,
         * karena sekarang bisa ada banyak record per siswa (beda kk_capaian_akhir_id).
         * Untuk tampilan deskripsi: kita cukup pakai "deskripsi pertama" per siswa.
         */
        $nilaiRows = KkNilai::where('kk_kelompok_id', $kelompok->id)
            ->where('kk_kegiatan_id', $kegiatan->id)
            ->orderBy('id')
            ->get()
            ->groupBy('data_siswa_id'); // hasil: [siswaId => Collection<KkNilai>]

        return view('guru.kokurikuler.deskripsi.index', [
            'kelompok'  => $kelompok->load(['kelas', 'koordinator']),
            'kegiatan'  => $kegiatan,
            'anggota'   => $anggota,
            'nilaiRows' => $nilaiRows,
        ]);
    }

    /**
     * Simpan Deskripsi
     * - Deskripsi 1 siswa harus sama untuk semua capaian profil yg dipilih.
     * - Jadi: update deskripsi ke SEMUA record kk_nilai milik siswa tsb dalam kelompok+kegiatan.
     */
    public function update(Request $request, KkKelompok $kelompok, KkKegiatan $kegiatan)
    {
        $this->assertKoordinator($kelompok);

        $request->validate([
            'deskripsi' => 'nullable|array',
        ]);

        $rows = $request->input('deskripsi', []);

        foreach ($rows as $siswaId => $desc) {
            $siswaId = (int) $siswaId;
            $desc = is_string($desc) ? trim($desc) : null;

            // Update semua record nilai untuk siswa ini pada kelompok+kegiatan ini
            $affected = KkNilai::where('kk_kelompok_id', $kelompok->id)
                ->where('kk_kegiatan_id', $kegiatan->id)
                ->where('data_siswa_id', $siswaId)
                ->update([
                    'deskripsi' => ($desc !== '') ? $desc : null,
                ]);

            /**
             * Jika belum ada record nilai sama sekali (misal belum input nilai),
             * kita buat 1 record placeholder supaya deskripsi tetap tersimpan.
             *
             * Catatan:
             * - kk_capaian_akhir_id diset NULL (harusnya nullable)
             * - predikat NULL
             */
            if ($affected === 0) {
                KkNilai::create([
                    'kk_kelompok_id'       => $kelompok->id,
                    'kk_kegiatan_id'       => $kegiatan->id,
                    'data_siswa_id'        => $siswaId,
                    'kk_capaian_akhir_id'  => null,
                    'predikat'             => null,
                    'deskripsi'            => ($desc !== '') ? $desc : null,
                ]);
            }
        }

        return back()->with('success', 'Deskripsi kokurikuler berhasil disimpan.');
    }
}
