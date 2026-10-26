// The paper-team rule (§38 stk. 7) in the TeamRound editor: messages for the findings of validatePaperSquads, a soft
// warning like the reserve findings. Framework-free, so node:test can load it.

// Shown with the findings: why a breach on a Paper Squad matters.
export const PAPER_SQUAD_AGGRAVATION = 'En ulovlig spiller på et papirhold er en skærpende omstændighed: klubbens underliggende hold kan blive taberdømt (§54 stk. 7).'

// The Help guide on linking the Team in earlier TeamRounds, linked under the findings when a player has uncounted rounds.
export const TEAM_LINK_GUIDE = 'tilknyt-holdet-i-tidligere-runder'

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
// TEAM_LINK_GUIDE explains how to link it.
function uncountedMessage(rounds, teamName) {
    const one = rounds.length === 1
    const matches = one ? '1 holdkamp' : `${rounds.length} holdkampe`
    return `${matches} (${roundsOf(rounds)}) kunne ikke tælles med, fordi ${teamName} ikke er tilknyttet i ${one ? 'den runde' : 'de runder'}.`
}

function hasUncountedRounds({matchCount, requiredMatchCount, uncountedRounds}) {
    return matchCount < requiredMatchCount && uncountedRounds.length > 0
}

function playerMessage(player) {
    const {squadIndex, name, unplayedCategories, matchCount, requiredMatchCount, teamName, uncountedRounds} = player
    const breaches = []
    if (unplayedCategories.length) {
        breaches.push(`har ikke spillet ${unplayedCategories.join(', ')} tidligere i sæsonen`)
    }
    if (matchCount < requiredMatchCount) {
        breaches.push(`har stået på samme eller et lavere hold ${times(matchCount)} tidligere i sæsonen, men skal have stået der mindst ${times(requiredMatchCount)}`)
    }
    const message = `Hold ${squadIndex + 1} (papirhold): ${name} ${breaches.join(' og ')}.`
    return hasUncountedRounds(player) ? `${message} ${uncountedMessage(uncountedRounds, teamName)}` : message
}

// validation is the PaperSquadValidation from validatePaperSquads, or null before it has run.
export function paperSquadMessages(validation) {
    if (!validation) return []
    if (validation.missingRound) {
        return ['Holdrunden mangler rundenummer, så papirholdsreglen (§38 stk. 7) kan ikke kontrolleres. Udfyld rundenummeret.']
    }
    const messages = validation.players.map(playerMessage)
    // Rounds without a round number can't be earlier, so they may hold the missing matches.
    const withoutRound = validation.withoutRound ?? []
    if (messages.length && withoutRound.length) {
        const names = listOf(withoutRound.map(round => round.name))
        messages.push(`${names} mangler rundenummer, så ${withoutRound.length > 1 ? 'de' : 'den'} tælles ikke med i papirholdsreglen. Udfyld rundenummeret.`)
    }
    return messages
}

// validation is the PaperSquadValidation from validatePaperSquads, or null before it has run.
export function showsTeamLinkGuide(validation) {
    return (validation?.players ?? []).some(hasUncountedRounds)
}
