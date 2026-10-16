// Reserve Players (§43) in the TeamRound editor: on a Squad, X from a lower Squad of the same lineup plays "for" Y on
// this Squad. A Squad's records are squad.reserves: [{id, reserve: {refId, name, gender, vintage}, replaced: {...}}].
// Framework-free, so node:test can load it.

function playersOf(squad) {
    return squad.categories.flatMap(category => category.players)
}

function standsOn(squad, refId) {
    return playersOf(squad).some(player => player.refId === refId)
}

// Index of the first Squad whose category slots hold refId, or -1.
export function placedSquadIndex(squads, refId) {
    return squads.findIndex(squad => standsOn(squad, refId))
}

function reserveRefIds(squads) {
    return new Set(squads.flatMap(squad => (squad.reserves ?? []).map(record => record.reserve.refId)))
}

// "Reserve fra et lavere hold": Members placed on a lower Squad who aren't a reserve anywhere in the lineup yet, each
// with the first lower Squad and category they stand on.
export function reserveCandidates(squads, squadIndex) {
    const taken = reserveRefIds(squads)
    const seen = new Set()
    const candidates = []
    squads.forEach((squad, index) => {
        if (index <= squadIndex) return
        squad.categories.forEach(category => category.players.forEach(player => {
            if (seen.has(player.refId) || taken.has(player.refId) || placedSquadIndex(squads, player.refId) <= squadIndex) return
            seen.add(player.refId)
            candidates.push({player, fromIndex: index, categoryName: category.name})
        }))
    })
    return candidates
}

// "Erstatter": Members placed on this Squad who haven't been replaced yet, with the names of their categories.
export function replaceableMembers(squad) {
    const replaced = new Set((squad.reserves ?? []).map(record => record.replaced.refId))
    const members = new Map()
    squad.categories.forEach(category => category.players.forEach(player => {
        if (replaced.has(player.refId)) return
        if (!members.has(player.refId)) {
            members.set(player.refId, {refId: player.refId, name: player.name, gender: player.gender, categories: []})
        }
        members.get(player.refId).categories.push(category.name)
    }))
    return [...members.values()]
}

// The record whose reserve replaces refId on this Squad ("Erstattes af X"), or null.
export function replacedBy(squad, refId) {
    return (squad.reserves ?? []).find(record => record.replaced.refId === refId) ?? null
}

// Where refId is a reserve for someone on another Squad ("Reserve på Hold N for Y"): {index, record}, or null.
export function reserveOnHigherSquad(squads, refId) {
    for (const [index, squad] of squads.entries()) {
        const record = (squad.reserves ?? []).find(candidate => candidate.reserve.refId === refId)
        if (record) return {index, record}
    }
    return null
}

// Where the reserve stands now ("Fra Hold M · <category>"): {index, categoryName}, or null when they aren't placed.
export function reserveOrigin(squads, record) {
    const index = placedSquadIndex(squads, record.reserve.refId)
    if (index === -1) return null
    const category = squads[index].categories.find(c => c.players.some(player => player.refId === record.reserve.refId))
    return {index, categoryName: category.name}
}

// Soft warnings on a record the lineup changed under. Such a record doesn't count in validation.
export function reserveWarnings(squads, squadIndex, record) {
    const warnings = []
    if (!standsOn(squads[squadIndex], record.replaced.refId)) {
        warnings.push(`${record.replaced.name} står ikke længere på Hold ${squadIndex + 1}`)
    }
    const index = placedSquadIndex(squads, record.reserve.refId)
    if (index === -1) {
        warnings.push(`${record.reserve.name} står ikke længere på et lavere hold`)
    } else if (index <= squadIndex) {
        warnings.push(`${record.reserve.name} står nu på Hold ${index + 1}, ikke et lavere hold`)
    }
    return warnings
}

// A Squad's records in the validation input (ValidateSquad.reserves).
export function validationReserves(squad) {
    return (squad.reserves ?? []).map(record => ({reserveRefId: record.reserve.refId, replacedRefId: record.replaced.refId}))
}

export function quarantineLabel(quarantine) {
    return `Karantæne · reserve i R${quarantine.round} på Hold ${quarantine.squadPosition}`
}

export function quarantineTooltip(quarantine) {
    return `Var reserve på Hold ${quarantine.squadPosition} for ${quarantine.replacedName} i ${quarantine.teamRoundName} (runde ${quarantine.round}). `
        + 'En spiller, der har været reserve, må ikke spille på noget hold i næste spillerunde (§44).'
}

// The §43 findings of validateReserves as messages: strength increases are errors, better reserves are warnings.
export function reserveFindingMessages(findings) {
    const errors = []
    const warnings = []
    for (const finding of findings) {
        const hold = `Hold ${finding.squadIndex + 1}`
        const {reserve, replaced, categoryName} = finding
        if (finding.type === 'STRENGTH_INCREASE') {
            errors.push(`${hold}: Reserven ${reserve.name} har flere point end ${replaced.name} i ${categoryName} (${reserve.points} mod ${replaced.points}). Det er en styrkeforøgelse (§43 stk. 3).`)
        } else if (finding.type === 'BETTER_RESERVE_AVAILABLE') {
            warnings.push(`${hold}: Bedre reserve tilgængelig: ${finding.betterReserve.name} (${finding.betterReserve.points} point i ${categoryName}) i stedet for ${reserve.name} for ${replaced.name} (§43 stk. 2).`)
        }
    }
    return {errors, warnings}
}

// Season History: the note on a player's row for a round they were a reserve in, or null.
export function seasonHistoryReserveNote(round, refId) {
    const reserve = (round.reserves ?? []).find(candidate => candidate.refId === refId)
    return reserve ? `Reserve på Hold ${reserve.squadPosition} for ${reserve.replacedName}` : null
}
