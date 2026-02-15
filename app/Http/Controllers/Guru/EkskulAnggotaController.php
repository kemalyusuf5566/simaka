<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\DataEkstrakurikuler;
use App\Models\DataGuru;
use App\Models\EkskulAnggota;
use App\Models\DataSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EkskulAnggotaController extends Controller
{
    /**
     * Ambil ID guru yang terkait dengan user login (pengguna).
     * NOTE: kalau kolom relasi di data_guru bukan "pengguna_id", ganti di sini.
     */
    private function getGuruIdByUser(): ?int
    {
        $userId = Auth::id();

        return DataGuru::where('pengguna_id', $userId)->value('id'); // <-- kalau beda, ubah kolom ini
    }

    /**
     * Samakan pola validasi pembina:
     * pembina_id boleh menyimpan:
     * - pengguna.id  (langsung match auth()->id())
     * - ATAU data_guru.id (match guru yang punya pengguna_id = auth()->id())
     */
    private function assertPembina(DataEkstrakurikuler $ekskul): void
    {
        $userId = (int) Auth::id();
        $guruId = $this->getGuruIdByUser(); // bisa null

        $pembinaId = (int) ($ekskul->pembina_id ?? 0);

        $ok = ($pembinaId === $userId) || ($guruId && $pembinaId === (int) $guruId);

        if (! $ok) {
            abort(403, 'Anda bukan pembina ekskul ini.');
        }
    }

    public function index(Request $request, DataEkstrakurikuler $ekskul)
    {
        $this->assertPembina($ekskul);

        // untuk dropdown "Tampilkan 10/25/50/100" (opsional)
        $perPage = (int) $request->get('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        // search (opsional) -> dipakai untuk filter nama/nis di tabel
        $q = trim((string) $request->get('q', ''));

        /**
         * AMBIL ANGGOTA (yang sudah masuk ekskul)
         * - paginate biar footer & pagination muncul
         */
        $anggotaQuery = EkskulAnggota::query()
            ->with(['siswa.kelas'])
            ->where('data_ekstrakurikuler_id', $ekskul->id)
            ->orderBy('id', 'desc');

        if ($q !== '') {
            $anggotaQuery->whereHas('siswa', function ($sq) use ($q) {
                $sq->where('nama_siswa', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        $anggota = $anggotaQuery
            ->paginate($perPage)
            ->appends($request->query());

        /**
         * AMBIL KANDIDAT (yang BELUM jadi anggota ekskul ini)
         * - ini yang harus dipakai untuk modal "Tambah Anggota"
         */
        $anggotaSiswaIds = EkskulAnggota::where('data_ekstrakurikuler_id', $ekskul->id)
            ->pluck('data_siswa_id')
            ->unique()
            ->values()
            ->all();

        $kandidatQuery = DataSiswa::query()
            ->with('kelas')
            ->when(!empty($anggotaSiswaIds), function ($sq) use ($anggotaSiswaIds) {
                $sq->whereNotIn('id', $anggotaSiswaIds);
            })
            ->orderBy('nama_siswa', 'asc');

        // search kandidat (opsional) -> biar modal tambah bisa filter juga lewat q_kandidat
        $qKandidat = trim((string) $request->get('q_kandidat', ''));
        if ($qKandidat !== '') {
            $kandidatQuery->where(function ($sq) use ($qKandidat) {
                $sq->where('nama_siswa', 'like', "%{$qKandidat}%")
                    ->orWhere('nis', 'like', "%{$qKandidat}%");
            });
        }

        $kandidat = $kandidatQuery
            ->paginate($perPage, ['*'], 'k_page')
            ->appends($request->query());

        /**
         * NOTE:
         * View yang kamu pakai sebelumnya mengirim variabel $siswa (semua siswa).
         * Sekarang supaya UI "Tambah" bener (yang tampil hanya kandidat),
         * kita kirim $kandidat. Kalau blade kamu masih pakai $siswa,
         * tinggal ganti di blade: $siswa -> $kandidat
         */
        return view('guru.ekskul.anggota.index', [
            'ekskul'   => $ekskul,
            'anggota'  => $anggota,
            'kandidat' => $kandidat,
            'perPage'  => $perPage,
            'q'        => $q,
            'qKandidat' => $qKandidat,
        ]);
    }

    public function store(Request $request, DataEkstrakurikuler $ekskul)
    {
        $this->assertPembina($ekskul);

        $request->validate([
            'data_siswa_id' => ['required', 'integer', 'exists:data_siswa,id'],
        ]);

        EkskulAnggota::firstOrCreate([
            'data_ekstrakurikuler_id' => $ekskul->id,
            'data_siswa_id'           => (int) $request->data_siswa_id,
        ]);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function update(Request $request, DataEkstrakurikuler $ekskul)
    {
        $this->assertPembina($ekskul);

        // OPSI PREDIKAT LENGKAP
        $allowed = [
            'Sangat Baik',
            'Baik',
            'Cukup',
            'Kurang',
        ];

        foreach ($request->input('nilai', []) as $anggotaId => $row) {

            $anggota = EkskulAnggota::where('id', $anggotaId)
                ->where('data_ekstrakurikuler_id', $ekskul->id)
                ->first();

            if (! $anggota) {
                continue;
            }

            $predikat  = $row['predikat'] ?? null;
            $deskripsi = $row['deskripsi'] ?? null;

            // validasi predikat manual
            if ($predikat !== null && $predikat !== '' && !in_array($predikat, $allowed, true)) {
                $predikat = null;
            }

            $anggota->predikat  = ($predikat === '' ? null : $predikat);
            $anggota->deskripsi = $deskripsi;
            $anggota->save();
        }

        return back()->with('success', 'Perubahan anggota berhasil disimpan.');
    }

    public function destroy(DataEkstrakurikuler $ekskul, EkskulAnggota $anggota)
    {
        $this->assertPembina($ekskul);

        if ((int) $anggota->data_ekstrakurikuler_id !== (int) $ekskul->id) {
            abort(404);
        }

        $anggota->delete();

        return back()->with('success', 'Anggota berhasil dihapus.');
    }
}
