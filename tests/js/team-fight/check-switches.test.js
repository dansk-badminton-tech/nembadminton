import {test} from 'node:test'
import assert from 'node:assert/strict'
import {
    readCheckSwitches,
    writeCheckSwitches,
} from '../../../resources/js/admin-v2/views/team-fight/checks/check-switches.mjs'

// A browser storage held in memory.
function memoryStorage() {
    const items = new Map()
    return {
        getItem: (key) => items.has(key) ? items.get(key) : null,
        setItem: (key, value) => items.set(key, String(value)),
    }
}

const ALL_ON = {complete: true, finalRound: true, qualification: true}

test('every switch is on when nothing is stored', () => {
    assert.deepEqual(readCheckSwitches(memoryStorage(), {userId: 7, teamRoundId: '12'}), ALL_ON)
})

test('a switched-off Check stays off for the same user and Team Round', () => {
    const storage = memoryStorage()
    writeCheckSwitches(storage, {userId: 7, teamRoundId: '12'}, {...ALL_ON, finalRound: false})
    assert.deepEqual(readCheckSwitches(storage, {userId: 7, teamRoundId: '12'}), {...ALL_ON, finalRound: false})
})

test('the choice belongs to one user and one Team Round', () => {
    const storage = memoryStorage()
    writeCheckSwitches(storage, {userId: 7, teamRoundId: '12'}, {complete: false, finalRound: false, qualification: false})
    assert.deepEqual(readCheckSwitches(storage, {userId: 7, teamRoundId: '13'}), ALL_ON)
    assert.deepEqual(readCheckSwitches(storage, {userId: 8, teamRoundId: '12'}), ALL_ON)
})

test('every switch falls back to on when storage throws, and saving is skipped silently', () => {
    const throwing = {
        getItem: () => { throw new Error('SecurityError') },
        setItem: () => { throw new Error('QuotaExceededError') },
    }
    assert.doesNotThrow(() => writeCheckSwitches(throwing, {userId: 7, teamRoundId: '12'}, {...ALL_ON, complete: false}))
    assert.deepEqual(readCheckSwitches(throwing, {userId: 7, teamRoundId: '12'}), ALL_ON)
})

test('every switch falls back to on without storage or with an unreadable stored value', () => {
    assert.deepEqual(readCheckSwitches(null, {userId: 7, teamRoundId: '12'}), ALL_ON)
    assert.doesNotThrow(() => writeCheckSwitches(null, {userId: 7, teamRoundId: '12'}, ALL_ON))

    for (const junk of ['not json', 'null', '"off"']) {
        const junkStorage = {getItem: () => junk, setItem: () => {}}
        assert.deepEqual(readCheckSwitches(junkStorage, {userId: 7, teamRoundId: '12'}), ALL_ON)
    }
})

test('nothing is remembered while the user is unknown', () => {
    const storage = memoryStorage()
    writeCheckSwitches(storage, {userId: null, teamRoundId: '12'}, {...ALL_ON, complete: false})
    assert.deepEqual(readCheckSwitches(storage, {userId: null, teamRoundId: '12'}), ALL_ON)
})
