// Remembers the "Kontrollér dette tjek" switches (Hold fuldendt, Tidligere hold, Kvalifikation) in browser storage,
// per user and per Team Round, so the Official Lineup and every Scenario of the round share one choice.
// A switch that isn't stored, storage that is missing or throws (private window, cleared data), or a user that isn't
// known yet all mean on. Nothing here throws.

const SWITCHES = ['complete', 'finalRound', 'qualification']

function storageKey({userId, teamRoundId}) {
    if (userId === null || userId === undefined || !teamRoundId) return null
    return `check_switches:${userId}:${teamRoundId}`
}

// The browser's localStorage, or null where it is missing or reaching it throws.
export function browserStorage() {
    try {
        return globalThis.localStorage ?? null
    } catch {
        return null
    }
}

// storage: a Web Storage (localStorage) or null. scope: {userId, teamRoundId}.
// Returns {complete, finalRound, qualification}.
export function readCheckSwitches(storage, scope) {
    let stored = {}
    try {
        const key = storageKey(scope)
        const parsed = key && storage ? JSON.parse(storage.getItem(key)) : null
        if (parsed && typeof parsed === 'object') stored = parsed
    } catch {
        // Falls back to on.
    }
    return Object.fromEntries(SWITCHES.map((key) => [key, stored[key] !== false]))
}

export function writeCheckSwitches(storage, scope, switches) {
    const key = storageKey(scope)
    if (!key || !storage) return
    const stored = Object.fromEntries(SWITCHES.map((name) => [name, switches[name] !== false]))
    try {
        storage.setItem(key, JSON.stringify(stored))
    } catch {
        // Not remembered: the next visit starts on.
    }
}
