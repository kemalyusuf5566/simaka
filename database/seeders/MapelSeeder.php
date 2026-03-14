<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MapelSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $mapelNames = $this->extractMapelFromJadwalText(base_path('storage/app/jadwal_xlsx_out.txt'));

        // Fallback minimum jika file jadwal tidak tersedia.
        if ($mapelNames === []) {
            $mapelNames = [
                'Pendidikan Agama Islam',
                'Pendidikan Pancasila',
                'Bahasa Indonesia',
                'Bahasa Inggris',
                'Matematika',
                'Informatika',
                'Sejarah',
                'PJOK',
                'Seni Budaya',
            ];
        }

        foreach ($mapelNames as $idx => $name) {
            DB::table('data_mapel')->updateOrInsert(
                ['nama_mapel' => $name],
                [
                    'singkatan' => $this->makeSingkatan($name),
                    'kelompok_mapel' => null,
                    'tingkat' => 'SEMUA',
                    'jurusan_id' => null,
                    'urutan_cetak' => $idx + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    private function extractMapelFromJadwalText(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $raw = (string) file_get_contents($path);
        if (trim($raw) === '') {
            return [];
        }

        $lines = preg_split('/\R/u', $raw) ?: [];
        $rows = [];
        $current = null;
        foreach ($lines as $line) {
            $line = rtrim($line);
            if ($line === '' || str_starts_with($line, '===')) {
                continue;
            }

            if (preg_match('/^\d+\|/', $line) === 1) {
                if ($current !== null) {
                    $rows[] = $current;
                }
                $current = $line;
            } elseif ($current !== null) {
                $current .= "\n" . $line;
            }
        }
        if ($current !== null) {
            $rows[] = $current;
        }

        $teacherAliases = $this->teacherAliases();
        $mapelSet = [];

        foreach ($rows as $row) {
            $parts = array_map('trim', explode('|', $row));
            if (count($parts) < 26) {
                continue;
            }

            $className = strtoupper((string) ($parts[1] ?? ''));
            if (in_array($className, ['HARI', 'KELAS', 'PIKET'], true) || is_numeric($className) || $className === '\\') {
                continue;
            }

            $cells = array_slice($parts, 2, 24);
            foreach ($cells as $cell) {
                $cell = trim((string) $cell);
                if ($cell === '' || strtoupper($cell) === 'PIKET') {
                    continue;
                }

                $mapel = $this->stripTeacherName($cell, $teacherAliases);
                $mapel = $this->normalizeMapel($mapel);
                if ($mapel === '') {
                    continue;
                }
                $mapelSet[$mapel] = true;
            }
        }

        $names = array_keys($mapelSet);
        sort($names);
        return $names;
    }

    private function stripTeacherName(string $cell, array $teacherAliases): string
    {
        $flat = trim((string) preg_replace('/\s+/u', ' ', $cell));
        foreach ($teacherAliases as $alias) {
            $pattern = '/' . preg_quote($alias, '/') . '/iu';
            $flat = (string) preg_replace($pattern, ' ', $flat);
        }
        return trim((string) preg_replace('/\s+/u', ' ', $flat));
    }

    private function normalizeMapel(string $text): string
    {
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));
        $text = preg_replace('/\b[0-9]+\b/u', ' ', $text) ?? $text;
        $text = trim((string) preg_replace('/\s+/u', ' ', $text), " -.,;:\t\n\r\0\x0B");
        if ($text === '' || strlen($text) < 2) {
            return '';
        }
        if (mb_strlen($text) > 190) {
            $text = mb_substr($text, 0, 190);
        }
        return $text;
    }

    private function makeSingkatan(string $name): string
    {
        $clean = strtoupper((string) preg_replace('/[^A-Za-z0-9]+/', '', $name));
        return $clean !== '' ? substr($clean, 0, 20) : 'MAPEL';
    }

    private function teacherAliases(): array
    {
        $fullNames = [
            'Acep Adit, S.Hum', 'Aditya Rachman Mulana, S.Tr.T', 'Agus Prasetio, S.Pd', 'Agus Sobari',
            'Ahmad Rafif Fauzi, S.Pd', 'Ai Cahyaningsih, S.Pd', 'Aldi Ridwan, S.E', 'Amelia Sugiharti',
            'Anggita Eka Sowandini, S.Li', 'Annisa Luthfiastuti, S.Pd', 'Aris Makmudin', 'Arya Wijaya Kusuma',
            'Astina, SE', 'Bisri Mustofa, S.Pd', 'Baskoro Ahnaf Nugroho, S.Kom', 'Diah Lutfi Khasani, S.Pd',
            'Dian Resti Kurniawati, S.S', 'Dimas Riki Adam', 'Dra. Mulyati', 'Dwiayu Hadilawati, S.Pi',
            'Eva Farhati, S.H.I, S.Pd.I', 'Finny Robbyatul Adawaiyah, S.Pd', 'Ida Zubaedah, S.Psi',
            'Imam Al Muharramain, S.Pd', 'Isna Ahwati, S.Pd', 'Kemal Yusuf Noviandi, S.Kom',
            'M. Estty Mei Indrayani, S.Pd', 'Muhammad Mahmudin, ST', 'Muhammad Febriansyah. M, S.Kom',
            'Muhammad Robi Sani', 'Murjiyanto', 'Nisa Farra Ulya, S.Pd', 'Nur Auliya Rahmawati, S.Pd',
            'Nurul Fauziah, S. I.Kom', 'Purwanti Hersriasih, SE', 'Rezza Denis Setiawan', 'Ria Mariana, S.Pd',
            'Rifda Zulfiana, S.Pd', 'Rizal Eko Mustofa, S.Pd', 'Saepul Hiar', 'Sapto Adi Putro, ST',
            'Siti Musyifah, S.Pd', 'Siti Zubaedah', 'Ujang Saepul Bahri, ST', 'Umin, ST',
            'Wachyudin, S.Pd.I', 'Wahyu Adi Luhur Prinanto, A.Md', 'Yogi Prasetyo',
        ];

        $aliases = [];
        foreach ($fullNames as $name) {
            $aliases[] = $name;
            $aliases[] = trim((string) strtok($name, ','));
        }

        // Variasi penulisan yang muncul di jadwal.
        $aliases = array_merge($aliases, [
            'Rizal Eko M', 'M Estty Mei Indriyani', 'Murjianto', 'Siti Zubaidah',
            'Baskoro Anhaf Nugroho', 'Imam Al Muharamain', 'Muhammad Febriansyah M,S.Kom',
            'Dwi Ayu Hadilawati', 'Eva Farhati, S.Hi, S.Pd.i', 'Wachyudin, S.Pdi',
            'Astina,S.T', 'Bisri Mustofa,S.T', 'Arya Wijaya Kusuma,S.Ko',
        ]);

        $aliases = array_values(array_unique(array_filter(array_map('trim', $aliases))));
        usort($aliases, static fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));
        return $aliases;
    }
}

