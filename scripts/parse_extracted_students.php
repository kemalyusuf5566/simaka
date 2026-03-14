<?php

declare(strict_types=1);

function parseFile(string $path): array
{
    $rows = [];
    $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return [];
    }

    foreach ($lines as $line) {
        if (!preg_match('/^\d+\|/', $line)) {
            continue;
        }
        $parts = array_map('trim', explode('|', $line));
        if (count($parts) < 13) {
            continue;
        }

        // Format from extract_xlsx.ps1:
        // row|No|NIS|NO_PESERTA|NAMA|LEVEL|KELAS|JURUSAN|...|AGAMA|JK|...
        $nis = $parts[2] ?? '';
        $nama = $parts[4] ?? '';
        $level = $parts[5] ?? '';
        $kelas = $parts[6] ?? '';
        $jurusan = $parts[7] ?? '';
        $agama = $parts[11] ?? '';
        $jk = $parts[12] ?? '';

        if (!preg_match('/^\d+$/', $nis) || $nama === '' || strtoupper($nama) === 'NAMA') {
            continue;
        }

        $rows[] = [
            'nis' => $nis,
            'nama_siswa' => $nama,
            'tingkat' => $level,
            'kelas' => $kelas,
            'jurusan' => $jurusan,
            'agama' => $agama,
            'jk' => $jk,
        ];
    }

    return $rows;
}

$x = parseFile(__DIR__ . '/../storage/app/xlsx_x_out.txt');
$xi = parseFile(__DIR__ . '/../storage/app/xlsx_xi_out.txt');
$xii = parseFile(__DIR__ . '/../storage/app/xlsx_xii_out.txt');

@mkdir(__DIR__ . '/../database/data', 0777, true);
file_put_contents(__DIR__ . '/../database/data/siswa_x.json', json_encode($x, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
file_put_contents(__DIR__ . '/../database/data/siswa_xi.json', json_encode($xi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
file_put_contents(__DIR__ . '/../database/data/siswa_xii.json', json_encode($xii, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo 'X=' . count($x) . PHP_EOL;
echo 'XI=' . count($xi) . PHP_EOL;
echo 'XII=' . count($xii) . PHP_EOL;
