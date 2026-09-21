import {parseHelpDocument, validateHelpDocuments} from './markdown'

const pageSources = import.meta.glob('../../../help/pages/*.md', {query: '?raw', import: 'default', eager: true})
const guideSources = import.meta.glob('../../../help/guides/*.md', {query: '?raw', import: 'default', eager: true})
const newsSources = import.meta.glob('../../../help/news/*.md', {query: '?raw', import: 'default', eager: true})

function loadDocuments(sources, kind) {
    return Object.entries(sources).map(([path, source]) => parseHelpDocument(source, path, kind))
}

const documents = [
    ...loadDocuments(pageSources, 'page'),
    ...loadDocuments(guideSources, 'guide'),
    ...loadDocuments(newsSources, 'news'),
]
const errors = validateHelpDocuments(documents)

if (errors.length > 0) {
    throw new Error(`Invalid Help documentation:\n${errors.join('\n')}`)
}

export const pages = Object.fromEntries(documents.filter(document => document.kind === 'page').map(document => [document.slug, document]))
export const guides = documents.filter(document => document.kind === 'guide').sort((left, right) => left.order - right.order || left.title.localeCompare(right.title, 'da'))
export const news = documents.filter(document => document.kind === 'news').sort((left, right) => right.published.localeCompare(left.published))

export function findGuide(slug) {
    return guides.find(guide => guide.slug === slug)
}

export function findAnnouncement(slug) {
    return news.find(announcement => announcement.slug === slug)
}
