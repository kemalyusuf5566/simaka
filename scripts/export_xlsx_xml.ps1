param(
    [Parameter(Mandatory = $true)]
    [string]$InputPath,
    [Parameter(Mandatory = $true)]
    [string]$OutputCsv
)

$ErrorActionPreference = 'Stop'

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

function Get-ColumnIndex {
    param([string]$CellRef)
    $letters = ($CellRef -replace '[^A-Z]', '')
    if ([string]::IsNullOrWhiteSpace($letters)) { return 0 }
    $sum = 0
    foreach ($ch in $letters.ToCharArray()) {
        $sum = ($sum * 26) + ([int][char]$ch - [int][char]'A' + 1)
    }
    return $sum
}

function Get-SharedStrings {
    param([System.IO.Compression.ZipArchive]$Zip)
    $entry = $Zip.GetEntry('xl/sharedStrings.xml')
    if (-not $entry) { return @() }
    $reader = New-Object System.IO.StreamReader($entry.Open())
    try {
        [xml]$xml = $reader.ReadToEnd()
    } finally {
        $reader.Close()
    }
    $arr = @()
    foreach ($si in $xml.sst.si) {
        if ($si.t) {
            $arr += [string]$si.t.'#text'
        } else {
            $parts = @()
            foreach ($r in $si.r) {
                $parts += [string]$r.t.'#text'
            }
            $arr += ($parts -join '')
        }
    }
    return $arr
}

if (-not (Test-Path $InputPath)) {
    throw "Input file not found: $InputPath"
}

$zip = [System.IO.Compression.ZipFile]::OpenRead($InputPath)
try {
    $shared = Get-SharedStrings -Zip $zip
    $sheetEntry = $zip.GetEntry('xl/worksheets/sheet1.xml')
    if (-not $sheetEntry) { throw "sheet1.xml not found in $InputPath" }

    $reader = New-Object System.IO.StreamReader($sheetEntry.Open())
    try {
        [xml]$sheet = $reader.ReadToEnd()
    } finally {
        $reader.Close()
    }

    $rows = $sheet.worksheet.sheetData.row
    if (-not $rows) { throw "No rows found in $InputPath" }

    $headerMap = @{}
    $headerRow = $rows | Where-Object { [int]$_.r -eq 1 } | Select-Object -First 1
    foreach ($c in $headerRow.c) {
        $col = Get-ColumnIndex -CellRef ([string]$c.r)
        $val = ''
        $type = [string]$c.t
        if ($type -eq 's') {
            $idx = [int]$c.v
            if ($idx -ge 0 -and $idx -lt $shared.Count) { $val = [string]$shared[$idx] }
        } elseif ($type -eq 'inlineStr') {
            $val = [string]$c.is.InnerText
        } else {
            $val = [string]$c.v
        }
        if ([string]::IsNullOrWhiteSpace($val)) { $val = "COL_$col" }
        $headerMap[$col] = $val.Trim()
    }

    $maxCol = ($headerMap.Keys | Measure-Object -Maximum).Maximum
    $headers = @()
    for ($i = 1; $i -le $maxCol; $i++) {
        if ($headerMap.ContainsKey($i)) {
            $headers += $headerMap[$i]
        } else {
            $headers += "COL_$i"
        }
    }

    $outRows = New-Object System.Collections.Generic.List[object]
    foreach ($row in $rows) {
        $rowNum = [int]$row.r
        if ($rowNum -eq 1) { continue }

        $vals = @{}
        foreach ($c in $row.c) {
            $col = Get-ColumnIndex -CellRef ([string]$c.r)
            $type = [string]$c.t
            $val = ''
            if ($type -eq 's') {
                $idx = [int]$c.v
                if ($idx -ge 0 -and $idx -lt $shared.Count) { $val = [string]$shared[$idx] }
            } elseif ($type -eq 'inlineStr') {
                $val = [string]$c.is.InnerText
            } else {
                $val = [string]$c.v
            }
            $vals[$col] = $val.Trim()
        }

        $obj = [ordered]@{}
        for ($i = 1; $i -le $maxCol; $i++) {
            $obj[$headers[$i - 1]] = if ($vals.ContainsKey($i)) { $vals[$i] } else { '' }
        }
        $outRows.Add([pscustomobject]$obj) | Out-Null
    }

    $dir = Split-Path -Parent $OutputCsv
    if ($dir -and -not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir | Out-Null
    }
    $outRows | Export-Csv -Path $OutputCsv -NoTypeInformation -Encoding UTF8
} finally {
    $zip.Dispose()
}

Write-Output "EXPORTED|$OutputCsv"
