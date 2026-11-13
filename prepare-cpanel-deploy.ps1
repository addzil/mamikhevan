Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

Push-Location $PSScriptRoot
$stagingRoot = $null
try {
    if (-not (Test-Path -LiteralPath 'vendor/autoload.php')) {
        throw 'vendor/autoload.php tidak ada. Jalankan composer install terlebih dahulu.'
    }

    if (-not (Test-Path -LiteralPath 'node_modules')) {
        throw 'node_modules tidak ada. Jalankan npm ci terlebih dahulu.'
    }

    $trackedEnvironmentFiles = @(& git ls-files -- '.env*' | Where-Object { $_ -ne '.env.example' })
    if ($LASTEXITCODE -ne 0) {
        throw 'Gagal memeriksa file environment yang dilacak Git.'
    }

    if ($trackedEnvironmentFiles.Count -gt 0) {
        throw "File environment rahasia terlacak Git: $($trackedEnvironmentFiles -join ', ')"
    }

    $composerCommand = Get-Command composer -ErrorAction Stop
    $stagingRoot = Join-Path $PSScriptRoot ".cpanel-build-$PID"
    New-Item -ItemType Directory -Path $stagingRoot | Out-Null
    Copy-Item -LiteralPath 'composer.json', 'composer.lock' -Destination $stagingRoot
    Copy-Item -LiteralPath 'vendor' -Destination $stagingRoot -Recurse
    Copy-Item -LiteralPath 'app' -Destination $stagingRoot -Recurse
    New-Item -ItemType Directory -Path (Join-Path $stagingRoot 'database/factories') -Force | Out-Null
    New-Item -ItemType Directory -Path (Join-Path $stagingRoot 'database/seeders') -Force | Out-Null
    Copy-Item -Path 'database/factories/*' -Destination (Join-Path $stagingRoot 'database/factories') -Recurse -Force
    Copy-Item -Path 'database/seeders/*' -Destination (Join-Path $stagingRoot 'database/seeders') -Recurse -Force

    & $composerCommand.Source install "--working-dir=$stagingRoot" --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
    if ($LASTEXITCODE -ne 0) {
        throw 'Instalasi dependensi production tanpa paket development gagal.'
    }

    & npm.cmd run build
    if ($LASTEXITCODE -ne 0) {
        throw 'Build frontend gagal.'
    }

    if (-not (Test-Path -LiteralPath 'public/build/manifest.json')) {
        throw 'Manifest Vite tidak ditemukan setelah build.'
    }

    $archive = Join-Path $PSScriptRoot 'cpanel-runtime-no-terminal.zip'
    $candidateArchive = Join-Path $PSScriptRoot 'cpanel-runtime-candidate.zip'
    New-Item -ItemType Directory -Path (Join-Path $stagingRoot 'public/build') -Force | Out-Null
    Copy-Item -Path 'public/build/*' -Destination (Join-Path $stagingRoot 'public/build') -Recurse -Force

    try {
        Push-Location $stagingRoot
        & tar.exe -a -cf $candidateArchive vendor public/build
        if ($LASTEXITCODE -ne 0) {
            throw 'Gagal membuat arsip runtime cPanel.'
        }

        $archiveEntries = @(& tar.exe -tf $candidateArchive)
        if ($LASTEXITCODE -ne 0) {
            throw 'Gagal membaca arsip runtime cPanel.'
        }

        $requiredEntries = @('vendor/autoload.php', 'public/build/manifest.json')
        $manifest = Get-Content -LiteralPath (Join-Path $PSScriptRoot 'public/build/manifest.json') -Raw | ConvertFrom-Json
        foreach ($entry in $manifest.PSObject.Properties) {
            $requiredEntries += "public/build/$($entry.Value.file)"
        }

        foreach ($entry in $requiredEntries) {
            if ($entry -notin $archiveEntries) {
                throw "File yang diperlukan tidak ada dalam arsip: $entry"
            }
        }

        Move-Item -LiteralPath $candidateArchive -Destination $archive -Force
    }
    finally {
        Pop-Location
        if (Test-Path -LiteralPath $candidateArchive) {
            Remove-Item -LiteralPath $candidateArchive -Force
        }
    }

    Write-Host 'Aset dan arsip runtime cPanel siap. Periksa git status dan diff sebelum commit.'
    & git status --short
}
finally {
    if ($stagingRoot -and (Test-Path -LiteralPath $stagingRoot)) {
        Remove-Item -LiteralPath $stagingRoot -Recurse -Force
    }
    Pop-Location
}
