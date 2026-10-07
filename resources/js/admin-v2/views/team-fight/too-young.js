// §31 stk. 1: A player must turn 15 no later than 31.12. in the calendar year the season starts.
// The server decides who is too young (validateTooYoungPlayers); this is a soft warning only.

export function isTooYoung(tooYoungPlayers, player) {
    return (tooYoungPlayers || []).some(tooYoungPlayer => tooYoungPlayer.refId === player.refId)
}

export function tooYoungMessage(seasonStartYear) {
    return `Spilleren er ikke fyldt 15 år senest 31.12.${seasonStartYear} og må ikke spille i seniorholdturneringen (§31 stk. 1)`
}
