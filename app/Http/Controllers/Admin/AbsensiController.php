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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $q = trim((string) $request->get('q', ''));
        $check = $request->boolean('check');

        $jadwal = JadwalPelajaran::query()
            ->with(['kelas', 'mapel', 'guru'])
            ->where('data_tahun_pelajaran_id', $tahunAktif->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($inner) use ($q) {
                    $inner->where('hari', 'like', "%{$q}%")
                        ->orWhere('jam_ke', 'like', "%{$q}%")
                        ->orWhereHas('kelas', fn($kelas) => $kelas->where('nama_kelas', 'like', "%{$q}%"))
                        ->orWhereHas('mapel', fn($mapel) => $mapel->where('nama_mapel', 'like', "%{$q}%"))
                        ->orWhereHas('guru', fn($guru) => $guru->where('nama', 'like', "%{$q}%"));
                });
            })
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 1 WHEN 'Selasa' THEN 2 WHEN 'Rabu' THEN 3 WHEN 'Kamis' THEN 4 WHEN 'Jumat' THEN 5 ELSE 99 END")
            ->orderBy('jam_ke')
            ->paginate(25)
            ->withQueryString();

        $kelas = DataKelas::orderBy('nama_kelas')->get();
        $guru = User::query()
            ->whereHas('peran', fn($q) => $q->where('nama_peran', 'guru_mapel'))
            ->orderBy('nama')
            ->get();
        $kelengkapanJadwal = $check ? $this->buildJadwalKelengkapan($tahunAktif->id) : [];

        return view('admin.absensi.jadwal', compact('jadwal', 'tahunAktif', 'kelas', 'guru', 'q', 'check', 'kelengkapanJadwal'));
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

    public function jadwalDownloadFormatImport(): StreamedResponse
    {
        if (!$this->absensiTablesReady()) {
            abort(400, 'Modul absensi belum siap.');
        }

        $headers = ['kelas', 'mapel', 'guru', 'hari', 'jam_ke'];
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        foreach ($headers as $i => $h) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
        }

        $sampleKelas = DataKelas::orderBy('nama_kelas')->value('nama_kelas') ?? 'X TKJ 1';
        $sampleMapel = DataMapel::orderBy('nama_mapel')->value('nama_mapel') ?? 'Matematika';
        $sampleGuru = User::whereHas('peran', fn($q) => $q->where('nama_peran', 'guru_mapel'))
            ->orderBy('nama')
            ->value('nama') ?? 'Nama Guru';

        $samples = [
            [$sampleKelas, $sampleMapel, $sampleGuru, 'Senin', 1],
            [$sampleKelas, $sampleMapel, $sampleGuru, 'Selasa', 3],
        ];

        foreach ($samples as $r => $sample) {
            $rowNo = $r + 2;
            foreach ($sample as $i => $val) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue($col . $rowNo, $val);
            }
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 'format_import_jadwal_absensi.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function jadwalImport(Request $request)
    {
        if (!$this->absensiTablesReady()) {
            return $this->renderSetupPage();
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx',
            'yakin' => 'required|in:1',
        ]);

        $tahunAktif = DataTahunPelajaran::where('status_aktif', 1)->firstOrFail();

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $sheet = $spreadsheet->getSheetByName('Sheet1') ?? $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return back()->with('error', 'File kosong / tidak ada data.');
        }

        $headerMap = [];
        foreach (($rows[1] ?? []) as $col => $header) {
            $key = strtolower(trim((string) $header));
            if ($key !== '') {
                $headerMap[$key] = $col;
            }
        }

        $findCol = function (array $aliases) use ($headerMap) {
            foreach ($aliases as $alias) {
                $key = strtolower(trim($alias));
                if (isset($headerMap[$key])) {
                    return $headerMap[$key];
                }
            }
            return null;
        };

        $kelasCol = $findCol(['kelas', 'nama_kelas', 'class']);
        $mapelCol = $findCol(['mapel', 'mata pelajaran', 'mata_pelajaran', 'nama_mapel', 'pelajaran']);
        $guruCol = $findCol(['guru', 'nama_guru', 'guru_pengampu', 'pengampu']);
        $hariCol = $findCol(['hari', 'day']);
        $jamCol = $findCol(['jam_ke', 'jam', 'jam ke', 'sesi', 'slot']);

        if (!$kelasCol || !$mapelCol || !$guruCol || !$hariCol || !$jamCol) {
            return back()->with('error', 'Header wajib tidak ditemukan. Wajib ada: kelas, mapel, guru, hari, jam_ke.');
        }

        $kelasCandidates = DataKelas::select('id', 'nama_kelas')->get()
            ->map(fn($k) => ['id' => (int) $k->id, 'label' => (string) $k->nama_kelas]);

        $mapelRows = DataMapel::select('id', 'nama_mapel', 'singkatan')->get();
        $mapelCandidates = collect();
        foreach ($mapelRows as $m) {
            $mapelCandidates->push(['id' => (int) $m->id, 'label' => (string) $m->nama_mapel]);
            if (!empty($m->singkatan)) {
                $mapelCandidates->push(['id' => (int) $m->id, 'label' => (string) $m->singkatan]);
            }
        }

        $guruCandidates = User::whereHas('peran', fn($q) => $q->where('nama_peran', 'guru_mapel'))
            ->select('id', 'nama')
            ->get()
            ->map(fn($g) => ['id' => (int) $g->id, 'label' => (string) $g->nama]);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            for ($i = 2; $i <= count($rows); $i++) {
                $row = $rows[$i] ?? null;
                if (!$row) {
                    continue;
                }

                $get = function ($col) use ($row) {
                    $val = $row[$col] ?? null;
                    if (is_string($val)) {
                        $val = trim($val);
                    }
                    return $val === '' ? null : $val;
                };

                $kelasVal = trim((string) ($get($kelasCol) ?? ''));
                $mapelVal = trim((string) ($get($mapelCol) ?? ''));
                $guruVal = trim((string) ($get($guruCol) ?? ''));
                $hariVal = trim((string) ($get($hariCol) ?? ''));
                $jamVal = trim((string) ($get($jamCol) ?? ''));

                if ($kelasVal === '' && $mapelVal === '' && $guruVal === '' && $hariVal === '' && $jamVal === '') {
                    continue;
                }

                if ($kelasVal === '' || $mapelVal === '' || $guruVal === '' || $hariVal === '' || $jamVal === '') {
                    $skipped++;
                    $errors[] = "Baris {$i}: kolom wajib kosong.";
                    continue;
                }

                $hari = $this->normalizeHari($hariVal);
                $jamKe = $this->normalizeJamKe($jamVal);
                if (!$hari || !$jamKe) {
                    $skipped++;
                    $errors[] = "Baris {$i}: hari/jam tidak valid.";
                    continue;
                }
                if ($hari === 'Jumat' && $jamKe > 8) {
                    $skipped++;
                    $errors[] = "Baris {$i}: jam ke-9/10 tidak berlaku untuk Jumat.";
                    continue;
                }

                $kelas = $this->resolveFuzzyCandidate($kelasVal, $kelasCandidates, 70);
                $mapel = $this->resolveFuzzyCandidate($mapelVal, $mapelCandidates, 68);
                $guru = $this->resolveFuzzyCandidate($guruVal, $guruCandidates, 72);

                if (!$kelas || !$mapel || !$guru) {
                    $skipped++;
                    $errors[] = "Baris {$i}: kelas/mapel/guru tidak cocok.";
                    continue;
                }

                if (!$this->isMapelValidForKelas((int) $kelas->id, (int) $mapel->id)) {
                    $skipped++;
                    $errors[] = "Baris {$i}: mapel tidak sesuai kelas.";
                    continue;
                }

                $exists = JadwalPelajaran::where('data_tahun_pelajaran_id', $tahunAktif->id)
                    ->where('data_kelas_id', $kelas->id)
                    ->where('hari', $hari)
                    ->where('jam_ke', $jamKe)
                    ->first();

                if ($exists) {
                    $exists->update([
                        'data_mapel_id' => $mapel->id,
                        'guru_id' => $guru->id,
                    ]);
                    $updated++;
                } else {
                    JadwalPelajaran::create([
                        'data_tahun_pelajaran_id' => $tahunAktif->id,
                        'data_kelas_id' => $kelas->id,
                        'data_mapel_id' => $mapel->id,
                        'guru_id' => $guru->id,
                        'hari' => $hari,
                        'jam_ke' => $jamKe,
                    ]);
                    $created++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }

        if ($errors !== []) {
            $preview = implode(' | ', array_slice($errors, 0, 6));
            return back()->with(
                'error',
                "Import selesai. Ditambah: {$created}, Diupdate: {$updated}, Dilewati: {$skipped}. {$preview}"
            );
        }

        return back()->with('success', "Import selesai. Ditambah: {$created}, Diupdate: {$updated}, Dilewati: {$skipped}.");
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

    private function normalizeToken(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        $text = preg_replace('/[^a-z0-9]+/u', '', $text) ?? $text;
        return $text;
    }

    private function resolveFuzzyCandidate(string $input, $candidates, int $minScore = 70): ?object
    {
        $input = trim($input);
        if ($input === '') {
            return null;
        }

        $needle = $this->normalizeToken($input);
        if ($needle === '') {
            return null;
        }

        $best = null;
        $bestScore = -1.0;

        foreach ($candidates as $row) {
            $label = (string) ($row['label'] ?? '');
            $norm = $this->normalizeToken($label);
            if ($norm === '') {
                continue;
            }

            if ($norm === $needle) {
                return (object) ['id' => (int) $row['id'], 'label' => $label];
            }

            $score = 0.0;
            if (str_contains($norm, $needle) || str_contains($needle, $norm)) {
                $short = min(strlen($norm), strlen($needle));
                $long = max(strlen($norm), strlen($needle));
                $score = $long > 0 ? ($short / $long) * 100 : 0.0;
            } else {
                similar_text($needle, $norm, $percent);
                $score = $percent;
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = (object) ['id' => (int) $row['id'], 'label' => $label];
            }
        }

        return ($best && $bestScore >= $minScore) ? $best : null;
    }

    private function normalizeHari(string $value): ?string
    {
        $v = mb_strtolower(trim($value));
        $v = str_replace(["'", '`', '.'], '', $v);
        $map = [
            'senin' => 'Senin',
            'selasa' => 'Selasa',
            'rabu' => 'Rabu',
            'kamis' => 'Kamis',
            'jumat' => 'Jumat',
            "jum'at" => 'Jumat',
            "jumat" => 'Jumat',
            'fri' => 'Jumat',
        ];
        return $map[$v] ?? null;
    }

    private function normalizeJamKe(string $value): ?int
    {
        $v = trim($value);
        if ($v === '') {
            return null;
        }

        if (preg_match('/^\d+$/', $v) === 1) {
            $n = (int) $v;
            return ($n >= 1 && $n <= 10) ? $n : null;
        }

        if (preg_match('/^(\d+)\s*[-\/]\s*(\d+)$/', $v, $m) === 1) {
            $n = (int) $m[1];
            return ($n >= 1 && $n <= 10) ? $n : null;
        }

        return null;
    }

    private function buildJadwalKelengkapan(int $tahunAktifId): array
    {
        $aturanHari = [
            'Senin' => range(1, 10),
            'Selasa' => range(1, 10),
            'Rabu' => range(1, 10),
            'Kamis' => range(1, 10),
            'Jumat' => range(1, 8),
        ];

        $jadwalRows = JadwalPelajaran::query()
            ->select('data_kelas_id', 'hari', 'jam_ke')
            ->where('data_tahun_pelajaran_id', $tahunAktifId)
            ->get()
            ->groupBy('data_kelas_id');

        $hasil = [];
        foreach (DataKelas::query()->orderBy('nama_kelas')->get() as $kelas) {
            $terisiPerHari = [];
            foreach (($jadwalRows->get($kelas->id) ?? collect()) as $item) {
                $hari = (string) $item->hari;
                $jamKe = (int) $item->jam_ke;
                if (!isset($terisiPerHari[$hari])) {
                    $terisiPerHari[$hari] = [];
                }

                $terisiPerHari[$hari][$jamKe] = true;
            }

            $detailKosong = [];
            $totalTerisi = 0;
            $totalWajib = 0;

            foreach ($aturanHari as $hari => $jamWajib) {
                $terisi = array_map('intval', array_keys($terisiPerHari[$hari] ?? []));
                sort($terisi);

                $kosong = array_values(array_diff($jamWajib, $terisi));
                $jumlahTerisiHari = count(array_intersect($jamWajib, $terisi));

                $totalTerisi += $jumlahTerisiHari;
                $totalWajib += count($jamWajib);

                if ($kosong !== []) {
                    $detailKosong[] = [
                        'hari' => $hari,
                        'jam' => $kosong,
                    ];
                }
            }

            $hasil[] = [
                'kelas' => $kelas,
                'lengkap' => $detailKosong === [],
                'total_terisi' => $totalTerisi,
                'total_wajib' => $totalWajib,
                'detail_kosong' => $detailKosong,
            ];
        }

        return $hasil;
    }
}
