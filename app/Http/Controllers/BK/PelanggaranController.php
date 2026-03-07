<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\BkJenisPelanggaran;
use App\Models\BkPelanggaranSiswa;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $limit = (int) $request->get('limit', 10);
        if (!in_array($limit, [10, 25, 50, 100], true)) {
            $limit = 10;
        }

        $q = trim((string) $request->get('q', ''));
        $kelasId = $request->get('kelas_id');
        $jenisId = $request->get('jenis_id');
        $status = trim((string) $request->get('status', ''));
        $tanggalDari = $request->get('tanggal_dari');
        $tanggalSampai = $request->get('tanggal_sampai');

        $pelanggaran = BkPelanggaranSiswa::query()
            ->with(['siswa', 'kelas', 'jenis'])
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->whereHas('siswa', function ($sQuery) use ($q) {
                        $sQuery->where('nama_siswa', 'like', "%{$q}%")
                            ->orWhere('nis', 'like', "%{$q}%")
                            ->orWhere('nisn', 'like', "%{$q}%");
                    })->orWhereHas('jenis', function ($jQuery) use ($q) {
                        $jQuery->where('nama_pelanggaran', 'like', "%{$q}%")
                            ->orWhere('kode', 'like', "%{$q}%");
                    })->orWhere('kronologi', 'like', "%{$q}%");
                });
            })
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', $kelasId))
            ->when($jenisId, fn($builder) => $builder->where('bk_jenis_pelanggaran_id', $jenisId))
            ->when($status !== '', fn($builder) => $builder->where('status', $status))
            ->when($tanggalDari, fn($builder) => $builder->whereDate('tanggal', '>=', $tanggalDari))
            ->when($tanggalSampai, fn($builder) => $builder->whereDate('tanggal', '<=', $tanggalSampai))
            ->latest('tanggal')
            ->latest('id')
            ->paginate($limit)
            ->withQueryString();

        $jenis = BkJenisPelanggaran::orderBy('nama_pelanggaran')->get();
        $kelasOptions = DataKelas::orderBy('nama_kelas')->get();
        $siswaOptions = DataSiswa::with('kelas')->orderBy('nama_siswa')->get();

        $topSiswa = BkPelanggaranSiswa::query()
            ->selectRaw('data_siswa_id, SUM(poin) as total_poin')
            ->with('siswa')
            ->groupBy('data_siswa_id')
            ->orderByDesc('total_poin')
            ->limit(5)
            ->get();

        return view('bk.pelanggaran.index', [
            'pelanggaran' => $pelanggaran,
            'jenis' => $jenis,
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'statusOptions' => BkPelanggaranSiswa::statusOptions(),
            'topSiswa' => $topSiswa,
            'limit' => $limit,
            'q' => $q,
            'kelasId' => $kelasId,
            'jenisId' => $jenisId,
            'status' => $status,
            'tanggalDari' => $tanggalDari,
            'tanggalSampai' => $tanggalSampai,
        ]);
    }

    public function storeJenis(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:30|unique:bk_jenis_pelanggaran,kode',
            'nama_pelanggaran' => 'required|string|max:150',
            'poin_default' => 'required|integer|min:0|max:999',
            'status_aktif' => 'nullable|boolean',
        ]);

        BkJenisPelanggaran::create([
            'kode' => strtoupper(trim($validated['kode'])),
            'nama_pelanggaran' => $validated['nama_pelanggaran'],
            'poin_default' => $validated['poin_default'],
            'status_aktif' => (bool) ($validated['status_aktif'] ?? true),
        ]);

        return redirect()->route('bk.pelanggaran.index')
            ->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    public function updateJenis(Request $request, BkJenisPelanggaran $jenis)
    {
        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:30',
                Rule::unique('bk_jenis_pelanggaran', 'kode')->ignore($jenis->id),
            ],
            'nama_pelanggaran' => 'required|string|max:150',
            'poin_default' => 'required|integer|min:0|max:999',
            'status_aktif' => 'nullable|boolean',
        ]);

        $jenis->update([
            'kode' => strtoupper(trim($validated['kode'])),
            'nama_pelanggaran' => $validated['nama_pelanggaran'],
            'poin_default' => $validated['poin_default'],
            'status_aktif' => (bool) ($validated['status_aktif'] ?? false),
        ]);

        return redirect()->route('bk.pelanggaran.index')
            ->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    public function destroyJenis(BkJenisPelanggaran $jenis)
    {
        if ($jenis->pelanggaranSiswa()->exists()) {
            return redirect()->route('bk.pelanggaran.index')
                ->with('error', 'Jenis pelanggaran tidak bisa dihapus karena sudah dipakai.');
        }

        $jenis->delete();

        return redirect()->route('bk.pelanggaran.index')
            ->with('success', 'Jenis pelanggaran berhasil dihapus.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'bk_jenis_pelanggaran_id' => 'required|exists:bk_jenis_pelanggaran,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:' . implode(',', BkPelanggaranSiswa::statusOptions()),
            'poin' => 'nullable|integer|min:0|max:999',
            'kronologi' => 'nullable|string',
            'tindakan' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $jenis = BkJenisPelanggaran::findOrFail($validated['bk_jenis_pelanggaran_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        BkPelanggaranSiswa::create([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif->id,
            'bk_jenis_pelanggaran_id' => $jenis->id,
            'tanggal' => $validated['tanggal'],
            'status' => $validated['status'],
            'poin' => $validated['poin'] ?? $jenis->poin_default,
            'kronologi' => $validated['kronologi'] ?? null,
            'tindakan' => $validated['tindakan'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.pelanggaran.index')
            ->with('success', 'Pelanggaran siswa berhasil ditambahkan.');
    }

    public function update(Request $request, BkPelanggaranSiswa $pelanggaran)
    {
        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'bk_jenis_pelanggaran_id' => 'required|exists:bk_jenis_pelanggaran,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:' . implode(',', BkPelanggaranSiswa::statusOptions()),
            'poin' => 'required|integer|min:0|max:999',
            'kronologi' => 'nullable|string',
            'tindakan' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        $pelanggaran->update([
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif?->id ?? $pelanggaran->data_tahun_pelajaran_id,
            'bk_jenis_pelanggaran_id' => $validated['bk_jenis_pelanggaran_id'],
            'tanggal' => $validated['tanggal'],
            'status' => $validated['status'],
            'poin' => $validated['poin'],
            'kronologi' => $validated['kronologi'] ?? null,
            'tindakan' => $validated['tindakan'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('bk.pelanggaran.index')
            ->with('success', 'Pelanggaran siswa berhasil diperbarui.');
    }

    public function destroy(BkPelanggaranSiswa $pelanggaran)
    {
        $pelanggaran->delete();

        return redirect()->route('bk.pelanggaran.index')
            ->with('success', 'Pelanggaran siswa berhasil dihapus.');
    }
}

