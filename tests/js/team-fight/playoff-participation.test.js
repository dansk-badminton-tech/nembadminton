import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    playoffParticipationGuidance,
    playoffParticipationMessage,
    showPlayoffParticipationControl
} from '../../../resources/js/admin-v2/views/team-fight/playoff-participation.js'

function player(fields) {
    return {
        squadIndex: 0, refId: '1', name: 'Anders', status: 'REQUIREMENT_NOT_MET', incompleteReasons: [],
        matchCount: 3, requiredMatchCount: 4, playedByDeadline: true, uncountedRounds: [], ...fields
    }
}

test('the control shows in the playoffs and qualification of Liga and 1. division', () => {
    const shows = (effectiveTier, round, isPaperSquad = false) =>
        showPlayoffParticipationControl({round, squads: [{effectiveTier, isPaperSquad}]})
    assert.equal(shows('Badmintonligaen', 9), false)
    assert.equal(shows('Badmintonligaen', 10), true)
    assert.equal(shows('Badmintonligaen', 12), true)
    assert.equal(shows('1. division', 7), false)
    assert.equal(shows('1. division', 8), true)
    assert.equal(shows('1. division - slutspil 2026', '12'), true)
    assert.equal(shows('2. division', 12), false)
    assert.equal(shows('Serie 1', 12), false)
    assert.equal(shows('1. division', 8, true), false)
    assert.equal(shows(null, 8), true)
    assert.equal(shows(null, 7), false)
    assert.equal(shows('Badmintonligaen', null), false)
    assert.equal(showPlayoffParticipationControl({round: 10, squads: [{effectiveTier: '2. division'}, {effectiveTier: 'Badmintonligaen'}]}), true)
})

test('an unmet requirement names the missing matches, the missed deadline or both', () => {
    assert.equal(playoffParticipationMessage(player({})),
        'Hold 1: Anders har 3 af 4 kampe i sæsonen (§32 stk. 1).')
    assert.equal(playoffParticipationMessage(player({squadIndex: 1, matchCount: 1, requiredMatchCount: 3, playedByDeadline: false})),
        'Hold 2: Anders har 1 af 3 kampe i sæsonen og har ikke spillet for klubben senest 20. oktober (§32 stk. 1).')
    assert.equal(playoffParticipationMessage(player({matchCount: 5, playedByDeadline: false})),
        'Hold 1: Anders har 5 af 4 kampe, men har ikke spillet for klubben senest 20. oktober (§32 stk. 1).')
})

test('an unverifiable requirement says so, with the count when the tier is known', () => {
    assert.equal(playoffParticipationMessage(player({status: 'CANNOT_VERIFY', matchCount: 2, requiredMatchCount: 3})),
        'Hold 1: Anders har 2 af 3 kampe – slutspilskravet kan ikke verificeres fuldt ud (§32 stk. 1).')
    assert.equal(playoffParticipationMessage(player({status: 'CANNOT_VERIFY', matchCount: 0, requiredMatchCount: 0})),
        'Hold 1: Anders – slutspilskravet kan ikke verificeres fuldt ud (§32 stk. 1).')
})

test('a met or not applicable requirement has no message', () => {
    assert.equal(playoffParticipationMessage(player({status: 'SATISFIED'})), null)
    assert.equal(playoffParticipationMessage(player({status: 'NOT_APPLICABLE'})), null)
})

test('each incomplete reason says what to fix', () => {
    const guidance = playoffParticipationGuidance(player({
        status: 'CANNOT_VERIFY',
        incompleteReasons: ['MISSING_ROUND', 'UNKNOWN_TIER', 'MISSING_SEASON', 'UNKNOWN_HISTORY_TIER', 'MISSING_HISTORY_DATE']
    }))
    assert.equal(guidance.length, 5)
    assert.ok(guidance.every(text => typeof text === 'string' && text.endsWith('.')))
    assert.deepEqual(playoffParticipationGuidance(player({})), [])
})
