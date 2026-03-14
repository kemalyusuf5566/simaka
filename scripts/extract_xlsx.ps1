$ErrorActionPreference = 'Stop'

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function Get-SharedStrings {
    param(
        [System.IO.Compression.ZipArchive]$Zip
    )

    $entry = $Zip.GetEntry('xl/sharedStrings.xml')
    if (-not $entry) { return @() }

    $reader = New-Object System.IO.StreamReader($entry.Open())
    try {
        $xmlText = $reader.ReadToEnd()
    } finally {
        $reader.Close()
    }

    [xml]$xml = $xmlText
    $result = @()
    foreach ($si in $xml.sst.si) {
        if ($si.t) {
            $text = [string]$si.t
            if ($text -eq 'System.Xml.XmlElement') {
                $text = [string]$si.t.InnerText
            }
            $result += $text
        } else {
            $parts = @()
            foreach ($r in $si.r) {
                $t = [string]$r.t
                if ($t -eq 'System.Xml.XmlElement') {
                    $t = [string]$r.t.InnerText
                }
                $parts += $t
            }
            $result += ($parts -join '')
        }
    }
    return $result
}

function Read-XlsxRows {
    param(
        [string]$Path
    )

    $zip = [System.IO.Compression.ZipFile]::OpenRead($Path)
    try {
        $sharedStrings = Get-SharedStrings -Zip $zip
        $sheetEntry = $zip.GetEntry('xl/worksheets/sheet1.xml')
        if (-not $sheetEntry) { return @() }

        $reader = New-Object System.IO.StreamReader($sheetEntry.Open())
        try {
            $sheetText = $reader.ReadToEnd()
        } finally {
            $reader.Close()
        }

        [xml]$sheet = $sheetText
        $rows = @()
        foreach ($row in $sheet.worksheet.sheetData.row) {
            $values = @()
            foreach ($c in $row.c) {
                $cellType = [string]$c.t
                $raw = [string]$c.v
                if ([string]::IsNullOrWhiteSpace($raw)) { continue }

                if ($cellType -eq 's') {
                    $idx = [int]$raw
                    if ($idx -ge 0 -and $idx -lt $sharedStrings.Count) {
                        $values += $sharedStrings[$idx].Trim()
                    }
                } elseif ($cellType -eq 'inlineStr') {
                    $values += [string]$c.is.InnerText
                } else {
                    $values += $raw.Trim()
                }
            }
            $values = $values | Where-Object { -not [string]::IsNullOrWhiteSpace($_) }
            if ($values.Count -gt 0) {
                $rows += [pscustomobject]@{
                    Row = [int]$row.r
                    Values = $values
                }
            }
        }

        return $rows
    } finally {
        $zip.Dispose()
    }
}

$defaultFiles = @(
    'C:\Users\LENOVO\Downloads\kelasX.xlsx',
    'C:\Users\LENOVO\Downloads\kelasXI.xlsx',
    'C:\Users\LENOVO\Downloads\kelasXII.xlsx'
)

$files = if ($args.Count -gt 0) { $args } else { $defaultFiles }

foreach ($file in $files) {
    Write-Output "=== $file ==="
    if (-not (Test-Path $file)) {
        Write-Output "FILE_NOT_FOUND"
        Write-Output ""
        continue
    }

    try {
        $rows = Read-XlsxRows -Path $file
    } catch {
        Write-Output ("READ_ERROR|{0}" -f $_.Exception.Message)
        Write-Output ""
        continue
    }
    foreach ($r in $rows) {
        $joined = ($r.Values -join ' | ')
        Write-Output ("{0}|{1}" -f $r.Row, $joined)
    }
    Write-Output ""
}
