// The state may be reactive; always mutate the caller's state rather than a captured raw object.
export function createValidationState() {
    return {revision: 0, enabled: true, status: 'pending', findings: null}
}

export function invalidateValidation(state) {
    state.revision++
    state.status = state.enabled ? 'pending' : 'disabled'
    state.findings = null
}

export function setValidationEnabled(state, enabled) {
    state.enabled = enabled
    invalidateValidation(state)
}

export async function runValidation(state, request) {
    invalidateValidation(state)
    if (!state.enabled) return
    const revision = state.revision
    try {
        const findings = await request()
        if (!Array.isArray(findings)) throw new Error('Missing validation result')
        if (revision === state.revision) {
            state.findings = findings
            state.status = 'complete'
        }
    } catch {
        if (revision === state.revision) state.status = 'failed'
    }
}
