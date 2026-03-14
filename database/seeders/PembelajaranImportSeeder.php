<?php

namespace Database\Seeders;

use App\Models\DataKelas;
use App\Models\DataMapel;
use App\Models\DataPembelajaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PembelajaranImportSeeder extends Seeder
{
    public function run(): void
    {
        $path = $this->resolveImportPath();
        if (!is_file($path)) {
            $this->command?->warn('File import pembelajaran tidak ditemukan.');
            $this->command?->warn('Letakkan file di salah satu path berikut:');
            $this->command?->warn('- ' . base_path('storage/app/import/pembelajaran.xlsx'));
            $this->command?->warn('- ' . base_path('storage/app/import/Mata Pelajaran dan Kelas.xlsx'));
            $this->command?->warn('- ' . base_path('database/data/pembelajaran.xlsx'));
            $this->command?->warn('Atau set env PEMBELAJARAN_XLSX_PATH ke path file Anda.');
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('Sheet1') ?? $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            $this->command?->warn('Sheet kosong / tidak ada data.');
            return;
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
            $this->command?->error('Header wajib tidak ditemukan. Minimal harus ada kolom kelas, mapel, dan guru.');
            return;
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
            $this->command?->error('Import gagal: ' . $e->getMessage());
            return;
        }

        $this->command?->info("Import selesai. Ditambah: {$created}, Diupdate: {$updated}, Dilewati: {$skipped}.");
        if ($errors !== []) {
            foreach (array_slice($errors, 0, 10) as $error) {
                $this->command?->warn($error);
            }
        }
    }

    private function resolveImportPath(): string
    {
        $candidates = [];

        $envPath = (string) env('PEMBELAJARAN_XLSX_PATH', '');
        if ($envPath !== '') {
            $candidates[] = $envPath;
        }

        $candidates = array_merge($candidates, [
            'G:\My Drive\Tahun Pelajaran 2025-2026\Semester Ganjil 2025-2026\Mata Pelajaran dan Kelas.xlsx',
            base_path('storage/app/import/pembelajaran.xlsx'),
            base_path('storage/app/import/Mata Pelajaran dan Kelas.xlsx'),
            base_path('database/data/pembelajaran.xlsx'),
            base_path('database/data/Mata Pelajaran dan Kelas.xlsx'),
        ]);

        foreach ($candidates as $path) {
            if ($path !== '' && is_file($path)) {
                return $path;
            }
        }

        $globbed = glob(base_path('storage/app/import/*.xlsx')) ?: [];
        if ($globbed !== []) {
            return (string) $globbed[0];
        }

        return base_path('storage/app/import/pembelajaran.xlsx');
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
