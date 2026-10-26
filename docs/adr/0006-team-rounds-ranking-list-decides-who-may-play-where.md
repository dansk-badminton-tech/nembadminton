# The TeamRound's Ranking List decides which Squad a Member may stand on

§38 stk. 1b says that when a club's teams in the same round play under different monthly Ranking Lists, the earliest one decides which players are picked for which team, and each team's own Ranking List decides its lineup order. A Squad could already have its own Ranking List (typically a Liga Squad playing on other dates than the rest of the club), and the comparison across Squads (§38 stk. 4) compared each Squad's own points. It mixed Ranking Lists and gave both false and missed findings.

We decided that the TeamRound's Ranking List always decides the comparison across Squads, and a Squad's own Ranking List only decides the order on that Squad (stk. 2–3). Nembadminton doesn't work out which Ranking List is the earliest from the Squads' playing dates. The coach enters the date of the club's first match in the round as the TeamRound's playing date, and the Ranking List is suggested from that date by the 10th-to-9th rule. The playing date field asks for that date. The rule is simple to state and check, and it leaves the judgement to the coach, who knows when the club's Squads play. Working it out automatically would depend on every Squad's playing date being entered and correct.

## Consequences

- The TeamRound editor validates the comparison across Squads with `validateTeamRoundCrossSquads(teamRoundId, scenarioId)`: the client sends no lineup, and the server reads it. A Squad on the TeamRound's Ranking List uses its stored points, so a coach's correction counts. A Squad with its own Ranking List stores points from that one, so the Members' points on the TeamRound's Ranking List are used, and a correction on it doesn't count there. The other checks still get the lineup from the client and read the Squad's own points.
- `validateCrossSquads(input)` stays for "Tidligere holdkamp", which validates team matches from badmintonplayer.dk that aren't stored, and compares the points it's sent.
- A Squad's saved points stay on its own Ranking List.
- Creating a TeamRound fills the Ranking List from the playing date. In the TeamRound's settings, changing the date only marks the suggested Ranking List, since changing the Ranking List there updates every Squad's points.
- Out of scope: suggesting the earliest Ranking List, and §38 stk. 1b's second part (a higher team that has finished the tournament uses the Ranking List of its final round). See [#226](https://github.com/dansk-badminton-tech/nembadminton/issues/226).
