Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

Push-Location $PSScriptRoot
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

    & npm.cmd run build
    if ($LASTEXITCODE -ne 0) {
        throw 'Build frontend gagal.'
    }

    if (-not (Test-Path -LiteralPath 'public/build/manifest.json')) {
        throw 'Manifest Vite tidak ditemukan setelah build.'
    }

    $archive = Join-Path $PSScriptRoot 'cpanel-runtime-no-terminal.zip'
    $candidateArchive = Join-Path $PSScriptRoot 'cpanel-runtime-candidate.zip'

    try {
        & tar.exe -a -cf $candidateArchive vendor public/build
        if ($LASTEXITCODE -ne 0) {
            throw 'Gagal membuat arsip runtime cPanel.'
        }

        $archiveEntries = @(& tar.exe -tf $candidateArchive)
        if ($LASTEXITCODE -ne 0) {
            throw 'Gagal membaca arsip runtime cPanel.'
        }

        $requiredEntries = @('vendor/autoload.php', 'public/build/manifest.json')
        $manifest = Get-Content -LiteralPath 'public/build/manifest.json' -Raw | ConvertFrom-Json
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
        if (Test-Path -LiteralPath $candidateArchive) {
            Remove-Item -LiteralPath $candidateArchive -Force
        }
    }

    Write-Host 'Aset dan arsip runtime cPanel siap. Periksa git status dan diff sebelum commit.'
    & git status --short
}
finally {
    Pop-Location
}
