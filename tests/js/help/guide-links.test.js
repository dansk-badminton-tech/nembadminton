import {test} from 'node:test'
import assert from 'node:assert/strict'
import {existsSync} from 'node:fs'
import {fileURLToPath} from 'node:url'
import {guideLinks} from '../../../resources/js/admin-v2/help/guideLinks.js'

const guidesDirectory = new URL('../../../resources/help/guides/', import.meta.url)

test('every guide linked from the product exists', () => {
    for (const [entryPoint, slug] of Object.entries(guideLinks)) {
        assert.ok(existsSync(fileURLToPath(new URL(`${slug}.md`, guidesDirectory))), `${entryPoint} links to missing guide "${slug}"`)
    }
})
