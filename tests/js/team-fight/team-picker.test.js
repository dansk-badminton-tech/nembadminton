import {test} from 'node:test'
import assert from 'node:assert/strict'
import {buildTeamPickerOptions, squadSubmitLabel} from '../../../resources/js/admin-v2/views/team-fight/team-picker.js'

const team = (id, name, extra = {}) => ({
    id,
    name,
    groupName: null,
    customTierName: null,
    tier: null,
    ...extra,
})

test('each team becomes one option labelled with its name', () => {
    const options = buildTeamPickerOptions([
        team('1', 'Højbjerg 1', {tier: {id: '9', tierName: '1. division'}, groupName: 'Pulje 2'}),
        team('2', 'Højbjerg 2'),
    ], [])

    assert.deepEqual(options.map((option) => [option.id, option.label]), [
        ['1', 'Højbjerg 1'],
        ['2', 'Højbjerg 2'],
    ])
})

test('each option carries the level and group as a details line', () => {
    const options = buildTeamPickerOptions([
        team('1', 'Højbjerg 1', {tier: {tierName: '1. division'}, groupName: 'Pulje 2'}),
        team('2', 'Højbjerg 2', {customTierName: 'Kredsserie'}),
        team('3', 'Højbjerg 3'),
    ], [])

    assert.deepEqual(options.map((option) => option.details), [
        '1. division · Pulje 2',
        'Kredsserie',
        '',
    ])
})

test('the tier wins over a custom level', () => {
    const [option] = buildTeamPickerOptions([
        team('1', 'A', {tier: {tierName: 'Liga'}, customTierName: 'Kredsserie'}),
    ], [])

    assert.equal(option.details, 'Liga')
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

test('the submit label names the squad being added', () => {
    assert.equal(squadSubmitLabel('Højbjerg 1', 3), 'Tilføj "Højbjerg 1" til holdopstillingen')
    assert.equal(squadSubmitLabel('  Reservehold ', 3), 'Tilføj "Reservehold" til holdopstillingen')
})

test('without a name the submit label falls back to the squad number', () => {
    assert.equal(squadSubmitLabel('', 3), 'Tilføj "Hold 3 uden navn" til holdopstillingen')
    assert.equal(squadSubmitLabel('   ', 1), 'Tilføj "Hold 1 uden navn" til holdopstillingen')
})
