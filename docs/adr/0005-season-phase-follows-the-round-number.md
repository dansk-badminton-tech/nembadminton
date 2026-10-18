# Season Phase follows the round number, not the date

Several DH rules apply only in one part of the season (grundspil, kvalifikation or slutspil), so Nembadminton needs to know which Season Phase a TeamRound belongs to. We derive it from the TeamRound's round number and never from its game date, because §45 numbers the rounds in the tournament programme and makes those numbers apply "uden hensyn til fastsat og faktisk afviklingstidspunkt": a moved match keeps its round, and a date-based rule would misplace it. The mapping lives in `SeasonPhase::fromRound()`: round 12 is qualification (§27 stk. 1), round 1–7 is the group stage, or 1–9 in Badmintonligaen (§9 stk. 1), and every other round is play-offs (§15).

## Consequences

- The phase depends on the Squad's tier as well as the round, so it is worked out per Squad. A TeamRound's phase is taken from its highest Squad.
- A TeamRound without a round number has an unknown phase (`null`). We don't guess it from the date: a lookup that filters by phase throws `UnknownPhaseException` naming the TeamRound, so the coach can fill in **Runde nr.** instead of getting a quietly wrong count.
- `SeasonPhase` is kept separate from `TournamentPhaseType`, which describes badmintonplayer.dk groups (puljer) and is not the same thing.
- Season History still orders TeamRounds by game date; only the phase, and the round-based warnings (parallel allocation, Reserve Quarantine), use the round number.
