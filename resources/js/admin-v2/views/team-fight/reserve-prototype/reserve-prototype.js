// PROTOTYPE (issue #227): throwaway. Two ways to record a Reserve Player on a Squad,
// switchable on the TeamRound editor via ?variant=A or ?variant=B.
// State lives in memory only: a reload forgets every mark.
import {reactive} from 'vue'

export const VARIANTS = {
    A: 'Mærke på opstillet spiller',
    B: 'Reserve-sektion pr. hold'
}

export function prototypeVariant(route) {
    const variant = route?.query?.variant
    return VARIANTS[variant] ? variant : null
}

// A: squadId -> list of reserve entries {refId, name, gender, vintage, forRefId, forName}: a Member placed
//    on the Squad who plays "for" (instead of) another Member, like badmintonplayer.dk's "Reserver" section.
// B: squadId -> list of reserve entries {refId, name, gender, vintage, parallelAllocation, source}
//    where source is 'available' or the index of the lower Squad they also stand on.
export const reserveState = reactive({
    marksA: {},
    reservesB: {}
})

export function reservesA(squadId) {
    return reserveState.marksA[squadId] ?? []
}

export function reserveEntryA(squadId, refId) {
    return reservesA(squadId).find(r => r.refId === refId) ?? null
}

export function isMarkedA(squadId, refId) {
    return reserveEntryA(squadId, refId) !== null
}

export function addReserveA(squadId, entry) {
    reserveState.marksA[squadId] = [...reservesA(squadId).filter(r => r.refId !== entry.refId), entry]
}

export function removeReserveA(squadId, refId) {
    reserveState.marksA[squadId] = reservesA(squadId).filter(r => r.refId !== refId)
}

// The reserves shown on the public link and in the CSV, for either variant.
export function publicReserves(variant, squadId) {
    if (variant === 'A') return reservesA(squadId)
    if (variant === 'B') return reservesB(squadId)
    return []
}

export function reservesB(squadId) {
    return reserveState.reservesB[squadId] ?? []
}

export function addReserveB(squadId, entry) {
    removeReserveEverywhereB(entry.refId)
    reserveState.reservesB[squadId] = [...reservesB(squadId), entry]
}

export function removeReserveB(squadId, refId) {
    reserveState.reservesB[squadId] = reservesB(squadId).filter(r => r.refId !== refId)
}

function removeReserveEverywhereB(refId) {
    for (const squadId of Object.keys(reserveState.reservesB)) {
        removeReserveB(squadId, refId)
    }
}

// The Squad (and its index) a refId is a B-reserve on, or null.
export function reserveSquadOfB(squads, refId) {
    const index = squads.findIndex(squad => reservesB(squad.id).some(r => r.refId === refId))
    return index === -1 ? null : {index, squad: squads[index]}
}

// Index of the Squad whose category slots hold refId, or -1.
export function placedSquadIndex(squads, refId) {
    return squads.findIndex(squad => squad.categories.some(c => c.players.some(p => p.refId === refId)))
}

// B candidates for a Squad: Members placed on any other Squad of the TeamRound.
// Lower Squads are allowed (§43 stk. 1), higher Squads are listed but disabled.
export function candidatesB(squads, squadIndex) {
    const seen = new Set()
    const own = squads[squadIndex]
    const ownRefIds = new Set(own.categories.flatMap(c => c.players.map(p => p.refId)))
    const result = []
    squads.forEach((squad, index) => {
        if (index === squadIndex) return
        squad.categories.forEach(category => category.players.forEach(player => {
            if (seen.has(player.refId) || ownRefIds.has(player.refId)) return
            seen.add(player.refId)
            result.push({
                player,
                fromIndex: index,
                category: category.name,
                allowed: index > squadIndex
            })
        }))
    })
    return result.sort((a, b) => Number(b.allowed) - Number(a.allowed) || a.fromIndex - b.fromIndex)
}

// Text for the current round's row in Season History.
export function seasonHistoryNote(variant, squads, refId) {
    if (variant === 'A') {
        const index = squads.findIndex(squad => isMarkedA(squad.id, refId))
        return index === -1 ? null : `Reserve på Hold ${index + 1} for ${reserveEntryA(squads[index].id, refId).forName} (kategoripladsen tæller som normalt)`
    }
    // B: Season History is unchanged; reserves don't appear in it.
    return null
}

// The lines the CSV export gets after each Squad's categories, per Squad ("Hold N").
// A names who the reserve played for: "Nicoline Tang for Amanda Aarrebo Petersen".
export function csvReserveLines(variant, squads, includeCategories) {
    return squads.map((squad, index) => ({
        hold: `Hold ${index + 1}`,
        lines: publicReserves(variant, squad.id).map((reserve, i) => {
            const text = reserve.forName ? `${reserve.name} for ${reserve.forName}` : reserve.name
            return includeCategories
                ? `${i === 0 ? '"Reserve"' : ''},"${text}"`
                : `"${text} (reserve)"`
        })
    })).filter(squad => squad.lines.length)
}
