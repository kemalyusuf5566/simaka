param(
    [Parameter(Mandatory = $true)]
    [string]$InputPath,
    [Parameter(Mandatory = $true)]
    [string]$OutputCsv
)

$ErrorActionPreference = 'Stop'

if (-not (Test-Path $InputPath)) {
    throw "Input file not found: $InputPath"
}

$excel = $null
$workbook = $null
$worksheet = $null

try {
    $excel = New-Object -ComObject Excel.Application
    $excel.Visible = $false
    $excel.DisplayAlerts = $false

    $workbook = $excel.Workbooks.Open($InputPath)
    $worksheet = $workbook.Worksheets.Item(1)
    $used = $worksheet.UsedRange

    $rowCount = $used.Rows.Count
    $colCount = $used.Columns.Count

    $rows = New-Object System.Collections.Generic.List[object]
    for ($r = 1; $r -le $rowCount; $r++) {
        $rowObj = [ordered]@{}
        for ($c = 1; $c -le $colCount; $c++) {
            $header = [string]$worksheet.Cells.Item(1, $c).Text
            if ([string]::IsNullOrWhiteSpace($header)) {
                $header = "COL_$c"
            }
            $val = [string]$worksheet.Cells.Item($r, $c).Text
            $rowObj[$header] = $val
        }
        $rows.Add([pscustomobject]$rowObj) | Out-Null
    }

    $dir = Split-Path -Parent $OutputCsv
    if ($dir -and -not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir | Out-Null
    }

    $rows | Export-Csv -Path $OutputCsv -NoTypeInformation -Encoding UTF8
} finally {
    if ($workbook) { $workbook.Close($false) }
    if ($excel) { $excel.Quit() }

    if ($worksheet) { [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($worksheet) }
    if ($workbook) { [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($workbook) }
    if ($excel) { [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel) }

    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}

Write-Output "EXPORTED|$OutputCsv"
