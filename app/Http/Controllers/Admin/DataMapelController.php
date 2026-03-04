<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataMapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\DB;

class DataMapelController extends Controller
{
    private function assertAdmin()
    {
        abort_unless(Auth::user()?->peran?->nama_peran === 'admin', 403);
    }

    public function index()
    {
        $this->assertAdmin();

        $mapel = DataMapel::orderByRaw('COALESCE(urutan_cetak, 999999) ASC')
            ->orderBy('nama_mapel')
            ->paginate(10);

        return view('admin.mapel.index', compact('mapel'));
    }

    public function create()
    {
        $this->assertAdmin();

        return view('admin.mapel.form', [
            'mapel' => null
        ]);
    }

    public function store(Request $request)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'nama_mapel'     => 'required|string|max:255',
            'singkatan'      => 'required|string|max:30',
            'urutan_cetak'   => 'required|integer|min:1|max:9999',
            'kelompok_mapel' => 'required|in:Mata Pelajaran Umum,Mata Pelajaran Kejuruan,Mata Pelajaran Pilihan,Muatan Lokal',

            // kolom baru
            'tingkat'        => 'required|in:X,XI,XII,SEMUA',
            'jurusan_id'     => 'nullable|integer|min:1',
        ]);

        DataMapel::create($data);

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', 'Data mata pelajaran berhasil ditambahkan');
    }

    public function edit($id)
    {
        $this->assertAdmin();

        $mapel = DataMapel::findOrFail($id);

        return view('admin.mapel.form', compact('mapel'));
    }

    public function update(Request $request, $id)
    {
        $this->assertAdmin();

        $mapel = DataMapel::findOrFail($id);

        $data = $request->validate([
            'nama_mapel'     => 'required|string|max:255',
            'singkatan'      => 'required|string|max:30',
            'urutan_cetak'   => 'required|integer|min:1|max:9999',
            'kelompok_mapel' => 'required|in:Mata Pelajaran Umum,Mata Pelajaran Kejuruan,Mata Pelajaran Pilihan,Muatan Lokal',

            // kolom baru
            'tingkat'        => 'required|in:X,XI,XII,SEMUA',
            'jurusan_id'     => 'nullable|integer|min:1',
        ]);

        $mapel->update($data);

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', 'Data mata pelajaran berhasil diperbarui');
    }

    public function destroy($id)
    {
        $this->assertAdmin();

        $mapel = DataMapel::findOrFail($id);
        $mapel->delete();

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', 'Data mata pelajaran berhasil dihapus');
    }

    public function export()
    {
        $this->assertAdmin();

        $rows = DataMapel::query()
            ->orderByRaw("FIELD(tingkat,'SEMUA','X','XI','XII') ASC")
            ->orderByRaw("COALESCE(jurusan_id, 0) ASC")
            ->orderBy('urutan_cetak')
            ->orderBy('nama_mapel')
            ->get();

        $headers = ['no', 'nama_mapel', 'singkatan', 'kelompok_mapel', 'tingkat', 'jurusan_id', 'urutan_cetak'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Mapel');

        foreach ($headers as $i => $h) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
        }

        $r = 2;
        foreach ($rows as $i => $m) {
            $sheet->setCellValue("A{$r}", $i + 1);
            $sheet->setCellValue("B{$r}", $m->nama_mapel ?? '');
            $sheet->setCellValue("C{$r}", $m->singkatan ?? '');
            $sheet->setCellValue("D{$r}", $m->kelompok_mapel ?? '');
            $sheet->setCellValue("E{$r}", $m->tingkat ?? 'SEMUA');
            $sheet->setCellValue("F{$r}", $m->jurusan_id ?? '');
            $sheet->setCellValue("G{$r}", $m->urutan_cetak ?? '');
            $r++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'EXPORT_DATA_MAPEL_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadFormatImport()
    {
        $this->assertAdmin();

        $headers = [
            'nama_mapel',
            'singkatan',
            'kelompok_mapel',
            'tingkat',      // X / XI / XII / SEMUA
            'jurusan_id',   // kosongkan untuk mapel umum
            'urutan_cetak',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $i => $h) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
        }

        $samples = [
            ['Matematika', 'MTK', 'Mata Pelajaran Umum', 'SEMUA', '', 1],
            ['Pemrograman Dasar', 'PD', 'Mata Pelajaran Kejuruan', 'X', 3, 2],
        ];

        $row = 2;
        foreach ($samples as $s) {
            $sheet->setCellValue("A{$row}", $s[0]);
            $sheet->setCellValue("B{$row}", $s[1]);
            $sheet->setCellValue("C{$row}", $s[2]);
            $sheet->setCellValue("D{$row}", $s[3]);
            $sheet->setCellValue("E{$row}", $s[4]);
            $sheet->setCellValue("F{$row}", $s[5]);
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'FORMAT_IMPORT_DATA_MAPEL.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $this->assertAdmin();

        $request->validate([
            'file'  => 'required|file|mimes:xlsx',
            'yakin' => 'required|in:1',
        ]);

        $path = $request->file('file')->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow();
        $highestColIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());

        $cell = function (int $colIndex, int $row) use ($sheet) {
            $col = Coordinate::stringFromColumnIndex($colIndex);
            $addr = $col . $row;
            $v = $sheet->getCell($addr)->getValue();
            return is_string($v) ? trim($v) : $v;
        };

        $headerMap = [];
        for ($c = 1; $c <= $highestColIndex; $c++) {
            $h = strtolower(trim((string)$cell($c, 1)));
            if ($h !== '') $headerMap[$h] = $c;
        }

        $required = ['nama_mapel', 'singkatan', 'kelompok_mapel', 'tingkat', 'urutan_cetak'];
        foreach ($required as $r) {
            if (!isset($headerMap[$r])) {
                return back()->with('error', "Kolom '{$r}' wajib ada di file.");
            }
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($highestRow, $headerMap, $cell, &$created, &$skipped) {

            $get = function (string $key, int $row) use ($headerMap, $cell) {
                if (!isset($headerMap[$key])) return null;
                $v = $cell($headerMap[$key], $row);
                return ($v === '' ? null : $v);
            };

            $kelompokAllowed = [
                'Mata Pelajaran Umum',
                'Mata Pelajaran Kejuruan',
                'Mata Pelajaran Pilihan',
                'Muatan Lokal',
            ];

            for ($row = 2; $row <= $highestRow; $row++) {

                $nama      = (string)($get('nama_mapel', $row) ?? '');
                $singkatan = (string)($get('singkatan', $row) ?? '');
                $kelompok  = (string)($get('kelompok_mapel', $row) ?? '');
                $tingkat   = strtoupper(trim((string)($get('tingkat', $row) ?? '')));
                $jurRaw    = $get('jurusan_id', $row);
                $urutan    = $get('urutan_cetak', $row);

                // skip baris kosong
                if (trim($nama) === '' && trim($singkatan) === '' && trim($kelompok) === '') {
                    continue;
                }

                $kelompokValid = in_array($kelompok, $kelompokAllowed, true);
                $tingkatValid  = in_array($tingkat, ['X', 'XI', 'XII', 'SEMUA'], true);
                $urutanInt     = is_numeric($urutan) ? (int)$urutan : 0;

                $jurusanId = is_numeric($jurRaw) ? (int)$jurRaw : null;
                if ($jurusanId !== null && $jurusanId <= 0) $jurusanId = null;

                if (trim($nama) === '' || trim($singkatan) === '' || !$kelompokValid || !$tingkatValid || $urutanInt <= 0) {
                    $skipped++;
                    continue;
                }

                // cegah duplikat (nama + kelompok + tingkat + jurusan_id)
                $exists = DataMapel::where('nama_mapel', $nama)
                    ->where('kelompok_mapel', $kelompok)
                    ->where('tingkat', $tingkat)
                    ->where(function ($q) use ($jurusanId) {
                        if ($jurusanId === null) $q->whereNull('jurusan_id');
                        else $q->where('jurusan_id', $jurusanId);
                    })
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                DataMapel::create([
                    'nama_mapel'     => $nama,
                    'singkatan'      => $singkatan,
                    'kelompok_mapel' => $kelompok,
                    'tingkat'        => $tingkat,
                    'jurusan_id'     => $jurusanId,
                    'urutan_cetak'   => $urutanInt,
                ]);

                $created++;
            }
        });

        return redirect()
            ->route('admin.mapel.index')
            ->with('success', "Import selesai. Berhasil: {$created}, Dilewati: {$skipped}");
    }
}
