// The paper-team rule (§38 stk. 7) in the TeamRound editor: messages for the findings of validatePaperSquads, a soft
// warning like the reserve findings. Framework-free, so node:test can load it.

// Shown with the findings: why a breach on a Paper Squad matters.
export const PAPER_SQUAD_AGGRAVATION = 'En ulovlig spiller på et papirhold er en skærpende omstændighed: klubbens underliggende hold kan blive taberdømt (§54 stk. 7).'

export function hasPaperSquad(squads) {
    return (squads ?? []).some(squad => squad.isPaperSquad === true)
}

function times(count) {
    return count === 1 ? '1 gang' : `${count} gange`
}

// "1, 2 og 3"
function listOf(items) {
    return items.length > 1 ? `${items.slice(0, -1).join(', ')} og ${items.at(-1)}` : items.join('')
}

// The rounds by round number, a round without one by its TeamRound name: "runde 1, 2 og Ekstra runde".
function roundsOf(rounds) {
    const numbers = rounds.filter(round => round.round !== null).map(round => round.round)
    const names = rounds.filter(round => round.round === null).map(round => round.name)
    if (!names.length) return `runde ${listOf(numbers)}`
    return listOf([...(numbers.length ? [`runde ${numbers.join(', ')}`] : []), ...names])
}

// Why earlier rounds didn't count: none of their Squads has the Paper Squad's Team, so the place can't be compared.
function uncountedMessage(rounds, teamName) {
    const one = rounds.length === 1
    const matches = one ? '1 holdkamp' : `${rounds.length} holdkampe`
    const there = one ? 'den runde' : 'de runder'
    const lineups = one ? 'holdopstillingen' : 'holdopstillingerne'
    return `${matches} (${roundsOf(rounds)}) kunne ikke tælles med, fordi ingen holdopstilling i ${there} er knyttet til ${teamName}. Vælg hold på ${lineups} i ${there}.`
}

function playerMessage({squadIndex, name, unplayedCategories, matchCount, requiredMatchCount, teamName, uncountedRounds}) {
    const breaches = []
    if (unplayedCategories.length) {
        breaches.push(`har ikke spillet ${unplayedCategories.join(', ')} tidligere i sæsonen`)
    }
    const tooFewMatches = matchCount < requiredMatchCount
    if (tooFewMatches) {
        breaches.push(`har stået på samme eller et lavere hold ${times(matchCount)} tidligere i sæsonen, men skal have stået der mindst ${times(requiredMatchCount)}`)
    }
    const message = `Hold ${squadIndex + 1} (papirhold): ${name} ${breaches.join(' og ')}.`
    return tooFewMatches && uncountedRounds?.length ? `${message} ${uncountedMessage(uncountedRounds, teamName)}` : message
}

// validation is the PaperSquadValidation from validatePaperSquads, or null before it has run.
export function paperSquadMessages(validation) {
    if (!validation) return []
    if (validation.missingRound) {
        return ['Holdrunden mangler rundenummer, så papirholdsreglen (§38 stk. 7) kan ikke kontrolleres. Udfyld rundenummeret.']
    }
    return validation.players.map(playerMessage)
}
