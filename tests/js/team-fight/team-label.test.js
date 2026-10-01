import {test} from 'node:test'
import assert from 'node:assert/strict'
import {teamLabel, teamTierLabel} from '../../../resources/js/admin-v2/views/team-fight/team-label.js'

test('teamTierLabel prefers the tournament tier over the custom tier name', () => {
    assert.equal(teamTierLabel({tier: {tierName: '1. division'}, customTierName: 'Egen række'}), '1. division')
})

test('teamTierLabel falls back to the custom tier name', () => {
    assert.equal(teamTierLabel({tier: null, customTierName: 'Veteranrække'}), 'Veteranrække')
})

test('teamTierLabel is empty for a missing team or a team without tier', () => {
    assert.equal(teamTierLabel(null), '')
    assert.equal(teamTierLabel({name: 'Hold', tier: null, customTierName: null}), '')
})

test('teamLabel joins name, tier and group, skipping blanks', () => {
    assert.equal(teamLabel({name: 'Højbjerg 1', tier: {tierName: 'Serie 1'}, groupName: 'Pulje 2'}), 'Højbjerg 1 · Serie 1 · Pulje 2')
    assert.equal(teamLabel({name: 'Højbjerg 2', tier: null, customTierName: null, groupName: null}), 'Højbjerg 2')
    assert.equal(teamLabel(null), '')
})
