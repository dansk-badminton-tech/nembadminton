import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    quarantineLabel,
    replaceableMembers,
    replacedBy,
    reserveCandidates,
    reserveFindingMessages,
    reserveOrigin,
    reserveOnHigherSquad,
    reserveWarnings,
    seasonHistoryReserveNote,
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

test('the reserve field offers Members on lower Squads who are not a reserve yet, with their Squad and category', () => {
    assert.deepEqual(
        reserveCandidates(squads(), 0).map(c => [c.player.name, c.fromIndex, c.categoryName]),
        [['Xenia', 1, '1. DS'], ['Tine', 1, '1. MD'], ['Zara', 2, '1. DS']],
    )
    assert.deepEqual(reserveCandidates(squads([record('r1', xenia, yrsa)]), 0).map(c => c.player.name), ['Tine', 'Zara'])
    assert.deepEqual(reserveCandidates(squads(), 1).map(c => c.player.name), ['Zara'])
    assert.deepEqual(reserveCandidates(squads(), 2), [])
})

test('a Member is offered once, from the first lower Squad they stand on', () => {
    const lineup = squads()
    lineup[2].categories.push(category('2. DS', [xenia]))
    assert.deepEqual(reserveCandidates(lineup, 0).map(c => [c.player.name, c.fromIndex]), [['Xenia', 1], ['Tine', 1], ['Zara', 2]])
})

test('the replaced field offers Members on this Squad who are not replaced yet, with their categories', () => {
    assert.deepEqual(replaceableMembers(squads()[0]), [
        {refId: '1', name: 'Yrsa', gender: 'WOMEN', categories: ['1. DS', '1. DD']},
        {refId: '2', name: 'Vera', gender: 'WOMEN', categories: ['1. DD']},
    ])
    assert.deepEqual(replaceableMembers(squads([record('r1', xenia, yrsa)])[0]).map(m => m.name), ['Vera'])
})

test('tags: Y is replaced on this Squad; X is a reserve on a higher Squad', () => {
    const lineup = squads([record('r1', xenia, yrsa)])
    assert.equal(replacedBy(lineup[0], '1').reserve.name, 'Xenia')
    assert.equal(replacedBy(lineup[0], '2'), null)
    assert.deepEqual(reserveOnHigherSquad(lineup, '3'), {index: 0, record: lineup[0].reserves[0]})
    assert.equal(reserveOnHigherSquad(lineup, '1'), null)
})

test('the row says where the reserve stands now', () => {
    const lineup = squads([record('r1', xenia, yrsa)])
    assert.deepEqual(reserveOrigin(lineup, lineup[0].reserves[0]), {index: 1, categoryName: '1. DS'})
    assert.equal(reserveOrigin(lineup, record('r2', player('9', 'Una'), yrsa)), null)
})

test('a stale record gets a soft warning', () => {
    assert.deepEqual(reserveWarnings(squads(), 0, record('r1', xenia, yrsa)), [])

    const yrsaMoved = squads()
    yrsaMoved[0].categories = [category('1. DS', [vera])]
    assert.deepEqual(reserveWarnings(yrsaMoved, 0, record('r1', xenia, yrsa)), ['Yrsa står ikke længere på Hold 1'])

    const xeniaUp = squads()
    xeniaUp[0].categories.push(category('2. DS', [xenia]))
    xeniaUp[1].categories = []
    assert.deepEqual(reserveWarnings(xeniaUp, 0, record('r1', xenia, yrsa)), ['Xenia står nu på Hold 1, ikke et lavere hold'])
})

test('validation gets each Squad\'s records as refIds', () => {
    assert.deepEqual(validationReserves(squads([record('r1', xenia, yrsa)])[0]), [{reserveRefId: '3', replacedRefId: '1'}])
    assert.deepEqual(validationReserves({categories: []}), [])
})

test('the quarantine tag names the round and the Squad', () => {
    assert.equal(quarantineLabel({round: 4, squadPosition: 1}), 'Karantæne · reserve i R4 på Hold 1')
})

test('§43 findings become errors and warnings that name the players and the category', () => {
    const reserve = {refId: '3', name: 'Xenia', points: 1200}
    const replaced = {refId: '1', name: 'Yrsa', points: 1100}
    const {errors, warnings} = reserveFindingMessages([
        {type: 'STRENGTH_INCREASE', squadIndex: 0, category: 'DS', categoryName: '1. DS', reserve, replaced, betterReserve: null},
        {type: 'BETTER_RESERVE_AVAILABLE', squadIndex: 1, category: 'DD', categoryName: '2. DD', reserve, replaced, betterReserve: {name: 'Zara', points: 1150}},
    ])
    assert.deepEqual(errors, ['Hold 1: Reserven Xenia har flere point end Yrsa i 1. DS (1200 mod 1100). Det er en styrkeforøgelse (§43 stk. 3).'])
    assert.deepEqual(warnings, ['Hold 2: Bedre reserve tilgængelig: Zara (1150 point i 2. DD) i stedet for Xenia for Yrsa (§43 stk. 2).'])
})

test('Season History notes who the player was a reserve for in a round', () => {
    const round = {reserves: [{refId: '3', name: 'Xenia', replacedName: 'Yrsa', squadPosition: 1}]}
    assert.equal(seasonHistoryReserveNote(round, '3'), 'Reserve på Hold 1 for Yrsa')
    assert.equal(seasonHistoryReserveNote(round, '1'), null)
    assert.equal(seasonHistoryReserveNote({}, '3'), null)
})
