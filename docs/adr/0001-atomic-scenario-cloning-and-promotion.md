# Architectural Decision Record: Category/Lineup-Layer Scenarios and Named Plan Promotion

## Status
Accepted

## Context
Badminton match rounds (`TeamRound`) contain multiple club squads (`Squad`, e.g. Hold 1, Hold 2, Hold 3) governed by strict Badminton Danmark cross-squad ranking rules (`validateCrossSquads`). When an injury occurs on a higher team, a domino cascade forces players to be moved across squads, altering lineups down the entire hierarchy.

We evaluated three potential architectural cut points for introducing Lineup Scenarios ("Gemte Holdopstillinger / Kladder"):
1. **TeamRound Layer**: Deep-cloning the entire `TeamRound` row and all child entities (`parent_team_round_id` / `is_scenario`).
2. **Squad Layer**: Cloning `Squad` rows with a `scenario_id` under a single `TeamRound`.
3. **Category / Lineup Layer**: Keeping `TeamRound` and `Squad` strictly canonical (1:1 with calendar rounds and matches), and branching only the lineup slots and player assignments (`SquadCategory`, `SquadMember`, `SquadPoint`) under a `team_round_scenarios` container.

## Decision

We decided to cut at the **Category / Lineup Layer**:

1. **Canonical Match Logistics (`TeamRound` & `Squad`)**:
   - `TeamRound` remains 1:1 with the calendar match round.
   - `Squad` remains 1:1 with the physical team match, acting as the single source of truth for `playing_place`, `playing_address`, `playing_datetime`, `tier`, and `external_team_fight_id` (BadmintonPlayer.dk ID).
   - Updating venue or match times on a squad automatically reflects across all draft scenarios without divergence.

2. **Scenarios as Lineup Containers (`team_round_scenarios`)**:
   - A `team_round_scenarios` table defines named plans (`id`, `team_round_id`, `name`, `is_official`).
   - `SquadCategory` links to both `squad_id` and `team_round_scenario_id` (nullable, where `NULL` or `is_official = true` represents the official lineup).
   - Branching a scenario clones only the `SquadCategory`, `SquadMember`, and `SquadPoint` records for that round's squads, assigning them to the new scenario.

3. **Promotion via Atomic Flag Pointer Swap**:
   - Promoting "Plan B" to official is an atomic metadata flag update: setting `is_official = false` on the old plan and `is_official = true` on Plan B.
   - Preserves all previous lineups without row deletions, re-insertions, or primary key re-mappings.
   - Reverting between Plan A and Plan B is instantaneous and non-destructive.

4. **Ranking & Validation Isolation**:
   - `validateCrossSquads` validates whatever set of squad categories is currently active in the client state.
   - Player notifications (`SendTeamNotification`) and player visibility scopes (`scopeVisibleToUser`) query only categories belonging to the official scenario (`is_official = true`).

## Consequences & Considered Options

- **Rejected: TeamRound Layer Cloning**: Creating phantom `TeamRound` records duplicates match venue and time details. If a match time is updated while testing a draft, the draft becomes stale. It also introduces risks of shadow rounds leaking into calendar views and player dashboards.
- **Rejected: Squad Layer Cloning**: Cloning squads still duplicates match venue and time metadata, failing to solve the single-source-of-truth problem for match logistics.
- **Benefit of Category Layer Cut**: Keeps match logistics strictly canonical while giving managers full freedom to draft, swap, and validate alternative player allocations across teams.
