<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\DataBk;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataBkController extends Controller
{
    public function index(Request $request)
    {
        $roleContext = $this->roleContext();
        $allowedKelasIds = $this->allowedKelasIds($roleContext);

        $limit = (int) $request->get('limit', 10);
        if (!in_array($limit, [10, 25, 50, 100], true)) {
            $limit = 10;
        }

        $q = trim((string) $request->get('q', ''));
        $kelasId = $request->get('kelas_id');
        $status = trim((string) $request->get('status', ''));
        $tanggalDari = $request->get('tanggal_dari');
        $tanggalSampai = $request->get('tanggal_sampai');

        $query = DataBk::query()
            ->with(['siswa', 'kelas', 'tahunPelajaran', 'creator'])
            ->when($allowedKelasIds !== null, fn($qBuilder) => $qBuilder->whereIn('data_kelas_id', $allowedKelasIds))
            ->when($q !== '', function ($qBuilder) use ($q) {
                $qBuilder->where(function ($w) use ($q) {
                    $w->whereHas('siswa', function ($sQuery) use ($q) {
                        $sQuery->where('nama_siswa', 'like', "%{$q}%")
                            ->orWhere('nis', 'like', "%{$q}%")
                            ->orWhere('nisn', 'like', "%{$q}%");
                    })
                        ->orWhere('jenis_kasus', 'like', "%{$q}%")
                        ->orWhere('deskripsi_masalah', 'like', "%{$q}%")
                        ->orWhere('tindak_lanjut', 'like', "%{$q}%");
                });
            })
            ->when($kelasId, fn($qBuilder) => $qBuilder->where('data_kelas_id', $kelasId))
            ->when($status !== '', fn($qBuilder) => $qBuilder->where('status', $status))
            ->when($tanggalDari, fn($qBuilder) => $qBuilder->whereDate('tanggal', '>=', $tanggalDari))
            ->when($tanggalSampai, fn($qBuilder) => $qBuilder->whereDate('tanggal', '<=', $tanggalSampai))
            ->latest('tanggal')
            ->latest('id');

        $dataBk = $query->paginate($limit)->withQueryString();

        $kelasOptions = DataKelas::query()
            ->when($allowedKelasIds !== null, fn($qBuilder) => $qBuilder->whereIn('id', $allowedKelasIds))
            ->orderBy('nama_kelas')
            ->get();

        $siswaOptions = DataSiswa::query()
            ->when($allowedKelasIds !== null, fn($qBuilder) => $qBuilder->whereIn('data_kelas_id', $allowedKelasIds))
            ->orderBy('nama_siswa')
            ->get();

        return view('bk.index', [
            'dataBk' => $dataBk,
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'statusOptions' => DataBk::statusOptions(),
            'routeBase' => $this->routeBase(),
            'roleContext' => $roleContext,
            'canEdit' => $this->canEdit($roleContext),
            'canDelete' => $this->canDelete($roleContext),
            'limit' => $limit,
            'q' => $q,
            'kelasId' => $kelasId,
            'status' => $status,
            'tanggalDari' => $tanggalDari,
            'tanggalSampai' => $tanggalSampai,
            'tahunAktif' => DataTahunPelajaran::where('status_aktif', 1)->first(),
        ]);
    }

    public function store(Request $request)
    {
        $roleContext = $this->roleContext();
        $payload = $this->validatedPayload($request, $roleContext);
        $payload['created_by'] = Auth::id();
        $payload['updated_by'] = Auth::id();

        DataBk::create($payload);

        return redirect()->route($this->routeBase() . '.index')
            ->with('success', 'Catatan BK berhasil ditambahkan.');
    }

    public function update(Request $request, DataBk $bk)
    {
        $roleContext = $this->roleContext();
        if (!$this->canEdit($roleContext)) {
            abort(403);
        }

        $this->ensureCanAccessEntry($bk, $roleContext);

        $payload = $this->validatedPayload($request, $roleContext);
        $payload['updated_by'] = Auth::id();
        $bk->update($payload);

        return redirect()->route($this->routeBase() . '.index')
            ->with('success', 'Catatan BK berhasil diperbarui.');
    }

    public function destroy(DataBk $bk)
    {
        $roleContext = $this->roleContext();
        if (!$this->canDelete($roleContext)) {
            abort(403);
        }

        $this->ensureCanAccessEntry($bk, $roleContext);
        $bk->delete();

        return redirect()->route($this->routeBase() . '.index')
            ->with('success', 'Catatan BK berhasil dihapus.');
    }

    public function riwayat(DataSiswa $siswa)
    {
        $roleContext = $this->roleContext();
        $allowedKelasIds = $this->allowedKelasIds($roleContext);
        if ($allowedKelasIds !== null && !in_array((int) $siswa->data_kelas_id, $allowedKelasIds, true)) {
            abort(403);
        }

        $riwayat = DataBk::with(['kelas', 'tahunPelajaran', 'creator'])
            ->where('data_siswa_id', $siswa->id)
            ->when($allowedKelasIds !== null, fn($qBuilder) => $qBuilder->whereIn('data_kelas_id', $allowedKelasIds))
            ->latest('tanggal')
            ->latest('id')
            ->get();

        return view('bk.riwayat', [
            'siswa' => $siswa->load('kelas'),
            'riwayat' => $riwayat,
            'routeBase' => $this->routeBase(),
        ]);
    }

    private function validatedPayload(Request $request, string $roleContext): array
    {
        $allowedKelasIds = $this->allowedKelasIds($roleContext);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'tanggal' => 'required|date',
            'jenis_kasus' => 'required|string|max:150',
            'deskripsi_masalah' => 'required|string',
            'tindak_lanjut' => 'nullable|string',
            'status' => 'required|in:' . implode(',', DataBk::statusOptions()),
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        if ($allowedKelasIds !== null && !in_array((int) $siswa->data_kelas_id, $allowedKelasIds, true)) {
            abort(403, 'Anda tidak berhak menambah/mengubah BK untuk siswa ini.');
        }

        return [
            'data_siswa_id' => $siswa->id,
            'data_kelas_id' => $siswa->data_kelas_id,
            'data_tahun_pelajaran_id' => $tahunAktif->id,
            'tanggal' => $validated['tanggal'],
            'jenis_kasus' => $validated['jenis_kasus'],
            'deskripsi_masalah' => $validated['deskripsi_masalah'],
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? null,
            'status' => $validated['status'],
        ];
    }

    private function ensureCanAccessEntry(DataBk $bk, string $roleContext): void
    {
        $allowedKelasIds = $this->allowedKelasIds($roleContext);
        if ($allowedKelasIds === null) {
            return;
        }

        if (!in_array((int) $bk->data_kelas_id, $allowedKelasIds, true)) {
            abort(403);
        }
    }

    private function routeBase(): string
    {
        $name = request()->route()?->getName() ?? '';

        if (str_starts_with($name, 'admin.')) {
            return 'admin.bk';
        }

        if (str_starts_with($name, 'guru.wali-kelas.')) {
            return 'guru.wali-kelas.bk';
        }

        return 'bk.data-bk';
    }

    private function roleContext(): string
    {
        $name = request()->route()?->getName() ?? '';
        if (str_starts_with($name, 'admin.')) {
            return 'admin';
        }
        if (str_starts_with($name, 'guru.wali-kelas.')) {
            return 'wali';
        }
        return 'bk';
    }

    private function canEdit(string $roleContext): bool
    {
        return in_array($roleContext, ['admin', 'bk'], true);
    }

    private function canDelete(string $roleContext): bool
    {
        return in_array($roleContext, ['admin', 'bk'], true);
    }

    private function allowedKelasIds(string $roleContext): ?array
    {
        if ($roleContext !== 'wali') {
            return null;
        }

        return DataKelas::where('wali_kelas_id', Auth::id())
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
    }
}
