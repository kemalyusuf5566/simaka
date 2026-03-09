<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\DataKelas;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use App\Models\HubinDudi;
use App\Models\HubinMonitoringPklLog;
use App\Models\HubinPenempatanPkl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HubinSkeletonController extends Controller
{
    public function dataDudi(Request $request): View
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);

        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status', '');
        $limit = (int) $request->get('limit', 20);
        if (!in_array($limit, [10, 20, 50, 100], true)) {
            $limit = 20;
        }

        $dudi = HubinDudi::query()
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('nama_instansi', 'like', "%{$q}%")
                        ->orWhere('bidang_usaha', 'like', "%{$q}%")
                        ->orWhere('kontak_person', 'like', "%{$q}%")
                        ->orWhere('telepon', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', fn($builder) => $builder->where('status_aktif', $status === 'aktif'))
            ->orderBy('nama_instansi')
            ->paginate($limit)
            ->withQueryString();

        return view('bk.hubin_data_dudi', [
            'dudi' => $dudi,
            'q' => $q,
            'status' => $status,
            'limit' => $limit,
            'routeBase' => $this->routeBase(),
            'canManageMaster' => $this->canManageMaster($roleContext),
        ]);
    }

    public function storeDudi(Request $request): RedirectResponse
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);
        if (!$this->canManageMaster($roleContext)) {
            abort(403);
        }

        $validated = $request->validate([
            'nama_instansi' => 'required|string|max:160',
            'bidang_usaha' => 'nullable|string|max:120',
            'alamat' => 'nullable|string|max:220',
            'kontak_person' => 'nullable|string|max:120',
            'telepon' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:120',
            'status_aktif' => 'nullable|boolean',
            'catatan' => 'nullable|string',
        ]);

        HubinDudi::create([
            ...$validated,
            'status_aktif' => (bool) ($validated['status_aktif'] ?? true),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route($this->routeBase() . '.hubin.data-dudi.index')
            ->with('success', 'Data DU/DI berhasil ditambahkan.');
    }

    public function updateDudi(Request $request, HubinDudi $dudi): RedirectResponse
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);
        if (!$this->canManageMaster($roleContext)) {
            abort(403);
        }

        $validated = $request->validate([
            'nama_instansi' => 'required|string|max:160',
            'bidang_usaha' => 'nullable|string|max:120',
            'alamat' => 'nullable|string|max:220',
            'kontak_person' => 'nullable|string|max:120',
            'telepon' => 'nullable|string|max:40',
            'email' => 'nullable|email|max:120',
            'status_aktif' => 'nullable|boolean',
            'catatan' => 'nullable|string',
        ]);

        $dudi->update([
            ...$validated,
            'status_aktif' => (bool) ($validated['status_aktif'] ?? false),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route($this->routeBase() . '.hubin.data-dudi.index')
            ->with('success', 'Data DU/DI berhasil diperbarui.');
    }

    public function destroyDudi(HubinDudi $dudi): RedirectResponse
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);
        if (!$this->canManageMaster($roleContext)) {
            abort(403);
        }

        if ($dudi->penempatan()->exists()) {
            return redirect()->route($this->routeBase() . '.hubin.data-dudi.index')
                ->with('error', 'Data DU/DI tidak bisa dihapus karena sudah dipakai pada penempatan.');
        }

        $dudi->delete();

        return redirect()->route($this->routeBase() . '.hubin.data-dudi.index')
            ->with('success', 'Data DU/DI berhasil dihapus.');
    }

    public function penempatanPkl(Request $request): View
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);
        $allowedKelasIds = $this->allowedKelasIds($roleContext);

        $q = trim((string) $request->get('q', ''));
        $status = trim((string) $request->get('status', ''));
        $kelasId = $request->get('kelas_id');
        $dudiId = $request->get('dudi_id');
        $limit = (int) $request->get('limit', 20);
        if (!in_array($limit, [10, 20, 50, 100], true)) {
            $limit = 20;
        }

        $penempatan = HubinPenempatanPkl::query()
            ->with(['siswa', 'kelas', 'dudi', 'tahunPelajaran'])
            ->when($allowedKelasIds !== null, fn($builder) => $builder->whereIn('data_kelas_id', $allowedKelasIds))
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', (int) $kelasId))
            ->when($dudiId, fn($builder) => $builder->where('hubin_dudi_id', (int) $dudiId))
            ->when($status !== '', fn($builder) => $builder->where('status_penempatan', $status))
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->whereHas('siswa', fn($s) => $s->where('nama_siswa', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%"))
                        ->orWhereHas('dudi', fn($d) => $d->where('nama_instansi', 'like', "%{$q}%"));
                });
            })
            ->latest('id')
            ->paginate($limit)
            ->withQueryString();

        $kelasOptions = DataKelas::query()
            ->when($allowedKelasIds !== null, fn($builder) => $builder->whereIn('id', $allowedKelasIds))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $siswaOptions = DataSiswa::query()
            ->when($allowedKelasIds !== null, fn($builder) => $builder->whereIn('data_kelas_id', $allowedKelasIds))
            ->with('kelas')
            ->orderBy('nama_siswa')
            ->get();

        $dudiOptions = HubinDudi::where('status_aktif', true)->orderBy('nama_instansi')->get();

        return view('bk.hubin_penempatan_pkl', [
            'penempatan' => $penempatan,
            'kelasOptions' => $kelasOptions,
            'siswaOptions' => $siswaOptions,
            'dudiOptions' => $dudiOptions,
            'statusOptions' => HubinPenempatanPkl::statusOptions(),
            'routeBase' => $this->routeBase(),
            'q' => $q,
            'status' => $status,
            'kelasId' => $kelasId,
            'dudiId' => $dudiId,
            'limit' => $limit,
        ]);
    }

    public function storePenempatan(Request $request): RedirectResponse
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();
        $allowedKelasIds = $this->allowedKelasIds($roleContext);

        $validated = $request->validate([
            'data_siswa_id' => 'required|exists:data_siswa,id',
            'hubin_dudi_id' => 'required|exists:hubin_dudi,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_penempatan' => 'required|in:' . implode(',', HubinPenempatanPkl::statusOptions()),
            'catatan' => 'nullable|string',
        ]);

        $siswa = DataSiswa::findOrFail($validated['data_siswa_id']);
        $this->ensureSiswaAccessible($siswa->data_kelas_id, $allowedKelasIds);

        HubinPenempatanPkl::updateOrCreate(
            [
                'data_siswa_id' => $siswa->id,
                'data_tahun_pelajaran_id' => $tahunAktif->id,
            ],
            [
                'data_kelas_id' => $siswa->data_kelas_id,
                'hubin_dudi_id' => $validated['hubin_dudi_id'],
                'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
                'status_penempatan' => $validated['status_penempatan'],
                'catatan' => $validated['catatan'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]
        );

        return redirect()->route($this->routeBase() . '.hubin.penempatan-pkl.index')
            ->with('success', 'Data penempatan PKL berhasil disimpan.');
    }

    public function updatePenempatan(Request $request, HubinPenempatanPkl $penempatan): RedirectResponse
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);
        $allowedKelasIds = $this->allowedKelasIds($roleContext);
        $this->ensureSiswaAccessible($penempatan->data_kelas_id, $allowedKelasIds);

        $validated = $request->validate([
            'hubin_dudi_id' => 'required|exists:hubin_dudi,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status_penempatan' => 'required|in:' . implode(',', HubinPenempatanPkl::statusOptions()),
            'catatan' => 'nullable|string',
        ]);

        $penempatan->update([
            'hubin_dudi_id' => $validated['hubin_dudi_id'],
            'tanggal_mulai' => $validated['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $validated['tanggal_selesai'] ?? null,
            'status_penempatan' => $validated['status_penempatan'],
            'catatan' => $validated['catatan'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route($this->routeBase() . '.hubin.penempatan-pkl.index')
            ->with('success', 'Data penempatan PKL berhasil diperbarui.');
    }

    public function destroyPenempatan(HubinPenempatanPkl $penempatan): RedirectResponse
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);
        if (!in_array($roleContext, ['admin', 'bk'], true)) {
            abort(403);
        }

        $penempatan->delete();

        return redirect()->route($this->routeBase() . '.hubin.penempatan-pkl.index')
            ->with('success', 'Data penempatan PKL berhasil dihapus.');
    }

    public function monitoringPkl(Request $request): View
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);
        $allowedKelasIds = $this->allowedKelasIds($roleContext);

        $status = trim((string) $request->get('status', ''));
        $penempatanId = $request->get('penempatan_id');
        $q = trim((string) $request->get('q', ''));
        $limit = (int) $request->get('limit', 20);
        if (!in_array($limit, [10, 20, 50, 100], true)) {
            $limit = 20;
        }

        $logs = HubinMonitoringPklLog::query()
            ->with(['penempatan.siswa', 'penempatan.kelas', 'penempatan.dudi'])
            ->when($allowedKelasIds !== null, function ($builder) use ($allowedKelasIds) {
                $builder->whereHas('penempatan', fn($p) => $p->whereIn('data_kelas_id', $allowedKelasIds));
            })
            ->when($penempatanId, fn($builder) => $builder->where('hubin_penempatan_pkl_id', (int) $penempatanId))
            ->when($status !== '', fn($builder) => $builder->where('status_monitoring', $status))
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('topik_monitoring', 'like', "%{$q}%")
                        ->orWhere('catatan', 'like', "%{$q}%")
                        ->orWhereHas('penempatan.siswa', fn($s) => $s->where('nama_siswa', 'like', "%{$q}%"));
                });
            })
            ->latest('tanggal_monitoring')
            ->latest('id')
            ->paginate($limit)
            ->withQueryString();

        $penempatanOptions = HubinPenempatanPkl::query()
            ->with(['siswa', 'kelas', 'dudi'])
            ->when($allowedKelasIds !== null, fn($builder) => $builder->whereIn('data_kelas_id', $allowedKelasIds))
            ->orderByDesc('id')
            ->get();

        return view('bk.hubin_monitoring_pkl', [
            'logs' => $logs,
            'penempatanOptions' => $penempatanOptions,
            'statusOptions' => HubinMonitoringPklLog::statusOptions(),
            'routeBase' => $this->routeBase(),
            'status' => $status,
            'penempatanId' => $penempatanId,
            'q' => $q,
            'limit' => $limit,
            'canDelete' => in_array($roleContext, ['admin', 'bk'], true),
        ]);
    }

    public function storeMonitoring(Request $request): RedirectResponse
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);
        $allowedKelasIds = $this->allowedKelasIds($roleContext);

        $validated = $request->validate([
            'hubin_penempatan_pkl_id' => 'required|exists:hubin_penempatan_pkl,id',
            'tanggal_monitoring' => 'required|date',
            'status_monitoring' => 'required|in:' . implode(',', HubinMonitoringPklLog::statusOptions()),
            'topik_monitoring' => 'nullable|string|max:150',
            'catatan' => 'nullable|string',
            'skor_kinerja' => 'nullable|integer|min:0|max:100',
            'tindak_lanjut' => 'nullable|string',
        ]);

        $penempatan = HubinPenempatanPkl::findOrFail($validated['hubin_penempatan_pkl_id']);
        $this->ensureSiswaAccessible($penempatan->data_kelas_id, $allowedKelasIds);

        HubinMonitoringPklLog::create([
            ...$validated,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route($this->routeBase() . '.hubin.monitoring-pkl.index')
            ->with('success', 'Log monitoring PKL berhasil ditambahkan.');
    }

    public function updateMonitoring(Request $request, HubinMonitoringPklLog $monitoring): RedirectResponse
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);
        $allowedKelasIds = $this->allowedKelasIds($roleContext);

        $penempatan = HubinPenempatanPkl::findOrFail($monitoring->hubin_penempatan_pkl_id);
        $this->ensureSiswaAccessible($penempatan->data_kelas_id, $allowedKelasIds);

        $validated = $request->validate([
            'tanggal_monitoring' => 'required|date',
            'status_monitoring' => 'required|in:' . implode(',', HubinMonitoringPklLog::statusOptions()),
            'topik_monitoring' => 'nullable|string|max:150',
            'catatan' => 'nullable|string',
            'skor_kinerja' => 'nullable|integer|min:0|max:100',
            'tindak_lanjut' => 'nullable|string',
        ]);

        $monitoring->update([
            ...$validated,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route($this->routeBase() . '.hubin.monitoring-pkl.index')
            ->with('success', 'Log monitoring PKL berhasil diperbarui.');
    }

    public function destroyMonitoring(HubinMonitoringPklLog $monitoring): RedirectResponse
    {
        $roleContext = $this->roleContext();
        $this->ensureHubinAccess($roleContext);
        if (!in_array($roleContext, ['admin', 'bk'], true)) {
            abort(403);
        }

        $monitoring->delete();

        return redirect()->route($this->routeBase() . '.hubin.monitoring-pkl.index')
            ->with('success', 'Log monitoring PKL berhasil dihapus.');
    }

    private function routeBase(): string
    {
        $name = request()->route()?->getName() ?? '';
        if (str_starts_with($name, 'admin.')) {
            return 'admin.bk';
        }
        if (str_starts_with($name, 'guru.')) {
            return 'guru';
        }
        return 'bk';
    }

    private function roleContext(): string
    {
        $name = request()->route()?->getName() ?? '';
        if (str_starts_with($name, 'admin.')) {
            return 'admin';
        }
        if (str_starts_with($name, 'guru.')) {
            return 'guru';
        }
        return 'bk';
    }

    private function canManageMaster(string $roleContext): bool
    {
        return in_array($roleContext, ['admin', 'bk'], true);
    }

    private function ensureHubinAccess(string $roleContext): void
    {
        if (in_array($roleContext, ['admin', 'bk'], true)) {
            return;
        }

        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        if ($user->hasRole('pembimbing_pkl') || $user->hasRole('wali_kelas') || $this->isWaliKelasUser($user->id)) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke modul Hubin.');
    }

    private function allowedKelasIds(string $roleContext): ?array
    {
        if ($roleContext !== 'guru') {
            return null;
        }

        $user = Auth::user();
        if (!$user) {
            return [];
        }

        if ($user->hasRole('pembimbing_pkl')) {
            return null;
        }

        if ($user->hasRole('wali_kelas') || $this->isWaliKelasUser($user->id)) {
            return DataKelas::where('wali_kelas_id', $user->id)
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->all();
        }

        return [];
    }

    private function ensureSiswaAccessible(int $kelasId, ?array $allowedKelasIds): void
    {
        if ($allowedKelasIds === null) {
            return;
        }

        if (!in_array((int) $kelasId, $allowedKelasIds, true)) {
            abort(403);
        }
    }

    private function isWaliKelasUser(int $userId): bool
    {
        return DataKelas::where('wali_kelas_id', $userId)->exists();
    }
}
