// Picking points (§38 stk. 4): the TeamRound's ranking decides which Squad a player may stand on, also for a Squad with
// its own ranking. That own ranking only decides the order on the Squad (stk. 2-3), so validateCrossSquads gets the
// players' points on the TeamRound's ranking as pickingPoints. In "Tidligere holdkamp" the shared ranking plays that part.
// Framework-free, so node:test can load it.

export function hasOwnRanking(squad, teamRoundVersion) {
    return Boolean(squad.version) && squad.version !== teamRoundVersion
}

// rankedSquads: [{key, players: [{refId, points}]}], the Squads with their own ranking and their players' points on the
// TeamRound's ranking. Returns (key, refId) => those points ([] when the player has none there), or undefined for a
// Squad that isn't ranked, whose own points are on the TeamRound's ranking already.
export function pickingPointsLookup(rankedSquads) {
    const bySquad = new Map(rankedSquads.map(({key, players}) => [
        key,
        new Map(players.map(player => [player.refId, player.points]))
    ]))
    return (key, refId) => {
        const players = bySquad.get(key)
        return players === undefined ? undefined : (players.get(refId) ?? [])
    }
}
