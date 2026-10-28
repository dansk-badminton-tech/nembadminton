export function showFinalRoundControl(teamRound) {
    const round = Number(teamRound?.round)
    if (round !== 11 && round !== 12) return false
    return (teamRound?.squads ?? []).some(squad => {
        if (squad.isPaperSquad) return false
        const tier = (squad.effectiveTier ?? '').trim()
        // Unknown tier context stays visible for the server's uncertainty warning.
        if (!tier) return true
        if (/^badmintonligaen\b/i.test(tier)) return round === 12
        return /^(?:[1-3]\.\s*division|danmarksserien)\b/i.test(tier)
    })
}

// The state may be reactive; always mutate the caller's state rather than a captured raw object.
// A check that needs no setup from the coach is created enabled; the others start disabled.
export function createValidationState(enabled = false) {
    return {enabled, status: enabled ? 'pending' : 'disabled', findings: null}
}

export function resetValidation(state) {
    state.status = state.enabled ? 'pending' : 'disabled'
    state.findings = null
}

export function setValidationEnabled(state, enabled) {
    state.enabled = enabled
    resetValidation(state)
}

export async function runValidation(state, request) {
    resetValidation(state)
    if (!state.enabled) return
    try {
        const findings = await request()
        if (!Array.isArray(findings)) throw new Error('Missing validation result')
        if (state.enabled) {
            state.findings = findings
            state.status = 'complete'
        }
    } catch {
        if (state.enabled) state.status = 'failed'
    }
}
