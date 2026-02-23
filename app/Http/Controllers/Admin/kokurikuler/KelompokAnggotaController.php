<?php

namespace App\Http\Controllers\Admin\Kokurikuler;

use App\Http\Controllers\Controller;
use App\Models\KkKelompok;
use App\Models\KkKelompokAnggota;
use App\Models\DataSiswa;
use Illuminate\Http\Request;

class KelompokAnggotaController extends Controller
{
    public function index(Request $request, KkKelompok $kelompok)
    {
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 10;

        $q = trim((string) $request->get('q', ''));

        $kelompok->load(['kelas', 'koordinator']);

        $anggotaQuery = KkKelompokAnggota::query()
            ->with(['siswa'])
            ->where('kk_kelompok_id', $kelompok->id);

        if ($q !== '') {
            $anggotaQuery->whereHas('siswa', function ($s) use ($q) {
                $s->where('nama_siswa', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        $anggota = $anggotaQuery
            ->orderBy('id', 'desc')
            ->paginate($perPage, ['*'], 'page')
            ->withQueryString();

        $anggotaSiswaIds = KkKelompokAnggota::where('kk_kelompok_id', $kelompok->id)
            ->pluck('data_siswa_id')
            ->toArray();

        $kandidatQuery = DataSiswa::query()
            ->where('data_kelas_id', $kelompok->data_kelas_id)
            ->when(count($anggotaSiswaIds) > 0, fn($qq) => $qq->whereNotIn('id', $anggotaSiswaIds))
            ->orderBy('nama_siswa');

        $kq = trim((string) $request->get('kq', ''));
        if ($kq !== '') {
            $kandidatQuery->where(function ($s) use ($kq) {
                $s->where('nama_siswa', 'like', "%{$kq}%")
                    ->orWhere('nis', 'like', "%{$kq}%");
            });
        }

        $kandidat = $kandidatQuery
            ->paginate($perPage, ['*'], 'kpage')
            ->withQueryString();

        return view('admin.kokurikuler.kelompok.anggota.index', compact(
            'kelompok',
            'anggota',
            'kandidat',
            'perPage',
            'q',
            'kq'
        ));
    }

    public function store(Request $request, KkKelompok $kelompok)
    {
        $request->validate([
            'data_siswa_id' => ['required', 'integer', 'exists:data_siswa,id'],
        ]);

        KkKelompokAnggota::firstOrCreate([
            'kk_kelompok_id' => $kelompok->id,
            'data_siswa_id'  => (int) $request->data_siswa_id,
        ]);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function destroy(KkKelompok $kelompok, KkKelompokAnggota $anggota)
    {
        if ((int) $anggota->kk_kelompok_id !== (int) $kelompok->id) abort(404);

        $anggota->delete();

        return back()->with('success', 'Anggota berhasil dihapus.');
    }

    public function addAll(Request $request, KkKelompok $kelompok)
    {
        $anggotaSiswaIds = KkKelompokAnggota::where('kk_kelompok_id', $kelompok->id)
            ->pluck('data_siswa_id')
            ->toArray();

        $kandidat = DataSiswa::query()
            ->where('data_kelas_id', $kelompok->data_kelas_id)
            ->when(count($anggotaSiswaIds) > 0, fn($qq) => $qq->whereNotIn('id', $anggotaSiswaIds))
            ->pluck('id')
            ->toArray();

        foreach ($kandidat as $sid) {
            KkKelompokAnggota::firstOrCreate([
                'kk_kelompok_id' => $kelompok->id,
                'data_siswa_id'  => (int) $sid,
            ]);
        }

        return back()->with('success', 'Semua kandidat berhasil ditambahkan.');
    }
}
