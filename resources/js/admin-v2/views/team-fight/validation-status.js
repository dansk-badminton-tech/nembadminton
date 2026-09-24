export function invalidLevelTip(invalidLevel) {
    if (invalidLevel === null) return 'Deaktiveret indtil alle hold er fuldt besat'
    return invalidLevel ? 'En eller flere spillere spiller på et for lavt rangeret hold (Bryder § 38. stk. 4).' : 'Niveauordningen overholdes (§ 38. stk. 4).'
}

export function formatBelowPlayers(belowPlayers, formatter) {
    const seenPlayers = new Set()

    return belowPlayers.flatMap(player => {
        const identity = player.refId ?? player
        if (seenPlayers.has(identity)) return []

        seenPlayers.add(identity)
        return [formatter(player)]
    })
}

export function buildValidationErrors({incompleteTeam, basicSquads, invalidLevel, invalidLevelList, invalidCategory, invalidCategoryList}) {
    const errors = []

    if (incompleteTeam) {
        basicSquads.forEach(squad => {
            if (!squad.spotsFulfilled) {
                errors.push(`Hold ${squad.index + 1} mangler spillere.`)
            }
        })
    }

    if (invalidLevel) {
        invalidLevelList.forEach(player => {
            const belowNames = formatBelowPlayers(player.belowPlayer, belowPlayer => belowPlayer.name).join(', ')
            errors.push(`${player.name} spiller på et for lavt rangeret hold i forhold til ${belowNames}.`)
        })
    }

    if (invalidCategory) {
        invalidCategoryList.forEach(player => {
            const belowNames = formatBelowPlayers(player.belowPlayer, belowPlayer => belowPlayer.name).join(', ')
            errors.push(`${player.name} spiller for højt i sin kategori (${player.category}) i forhold til ${belowNames}.`)
        })
    }

    return errors
}
