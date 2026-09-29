import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    buildTeamPickerOptions,
    describeTeam,
} from '../../../resources/js/admin-v2/views/team-fight/team-picker.js'

const team = (id, name, extra = {}) => ({
    id,
    name,
    groupName: null,
    customTierName: null,
    tier: null,
    ...extra,
})

test('each team becomes one option labelled with its name only', () => {
    const options = buildTeamPickerOptions([
        team('1', 'Højbjerg 1', {tier: {id: '9', tierName: '1. division'}, groupName: 'Pulje 2'}),
        team('2', 'Højbjerg 2'),
    ], [])

    assert.deepEqual(options.map((option) => [option.id, option.label]), [
        ['1', 'Højbjerg 1'],
        ['2', 'Højbjerg 2'],
    ])
})

test('teams that already have a squad in the team round stay selectable but are marked added', () => {
    const options = buildTeamPickerOptions([team('1', 'A'), team(2, 'B')], [2])

    assert.deepEqual(options.map((option) => [option.label, option.added]), [
        ['A', false],
        ['B', true],
    ])
})

test('the option carries the team so it can be selected', () => {
    const hold = team('1', 'A')

    assert.equal(buildTeamPickerOptions([hold], [])[0].team, hold)
})

test('a team is described by name, level and group', () => {
    assert.equal(
        describeTeam(team('1', 'Højbjerg 1', {tier: {tierName: '1. division'}, groupName: 'Pulje 2'})),
        'Højbjerg 1 · 1. division · Pulje 2'
    )
})

test('a custom level is used when the team has no tier, and missing parts are left out', () => {
    assert.equal(describeTeam(team('1', 'Højbjerg 3', {customTierName: 'Kredsserie'})), 'Højbjerg 3 · Kredsserie')
    assert.equal(describeTeam(team('1', 'Højbjerg 4')), 'Højbjerg 4')
})

test('tierLabel prefers the tier over the custom level', () => {
    const [option] = buildTeamPickerOptions([
        team('1', 'A', {tier: {tierName: 'Liga'}, customTierName: 'Kredsserie'}),
    ], [])

    assert.equal(option.tierLabel, 'Liga')
})
