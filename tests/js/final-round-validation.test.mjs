import {test} from 'node:test'
import assert from 'node:assert/strict'
import {createValidationState, resetValidation, runValidation, setValidationEnabled, showFinalRoundControl} from '../../resources/js/admin-v2/views/team-fight/final-round-validation.mjs'

function deferred() {
    let resolve, reject
    const promise = new Promise((yes, no) => { resolve = yes; reject = no })
    return {promise, resolve, reject}
}

test('the control is visible only in relevant rounds for a non-Paper Squad', () => {
    for (const tier of ['1. division', '2. division', '3. division', 'Danmarksserien', 'Badmintonligaen']) {
        for (const round of [1, 7, 8, 9, 10, 11, 12, 13]) {
            const squads = [{effectiveTier: tier, isPaperSquad: false}]
            const expected = round === 12 || (round === 11 && tier !== 'Badmintonligaen')
            assert.equal(showFinalRoundControl({round, squads}), expected, `${tier} round ${round}`)
            assert.equal(showFinalRoundControl({round, squads: [{...squads[0], isPaperSquad: true}]}), false)
        }
    }
    assert.equal(showFinalRoundControl({round: '11', squads: [{effectiveTier: '2. division - slutspil 2026'}]}), true)
    assert.equal(showFinalRoundControl({round: 11, squads: [{effectiveTier: 'Badmintonligaen'}, {effectiveTier: '2. division'}]}), true)
    assert.equal(showFinalRoundControl({round: 12, squads: [{effectiveTier: 'Serie 1'}]}), false)
    assert.equal(showFinalRoundControl({round: 11, squads: [{effectiveTier: null}]}), true)
    assert.equal(showFinalRoundControl({round: 10, squads: [{effectiveTier: null}]}), false)
    assert.equal(showFinalRoundControl({round: 12, squads: []}), false)
})

test('the client check starts disabled and skips requests until explicitly enabled', async () => {
    const state = createValidationState()
    assert.equal(state.enabled, false)
    assert.equal(state.status, 'disabled')
    assert.equal(state.findings, null)
    let requested = false
    await runValidation(state, async () => { requested = true; return [] })
    assert.equal(requested, false)
    assert.equal(state.status, 'disabled')
    setValidationEnabled(state, true)
    await runValidation(state, async () => [])
    assert.equal(state.status, 'complete')
})

test('disabling the client check clears findings and skips requests until enabled again', async () => {
    const state = createValidationState()
    setValidationEnabled(state, true)
    await runValidation(state, async () => [{status: 'REQUIREMENT_NOT_MET'}])
    setValidationEnabled(state, false)
    assert.equal(state.status, 'disabled')
    assert.equal(state.findings, null)
    let requested = false
    await runValidation(state, async () => { requested = true; return [] })
    resetValidation(state)
    assert.equal(requested, false)
    assert.equal(state.status, 'disabled')
    setValidationEnabled(state, true)
    assert.equal(state.status, 'pending')
    await runValidation(state, async () => [])
    assert.equal(state.status, 'complete')
    assert.equal(createValidationState().enabled, false)
})

test('the check stays disabled when a request finishes after switching it off', async () => {
    const state = createValidationState()
    setValidationEnabled(state, true)
    const old = deferred()
    const pending = runValidation(state, () => old.promise)
    setValidationEnabled(state, false)
    old.resolve([{status: 'REQUIREMENT_NOT_MET'}])
    await pending
    assert.equal(state.status, 'disabled')
    assert.equal(state.findings, null)
})

test('failed validation remains explicitly failed until a successful retry', async () => {
    const state = createValidationState()
    setValidationEnabled(state, true)
    await runValidation(state, async () => [])
    const failed = deferred()
    const pending = runValidation(state, () => failed.promise)
    assert.equal(state.status, 'pending')
    assert.equal(state.findings, null)
    failed.reject(new Error('offline'))
    await pending
    assert.equal(state.status, 'failed')
    assert.equal(state.findings, null)
    await runValidation(state, async () => [])
    assert.equal(state.status, 'complete')
    assert.deepEqual(state.findings, [])
})

test('an absent result is not a completed check', async () => {
    const state = createValidationState()
    setValidationEnabled(state, true)
    await runValidation(state, async () => null)
    assert.equal(state.status, 'failed')
    assert.equal(state.findings, null)
})

test('a check created enabled runs without being switched on', async () => {
    const state = createValidationState(true)
    assert.equal(state.enabled, true)
    assert.equal(state.status, 'pending')
    await runValidation(state, async () => [])
    assert.equal(state.status, 'complete')
})
