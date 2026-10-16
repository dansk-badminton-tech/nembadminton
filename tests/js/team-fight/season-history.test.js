import {test} from 'node:test'
import assert from 'node:assert/strict'
import {seasonHistory} from '../../../resources/js/admin-v2/views/team-fight/season-history/season-history.js'

const player = (refId, name, vintage = 'SEN', gender = 'MEN') => ({refId, name, gender, vintage})
const anders = player('1', 'Anders')
const bo = player('2', 'Bo')
const carl = player('3', 'Carl', 'U17')
const dan = player('4', 'Dan')
const erik = player('5', 'Erik')
const fie = player('6', 'Fie', 'SEN', 'WOMEN')

const place = (place, squadPosition, label, players) => ({place, squadPosition, teamName: `Hold ${squadPosition}`, tier: 'Serie 1', label, players})
const round = (id, name, isCurrent, categories) => ({
    id,
    name,
    gameDate: isCurrent ? '2026-11-01' : `2026-09-${id}`,
    round: Number(id),
    phase: 'GRUNDSPIL',
    isCurrent,
    categories: ['HS', 'DS', 'HD', 'DD', 'MD'].map(category => ({category, places: categories[category] || []})),
})

// Round 1: Anders 1. HS, Bo 2. HS, Carl (U17) on Hold 2. Round 2: Carl climbs past Bo. Now: Carl at 1. HS.
// Dan (3 places below) and Erik are never within 2 places of Anders.
const rounds = [
    round('10', 'Runde 1', false, {
        HS: [place(1, 1, '1. HS', [anders]), place(2, 1, '2. HS', [bo]), place(3, 2, '1. HS', [carl]), place(4, 2, '2. HS', [dan]), place(5, 2, '3. HS', []), place(6, 3, '1. HS', [erik])],
        HD: [place(1, 1, '1. HD', [anders, bo])],
    }),
    round('11', 'Runde 2', false, {
        HS: [place(1, 1, '1. HS', [anders]), place(2, 1, '2. HS', [carl]), place(3, 2, '1. HS', [bo]), place(4, 2, '2. HS', []), place(5, 2, '3. HS', []), place(6, 3, '1. HS', [erik])],
        MD: [place(1, 1, '1. MD', [anders, fie])],
    }),
    round('12', 'Runde 3', true, {
        HS: [place(1, 1, '1. HS', [carl]), place(2, 1, '2. HS', [bo]), place(6, 3, '1. HS', [erik])],
        HD: [place(1, 1, '1. HD', [carl, bo])],
    }),
]

test('the player has only the categories they were placed in, in category order', () => {
    const history = seasonHistory(rounds)

    assert.deepEqual(history.categoriesOf('1'), ['HS', 'HD', 'MD'])
    assert.deepEqual(history.currentCategoriesOf('1'), [])
    assert.deepEqual(history.currentCategoriesOf('3'), ['HS', 'HD'])
    assert.deepEqual(history.categoriesOf('unknown'), [])
})

test('the default category is the current single, then the first current one, then the first one', () => {
    const history = seasonHistory(rounds)

    assert.equal(history.defaultCategory('3'), 'HS')
    assert.equal(history.defaultCategory('6'), 'MD')
    const onlyDoubleNow = seasonHistory([rounds[0], round('12', 'Runde 3', true, {HD: [place(1, 1, '1. HD', [anders, bo])]})])
    assert.equal(onlyDoubleNow.defaultCategory('1'), 'HD')
    assert.equal(history.defaultCategory('unknown'), null)
})

test('a player has a Category Rank per round, or null where they were not placed', () => {
    const history = seasonHistory(rounds)

    assert.deepEqual([0, 1, 2].map(r => history.placeOf('3', 'HS', r)), [3, 2, 1])
    assert.deepEqual([0, 1, 2].map(r => history.placeOf('1', 'HS', r)), [1, 1, null])
})

test('a place is labelled with its squad and category name', () => {
    const history = seasonHistory(rounds)

    assert.equal(history.placeLabel('HS', 3), 'Hold 2 · 1. HS')
    assert.equal(history.placeLabel('HS', 99), '')
})

test('a place is labelled as it was in a given round, where the squads may differ', () => {
    const history = seasonHistory([
        round('10', 'Runde 1', false, {HS: [place(1, 1, '1. HS', [anders]), place(2, 2, '1. HS', [bo])]}),
        round('11', 'Runde 2', true, {HS: [place(1, 1, '1. HS', [anders]), place(2, 1, '2. HS', [bo])]}),
    ])

    assert.equal(history.placeLabel('HS', 2), 'Hold 1 · 2. HS')
    assert.equal(history.placeLabel('HS', 2, 0), 'Hold 2 · 1. HS')
})

