<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\BkHomeVisit;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeVisitController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', 10);
        if (!in_array($limit, [10, 25, 50, 100], true)) {
            $limit = 10;
        }

        $q = trim((string) $request->get('q', ''));
        $kelasId = $request->get('kelas_id');
        $status = trim((string) $request->get('status', ''));
        $tanggalDari = $request->get('tanggal_dari');
        $tanggalSampai = $request->get('tanggal_sampai');

        $homeVisits = BkHomeVisit::query()
            ->with(['siswa', 'kelas'])
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->whereHas('siswa', function ($sQuery) use ($q) {
                        $sQuery->where('nama_siswa', 'like', "%{$q}%")
                            ->orWhere('nis', 'like', "%{$q}%")
                            ->orWhere('nisn', 'like', "%{$q}%");
                    })
                        ->orWhere('tujuan_kunjungan', 'like', "%{$q}%")
                        ->orWhere('lokasi_kunjungan', 'like', "%{$q}%")
                        ->orWhere('hasil_observasi', 'like', "%{$q}%");
                });
            })
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', $kelasId))
            ->when($status !== '', fn($builder) => $builder->where('status', $status))
            ->when($tanggalDari, fn($builder) => $builder->whereDate('tanggal_kunjungan', '>=', $tanggalDari))
            ->when($tanggalSampai, fn($builder) => $builder->whereDate('tanggal_kunjungan', '<=', $tanggalSampai))
            ->latest('tanggal_kunjungan')
            ->latest('id')
            ->paginate($limit)
            ->withQueryString();

        $kelasOptions = DataKelas::orderBy('nama_kelas')->get();
        $siswaOptions = DataSiswa::with('kelas')->orderBy('nama_siswa')->get();

        $statusCounts = [];
        foreach (BkHomeVisit::statusOptions() as $st) {
            $statusCounts[$st] = BkHomeVisit::where('status', $st)->count();
        }

        return view('bk.home_visit.index', [
            'homeVisits' => $homeVisits,
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'statusOptions' => BkHomeVisit::statusOptions(),
            'statusCounts' => $statusCounts,
            'limit' => $limit,
            'q' => $q,
            'kelasId' => $kelasId,
            'status' => $status,
            'tanggalDari' => $tanggalDari,
            'tanggalSampai' => $tanggalSampai,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'tanggal_kunjungan' => 'required|date',
            'lokasi_kunjungan' => 'nullable|string|max:180',
            'tujuan_kunjungan' => 'required|string|max:200',
            'status' => 'required|in:' . implode(',', BkHomeVisit::statusOptions()),
            'hasil_observasi' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        BkHomeVisit::create([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif->id,
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'lokasi_kunjungan' => $validated['lokasi_kunjungan'] ?? null,
            'tujuan_kunjungan' => $validated['tujuan_kunjungan'],
            'status' => $validated['status'],
            'hasil_observasi' => $validated['hasil_observasi'] ?? null,
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.home-visit.index')
            ->with('success', 'Laporan home visit berhasil ditambahkan.');
    }

    public function update(Request $request, BkHomeVisit $homeVisit)
    {
        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'tanggal_kunjungan' => 'required|date',
            'lokasi_kunjungan' => 'nullable|string|max:180',
            'tujuan_kunjungan' => 'required|string|max:200',
            'status' => 'required|in:' . implode(',', BkHomeVisit::statusOptions()),
            'hasil_observasi' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        $homeVisit->update([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif?->id ?? $homeVisit->data_tahun_pelajaran_id,
            'tanggal_kunjungan' => $validated['tanggal_kunjungan'],
            'lokasi_kunjungan' => $validated['lokasi_kunjungan'] ?? null,
            'tujuan_kunjungan' => $validated['tujuan_kunjungan'],
            'status' => $validated['status'],
            'hasil_observasi' => $validated['hasil_observasi'] ?? null,
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.home-visit.index')
            ->with('success', 'Laporan home visit berhasil diperbarui.');
    }

    public function destroy(BkHomeVisit $homeVisit)
    {
        $homeVisit->delete();

        return redirect()->route('bk.home-visit.index')
            ->with('success', 'Laporan home visit berhasil dihapus.');
    }
}

