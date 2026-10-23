import {test} from 'node:test'
import assert from 'node:assert/strict'
import {createValidationState, invalidateValidation, runValidation} from '../../resources/js/admin-v2/views/team-fight/final-round-validation.mjs'

function deferred() {
    let resolve, reject
    const promise = new Promise((yes, no) => { resolve = yes; reject = no })
    return {promise, resolve, reject}
}

test('switching Scenario invalidates an outstanding check before the new lineup has loaded', async () => {
    const state = createValidationState()
    const old = deferred()
    const pending = runValidation(state, () => old.promise)
    invalidateValidation(state)
    old.resolve([{status: 'REQUIREMENT_NOT_MET', name: 'Old Scenario'}])
    await pending
    assert.equal(state.status, 'pending')
    assert.equal(state.findings, null)
    await runValidation(state, async () => [{status: 'SATISFIED', name: 'New Scenario'}])
    assert.equal(state.status, 'complete')
    assert.deepEqual(state.findings, [{status: 'SATISFIED', name: 'New Scenario'}])
})

test('failed validation remains explicitly failed until a successful retry', async () => {
    const state = createValidationState()
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

test('older successes and failures cannot overwrite the latest completed lineup check', async () => {
    for (const outcome of ['success', 'failure']) {
        const state = createValidationState()
        const old = deferred()
        const pending = runValidation(state, () => old.promise)
        await runValidation(state, async () => [{status: 'NOT_APPLICABLE'}])
        if (outcome === 'success') old.resolve([{status: 'REQUIREMENT_NOT_MET'}])
        else old.reject(new Error('old failure'))
        await pending
        assert.equal(state.status, 'complete')
        assert.deepEqual(state.findings, [{status: 'NOT_APPLICABLE'}])
    }
})

test('an absent result is not a completed check', async () => {
    const state = createValidationState()
    await runValidation(state, async () => null)
    assert.equal(state.status, 'failed')
    assert.equal(state.findings, null)
})
