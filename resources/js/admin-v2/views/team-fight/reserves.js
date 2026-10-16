// Reserve Players (§43) in the TeamRound editor: on a Squad, X plays "for" Y on this Squad. X is an Available Member or
// stands on a lower Squad of the same lineup. A Squad's records are squad.reserves:
// [{id, reserve: {refId, name, gender, vintage}, replaced: {...}, reservePoints: [{category, points, position, vintage}]}].
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

// Where the reserve stands now ("Fra Hold M · <category>"): {index, categoryName}, or null for an Available Member.
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
    if (index !== -1 && index <= squadIndex) {
        warnings.push(`${record.reserve.name} står nu på Hold ${index + 1}, ikke et lavere hold`)
    }
    return warnings
}

// A Squad's records in the validation input (ValidateSquad.reserves), with the reserve's points at the Squad's ranking
// version.
export function validationReserves(squad) {
    return (squad.reserves ?? []).map(record => ({
        reserve: {
            refId: record.reserve.refId,
            name: record.reserve.name,
            gender: record.reserve.gender,
            points: (record.reservePoints ?? []).map(({category, points, position, vintage}) => ({category, points, position, vintage}))
        },
        replacedRefId: record.replaced.refId
    }))
}

export function quarantineLabel(quarantine) {
    return `Karantæne · reserve i R${quarantine.round} på Hold ${quarantine.squadPosition}`
}

export function quarantineTooltip(quarantine) {
    return `Var reserve på Hold ${quarantine.squadPosition} for ${quarantine.replacedName} i ${quarantine.teamRoundName} (runde ${quarantine.round}). `
        + 'En spiller, der har været reserve, må ikke spille på noget hold i næste spillerunde (§44).'
}

// The §43 stk. 3 findings of validateReserves as error messages.
export function reserveFindingMessages(findings) {
    return findings.map(({squadIndex, reserve, replaced, categoryName}) =>
        `Hold ${squadIndex + 1}: Reserven ${reserve.name} har flere point end ${replaced.name} i ${categoryName} (${reserve.points} mod ${replaced.points}). Det er en styrkeforøgelse (§43 stk. 3).`)
}
