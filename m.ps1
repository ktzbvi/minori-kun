[CmdletBinding()]
param(
    [Parameter(Position = 0)]
    [string] $Command = 'help',

    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]] $Arguments = @()
)

Set-StrictMode -Version 2.0
$ErrorActionPreference = 'Stop'

function Show-Help {
    @'
Minori-kun development commands

  .\m.ps1 bootstrap       First-time/recoverable setup and start
  .\m.ps1 up              Start the daily development stack
  .\m.ps1 stop            Stop containers without deleting data
  .\m.ps1 restart         Restart application containers
  .\m.ps1 status          Show container and health status
  .\m.ps1 logs [service]  Follow logs (core services by default)
  .\m.ps1 shell           Open a shell in the API container
  .\m.ps1 artisan ...     Run a Laravel Artisan command
  .\m.ps1 migrate         Run pending database migrations
  .\m.ps1 seed            Refresh safe local fixtures
  .\m.ps1 worker          Run the database queue worker in foreground
  .\m.ps1 test            Run backend and frontend unit tests
  .\m.ps1 check           Run the complete non-mutating quality gate
  .\m.ps1 api-sync        Regenerate OpenAPI and TypeScript API types
  .\m.ps1 db-reset        Destructively rebuild the local database
  .\m.ps1 help            Show this help
'@ | Write-Host
}

function Assert-Docker {
    if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
        throw 'Docker was not found. Install or start Docker Desktop, then retry.'
    }

    & docker compose version *> $null
    if ($LASTEXITCODE -ne 0) {
        throw 'Docker Compose is unavailable. Start Docker Desktop, then retry.'
    }
}

function Invoke-Compose {
    param([Parameter(Mandatory = $true)][string[]] $ComposeArguments)

    & docker compose @ComposeArguments
    if ($LASTEXITCODE -ne 0) {
        throw "Docker Compose command failed with exit code $LASTEXITCODE."
    }
}

function Show-Urls {
    Write-Host ''
    Write-Host 'API:      http://localhost:8000'
    Write-Host 'Buyer:    http://localhost:5173'
    Write-Host 'Producer: http://localhost:5174'
    Write-Host 'Admin:    http://localhost:5175'
    Write-Host 'Mailpit:  http://localhost:8025'
}

$normalizedCommand = $Command.ToLowerInvariant()

if ($normalizedCommand -in @('help', '-h', '--help')) {
    Show-Help
    exit 0
}

Push-Location $PSScriptRoot
try {
    Assert-Docker

    switch ($normalizedCommand) {
        'bootstrap' {
            Invoke-Compose @('run', '--rm', '--build', 'setup')
            Invoke-Compose @('up', '-d', '--build', '--wait')
            Show-Urls
        }
        'up' {
            Invoke-Compose @('up', '-d', '--wait')
            Show-Urls
        }
        'stop' {
            Invoke-Compose @('stop')
        }
        'restart' {
            Invoke-Compose @('restart', 'api', 'api-nginx', 'frontend', 'mailpit')
            Invoke-Compose @('ps')
        }
        'status' {
            Invoke-Compose @('ps')
        }
        'logs' {
            $services = if ($Arguments.Count -gt 0) { $Arguments } else { @('api', 'api-nginx', 'frontend', 'mailpit') }
            Invoke-Compose (@('logs', '--follow', '--tail', '100') + $services)
        }
        'shell' {
            Invoke-Compose @('exec', 'api', 'bash')
        }
        'artisan' {
            if ($Arguments.Count -eq 0) {
                throw 'Provide an Artisan command, for example: .\m.ps1 artisan route:list'
            }
            Invoke-Compose (@('exec', '-T', 'api', 'php', 'artisan') + $Arguments)
        }
        'migrate' {
            Invoke-Compose @('exec', '-T', 'api', 'php', 'artisan', 'migrate', '--force')
        }
        'seed' {
            Invoke-Compose @('exec', '-T', 'api', 'php', 'artisan', 'db:seed', '--force')
        }
        'worker' {
            Write-Host 'Queue worker is running in the foreground. Press Ctrl+C to stop it.'
            Invoke-Compose @('exec', 'api', 'php', 'artisan', 'queue:work', '--sleep=1', '--tries=3', '--timeout=90')
        }
        'test' {
            Invoke-Compose @('exec', '-T', 'api', 'composer', 'test')
            Invoke-Compose @('exec', '-T', 'frontend', 'pnpm', 'test')
        }
        'check' {
            Invoke-Compose @('exec', '-T', 'api', 'composer', 'format:check')
            Invoke-Compose @('exec', '-T', 'api', 'composer', 'analyse')
            Invoke-Compose @('exec', '-T', 'api', 'composer', 'test')
            Invoke-Compose @('exec', '-T', 'api', 'sh', '-lc', 'php artisan scramble:export --path=storage/framework/cache/openapi-check.json >/dev/null && cmp -s storage/framework/cache/openapi-check.json openapi.json')
            Invoke-Compose @('exec', '-T', 'frontend', 'pnpm', 'lint')
            Invoke-Compose @('exec', '-T', 'frontend', 'pnpm', 'format:check')
            Invoke-Compose @('exec', '-T', 'frontend', 'pnpm', 'typecheck')
            Invoke-Compose @('exec', '-T', 'frontend', 'pnpm', 'test')
            Invoke-Compose @('exec', '-T', 'frontend', 'pnpm', 'build')
            Invoke-Compose @('exec', '-T', 'frontend', 'pnpm', 'api:types:check')
        }
        'api-sync' {
            Invoke-Compose @('exec', '-T', 'api', 'composer', 'openapi')
            Invoke-Compose @('exec', '-T', 'frontend', 'pnpm', 'api:types')
        }
        'db-reset' {
            Write-Warning 'This deletes every table and all local MySQL data for Minori-kun.'
            $confirmation = Read-Host 'Type RESET MINORI LOCAL DATABASE to continue'
            if ($confirmation -cne 'RESET MINORI LOCAL DATABASE') {
                Write-Host 'Database reset cancelled. No data was changed.'
                exit 1
            }
            Invoke-Compose @('exec', '-T', 'api', 'php', 'artisan', 'migrate:fresh', '--seed', '--force')
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
