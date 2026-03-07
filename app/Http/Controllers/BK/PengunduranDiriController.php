<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\BkPengunduranDiri;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengunduranDiriController extends Controller
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

        $pengunduran = BkPengunduranDiri::query()
            ->with(['siswa', 'kelas'])
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->whereHas('siswa', function ($sQuery) use ($q) {
                        $sQuery->where('nama_siswa', 'like', "%{$q}%")
                            ->orWhere('nis', 'like', "%{$q}%")
                            ->orWhere('nisn', 'like', "%{$q}%");
                    })->orWhere('alasan_pengunduran_diri', 'like', "%{$q}%")
                        ->orWhere('keterangan', 'like', "%{$q}%");
                });
            })
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', $kelasId))
            ->when($status !== '', fn($builder) => $builder->where('status', $status))
            ->when($tanggalDari, fn($builder) => $builder->whereDate('tanggal_pengajuan', '>=', $tanggalDari))
            ->when($tanggalSampai, fn($builder) => $builder->whereDate('tanggal_pengajuan', '<=', $tanggalSampai))
            ->latest('tanggal_pengajuan')
            ->latest('id')
            ->paginate($limit)
            ->withQueryString();

        $kelasOptions = DataKelas::orderBy('nama_kelas')->get();
        $siswaOptions = DataSiswa::with('kelas')->orderBy('nama_siswa')->get();

        $statusCounts = [];
        foreach (BkPengunduranDiri::statusOptions() as $st) {
            $statusCounts[$st] = BkPengunduranDiri::where('status', $st)->count();
        }

        return view('bk.pengunduran_diri.index', [
            'pengunduran' => $pengunduran,
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'statusOptions' => BkPengunduranDiri::statusOptions(),
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
            'tanggal_pengajuan' => 'required|date',
            'tanggal_efektif' => 'nullable|date|after_or_equal:tanggal_pengajuan',
            'status' => 'required|in:' . implode(',', BkPengunduranDiri::statusOptions()),
            'alasan_pengunduran_diri' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        BkPengunduranDiri::create([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif->id,
            'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
            'tanggal_efektif' => $validated['tanggal_efektif'] ?? null,
            'status' => $validated['status'],
            'alasan_pengunduran_diri' => $validated['alasan_pengunduran_diri'],
            'keterangan' => $validated['keterangan'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.pengunduran-diri.index')
            ->with('success', 'Laporan pengunduran diri berhasil ditambahkan.');
    }

    public function update(Request $request, BkPengunduranDiri $pengunduranDiri)
    {
        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'tanggal_pengajuan' => 'required|date',
            'tanggal_efektif' => 'nullable|date|after_or_equal:tanggal_pengajuan',
            'status' => 'required|in:' . implode(',', BkPengunduranDiri::statusOptions()),
            'alasan_pengunduran_diri' => 'required|string',
            'keterangan' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        $pengunduranDiri->update([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif?->id ?? $pengunduranDiri->data_tahun_pelajaran_id,
            'tanggal_pengajuan' => $validated['tanggal_pengajuan'],
            'tanggal_efektif' => $validated['tanggal_efektif'] ?? null,
            'status' => $validated['status'],
            'alasan_pengunduran_diri' => $validated['alasan_pengunduran_diri'],
            'keterangan' => $validated['keterangan'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.pengunduran-diri.index')
            ->with('success', 'Laporan pengunduran diri berhasil diperbarui.');
    }

    public function destroy(BkPengunduranDiri $pengunduranDiri)
    {
        $pengunduranDiri->delete();

        return redirect()->route('bk.pengunduran-diri.index')
            ->with('success', 'Laporan pengunduran diri berhasil dihapus.');
    }
}

