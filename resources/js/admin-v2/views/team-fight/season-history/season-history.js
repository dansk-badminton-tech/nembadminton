// Season History (TeamRound.seasonHistory) for one player at a time: the categories they were placed in, their
// Kategoriplads per round, and the lines of the players around them. The backend ranks the places; this only reads them.

export const CATEGORIES = ['HS', 'DS', 'HD', 'DD', 'MD']
export const CATEGORY_NAMES = {HS: 'herresingle', DS: 'damesingle', HD: 'herredouble', DD: 'damedouble', MD: 'mixdouble'}
export const EMPTY_TEXT = 'Ingen tidligere holdrunder i sæsonen'
export const NOT_PLACED_TEXT = 'Ikke opstillet'

// How far from the player another player may be placed, in any round, to get a line in the chart.
const NEARBY = 2
const YOUTH = ['U15', 'U17', 'U19']

export function isDoubleCategory(category) {
    return ['HD', 'DD', 'MD'].includes(category)
}

function placeLabelOf(place) {
    return `Hold ${place.squadPosition} · ${place.label}`
}

/**
 * @param {Array} rounds TeamRound.seasonHistory: the earlier rounds oldest first, then the current one.
 */
export function seasonHistory(rounds) {
    // seats[category][round] = Map(refId -> place)
    const seats = Object.fromEntries(CATEGORIES.map(category => [category, rounds.map(() => new Map())]))
    const players = new Map()
    rounds.forEach((round, r) => {
        for (const {category, places} of round.categories) {
            for (const place of places) {
                for (const player of place.players) {
                    seats[category][r].set(player.refId, place)
                    if (!players.has(player.refId)) {
                        players.set(player.refId, {...player, youth: YOUTH.includes(player.vintage)})
                    }
                }
            }
        }
    })
    const currentRound = rounds.findIndex(round => round.isCurrent)

    const seat = (refId, category, r) => seats[category]?.[r]?.get(refId) ?? null
    const placeOf = (refId, category, r) => seat(refId, category, r)?.place ?? null
    const roundIndexes = rounds.map((_, r) => r)

    const categoriesOf = refId => CATEGORIES.filter(category => roundIndexes.some(r => seat(refId, category, r)))
    const currentCategoriesOf = refId => currentRound < 0 ? [] : CATEGORIES.filter(category => seat(refId, category, currentRound))

    const findPlace = (category, place, r) => rounds[r]?.categories.find(c => c.category === category)?.places.find(p => p.place === place)

    // The place as it was in round r. Without r, or when round r has no such place, as it was most recently.
    function placeLabel(category, place, r = null) {
        const order = [r, ...[...roundIndexes].reverse()].filter(i => i !== null)
        for (const i of order) {
            const found = findPlace(category, place, i)
            if (found) {
                return placeLabelOf(found)
            }
        }
        return ''
    }

    function line(refId, category, kind) {
        const segments = []
        let segment = []
        for (const r of roundIndexes) {
            const place = placeOf(refId, category, r)
            if (place === null) {
                if (segment.length) segments.push(segment)
                segment = []
                continue
            }
            segment.push({round: r, place})
        }
        if (segment.length) segments.push(segment)
        const player = players.get(refId)
        return {refId, name: player.name, kind, segments, end: segments.at(-1)?.at(-1) ?? null}
    }

    function isNearby(subject, other, category) {
        return roundIndexes.some(r => {
            const a = placeOf(subject, category, r)
            const b = placeOf(other, category, r)
            return a !== null && b !== null && Math.abs(a - b) <= NEARBY
        })
    }

    return {
        rounds,
        hasEarlierRounds: rounds.some(round => !round.isCurrent),
        player: refId => players.get(refId) ?? null,
        placeOf,
        placeLabel,
        categoriesOf,
        currentCategoriesOf,
        defaultCategory(refId) {
            const now = currentCategoriesOf(refId)
            return now.find(category => !isDoubleCategory(category)) ?? now[0] ?? categoriesOf(refId)[0] ?? null
        },
        // Every category the player was placed in that round, with its place and partners.
        placementsOf(refId, r) {
            return CATEGORIES.flatMap(category => {
                const place = seat(refId, category, r)
                if (!place) return []
                return [{
                    category,
                    place: place.place,
                    label: placeLabelOf(place),
                    partners: place.players.filter(p => p.refId !== refId).map(p => p.name),
                }]
            })
        },
        // The player's line, drawn last, and the lines of everyone within NEARBY places of them in any round.
        chart(refId, category) {
            const others = [...new Set(roundIndexes.flatMap(r => [...seats[category][r].keys()]))]
                .filter(other => other !== refId && isNearby(refId, other, category))
            const lines = [
                ...others.map(other => line(other, category, players.get(other).youth ? 'youth' : 'other')),
                line(refId, category, 'subject'),
            ]
            const placesInChart = lines.flatMap(l => l.segments.flat().map(point => point.place))
            const places = []
            if (placesInChart.length) {
                for (let place = Math.min(...placesInChart); place <= Math.max(...placesInChart); place++) {
                    places.push({place, label: placeLabel(category, place)})
                }
            }
            // Partners end on the same point: one label per point, the player's name first.
            const labels = new Map()
            for (const l of [...lines].reverse()) {
                if (!l.end) continue
                const key = `${l.end.round}-${l.end.place}`
                if (!labels.has(key)) {
                    labels.set(key, {key, round: l.end.round, place: l.end.place, names: [], subject: false, youth: false})
                }
                const label = labels.get(key)
                label.names.push(l.name)
                label.subject ||= l.kind === 'subject'
                label.youth ||= l.kind === 'youth'
            }
            return {
                lines,
                places,
                labels: [...labels.values()].map(({names, ...label}) => ({...label, text: names.join(' / ')})),
            }
        },
    }
}
