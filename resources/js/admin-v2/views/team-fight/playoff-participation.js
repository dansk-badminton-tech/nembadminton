// The Playoff Participation Requirement (§32 stk. 1 a–c) in the TeamRound editor: when to check it and the messages for
// the findings of validatePlayoffParticipation, a soft warning. Framework-free, so node:test can load it.

// The User Guide section that explains the requirement, linked under the findings.
export const PLAYOFF_PARTICIPATION_GUIDE = {slug: 'lav-og-kontroller-den-officielle-holdopstilling', hash: '#kontrol-af-slutspilskravet'}

// The first playoff round of each tier the requirement covers (§9 stk. 1).
const FIRST_PLAYOFF_ROUND = {liga: 10, division1: 8}

export function showPlayoffParticipationControl(teamRound) {
    const round = Number(teamRound?.round)
    if (!teamRound?.round || !Number.isInteger(round)) return false
    return (teamRound?.squads ?? []).some(squad => {
        if (squad.isPaperSquad) return false
        const tier = (squad.effectiveTier ?? '').trim()
        // Unknown tier context stays visible for the server's uncertainty warning.
        if (!tier) return round >= FIRST_PLAYOFF_ROUND.division1
        if (/^badmintonligaen\b/i.test(tier)) return round >= FIRST_PLAYOFF_ROUND.liga
        if (/^1\.\s*division\b/i.test(tier)) return round >= FIRST_PLAYOFF_ROUND.division1
        return false
    })
}

const DEADLINE_MISSED = 'har ikke spillet for klubben senest 20. oktober'

// "Hold 1: Anders har 3 af 4 kampe i sæsonen (§32 stk. 1)."; null when the player meets the requirement or it doesn't apply.
export function playoffParticipationMessage({squadIndex, name, status, matchCount, requiredMatchCount, playedByDeadline}) {
    const player = `Hold ${squadIndex + 1}: ${name}`
    const matches = `${matchCount} af ${requiredMatchCount} kampe`
    if (status === 'CANNOT_VERIFY') {
        const counted = requiredMatchCount > 0 ? ` har ${matches}` : ''
        return `${player}${counted} – slutspilskravet kan ikke verificeres fuldt ud (§32 stk. 1).`
    }
    if (status !== 'REQUIREMENT_NOT_MET') return null
    if (matchCount >= requiredMatchCount) return `${player} har ${matches}, men ${DEADLINE_MISSED} (§32 stk. 1).`
    const deadline = playedByDeadline === false ? ` og ${DEADLINE_MISSED}` : ''
    return `${player} har ${matches} i sæsonen${deadline} (§32 stk. 1).`
}

const GUIDANCE = {
    MISSING_ROUND: 'Udfyld Runde nr. i holdrundens indstillinger.',
    UNKNOWN_TIER: 'Angiv holdets række under Rediger holdet. Er et hold tilknyttet, skal rækken rettes på sæsonens hold.',
    MISSING_SEASON: 'Holdrunden mangler sæson. Kontakt support for at få holdrundens sæson rettet.',
    UNKNOWN_HISTORY_TIER: 'Angiv rækken på spillerens hold i de tidligere holdrunder herunder.',
    MISSING_HISTORY_DATE: 'Angiv spilledatoen i de tidligere holdrunder herunder.'
}

// What to fix for each incomplete reason, in the server's order.
export function playoffParticipationGuidance(player) {
    return (player.incompleteReasons ?? []).map(reason => GUIDANCE[reason]).filter(Boolean)
}
