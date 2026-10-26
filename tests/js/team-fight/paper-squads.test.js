import {test} from 'node:test'
import assert from 'node:assert/strict'
import {existsSync} from 'node:fs'
import {
    hasPaperSquad,
    paperSquadMessages,
    showsTeamLinkGuide,
    TEAM_LINK_GUIDE
} from '../../../resources/js/admin-v2/views/team-fight/paper-squads.js'

const finding = (overrides) => ({
    squadIndex: 0,
    refId: '1',
    name: 'Bo',
    unplayedCategories: [],
    matchCount: 1,
    requiredMatchCount: 1,
    teamName: 'BC37 Amager 2',
    uncountedRounds: [],
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

const uncounted = (round, name = `Runde ${round}`) => ({teamRoundId: `tr-${name}`, name, round})

test('earlier rounds without the Team linked are named, so the coach can link it', () => {
    assert.deepEqual(paperSquadMessages({missingRound: false, players: [
        finding({name: 'Signe Markwall', matchCount: 0, uncountedRounds: [uncounted(1), uncounted(2), uncounted(3)]}),
        finding({name: 'Anders', matchCount: 0, uncountedRounds: [uncounted(2)]}),
    ]}), [
        'Hold 1 (papirhold): Signe Markwall har stået på samme eller et lavere hold 0 gange tidligere i sæsonen, men skal have stået der mindst 1 gang. 3 holdkampe (runde 1, 2 og 3) kunne ikke tælles med, fordi BC37 Amager 2 ikke er tilknyttet i de runder.',
        'Hold 1 (papirhold): Anders har stået på samme eller et lavere hold 0 gange tidligere i sæsonen, men skal have stået der mindst 1 gang. 1 holdkamp (runde 2) kunne ikke tælles med, fordi BC37 Amager 2 ikke er tilknyttet i den runde.',
    ])
})

test('a round without a round number is named by the TeamRound name', () => {
    assert.deepEqual(paperSquadMessages({missingRound: false, players: [
        finding({matchCount: 0, uncountedRounds: [uncounted(1), uncounted(null, 'Ekstra runde')]}),
        finding({matchCount: 0, uncountedRounds: [uncounted(null, 'Ekstra runde')]}),
    ]}).map(message => message.match(/\(([^()]*)\) kunne/)[1]), ['runde 1 og Ekstra runde', 'Ekstra runde'])
})

test('uncounted rounds are not named when the player has enough matches', () => {
    assert.deepEqual(paperSquadMessages({missingRound: false, players: [
        finding({unplayedCategories: ['HD'], uncountedRounds: [uncounted(1)]}),
    ]}), [
        'Hold 1 (papirhold): Bo har ikke spillet HD tidligere i sæsonen.',
    ])
})

test('earlier rounds without a round number are named with the findings, since they are not counted', () => {
    const withoutRound = [{id: 'a', name: 'Ekstra runde', gameDate: null}, {id: 'b', name: 'Omkamp', gameDate: null}]
    assert.deepEqual(paperSquadMessages({missingRound: false, players: [finding({matchCount: 0})], withoutRound}), [
        'Hold 1 (papirhold): Bo har stået på samme eller et lavere hold 0 gange tidligere i sæsonen, men skal have stået der mindst 1 gang.',
        'Ekstra runde og Omkamp mangler rundenummer, så de tælles ikke med i papirholdsreglen. Udfyld rundenummeret.',
    ])
    assert.deepEqual(paperSquadMessages({missingRound: false, players: [], withoutRound}), [])
})

test('the guide on linking the Team shows only when a player has uncounted rounds', () => {
    assert.equal(showsTeamLinkGuide({missingRound: false, players: [finding({matchCount: 0, uncountedRounds: [uncounted(1)]})]}), true)
    assert.equal(showsTeamLinkGuide({missingRound: false, players: [finding({unplayedCategories: ['HD'], uncountedRounds: [uncounted(1)]})]}), false)
    assert.equal(showsTeamLinkGuide({missingRound: false, players: [finding({matchCount: 0})]}), false)
    assert.equal(showsTeamLinkGuide(null), false)
    assert.ok(existsSync(`resources/help/guides/${TEAM_LINK_GUIDE}.md`))
})

test('the paper-squad status shows when a Squad in the lineup is a Paper Squad', () => {
    assert.equal(hasPaperSquad([{isPaperSquad: false}, {isPaperSquad: true}]), true)
    assert.equal(hasPaperSquad([{isPaperSquad: false}, {}]), false)
    assert.equal(hasPaperSquad(undefined), false)
})
