<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataGuru;
use App\Models\Peran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DataGuruController extends Controller
{
    public function index(Request $request)
    {
        $limit  = (int)($request->get('limit', 10));
        if (!in_array($limit, [10, 25, 50, 100])) $limit = 10;

        $q = trim((string)$request->get('q', ''));

        // FILTER BARU (SAMA KONSEP DENGAN DATA SISWA)
        // status: '' | '1' | '0'
        $status = $request->get('status', '');
        if (!in_array((string)$status, ['', '1', '0'], true)) $status = '';

        // jk: '' | 'L' | 'P'
        $jk = strtoupper(trim((string)$request->get('jk', '')));
        if (!in_array($jk, ['', 'L', 'P'], true)) $jk = '';

        $guru = DataGuru::with('pengguna')
            // SEARCH
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('pengguna', function ($p) use ($q) {
                    $p->where('nama', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                })
                    ->orWhere('nip', 'like', "%{$q}%")
                    ->orWhere('nuptk', 'like', "%{$q}%");
            })
            // FILTER STATUS (dari pengguna.status_aktif)
            ->when($status !== '', function ($query) use ($status) {
                $query->whereHas('pengguna', function ($p) use ($status) {
                    $p->where('status_aktif', (int)$status);
                });
            })
            // FILTER JENIS KELAMIN (dari data_guru.jenis_kelamin)
            ->when($jk !== '', function ($query) use ($jk) {
                $query->where('jenis_kelamin', $jk);
            })
            ->orderByDesc('id')
            ->paginate($limit)
            ->appends([
                'limit'  => $limit,
                'q'      => $q,
                'status' => $status,
                'jk'     => $jk,
            ]);

        return view('admin.guru.index', compact('guru', 'limit', 'q', 'status', 'jk'));
    }

    public function create()
    {
        return view('admin.guru.form', [
            'mode' => 'create',
            'guru' => null
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'          => 'required|string|max:255',
            'email'         => 'required|email|unique:pengguna,email',
            'password'      => 'required|min:6',
            'jenis_kelamin' => 'required|in:L,P',
            'status_aktif'  => 'required|in:1,0',
        ]);

        $peranGuru = Peran::where('nama_peran', 'guru_mapel')->value('id');
        if (!$peranGuru) abort(500, 'Peran guru_mapel belum tersedia');

        DB::transaction(function () use ($request, $peranGuru) {
            $pengguna = User::create([
                'peran_id'     => $peranGuru,
                'nama'         => $request->nama,
                'email'        => $request->email,
                'password'     => bcrypt($request->password),
                'status_aktif' => (bool)$request->status_aktif,
            ]);

            DataGuru::create([
                'pengguna_id'   => $pengguna->id,
                'nip'           => $request->nip,
                'nuptk'         => $request->nuptk,
                'tempat_lahir'  => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'alamat'        => $request->alamat,
                'telepon'       => $request->telepon,
            ]);
        });

        return redirect()->route('admin.guru.index')->with('success', 'Data guru berhasil disimpan');
    }

    public function show($id)
    {
        $guru = DataGuru::with('pengguna')->findOrFail($id);

        return view('admin.guru.form', [
            'mode' => 'detail',
            'guru' => $guru
        ]);
    }

    public function edit($id)
    {
        $guru = DataGuru::with('pengguna')->findOrFail($id);

        return view('admin.guru.form', [
            'mode' => 'edit',
            'guru' => $guru
        ]);
    }

    public function update(Request $request, $id)
    {
        $guru = DataGuru::with('pengguna')->findOrFail($id);

        $request->validate([
            'nama'          => 'required|string|max:255',
            'email'         => 'required|email|unique:pengguna,email,' . $guru->pengguna_id,
            'jenis_kelamin' => 'required|in:L,P',
            'status_aktif'  => 'required|in:1,0',
            'password'      => 'nullable|min:6',
        ]);

        DB::transaction(function () use ($request, $guru) {
            $updateUser = [
                'nama'         => $request->nama,
                'email'        => $request->email,
                'status_aktif' => (bool)$request->status_aktif,
            ];

            if ($request->filled('password')) {
                $updateUser['password'] = Hash::make($request->password);
            }

            $guru->pengguna->update($updateUser);

            $guru->update($request->only([
                'nip',
                'nuptk',
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'alamat',
                'telepon'
            ]));
        });

        return redirect()->route('admin.guru.index')->with('success', 'Data guru diperbarui');
    }

    public function destroy($id)
    {
        $guru = DataGuru::with('pengguna')->findOrFail($id);
        $guru->pengguna?->delete();

        return back()->with('success', 'Guru berhasil dihapus');
    }

    public function detailModal($id)
    {
        $guru = DataGuru::with('pengguna')->findOrFail($id);
        return view('admin.guru.detail-modal', compact('guru'));
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            return back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $guruList = DataGuru::with('pengguna')->whereIn('id', $ids)->get();

        DB::transaction(function () use ($guruList) {
            foreach ($guruList as $g) {
                if ($g->pengguna) {
                    $g->pengguna->delete();
                } else {
                    $g->delete();
                }
            }
        });

        return back()->with('success', 'Beberapa data guru berhasil dihapus.');
    }

    public function importCreate()
    {
        return view('admin.guru.import');
    }

    public function downloadFormatImport()
    {
        $headers = [
            'nama',
            'email',
            'password',
            'status_guru',       // AKTIF / TIDAK AKTIF
            'nip',
            'nuptk',
            'tempat_lahir',
            'tanggal_lahir',     // yyyy-mm-dd
            'jenis_kelamin',     // L / P
            'telepon',
            'alamat',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $i => $h) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '1', $h);
        }

        $sample = [
            'Ani Yulianti',
            'ani@example.com',
            'password123',
            'AKTIF',
            '1900002784726644',
            '8000000576613894',
            'Jakarta',
            '1971-10-31',
            'P',
            '08123456789',
            'Jl. Contoh No. 1',
        ];

        foreach ($sample as $i => $val) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . '2', $val);
        }

        $filename = 'FORMAT_IMPORT_DATA_GURU.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file'  => 'required|file|mimes:xlsx',
            'yakin' => 'required|in:1',
        ]);

        $peranGuru = Peran::where('nama_peran', 'guru_mapel')->value('id');
        if (!$peranGuru) abort(500, 'Peran guru_mapel belum tersedia');

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

        $required = ['nama', 'email', 'password', 'status_guru', 'jenis_kelamin'];
        foreach ($required as $r) {
            if (!isset($headerMap[$r])) {
                return back()->with('error', "Kolom '{$r}' wajib ada di file.");
            }
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($sheet, $highestRow, $headerMap, $peranGuru, $cell, &$created, &$skipped) {
            $get = function (string $key, int $row) use ($headerMap, $cell) {
                if (!isset($headerMap[$key])) return null;
                $v = $cell($headerMap[$key], $row);
                return ($v === '' ? null : $v);
            };

            for ($row = 2; $row <= $highestRow; $row++) {
                $nama = (string)$get('nama', $row);
                $email = (string)$get('email', $row);
                $password = (string)$get('password', $row);
                $statusGuru = strtoupper(trim((string)$get('status_guru', $row)));
                $jk = strtoupper(trim((string)$get('jenis_kelamin', $row)));

                if ($nama === '' && $email === '') continue;

                if ($nama === '' || $email === '' || $password === '' || !in_array($jk, ['L', 'P'])) {
                    $skipped++;
                    continue;
                }

                if (User::where('email', $email)->exists()) {
                    $skipped++;
                    continue;
                }

                $isActive = ($statusGuru === 'AKTIF');

                $pengguna = User::create([
                    'peran_id'     => $peranGuru,
                    'nama'         => $nama,
                    'email'        => $email,
                    'password'     => bcrypt($password),
                    'status_aktif' => $isActive,
                ]);

                DataGuru::create([
                    'pengguna_id'   => $pengguna->id,
                    'nip'           => $get('nip', $row),
                    'nuptk'         => $get('nuptk', $row),
                    'tempat_lahir'  => $get('tempat_lahir', $row),
                    'tanggal_lahir' => $get('tanggal_lahir', $row),
                    'jenis_kelamin' => $jk,
                    'telepon'       => $get('telepon', $row),
                    'alamat'        => $get('alamat', $row),
                ]);

                $created++;
            }
        });

        return redirect()
            ->route('admin.guru.index')
            ->with('success', "Import selesai. Berhasil: {$created}, Dilewati: {$skipped}");
    }
}
