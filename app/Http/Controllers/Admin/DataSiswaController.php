<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DataSiswa;
use App\Models\DataKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class DataSiswaController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()->peran->nama_peran === 'admin';
    }

    private function isWaliKelas(): bool
    {
        return Auth::user()->peran->nama_peran === 'guru_mapel';
    }

    private function ensureCanAccessSiswa(DataSiswa $siswa): void
    {
        if ($this->isAdmin()) {
            return;
        }

        if ($this->isWaliKelas()) {

            $kelasIds = DataKelas::where('wali_kelas_id', Auth::id())
                ->pluck('id')
                ->toArray();

            if (!in_array($siswa->data_kelas_id, $kelasIds)) {
                abort(403, 'Anda tidak berhak mengakses siswa ini.');
            }

            return;
        }

        abort(403);
    }

    public function index(Request $request)
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        $limit = (int) $request->get('limit', 10);
        if (!in_array($limit, [10, 25, 50, 100])) $limit = 10;

        $q = trim((string) $request->get('q', ''));
        $kelas = trim((string) $request->get('kelas', ''));
        $jk = strtoupper(trim((string) $request->get('jk', '')));
        if (!in_array($jk, ['', 'L', 'P'], true)) {
            $jk = '';
        }

        $status = strtolower(trim((string) $request->get('status', '')));
        if (in_array($status, ['nonaktif', 'non aktif'], true)) {
            $status = 'tidak aktif';
        }
        if (!in_array($status, ['', 'aktif', 'tidak aktif'], true)) {
            $status = '';
        }

        $siswa = DataSiswa::with('kelas')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('nama_siswa', 'like', "%{$q}%")
                        ->orWhere('nis', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            })
            ->when($kelas !== '', function ($query) use ($kelas) {
                $query->whereHas('kelas', function ($k) use ($kelas) {
                    $k->where('nama_kelas', 'like', "%{$kelas}%");
                });
            })
            ->when($jk !== '', fn($query) => $query->where('jenis_kelamin', $jk))
            ->when($status === 'aktif', function ($query) {
                $query->whereRaw("UPPER(COALESCE(status_siswa, 'AKTIF')) = 'AKTIF'");
            })
            ->when($status === 'tidak aktif', function ($query) {
                $query->whereRaw("UPPER(COALESCE(status_siswa, 'AKTIF')) <> 'AKTIF'");
            })
            ->orderBy('nama_siswa')
            ->paginate($limit)
            ->withQueryString();

        return view('admin.siswa.index', compact('siswa', 'limit', 'q', 'kelas', 'jk', 'status'));
    }

    public function create()
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        return view('admin.siswa.form', [
            'mode'  => 'create',
            'siswa' => new DataSiswa(),
            'kelas' => DataKelas::orderBy('tingkat')->orderBy('nama_kelas')->get(),
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        $data = $this->validatedData($request);

        DataSiswa::create($data);

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $siswa = DataSiswa::with('kelas')->findOrFail($id);

        $this->ensureCanAccessSiswa($siswa);

        return view('admin.siswa.form', [
            'mode'  => 'detail',
            'siswa' => $siswa,
            'kelas' => DataKelas::orderBy('tingkat')->orderBy('nama_kelas')->get(),
        ]);
    }

    public function edit(string $id)
    {
        $siswa = DataSiswa::findOrFail($id);

        $this->ensureCanAccessSiswa($siswa);

        return view('admin.siswa.form', [
            'mode'  => 'edit',
            'siswa' => $siswa,
            'kelas' => DataKelas::orderBy('tingkat')->orderBy('nama_kelas')->get(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $siswa = DataSiswa::findOrFail($id);

        $this->ensureCanAccessSiswa($siswa);

        $data = $this->validatedData($request);

        $siswa->update($data);

        if ($this->isAdmin()) {
            return redirect()
                ->route('admin.siswa.index')
                ->with('success', 'Data siswa berhasil diperbarui');
        }

        return redirect()
            ->route('guru.wali-kelas.siswa.index')
            ->with('success', 'Data siswa berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        DataSiswa::findOrFail($id)->delete();

        return redirect()
            ->route('admin.siswa.index')
            ->with('success', 'Data siswa berhasil dihapus');
    }

    /**
     * ============================
     * DOWNLOAD FORMAT IMPORT (XLSX)
     * ============================
     */
    public function downloadFormatImport(): StreamedResponse
    {
        if (!$this->isAdmin()) abort(403);

        $headers = [
            'nama_siswa',
            'kelas',
            'nis',
            'nisn',
            'tempat_lahir',
            'tanggal_lahir',      // YYYY-MM-DD
            'jenis_kelamin',      // L/P
            'agama',
            'status_dalam_keluarga',
            'anak_ke',
            'alamat',
            'telepon',
            'sekolah_asal',
            'diterima_di_kelas',
            'tanggal_diterima',   // YYYY-MM-DD
            'nama_ayah',
            'pekerjaan_ayah',
            'nama_ibu',
            'pekerjaan_ibu',
            'alamat_orang_tua',
            'telepon_orang_tua',
            'nama_wali',
            'pekerjaan_wali',
            'alamat_wali',
            'telepon_wali',
            'status_siswa',       // AKTIF/TIDAK AKTIF
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Format Import Siswa');

        // Header row
        foreach ($headers as $i => $h) {
            $col = Coordinate::stringFromColumnIndex($i + 1); // 1->A, 2->B, dst
            $sheet->setCellValue($col . '1', $h);
        }

        // Contoh 2 baris data.
        $kelasContoh = DataKelas::orderBy('id')->value('nama_kelas') ?? 'X TKJ 1';
        $samples = [
            [
                'Ahmad Fauzan',
                $kelasContoh,
                '24010001',
                '0076543211',
                'Bekasi',
                '2008-05-12',
                'L',
                'Islam',
                'Anak Kandung',
                '1',
                'Jl. Melati No. 10',
                '081234567890',
                'SMPN 1 Bekasi',
                'X',
                '2024-07-15',
                'Budi Santoso',
                'Karyawan',
                'Siti Aminah',
                'Ibu Rumah Tangga',
                'Jl. Melati No. 10',
                '081298765432',
                '',
                '',
                '',
                '',
                'AKTIF',
            ],
            [
                'Nabila Putri',
                $kelasContoh,
                '24010002',
                '0076543212',
                'Jakarta',
                '2008-11-03',
                'P',
                'Islam',
                'Anak Kandung',
                '2',
                'Jl. Kenanga No. 22',
                '082233445566',
                'SMPN 5 Jakarta',
                'X',
                '2024-07-15',
                'Rahmat Hidayat',
                'Wiraswasta',
                'Dewi Lestari',
                'Guru',
                'Jl. Kenanga No. 22',
                '082211009988',
                '',
                '',
                '',
                '',
                'AKTIF',
            ],
        ];

        foreach ($samples as $rowIdx => $sample) {
            $excelRow = $rowIdx + 2;
            foreach ($sample as $i => $val) {
                $col = Coordinate::stringFromColumnIndex($i + 1);
                $sheet->setCellValue($col . $excelRow, $val);
            }
        }

        $sheet->freezePane('A2');

        $filename = 'format_import_data_siswa.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * ============================
     * IMPORT DATA SISWA (XLSX)
     * ============================
     */
    public function import(Request $request)
    {
        if (!$this->isAdmin()) abort(403);

        $request->validate([
            'file' => 'required|file|mimes:xlsx',
            'confirm' => 'required|accepted',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes' => 'File harus berformat XLSX.',
            'confirm.required' => 'Checklist konfirmasi wajib dicentang.',
            'confirm.accepted' => 'Checklist konfirmasi wajib dicentang.',
        ]);

        $path = $request->file('file')->getRealPath();

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return back()->with('error', 'File kosong / tidak ada data.');
        }

        // Ambil header dari baris 1
        $headerRow = $rows[1];
        $headers = [];
        foreach ($headerRow as $col => $name) {
            $name = trim((string) $name);
            if ($name !== '') $headers[$col] = $name;
        }

        $requiredHeaders = [
            'nama_siswa',
            'kelas',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'agama'
        ];

        foreach ($requiredHeaders as $rh) {
            if (!in_array($rh, array_values($headers))) {
                return back()->with('error', "Header '{$rh}' tidak ditemukan. Download format import dan gunakan format tersebut.");
            }
        }

        $success = 0;
        $updated = 0;
        $errors = [];

        // mulai dari baris 2
        for ($i = 2; $i <= count($rows); $i++) {
            $row = $rows[$i] ?? null;
            if (!$row) continue;

            $get = function (string $key) use ($headers, $row) {
                $col = array_search($key, $headers, true);
                if ($col === false) return null;
                $val = $row[$col] ?? null;
                if (is_string($val)) $val = trim($val);
                return $val === '' ? null : $val;
            };

            $nama = $get('nama_siswa');
            $kelasNama = $get('kelas');
            if (!$nama && !$kelasNama) continue;

            $tempatLahir = $get('tempat_lahir');
            $tglLahirRaw = $get('tanggal_lahir');
            $jk = $get('jenis_kelamin');
            $agama = $get('agama');

            if (!$nama || !$kelasNama || !$tempatLahir || !$tglLahirRaw || !$jk || !$agama) {
                $errors[] = "Baris {$i}: kolom wajib kosong (nama_siswa/kelas/tempat_lahir/tanggal_lahir/jenis_kelamin/agama).";
                continue;
            }

            $kelas = DataKelas::where('nama_kelas', $kelasNama)->first();
            if (!$kelas) {
                $errors[] = "Baris {$i}: kelas '{$kelasNama}' tidak ditemukan di master Data Kelas.";
                continue;
            }

            try {
                $tanggalLahir = is_numeric($tglLahirRaw)
                    ? ExcelDate::excelToDateTimeObject($tglLahirRaw)->format('Y-m-d')
                    : date('Y-m-d', strtotime((string) $tglLahirRaw));
            } catch (\Throwable $e) {
                $errors[] = "Baris {$i}: tanggal_lahir tidak valid.";
                continue;
            }

            if (!in_array($jk, ['L', 'P'], true)) {
                $errors[] = "Baris {$i}: jenis_kelamin harus L atau P.";
                continue;
            }

            $tglTerimaRaw = $get('tanggal_diterima');
            $tanggalDiterima = null;
            if ($tglTerimaRaw !== null) {
                try {
                    $tanggalDiterima = is_numeric($tglTerimaRaw)
                        ? ExcelDate::excelToDateTimeObject($tglTerimaRaw)->format('Y-m-d')
                        : date('Y-m-d', strtotime((string) $tglTerimaRaw));
                } catch (\Throwable $e) {
                    $errors[] = "Baris {$i}: tanggal_diterima tidak valid.";
                    continue;
                }
            }

            $statusSiswa = strtoupper((string) ($get('status_siswa') ?? 'AKTIF'));
            if (!in_array($statusSiswa, ['AKTIF', 'TIDAK AKTIF'], true)) {
                $errors[] = "Baris {$i}: status_siswa harus AKTIF atau TIDAK AKTIF.";
                continue;
            }

            $nis = $get('nis');
            $nisn = $get('nisn');
            $nis = $nis !== null ? trim((string) $nis) : null;
            $nisn = $nisn !== null ? trim((string) $nisn) : null;
            $nis = $nis === '' ? null : $nis;
            $nisn = $nisn === '' ? null : $nisn;

            $payload = [
                'data_kelas_id' => $kelas->id,
                'nama_siswa' => $nama,
                'nis' => $nis,
                'nisn' => $nisn,
                'tempat_lahir' => $tempatLahir,
                'tanggal_lahir' => $tanggalLahir,
                'jenis_kelamin' => $jk,
                'agama' => $agama,
                'status_dalam_keluarga' => $get('status_dalam_keluarga'),
                'anak_ke' => $get('anak_ke'),
                'alamat' => $get('alamat'),
                'telepon' => $get('telepon'),
                'sekolah_asal' => $get('sekolah_asal'),
                'diterima_di_kelas' => $get('diterima_di_kelas'),
                'tanggal_diterima' => $tanggalDiterima,
                'nama_ayah' => $get('nama_ayah'),
                'pekerjaan_ayah' => $get('pekerjaan_ayah'),
                'nama_ibu' => $get('nama_ibu'),
                'pekerjaan_ibu' => $get('pekerjaan_ibu'),
                'alamat_orang_tua' => $get('alamat_orang_tua'),
                'telepon_orang_tua' => $get('telepon_orang_tua'),
                'nama_wali' => $get('nama_wali'),
                'pekerjaan_wali' => $get('pekerjaan_wali'),
                'alamat_wali' => $get('alamat_wali'),
                'telepon_wali' => $get('telepon_wali'),
                'status_siswa' => $statusSiswa,
            ];

            try {
                $existing = null;
                if ($nis !== null) {
                    $existing = DataSiswa::where('nis', $nis)->first();
                }
                if (!$existing && $nisn !== null) {
                    $existing = DataSiswa::where('nisn', $nisn)->first();
                }

                if ($existing) {
                    if ($nis !== null && DataSiswa::where('nis', $nis)->where('id', '!=', $existing->id)->exists()) {
                        $errors[] = "Baris {$i}: NIS {$nis} sudah dipakai siswa lain.";
                        continue;
                    }
                    if ($nisn !== null && DataSiswa::where('nisn', $nisn)->where('id', '!=', $existing->id)->exists()) {
                        $errors[] = "Baris {$i}: NISN {$nisn} sudah dipakai siswa lain.";
                        continue;
                    }

                    $existing->update($payload);
                    $updated++;
                } else {
                    DataSiswa::create($payload);
                    $success++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Baris {$i}: gagal diproses ({$e->getMessage()}).";
            }
        }

        if (count($errors) > 0) {
            $preview = array_slice($errors, 0, 15);
            $msg = "Import selesai. Ditambah: {$success}, Diupdate: {$updated}, Gagal: " . count($errors) . ".\n- " . implode("\n- ", $preview);
            if (count($errors) > 15) $msg .= "\n...dan lainnya.";
            return back()->with('error', nl2br(e($msg)));
        }

        return back()->with('success', "Import berhasil. Ditambah: {$success}, Diupdate: {$updated}");
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'data_kelas_id'        => 'required|exists:data_kelas,id',
            'nama_siswa'           => 'required|string|max:255',
            'nis'                  => 'nullable|string|max:50',
            'nisn'                 => 'nullable|string|max:50',
            'tempat_lahir'         => 'required|string|max:100',
            'tanggal_lahir'        => 'required|date',
            'jenis_kelamin'        => 'required|in:L,P',
            'agama'                => 'required|string|max:50',
            'status_dalam_keluarga' => 'nullable|string|max:50',
            'anak_ke'              => 'nullable|integer',
            'alamat'               => 'nullable|string',
            'telepon'              => 'nullable|string|max:20',
            'sekolah_asal'         => 'nullable|string|max:255',
            'diterima_di_kelas'    => 'nullable|string|max:50',
            'tanggal_diterima'     => 'nullable|date',
            'nama_ayah'            => 'nullable|string|max:255',
            'pekerjaan_ayah'       => 'nullable|string|max:255',
            'nama_ibu'             => 'nullable|string|max:255',
            'pekerjaan_ibu'        => 'nullable|string|max:255',
            'alamat_orang_tua'     => 'nullable|string',
            'telepon_orang_tua'    => 'nullable|string|max:20',
            'nama_wali'            => 'nullable|string|max:255',
            'pekerjaan_wali'       => 'nullable|string|max:255',
            'alamat_wali'          => 'nullable|string',
            'telepon_wali'         => 'nullable|string|max:20',
            'status_siswa'         => 'nullable|string|max:20',
        ]);
    }

    public function destroyMultiple(Request $request)
    {
        if (!$this->isAdmin()) abort(403);

        $ids = $request->input('ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->back()->with('error', 'Tidak ada siswa yang dipilih.');
        }

        DataSiswa::whereIn('id', $ids)->delete();

        return redirect()->route('admin.siswa.index')
            ->with('success', 'Data siswa terpilih berhasil dihapus.');
    }

    public function importCreate()
    {
        if (!$this->isAdmin()) abort(403);

        return view('admin.siswa.import');
    }
}
