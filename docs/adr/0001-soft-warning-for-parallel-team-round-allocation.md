# ADR 0001: Soft Warning for Player Allocation Across Parallel Team Rounds

## Status
Accepted

## Context
In badminton clubs, coordinators often configure multiple team rounds (`team_rounds`) that share the same round number within a season (e.g. senior teams in one round setup, youth/veteran or parallel tiers in another).
While the system strictly excludes players already allocated in the *current* team round (`scopeNotOnSquad`), customer feedback indicated that coordinators had no visibility when a player was already placed on a squad in a *parallel* team round during that same round number.

## Decision
1. **Collision Boundary**: Collisions are scoped to `clubhouse_id`, `season_id`, and `round` number, excluding the current `team_round_id`.
2. **Soft Badging vs Hard Blocking**: The system surfaces parallel allocation as an informative badge (`Optaget på: [Holdrunde-navn]`) in the player search list (`PlayersListSearch.vue`), but **does not hard-block** or prevent the manager from adding the player (`addSquadMemberByRefId`).
   - *Reason*: Under Danish badminton regulations (Holdturneringsreglementet § 14 / opryknings- og nedrykningsregler), players are legally permitted to play on two different teams in the same round under specific emergency/substitution conditions, with the constraint that they must skip the subsequent round. Hard-blocking would break legitimate substitution workflows.
3. **Query Architecture**: A batched resolver fetches parallel round allocations for all displayed members in a single query keyed on `(clubhouse_id, season_id, round)`, avoiding N+1 database queries during pagination.
