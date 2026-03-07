<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\BkPemanggilanOrangTua;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PemanggilanOrangTuaController extends Controller
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

        $panggilan = BkPemanggilanOrangTua::query()
            ->with(['siswa', 'kelas'])
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->whereHas('siswa', function ($sQuery) use ($q) {
                        $sQuery->where('nama_siswa', 'like', "%{$q}%")
                            ->orWhere('nis', 'like', "%{$q}%")
                            ->orWhere('nisn', 'like', "%{$q}%");
                    })
                        ->orWhere('nomor_surat', 'like', "%{$q}%")
                        ->orWhere('alasan_pemanggilan', 'like', "%{$q}%")
                        ->orWhere('nama_wali_hadir', 'like', "%{$q}%");
                });
            })
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', $kelasId))
            ->when($status !== '', fn($builder) => $builder->where('status', $status))
            ->when($tanggalDari, fn($builder) => $builder->whereDate('tanggal_panggilan', '>=', $tanggalDari))
            ->when($tanggalSampai, fn($builder) => $builder->whereDate('tanggal_panggilan', '<=', $tanggalSampai))
            ->latest('tanggal_panggilan')
            ->latest('id')
            ->paginate($limit)
            ->withQueryString();

        $kelasOptions = DataKelas::orderBy('nama_kelas')->get();
        $siswaOptions = DataSiswa::with('kelas')->orderBy('nama_siswa')->get();

        $statusCounts = [];
        foreach (BkPemanggilanOrangTua::statusOptions() as $st) {
            $statusCounts[$st] = BkPemanggilanOrangTua::where('status', $st)->count();
        }

        return view('bk.pemanggilan_ortu.index', [
            'panggilan' => $panggilan,
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'statusOptions' => BkPemanggilanOrangTua::statusOptions(),
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
            'tanggal_panggilan' => 'required|date',
            'nomor_surat' => 'nullable|string|max:80',
            'nama_wali_hadir' => 'nullable|string|max:150',
            'hubungan_wali' => 'nullable|string|max:80',
            'status' => 'required|in:' . implode(',', BkPemanggilanOrangTua::statusOptions()),
            'alasan_pemanggilan' => 'required|string',
            'hasil_pertemuan' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        BkPemanggilanOrangTua::create([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif->id,
            'tanggal_panggilan' => $validated['tanggal_panggilan'],
            'nomor_surat' => $validated['nomor_surat'] ?? null,
            'nama_wali_hadir' => $validated['nama_wali_hadir'] ?? null,
            'hubungan_wali' => $validated['hubungan_wali'] ?? null,
            'status' => $validated['status'],
            'alasan_pemanggilan' => $validated['alasan_pemanggilan'],
            'hasil_pertemuan' => $validated['hasil_pertemuan'] ?? null,
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.pemanggilan-ortu.index')
            ->with('success', 'Laporan pemanggilan orang tua berhasil ditambahkan.');
    }

    public function update(Request $request, BkPemanggilanOrangTua $pemanggilanOrtu)
    {
        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'tanggal_panggilan' => 'required|date',
            'nomor_surat' => 'nullable|string|max:80',
            'nama_wali_hadir' => 'nullable|string|max:150',
            'hubungan_wali' => 'nullable|string|max:80',
            'status' => 'required|in:' . implode(',', BkPemanggilanOrangTua::statusOptions()),
            'alasan_pemanggilan' => 'required|string',
            'hasil_pertemuan' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        $pemanggilanOrtu->update([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif?->id ?? $pemanggilanOrtu->data_tahun_pelajaran_id,
            'tanggal_panggilan' => $validated['tanggal_panggilan'],
            'nomor_surat' => $validated['nomor_surat'] ?? null,
            'nama_wali_hadir' => $validated['nama_wali_hadir'] ?? null,
            'hubungan_wali' => $validated['hubungan_wali'] ?? null,
            'status' => $validated['status'],
            'alasan_pemanggilan' => $validated['alasan_pemanggilan'],
            'hasil_pertemuan' => $validated['hasil_pertemuan'] ?? null,
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.pemanggilan-ortu.index')
            ->with('success', 'Laporan pemanggilan orang tua berhasil diperbarui.');
    }

    public function destroy(BkPemanggilanOrangTua $pemanggilanOrtu)
    {
        $pemanggilanOrtu->delete();

        return redirect()->route('bk.pemanggilan-ortu.index')
            ->with('success', 'Laporan pemanggilan orang tua berhasil dihapus.');
    }
}

