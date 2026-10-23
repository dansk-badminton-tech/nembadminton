// The state may be reactive; always mutate the caller's state rather than a captured raw object.
export function createValidationState() {
    return {revision: 0, status: 'pending', findings: null}
}

export function invalidateValidation(state) {
    state.revision++
    state.status = 'pending'
    state.findings = null
}

export async function runValidation(state, request) {
    invalidateValidation(state)
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
