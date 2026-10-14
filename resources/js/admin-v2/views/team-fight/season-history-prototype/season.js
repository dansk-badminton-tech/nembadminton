// PROTOTYPE (issue #254): throwaway, do not merge to master.
// Simulates a Season from the lineup on screen: the current lineup is "Denne runde", and
// earlier rounds are made by shuffling the club-wide order per gender backwards in time.
// Youth Players climb over the season, so others get passed. Partners follow the slots.
import {isYoungPlayer} from '../../../helpers'

const EARLIER_ROUNDS = 6
const SINGLES = ['HS', 'DS']
const GENDER_WORD = {MEN: 'herrerne', WOMEN: 'damerne'}

export const EMPTY_TEXT = 'Ingen tidligere holdrunder i sæsonen'
export const NOT_PLACED_TEXT = 'Ikke opstillet'

function seededRandom(text) {
    let a = 7
    for (const c of text) {
        a = (Math.imul(a, 31) + c.charCodeAt(0)) | 0
    }
    return function () {
        a = (a + 0x6D2B79F5) | 0
        let t = Math.imul(a ^ (a >>> 15), 1 | a)
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296
    }
}

export function buildSeason(squads, {empty = false} = {}) {
    const players = new Map()
    const placementsNow = new Map()
    const order = {MEN: [], WOMEN: []}

    squads.forEach((squad, squadIndex) => {
        const teamName = squad.team?.name || squad.name || `Hold ${squadIndex + 1}`
        const tier = squad.effectiveTier || null
        const categories = [...squad.categories].sort((a, b) =>
            (SINGLES.includes(b.category) ? 1 : 0) - (SINGLES.includes(a.category) ? 1 : 0))
        for (const category of categories) {
            for (const player of category.players) {
                if (!players.has(player.refId)) {
                    players.set(player.refId, {
                        refId: player.refId,
                        name: player.name,
                        gender: player.gender,
                        vintage: player.vintage,
                        youth: isYoungPlayer(player),
                        simulatedYouth: false,
                    })
                    order[player.gender]?.push(player.refId)
                    placementsNow.set(player.refId, [])
                }
                const partner = category.players.find(p => p.refId !== player.refId)
                placementsNow.get(player.refId).push({
                    teamName, tier, category: category.category, label: category.name,
                    partnerRefId: partner ? partner.refId : null,
                })
            }
        }
    })

    // The seed may have no Youth Player in the lineup: pretend one per gender so the case shows.
    for (const gender of Object.keys(order)) {
        const list = order[gender]
        if (list.length >= 4 && !list.some(refId => players.get(refId).youth)) {
            const p = players.get(list[list.length - 3])
            p.youth = true
            p.simulatedYouth = true
            p.vintage = 'U17'
        }
    }

    const rounds = []
    if (!empty) {
        for (let i = 1; i <= EARLIER_ROUNDS; i++) {
            const date = new Date(Date.UTC(2026, 7, 9 + (i - 1) * 9))
            rounds.push({key: 'r' + i, name: `${i}. runde`, gameDate: date.toISOString().slice(0, 10), isCurrent: false})
        }
    }
    rounds.push({key: 'now', name: 'Denne runde', gameDate: null, isCurrent: true})
    const R = rounds.length

    const orders = {}
    const rankIndex = {}
    for (const gender of Object.keys(order)) {
        const random = seededRandom(gender + order[gender].join())
        const full = new Array(R)
        full[R - 1] = [...order[gender]]
        for (let r = R - 2; r >= 0; r--) {
            const list = [...full[r + 1]]
            for (let i = 0; i < list.length - 1; i++) {
                const chance = players.get(list[i]).youth ? 0.55 : 0.1
                if (random() < chance) {
                    [list[i], list[i + 1]] = [list[i + 1], list[i]]
                    i++
                }
            }
            full[r] = list
        }
        orders[gender] = full.map((list, r) => r === R - 1 ? list : list.filter(() => random() >= 0.08))
        rankIndex[gender] = orders[gender].map(list => new Map(list.map((refId, i) => [refId, i])))
    }

    function rankAt(refId, r) {
        const gender = players.get(refId).gender
        const i = rankIndex[gender]?.[r].get(refId)
        return i === undefined ? null : i + 1
    }

    function placementsAt(refId, r) {
        const gender = players.get(refId).gender
        const slot = rankIndex[gender][r].get(refId)
        if (slot === undefined) {
            return []
        }
        const owner = order[gender][slot]
        return placementsNow.get(owner).map(p => {
            let partnerName = null
            if (p.partnerRefId) {
                const partnerGender = players.get(p.partnerRefId).gender
                const partnerSlot = order[partnerGender].indexOf(p.partnerRefId)
                const partnerThen = orders[partnerGender][r][partnerSlot]
                partnerName = partnerThen ? players.get(partnerThen).name : null
            }
            return {teamName: p.teamName, tier: p.tier, category: p.category, label: p.label, partnerName}
        })
    }

    // Who moved past whom between round r-1 and r (same gender, placed in both).
    function movesAt(refId, r) {
        const passed = []
        const passedBy = []
        if (r === 0) {
            return {passed, passedBy}
        }
        const gender = players.get(refId).gender
        const mineBefore = rankAt(refId, r - 1)
        const mineNow = rankAt(refId, r)
        if (mineBefore === null || mineNow === null) {
            return {passed, passedBy}
        }
        for (const other of order[gender]) {
            if (other === refId) continue
            const before = rankAt(other, r - 1)
            const now = rankAt(other, r)
            if (before === null || now === null) continue
            if (before > mineBefore && now < mineNow) passedBy.push(players.get(other).name)
            if (before < mineBefore && now > mineNow) passed.push(players.get(other).name)
        }
        return {passed, passedBy}
    }

    function history(refId) {
        let previousRank = null
        return rounds.map((round, r) => {
            const rank = rankAt(refId, r)
            const delta = rank !== null && previousRank !== null ? previousRank - rank : null
            if (rank !== null) previousRank = rank
            return {round, r, placed: rank !== null, rank, delta, placements: placementsAt(refId, r), ...movesAt(refId, r)}
        })
    }

    function neighbours(refId, r) {
        const gender = players.get(refId).gender
        const i = rankIndex[gender][r].get(refId)
        if (i === undefined) return {above: null, below: null}
        const list = orders[gender][r]
        const at = j => list[j] ? {name: players.get(list[j]).name, rank: j + 1} : null
        return {above: at(i - 1), below: at(i + 1)}
    }

    return {
        rounds, players, order, orders, history, neighbours, rankAt,
        hasEarlier: R > 1,
        genderWord: refId => GENDER_WORD[players.get(refId)?.gender] || 'spillerne',
    }
}

export function formatDate(gameDate) {
    if (!gameDate) return 'nu'
    return new Date(gameDate).toLocaleDateString('da-DK', {day: 'numeric', month: 'short'})
}

export function formatPlacement(p) {
    return [p.teamName, p.tier, p.label].filter(Boolean).join(' · ')
}

export function deltaText(delta) {
    if (!delta) return ''
    return delta > 0 ? `▲ ${delta}` : `▼ ${-delta}`
}
