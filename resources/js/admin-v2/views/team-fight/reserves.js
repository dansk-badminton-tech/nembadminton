// Reserve Players (§43) in the TeamRound editor: on a Squad, X plays "for" Y on this Squad. X is an Available Member or
// stands on a lower Squad of the same lineup. Adding the record puts X in Y's slots, so the Squad shows the lineup as
// played; X keeps their slot on a lower Squad. A Squad's records are squad.reserves:
// [{id, reserve: {refId, name, gender, vintage}, replaced: {...}, replacedPoints: [{category, points, position, vintage}]}].
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

// Members placed on a lower Squad who aren't a reserve anywhere in the lineup yet, each with the first lower Squad and
// category they stand on.
function lowerSquadCandidates(squads, squadIndex) {
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

// The "Reserve" field: the Members on lower Squads, then the Available Members (fromIndex null) who aren't a reserve in
// the lineup yet. availableMembers come from the membersSearch query, which leaves out placed Members.
export function reserveCandidates(squads, squadIndex, availableMembers = []) {
    const taken = reserveRefIds(squads)
    const available = availableMembers
        .filter(member => !taken.has(member.refId) && placedSquadIndex(squads, member.refId) === -1)
        .map(member => ({player: member, fromIndex: null, categoryName: null}))
    return [...lowerSquadCandidates(squads, squadIndex), ...available]
}

// "Erstatter": Members placed on this Squad who aren't a reserve there, with the names of their categories.
export function replaceableMembers(squad) {
    const reserves = new Set((squad.reserves ?? []).map(record => record.reserve.refId))
    const members = new Map()
    squad.categories.forEach(category => category.players.forEach(player => {
        if (reserves.has(player.refId)) return
        if (!members.has(player.refId)) {
            members.set(player.refId, {refId: player.refId, name: player.name, gender: player.gender, categories: []})
        }
        members.get(player.refId).categories.push(category.name)
    }))
    return [...members.values()]
}

// The record whose reserve is refId on this Squad ("Reserve for Y"), or null.
export function reserveOn(squad, refId) {
    return (squad.reserves ?? []).find(record => record.reserve.refId === refId) ?? null
}

// Where refId is a reserve for someone on another Squad ("Reserve på Hold N for Y"): {index, record}, or null.
export function reserveOnHigherSquad(squads, refId) {
    for (const [index, squad] of squads.entries()) {
        const record = (squad.reserves ?? []).find(candidate => candidate.reserve.refId === refId)
        if (record) return {index, record}
    }
    return null
}

// The lower Squad the reserve also plays on ("Fra Hold M · <category>"): {index, categoryName}, or null for an Available
// Member.
export function reserveOrigin(squads, squadIndex, record) {
    const index = squads.findIndex((squad, i) => i > squadIndex && standsOn(squad, record.reserve.refId))
    if (index === -1) return null
    const category = squads[index].categories.find(c => c.players.some(player => player.refId === record.reserve.refId))
    return {index, categoryName: category.name}
}

// Soft warnings on a record the lineup changed under. Such a record doesn't count in validation.
export function reserveWarnings(squads, squadIndex, record) {
    const warnings = []
    if (!standsOn(squads[squadIndex], record.reserve.refId)) {
        warnings.push(`${record.reserve.name} står ikke på Hold ${squadIndex + 1}`)
    }
    if (standsOn(squads[squadIndex], record.replaced.refId)) {
        warnings.push(`${record.replaced.name} står stadig på Hold ${squadIndex + 1}`)
    }
    return warnings
}

// A Squad's records in the validation input (ValidateSquad.reserves), with the replaced player's points at the Squad's
// ranking version.
export function validationReserves(squad) {
    return (squad.reserves ?? []).map(record => ({
        reserveRefId: record.reserve.refId,
        replaced: {
            refId: record.replaced.refId,
            name: record.replaced.name,
            gender: record.replaced.gender,
            points: (record.replacedPoints ?? []).map(({category, points, position, vintage}) => ({category, points, position, vintage}))
        }
    }))
}

export function quarantineLabel(quarantine) {
    return `Karantæne · reserve i R${quarantine.round} på Hold ${quarantine.squadPosition}`
}

export function quarantineTooltip(quarantine) {
    return `Var reserve på Hold ${quarantine.squadPosition} for ${quarantine.replacedName} i ${quarantine.teamRoundName} (runde ${quarantine.round}). `
        + 'En spiller, der har været reserve, må ikke spille på noget hold i næste spillerunde (§44).'
}

// The §43 stk. 3 findings of validateReserves as warnings: the club judges whether the team is stronger than planned.
export function reserveFindingMessages(findings) {
    return findings.map(({squadIndex, reserve, replaced, categoryName}) =>
        `Hold ${squadIndex + 1}: Reserven ${reserve.name} har flere point end ${replaced.name} i ${categoryName} (${reserve.points} mod ${replaced.points}). Er holdet stærkere end planlagt (§43 stk. 3)?`)
}
