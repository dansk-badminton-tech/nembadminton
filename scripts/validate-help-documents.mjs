import {readdir, readFile} from 'node:fs/promises'
import {relative, resolve} from 'node:path'
import {fileURLToPath} from 'node:url'
import {parseHelpDocument, validateHelpDocuments} from '../resources/js/admin-v2/help/markdown.js'

const projectRoot = resolve(fileURLToPath(new URL('..', import.meta.url)))
const collections = [
    {directory: 'resources/help/pages', kind: 'page'},
    {directory: 'resources/help/guides', kind: 'guide'},
    {directory: 'resources/help/news', kind: 'news'},
]

const documents = []

try {
    for (const collection of collections) {
        const directory = resolve(projectRoot, collection.directory)
        const entries = await readdir(directory, {withFileTypes: true})

        for (const entry of entries.filter(entry => entry.isFile() && entry.name.endsWith('.md'))) {
            const path = resolve(directory, entry.name)
            const source = await readFile(path, 'utf8')
            documents.push(parseHelpDocument(source, relative(projectRoot, path), collection.kind))
        }
    }

    const errors = validateHelpDocuments(documents)

    if (errors.length > 0) {
        throw new Error(errors.join('\n'))
    }

    console.log(`Validated ${documents.length} Help documents.`)
} catch (error) {
    console.error(`Help documentation validation failed:\n${error.message}`)
    process.exitCode = 1
}
