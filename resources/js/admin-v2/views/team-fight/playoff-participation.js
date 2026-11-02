// The Playoff Participation Requirement (§32 stk. 1 a–c) in the TeamRound editor: the messages for
// the findings of validatePlayoffParticipation. Framework-free, so node:test can load it.

// The User Guide section that explains the requirement, linked under the findings.
export const PLAYOFF_PARTICIPATION_GUIDE = {slug: 'lav-og-kontroller-den-officielle-holdopstilling', hash: '#kontrol-af-slutspilskravet'}

const DEADLINE_MISSED = 'har ikke spillet for klubben senest 20. oktober'
const DEADLINE_MET = '20. oktober-kravet er opfyldt'
const DEADLINE_UNKNOWN = '20. oktober-kravet kan ikke afgøres'

// "Hold 1: Anders har 3 af 4 kampe i sæsonen; 20. oktober-kravet er opfyldt (§32 stk. 1)."; null when the player meets
// the requirement or it doesn't apply.
export function playoffParticipationMessage({squadIndex, name, status, matchCount, requiredMatchCount, playedByDeadline}) {
    const player = `Hold ${squadIndex + 1}: ${name}`
    const matches = `${matchCount} af ${requiredMatchCount} kampe`
    if (status === 'CANNOT_VERIFY') {
        // Unknown here can also mean not checked, so only a met deadline is named; the guidance says what is missing.
        const deadline = playedByDeadline === true ? `; ${DEADLINE_MET}` : ''
        const counted = requiredMatchCount > 0 ? ` har ${matches}${deadline}` : ''
        return `${player}${counted} – slutspilskravet kan ikke verificeres fuldt ud (§32 stk. 1).`
    }
    if (status !== 'REQUIREMENT_NOT_MET') return null
    if (matchCount >= requiredMatchCount) return `${player} har ${matches}, men ${DEADLINE_MISSED} (§32 stk. 1).`
    const deadline = {true: `; ${DEADLINE_MET}`, false: ` og ${DEADLINE_MISSED}`, null: `; ${DEADLINE_UNKNOWN}`}[playedByDeadline]
    return `${player} har ${matches} i sæsonen${deadline} (§32 stk. 1).`
}

const GUIDANCE = {
    MISSING_ROUND: 'Udfyld Runde nr. i holdrundens indstillinger.',
    UNKNOWN_TIER: 'Angiv holdets række under Rediger holdet, eller på sæsonens hold, hvis et hold er tilknyttet.',
    MISSING_SEASON: 'Holdrunden mangler sæson, så kontakt support for at få den rettet.',
    UNKNOWN_HISTORY_TIER: 'Angiv rækken på spillerens hold i de tidligere holdrunder herunder.',
    MISSING_HISTORY_DATE: 'Angiv spilledatoen i de tidligere holdrunder herunder.'
}

// What to fix for each incomplete reason, in the server's order.
export function playoffParticipationGuidance(player) {
    return (player.incompleteReasons ?? []).map(reason => GUIDANCE[reason]).filter(Boolean)
}
