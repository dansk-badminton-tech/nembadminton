import {test} from 'node:test'
import assert from 'node:assert/strict'
import {validateHelpDocuments} from '../../../resources/js/admin-v2/help/markdown.js'

const requiredPages = [
    {kind: 'page', slug: 'faq', path: 'pages/faq.md', title: 'FAQ', summary: 'Svar.', body: 'Tekst.'},
    {kind: 'page', slug: 'about', path: 'pages/about.md', title: 'Om', summary: 'Om os.', body: 'Tekst.'},
]

function guide(slug, metadata = {}) {
    return {kind: 'guide', slug, path: `guides/${slug}.md`, title: slug, summary: 'Resumé.', body: 'Tekst.', order: 10, ...metadata}
}

function validate(...guides) {
    return validateHelpDocuments([...requiredPages, ...guides])
}

test('a guide without journey placement is valid', () => {
    assert.deepEqual(validate(guide('andet')), [])
})

test('a guide placed as an optional branch of a known stage is valid', () => {
    assert.deepEqual(validate(guide('scenarier', {journey: {stage: 'lineup', role: 'optional'}})), [])
})

test('journey placement must name a known stage', () => {
    assert.deepEqual(validate(guide('scenarier', {journey: {stage: 'holdopstilling', role: 'optional'}})), [
        'guides/scenarier.md: journey.stage must be one of setup, cancellations, lineup, sharing',
    ])
})

test('journey placement must name a known role', () => {
    assert.deepEqual(validate(guide('scenarier', {journey: {stage: 'lineup', role: 'branch'}})), [
        'guides/scenarier.md: journey.role must be one of step, optional, alternative, troubleshooting',
    ])
})

test('journey placement must be an object', () => {
    assert.deepEqual(validate(guide('scenarier', {journey: 'lineup'})), [
        'guides/scenarier.md: journey must be an object with stage and role',
    ])
})

test('each stage has at most one step guide', () => {
    assert.deepEqual(validate(
        guide('lav-holdopstilling', {journey: {stage: 'lineup', role: 'step'}}),
        guide('kontroller-holdopstilling', {journey: {stage: 'lineup', role: 'step'}}),
    ), [
        'guides/kontroller-holdopstilling.md: journey stage "lineup" already has step guide "lav-holdopstilling"',
    ])
})

test('journey placement is only allowed on guides', () => {
    const announcement = {kind: 'news', slug: '2026-09-21-nyt', path: 'news/2026-09-21-nyt.md', title: 'Nyt', summary: 'Nyt.', body: 'Tekst.', published: '2026-09-21', journey: {stage: 'lineup', role: 'step'}}

    assert.deepEqual(validateHelpDocuments([...requiredPages, announcement]), [
        'news/2026-09-21-nyt.md: journey is only allowed on guides',
    ])
})
