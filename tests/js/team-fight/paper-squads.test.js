import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    hasPaperSquad,
    paperSquadMessages
} from '../../../resources/js/admin-v2/views/team-fight/paper-squads.js'

const finding = (overrides) => ({
    squadIndex: 0,
    refId: '1',
    name: 'Bo',
    unplayedCategories: [],
    matchCount: 1,
    requiredMatchCount: 1,
    ...overrides,
})

test('no findings give no messages', () => {
    assert.deepEqual(paperSquadMessages({missingRound: false, players: []}), [])
    assert.deepEqual(paperSquadMessages(null), [])
})

test('a missing round number asks the team manager to fill it in', () => {
    assert.deepEqual(paperSquadMessages({missingRound: true, players: []}), [
        'Holdrunden mangler rundenummer, så papirholdsreglen (§38 stk. 7) kan ikke kontrolleres. Udfyld rundenummeret.',
    ])
})

test('an unplayed category is named', () => {
    assert.deepEqual(paperSquadMessages({missingRound: false, players: [finding({unplayedCategories: ['HD', 'MD']})]}), [
        'Hold 1 (papirhold): Bo har ikke spillet HD, MD tidligere i sæsonen.',
    ])
})

test('too few earlier matches on the same or a lower team are counted', () => {
    assert.deepEqual(paperSquadMessages({missingRound: false, players: [
        finding({squadIndex: 1, matchCount: 0, requiredMatchCount: 1}),
        finding({name: 'Anders', matchCount: 1, requiredMatchCount: 2, unplayedCategories: ['HS']}),
    ]}), [
        'Hold 2 (papirhold): Bo har stået på samme eller et lavere hold 0 gange tidligere i sæsonen, men skal have stået der mindst 1 gang.',
        'Hold 1 (papirhold): Anders har ikke spillet HS tidligere i sæsonen og har stået på samme eller et lavere hold 1 gang tidligere i sæsonen, men skal have stået der mindst 2 gange.',
    ])
})

test('the paper-squad status shows when a Squad in the lineup is a Paper Squad', () => {
    assert.equal(hasPaperSquad([{isPaperSquad: false}, {isPaperSquad: true}]), true)
    assert.equal(hasPaperSquad([{isPaperSquad: false}, {}]), false)
    assert.equal(hasPaperSquad(undefined), false)
})
