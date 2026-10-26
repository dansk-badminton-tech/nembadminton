import {test} from 'node:test'
import assert from 'node:assert/strict'
import {hasOwnRanking, pickingPointsLookup} from '../../../resources/js/admin-v2/views/team-fight/picking-points.js'
import {wrapInTeamAndSquads} from '../../../resources/js/admin-v2/views/team-fight/helper.js'

const hs = (points, version) => [{category: 'HS', points, position: 1, vintage: 'SEN', version}]
const player = (refId, points) => ({id: refId, refId, name: refId, gender: 'MEN', points})
const squad = (id, version, ...players) => ({
    id,
    version,
    playerLimit: 10,
    categories: players.map((p, index) => ({id: `${id}-${index}`, category: 'HS', name: `${index + 1}. HS`, players: [p]}))
})

test('a Squad has its own ranking when its version differs from the TeamRound\'s', () => {
    assert.equal(hasOwnRanking({version: '2026-03-01'}, '2026-02-01'), true)
    assert.equal(hasOwnRanking({version: '2026-02-01'}, '2026-02-01'), false)
    assert.equal(hasOwnRanking({version: null}, '2026-02-01'), false)
})

test('the lookup gives the points on the shared ranking only for the ranked Squads', () => {
    const lookup = pickingPointsLookup([{key: 's2', players: [{refId: 'bo', points: hs(1940, '2026-02-01')}]}])

    assert.deepEqual(lookup('s2', 'bo'), hs(1940, '2026-02-01'))
    assert.deepEqual(lookup('s2', 'carl'), [], 'a player without points on the shared ranking has none')
    assert.equal(lookup('s1', 'anders'), undefined, 'a Squad without its own ranking compares its own points')
})

test('wrapInTeamAndSquads sends pickingPoints only for players the lookup knows', () => {
    const squads = [
        squad('s1', null, player('anders', hs(2000, '2026-02-01'))),
        squad('s2', '2026-03-01', player('bo', hs(2060, '2026-03-01')))
    ]
    const lookup = pickingPointsLookup([{key: 's2', players: [{refId: 'bo', points: hs(1940, '2026-02-01')}]}])

    const input = wrapInTeamAndSquads(squads, (squad, index, player) => lookup(squad.id, player.refId))
    const [anders] = input[0].squad.categories[0].players
    const [bo] = input[1].squad.categories[0].players

    assert.equal('pickingPoints' in anders, false)
    assert.deepEqual(bo.points, [{category: 'HS', points: 2060, position: 1, vintage: 'SEN'}])
    assert.deepEqual(bo.pickingPoints, [{category: 'HS', points: 1940, position: 1, vintage: 'SEN'}])
})

test('wrapInTeamAndSquads without a lookup sends the input as before', () => {
    const input = wrapInTeamAndSquads([squad('s1', null, player('anders', hs(2000, '2026-02-01')))])

    assert.equal('pickingPoints' in input[0].squad.categories[0].players[0], false)
})
