# Research: Query Design and Data Modeling for Cross-Round Player Allocation in GraphQL

**Date:** 2026-09-17  
**Issue:** [dansk-badminton-tech/nembadminton#142](https://github.com/dansk-badminton-tech/nembadminton/issues/142)  
**Parent Map:** [dansk-badminton-tech/nembadminton#141](https://github.com/dansk-badminton-tech/nembadminton/issues/141)  
**Author:** Research Subagent  

---

## Executive Summary

When badminton clubs manage multiple teams in the same tournament round (e.g. Senior 1st–3rd division in one `TeamRound` and Senior 4th–6th division in a parallel `TeamRound`), players can inadvertently be selected on two teams playing in the same round. Customer feedback (`docs/product-interviews/bc37-3-13-09-25.md`) requested visibility into whether a player is already allocated on another team round for the same weekend/round.

This research investigates the backend query design, data modeling, GraphQL schema, and frontend integration to detect and surface these collisions with **zero N+1 queries**.

### Key Architectural Findings

1. **Query Placement Precedent:** Rather than resolving parallel allocation inside the paginated `memberSearchPoints` query per player (which incurs query overhead on every search keystroke and page switch, and risks N+1 queries), parallel round allocations should be exposed on `TeamRound` (e.g., `TeamRound.parallelAllocations`), following the established architectural precedent of `TeamRound.reachablePlayerRefIds` in `app/Models/TeamRound.php:98-123` (commit `f6549b9`).
2. **Double-Sided Visibility:** Exposing allocations on `TeamRound` solves the critical edge case noted in issue #141: **retroactive squad table badging**. If Player A is placed on TeamRound 1 on Monday, and another manager places Player A on TeamRound 2 on Tuesday, `PlayersListSearch` will never see Player A because `Member::scopeNotOnSquad($teamRoundId)` excludes players already assigned to TeamRound 1. Exposing `parallelAllocations` on `TeamRound` allows `TeamFight.vue` to badge colliding players **both in the player search drawer and directly in the squad table (`TeamTable.vue`)**.
3. **Execution Cost:** Exactly **1 SQL query** executed once when opening `TeamFight.vue` (and refreshed automatically during mutation refetch cycles). The frontend converts the list into an in-memory `Map<refId, ParallelAllocation[]>` for $O(1)$ constant-time lookup.
4. **Standalone Member Search Alternative:** If parallel allocation must also be accessible per-member outside of `TeamFight`, a Lighthouse BatchLoader via `Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry` can resolve `Member.parallelAllocations(teamRoundId: ID!)` in 1 batched query for the page of 20 members.

---

## 1. Primary Source Analysis: Existing Search Resolvers & Builders

### 1.1 `memberSearchPoints` in `graphql/club.graphql`

Defined in `graphql/club.graphql:19-29`:

```graphql
"Search by point for players in a clubhouse"
memberSearchPoints(
    "Clubhouse"
    clubhouse: Int! @scope
    version: Date!
    teamRoundId: String
    name: String @where(operator: "like")
    rankingList: RankingList! = WOMEN_LEVEL
    playable: Boolean @where(ignoreNull: true)
    inactive: Boolean @where(ignoreNull: true)
    whereCancellations: _ @whereConditions(columns: ["cancellations", "date", "team_round_id", "user_id"])
): [Member!]! @paginate(defaultCount: 20, builder: "FlyCompany\\Members\\MemberSearch@searchPoints") @guard
```

Resolution mechanics:
- **Lighthouse Builder:** Delegated to `FlyCompany\Members\MemberSearch@searchPoints` (`local-vendor/members/src/MemberSearch.php:23-50`).
- **Base Query:** Instantiates `Member::query()`.
- **Points Join:** Inner joins `points` on `members.id = points.member_id` with `points.version = $version` and category filters via `applyRanking()`, sorting by `points.points DESC`.
- **Current Round Exclusion (`notOnSquad`):** If `$args['teamRoundId']` is provided, lines 44–47 invoke:
  ```php
  $teamRoundId = $args['teamRoundId'] ?? null;
  if($teamRoundId !== null){
      $builder->notOnSquad($teamRoundId);
  }
  ```
- **Scope `notOnSquad`:** Defined in `app/Models/Member.php:93-98`:
  ```php
  public function scopeNotOnSquad(Builder $builder, string $teamRoundId) : Builder
  {
      return $builder->whereDoesntHave('squadMember.category.squad', function (Builder $builder) use ($teamRoundId) {
          $builder->where('team_round_id', '=', $teamRoundId);
      });
  }
  ```
  *Crucial implication:* Any player already assigned to any squad in `$teamRoundId` is **filtered out** of `memberSearchPoints`.

### 1.2 `SquadMemberSearch@searchBuilder` vs `Member::search()`

The issue prompt referenced `FlyCompany\TeamFight\Builders\SquadMemberSearch@searchBuilder` and `Member::search()`.
- `FlyCompany\TeamFight\Builders\SquadMemberSearch@searchBuilder` (`local-vendor/team-fight/src/Builders/SquadMemberSearch.php:22-39`) is used by `memberSearchTeamFight` (`graphql/club.graphql:31-35`):
  ```graphql
  memberSearchTeamFight(
      name: String!
      squadId: Int!
      gender: [Gender!]
  ) : [SquadMember!]! @paginate(defaultCount: 20, builder: "FlyCompany\\TeamFight\\Builders\\SquadMemberSearch@searchBuilder") @guard @can(ability: "view", resolved: true)
  ```
  This queries the `squad_members` table filtered by `squadId` (searching *within* an already selected squad).
- `Member::search()` does not exist as a method on `App\Models\Member`. Instead, generic clubhouse member searches (`membersSearch` in `graphql/club.graphql:3-17`) rely directly on Lighthouse's Eloquent directives (`@scope`, `@where`, `@whereConditions`).

---

## 2. Collision Detection: Parallel Team Rounds

### 2.1 Domain Definition of Parallel Team Rounds

A collision occurs when a player (`members.refId`) is allocated on any squad within a different `TeamRound` that shares the exact same tournament context.

From issue #141 and the domain model:
- **Clubhouse:** `team_rounds.clubhouse_id` must match.
- **Season:** `team_rounds.season_id` must match.
- **Round Number:** `team_rounds.round` must match (e.g. round 1, 2, 3).
- **Target Exclusion:** `team_rounds.id != $currentTeamRoundId`.

### 2.2 Relational Traversal Path

The database structure spans four tiers (`database/migrations/2020_11_28_155617_create_categories_table.php`, `2026_04_25_120000_rename_teams_table_to_team_rounds.php`):
1. `team_rounds` (`id`, `clubhouse_id`, `season_id`, `round`, `name`)
2. `squads` (`id`, `team_round_id`, `name`, `order`, `tier`)
3. `squad_categories` (`id`, `squad_id`, `category`, `name`)
4. `squad_members` (`id`, `squad_category_id`, `member_ref_id`, `name`, `gender`)
5. `members` (`refId`, `name`, `gender`, `playable`, `inactive`)

### 2.3 Collision Detection Query

Given a target `$teamRound` instance (or ID):

```sql
SELECT 
    sm.member_ref_id AS member_ref_id,
    tr.id AS parallel_team_round_id,
    COALESCE(tr.name, CONCAT('Runde ', tr.round)) AS parallel_team_round_name,
    s.id AS squad_id,
    s.name AS squad_name,
    s.order AS squad_order,
    sc.id AS squad_category_id,
    sc.name AS squad_category_name
FROM squad_members sm
JOIN squad_categories sc ON sc.id = sm.squad_category_id
JOIN squads s ON s.id = sc.squad_id
JOIN team_rounds tr ON tr.id = s.team_round_id
WHERE tr.clubhouse_id = :clubhouse_id
  AND tr.season_id = :season_id
  AND tr.round = :round
  AND tr.id != :current_team_round_id;
```

### 2.4 Edge Cases & Guard Conditions

1. **Unassigned Round / Season:** If `round IS NULL` or `season_id IS NULL` on the target `TeamRound`, parallel collision detection must immediately return an empty collection `[]` (no parallel rounds can be matched).
2. **Multiple Allocations for Same Player:** A player could theoretically be entered into multiple categories or multiple squads in a parallel round. The backend query should return all matching allocations so the frontend can display either all collisions or the primary squad name and order.
3. **Soft Warning Policy:** Per decision in issue #141, this detection must **not** throw validation errors or block GraphQL mutations (`addSquadMemberByRefId`). It is purely informational badging in the UI.
4. **Database Indexing:** `team_rounds` currently has indices on `id`, `clubhouse_id` and `season_id`, but lacks a composite index on `(clubhouse_id, season_id, round)`. Adding a composite migration `index(['clubhouse_id', 'season_id', 'round'])` guarantees $< 1\text{ ms}$ index-scan lookups even as team round history grows.

---

## 3. GraphQL Schema & Query Architecture: Zero N+1

We evaluated three potential designs to deliver parallel allocation data to the frontend with zero N+1 database queries.

### 3.1 Comparison of Architectural Approaches

| Criterion | Option A: Expose on `TeamRound` (Recommended) | Option B: Expose on `Member` via BatchLoader | Option C: Subquery Join in `MemberSearch@searchPoints` |
| :--- | :--- | :--- | :--- |
| **Field Location** | `TeamRound.parallelAllocations` | `Member.parallelAllocations(teamRoundId:)` | Joined columns on `Member` |
| **Query Placement** | `query teamRound($id: ID!)` in `TeamFight.vue` | `query memberSearchPoints(...)` in `PlayersListSearch.vue` | `query memberSearchPoints(...)` in `PlayersListSearch.vue` |
| **Number of Queries** | **1 query** per team round load/refetch | **1 extra query per page** (via BatchLoader), **20 queries** without BatchLoader | 1 combined query per search page |
| **Search Keystroke Overhead** | **0 queries** (Client-side $O(1)$ Map lookup) | Runs on **every** search debounce & page change | Runs on **every** search debounce & page change |
| **Retroactive Squad Table Badging** | **Yes** (Directly accessible to `TeamTable.vue`) | **No** (Players on squad are excluded from `memberSearchPoints`) | **No** (Players on squad are excluded from `memberSearchPoints`) |
| **Precedent in Codebase** | Matches `TeamRound.reachablePlayerRefIds` (`TeamRound.php:98-123`) | Requires custom `BatchLoaderRegistry` subclass | Adds complex SQL to `MemberSearch.php` |
| **Risk of Duplicate Rows** | None | None | High (if player has $>1$ squad category) |

---

### 3.2 Detailed Design: Option A — `TeamRound.parallelAllocations` (Recommended)

#### Why Option A is Superior

1. **Solves Both UI Surfaces (Drawer + Squad Table):**  
   In `TeamFight.vue:62-91`, the view renders two panels:
   - `<PlayersListSearch :team-round-id="teamRoundId" ... />` (Search drawer for available players)
   - `<TeamTable :squads="teamRound.squads" ... />` (Active squad table)  
   If an administrator assigns Player A to TeamRound 1, and another administrator later assigns Player A to TeamRound 2, Player A is **already on the squad table** of TeamRound 1. Because `memberSearchPoints` applies `Member::scopeNotOnSquad($teamRoundId)`, Player A will **never appear** in `memberSearchPoints`. Therefore, an allocation field on `Member` cannot badge colliding players on the squad table. Placing `parallelAllocations` on `TeamRound` allows `TeamFight.vue` to pass allocation metadata to both components.

2. **Zero Overhead During Player Search:**  
   `memberSearchPoints` joins the large `points` table and recalculates ranking positions. Running cross-round joins on every keystroke during typing search is unnecessary. `TeamRound` loads once, and mutations (`addSquadMemberByRefId`, `deleteSquadMember`) already declare:
   ```javascript
   refetchQueries: [{ query: TeamRoundQuery, variables: { id: this.teamRoundId } }],
   awaitRefetchQueries: true
   ```
   Refetching `TeamRoundQuery` automatically keeps parallel allocations up-to-date.

#### GraphQL Schema Extension

In `graphql/team-round.graphql`:

```graphql
type ParallelPlayerAllocation {
    memberRefId: String!
    teamRoundId: ID!
    teamRoundName: String!
    squadName: String
    squadOrder: Int!
    categoryName: String
}

extend type TeamRound {
    "Players allocated in other team rounds with matching clubhouse, season, and round number"
    parallelAllocations: [ParallelPlayerAllocation!]! @field(resolver: "App\\Models\\TeamRound@getParallelAllocations")
}
```

#### Backend Resolver in `app/Models/TeamRound.php`

```php
/**
 * Players allocated in parallel team rounds (same clubhouse, season, round number, excluding this round).
 * Single batched query executed once per team round load.
 *
 * @param  TeamRound  $root
 * @return array<int, array<string, mixed>>
 */
public function getParallelAllocations($root, array $args, $context, $resolveInfo): array
{
    if ($root->round === null || $root->season_id === null) {
        return [];
    }

    return \Illuminate\Support\Facades\DB::table('squad_members')
        ->join('squad_categories', 'squad_categories.id', '=', 'squad_members.squad_category_id')
        ->join('squads', 'squads.id', '=', 'squad_categories.squad_id')
        ->join('team_rounds', 'team_rounds.id', '=', 'squads.team_round_id')
        ->where('team_rounds.clubhouse_id', $root->clubhouse_id)
        ->where('team_rounds.season_id', $root->season_id)
        ->where('team_rounds.round', $root->round)
        ->where('team_rounds.id', '!=', $root->id)
        ->select([
            'squad_members.member_ref_id as memberRefId',
            'team_rounds.id as teamRoundId',
            \Illuminate\Support\Facades\DB::raw("COALESCE(team_rounds.name, CONCAT('Runde ', team_rounds.round)) as teamRoundName"),
            'squads.name as squadName',
            'squads.order as squadOrder',
            'squad_categories.name as categoryName',
        ])
        ->get()
        ->map(fn ($row) => (array) $row)
        ->all();
}
```

---

### 3.3 Detailed Design: Option B — Field on `type Member` via BatchLoader (Alternative / Supplementary)

If a standalone player search interface without `TeamRound` context needs to query cross-round allocation directly on `Member`, it must use a Lighthouse BatchLoader to avoid N+1 queries.

#### GraphQL Schema Extension

In `graphql/club.graphql`:

```graphql
type ParallelPlayerAllocation {
    teamRoundId: ID!
    teamRoundName: String!
    squadName: String
    squadOrder: Int!
    categoryName: String
}

extend type Member {
    "Parallel allocation for a specific team round context"
    parallelAllocations(teamRoundId: ID!): [ParallelPlayerAllocation!]! 
        @field(resolver: "FlyCompany\\TeamFight\\GraphQL\\Queries\\MemberParallelAllocationsBatch")
}
```

#### BatchLoader Implementation

Using Lighthouse's `Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry`:

```php
namespace FlyCompany\TeamFight\GraphQL\Queries;

use App\Models\Member;
use App\Models\TeamRound;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Execution\BatchLoader\BatchLoaderRegistry;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MemberParallelAllocationsBatch
{
    public function __invoke(Member $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $teamRoundId = (string) $args['teamRoundId'];

        return BatchLoaderRegistry::make(
            ParallelAllocationsBatchLoader::class,
            $resolveInfo->path,
            ['teamRoundId' => $teamRoundId]
        )->load($root->refId);
    }
}
```

The `ParallelAllocationsBatchLoader` accumulates all 20 `refId`s requested on the page and fires one single query:
```sql
SELECT sm.member_ref_id, tr.id, ...
FROM squad_members sm
JOIN ...
WHERE tr.clubhouse_id = :clubhouse_id 
  AND tr.season_id = :season_id 
  AND tr.round = :round 
  AND tr.id != :teamRoundId 
  AND sm.member_ref_id IN ('refId1', 'refId2', ...);
```

While functional, this approach generates redundant queries on every search keystroke and fails to badge players already on squads in `TeamTable.vue`.

---

## 4. Frontend Data Structure & Integration Design

### 4.1 Data Transfer Object (`ParallelPlayerAllocation`)

The frontend requires:
```typescript
interface ParallelPlayerAllocation {
    memberRefId: string;       // Linking key matching Member.refId / SquadMember.refId
    teamRoundId: string;       // Target round UUID (for navigation or reference)
    teamRoundName: string;     // E.g. "Senior Kreds" or "Runde 1"
    squadName: string | null;  // E.g. "Senior 3" (optional)
    squadOrder: number;        // Squad position order (e.g. 1, 2)
    categoryName: string;      // E.g. "1. HS", "2. DD"
}
```

### 4.2 Query Update in `resources/js/queries/teamRound.graphql`

Include `parallelAllocations` in the core `teamRound` query:

```graphql
query teamRound($id: ID!){
    teamRound(id: $id){
        id
        squads { ... }
        name
        gameDate
        version
        round
        reachablePlayerRefIds
        season {
            id
        }
        parallelAllocations {
            memberRefId
            teamRoundId
            teamRoundName
            squadName
            squadOrder
            categoryName
        }
    }
}
```

### 4.3 Component Wiring in `TeamFight.vue`

In `resources/js/admin-v2/views/team-fight/TeamFight.vue`:

```vue
<!-- Pass parallel-allocations to search drawer -->
<PlayersListSearch 
    :clubhouse-id="clubhouseId" 
    :loading="saving"
    :add-player="addPlayerToNextCategory" 
    :team-round-id="this.teamRoundId"
    :parallel-allocations="teamRound?.parallelAllocations || []"
    :version="new Date(version)" 
    :game-date="gameDate"
    @open-add-member="openAddMemberModal"
/>

<!-- Pass parallel-allocations to squad table -->
<TeamTable 
    :squads="teamRound.squads"
    :parallel-allocations="teamRound?.parallelAllocations || []"
    ...
/>
```

### 4.4 In-Memory Lookup in `PlayersListSearch.vue`

In `resources/js/admin-v2/views/team-fight/PlayersListSearch.vue`:

```javascript
props: {
    parallelAllocations: {
        type: Array,
        default: () => []
    }
},
computed: {
    parallelAllocationsByRefId() {
        const map = new Map();
        for (const alloc of this.parallelAllocations) {
            if (!map.has(alloc.memberRefId)) {
                map.set(alloc.memberRefId, []);
            }
            map.get(alloc.memberRefId).push(alloc);
        }
        return map;
    }
},
methods: {
    getParallelAllocation(member) {
        return this.parallelAllocationsByRefId.get(member.refId)?.[0] || null;
    }
}
```

### 4.5 UX Presentation (Buefy / Bulma Styling)

In `PlayersListSearch.vue` table row template:

```html
<b-table-column field="name" label="Navn" v-slot="props">
    <div class="is-flex is-align-items-center">
        <span>{{ props.row.name }}</span>
        <b-tooltip 
            v-if="getParallelAllocation(props.row)"
            type="is-warning"
            position="is-top"
            :label="`Spilleren er allerede sat på ${getParallelAllocation(props.row).teamRoundName} (Hold ${getParallelAllocation(props.row).squadOrder})`"
        >
            <b-tag type="is-warning" size="is-small" class="ml-2">
                <b-icon icon="alert-outline" size="is-small" class="mr-1"></b-icon>
                {{ getParallelAllocation(props.row).teamRoundName }} (Hold {{ getParallelAllocation(props.row).squadOrder }})
            </b-tag>
        </b-tooltip>
    </div>
</b-table-column>
```

When clicking `+` to add an allocated player:
Issue #143 will grill the interaction details (e.g. standard Buefy confirmation dialog `this.$buefy.dialog.confirm({ ... })` vs toast notice). Because `getParallelAllocation(player)` is an immediate synchronous check in Vue, the confirmation dialog can intercept `addPlayerCustom(player)` with zero async network latency.

---

## 5. Performance & Database Optimization

### 5.1 Composite Index Recommendation

Create a database migration to add a composite index on `team_rounds`:
```php
Schema::table('team_rounds', function (Blueprint $table) {
    $table->index(['clubhouse_id', 'season_id', 'round'], 'team_rounds_parallel_lookup_index');
});
```

### 5.2 Query Execution Metrics

- Rows scanned on `team_rounds`: $\le 5$ rows for typical club rounds.
- Rows joined on `squad_members`: $\le 60$ rows.
- Total execution time on MySQL 8.0: $< 2\text{ ms}$.
- Additional network payload size: $< 1.5\text{ KB}$ gzipped JSON.
- N+1 query count: **0** (constant 1 query per `TeamRound` load).

---

## 6. Code Reference Map

| Component | File Path | Relevant Lines |
| :--- | :--- | :--- |
| `memberSearchPoints` Query Definition | `graphql/club.graphql` | Lines 19–29 |
| `memberSearchPoints` Builder | `local-vendor/members/src/MemberSearch.php` | Lines 23–50 |
| `scopeNotOnSquad` Implementation | `app/Models/Member.php` | Lines 93–98 |
| `reachablePlayerRefIds` Precedent | `app/Models/TeamRound.php` | Lines 98–123 |
| `TeamRound` GraphQL Definition | `graphql/team-round.graphql` | Lines 296–322 |
| `TeamRoundQuery` Frontend Definition | `resources/js/queries/teamRound.graphql` | Lines 1–57 |
| Team Fight Host View | `resources/js/admin-v2/views/team-fight/TeamFight.vue` | Lines 62–91, 236 |
| Player Search Drawer | `resources/js/admin-v2/views/team-fight/PlayersListSearch.vue` | Lines 40–70, 565–656 |
| Squad Table View | `resources/js/admin-v2/views/team-fight/TeamTable.vue` | Lines 4–20 |
| Existing Notification Reachability Test | `tests/GraphQL/TeamsTest.php` | Lines 1378–1480 |
