<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbsensiJamSiswa;
use App\Models\DataKelas;
use App\Models\DataMapel;
use App\Models\DataSiswa;
use App\Models\DataTahunPelajaran;
use App\Models\JadwalPelajaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        if (!$this->absensiTablesReady()) {
            return $this->renderSetupPage();
        }

        $limit = (int) $request->get('limit', 10);
        if (!in_array($limit, [10, 25, 50, 100], true)) {
            $limit = 10;
        }

        $q = trim((string) $request->get('q', ''));
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->first();

        $kelas = DataKelas::query()
            ->withCount('siswa')
            ->with('wali.pengguna')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('nama_kelas', 'like', "%{$q}%")
                        ->orWhereHas('wali.pengguna', fn($u) => $u->where('nama', 'like', "%{$q}%"));
                });
            })
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate($limit)
            ->withQueryString();

        return view('admin.absensi.index', compact('kelas', 'limit', 'q', 'tahunAktif'));
    }

    public function rekap($kelasId, Request $request)
    {
        if (!$this->absensiTablesReady()) {
            return $this->renderSetupPage();
        }

        $kelas = DataKelas::with('wali.pengguna')->findOrFail($kelasId);
        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        [$thAwal, $thAkhir] = $this->parseTahunPelajaran((string) $tahunAktif->tahun_pelajaran);
        $periode = $request->get('periode', 'month');
        $bulan = (int) $request->get('bulan', $tahunAktif->semester === 'Genap' ? 1 : 7);
        $quarter = (int) $request->get('quarter', 1);

        [$startDate, $endDate, $periodeLabel] = $this->resolveRange(
            $periode,
            $bulan,
            $quarter,
            $tahunAktif->semester,
            $thAwal,
            $thAkhir
        );

        $rows = $this->buildRekapRows($kelas->id, $startDate, $endDate, $tahunAktif->id, $tahunAktif->semester);

        return view('admin.absensi.rekap', compact(
            'kelas',
            'tahunAktif',
            'periode',
            'bulan',
            'quarter',
            'periodeLabel',
            'startDate',
            'endDate',
            'rows'
        ));
    }

    public function jadwal(Request $request)
    {
        if (!$this->absensiTablesReady()) {
            return $this->renderSetupPage();
        }

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        $jadwal = JadwalPelajaran::query()
            ->with(['kelas', 'mapel', 'guru'])
            ->where('data_tahun_pelajaran_id', $tahunAktif->id)
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 ELSE 99 END")
            ->orderBy('jam_ke')
            ->paginate(25)
            ->withQueryString();

        $kelas = DataKelas::orderBy('nama_kelas')->get();
        $guru = User::query()
            ->whereHas('peran', fn($q) => $q->where('nama_peran', 'guru_mapel'))
            ->orderBy('nama')
            ->get();

        return view('admin.absensi.jadwal', compact('jadwal', 'tahunAktif', 'kelas', 'guru'));
    }

    public function jadwalStore(Request $request)
    {
        if (!$this->absensiTablesReady()) {
            return $this->renderSetupPage();
        }

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        $data = $request->validate([
            'data_kelas_id' => 'required|exists:data_kelas,id',
            'data_mapel_id' => 'required|exists:data_mapel,id',
            'guru_id' => 'required|exists:pengguna,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'jam_ke' => 'required|integer|min:1|max:10',
        ]);

        if ($data['hari'] === 'Jumat' && (int) $data['jam_ke'] > 8) {
            return back()->with('error', 'Jam ke-9 dan ke-10 tidak tersedia pada hari Jumat.');
        }

        if (!$this->isMapelValidForKelas((int) $data['data_kelas_id'], (int) $data['data_mapel_id'])) {
            return back()->withInput()->with('error', 'Mapel tidak sesuai dengan kelas yang dipilih.');
        }

        JadwalPelajaran::create([
            'data_tahun_pelajaran_id' => $tahunAktif->id,
            'data_kelas_id' => $data['data_kelas_id'],
            'data_mapel_id' => $data['data_mapel_id'],
            'guru_id' => $data['guru_id'],
            'hari' => $data['hari'],
            'jam_ke' => $data['jam_ke'],
        ]);

        return back()->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function jadwalDestroy($id)
    {
        if (!$this->absensiTablesReady()) {
            return $this->renderSetupPage();
        }

        JadwalPelajaran::findOrFail($id)->delete();
        return back()->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }

    private function absensiTablesReady(): bool
    {
        return Schema::hasTable('jam_pelajaran')
            && Schema::hasTable('jadwal_pelajaran')
            && Schema::hasTable('absensi_jam_siswa');
    }

    private function renderSetupPage()
    {
        return response()->view('admin.absensi.setup');
    }

    private function buildRekapRows(int $kelasId, string $startDate, string $endDate, int $tahunId, string $semester): array
    {
        $siswa = DataSiswa::where('data_kelas_id', $kelasId)
            ->orderBy('nama_siswa')
            ->get();

        $records = AbsensiJamSiswa::query()
            ->where('data_kelas_id', $kelasId)
            ->where('data_tahun_pelajaran_id', $tahunId)
            ->where('semester', $semester)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->groupBy('data_siswa_id');

        $rows = [];
        foreach ($siswa as $s) {
            $daily = ($records->get($s->id) ?? collect())->groupBy('tanggal');

            $h = $si = $iz = $a = 0;
            foreach ($daily as $items) {
                $statuses = $items->pluck('status');
                if ($statuses->contains('H')) {
                    $h++;
                    continue;
                }

                $countS = $statuses->filter(fn($x) => $x === 'S')->count();
                $countI = $statuses->filter(fn($x) => $x === 'I')->count();
                $countA = $statuses->filter(fn($x) => $x === 'A')->count();
                $max = max($countA, $countI, $countS);

                if ($max === 0) {
                    continue;
                }
                if ($countA === $max) {
                    $a++;
                } elseif ($countI === $max) {
                    $iz++;
                } else {
                    $si++;
                }
            }

            $rows[] = [
                'siswa' => $s,
                'hadir' => $h,
                'sakit' => $si,
                'izin' => $iz,
                'alpa' => $a,
            ];
        }

        return $rows;
    }

    private function resolveRange(string $periode, int $bulan, int $quarter, string $semester, int $thAwal, int $thAkhir): array
    {
        $periode = in_array($periode, ['month', 'quarter', 'semester', 'year'], true) ? $periode : 'month';

        if ($periode === 'semester') {
            if ($semester === 'Ganjil') {
                return ["{$thAwal}-07-01", "{$thAwal}-12-31", "Per Semester ({$semester})"];
            }
            return ["{$thAkhir}-01-01", "{$thAkhir}-06-30", "Per Semester ({$semester})"];
        }

        if ($periode === 'year') {
            return ["{$thAwal}-07-01", "{$thAkhir}-06-30", 'Per Tahun Pelajaran'];
        }

        if ($periode === 'quarter') {
            if ($semester === 'Ganjil') {
                if ($quarter === 2) {
                    return ["{$thAwal}-10-01", "{$thAwal}-12-31", 'Per 3 Bulan (Okt-Des)'];
                }
                return ["{$thAwal}-07-01", "{$thAwal}-09-30", 'Per 3 Bulan (Jul-Sep)'];
            }

            if ($quarter === 2) {
                return ["{$thAkhir}-04-01", "{$thAkhir}-06-30", 'Per 3 Bulan (Apr-Jun)'];
            }
            return ["{$thAkhir}-01-01", "{$thAkhir}-03-31", 'Per 3 Bulan (Jan-Mar)'];
        }

        $validMonths = $semester === 'Ganjil' ? [7, 8, 9, 10, 11, 12] : [1, 2, 3, 4, 5, 6];
        if (!in_array($bulan, $validMonths, true)) {
            $bulan = $validMonths[0];
        }

        $year = $bulan >= 7 ? $thAwal : $thAkhir;
        $start = sprintf('%04d-%02d-01', $year, $bulan);
        $end = date('Y-m-t', strtotime($start));
        $label = 'Per 1 Bulan (' . date('F Y', strtotime($start)) . ')';

        return [$start, $end, $label];
    }

    private function parseTahunPelajaran(string $tahunPelajaran): array
    {
        if (preg_match('/^(\d{4})\s*\/\s*(\d{4})$/', $tahunPelajaran, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }

        $y = (int) date('Y');
        return [$y, $y + 1];
    }

    private function isMapelValidForKelas(int $kelasId, int $mapelId): bool
    {
        $kelas = DataKelas::find($kelasId);
        if (!$kelas) {
            return false;
        }

        $rawTingkat = strtoupper(trim((string) $kelas->tingkat));
        $mapTingkat = ['10' => 'X', '11' => 'XI', '12' => 'XII', 'X' => 'X', 'XI' => 'XI', 'XII' => 'XII'];
        $tingkatKelas = $mapTingkat[$rawTingkat] ?? $rawTingkat;

        $jurusanId = $kelas->jurusan_id;
        $hasJurusan = !empty($jurusanId);

        return DataMapel::query()
            ->where('id', $mapelId)
            ->whereIn('tingkat', [$tingkatKelas, 'SEMUA'])
            ->where(function ($q) use ($hasJurusan, $jurusanId) {
                if ($hasJurusan) {
                    $q->whereNull('jurusan_id')
                        ->orWhere('jurusan_id', (int) $jurusanId);
                } else {
                    $q->whereNull('jurusan_id');
                }
            })
            ->exists();
    }
}
