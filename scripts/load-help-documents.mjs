import {readdir, readFile} from 'node:fs/promises'
import {relative, resolve} from 'node:path'
import {fileURLToPath} from 'node:url'
import {parseHelpDocument} from '../resources/js/admin-v2/help/markdown.js'

const projectRoot = resolve(fileURLToPath(new URL('..', import.meta.url)))
const collections = [
    {directory: 'resources/help/pages', kind: 'page'},
    {directory: 'resources/help/guides', kind: 'guide'},
    {directory: 'resources/help/news', kind: 'news'},
]

export async function loadHelpDocuments() {
    const documents = []

    for (const collection of collections) {
        const directory = resolve(projectRoot, collection.directory)
        const entries = await readdir(directory, {withFileTypes: true})

        for (const entry of entries.filter(entry => entry.isFile() && entry.name.endsWith('.md'))) {
            const path = resolve(directory, entry.name)
            const source = await readFile(path, 'utf8')
            documents.push(parseHelpDocument(source, relative(projectRoot, path), collection.kind))
        }
    }

    return documents
}
