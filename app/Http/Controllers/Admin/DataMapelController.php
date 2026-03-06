<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataJurusan;
use App\Models\DataMapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataMapelController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(Auth::user()->peran->nama_peran === 'admin', 403);
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $limit = (int) $request->get('limit', 10);
        if (!in_array($limit, [10, 25, 50, 100], true)) {
            $limit = 10;
        }

        $q = trim((string) $request->get('q', ''));

        $tingkat = strtoupper(trim((string) $request->get('tingkat', 'all')));
        if (!in_array($tingkat, ['ALL', 'SEMUA', 'X', 'XI', 'XII'], true)) {
            $tingkat = 'ALL';
        }

        $jurusanId = trim((string) $request->get('jurusan_id', 'all'));
        if ($jurusanId !== 'all' && $jurusanId !== 'umum' && !ctype_digit($jurusanId)) {
            $jurusanId = 'all';
        }

        $kelompok = trim((string) $request->get('kelompok_mapel', 'all'));
        $allowedKelompok = ['Mata Pelajaran Umum', 'Mata Pelajaran Kejuruan'];
        if ($kelompok !== 'all' && !in_array($kelompok, $allowedKelompok, true)) {
            $kelompok = 'all';
        }

        $mapel = DataMapel::query()
            ->with('jurusan')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('nama_mapel', 'like', "%{$q}%")
                        ->orWhere('singkatan', 'like', "%{$q}%")
                        ->orWhere('kelompok_mapel', 'like', "%{$q}%");
                });
            })
            ->when($tingkat !== 'ALL', fn($query) => $query->where('tingkat', $tingkat))
            ->when($jurusanId === 'umum', fn($query) => $query->whereNull('jurusan_id'))
            ->when(ctype_digit($jurusanId), fn($query) => $query->where('jurusan_id', (int) $jurusanId))
            ->when($kelompok !== 'all', fn($query) => $query->where('kelompok_mapel', $kelompok))
            ->orderByRaw("CASE WHEN tingkat = 'SEMUA' THEN 0 WHEN tingkat = 'X' THEN 1 WHEN tingkat = 'XI' THEN 2 WHEN tingkat = 'XII' THEN 3 ELSE 9 END")
            ->orderByRaw('CASE WHEN jurusan_id IS NULL THEN 0 ELSE 1 END')
            ->orderByRaw('COALESCE(urutan_cetak, 999999)')
            ->orderBy('nama_mapel')
            ->paginate($limit)
            ->withQueryString();

        $jurusan = DataJurusan::orderBy('kode_jurusan')->get();

        return view('admin.mapel.index', [
            'mapel' => $mapel,
            'jurusan' => $jurusan,
            'limit' => $limit,
            'q' => $q,
            'tingkat' => $tingkat === 'ALL' ? 'all' : $tingkat,
            'jurusanId' => $jurusanId,
            'kelompok' => $kelompok,
        ]);
    }

    public function create()
    {
        $this->ensureAdmin();

        return view('admin.mapel.form', [
            'mapel' => null,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $this->validatedData($request);

        DataMapel::create($data);

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', 'Data mata pelajaran berhasil ditambahkan.');
    }

    public function show(string $id)
    {
        $this->ensureAdmin();

        return redirect()->route('admin.mapel.edit', $id);
    }

    public function edit(string $id)
    {
        $this->ensureAdmin();

        return view('admin.mapel.form', [
            'mapel' => DataMapel::findOrFail($id),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $this->ensureAdmin();

        $mapel = DataMapel::findOrFail($id);
        $data = $this->validatedData($request, true);

        $mapel->update($data);

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', 'Data mata pelajaran berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $this->ensureAdmin();

        DataMapel::findOrFail($id)->delete();

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', 'Data mata pelajaran berhasil dihapus.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->ensureAdmin();

        $q = trim((string) $request->get('q', ''));
        $tingkat = strtoupper(trim((string) $request->get('tingkat', 'all')));
        $jurusanId = trim((string) $request->get('jurusan_id', 'all'));
        $kelompok = trim((string) $request->get('kelompok_mapel', 'all'));

        $rows = DataMapel::query()
            ->with('jurusan')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('nama_mapel', 'like', "%{$q}%")
                        ->orWhere('singkatan', 'like', "%{$q}%")
                        ->orWhere('kelompok_mapel', 'like', "%{$q}%");
                });
            })
            ->when(in_array($tingkat, ['SEMUA', 'X', 'XI', 'XII'], true), fn($query) => $query->where('tingkat', $tingkat))
            ->when($jurusanId === 'umum', fn($query) => $query->whereNull('jurusan_id'))
            ->when(ctype_digit($jurusanId), fn($query) => $query->where('jurusan_id', (int) $jurusanId))
            ->when(in_array($kelompok, ['Mata Pelajaran Umum', 'Mata Pelajaran Kejuruan'], true), fn($query) => $query->where('kelompok_mapel', $kelompok))
            ->orderByRaw("CASE WHEN tingkat = 'SEMUA' THEN 0 WHEN tingkat = 'X' THEN 1 WHEN tingkat = 'XI' THEN 2 WHEN tingkat = 'XII' THEN 3 ELSE 9 END")
            ->orderByRaw('CASE WHEN jurusan_id IS NULL THEN 0 ELSE 1 END')
            ->orderByRaw('COALESCE(urutan_cetak, 999999)')
            ->orderBy('nama_mapel')
            ->get();

        $filename = 'data-mapel-' . date('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['nama_mapel', 'singkatan', 'tingkat', 'jurusan', 'kelompok_mapel', 'urutan_cetak']);

            foreach ($rows as $m) {
                fputcsv($out, [
                    $m->nama_mapel,
                    $m->singkatan,
                    $m->tingkat ?? 'SEMUA',
                    $m->jurusan->kode_jurusan ?? 'UMUM',
                    $m->kelompok_mapel,
                    $m->urutan_cetak,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadFormatImport(): StreamedResponse
    {
        $this->ensureAdmin();

        $headers = [
            'nama_mapel',
            'singkatan',
            'tingkat',
            'jurusan_kode',
            'kelompok_mapel',
            'urutan_cetak',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $i => $h) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
        }

        $sample = [
            'Pendidikan Agama Islam',
            'PAI',
            'SEMUA',
            'UMUM',
            'Mata Pelajaran Umum',
            1,
        ];

        foreach ($sample as $i => $val) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '2', $val);
        }

        $filename = 'format_import_data_mapel.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $this->ensureAdmin();

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
        $sheet = $spreadsheet->getActiveSheet();
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

        $required = ['nama_mapel', 'singkatan', 'tingkat', 'kelompok_mapel', 'urutan_cetak'];
        foreach ($required as $colName) {
            if (!isset($headerMap[$colName])) {
                return back()->with('error', "Header '{$colName}' tidak ditemukan. Gunakan format import yang tersedia.");
            }
        }

        $jurusanByKode = DataJurusan::query()
            ->get()
            ->keyBy(fn($j) => strtoupper(trim((string) $j->kode_jurusan)));

        $created = 0;
        $skipped = 0;

        for ($i = 2; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? null;
            if (!$row) {
                continue;
            }

            $get = function (string $name) use ($headerMap, $row) {
                $col = $headerMap[$name] ?? null;
                if (!$col) {
                    return null;
                }

                $value = $row[$col] ?? null;
                if (is_string($value)) {
                    $value = trim($value);
                }

                return $value === '' ? null : $value;
            };

            $nama = $get('nama_mapel');
            $singkatan = $get('singkatan');
            $tingkat = strtoupper((string) ($get('tingkat') ?? 'SEMUA'));
            $jurusanKode = strtoupper((string) ($get('jurusan_kode') ?? 'UMUM'));
            $kelompokMapel = $get('kelompok_mapel');
            $urutanCetak = $get('urutan_cetak');

            if (!$nama && !$singkatan && !$kelompokMapel) {
                continue;
            }

            if (!$nama || !$singkatan || !$kelompokMapel || !is_numeric($urutanCetak)) {
                $skipped++;
                continue;
            }

            if (!in_array($tingkat, ['SEMUA', 'X', 'XI', 'XII'], true)) {
                $skipped++;
                continue;
            }

            if (!in_array($kelompokMapel, ['Mata Pelajaran Umum', 'Mata Pelajaran Kejuruan'], true)) {
                $skipped++;
                continue;
            }

            $jurusanId = null;
            if ($jurusanKode !== '' && $jurusanKode !== 'UMUM') {
                $jurusan = $jurusanByKode->get($jurusanKode);
                if (!$jurusan) {
                    $skipped++;
                    continue;
                }
                $jurusanId = $jurusan->id;
            }

            DataMapel::create([
                'nama_mapel' => $nama,
                'singkatan' => strtoupper((string) $singkatan),
                'tingkat' => $tingkat,
                'jurusan_id' => $jurusanId,
                'kelompok_mapel' => $kelompokMapel,
                'urutan_cetak' => (int) $urutanCetak,
            ]);

            $created++;
        }

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', "Import selesai. Berhasil: {$created}, Dilewati: {$skipped}");
    }

    private function validatedData(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'nama_mapel' => 'required|string|max:255',
            'singkatan' => 'required|string|max:30',
            'kelompok_mapel' => 'required|in:Mata Pelajaran Umum,Mata Pelajaran Kejuruan',
            'urutan_cetak' => 'required|integer|min:1|max:9999',
            'tingkat' => 'nullable|in:SEMUA,X,XI,XII',
            'jurusan_id' => 'nullable|exists:data_jurusan,id',
        ];

        if ($isUpdate) {
            $rules['tingkat'] = 'sometimes|nullable|in:SEMUA,X,XI,XII';
            $rules['jurusan_id'] = 'sometimes|nullable|exists:data_jurusan,id';
        }

        $data = $request->validate($rules);

        if (!array_key_exists('tingkat', $data) || is_null($data['tingkat']) || $data['tingkat'] === '') {
            $data['tingkat'] = 'SEMUA';
        }

        if (!array_key_exists('jurusan_id', $data) || $data['jurusan_id'] === '') {
            $data['jurusan_id'] = null;
        }

        $data['singkatan'] = strtoupper(trim((string) $data['singkatan']));
        $data['nama_mapel'] = trim((string) $data['nama_mapel']);

        return $data;
    }
}

