# Research: Schema and Architectural Impact of Scenario Modeling on TeamRound and Squads

**Date:** 2026-09-15  
**Issue Reference:** [#138](https://github.com/dansk-badminton-tech/nembadminton/issues/138)  
**Parent Map:** [#137](https://github.com/dansk-badminton-tech/nembadminton/issues/137)  
**Status:** Complete / Decided  

---

## 1. Executive Summary & Recommendation

This research evaluates how **Lineup Scenarios** ("Gemte Holdopstillinger / Kladder") should be represented at the database, Eloquent, GraphQL, and frontend layers in `nembadminton`.

The core problem is enabling badminton team managers to draft, test, and compare alternative rosters (e.g. Plan A vs. Plan B for injuries or domino cascades across club tiers) without overwriting active match rounds, creating dummy player accounts, or triggering premature player notifications.

### Summary of Compared Options

| Evaluation Metric | Option 1: Dedicated Container (`TeamRoundScenario`) | Option 2: Self-Referential Cloning (`parent_team_round_id` / `is_scenario`) | Option 3: Serialized JSON Snapshot |
| :--- | :--- | :--- | :--- |
| **Schema Migration Safety** | 🔴 **High Risk / Breaking** (`squads.team_round_id` foreign key altered/migrated) | 🟢 **Zero Risk** (additive nullable FK + boolean flag on `team_rounds`) | 🟢 **Zero Risk** (additive JSON column or table) |
| **`SquadManager` Point Calculation Reuse** | 🟡 **Moderate Friction** (signatures & queries rewritten for `TeamRoundScenario`) | 🟢 **100% Reuse** (zero code changes to point updating routines) | 🔴 **High Friction** (requires custom in-memory hydration or rewrite) |
| **GraphQL Mutation Reusability** | 🔴 **Breaking** (requires new scenario-scoped mutation arguments or adapters) | 🟢 **100% Reuse** (all existing granular mutations work out of the box) | 🔴 **Incompatible** (cannot use existing granular relational mutations) |
| **Frontend Integration (`TeamFight.vue`)** | 🟡 **Moderate Rewrite** (squads moved under scenario nesting) | 🟢 **Minimal Friction** (scenario is just a `teamRoundId`, loads identically) | 🔴 **Heavy Rewrite** (switches from granular mutations to monolithic JSON sync) |
| **Data Leakage & Blast Radius** | 🟢 **Inherently Isolated** (scenarios not in `team_rounds` table) | 🟡 **Requires Strict Scoping & Guards** (must guard queries and notification mutations) | 🟢 **Inherently Isolated** |
| **Partial Squad Promotion (Issue #140)** | 🟢 Supported (squads are relational rows) | 🟢 Supported (squads are relational rows) | 🔴 Difficult (JSON slicing & database row patching) |

### Recommendation: **Option 2 (Self-Referential `TeamRound` Cloning with Strict Scoping & Guards)**

**Option 2 is the clear architectural winner for NemBadminton.**  
NemBadminton's lineup builder is built entirely around fine-grained GraphQL mutations (`addSquadMemberByRefId`, `deleteSquadMember`, `updateSquad`, `moveSquadOrderUp`/`Down`) executed on real database IDs. Furthermore, `FlyCompany\TeamFight\SquadManager::copySquad()` and `copyTeamRound` mutation already implement deep cloning across `Squad -> SquadCategory -> SquadMember -> SquadPoint`.

By adding `parent_team_round_id` (nullable FK to `team_rounds.id`) and `is_scenario` (boolean, default false) to `team_rounds`:
1. Scenarios become fully functional sandboxes instantly.
2. Ranking recalculations, dirty version triggers, and cross-squad validation rules work with 100% reuse.
3. The UI can load and edit a scenario simply by passing `teamRoundId = scenario.id`.
4. The sole prerequisite is adding rigorous query scopes (`where('is_scenario', false)`) to public/list queries and authorization guards on notifications (`sendTeamNotification`).

---

## 2. In-Depth Comparison of the Three Architectural Patterns

### Option 1: Dedicated Scenario Container (`TeamRoundScenario`)

In this architecture, a new relational entity `TeamRoundScenario` is inserted between `TeamRound` and `Squad`:
```
TeamRound (1) ─── (N) TeamRoundScenario (1) ─── (N) Squad (1) ─── (N) SquadCategory (1) ─── (N) SquadMember (1) ─── (N) SquadPoint
```

#### Proposed Schema
```sql
CREATE TABLE team_round_scenarios (
    id VARCHAR(24) PRIMARY KEY,
    team_round_id VARCHAR(24) NOT NULL REFERENCES team_rounds(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Existing squads table must be migrated:
ALTER TABLE squads DROP FOREIGN KEY squads_team_round_id_foreign;
ALTER TABLE squads DROP COLUMN team_round_id;
ALTER TABLE squads ADD COLUMN team_round_scenario_id VARCHAR(24) NOT NULL REFERENCES team_round_scenarios(id) ON DELETE CASCADE;
```

#### Trade-Offs & Architectural Friction
- **Schema Migration Risk:** Breaking change to the core `squads` table. In April 2026, migrations `2026_04_25_120000_rename_teams_table_to_team_rounds.php` and `2026_04_25_130000_rename_squads_teams_id_to_team_round_id.php` established the current canonical link `squads.team_round_id -> team_rounds.id`. Breaking this link requires backfilling a default `TeamRoundScenario` row for every existing `TeamRound` in production and repointing every existing squad.
- **Eloquent & Policy Breakage:**
  - `Squad::teamRound()` (`app/Models/Squad.php:71`) currently expects `$this->belongsTo(TeamRound::class, 'team_round_id')`.
  - `Squad::buildSortQuery()` (`app/Models/Squad.php:61`) orders squads by `where('team_round_id', $this->team_round_id)`.
  - Policies such as `SquadPolicy::update()` (`app/Policies/SquadPolicy.php:30`) and `SquadMemberPolicy::view()` (`app/Policies/SquadMemberPolicy.php:34`) walk `$squadMember->category->squad->teamRound->clubhouse_id`.
  - Unit tests like `tests/Unit/TeamRoundRelationsTest.php` explicitly assert `squad_has_canonical_team_round_relation()`.
- **GraphQL Schema Impact:**
  - `CreateSquadInput` (`graphql/team-round.graphql:175`) currently requires `teamRound: SquadBelongsTo!`. This would break or require dual resolvers.
  - In `TeamRound`, `squads` would become a synthetic resolver returning the active scenario's squads, or the frontend query `resources/js/queries/teamRound.graphql` would have to be updated to traverse scenarios.
- **Promotion Semantics:** Promotion is clean: `is_active = true` on the promoted scenario and `false` on all siblings.

---

### Option 2: Self-Referential `TeamRound` Cloning (`parent_team_round_id` / `is_scenario=true`)

In this architecture, scenarios are standard `TeamRound` rows flagged as scenarios and linked to their root `TeamRound`:
```
TeamRound (Parent: is_scenario = false)
   ├── Squads / Categories / Players / Points (Active Match Lineup)
   └── Scenarios (hasMany self-referential: is_scenario = true, parent_team_round_id = parent.id)
          ├── Scenario "Plan A": Squads / Categories / Players / Points
          └── Scenario "Plan B": Squads / Categories / Players / Points
```

#### Proposed Schema
```sql
ALTER TABLE team_rounds 
    ADD COLUMN parent_team_round_id VARCHAR(24) NULL AFTER clubhouse_id,
    ADD COLUMN is_scenario BOOLEAN NOT NULL DEFAULT FALSE AFTER parent_team_round_id,
    ADD CONSTRAINT team_rounds_parent_fk FOREIGN KEY (parent_team_round_id) REFERENCES team_rounds(id) ON DELETE CASCADE,
    ADD INDEX team_rounds_parent_scenario_idx (parent_team_round_id, is_scenario);
```

#### Trade-Offs & Architectural Friction
- **Schema Migration Safety:** Exceptional. 100% additive, non-breaking, nullable foreign key with cascade deletion. No historical data backfill is required.
- **Maximum Reuse:**
  - `FlyCompany\TeamFight\GraphQL\Mutations\CopyTeam::copyTeam()` (`local-vendor/team-fight/src/GraphQL/Mutations/CopyTeam.php:24`) and `TeamRoundManager::copyTeam()` already implement full round cloning and squad replication via `SquadManager::copySquad()`.
  - `SquadManager::updatePoints()` and `updatePointsOnAllSquadsInTeamRound()` operate directly on `TeamRound` and `Squad` models. Scenarios can update ranking points independently without touching other scenarios or parent rounds.
  - All existing GraphQL mutations (`createSquad`, `updateSquad`, `deleteSquad`, `addSquadMemberByRefId`, `deleteSquadMember`, `moveSquadOrderUp`, `moveSquadOrderDown`) work identically without any modification because they operate on standard IDs.
- **Frontend Compatibility:** `TeamFight.vue` accepts `teamRoundId: String`. Loading a scenario is as simple as fetching `teamRound(id: $scenarioId)`.
- **Key Risks & Required Mitigations:**
  1. *Query Pollution:* Without scopes, `teamRounds` list queries (`graphql/team-round.graphql:3`, `ListTeamFights.vue`, `UpcomingTeamRounds.vue`) would return scenario rows.  
     **Mitigation:** Add an Eloquent local/global scope `scopeActiveRounds(Builder $query)` or default filter `where('is_scenario', false)` on list queries.
  2. *Player Privacy / Visibility Leak:* `TeamRound::scopeVisibleToUser` (`app/Models/TeamRound.php:50`) checks if a player is rostered on any squad. A player placed in a draft scenario must **never** see that scenario on their `PlayerDashboard.vue`.  
     **Mitigation:** Ensure `scopeVisibleToUser` explicitly filters out `is_scenario = true`.
  3. *Accidental Notifications / Publishing:* Constraint from Issue #137 states that scenarios must never send notifications or publish externally.  
     **Mitigation:** In `SendTeamNotification::__invoke()` (`local-vendor/team-fight/src/GraphQL/Mutations/SendTeamNotification.php:38`), add an explicit guard:
     ```php
     if ($team->is_scenario) {
         throw new \DomainException("Cannot send notifications for draft scenarios.");
     }
     ```
  4. *Promotion Mechanics:* Because external URLs, share links (`ShareLinkModal.vue`), cancellations (`cancellations.team_round_id`), and activity logs point to the canonical parent `team_rounds.id`, promotion **must not** swap IDs. Instead, promoting a scenario into the active round must execute inside a database transaction:
     ```php
     DB::transaction(function() use ($parentRound, $scenario) {
         // Delete parent's existing squads
         $parentRound->squads()->delete();
         // Copy scenario's squads into parent round
         foreach ($scenario->squads as $squad) {
             $this->squadManager->copySquad($squad, $parentRound);
         }
         // Optionally archive or keep scenario
     });
     ```

---

### Option 3: JSON / Snapshot State (Serialized Column)

In this architecture, scenarios are stored as serialized JSON documents (e.g. `scenarios` JSON column on `team_rounds` or in a key-value draft table).

#### Proposed Schema
```sql
ALTER TABLE team_rounds ADD COLUMN draft_scenarios JSON NULL;
```

#### Trade-Offs & Architectural Friction
- **Complete Mismatch with NemBadminton Mutation Model:**
  - `TeamFight.vue` does not maintain a client-side offline store. It mutates the server immediately:
    - Adding a player invokes `addSquadMemberByRefId(categoryId: Int!, refId: String!)`.
    - Deleting a player invokes `deleteSquadMember(id: ID!)`.
    - Drag-and-drop triggers delete + add (`TeamFight.vue:327`).
  - In a JSON document, categories and members do not have database primary keys. The existing GraphQL schema (`graphql/team-round.graphql`) cannot be used.
- **Point Re-Calculation Friction:**
  - `SquadManager::updatePoints()` and `updatePointsOnAllSquadsInTeamRound()` query the `points` and `members` tables by relational keys. Updating ranking versions across a JSON document would require writing an entire JSON deserializer, query mapper, and re-serializer.
- **Frontend Rewrite:**
  - `TeamFight.vue` would require two divergent workflows: one using granular GraphQL mutations for active rounds, and another completely separate Vue reactive store that serializes JSON blobs on draft rounds.
- **Verdict on Option 3:** Unviable. It introduces massive friction and code duplication for zero architectural benefit.

---

### Option 4: In-Round Queryable Marked Squads (`squads.scenario_id`)

In this architecture, `team_rounds` remains strictly 1:1 with real calendar rounds. Scenarios do **not** create shadow `TeamRound` rows; instead, individual `squads` are tagged with a `scenario_id` (or `scenario_name`), where `scenario_id IS NULL` denotes the default/active lineup:
```
TeamRound (Single canonical row)
   ├── Active Squad 1 (scenario_id = null)
   ├── Active Squad 2 (scenario_id = null)
   ├── Scenario Squad 1 (scenario_id = 'scenario-b-injuries')
   └── Scenario Squad 2 (scenario_id = 'scenario-b-injuries')
```

#### Proposed Schema
```sql
ALTER TABLE squads 
    ADD COLUMN scenario_id VARCHAR(24) NULL AFTER team_round_id,
    ADD INDEX squads_team_round_scenario_idx (team_round_id, scenario_id);
```

#### GraphQL Integration
In `graphql/team-round.graphql`, the `squads` field on `TeamRound` accepts an optional filter argument:
```graphql
type TeamRound {
    id: ID!
    name: String
    # Query squads filtered by scenario (defaults to active / null)
    squads(scenarioId: ID): [Squad!]! @field(resolver: "App\\GraphQL\\Resolvers\\TeamRoundSquadsResolver")
    scenarios: [TeamRoundScenario!]! @hasMany
}
```

#### Codebase Verification: Cross-Squad Validation is 100% Frontend-Driven
A critical verification was performed in the codebase regarding how cross-squad ranking rules are executed:
- **Verified Fact:** Cross-squad validation rules are **100% controlled by the frontend**.
- **Proof in Code:**
  - `TeamFight.vue:526-550`: `validateCrossSquads()` passes `wrapInTeamAndSquads(this.teamRound.squads)` to the backend mutation.
  - `helper.js:52-77`: `wrapInTeamAndSquads` serializes whatever list of `squads` is currently in the Vue component's local state.
  - `FlyCompany\TeamFight\GraphQL\Mutations\Validate.php:43-52`: The backend resolver does **not** query Eloquent or the database for squads. It directly denormalizes `$args['input']` into `FlyCompany\TeamFight\Models\Squad[]` DTOs and passes them directly to `TeamValidator::validateCrossSquadsLeagueV3($squads)`.
- **Architectural Implication:** Because validation is completely decoupled from database round queries and simply evaluates whatever array of squads the frontend provides, the frontend can query or combine any mix of marked squads (e.g. marked Squad 1 + marked Squad 2 + base Squad 3) and validate them in real-time with zero backend validator changes!

#### Trade-Offs & Comparison with Option 2
- **Advantages over Option 2:**
  - **Zero Shadow Rounds:** `team_rounds` never contains phantom records. No risk of draft rounds leaking into round pickers, calendar widgets, or cron jobs.
  - **Selective Branching:** Only the squads affected by the domino effect (e.g. Squads 1 and 2) need to be duplicated/marked. Untouched squads (3 through 8) remain active and don't need duplication if the resolver falls back to active squads.
  - **Granular Swapping:** Promoting a scenario is an atomic pointer flip (`UPDATE squads SET scenario_id = NULL ...`).
- **Considerations:**
  - `Squad::buildSortQuery()` (`app/Models/Squad.php:61`) orders squads by `order` scoped to `team_round_id`. With multiple scenarios under the same round, sorting must also scope by `where('scenario_id', $this->scenario_id)`.
  - `SquadManager::updatePointsOnAllSquadsInTeamRound()` updates points on all squads under `$teamRound->squads`. It should be adapted to accept an optional `scenarioId` filter so ranking updates on a scenario don't prematurely rewrite points on active squads.

---

## 3. Detailed Impact Analysis

### Impact on `FlyCompany\TeamFight\SquadManager`

1. **Ranking Point Recalculations (`updatePoints` & `updatePointsOnAllSquadsInTeamRound`):**
   - In Option 2, `updatePointsOnAllSquadsInTeamRound(TeamRound $teamRound, string $version)` (`local-vendor/team-fight/src/SquadManager.php:219`) operates on `$teamRound->squads`. Since a scenario is an instance of `TeamRound`, point updates work natively.
   - If a manager updates the ranking list version on a draft scenario via `TeamRoundSettingsModal.vue`, only that scenario's squad points are recalculated. The parent round and other scenarios remain untouched.
2. **Deep Copying (`copySquad`):**
   - `SquadManager::copySquad(SquadModel $sourceSquad, TeamRound $targetTeam)` (`SquadManager.php:121`) already replicates squads, categories, players, and points. This is the exact mechanism needed to branch a scenario or promote a scenario back to the active round.
3. **Validation Rules (`TeamValidator`):**
   - `TeamValidator::validateCrossSquadsLeagueV3()` and `TeamValidator::validateSquads()` (`local-vendor/team-fight/src/TeamValidator.php`) do **not** query Eloquent models directly. They accept deserialized DTOs (`FlyCompany\TeamFight\Models\Squad[]`).
   - In GraphQL, `Validate::validateCrossSquads()` (`local-vendor/team-fight/src/GraphQL/Mutations/Validate.php:43`) receives `[ValidateTeam!]!` passed from Vue's `wrapInTeamAndSquads(this.teamRound.squads)`.
   - Because `this.teamRound.squads` in Vue has the identical schema whether viewing an active round or a scenario, cross-squad validation works 100% identically with zero backend validation changes.

### Impact on Existing GraphQL Schema & Operations

1. **Query Scoping (`graphql/team-round.graphql`):**
   - The query `teamRounds(...)` (`graphql/team-round.graphql:3`) uses Lighthouse's `@paginate` directive. It must be updated to only return top-level active rounds:
     ```graphql
     extend type Query {
         teamRounds(
             clubhouseId: ID! @eq(key: "clubhouse_id"),
             order: _ @orderBy(columns: ["created_at", "updated_at", "game_date"]),
             gameDate: DateRange @whereBetween(key: "game_date")
         ) : [TeamRound!]! @paginate(defaultCount: 20, scopes: ["visibleToUser", "onlyActiveRounds"]) @guard @canResolved(ability: "view")
     }
     ```
2. **Exposing Scenarios on `TeamRound`:**
   - Add the `scenarios` relation to `TeamRound` in `graphql/team-round.graphql`:
     ```graphql
     type TeamRound {
         id: ID!
         name: String
         isScenario: Boolean! @rename(attribute: "is_scenario")
         parentTeamRound: TeamRound @belongsTo(relation: "parentRound")
         scenarios: [TeamRound!]! @hasMany(relation: "scenarios")
         squads: [Squad!]! @hasMany
         ...
     }
     ```
3. **Mutations for Scenario Lifecycle:**
   - **Branch / Create Scenario:**
     ```graphql
     createScenario(teamRoundId: ID!, name: String!): TeamRound!
     ```
     Implementation: Resolves the parent `TeamRound`, duplicates it with `parent_team_round_id = $parent->id`, `is_scenario = true`, `name = $name`, and copies squads via `$squadManager->copySquad()`.
   - **Promote Scenario:**
     ```graphql
     promoteScenario(scenarioId: ID!): TeamRound!
     ```
     Implementation: Transactionally replaces parent round's squads with the scenario's squads.
   - **Delete Scenario:**
     Existing `deleteTeamRound(id: ID!)` already deletes the round and cascades to squads.
4. **Safety Guards on Dangerous Operations:**
   - `sendTeamNotification`: Must abort if `teamRound.is_scenario === true`.
   - `teamRoundReceiver`: Should only belong to active rounds.
   - `cancellations`: Cancellation links (`cancellations.team_round_id`) must always bind to the parent `TeamRound`.

### Impact on Eloquent Models & Relations

1. **`App\Models\TeamRound`:**
   - Add relations:
     ```php
     public function parentRound(): BelongsTo
     {
         return $this->belongsTo(TeamRound::class, 'parent_team_round_id');
     }

     public function scenarios(): HasMany
     {
         return $this->hasMany(TeamRound::class, 'parent_team_round_id');
     }
     ```
   - Scope:
     ```php
     public function scopeOnlyActiveRounds(Builder $query): Builder
     {
         return $query->where('is_scenario', false);
     }
     ```
   - Adjust `scopeVisibleToUser`:
     ```php
     public function scopeVisibleToUser(Builder $query): Builder
     {
         $query->where('is_scenario', false);
         $user = Auth::user();
         if ($user && $user->primaryRole?->name === Role::PLAYER->value) {
             return $query->whereHas('squads.categories.players', function (Builder $q) use ($user) {
                 $q->where('member_ref_id', $user->player_id);
             });
         }
         return $query;
     }
     ```
2. **`App\Models\Squad`, `SquadCategory`, `SquadMember`, `SquadPoint`:**
   - Zero structural or relational changes.
   - All foreign keys, sort queries (`$squad->buildSortQuery()`), and policies remain unaltered.

### Impact on Frontend Vue (`resources/js/admin-v2/`)

1. **`TeamFight.vue`:**
   - Already accepts `teamRoundId` as a prop.
   - Header area adds a **Scenario Selector** component:
     - Dropdown / tabs listing: "Hovedopstilling (Aktiv)" + any saved scenarios ("Plan A", "Plan B - Skader").
     - "Opret nyt scenarie" button (prompts for name, triggers `createScenario`).
   - When viewing a scenario (`teamRound.isScenario === true`):
     - Displays an amber banner: *"Du redigerer en kladde ([Navn]). Ændringer påvirker ikke den officielle opstilling."*
     - The "Underret spillere" (Notify) button is hidden/disabled.
     - Adds a prominent button: **"Gør til aktiv opstilling"** (triggers `promoteScenario`).
   - All drag-and-drop, inline additions, and validations continue calling standard mutations (`addSquadMemberByRefId`, `deleteSquadMember`, etc.) against the scenario's ID.
2. **`TeamFightNotify.vue` & `ListTeamFights.vue`:**
   - Because list queries are scoped with `onlyActiveRounds`, scenarios never clutter the round overview or notification lists.

---

## 4. Addressing Wayfinder Follow-Ups (#139 & #140)

### Insight for Issue #139 (Scenario Switching & Activation UX Flow)
- Because a scenario in Option 2 has a genuine `teamRoundId`, switching between scenarios in the UI does not require custom client-side caching: it simply updates the active route/ID and refetches `TeamRoundQuery`.
- The "Named Scenarios with Active Pointer" model (Model 1 in #139) maps directly to this architecture. The canonical parent round is always the "Active" anchor, and child scenarios are branches displayed in a tab bar or dropdown header.

### Insight for Issue #140 (Scenario Granularity & Partial Promotion Semantics)
- Because squads are discrete relational rows in Option 2, **partial squad promotion** is trivial:
  - If a manager only wants to promote changes to "Hold 2" from a scenario while keeping "Hold 1" intact, a mutation `promoteSquadToActive(sourceSquadId: ID!)` can replace just the corresponding squad on the parent round.
  - Option 3 (JSON) would have made partial promotion an error-prone JSON-slicing exercise. Option 2 enables both full-round and squad-level promotion naturally.

---

## 5. Architectural Checklist for Implementation

When ready to implement under Issue #137 / #139:
- [ ] **Migration:** Add `parent_team_round_id` (nullable, FK to `team_rounds.id`, `onDelete('cascade')`) and `is_scenario` (boolean, default false, indexed) to `team_rounds`.
- [ ] **Model:** Add `scenarios()` and `parentRound()` relations on `TeamRound.php`.
- [ ] **Scope:** Add `scopeOnlyActiveRounds` and update `scopeVisibleToUser` to ensure players and list views never leak draft scenarios.
- [ ] **Guard:** Add assertion in `SendTeamNotification.php` throwing `DomainException` if `is_scenario == true`.
- [ ] **GraphQL:** Add `isScenario`, `parentTeamRound`, `scenarios` fields to `TeamRound` in `graphql/team-round.graphql`.
- [ ] **Mutations:** Implement `createScenario` and `promoteScenario` in `local-vendor/team-fight`.
- [ ] **Frontend:** Add scenario switcher and draft mode indicator to `TeamFight.vue`.
