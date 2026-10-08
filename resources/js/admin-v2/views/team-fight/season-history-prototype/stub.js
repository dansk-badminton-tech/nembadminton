// PROTOTYPE (issue #254): throwaway, do not merge to master.
// Fake Season History until SquadMember.seasonHistory (#253) exists.
// Same shape as the planned GraphQL type: newest first, up to 3 entries.

const ROUNDS = [
    {teamRoundId: 'p5', name: '5. runde', round: 5, gameDate: '2026-09-27'},
    {teamRoundId: 'p4', name: '4. runde', round: 4, gameDate: '2026-09-13'},
    {teamRoundId: 'p3', name: '3. runde', round: 3, gameDate: '2026-08-30'},
]

const TEAMS = [
    {teamName: 'Højbjerg 1', tier: 'Badmintonligaen'},
    {teamName: 'Højbjerg 2', tier: '2. division'},
    {teamName: 'Højbjerg 3', tier: 'Danmarksserien'},
    {teamName: null, tier: 'Serie 1'},
]

function hash(text) {
    let h = 7
    for (const c of String(text)) {
        h = (h * 31 + c.charCodeAt(0)) % 100003
    }
    return h
}

export function seasonHistoryFor(player, {empty = false} = {}) {
    if (empty) {
        return []
    }
    const singles = player.gender === 'WOMEN' ? 'DS' : 'HS'
    const doubles = player.gender === 'WOMEN' ? 'DD' : 'HD'
    return ROUNDS.map(round => {
        const h = hash(player.refId + round.teamRoundId)
        if (h % 4 === 0) {
            return {...round, placed: false, placements: []}
        }
        const team = TEAMS[h % TEAMS.length]
        const placements = [{...team, category: singles, position: (h % 4) + 1}]
        if (h % 3 === 0) {
            placements.push({...team, category: doubles, position: (h % 2) + 1})
        } else if (h % 5 === 0) {
            placements.push({...team, category: 'MD', position: (h % 3) + 1})
        }
        return {...round, placed: true, placements}
    })
}

export function formatDate(gameDate) {
    return new Date(gameDate).toLocaleDateString('da-DK', {day: 'numeric', month: 'short'})
}

export function formatPlacement(p) {
    return [p.teamName, p.tier, `${p.position}. ${p.category}`].filter(Boolean).join(' · ')
}

export function shortPlacement(p) {
    return `${p.position}. ${p.category}`
}

export const EMPTY_TEXT = 'Ingen tidligere holdrunder i sæsonen'
export const NOT_PLACED_TEXT = 'Ikke opstillet'
export const TITLE = 'Opstillet tidligere i sæsonen'
