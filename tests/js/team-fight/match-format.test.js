import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    chooseTier,
    pickMatchFormat,
    suggestMatchFormat
} from '../../../resources/js/admin-v2/views/team-fight/match-format.js'

test('Badmintonligaen and 1. division suggest 9 matches', () => {
    assert.equal(suggestMatchFormat('Badmintonligaen', 3), 9)
    assert.equal(suggestMatchFormat('1. division', 12), 9)
})

test('2. division, 3. division and Danmarksserien suggest 13 matches', () => {
    assert.equal(suggestMatchFormat('2. division', 3), 13)
    assert.equal(suggestMatchFormat('3. division', 12), 13)
    assert.equal(suggestMatchFormat('Danmarksserien', 12), 13)
})

test('2. division suggests 9 matches in round 12, the qualification for 1. division', () => {
    assert.equal(suggestMatchFormat('2. division', 12), 9)
})

test('2. division without a round number suggests 13 matches', () => {
    assert.equal(suggestMatchFormat('2. division', null), 13)
    assert.equal(suggestMatchFormat('2. division', undefined), 13)
})

test('tier variants, Kreds tiers and blanks get no suggestion', () => {
    assert.equal(suggestMatchFormat('2. division - slutspil', 3), null)
    assert.equal(suggestMatchFormat('1. division (oversidder-runde)', 3), null)
    assert.equal(suggestMatchFormat('1. Division', 3), null)
    assert.equal(suggestMatchFormat('1. div', 3), null)
    assert.equal(suggestMatchFormat('Serie 1', 3), null)
    assert.equal(suggestMatchFormat('Københavnsserien', 3), null)
    assert.equal(suggestMatchFormat('', 3), null)
    assert.equal(suggestMatchFormat(null, 3), null)
})

test('surrounding whitespace does not hide the tier', () => {
    assert.equal(suggestMatchFormat(' 1. division ', 3), 9)
})

test('choosing a DH tier sets the Match Format and remembers the tier', () => {
    assert.deepEqual(
        chooseTier({matchCount: 13, fromTier: null}, '1. division', 3),
        {matchCount: 9, fromTier: '1. division'}
    )
})

test('choosing a DH tier overrides an earlier manual pick', () => {
    assert.deepEqual(
        chooseTier({matchCount: 'custom', fromTier: null}, 'Danmarksserien', 3),
        {matchCount: 13, fromTier: 'Danmarksserien'}
    )
})

test('a tier without a suggestion or no tier leaves the Match Format as it is', () => {
    const current = {matchCount: 9, fromTier: '1. division'}
    assert.deepEqual(chooseTier(current, 'Serie 1', 3), current)
    assert.deepEqual(chooseTier(current, '', 3), current)
    assert.deepEqual(chooseTier(current, null, 3), current)
})

test('picking another Match Format drops the tier as its source', () => {
    assert.deepEqual(
        pickMatchFormat({matchCount: 9, fromTier: '1. division'}, 13),
        {matchCount: 13, fromTier: null}
    )
    assert.deepEqual(
        pickMatchFormat({matchCount: 9, fromTier: '1. division'}, 'custom'),
        {matchCount: 'custom', fromTier: null}
    )
})

test('picking the suggested Match Format again keeps the tier as its source', () => {
    assert.deepEqual(
        pickMatchFormat({matchCount: 9, fromTier: '1. division'}, 9),
        {matchCount: 9, fromTier: '1. division'}
    )
})
