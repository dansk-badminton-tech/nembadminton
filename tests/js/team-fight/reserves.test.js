import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    quarantineLabel,
    replaceableMembers,
    reserveCandidates,
    reserveFindingMessages,
    reserveOrigin,
    reserveOn,
    reserveOnHigherSquad,
    reserveWarnings,
    validationReserves
} from '../../../resources/js/admin-v2/views/team-fight/reserves.js'

const player = (refId, name, gender = 'WOMEN') => ({refId, name, gender})
const yrsa = player('1', 'Yrsa')
const vera = player('2', 'Vera')
const xenia = player('3', 'Xenia')
const tine = player('4', 'Tine')
const zara = player('5', 'Zara')

const category = (name, players) => ({name, category: name.split(' ')[1], players})
const record = (id, reserve, replaced) => ({id, reserve, replaced})
const squads = (reservesOnHold1 = []) => [
    {id: 'h1', categories: [category('1. DS', [yrsa]), category('1. DD', [yrsa, vera])], reserves: reservesOnHold1},
    {id: 'h2', categories: [category('1. DS', [xenia]), category('1. MD', [tine])], reserves: []},
    {id: 'h3', categories: [category('1. DS', [zara])], reserves: []},
]
// The lineup as played after "Xenia for Yrsa" on Hold 1: Xenia stands in Yrsa's slots and keeps 1. DS on Hold 2.
const xeniaForYrsa = () => {
    const lineup = squads([record('r1', xenia, yrsa)])
    lineup[0].categories = [category('1. DS', [xenia]), category('1. DD', [xenia, vera])]
    return lineup
}

test('the reserve field offers Members on lower Squads who are not a reserve yet, with their Squad and category', () => {
    assert.deepEqual(
        reserveCandidates(squads(), 0).map(c => [c.player.name, c.fromIndex, c.categoryName]),
        [['Xenia', 1, '1. DS'], ['Tine', 1, '1. MD'], ['Zara', 2, '1. DS']],
    )
    assert.deepEqual(reserveCandidates(xeniaForYrsa(), 0).map(c => c.player.name), ['Tine', 'Zara'])
    assert.deepEqual(reserveCandidates(squads(), 1).map(c => c.player.name), ['Zara'])
    assert.deepEqual(reserveCandidates(squads(), 2), [])
})

test('the reserve field also offers Available Members who are not a reserve yet, after the lower Squads', () => {
    const una = player('9', 'Una')
    const ole = player('8', 'Ole', 'MEN')
    assert.deepEqual(
        reserveCandidates(squads(), 1, [una, ole]).map(c => [c.player.name, c.fromIndex]),
        [['Zara', 2], ['Una', null], ['Ole', null]],
    )
    assert.deepEqual(reserveCandidates(squads([record('r1', una, yrsa)]), 1, [una, ole]).map(c => c.player.name), ['Zara', 'Ole'])
    // A search result that is placed after all (e.g. a stale result) is never offered as an Available Member.
    assert.deepEqual(reserveCandidates(squads(), 1, [yrsa]).map(c => c.player.name), ['Zara'])
})

test('a Member is offered once, from the first lower Squad they stand on', () => {
    const lineup = squads()
    lineup[2].categories.push(category('2. DS', [xenia]))
    assert.deepEqual(reserveCandidates(lineup, 0).map(c => [c.player.name, c.fromIndex]), [['Xenia', 1], ['Tine', 1], ['Zara', 2]])
})

test('the replaced field offers Members on this Squad who are not a reserve there, with their categories', () => {
    assert.deepEqual(replaceableMembers(squads()[0]), [
        {refId: '1', name: 'Yrsa', gender: 'WOMEN', categories: ['1. DS', '1. DD']},
        {refId: '2', name: 'Vera', gender: 'WOMEN', categories: ['1. DD']},
    ])
    assert.deepEqual(replaceableMembers(xeniaForYrsa()[0]).map(m => m.name), ['Vera'])
})

test('tags: X is a reserve on this Squad, and on a lower Squad X is a reserve on a higher one', () => {
    const lineup = xeniaForYrsa()
    assert.equal(reserveOn(lineup[0], '3').replaced.name, 'Yrsa')
    assert.equal(reserveOn(lineup[0], '2'), null)
    assert.deepEqual(reserveOnHigherSquad(lineup, '3'), {index: 0, record: lineup[0].reserves[0]})
    assert.equal(reserveOnHigherSquad(lineup, '1'), null)
})

test('the row says which lower Squad the reserve also plays on', () => {
    const lineup = xeniaForYrsa()
    assert.deepEqual(reserveOrigin(lineup, 0, lineup[0].reserves[0]), {index: 1, categoryName: '1. DS'})
    const una = player('9', 'Una')
    lineup[0].categories[0].players = [una]
    assert.equal(reserveOrigin(lineup, 0, record('r2', una, yrsa)), null)
})

test('a stale record gets a soft warning', () => {
    assert.deepEqual(reserveWarnings(xeniaForYrsa(), 0, record('r1', xenia, yrsa)), [])

    // Yrsa put back, Xenia taken off Hold 1.
    assert.deepEqual(reserveWarnings(squads(), 0, record('r1', xenia, yrsa)), ['Xenia står ikke på Hold 1', 'Yrsa står stadig på Hold 1'])

    const both = xeniaForYrsa()
    both[0].categories.push(category('2. DS', [yrsa]))
    assert.deepEqual(reserveWarnings(both, 0, record('r1', xenia, yrsa)), ['Yrsa står stadig på Hold 1'])
})

test('validation gets each Squad\'s records with the replaced player\'s points', () => {
    const withPoints = {...record('r1', xenia, yrsa), replacedPoints: [{category: 'DS', points: 900, position: 12, vintage: 'SEN', __typename: 'Point'}]}
    assert.deepEqual(validationReserves({categories: [], reserves: [withPoints]}), [{
        reserveRefId: '3',
        replaced: {refId: '1', name: 'Yrsa', gender: 'WOMEN', points: [{category: 'DS', points: 900, position: 12, vintage: 'SEN'}]}
    }])
    assert.deepEqual(validationReserves(xeniaForYrsa()[0])[0].replaced.points, [])
    assert.deepEqual(validationReserves({categories: []}), [])
})

test('the quarantine tag names the round and the Squad', () => {
    assert.equal(quarantineLabel({round: 4, squadPosition: 1}), 'Karantæne · reserve i R4 på Hold 1')
})

test('§43 stk. 3 findings become warnings that name the players and the category', () => {
    const reserve = {refId: '3', name: 'Xenia', points: 1200}
    const replaced = {refId: '1', name: 'Yrsa', points: 1100}
    assert.deepEqual(
        reserveFindingMessages([{type: 'STRENGTH_INCREASE', squadIndex: 0, category: 'DS', categoryName: '1. DS', reserve, replaced}]),
        ['Hold 1: Reserven Xenia har flere point end Yrsa i 1. DS (1200 mod 1100). Er holdet stærkere end planlagt (§43 stk. 3)?'],
    )
})
