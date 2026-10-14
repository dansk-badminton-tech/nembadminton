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
        const singles = list.filter(refId => placementsNow.get(refId).some(p => SINGLES.includes(p.category)))
        if (singles.length >= 4 && !list.some(refId => players.get(refId).youth)) {
            // A singles player in the middle, so there is room to climb past others.
            const p = players.get(singles[Math.floor(singles.length * 0.6)])
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

    const byCategory = buildCategoryModel(squads, players, R)

    return {
        rounds, players, order, orders, history, neighbours, rankAt, byCategory,
        hasEarlier: R > 1,
        genderWord: refId => GENDER_WORD[players.get(refId)?.gender] || 'spillerne',
    }
}

// Order per category across all teams: Hold 1 1. HS, 2. HS … then Hold 2 1. HS …
// Each category is a row of seats (singles 1 per slot, HD/DD 2 per slot, MD 1 man + 1 woman per slot).
// Earlier rounds move players between seats, so pairs change; partners share their slot's rank.
export const CATEGORIES = ['HS', 'DS', 'HD', 'DD', 'MD']
export const CATEGORY_LABEL = {HS: 'Herresingle', DS: 'Damesingle', HD: 'Herredouble', DD: 'Damedouble', MD: 'Mixeddouble'}

function buildCategoryModel(squads, players, R) {
    const slots = {}
    const lanes = {}
    for (const c of CATEGORIES) {
        slots[c] = []
        lanes[c] = {MEN: [], WOMEN: []}
    }
    squads.forEach((squad, squadIndex) => {
        const teamName = squad.team?.name || squad.name || `Hold ${squadIndex + 1}`
        const tier = squad.effectiveTier || null
        const categories = [...squad.categories].sort((a, b) => parseInt(a.name) - parseInt(b.name))
        for (const category of categories) {
            const c = category.category
            if (!slots[c]) continue
            slots[c].push({teamName, tier, label: category.name})
            const slot = slots[c].length - 1
            if (c === 'MD') {
                for (const gender of ['MEN', 'WOMEN']) {
                    lanes[c][gender][slot] = category.players.find(p => p.gender === gender)?.refId ?? null
                }
            } else {
                const gender = c === 'HS' || c === 'HD' ? 'MEN' : 'WOMEN'
                const seats = c === 'HD' || c === 'DD' ? 2 : 1
                for (let s = 0; s < seats; s++) {
                    lanes[c][gender][slot * seats + s] = category.players[s]?.refId ?? null
                }
            }
        }
    })

    const seatsPerSlot = c => (c === 'HD' || c === 'DD') ? 2 : 1
    const sim = {}
    for (const c of CATEGORIES) {
        sim[c] = {}
        for (const gender of ['MEN', 'WOMEN']) {
            const now = Array.from(lanes[c][gender], x => x ?? null)
            if (!now.some(Boolean)) continue
            const random = seededRandom(c + gender + now.join())
            const full = new Array(R)
            full[R - 1] = now
            for (let r = R - 2; r >= 0; r--) {
                const list = [...full[r + 1]]
                for (let i = 0; i < list.length - 1; i++) {
                    // Going back in time a Youth Player drops, so over the season they climb.
                    const youthHere = list[i] && players.get(list[i]).youth
                    const youthBelow = list[i + 1] && players.get(list[i + 1]).youth
                    const chance = youthHere ? 0.55 : (youthBelow ? 0 : 0.12)
                    if (random() < chance) {
                        [list[i], list[i + 1]] = [list[i + 1], list[i]]
                        i++
                    }
                }
                full[r] = list
            }
            const rounds = full.map((list, r) => {
                if (r === R - 1) return list
                const kept = list.filter(x => x && random() >= 0.08)
                return [...kept, ...new Array(list.length - kept.length).fill(null)]
            })
            sim[c][gender] = rounds.map(list => ({list, index: new Map(list.map((refId, i) => [refId, i]).filter(([refId]) => refId))}))
        }
    }

    function seat(refId, c, r) {
        const gender = players.get(refId)?.gender
        const lane = sim[c][gender]
        const i = lane?.[r].index.get(refId)
        return i === undefined ? null : i
    }

    function rank(refId, c, r) {
        const i = seat(refId, c, r)
        return i === null ? null : Math.floor(i / seatsPerSlot(c)) + 1
    }

    function placement(refId, c, r) {
        const i = seat(refId, c, r)
        if (i === null) return null
        const gender = players.get(refId).gender
        const slotIndex = Math.floor(i / seatsPerSlot(c))
        let partner = null
        if (c === 'MD') {
            partner = sim[c][gender === 'MEN' ? 'WOMEN' : 'MEN']?.[r].list[slotIndex]
        } else if (seatsPerSlot(c) === 2) {
            partner = sim[c][gender][r].list[i ^ 1]
        }
        return {category: c, rank: slotIndex + 1, ...slots[c][slotIndex], partnerName: partner ? players.get(partner).name : null}
    }

    return {
        rank,
        placement,
        slotLabel(c, rank) {
            const slot = slots[c][rank - 1]
            return slot ? `${slot.teamName} · ${slot.label}` : ''
        },
        categoriesFor(refId) {
            return CATEGORIES.filter(c => Array.from({length: R}, (_, r) => r).some(r => seat(refId, c, r) !== null))
        },
        currentCategories(refId) {
            return CATEGORIES.filter(c => seat(refId, c, R - 1) !== null)
        },
        playersIn(c) {
            const all = new Set()
            for (const gender of ['MEN', 'WOMEN']) {
                (sim[c][gender] || []).forEach(round => round.index.forEach((_, refId) => all.add(refId)))
            }
            return [...all]
        },
        placementsAt(refId, r) {
            return CATEGORIES.map(c => placement(refId, c, r)).filter(Boolean)
        },
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
