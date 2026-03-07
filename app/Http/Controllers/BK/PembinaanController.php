<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\BkPembinaanSiswa;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PembinaanController extends Controller
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

        $pembinaan = BkPembinaanSiswa::query()
            ->with(['siswa', 'kelas'])
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->whereHas('siswa', function ($sQuery) use ($q) {
                        $sQuery->where('nama_siswa', 'like', "%{$q}%")
                            ->orWhere('nis', 'like', "%{$q}%")
                            ->orWhere('nisn', 'like', "%{$q}%");
                    })
                        ->orWhere('bentuk_pembinaan', 'like', "%{$q}%")
                        ->orWhere('tujuan', 'like', "%{$q}%")
                        ->orWhere('catatan', 'like', "%{$q}%");
                });
            })
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', $kelasId))
            ->when($status !== '', fn($builder) => $builder->where('status', $status))
            ->when($tanggalDari, fn($builder) => $builder->whereDate('tanggal_mulai', '>=', $tanggalDari))
            ->when($tanggalSampai, fn($builder) => $builder->whereDate('tanggal_mulai', '<=', $tanggalSampai))
            ->latest('tanggal_mulai')
            ->latest('id')
            ->paginate($limit)
            ->withQueryString();

        $kelasOptions = DataKelas::orderBy('nama_kelas')->get();
        $siswaOptions = DataSiswa::with('kelas')->orderBy('nama_siswa')->get();

        $statusCounts = [];
        foreach (BkPembinaanSiswa::statusOptions() as $st) {
            $statusCounts[$st] = BkPembinaanSiswa::where('status', $st)->count();
        }

        return view('bk.pembinaan.index', [
            'pembinaan' => $pembinaan,
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'statusOptions' => BkPembinaanSiswa::statusOptions(),
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
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'bentuk_pembinaan' => 'required|string|max:120',
            'tujuan' => 'required|string|max:180',
            'status' => 'required|in:' . implode(',', BkPembinaanSiswa::statusOptions()),
            'catatan' => 'nullable|string',
            'hasil' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        BkPembinaanSiswa::create([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif->id,
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'bentuk_pembinaan' => $validated['bentuk_pembinaan'],
            'tujuan' => $validated['tujuan'],
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
            'hasil' => $validated['hasil'] ?? null,
            'rekomendasi' => $validated['rekomendasi'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.pembinaan.index')
            ->with('success', 'Laporan pembinaan berhasil ditambahkan.');
    }

    public function update(Request $request, BkPembinaanSiswa $pembinaan)
    {
        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'bentuk_pembinaan' => 'required|string|max:120',
            'tujuan' => 'required|string|max:180',
            'status' => 'required|in:' . implode(',', BkPembinaanSiswa::statusOptions()),
            'catatan' => 'nullable|string',
            'hasil' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        $pembinaan->update([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif?->id ?? $pembinaan->data_tahun_pelajaran_id,
            'tanggal_mulai' => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'bentuk_pembinaan' => $validated['bentuk_pembinaan'],
            'tujuan' => $validated['tujuan'],
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
            'hasil' => $validated['hasil'] ?? null,
            'rekomendasi' => $validated['rekomendasi'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.pembinaan.index')
            ->with('success', 'Laporan pembinaan berhasil diperbarui.');
    }

    public function destroy(BkPembinaanSiswa $pembinaan)
    {
        $pembinaan->delete();

        return redirect()->route('bk.pembinaan.index')
            ->with('success', 'Laporan pembinaan berhasil dihapus.');
    }
}

