import {test} from 'node:test'
import assert from 'node:assert/strict'
import {headingAnchor, parseHelpDocument} from '../../../resources/js/admin-v2/help/markdown.js'

const guide = body => parseHelpDocument(`---\ntitle: Vejledning\nsummary: Resumé.\norder: 1\n---\n${body}`, 'guides/vejledning.md', 'guide')

test('a heading gets an anchor written like a slug, with æ, ø and å spelled out', () => {
    assert.equal(headingAnchor('Når holdene spiller på forskellige datoer'), 'naar-holdene-spiller-paa-forskellige-datoer')
    assert.equal(headingAnchor('Kontrollér holdenes ranglister'), 'kontroller-holdenes-ranglister')
    assert.equal(headingAnchor('Hvad er "nyeste"? Ærlig talt'), 'hvad-er-nyeste-aerlig-talt')
})

test('headings in a document are rendered with their anchor as id', () => {
    const {html} = guide('## Marker et papirhold\n\nTekst.\n\n### Når holdene spiller på forskellige datoer\n')

    assert.match(html, /<h2 id="marker-et-papirhold">Marker et papirhold<\/h2>/)
    assert.match(html, /<h3 id="naar-holdene-spiller-paa-forskellige-datoer">/)
})
