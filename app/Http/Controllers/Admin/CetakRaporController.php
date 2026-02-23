<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;

class CetakRaporController extends Controller
{
    /**
     * ==============================
     * INDEX CETAK RAPOR (PER KELAS)
     * URL: /admin/rapor/cetak
     * ==============================
     */
    public function index(Request $request)
    {
        // per page valid
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 10;

        $q = trim((string) $request->get('q', ''));
        $tingkat = $request->get('tingkat', '');

        // list tingkat untuk dropdown filter
        $tingkatList = DataKelas::query()
            ->select('tingkat')
            ->whereNotNull('tingkat')
            ->groupBy('tingkat')
            ->orderBy('tingkat', 'asc')
            ->pluck('tingkat')
            ->values();

        $kelasQuery = DataKelas::query()
            ->with(['wali.pengguna'])
            ->withCount('siswa');

        // filter tingkat
        if ($tingkat !== '' && $tingkat !== null) {
            $kelasQuery->where('tingkat', $tingkat);
        }

        // search: nama_kelas atau nama wali (kolom pengguna: "nama" saja, bukan "name")
        if ($q !== '') {
            $kelasQuery->where(function ($qq) use ($q) {
                $qq->where('nama_kelas', 'like', "%{$q}%")
                    ->orWhereHas('wali.pengguna', function ($w) use ($q) {
                        $w->where('nama', 'like', "%{$q}%");
                    });
            });
        }

        $kelas = $kelasQuery
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.rapor.cetak.index', compact(
            'kelas',
            'perPage',
            'q',
            'tingkat',
            'tingkatList'
        ));
    }

    /**
     * ==============================
     * DETAIL CETAK RAPOR (PER KELAS)
     * URL: /admin/rapor/cetak/{kelas}
     * ==============================
     */
    public function detail(Request $request, $kelasId)
    {
        $kelas = DataKelas::with(['wali.pengguna'])->findOrFail($kelasId);

        $tahun = DataTahunPelajaran::where('status_aktif', 1)->first();
        $semester = $tahun?->semester ?? 'Ganjil';

        // jenis kertas via querystring (default F4 biar sesuai contoh kamu)
        $paper = strtoupper((string) $request->get('paper', 'F4'));
        if (!in_array($paper, ['A4', 'F4'], true)) $paper = 'F4';

        // per page valid
        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 10;

        $q = trim((string) $request->get('q', ''));

        $siswaQuery = DataSiswa::query()
            ->where('data_kelas_id', $kelasId);

        // search siswa: nama / nis / nisn
        if ($q !== '') {
            $siswaQuery->where(function ($qq) use ($q) {
                $qq->where('nama_siswa', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%");
            });
        }

        $siswa = $siswaQuery
            ->orderBy('nama_siswa')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.rapor.cetak.detail', compact(
            'kelas',
            'siswa',
            'tahun',
            'semester',
            'paper',
            'perPage',
            'q'
        ));
    }
}
