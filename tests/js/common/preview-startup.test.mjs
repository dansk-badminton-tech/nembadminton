import {test} from 'node:test'
import assert from 'node:assert/strict'
import {mkdtempSync, writeFileSync, readFileSync, rmSync} from 'node:fs'
import {tmpdir} from 'node:os'
import {join} from 'node:path'
import {spawnSync} from 'node:child_process'
import {fileURLToPath} from 'node:url'

test('preview clears a previous checkout schema before starting its server', () => {
    const directory = mkdtempSync(join(tmpdir(), 'preview-startup-'))
    try {
        const log = join(directory, 'docker.log')
        writeFileSync(join(directory, 'docker'), '#!/bin/sh\nprintf "%s\\n" "$*" >> "$PREVIEW_TEST_LOG"\ncase "$*" in *"port dusk-serve 80") echo "0.0.0.0:12345" ;; esac\n', {mode: 0o755})
        writeFileSync(join(directory, 'yarn'), '#!/bin/sh\nexit 0\n', {mode: 0o755})
        const result = spawnSync('bash', ['bin/preview'], {
            cwd: fileURLToPath(new URL('../../../', import.meta.url)),
            env: {...process.env, PATH: `${directory}:${process.env.PATH}`, PREVIEW_TEST_LOG: log},
            encoding: 'utf8'
        })
        assert.equal(result.status, 0, result.stderr)
        const commands = readFileSync(log, 'utf8').trim().split('\n')
        const clear = commands.findIndex(command => command.includes('artisan lighthouse:clear-schema-cache --env=dusk.local'))
        const start = commands.findIndex(command => command.endsWith('up -d dusk-serve'))
        assert.ok(clear >= 0, 'preview must clear the persisted GraphQL schema cache')
        assert.ok(start > clear, 'clear the cache before Octane loads the schema')
    } finally {
        rmSync(directory, {recursive: true, force: true})
    }
})
