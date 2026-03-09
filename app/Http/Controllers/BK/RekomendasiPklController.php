<?php

namespace App\Http\Controllers\BK;

use App\Http\Controllers\Controller;
use App\Models\AbsensiJamSiswa;
use App\Models\BkPelanggaranSiswa;
use App\Models\BkSikapSiswa;
use App\Models\DataKelas;
use App\Models\DataKetidakhadiran;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use App\Models\HubinRekomendasiPklSetting;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class RekomendasiPklController extends Controller
{
    private ?array $runtimeConfigCache = null;

    public function index(Request $request)
    {
        $roleContext = $this->roleContext();
        $this->authorizeAccess($roleContext);
        $allowedKelasIds = $this->allowedKelasIds($roleContext);

        $limit = (int) $request->get('limit', 25);
        if (!in_array($limit, [10, 25, 50, 100], true)) {
            $limit = 25;
        }

        $q = trim((string) $request->get('q', ''));
        $kelasId = $request->get('kelas_id');
        $tingkat = strtoupper(trim((string) $request->get('tingkat', '')));
        $grade = strtoupper(trim((string) $request->get('grade', '')));

        if (!in_array($tingkat, ['X', 'XI', 'XII'], true)) {
            $tingkat = '';
        }

        if (!in_array($grade, ['A', 'B', 'C', 'D', 'E'], true)) {
            $grade = '';
        }

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();
        $weights = $this->weights();

        $siswa = DataSiswa::query()
            ->with('kelas')
            ->when($allowedKelasIds !== null, fn($builder) => $builder->whereIn('data_kelas_id', $allowedKelasIds))
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', (int) $kelasId))
            ->when($tingkat !== '', function ($builder) use ($tingkat) {
                $builder->whereHas('kelas', function ($kelasQuery) use ($tingkat) {
                    $kelasQuery->whereIn('tingkat', $this->tingkatCandidates($tingkat));
                });
            })
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('nama_siswa', 'like', "%{$q}%")
                        ->orWhere('nis', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_siswa')
            ->get();

        $rows = $this->buildRows($siswa, $tahunAktif);

        if ($grade !== '') {
            $rows = $rows->filter(fn(array $row) => $row['grade_pkl'] === $grade)->values();
        }

        $rows = $rows->sortBy([
            ['nilai_akhir', 'desc'],
            ['nama_siswa', 'asc'],
        ])->values();

        $stats = [
            'A' => $rows->where('grade_pkl', 'A')->count(),
            'B' => $rows->where('grade_pkl', 'B')->count(),
            'C' => $rows->where('grade_pkl', 'C')->count(),
            'D' => $rows->where('grade_pkl', 'D')->count(),
            'E' => $rows->where('grade_pkl', 'E')->count(),
            'total' => $rows->count(),
        ];

        $paged = $this->paginateCollection($rows, $limit, $request);

        $kelasOptions = DataKelas::query()
            ->when($allowedKelasIds !== null, fn($builder) => $builder->whereIn('id', $allowedKelasIds))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        return view('bk.rekomendasi_pkl.index', [
            'rows' => $paged,
            'stats' => $stats,
            'tahunAktif' => $tahunAktif,
            'routeBase' => $this->routeBase(),
            'weights' => $weights,
            'thresholds' => $this->gradeThresholds(),
            'q' => $q,
            'kelasId' => $kelasId,
            'tingkat' => $tingkat,
            'grade' => $grade,
            'limit' => $limit,
            'kelasOptions' => $kelasOptions,
        ]);
    }

    public function export(Request $request)
    {
        $roleContext = $this->roleContext();
        $this->authorizeAccess($roleContext);
        $allowedKelasIds = $this->allowedKelasIds($roleContext);

        $q = trim((string) $request->get('q', ''));
        $kelasId = $request->get('kelas_id');
        $tingkat = strtoupper(trim((string) $request->get('tingkat', '')));
        $grade = strtoupper(trim((string) $request->get('grade', '')));

        if (!in_array($tingkat, ['X', 'XI', 'XII'], true)) {
            $tingkat = '';
        }

        if (!in_array($grade, ['A', 'B', 'C', 'D', 'E'], true)) {
            $grade = '';
        }

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        $siswa = DataSiswa::query()
            ->with('kelas')
            ->when($allowedKelasIds !== null, fn($builder) => $builder->whereIn('data_kelas_id', $allowedKelasIds))
            ->when($kelasId, fn($builder) => $builder->where('data_kelas_id', (int) $kelasId))
            ->when($tingkat !== '', function ($builder) use ($tingkat) {
                $builder->whereHas('kelas', function ($kelasQuery) use ($tingkat) {
                    $kelasQuery->whereIn('tingkat', $this->tingkatCandidates($tingkat));
                });
            })
            ->when($q !== '', function ($builder) use ($q) {
                $builder->where(function ($w) use ($q) {
                    $w->where('nama_siswa', 'like', "%{$q}%")
                        ->orWhere('nis', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_siswa')
            ->get();

        $rows = $this->buildRows($siswa, $tahunAktif);
        if ($grade !== '') {
            $rows = $rows->filter(fn(array $row) => $row['grade_pkl'] === $grade)->values();
        }

        $rows = $rows->sortBy([
            ['nilai_akhir', 'desc'],
            ['nama_siswa', 'asc'],
        ])->values();

        $filename = 'rekomendasi-pkl-' . date('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows, $tahunAktif) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['Rekomendasi PKL']);
            fputcsv($out, [
                'Tahun Pelajaran',
                $tahunAktif?->tahun_pelajaran ?? '-',
                'Semester',
                $tahunAktif?->semester ?? '-',
            ]);
            fputcsv($out, []);

            fputcsv($out, [
                'No',
                'Nama Siswa',
                'Kelas',
                'Persentase Kehadiran',
                'Sikap Terakhir',
                'Poin BK',
                'Nilai Akhir',
                'Grade PKL',
                'Rekomendasi',
            ]);

            $no = 1;
            foreach ($rows as $row) {
                fputcsv($out, [
                    $no++,
                    $row['nama_siswa'],
                    $row['kelas'],
                    $row['persentase_kehadiran'] === null ? '-' : number_format($row['persentase_kehadiran'], 2) . '%',
                    $row['sikap_terakhir'],
                    $row['poin_bk'],
                    number_format($row['nilai_akhir'], 2),
                    $row['grade_pkl'],
                    $row['grade_label'],
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function settings()
    {
        $this->ensureAdminContext();

        $cfg = $this->runtimeConfig();
        $weights = $cfg['weights'] ?? [];
        $thresholds = $cfg['grade_thresholds'] ?? [];

        return view('bk.rekomendasi_pkl.settings', [
            'weights' => [
                'kehadiran' => (float) ($weights['kehadiran'] ?? 50),
                'sikap' => (float) ($weights['sikap'] ?? 30),
                'bk' => (float) ($weights['bk'] ?? 20),
            ],
            'thresholds' => [
                'A' => (float) ($thresholds['A'] ?? 85),
                'B' => (float) ($thresholds['B'] ?? 75),
                'C' => (float) ($thresholds['C'] ?? 65),
                'D' => (float) ($thresholds['D'] ?? 50),
            ],
            'attendanceDefault' => (float) ($cfg['attendance_default_score_without_data'] ?? 70),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $this->ensureAdminContext();

        $validated = $request->validate([
            'weight_kehadiran' => 'required|numeric|min:0|max:100',
            'weight_sikap' => 'required|numeric|min:0|max:100',
            'weight_bk' => 'required|numeric|min:0|max:100',
            'grade_a' => 'required|numeric|min:0|max:100',
            'grade_b' => 'required|numeric|min:0|max:100',
            'grade_c' => 'required|numeric|min:0|max:100',
            'grade_d' => 'required|numeric|min:0|max:100',
            'attendance_default' => 'required|numeric|min:0|max:100',
        ]);

        if (
            (float) $validated['grade_a'] < (float) $validated['grade_b'] ||
            (float) $validated['grade_b'] < (float) $validated['grade_c'] ||
            (float) $validated['grade_c'] < (float) $validated['grade_d']
        ) {
            return back()
                ->withErrors(['grade_a' => 'Urutan grade harus A >= B >= C >= D.'])
                ->withInput();
        }

        if (!Schema::hasTable('hubin_rekomendasi_pkl_settings')) {
            return back()->with('error', 'Tabel pengaturan belum tersedia. Jalankan migration terlebih dahulu.');
        }

        $row = HubinRekomendasiPklSetting::query()->first();
        if (!$row) {
            $row = new HubinRekomendasiPklSetting();
        }

        $row->weights = [
            'kehadiran' => (float) $validated['weight_kehadiran'],
            'sikap' => (float) $validated['weight_sikap'],
            'bk' => (float) $validated['weight_bk'],
        ];
        $row->grade_thresholds = [
            'A' => (float) $validated['grade_a'],
            'B' => (float) $validated['grade_b'],
            'C' => (float) $validated['grade_c'],
            'D' => (float) $validated['grade_d'],
        ];
        $row->attendance_default_score_without_data = (float) $validated['attendance_default'];
        $row->updated_by = Auth::id();
        $row->save();

        $this->runtimeConfigCache = null;

        return redirect()
            ->route('admin.bk.rekomendasi-pkl.settings')
            ->with('success', 'Pengaturan rekomendasi PKL berhasil diperbarui.');
    }

    private function buildRows(Collection $siswa, ?DataTahunPelajaran $tahunAktif): Collection
    {
        if ($siswa->isEmpty()) {
            return collect();
        }

        $siswaIds = $siswa->pluck('id')->all();
        $tahunId = $tahunAktif?->id;
        $semester = $tahunAktif?->semester;

        $absensiBySiswa = AbsensiJamSiswa::query()
            ->when($tahunId, fn($builder) => $builder->where('data_tahun_pelajaran_id', $tahunId))
            ->when($semester, fn($builder) => $builder->where('semester', $semester))
            ->whereIn('data_siswa_id', $siswaIds)
            ->get(['data_siswa_id', 'tanggal', 'status'])
            ->groupBy('data_siswa_id');

        $rekapKetidakhadiran = DataKetidakhadiran::query()
            ->when($tahunId, fn($builder) => $builder->where('data_tahun_pelajaran_id', $tahunId))
            ->when($semester, fn($builder) => $builder->where('semester', $semester))
            ->whereIn('data_siswa_id', $siswaIds)
            ->get()
            ->keyBy('data_siswa_id');

        $latestSikap = BkSikapSiswa::query()
            ->when($tahunId, fn($builder) => $builder->where('data_tahun_pelajaran_id', $tahunId))
            ->whereIn('data_siswa_id', $siswaIds)
            ->orderByDesc('tanggal_penilaian')
            ->orderByDesc('id')
            ->get()
            ->groupBy('data_siswa_id')
            ->map(fn(Collection $items) => $items->first());

        $bkPointMap = BkPelanggaranSiswa::query()
            ->selectRaw('data_siswa_id, SUM(poin) as total_poin')
            ->when($tahunId, fn($builder) => $builder->where('data_tahun_pelajaran_id', $tahunId))
            ->whereIn('data_siswa_id', $siswaIds)
            ->groupBy('data_siswa_id')
            ->pluck('total_poin', 'data_siswa_id');

        $estimatedSchoolDays = $this->estimateSchoolDaysInSemester($tahunAktif);
        $weights = $this->weights();

        return $siswa->map(function (DataSiswa $item) use (
            $absensiBySiswa,
            $rekapKetidakhadiran,
            $latestSikap,
            $bkPointMap,
            $estimatedSchoolDays,
            $weights
        ) {
            $attendance = $this->calculateAttendance(
                $absensiBySiswa->get($item->id),
                $rekapKetidakhadiran->get($item->id),
                $estimatedSchoolDays
            );

            $sikap = $latestSikap->get($item->id);
            $predikat = $sikap?->predikat ?? '-';
            $sikapScore = $this->scoreSikap($predikat);

            $poinBk = (int) ($bkPointMap[$item->id] ?? 0);
            $bkScore = $this->scoreBk($poinBk);

            $nilaiAkhir = round(
                ($attendance['skor'] * $weights['kehadiran']) +
                ($sikapScore * $weights['sikap']) +
                ($bkScore * $weights['bk']),
                2
            );

            $grade = $this->gradeFromScore($nilaiAkhir);

            return [
                'nama_siswa' => $item->nama_siswa,
                'kelas' => $item->kelas?->nama_kelas ?? '-',
                'persentase_kehadiran' => $attendance['persentase'],
                'sikap_terakhir' => $predikat,
                'poin_bk' => $poinBk,
                'grade_pkl' => $grade,
                'grade_label' => $this->gradeLabel($grade),
                'nilai_akhir' => $nilaiAkhir,
            ];
        });
    }

    private function calculateAttendance(?Collection $records, ?DataKetidakhadiran $rekap, int $estimatedDays): array
    {
        $records = $records ?? collect();
        $daily = $records->groupBy('tanggal');

        $h = 0;
        $s = 0;
        $i = 0;
        $a = 0;

        foreach ($daily as $items) {
            $statuses = $items->pluck('status');
            if ($statuses->contains('H')) {
                $h++;
                continue;
            }

            $countS = $statuses->filter(fn($value) => $value === 'S')->count();
            $countI = $statuses->filter(fn($value) => $value === 'I')->count();
            $countA = $statuses->filter(fn($value) => $value === 'A')->count();
            $max = max($countA, $countI, $countS);

            if ($max === 0) {
                continue;
            }

            if ($countA === $max) {
                $a++;
            } elseif ($countI === $max) {
                $i++;
            } else {
                $s++;
            }
        }

        $totalObserved = $h + $s + $i + $a;
        if ($totalObserved > 0) {
            $persentase = round(($h / $totalObserved) * 100, 2);
            return [
                'persentase' => $persentase,
                'skor' => $persentase,
            ];
        }

        if ($rekap) {
            $tidakHadir = (int) $rekap->sakit + (int) $rekap->izin + (int) $rekap->tanpa_keterangan;
            $total = max($estimatedDays, $tidakHadir);
            $hadir = max($total - $tidakHadir, 0);
            $persentase = $total > 0 ? round(($hadir / $total) * 100, 2) : 0.0;
            return [
                'persentase' => $persentase,
                'skor' => $persentase,
            ];
        }

        return [
            'persentase' => null,
            'skor' => (float) ($this->runtimeConfig()['attendance_default_score_without_data'] ?? 70),
        ];
    }

    private function scoreSikap(string $predikat): float
    {
        $key = strtolower(trim($predikat));
        $scores = $this->runtimeConfig()['sikap_scores'] ?? [];
        if (array_key_exists($key, $scores)) {
            return (float) $scores[$key];
        }

        return (float) ($this->runtimeConfig()['sikap_default_score'] ?? 60);
    }

    private function scoreBk(int $poin): float
    {
        $ranges = $this->runtimeConfig()['bk_score_ranges'] ?? [];
        foreach ($ranges as $range) {
            $max = $range['max'] ?? null;
            if ($max === null || $poin <= (int) $max) {
                return (float) ($range['score'] ?? 40);
            }
        }

        return 40.0;
    }

    private function gradeFromScore(float $nilai): string
    {
        $thresholds = $this->gradeThresholds();
        if ($nilai >= (float) ($thresholds['A'] ?? 85)) {
            return 'A';
        }
        if ($nilai >= (float) ($thresholds['B'] ?? 75)) {
            return 'B';
        }
        if ($nilai >= (float) ($thresholds['C'] ?? 65)) {
            return 'C';
        }
        if ($nilai >= (float) ($thresholds['D'] ?? 50)) {
            return 'D';
        }
        return 'E';
    }

    private function gradeLabel(string $grade): string
    {
        $labels = $this->runtimeConfig()['grade_labels'] ?? [];
        return $labels[$grade] ?? 'Tidak Direkomendasikan';
    }

    private function estimateSchoolDaysInSemester(?DataTahunPelajaran $tahunAktif): int
    {
        [$start, $end] = $this->semesterRange($tahunAktif);
        if (!$start || !$end) {
            return 110;
        }

        $cursor = $start;
        $count = 0;
        while ($cursor->lte($end)) {
            if ($cursor->isWeekday()) {
                $count++;
            }
            $cursor = $cursor->addDay();
        }

        return max($count, 1);
    }

    private function semesterRange(?DataTahunPelajaran $tahunAktif): array
    {
        if (!$tahunAktif) {
            return [null, null];
        }

        [$thAwal, $thAkhir] = $this->parseTahunPelajaran((string) $tahunAktif->tahun_pelajaran);
        if ($tahunAktif->semester === 'Ganjil') {
            return [
                CarbonImmutable::create($thAwal, 7, 1),
                CarbonImmutable::create($thAwal, 12, 31),
            ];
        }

        return [
            CarbonImmutable::create($thAkhir, 1, 1),
            CarbonImmutable::create($thAkhir, 6, 30),
        ];
    }

    private function parseTahunPelajaran(string $tahunPelajaran): array
    {
        if (preg_match('/^(\d{4})\s*\/\s*(\d{4})$/', $tahunPelajaran, $match)) {
            return [(int) $match[1], (int) $match[2]];
        }

        $now = (int) date('Y');
        return [$now, $now + 1];
    }

    private function paginateCollection(Collection $items, int $perPage, Request $request): LengthAwarePaginator
    {
        $page = max((int) $request->get('page', 1), 1);
        $total = $items->count();
        $slice = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }

    private function routeBase(): string
    {
        $name = request()->route()?->getName() ?? '';
        if (str_starts_with($name, 'admin.')) {
            return 'admin.bk';
        }
        if (str_starts_with($name, 'guru.wali-kelas.')) {
            return 'guru.wali-kelas';
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
        if (str_starts_with($name, 'guru.wali-kelas.')) {
            return 'wali';
        }
        if (str_starts_with($name, 'guru.')) {
            return 'guru';
        }
        return 'bk';
    }

    private function allowedKelasIds(string $roleContext): ?array
    {
        if ($roleContext === 'wali') {
            return $this->waliKelasIds(Auth::id());
        }

        if ($roleContext !== 'guru') {
            return null;
        }

        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $globalRoles = config('rekomendasi_pkl.roles.guru_global_access', ['pembimbing_pkl']);
        $cfgRoles = $this->runtimeConfig()['roles'] ?? [];
        $globalRoles = $cfgRoles['guru_global_access'] ?? $globalRoles;
        if ($this->userHasAnyRole($user, $globalRoles)) {
            return null;
        }

        $limitedRoles = $cfgRoles['guru_limited_access'] ?? config('rekomendasi_pkl.roles.guru_limited_access', ['wali_kelas']);
        if ($this->userHasAnyRole($user, $limitedRoles) || $this->isWaliKelasUser($user->id)) {
            return $this->waliKelasIds($user->id);
        }

        return [];
    }

    private function tingkatCandidates(string $tingkat): array
    {
        return match ($tingkat) {
            'X' => ['X', '10'],
            'XI' => ['XI', '11'],
            'XII' => ['XII', '12'],
            default => [$tingkat],
        };
    }

    private function authorizeAccess(string $roleContext): void
    {
        if (in_array($roleContext, ['admin', 'bk', 'wali'], true)) {
            return;
        }

        if ($roleContext !== 'guru') {
            return;
        }

        $user = Auth::user();
        if (!$user) {
            abort(403);
        }

        $cfgRoles = $this->runtimeConfig()['roles'] ?? [];
        $globalRoles = $cfgRoles['guru_global_access'] ?? config('rekomendasi_pkl.roles.guru_global_access', ['pembimbing_pkl']);
        $limitedRoles = $cfgRoles['guru_limited_access'] ?? config('rekomendasi_pkl.roles.guru_limited_access', ['wali_kelas']);

        if (
            $this->userHasAnyRole($user, $globalRoles) ||
            $this->userHasAnyRole($user, $limitedRoles) ||
            $this->isWaliKelasUser($user->id)
        ) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke rekomendasi PKL.');
    }

    private function userHasAnyRole($user, array $roles): bool
    {
        foreach ($roles as $role) {
            if (method_exists($user, 'hasRole') && $user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    private function isWaliKelasUser(int $userId): bool
    {
        return DataKelas::where('wali_kelas_id', $userId)->exists();
    }

    private function waliKelasIds(int $userId): array
    {
        return DataKelas::query()
            ->where('wali_kelas_id', $userId)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->all();
    }

    private function weights(): array
    {
        $weights = $this->runtimeConfig()['weights'] ?? [];
        $kehadiran = max((float) ($weights['kehadiran'] ?? 50), 0.0);
        $sikap = max((float) ($weights['sikap'] ?? 30), 0.0);
        $bk = max((float) ($weights['bk'] ?? 20), 0.0);
        $total = $kehadiran + $sikap + $bk;

        if ($total <= 0) {
            return ['kehadiran' => 0.5, 'sikap' => 0.3, 'bk' => 0.2, 'raw' => [50, 30, 20]];
        }

        return [
            'kehadiran' => $kehadiran / $total,
            'sikap' => $sikap / $total,
            'bk' => $bk / $total,
            'raw' => [$kehadiran, $sikap, $bk],
        ];
    }

    private function gradeThresholds(): array
    {
        return $this->runtimeConfig()['grade_thresholds'] ?? [
            'A' => 85,
            'B' => 75,
            'C' => 65,
            'D' => 50,
        ];
    }

    private function runtimeConfig(): array
    {
        if ($this->runtimeConfigCache !== null) {
            return $this->runtimeConfigCache;
        }

        $base = config('rekomendasi_pkl', []);

        if (!Schema::hasTable('hubin_rekomendasi_pkl_settings')) {
            $this->runtimeConfigCache = $base;
            return $this->runtimeConfigCache;
        }

        $row = HubinRekomendasiPklSetting::query()->first();
        if (!$row) {
            $this->runtimeConfigCache = $base;
            return $this->runtimeConfigCache;
        }

        $merged = $base;
        if (is_array($row->weights)) {
            $merged['weights'] = array_merge($merged['weights'] ?? [], $row->weights);
        }
        if (is_array($row->grade_thresholds)) {
            $merged['grade_thresholds'] = array_merge($merged['grade_thresholds'] ?? [], $row->grade_thresholds);
        }
        if (!is_null($row->attendance_default_score_without_data)) {
            $merged['attendance_default_score_without_data'] = (float) $row->attendance_default_score_without_data;
        }

        $this->runtimeConfigCache = $merged;
        return $this->runtimeConfigCache;
    }

    private function ensureAdminContext(): void
    {
        $name = request()->route()?->getName() ?? '';
        if (!str_starts_with($name, 'admin.')) {
            abort(403);
        }
    }
}
