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

function announcement(slug, metadata = {}) {
    return {kind: 'news', slug, path: `news/${slug}.md`, title: 'Nyt', summary: 'Nyt.', body: 'Tekst.', published: slug.slice(0, 10), ...metadata}
}

function validate(...documents) {
    return validateHelpDocuments([...requiredPages, ...documents])
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
    assert.deepEqual(validate(announcement('2026-09-21-nyt', {journey: {stage: 'lineup', role: 'step'}})), [
        'news/2026-09-21-nyt.md: journey is only allowed on guides',
    ])
})

test('a guide containing a TODO(agent) marker names the file and quotes the question', () => {
    const body = 'Tekst.\n\n<!-- TODO(agent): Hvilken knap gemmer holdopstillingen? -->\n\nMere tekst.'

    assert.deepEqual(validate(guide('gem-holdopstilling', {body})), [
        'guides/gem-holdopstilling.md: unresolved TODO(agent) marker "Hvilken knap gemmer holdopstillingen?"',
    ])
})

test('a Release Announcement reports every TODO(agent) marker it contains', () => {
    const body = '<!-- TODO(agent): Hvornår udrulles ændringen? -->\n\nTekst.\n\n<!--TODO(agent):\nGælder det også\nfor ungdomshold?\n-->'

    assert.deepEqual(validate(announcement('2026-09-21-nyt', {body})), [
        'news/2026-09-21-nyt.md: unresolved TODO(agent) marker "Hvornår udrulles ændringen?"',
        'news/2026-09-21-nyt.md: unresolved TODO(agent) marker "Gælder det også for ungdomshold?"',
    ])
})

test('a TODO(agent) marker without a question is reported', () => {
    assert.deepEqual(validate(guide('andet', {body: 'Tekst.\n\n<!-- TODO(agent): -->'})), [
        'guides/andet.md: TODO(agent) marker has no question',
    ])
})

test('a malformed TODO(agent) marker is reported instead of being published as text', () => {
    const body = [
        '<!-- TODO(agent) Mangler kolon? -->',
        '<!-- todo (agent): Små bogstaver? -->',
        '<!-- TODO(agent): Ikke lukket?',
    ].join('\n\n')

    assert.deepEqual(validate(guide('andet', {body})), [
        'guides/andet.md: malformed TODO(agent) marker; use <!-- TODO(agent): <question> -->',
        'guides/andet.md: malformed TODO(agent) marker; use <!-- TODO(agent): <question> -->',
        'guides/andet.md: malformed TODO(agent) marker; use <!-- TODO(agent): <question> -->',
    ])
})

test('ordinary comments are not TODO(agent) markers', () => {
    assert.deepEqual(validate(guide('andet', {body: 'Tekst.\n\n<!-- En almindelig kommentar -->'})), [])
})
