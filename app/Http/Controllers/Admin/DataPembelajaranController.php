<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataPembelajaran;
use App\Models\DataKelas;
use App\Models\DataMapel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataPembelajaranController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $pembelajaran = DataPembelajaran::with(['kelas', 'mapel', 'guru'])
            ->orderBy('data_kelas_id')
            ->get();

        return view('admin.pembelajaran.index', [
            'pembelajaran' => $pembelajaran,
            'kelas' => DataKelas::orderBy('nama_kelas')->get(),
            'mapel' => DataMapel::orderBy('nama_mapel')->get(),
            'guru'  => User::whereHas('peran', fn($q) => $q->where('nama_peran', 'guru_mapel'))
                ->orderBy('nama')->get(),
        ]);
    }

    public function create()
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        return view('admin.pembelajaran.form', [
            'pembelajaran' => null,
            'kelas' => DataKelas::orderBy('nama_kelas')->get(),
            'mapel' => DataMapel::orderBy('nama_mapel')->get(),
            'guru'  => User::whereHas('peran', fn($q) => $q->where('nama_peran', 'guru_mapel'))
                ->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $data = $request->validate([
            'data_kelas_id' => 'required|exists:data_kelas,id',
            'data_mapel_id' => 'required|exists:data_mapel,id',
            'guru_id'       => 'required|exists:pengguna,id',
        ]);

        $exists = DataPembelajaran::where('data_kelas_id', $data['data_kelas_id'])
            ->where('data_mapel_id', $data['data_mapel_id'])
            ->exists();

        if ($exists) {
            return redirect()
                ->route('admin.pembelajaran.index')
                ->withInput()
                ->with('error', 'Pembelajaran untuk kelas dan mata pelajaran tersebut sudah ada.');
        }

        DataPembelajaran::create($data);

        return redirect()->route('admin.pembelajaran.index')
            ->with('success', 'Data pembelajaran berhasil ditambahkan');
    }

    public function edit($id)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        return view('admin.pembelajaran.form', [
            'pembelajaran' => DataPembelajaran::findOrFail($id),
            'kelas' => DataKelas::orderBy('nama_kelas')->get(),
            'mapel' => DataMapel::orderBy('nama_mapel')->get(),
            'guru'  => User::whereHas('peran', fn($q) => $q->where('nama_peran', 'guru_mapel'))
                ->orderBy('nama')->get(),
        ]);
    }

    public function json($id)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $p = DataPembelajaran::findOrFail($id);

        return response()->json([
            'id' => $p->id,
            'data_kelas_id' => $p->data_kelas_id,
            'data_mapel_id' => $p->data_mapel_id,
            'guru_id' => $p->guru_id,
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $pembelajaran = DataPembelajaran::findOrFail($id);

        $data = $request->validate([
            'data_kelas_id' => 'required|exists:data_kelas,id',
            'data_mapel_id' => 'required|exists:data_mapel,id',
            'guru_id'       => 'required|exists:pengguna,id',
        ]);

        $exists = DataPembelajaran::where('id', '!=', $pembelajaran->id)
            ->where('data_kelas_id', $data['data_kelas_id'])
            ->where('data_mapel_id', $data['data_mapel_id'])
            ->exists();

        if ($exists) {
            return redirect()
                ->route('admin.pembelajaran.index')
                ->withInput()
                ->with('error', 'Pembelajaran untuk kelas dan mata pelajaran tersebut sudah ada.');
        }

        $pembelajaran->update($data);

        return redirect()->route('admin.pembelajaran.index')
            ->with('success', 'Data pembelajaran berhasil diperbarui');
    }

    public function mapelByKelas($kelasId)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $kelas = DataKelas::findOrFail($kelasId);

        $rawTingkat = strtoupper(trim((string)$kelas->tingkat));
        $mapTingkat = ['10' => 'X', '11' => 'XI', '12' => 'XII', 'X' => 'X', 'XI' => 'XI', 'XII' => 'XII'];
        $tingkatKelas = $mapTingkat[$rawTingkat] ?? $rawTingkat;

        $jurusanId  = $kelas->jurusan_id;
        $hasJurusan = !empty($jurusanId);

        $rows = DataMapel::query()
            ->whereIn('tingkat', [$tingkatKelas, 'SEMUA'])
            ->where(function ($q) use ($hasJurusan, $jurusanId) {
                if ($hasJurusan) {
                    $q->whereNull('jurusan_id')
                        ->orWhere('jurusan_id', (int)$jurusanId);
                } else {
                    $q->whereNull('jurusan_id');
                }
            })
            ->get();

        $rows = $rows->map(function ($m) use ($tingkatKelas, $jurusanId) {
            $tingkatPrior = ($m->tingkat === 'SEMUA') ? 0 : (($m->tingkat === $tingkatKelas) ? 1 : 9);
            $jurusanPrior = is_null($m->jurusan_id) ? 0 : (((int)$m->jurusan_id === (int)$jurusanId) ? 1 : 9);
            $urutan = is_null($m->urutan_cetak) ? 999999 : (int)$m->urutan_cetak;

            $m->_sortKey = [$tingkatPrior, $jurusanPrior, $urutan, mb_strtolower($m->nama_mapel)];
            return $m;
        });

        $unique = $rows
            ->groupBy(fn($m) => mb_strtolower(trim((string)$m->nama_mapel)))
            ->map(function ($grp) {
                return $grp->sortBy(fn($m) => $m->_sortKey)->first();
            })
            ->values()
            ->sortBy(fn($m) => $m->_sortKey)
            ->values()
            ->map(fn($m) => [
                'id'   => $m->id,
                'nama' => $m->nama_mapel,
            ]);

        return response()->json($unique);
    }

    public function downloadFormatImport(): StreamedResponse
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $headers = ['kelas', 'mapel', 'guru'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

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
            [$sampleKelas, $sampleMapel, $sampleGuru],
            [$sampleKelas, $sampleMapel, $sampleGuru],
        ];

        foreach ($samples as $rowIdx => $sample) {
            $excelRow = $rowIdx + 2;
            foreach ($sample as $i => $val) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue($col . $excelRow, $val);
            }
        }

        $filename = 'format_import_data_pembelajaran.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);

        $request->validate([
            'file' => 'required|file|mimes:xlsx',
            'yakin' => 'required|in:1',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes' => 'File harus berformat XLSX.',
            'yakin.required' => 'Checklist konfirmasi wajib dicentang.',
            'yakin.in' => 'Checklist konfirmasi wajib dicentang.',
        ]);

        $path = $request->file('file')->getRealPath();
        $spreadsheet = IOFactory::load($path);
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

        if (!$kelasCol || !$mapelCol || !$guruCol) {
            return back()->with('error', 'Header wajib tidak ditemukan. Minimal harus ada kolom kelas, mapel, dan guru.');
        }

        $kelasCandidates = DataKelas::query()
            ->select('id', 'nama_kelas')
            ->get()
            ->map(fn($k) => ['id' => (int) $k->id, 'label' => (string) $k->nama_kelas]);

        $mapelRows = DataMapel::query()
            ->select('id', 'nama_mapel', 'singkatan')
            ->get();
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

                $getByCol = function ($col) use ($row) {
                    $value = $row[$col] ?? null;
                    if (is_string($value)) {
                        $value = trim($value);
                    }
                    return $value === '' ? null : $value;
                };

                $kelasVal = trim((string) ($getByCol($kelasCol) ?? ''));
                $mapelVal = trim((string) ($getByCol($mapelCol) ?? ''));
                $guruVal = trim((string) ($getByCol($guruCol) ?? ''));

                if ($kelasVal === '' && $mapelVal === '' && $guruVal === '') {
                    continue;
                }

                if ($kelasVal === '' || $mapelVal === '' || $guruVal === '') {
                    $skipped++;
                    $errors[] = "Baris {$i}: kolom kelas/mapel/guru wajib diisi.";
                    continue;
                }

                $kelas = $this->resolveFuzzyCandidate($kelasVal, $kelasCandidates, 70);
                if (!$kelas) {
                    $skipped++;
                    $errors[] = "Baris {$i}: kelas '{$kelasVal}' tidak ditemukan.";
                    continue;
                }

                $mapel = $this->resolveFuzzyCandidate($mapelVal, $mapelCandidates, 68);
                if (!$mapel) {
                    $skipped++;
                    $errors[] = "Baris {$i}: mapel '{$mapelVal}' tidak ditemukan.";
                    continue;
                }

                $guru = $this->resolveFuzzyCandidate($guruVal, $guruCandidates, 72);
                if (!$guru) {
                    $skipped++;
                    $errors[] = "Baris {$i}: guru '{$guruVal}' tidak ditemukan.";
                    continue;
                }

                $exists = DataPembelajaran::where('data_kelas_id', $kelas->id)
                    ->where('data_mapel_id', $mapel->id)
                    ->first();

                if ($exists) {
                    $exists->update(['guru_id' => $guru->id]);
                    $updated++;
                } else {
                    DataPembelajaran::create([
                        'data_kelas_id' => $kelas->id,
                        'data_mapel_id' => $mapel->id,
                        'guru_id' => $guru->id,
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
            $preview = implode(' | ', array_slice($errors, 0, 5));
            return back()->with(
                'error',
                "Import selesai. Ditambah: {$created}, Diupdate: {$updated}, Dilewati: {$skipped}. {$preview}"
            );
        }

        return back()->with('success', "Import selesai. Ditambah: {$created}, Diupdate: {$updated}, Dilewati: {$skipped}.");
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

        if ($best && $bestScore >= $minScore) {
            return $best;
        }

        return null;
    }
}
