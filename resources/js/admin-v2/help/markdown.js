import MarkdownIt from 'markdown-it'
import {parse as parseYaml} from 'yaml'

const markdown = new MarkdownIt({
    html: false,
    linkify: true,
})

const defaultLinkOpen = markdown.renderer.rules.link_open ?? ((tokens, index, options, environment, renderer) => {
    return renderer.renderToken(tokens, index, options)
})

markdown.renderer.rules.link_open = (tokens, index, options, environment, renderer) => {
    const href = tokens[index].attrGet('href') ?? ''

    if (/^https?:\/\//i.test(href)) {
        tokens[index].attrSet('target', '_blank')
        tokens[index].attrSet('rel', 'noopener noreferrer')
    }

    return defaultLinkOpen(tokens, index, options, environment, renderer)
}

export function slugFromPath(path) {
    return path.split('/').pop().replace(/\.md$/, '')
}

export function parseHelpDocument(source, path, kind) {
    const match = source.match(/^---\r?\n([\s\S]*?)\r?\n---\r?\n([\s\S]*)$/)

    if (!match) {
        throw new Error(`${path}: expected YAML front matter`)
    }

    let metadata

    try {
        metadata = parseYaml(match[1])
    } catch (error) {
        throw new Error(`${path}: invalid YAML front matter: ${error.message}`)
    }

    if (!metadata || typeof metadata !== 'object' || Array.isArray(metadata)) {
        throw new Error(`${path}: front matter must be an object`)
    }

    return {
        ...metadata,
        kind,
        path,
        slug: slugFromPath(path),
        body: match[2].trim(),
        html: markdown.render(match[2]),
    }
}

export function validateHelpDocuments(documents) {
    const errors = []
    const routes = new Set()
    const pageSlugs = new Set(documents.filter(document => document.kind === 'page').map(document => document.slug))
    const guideSlugs = new Set(documents.filter(document => document.kind === 'guide').map(document => document.slug))

    for (const document of documents) {
        const routeKey = `${document.kind}:${document.slug}`

        if (routes.has(routeKey)) {
            errors.push(`${document.path}: duplicate slug "${document.slug}"`)
        }
        routes.add(routeKey)

        if (!/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(document.slug)) {
            errors.push(`${document.path}: filename must be a lowercase slug containing only letters, numbers, and hyphens`)
        }

        for (const field of ['title', 'summary']) {
            if (typeof document[field] !== 'string' || document[field].trim() === '') {
                errors.push(`${document.path}: ${field} must be a non-empty string`)
            }
        }

        if (document.body === '') {
            errors.push(`${document.path}: document body must not be empty`)
        }

        if (/^#\s/m.test(document.body)) {
            errors.push(`${document.path}: body headings must start at level 2`)
        }

        if (document.kind === 'guide' && (!Number.isInteger(document.order) || document.order < 0)) {
            errors.push(`${document.path}: order must be a non-negative integer`)
        }

        if (document.kind === 'news') {
            const published = String(document.published ?? '')
            const parsedDate = new Date(`${published}T00:00:00Z`)

            if (!/^\d{4}-\d{2}-\d{2}$/.test(published) || Number.isNaN(parsedDate.valueOf()) || parsedDate.toISOString().slice(0, 10) !== published) {
                errors.push(`${document.path}: published must be a valid YYYY-MM-DD date`)
            }

            if (!document.slug.startsWith(`${published}-`) || document.slug === published) {
                errors.push(`${document.path}: filename must start with the published date`)
            }

            if (document.guide !== undefined && (typeof document.guide !== 'string' || !guideSlugs.has(document.guide))) {
                errors.push(`${document.path}: guide must reference an existing guide slug`)
            }
        }
    }

    for (const requiredPage of ['faq', 'about']) {
        if (!pageSlugs.has(requiredPage)) {
            errors.push(`resources/help/pages/${requiredPage}.md: required Help page is missing`)
        }
    }

    return errors
}
