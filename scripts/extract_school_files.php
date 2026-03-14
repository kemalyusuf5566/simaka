<?php

declare(strict_types=1);

function readSharedStrings(ZipArchive $zip): array
{
    $xml = $zip->getFromName('xl/sharedStrings.xml');
    if ($xml === false) {
        return [];
    }

    $sx = simplexml_load_string($xml);
    if ($sx === false) {
        return [];
    }

    $strings = [];
    foreach ($sx->si as $si) {
        if (isset($si->t)) {
            $strings[] = (string) $si->t;
            continue;
        }
        $parts = [];
        foreach ($si->r as $r) {
            $parts[] = (string) $r->t;
        }
        $strings[] = implode('', $parts);
    }

    return $strings;
}

function readFirstSheetRows(string $path): array
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        return [];
    }

    $sharedStrings = readSharedStrings($zip);
    $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if ($sheetXml === false) {
        return [];
    }

    $sx = simplexml_load_string($sheetXml);
    if ($sx === false || !isset($sx->sheetData)) {
        return [];
    }

    $rows = [];
    foreach ($sx->sheetData->row as $row) {
        $rowIndex = (string) $row['r'];
        $values = [];
        foreach ($row->c as $c) {
            $type = (string) $c['t'];
            $v = isset($c->v) ? (string) $c->v : '';
            if ($type === 's') {
                $sIdx = (int) $v;
                $values[] = trim($sharedStrings[$sIdx] ?? '');
            } else {
                $values[] = trim($v);
            }
        }
        $values = array_values(array_filter($values, static fn ($x) => $x !== ''));
        if ($values !== []) {
            $rows[$rowIndex] = $values;
        }
    }

    return $rows;
}

$files = [
    'kelasX' => 'C:\\Users\\LENOVO\\Downloads\\kelasX.xlsx',
    'kelasXI' => 'C:\\Users\\LENOVO\\Downloads\\kelasXI.xlsx',
    'kelasXII' => 'C:\\Users\\LENOVO\\Downloads\\kelasXII.xlsx',
];

foreach ($files as $label => $path) {
    echo "=== {$label} ({$path}) ===\n";
    if (!is_file($path)) {
        echo "FILE_NOT_FOUND\n\n";
        continue;
    }

    $rows = readFirstSheetRows($path);
    foreach ($rows as $idx => $values) {
        echo $idx . '|' . implode(' | ', $values) . "\n";
    }
    echo "\n";
}
