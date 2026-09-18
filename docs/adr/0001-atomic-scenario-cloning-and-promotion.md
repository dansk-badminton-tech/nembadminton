# Atomic Scenario Cloning and Promotion

## Context

Badminton match rounds involve multiple club squads (e.g. Hold 1, Hold 2, Hold 3) governed by strict Badminton Danmark cross-squad ranking rules (`validateCrossSquads`). When an injury occurs on a higher team, a domino cascade forces players to be promoted across squads, altering lineups down the entire hierarchy.

When introducing Lineup Scenarios ("Gemte Holdopstillinger / Kladder"), we evaluated whether branching and promoting could be performed at the individual squad level (cherry-picking / partial promotion) or must encompass the entire round.

## Decision

We decided that:
1. **Branching is full-round**: Creating a scenario always deep-clones all squads in the `TeamRound`. Scenarios are 100% self-contained, independent sandboxes.
2. **Promotion is atomic and full-round**: Promoting a scenario replaces all squads in the official round in a single database transaction. Cherry-picking or partial squad-level promotion is explicitly rejected.
3. **Preservation via named plan swap**: Promoting a draft (e.g. "Plan B") preserves the outgoing official lineup as a draft scenario named after its plan (e.g. "Plan A"), ensuring zero data loss and immediate rollback capability.

## Consequences & Considered Options

- **Rejected: Sparse / Squad-level Branching & Partial Promotion**: Cherry-picking a single squad from a draft into the official round creates severe domain hazards: players duplicated across multiple teams, broken strength order across tiers, and complex merge conflicts. By enforcing full-round branching and atomic promotion, the system guarantees that the exact lineup validated in the draft is what becomes official without runtime validation failures or race conditions.
