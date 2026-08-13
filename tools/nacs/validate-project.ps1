[CmdletBinding()]
param()
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 2.0
$ProjectRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)

function Invoke-ProjectCommand {
    param(
        [Parameter(Mandatory=$true)][string]$FilePath,
        [Parameter(Mandatory=$false)][string[]]$Arguments = @(),
        [Parameter(Mandatory=$true)][string]$FailureMessage
    )

    Write-Host ('[INFO] ' + $FilePath + ' ' + ($Arguments -join ' ')) -ForegroundColor Gray
    $previousErrorActionPreference = $ErrorActionPreference
    $exitCode = 1
    try {
        $ErrorActionPreference = 'Continue'
        & $FilePath @Arguments | Out-Host
        $exitCode = $LASTEXITCODE
    } finally {
        $ErrorActionPreference = $previousErrorActionPreference
    }

    if ($exitCode -ne 0) {
        throw ($FailureMessage + ' Exit code: ' + $exitCode)
    }
}

Push-Location $ProjectRoot
try {
    foreach ($command in @('php','composer','node','npm','git')) {
        if (-not (Get-Command $command -ErrorAction SilentlyContinue)) { throw "Missing command: $command" }
    }
    Invoke-ProjectCommand -FilePath 'php' -Arguments @('artisan', 'optimize:clear') -FailureMessage 'optimize:clear failed.'
    Invoke-ProjectCommand -FilePath 'php' -Arguments @('artisan', 'route:list') -FailureMessage 'route:list failed.'
    Invoke-ProjectCommand -FilePath 'php' -Arguments @('artisan', 'test') -FailureMessage 'Tests failed.'
    Invoke-ProjectCommand -FilePath 'npm' -Arguments @('run', 'build') -FailureMessage 'Frontend build failed.'
    Invoke-ProjectCommand -FilePath 'php' -Arguments @('tools/nacs/validate-source.php') -FailureMessage 'Source safety validation failed.'
    Write-Host '[PASS] NACS-Phil project validation completed.' -ForegroundColor Green
} finally { Pop-Location }
