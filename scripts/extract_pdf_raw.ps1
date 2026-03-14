param(
    [Parameter(Mandatory = $true)]
    [string]$InputPath
)

$ErrorActionPreference = 'Stop'

$bytes = [System.IO.File]::ReadAllBytes($InputPath)
$text = [System.Text.Encoding]::ASCII.GetString($bytes)

# Very rough PDF text token extraction from literal strings "(...)"
$matches = [System.Text.RegularExpressions.Regex]::Matches($text, '\(([^()]*)\)')
foreach ($m in $matches) {
    $v = $m.Groups[1].Value
    if ([string]::IsNullOrWhiteSpace($v)) { continue }
    if ($v.Length -lt 2) { continue }
    if ($v -match '^[\x00-\x1F]+$') { continue }
    Write-Output $v
}
