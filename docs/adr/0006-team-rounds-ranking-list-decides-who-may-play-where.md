# The TeamRound's Ranking List decides which Squad a Member may stand on

§38 stk. 1b says that when a club's teams in the same round play under different monthly Ranking Lists, the earliest one decides which players are picked for which team, and each team's own Ranking List decides its lineup order. A Squad could already have its own Ranking List (typically a Liga Squad playing on other dates than the rest of the club), and the comparison across Squads (§38 stk. 4) compared each Squad's own points. It mixed Ranking Lists and gave both false and missed findings.

We decided that the TeamRound's Ranking List always decides the comparison across Squads, and a Squad's own Ranking List only decides the order on that Squad (stk. 2–3). Nembadminton doesn't work out which Ranking List is the earliest from the Squads' playing dates: the coach picks it as the TeamRound's Ranking List, and the Ranking List field explains this. The rule is simple to state and check, and it leaves the judgement to the coach, who knows when the club's Squads play. Working it out automatically would depend on every Squad's playing date being entered and correct.

## Consequences

- `validateCrossSquads` stays stateless: the client sends each player's points on the TeamRound's Ranking List as the optional `pickingPoints`, next to `points`, which are still the Squad's own. Without `pickingPoints`, such as from a browser tab with an older bundle, it compares `points` as before. The other checks read only `points`.
- The TeamRound editor reads those points through `SquadMember.rankingPoints(version:)`, only for Squads with their own Ranking List. "Tidligere holdkamp" fetches the team matches a second time on the shared Ranking List when a team match has its own.
- A Squad's saved points stay on its own Ranking List.
- Out of scope: suggesting the earliest Ranking List, and §38 stk. 1b's second part (a higher team that has finished the tournament uses the Ranking List of its final round). See [#226](https://github.com/dansk-badminton-tech/nembadminton/issues/226).
