import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    isTooYoung,
    tooYoungMessage,
} from '../../../resources/js/admin-v2/views/team-fight/too-young.js'

const tooYoungPlayers = [{id: '1', refId: '120101-1111', name: 'Ung Spiller'}]

test('a player reported as too young is matched by refId', () => {
    assert.equal(isTooYoung(tooYoungPlayers, {refId: '120101-1111'}), true)
    assert.equal(isTooYoung(tooYoungPlayers, {refId: '950101-3333'}), false)
})

test('nothing is too young when no validation has run', () => {
    assert.equal(isTooYoung(undefined, {refId: '120101-1111'}), false)
})

test('the warning names 31.12. of the season start year and §31 stk. 1', () => {
    assert.equal(
        tooYoungMessage(2026),
        'Spilleren er ikke fyldt 15 år senest 31.12.2026 og må ikke spille i seniorholdturneringen (§31 stk. 1)'
    )
})
