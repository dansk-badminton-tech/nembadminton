import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    buildValidationErrors,
    formatBelowPlayers,
    invalidLevelTip,
} from '../../../resources/js/admin-v2/views/team-fight/validation-status.js'

const repeatedComparisons = [
    {refId: 'lower-1', name: 'Lavere Spiller', category: 'MxD'},
    {refId: 'lower-1', name: 'Lavere Spiller', category: 'DD'},
    {refId: 'lower-2', name: 'Anden Spiller', category: 'DD'},
]

test('cross-squad details describe the player as playing too low and list each compared player once', () => {
    const errors = buildValidationErrors({
        incompleteTeam: false,
        basicSquads: [],
        invalidLevel: true,
        invalidLevelList: [{name: 'Fejlplaceret Spiller', belowPlayer: repeatedComparisons}],
        invalidCategory: false,
        invalidCategoryList: [],
    })

    assert.deepEqual(errors, [
        'Fejlplaceret Spiller spiller på et for lavt rangeret hold i forhold til Lavere Spiller, Anden Spiller.',
    ])
})

test('cross-squad status summary describes players as playing too low', () => {
    assert.equal(
        invalidLevelTip(true),
        'En eller flere spillere spiller på et for lavt rangeret hold (Bryder § 38. stk. 4).',
    )
})

test('per-player details render repeated cross-category comparisons once', () => {
    assert.deepEqual(
        formatBelowPlayers(repeatedComparisons, player => player.name),
        ['Lavere Spiller', 'Anden Spiller'],
    )
})

test('players with the same name but different ids remain distinct', () => {
    const players = [
        {refId: 'player-1', name: 'Samme Navn'},
        {refId: 'player-2', name: 'Samme Navn'},
    ]

    assert.deepEqual(formatBelowPlayers(players, player => player.name), ['Samme Navn', 'Samme Navn'])
})
