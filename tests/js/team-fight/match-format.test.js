import {test} from 'node:test'
import assert from 'node:assert/strict'
import {applyTierToMatchFormat, suggestMatchFormat} from '../../../resources/js/admin-v2/views/team-fight/match-format.js'

test('Badmintonligaen and 1. division play 9 matches', () => {
    assert.equal(suggestMatchFormat('Badmintonligaen', 3), 9)
    assert.equal(suggestMatchFormat('1. division', 3), 9)
    assert.equal(suggestMatchFormat('1. division', 12), 9)
})

test('2. division, 3. division and Danmarksserien play 13 matches', () => {
    assert.equal(suggestMatchFormat('2. division', 3), 13)
    assert.equal(suggestMatchFormat('3. division', 3), 13)
    assert.equal(suggestMatchFormat('Danmarksserien', 3), 13)
})

test('2. division plays 9 matches in round 12, the qualification for 1. division', () => {
    assert.equal(suggestMatchFormat('2. division', 12), 9)
    assert.equal(suggestMatchFormat('3. division', 12), 13)
    assert.equal(suggestMatchFormat('Danmarksserien', 12), 13)
})

test('2. division without a round number plays 13 matches', () => {
    assert.equal(suggestMatchFormat('2. division', null), 13)
    assert.equal(suggestMatchFormat('2. division', undefined), 13)
})

test('only exact tier names get a suggestion', () => {
    assert.equal(suggestMatchFormat('2. division - slutspil', 3), null)
    assert.equal(suggestMatchFormat('1. division (oversidder-runde)', 3), null)
    assert.equal(suggestMatchFormat('1. div', 3), null)
    assert.equal(suggestMatchFormat('1. Division', 3), null)
})

test('surrounding spaces do not stop a suggestion', () => {
    assert.equal(suggestMatchFormat(' 1. division ', 3), 9)
})

test('kreds tiers and missing tiers get no suggestion', () => {
    assert.equal(suggestMatchFormat('Serie 1', 3), null)
    assert.equal(suggestMatchFormat('Københavnsserien', 3), null)
    assert.equal(suggestMatchFormat('Kredsserie', 3), null)
    assert.equal(suggestMatchFormat('', 3), null)
    assert.equal(suggestMatchFormat(null, 3), null)
})

test('a DH tier sets the Match Format and remembers the tier for the hint', () => {
    assert.deepEqual(
        applyTierToMatchFormat({matchCount: 13, suggestedTier: null}, '1. division', 3),
        {matchCount: 9, suggestedTier: '1. division'},
    )
})

test('a DH tier overrides an earlier manual pick, also Tilpas', () => {
    assert.deepEqual(
        applyTierToMatchFormat({matchCount: 'custom', suggestedTier: null}, 'Badmintonligaen', 3),
        {matchCount: 9, suggestedTier: 'Badmintonligaen'},
    )
})

test('a tier without a suggestion keeps the format and hides the hint', () => {
    assert.deepEqual(
        applyTierToMatchFormat({matchCount: 9, suggestedTier: '1. division'}, 'Serie 1', 3),
        {matchCount: 9, suggestedTier: null},
    )
})

test('removing the tier keeps the format and hides the hint', () => {
    assert.deepEqual(
        applyTierToMatchFormat({matchCount: 9, suggestedTier: '1. division'}, '', 3),
        {matchCount: 9, suggestedTier: null},
    )
})