test('the placements of a round list every category with its place and partners', () => {
    const history = seasonHistory(rounds)

    assert.deepEqual(history.placementsOf('1', 0), [
        {category: 'HS', place: 1, label: 'Hold 1 · 1. HS', partners: []},
        {category: 'HD', place: 1, label: 'Hold 1 · 1. HD', partners: ['Bo']},
    ])
    assert.deepEqual(history.placementsOf('1', 2), [])
})

test('the chart has the player and everyone within 2 places of them in any round', () => {
    const chart = seasonHistory(rounds).chart('1', 'HS')

    assert.deepEqual(chart.lines.map(line => [line.name, line.kind]), [
        ['Bo', 'other'],
        ['Carl', 'youth'],
        ['Anders', 'subject'],
    ])
    assert.deepEqual(chart.places.map(p => p.place), [1, 2, 3])
    assert.deepEqual(chart.places[2], {place: 3, label: 'Hold 2 · 1. HS'})
})

test('a line breaks where the player was not placed', () => {
    const history = seasonHistory([
        rounds[0],
        round('11', 'Runde 2', false, {HS: [place(1, 1, '1. HS', [bo])]}),
        round('12', 'Runde 3', true, {HS: [place(1, 1, '1. HS', [anders]), place(2, 1, '2. HS', [bo])]}),
    ])

    const anderLine = history.chart('1', 'HS').lines.find(line => line.name === 'Anders')
    assert.deepEqual(anderLine.segments, [[{round: 0, place: 1}], [{round: 2, place: 1}]])
})

test('partners ending on the same point share one label', () => {
    const chart = seasonHistory(rounds).chart('3', 'HD')

    assert.deepEqual(chart.labels.map(label => [label.text, label.round, label.place]), [
        ['Carl / Bo', 2, 1],
    ])
})

test('the season has earlier rounds only when more than the current round is returned', () => {
    assert.equal(seasonHistory(rounds).hasEarlierRounds, true)
    assert.equal(seasonHistory([rounds[2]]).hasEarlierRounds, false)
    assert.equal(seasonHistory([]).hasEarlierRounds, false)
})

test('a line that breaks is bridged across the rounds the player was not placed', () => {
    const history = seasonHistory([
        rounds[0],
        round('11', 'Runde 2', false, {HS: [place(1, 1, '1. HS', [bo])]}),
        round('12', 'Runde 3', true, {HS: [place(1, 1, '1. HS', [bo]), place(2, 1, '2. HS', [anders])]}),
    ])

    const lines = history.chart('1', 'HS').lines
    assert.deepEqual(lines.find(line => line.name === 'Anders').gaps, [[{round: 0, place: 1}, {round: 2, place: 2}]])
    assert.deepEqual(lines.find(line => line.name === 'Bo').gaps, [])
})

test('the pair on a place names the player first, then their partners', () => {
    const history = seasonHistory(rounds)

    assert.deepEqual(history.pairOf('2', 'HD', 0), ['Bo', 'Anders'])
    assert.deepEqual(history.pairOf('1', 'HS', 0), ['Anders'])
    assert.deepEqual(history.pairOf('1', 'HS', 2), [])
})

test('a label also names a partner who has no line in the chart', () => {
    // Dan is never within 2 places of Anders, but ends on the same place as Carl, who is.
    const history = seasonHistory([
        round('10', 'Runde 1', false, {HD: [place(1, 1, '1. HD', [anders, bo]), place(2, 1, '2. HD', [carl, erik])]}),
        round('11', 'Runde 2', true, {HD: [place(1, 1, '1. HD', [anders, bo]), place(4, 2, '2. HD', [carl, dan])]}),
    ])

    assert.deepEqual(history.chart('1', 'HD').labels.map(label => label.text), ['Anders / Bo', 'Erik', 'Carl / Dan'])
})

test('each name in a label has its own kind, so a youth partner does not colour the whole pair', () => {
    const chart = seasonHistory(rounds).chart('2', 'HD')

    assert.deepEqual(chart.labels[0].names, [
        {name: 'Bo', kind: 'subject'},
        {name: 'Carl', kind: 'youth'},
    ])
})
