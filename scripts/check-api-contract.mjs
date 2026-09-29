import { mkdtempSync, readFileSync, rmSync } from 'node:fs'
import { tmpdir } from 'node:os'
import { join, resolve } from 'node:path'
import { spawnSync } from 'node:child_process'

const root = resolve(import.meta.dirname, '..')
const temporaryDirectory = mkdtempSync(join(tmpdir(), 'minori-api-contract-'))
const generatedOpenApi = join(temporaryDirectory, 'openapi.json')
const generatedTypes = join(temporaryDirectory, 'schema.d.ts')
const typesOnly = process.argv.includes('--types-only')

function run(command, args, cwd = root) {
  const result = spawnSync(command, args, {
    cwd,
    stdio: 'inherit',
    shell: process.platform === 'win32' && command.endsWith('.cmd'),
  })

  if (result.status !== 0) {
    process.exitCode = result.status ?? 1
    throw new Error(`${command} failed.`)
  }
}

function assertSame(actualPath, expectedPath, label) {
  if (!readFileSync(actualPath).equals(readFileSync(expectedPath))) {
    throw new Error(`${label} is stale. Run .\\m.ps1 api-sync and review the generated files.`)
  }
}

try {
  if (!typesOnly) {
    run('php', ['artisan', 'scramble:export', `--path=${generatedOpenApi}`], join(root, 'apps/api'))
    assertSame(generatedOpenApi, join(root, 'apps/api/openapi.json'), 'OpenAPI contract')
  }

  const pnpm = process.platform === 'win32' ? 'pnpm.cmd' : 'pnpm'
  run(pnpm, ['exec', 'openapi-typescript', 'apps/api/openapi.json', '-o', generatedTypes])
  assertSame(generatedTypes, join(root, 'packages/api-contracts/src/generated/schema.d.ts'), 'Generated API types')
} catch (error) {
  console.error(error.message)
  process.exitCode = 1
} finally {
  rmSync(temporaryDirectory, { recursive: true, force: true })
}
