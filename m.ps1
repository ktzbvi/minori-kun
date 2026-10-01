[CmdletBinding()]
param(
    [Parameter(Position = 0)]
    [string] $Command = 'help'
)

Set-StrictMode -Version 2.0
$ErrorActionPreference = 'Stop'

function Show-Help {
    @'
Minori-kun native development commands

  .\m.ps1 bootstrap       Install locked dependencies and initialize the app
  .\m.ps1 api             Run the Laravel API with development-safe OPcache
  .\m.ps1 web             Run all three Vue development servers
  .\m.ps1 mail            Run the local Mailpit inbox for registration emails
  .\m.ps1 test            Run backend and frontend unit tests
  .\m.ps1 check           Run the complete non-mutating quality gate
  .\m.ps1 api-sync        Regenerate OpenAPI and TypeScript API types
  .\m.ps1 help            Show this help

Use normal php artisan, Composer, pnpm filter, and Mailpit commands for individual tasks.
'@ | Write-Host
}

function Assert-Command {
    param(
        [Parameter(Mandatory = $true)][string] $Name,
        [Parameter(Mandatory = $true)][string] $InstallHint
    )

    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        throw "$Name was not found. $InstallHint"
    }
}

function Invoke-Checked {
    param(
        [Parameter(Mandatory = $true)][string] $Executable,
        [string[]] $CommandArguments = @()
    )

    & $Executable @CommandArguments
    if ($LASTEXITCODE -ne 0) {
        throw "$Executable failed with exit code $LASTEXITCODE."
    }
}

function Assert-Toolchain {
    Assert-Command 'php' 'Install PHP 8.4 with the extensions listed in README.md.'
    Assert-Command 'composer' 'Install Composer 2.8 or newer.'
    Assert-Command 'node' 'Install Node.js 22 LTS or newer.'
    Assert-Command 'pnpm' 'Enable Corepack and activate pnpm 10.'

    Invoke-Checked 'php' @('-r', "exit(version_compare(PHP_VERSION, '8.4.0', '>=') ? 0 : 1);")
    Invoke-Checked 'node' @('-e', "const [major, minor] = process.versions.node.split('.').map(Number); process.exit(major > 22 || (major === 22 && minor >= 12) ? 0 : 1)")
}

function Resolve-MailpitExecutable {
    $command = Get-Command 'mailpit' -ErrorAction SilentlyContinue
    if ($command) {
        return $command.Source
    }

    $wingetPackageRoot = Join-Path $env:LOCALAPPDATA 'Microsoft/WinGet/Packages'
    if (Test-Path -LiteralPath $wingetPackageRoot) {
        $installed = Get-ChildItem -LiteralPath $wingetPackageRoot -Recurse -Filter 'mailpit.exe' -ErrorAction SilentlyContinue |
            Select-Object -First 1

        if ($installed) {
            return $installed.FullName
        }
    }

    throw 'mailpit was not found. Install Mailpit from https://mailpit.axllent.org/install/ and rerun .\m.ps1 mail.'
}

function Invoke-ApiCommand {
    param(
        [Parameter(Mandatory = $true)][string] $Executable,
        [string[]] $CommandArguments = @()
    )

    Push-Location (Join-Path $PSScriptRoot 'apps/api')
    try {
        Invoke-Checked $Executable $CommandArguments
    }
    finally {
        Pop-Location
    }
}

function Test-DirectoryAccessible {
    param(
        [Parameter(Mandatory = $true)][string] $Path
    )

    try {
        [System.IO.Directory]::GetFileSystemEntries($Path) | Out-Null
        return $true
    }
    catch {
        return $false
    }
}

function Remove-GeneratedNodeModules {
    $workspaceRoot = [System.IO.Path]::GetFullPath($PSScriptRoot).TrimEnd([System.IO.Path]::DirectorySeparatorChar)
    $nodeModulesDirectories = @(
        'node_modules',
        'apps/buyer-web/node_modules',
        'apps/producer-web/node_modules',
        'apps/admin-web/node_modules',
        'packages/api-contracts/node_modules',
        'packages/ui/node_modules'
    )

    foreach ($relativePath in $nodeModulesDirectories) {
        $fullPath = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot $relativePath))
        $expectedPrefix = $workspaceRoot + [System.IO.Path]::DirectorySeparatorChar

        if (-not $fullPath.StartsWith($expectedPrefix, [System.StringComparison]::OrdinalIgnoreCase) -or
            [System.IO.Path]::GetFileName($fullPath) -ne 'node_modules') {
            throw "Refusing to remove unexpected dependency path: $fullPath"
        }

        if (Test-Path -LiteralPath $fullPath) {
            Remove-Item -LiteralPath $fullPath -Recurse -Force
        }
    }
}

function Repair-LegacyDockerWorkspaceLinks {
    $workspaceLink = Join-Path $PSScriptRoot 'packages/api-contracts/node_modules/@minorikun/config'

    if ((Test-Path -LiteralPath $workspaceLink) -and -not (Test-DirectoryAccessible $workspaceLink)) {
        Write-Host 'Removing generated node_modules links left by the former Docker development environment...'
        Remove-GeneratedNodeModules
    }
}

