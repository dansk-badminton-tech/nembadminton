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

function playerMessage({squadIndex, name, unplayedCategories, matchCount, requiredMatchCount}) {
    const breaches = []
    if (unplayedCategories.length) {
        breaches.push(`har ikke spillet ${unplayedCategories.join(', ')} tidligere i sæsonen`)
    }
    if (matchCount < requiredMatchCount) {
        breaches.push(`har stået på samme eller et lavere hold ${times(matchCount)} tidligere i sæsonen, men skal have stået der mindst ${times(requiredMatchCount)}`)
    }
    return `Hold ${squadIndex + 1} (papirhold): ${name} ${breaches.join(' og ')}.`
}

// validation is the PaperSquadValidation from validatePaperSquads, or null before it has run.
export function paperSquadMessages(validation) {
    if (!validation) return []
    if (validation.missingRound) {
        return ['Holdrunden mangler rundenummer, så papirholdsreglen (§38 stk. 7) kan ikke kontrolleres. Udfyld rundenummeret.']
    }
    return validation.players.map(playerMessage)
}
