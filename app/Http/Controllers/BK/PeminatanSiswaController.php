<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\BkPeminatanSiswa;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeminatanSiswaController extends Controller
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

        $peminatan = BkPeminatanSiswa::query()
            ->with(['siswa', 'kelas'])
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->whereHas('siswa', function ($sQuery) use ($q) {
                        $sQuery->where('nama_siswa', 'like', "%{$q}%")
                            ->orWhere('nis', 'like', "%{$q}%")
                            ->orWhere('nisn', 'like', "%{$q}%");
                    })
                        ->orWhere('minat_utama', 'like', "%{$q}%")
                        ->orWhere('minat_alternatif', 'like', "%{$q}%")
                        ->orWhere('rencana_lanjutan', 'like', "%{$q}%");
                });
            })
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', $kelasId))
            ->when($status !== '', fn($builder) => $builder->where('status', $status))
            ->when($tanggalDari, fn($builder) => $builder->whereDate('tanggal_peminatan', '>=', $tanggalDari))
            ->when($tanggalSampai, fn($builder) => $builder->whereDate('tanggal_peminatan', '<=', $tanggalSampai))
            ->latest('tanggal_peminatan')
            ->latest('id')
            ->paginate($limit)
            ->withQueryString();

        $kelasOptions = DataKelas::orderBy('nama_kelas')->get();
        $siswaOptions = DataSiswa::with('kelas')->orderBy('nama_siswa')->get();

        $statusCounts = [];
        foreach (BkPeminatanSiswa::statusOptions() as $st) {
            $statusCounts[$st] = BkPeminatanSiswa::where('status', $st)->count();
        }

        return view('bk.peminatan_siswa.index', [
            'peminatan' => $peminatan,
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'statusOptions' => BkPeminatanSiswa::statusOptions(),
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
            'tanggal_peminatan' => 'required|date',
            'minat_utama' => 'required|string|max:150',
            'minat_alternatif' => 'nullable|string|max:150',
            'rencana_lanjutan' => 'nullable|string|max:180',
            'status' => 'required|in:' . implode(',', BkPeminatanSiswa::statusOptions()),
            'rekomendasi_bk' => 'nullable|string',
            'catatan_orang_tua' => 'nullable|string',
            'catatan_tindak_lanjut' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        BkPeminatanSiswa::create([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif->id,
            'tanggal_peminatan' => $validated['tanggal_peminatan'],
            'minat_utama' => $validated['minat_utama'],
            'minat_alternatif' => $validated['minat_alternatif'] ?? null,
            'rencana_lanjutan' => $validated['rencana_lanjutan'] ?? null,
            'status' => $validated['status'],
            'rekomendasi_bk' => $validated['rekomendasi_bk'] ?? null,
            'catatan_orang_tua' => $validated['catatan_orang_tua'] ?? null,
            'catatan_tindak_lanjut' => $validated['catatan_tindak_lanjut'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.peminatan.index')
            ->with('success', 'Data peminatan siswa berhasil ditambahkan.');
    }

    public function update(Request $request, BkPeminatanSiswa $peminatanSiswa)
    {
        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'tanggal_peminatan' => 'required|date',
            'minat_utama' => 'required|string|max:150',
            'minat_alternatif' => 'nullable|string|max:150',
            'rencana_lanjutan' => 'nullable|string|max:180',
            'status' => 'required|in:' . implode(',', BkPeminatanSiswa::statusOptions()),
            'rekomendasi_bk' => 'nullable|string',
            'catatan_orang_tua' => 'nullable|string',
            'catatan_tindak_lanjut' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        $peminatanSiswa->update([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif?->id ?? $peminatanSiswa->data_tahun_pelajaran_id,
            'tanggal_peminatan' => $validated['tanggal_peminatan'],
            'minat_utama' => $validated['minat_utama'],
            'minat_alternatif' => $validated['minat_alternatif'] ?? null,
            'rencana_lanjutan' => $validated['rencana_lanjutan'] ?? null,
            'status' => $validated['status'],
            'rekomendasi_bk' => $validated['rekomendasi_bk'] ?? null,
            'catatan_orang_tua' => $validated['catatan_orang_tua'] ?? null,
            'catatan_tindak_lanjut' => $validated['catatan_tindak_lanjut'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.peminatan.index')
            ->with('success', 'Data peminatan siswa berhasil diperbarui.');
    }

    public function destroy(BkPeminatanSiswa $peminatanSiswa)
    {
        $peminatanSiswa->delete();

        return redirect()->route('bk.peminatan.index')
            ->with('success', 'Data peminatan siswa berhasil dihapus.');
    }
}

