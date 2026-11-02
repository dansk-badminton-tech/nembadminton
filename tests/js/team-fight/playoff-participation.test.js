import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    playoffParticipationGuidance,
    playoffParticipationMessage
} from '../../../resources/js/admin-v2/views/team-fight/playoff-participation.js'

function player(fields) {
    return {
        squadIndex: 0, refId: '1', name: 'Anders', status: 'REQUIREMENT_NOT_MET', incompleteReasons: [],
        matchCount: 3, requiredMatchCount: 4, playedByDeadline: true, uncountedRounds: [], ...fields
    }
}

test('an unmet requirement names the missing matches, the missed deadline or both', () => {
    assert.equal(playoffParticipationMessage(player({})),
        'Hold 1: Anders har 3 af 4 kampe i sæsonen; 20. oktober-kravet er opfyldt (§32 stk. 1).')
    assert.equal(playoffParticipationMessage(player({playedByDeadline: null})),
        'Hold 1: Anders har 3 af 4 kampe i sæsonen; 20. oktober-kravet kan ikke afgøres (§32 stk. 1).')
    assert.equal(playoffParticipationMessage(player({squadIndex: 1, matchCount: 1, requiredMatchCount: 3, playedByDeadline: false})),
        'Hold 2: Anders har 1 af 3 kampe i sæsonen og har ikke spillet for klubben senest 20. oktober (§32 stk. 1).')
    assert.equal(playoffParticipationMessage(player({matchCount: 5, playedByDeadline: false})),
        'Hold 1: Anders har 5 af 4 kampe, men har ikke spillet for klubben senest 20. oktober (§32 stk. 1).')
})

test('an unverifiable requirement says so, with the count when the tier is known', () => {
    assert.equal(playoffParticipationMessage(player({status: 'CANNOT_VERIFY', matchCount: 2, requiredMatchCount: 3})),
        'Hold 1: Anders har 2 af 3 kampe; 20. oktober-kravet er opfyldt – slutspilskravet kan ikke verificeres fuldt ud (§32 stk. 1).')
    assert.equal(playoffParticipationMessage(player({status: 'CANNOT_VERIFY', matchCount: 3, requiredMatchCount: 3, playedByDeadline: null})),
        'Hold 1: Anders har 3 af 3 kampe – slutspilskravet kan ikke verificeres fuldt ud (§32 stk. 1).')
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
