[CmdletBinding()]
param([string]$CommitMessage = 'NACS-Phil R1.6 native admin foundation')
$ErrorActionPreference = 'Stop'
Set-StrictMode -Version 2.0
$ProjectRoot = Split-Path -Parent (Split-Path -Parent $PSScriptRoot)

function Invoke-PublisherCommand {
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
    Invoke-PublisherCommand -FilePath 'powershell.exe' -Arguments @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', (Join-Path $PSScriptRoot 'validate-project.ps1')) -FailureMessage 'Validation failed. GitHub publishing was blocked.'
    Invoke-PublisherCommand -FilePath 'git' -Arguments @('add', '-A') -FailureMessage 'git add failed.'

    $previousErrorActionPreference = $ErrorActionPreference
    try {
        $ErrorActionPreference = 'Continue'
        $staged = @(& git diff --cached --name-only)
        $diffExitCode = $LASTEXITCODE
    } finally {
        $ErrorActionPreference = $previousErrorActionPreference
    }
    if ($diffExitCode -ne 0) { throw ('git diff failed. Exit code: ' + $diffExitCode) }

    $blockedPatterns = @(
        '^\.env$', '^database/database\.sqlite$', '^storage/app/public/', '^storage/logs/',
        '^vendor/', '^node_modules/', '^\.nacs-backups/'
    )
    foreach ($file in $staged) {
        foreach ($pattern in $blockedPatterns) {
            if ($file -match $pattern) { throw "Blocked sensitive or generated path is staged: $file" }
        }
    }

    if ($staged.Count -eq 0) {
        Write-Host '[INFO] There are no source changes to publish.' -ForegroundColor Yellow
        exit 0
    }

    Invoke-PublisherCommand -FilePath 'git' -Arguments @('commit', '-m', $CommitMessage) -FailureMessage 'git commit failed.'
    Invoke-PublisherCommand -FilePath 'git' -Arguments @('push', '-u', 'origin', 'HEAD') -FailureMessage 'git push failed.'
    Write-Host '[PASS] NACS-Phil source was published to GitHub.' -ForegroundColor Green
} finally { Pop-Location }
