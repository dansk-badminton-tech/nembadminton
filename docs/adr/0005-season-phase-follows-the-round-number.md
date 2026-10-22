# Season Phase follows the round number, not the date

Several DH rules apply only in one part of the season (grundspil, kvalifikation or slutspil), so Nembadminton needs to know which Season Phase a TeamRound belongs to. We use the TeamRound's round number and never its game date, because §45 numbers the rounds in the tournament programme and makes those numbers apply "uden hensyn til fastsat og faktisk afviklingstidspunkt": a moved match keeps its round, and a date-based rule would misplace it. Round numbers must be interpreted in the Squad's tournament context: a number shared by different match kinds does not establish the phase on its own.

## Clarification: Liga finals share round 12 with qualification

The completed 2025/2026 DH season corroborates this structure:

| Competition | Group stage | Playoffs | Last ordinary playoff round |
| --- | --- | --- | --- |
| 1. division–Danmarksserien | 1–7 | 8–11 | 11 |
| Badmintonligaen medal playoffs | 1–9 | Quarterfinal 10, semifinal 11, gold/bronze 12 | 12 |

This agrees with the overall structure in §§9 and 16–25 of the regulations effective 1 July 2026. Qualification matches are round 12 under §27 stk. 1, but Liga gold/bronze are also round 12 and remain playoffs. Liga has no promotion to a higher domestic league; its ninth-placed Team can nevertheless play a qualification match to retain its Liga place (§17 stk. 2). Tier plus round therefore cannot distinguish every case. A group named "Kvalifikation til …" may be an ordinary promotion playoff group in rounds 8–11, not a qualification match.

**Implementation gap:** `SeasonPhase::fromRound()` currently treats every round 12 as qualification. That fallback misclassifies Liga medal finals and is not the complete domain rule. Do not replace this gap with date-based inference or assume the latest entered TeamRound is the final round.

## Agreed Match Kind design (not yet implemented)

- For a Liga Squad in round 12, the coach explicitly chooses between a medal final (gold/bronze) and a qualification match. Other unambiguous tier/round combinations remain automatic. External metadata must not silently establish or overwrite the choice.
- The Match Kind belongs to the Squad and is shared across its Scenarios, not to its season-long Team or the whole TeamRound: Squads within one TeamRound may have different match kinds. The Team's original tier remains unchanged.
- An unresolved Match Kind, including in legacy data, does not prevent editing or saving, but affected eligibility checks must visibly state that eligibility cannot be fully verified. They must not silently assume a kind or report a passed check.
- Copying a TeamRound preserves the explicit choice rather than resetting it: the maintainer prefers retaining it, given that this is the season's final match. A retained choice only affects classification when the Squad is Liga in round 12; elsewhere it is kept but ignored, and tier/round classification applies normally.
- Paper Squads do not require the choice and do not receive a missing-Match-Kind eligibility warning. Their paper-team checks apply independently. If a Squad is unmarked as paper, a choice is needed for complete validation when it is Liga in round 12.
- The coach may correct a choice after entering lineups. A correction recalculates affected validation across every Scenario without changing player placements or the Team's original tier.

The historical Liga/1. division qualification match displays the composite round label "11/12". Its meaning and import handling remain unresolved; do not silently coerce it to an integer or use it to override §27 in the newer regulations. Detailed observations, source links and the historical-season caveat are recorded in [#224's findings](https://github.com/dansk-badminton-tech/nembadminton/issues/224#issuecomment-6340885558).

## Rule consequences of distinguishing the match kinds

- Liga gold/bronze are the final playoff round for §38 stk. 8: at least one earlier season appearance on the same or a lower Team. Ordinary playoffs below Liga reach that round at 11.
- §32 covers both playoffs and qualification: Liga players need at least four club senior team appearances, 1. division players at least three, plus the 20 October condition, subject to the regulation's exceptions and dispensations.
- The Liga/1. division qualification match uses the 1. division match format (§27 stk. 2), but each side retains its original division's player rules (§27 stk. 3). The separate appearance requirement in §27 stk. 5 explicitly excludes Teams covered by §32; do not stack it on top for these Teams.
- Qualification must not become an ordinary final playoff round merely because its round number is 12. §27 stk. 6 retains ordinary lineup rules; any interpretation extending §38 stk. 8 to qualification must be explicit, not an accidental consequence of phase classification.
- Paper Squads obey their own rule (§38 stk. 7), which explicitly says only that subsection must be met. A round-12 paper-team record does not establish actual qualification participation.

## Consequences

- The phase depends on the Squad's tier as well as the round, so it is worked out per Squad. A TeamRound's phase is taken from its highest Squad.
- A TeamRound without a round number has an unknown phase (`null`). We don't guess it from the date: a lookup that filters by phase throws `UnknownPhaseException` naming the TeamRound, so the coach can fill in **Runde nr.** instead of getting a quietly wrong count.
- `SeasonPhase` is kept separate from `TournamentPhaseType`, which describes badmintonplayer.dk groups (puljer) and is not the same thing.
- Season History still orders TeamRounds by game date; only the phase, and the round-based warnings (parallel allocation, Reserve Quarantine), use the round number.