function Ensure-ApiStorageLink {
    $publicStorage = Join-Path $PSScriptRoot 'apps/api/public/storage'

    if (Test-Path -LiteralPath $publicStorage) {
        $item = Get-Item -LiteralPath $publicStorage -Force
        $isLink = ($item.Attributes -band [System.IO.FileAttributes]::ReparsePoint) -ne 0

        if (-not $isLink) {
            throw "The path $publicStorage exists but is not a storage link. Move it manually before running bootstrap."
        }

        if (Test-DirectoryAccessible $publicStorage) {
            return
        }

        Write-Host 'Replacing the storage link left by the former Docker development environment...'
        Remove-Item -LiteralPath $publicStorage -Force
    }

    Invoke-ApiCommand 'php' @('artisan', 'storage:link')
}

function Assert-LocalDatabaseConfiguration {
    $apiEnvironment = Join-Path $PSScriptRoot 'apps/api/.env'
    $databaseConnection = Select-String -LiteralPath $apiEnvironment -Pattern '^DB_CONNECTION=(.*)$' | Select-Object -Last 1

    if ($null -eq $databaseConnection -or $databaseConnection.Matches[0].Groups[1].Value -ne 'mysql') {
        return
    }

    foreach ($name in @('DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD')) {
        $setting = Select-String -LiteralPath $apiEnvironment -Pattern "^$name=(.+)$" | Select-Object -Last 1
        if ($null -eq $setting) {
            throw "$name is missing or empty in apps/api/.env. Configure the local MySQL connection before running bootstrap. The documented default password is minori_local."
        }
    }
}

$normalizedCommand = $Command.ToLowerInvariant()

if ($normalizedCommand -in @('help', '-h', '--help')) {
    Show-Help
    exit 0
}

Push-Location $PSScriptRoot
try {
    switch ($normalizedCommand) {
        'bootstrap' {
            Assert-Toolchain
            Invoke-ApiCommand 'composer' @('install', '--no-interaction', '--prefer-dist')

            $apiEnvironment = Join-Path $PSScriptRoot 'apps/api/.env'
            if (-not (Test-Path -LiteralPath $apiEnvironment)) {
                Copy-Item -LiteralPath (Join-Path $PSScriptRoot 'apps/api/.env.example') -Destination $apiEnvironment
            }

            if (-not (Select-String -LiteralPath $apiEnvironment -Pattern '^APP_KEY=base64:.+' -Quiet)) {
                Invoke-ApiCommand 'php' @('artisan', 'key:generate', '--force')
            }
            Ensure-ApiStorageLink
            Repair-LegacyDockerWorkspaceLinks
            Invoke-Checked 'pnpm' @('install', '--frozen-lockfile')
            Assert-LocalDatabaseConfiguration
            Invoke-ApiCommand 'php' @('artisan', 'migrate', '--force')
            Invoke-ApiCommand 'php' @('artisan', 'db:seed', '--force')
            Write-Host 'Bootstrap complete. See README.md for the normal Laravel and workspace development commands.'
        }
        'web' {
            Assert-Command 'node' 'Install Node.js 22 LTS or newer.'
            Assert-Command 'pnpm' 'Enable Corepack and activate pnpm 10.'
            Invoke-Checked 'pnpm' @('dev')
        }
        'api' {
            Assert-Command 'php' 'Install PHP 8.4 with the extensions listed in README.md.'
            $apiRoot = Join-Path $PSScriptRoot 'apps/api'
            $publicRoot = Join-Path $apiRoot 'public'
            $serverRouter = Join-Path $apiRoot 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'

            if (-not (Test-Path -LiteralPath $serverRouter)) {
                throw 'Laravel development server router was not found. Run .\m.ps1 bootstrap first.'
            }

            Push-Location $publicRoot
            try {
                Invoke-Checked 'php' @(
                    '-d', 'zend_extension=opcache',
                    '-d', 'opcache.enable=1',
                    '-d', 'opcache.enable_cli=1',
                    '-d', 'opcache.validate_timestamps=1',
                    '-d', 'opcache.revalidate_freq=0',
                    '-S', 'localhost:8000',
                    $serverRouter
                )
            }
            finally {
                Pop-Location
            }
        }
        'mail' {
            $mailpit = Resolve-MailpitExecutable
            Invoke-Checked $mailpit
        }
        'test' {
            Assert-Toolchain
            Invoke-ApiCommand 'composer' @('test')
            Invoke-Checked 'pnpm' @('test')
        }
        'check' {
            Assert-Toolchain
            Invoke-ApiCommand 'composer' @('format:check')
            Invoke-ApiCommand 'composer' @('analyse')
            Invoke-ApiCommand 'composer' @('test')
            Invoke-Checked 'pnpm' @('lint')
            Invoke-Checked 'pnpm' @('format:check')
            Invoke-Checked 'pnpm' @('typecheck')
            Invoke-Checked 'pnpm' @('test')
            Invoke-Checked 'pnpm' @('build')
            Invoke-Checked 'pnpm' @('api:contract:check')
        }
        'api-sync' {
            Assert-Toolchain
            Invoke-ApiCommand 'composer' @('openapi')
            Invoke-Checked 'pnpm' @('api:types')
        }
        default {
            Show-Help
            throw "Unknown command: $Command"
        }
    }
}
catch {
    Write-Error $_.Exception.Message
    exit 1
}
finally {
    Pop-Location
}
