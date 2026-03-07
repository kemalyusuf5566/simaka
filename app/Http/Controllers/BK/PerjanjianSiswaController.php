<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\BkPerjanjianSiswa;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerjanjianSiswaController extends Controller
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

        $perjanjian = BkPerjanjianSiswa::query()
            ->with(['siswa', 'kelas'])
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->whereHas('siswa', function ($sQuery) use ($q) {
                        $sQuery->where('nama_siswa', 'like', "%{$q}%")
                            ->orWhere('nis', 'like', "%{$q}%")
                            ->orWhere('nisn', 'like', "%{$q}%");
                    })
                        ->orWhere('nomor_dokumen', 'like', "%{$q}%")
                        ->orWhere('isi_perjanjian', 'like', "%{$q}%")
                        ->orWhere('pihak_orang_tua', 'like', "%{$q}%");
                });
            })
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', $kelasId))
            ->when($status !== '', fn($builder) => $builder->where('status', $status))
            ->when($tanggalDari, fn($builder) => $builder->whereDate('tanggal_perjanjian', '>=', $tanggalDari))
            ->when($tanggalSampai, fn($builder) => $builder->whereDate('tanggal_perjanjian', '<=', $tanggalSampai))
            ->latest('tanggal_perjanjian')
            ->latest('id')
            ->paginate($limit)
            ->withQueryString();

        $kelasOptions = DataKelas::orderBy('nama_kelas')->get();
        $siswaOptions = DataSiswa::with('kelas')->orderBy('nama_siswa')->get();

        $statusCounts = [];
        foreach (BkPerjanjianSiswa::statusOptions() as $st) {
            $statusCounts[$st] = BkPerjanjianSiswa::where('status', $st)->count();
        }

        return view('bk.perjanjian_siswa.index', [
            'perjanjian' => $perjanjian,
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'statusOptions' => BkPerjanjianSiswa::statusOptions(),
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
            'tanggal_perjanjian' => 'required|date',
            'nomor_dokumen' => 'nullable|string|max:80',
            'pihak_orang_tua' => 'nullable|string|max:150',
            'hubungan_orang_tua' => 'nullable|string|max:80',
            'status' => 'required|in:' . implode(',', BkPerjanjianSiswa::statusOptions()),
            'isi_perjanjian' => 'required|string',
            'target_perbaikan' => 'nullable|string',
            'tanggal_evaluasi' => 'nullable|date|after_or_equal:tanggal_perjanjian',
            'hasil_evaluasi' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        BkPerjanjianSiswa::create([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif->id,
            'tanggal_perjanjian' => $validated['tanggal_perjanjian'],
            'nomor_dokumen' => $validated['nomor_dokumen'] ?? null,
            'pihak_orang_tua' => $validated['pihak_orang_tua'] ?? null,
            'hubungan_orang_tua' => $validated['hubungan_orang_tua'] ?? null,
            'status' => $validated['status'],
            'isi_perjanjian' => $validated['isi_perjanjian'],
            'target_perbaikan' => $validated['target_perbaikan'] ?? null,
            'tanggal_evaluasi' => $validated['tanggal_evaluasi'] ?? null,
            'hasil_evaluasi' => $validated['hasil_evaluasi'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.perjanjian-siswa.index')
            ->with('success', 'Perjanjian siswa berhasil ditambahkan.');
    }

    public function update(Request $request, BkPerjanjianSiswa $perjanjianSiswa)
    {
        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'tanggal_perjanjian' => 'required|date',
            'nomor_dokumen' => 'nullable|string|max:80',
            'pihak_orang_tua' => 'nullable|string|max:150',
            'hubungan_orang_tua' => 'nullable|string|max:80',
            'status' => 'required|in:' . implode(',', BkPerjanjianSiswa::statusOptions()),
            'isi_perjanjian' => 'required|string',
            'target_perbaikan' => 'nullable|string',
            'tanggal_evaluasi' => 'nullable|date|after_or_equal:tanggal_perjanjian',
            'hasil_evaluasi' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        $perjanjianSiswa->update([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif?->id ?? $perjanjianSiswa->data_tahun_pelajaran_id,
            'tanggal_perjanjian' => $validated['tanggal_perjanjian'],
            'nomor_dokumen' => $validated['nomor_dokumen'] ?? null,
            'pihak_orang_tua' => $validated['pihak_orang_tua'] ?? null,
            'hubungan_orang_tua' => $validated['hubungan_orang_tua'] ?? null,
            'status' => $validated['status'],
            'isi_perjanjian' => $validated['isi_perjanjian'],
            'target_perbaikan' => $validated['target_perbaikan'] ?? null,
            'tanggal_evaluasi' => $validated['tanggal_evaluasi'] ?? null,
            'hasil_evaluasi' => $validated['hasil_evaluasi'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.perjanjian-siswa.index')
            ->with('success', 'Perjanjian siswa berhasil diperbarui.');
    }

    public function destroy(BkPerjanjianSiswa $perjanjianSiswa)
    {
        $perjanjianSiswa->delete();

        return redirect()->route('bk.perjanjian-siswa.index')
            ->with('success', 'Perjanjian siswa berhasil dihapus.');
    }
}

