import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    applyTier,
    pickMatchFormat,
    resetTierFormat,
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
        applyTier({matchCount: 13, fromTier: null}, '1. division', 3),
        {matchCount: 9, fromTier: '1. division'}
    )
})

test('choosing a DH tier overrides an earlier manual pick', () => {
    assert.deepEqual(
        applyTier({matchCount: 'custom', fromTier: null}, 'Danmarksserien', 3),
        {matchCount: 13, fromTier: 'Danmarksserien'}
    )
})

test('a tier without a suggestion or no tier keeps the Match Format but drops the tier as its source', () => {
    const current = {matchCount: 9, fromTier: '1. division'}
    const kept = {matchCount: 9, fromTier: null}
    assert.deepEqual(applyTier(current, 'Serie 1', 3), kept)
    assert.deepEqual(applyTier(current, '', 3), kept)
    assert.deepEqual(applyTier(current, null, 3), kept)
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

test('starting without a Team resets a format from the tier to 13', () => {
    assert.deepEqual(
        resetTierFormat({matchCount: 9, fromTier: '1. division'}),
        {matchCount: 13, fromTier: null}
    )
})

test('starting without a Team keeps a format the coach picked', () => {
    assert.deepEqual(resetTierFormat({matchCount: 9, fromTier: null}), {matchCount: 9, fromTier: null})
    assert.deepEqual(resetTierFormat({matchCount: 'custom', fromTier: null}), {matchCount: 'custom', fromTier: null})
})
