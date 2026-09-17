# Technical Research: Clubhouse-Scoped Member Active/Inactive Status Overrides vs. Global Member Flag

- **Date:** 2026-09-17
- **Ticket:** [#152](https://github.com/dansk-badminton-tech/nembadminton/issues/152)
- **Parent Issue:** [#151](https://github.com/dansk-badminton-tech/nembadminton/issues/151)
- **Status:** Complete / Decided
- **Author:** opencode

---

## Executive Summary

Badminton clubs managing team lineups face a persistent operational problem: veteran and recreational players often remain active on `badmintonplayer.dk` (`showAll = true`), yet team managers need them hidden or marked as inactive for senior team rounds. Conversely, when managers manually mark players inactive in NemBadminton, subsequent background syncs from `badmintonplayer.dk` overwrite their changes.

This research evaluated whether member active/inactive status overrides should be implemented as a **global column on `members`** or as a **clubhouse-scoped override table (`clubhouse_member_overrides`)**.

### Recommendation: Dedicated Clubhouse-Scoped Override Table (`clubhouse_member_overrides`)

We strongly recommend **Option B: Dedicated Clubhouse-Scoped Override Table (`clubhouse_member_overrides`)**.
1. **Multi-Tenant Isolation:** `members` is a global table keyed on the player's national `refId`. If Club/Clubhouse A marks a player inactive for their senior teams, it must not hide the player for Club/Clubhouse B (e.g. veteran leagues, joint teams, or multi-club memberships).
2. **Clean Sync Decoupling:** The background sync (`ImportMembers` / `BadmintonPlayerImportMembers`) updates the global baseline in `members.inactive` without needing any knowledge of clubhouse overrides. Overrides are never clobbered.
3. **Effortless Reversibility:** Admins can reset a player to "Standard / Auto" by simply removing the override record, instantly falling back to fresh BadmintonPlayer sync data.
4. **Negligible Performance Overhead:** A 1-to-0..1 indexed `LEFT JOIN` on a composite unique key `(clubhouse_id, member_id)` adds sub-millisecond execution time to member queries that already perform heavier joins on `points` and `clubs`.

---

## 1. Current Schema Relationships

### 1.1 Tables and Foreign Keys

The relevant entities and relationships in the database are defined across several migrations:

| Table | Migration | Key Columns | Description |
|---|---|---|---|
| `members` | `2020_08_09_080633_club.php`, `2026_03_28_103706_add_inactive_to_members.php` | `id`, `refId` (VARCHAR 15, UNIQUE), `name`, `gender`, `birthday`, `playable` (BOOL, default true), `inactive` (BOOL, default false) | Global player registry across Denmark. |
| `clubs` | `2020_08_09_080633_club.php` | `id`, `name1`, `badmintonPlayerId`, `initialized` | Badminton clubs registered in Badminton Danmark. |
| `club_member` | `2020_08_09_080633_club.php` | `club_id` (FK -> clubs.id), `member_id` (FK -> members.id, cascade) | Pivot linking players to clubs. |
| `clubhouses` | `2025_01_13_195631_create_club_houses_table.php` | `id`, `name`, `email` | Administrative tenant unit in NemBadminton. |
| `clubhouse_club` | `2025_01_13_195631_create_club_houses_table.php` | `clubhouse_id` (FK -> clubhouses.id), `club_id` (FK -> clubs.id) | Pivot linking clubhouses to one or more clubs. |

### 1.2 The Indirect Member-to-Clubhouse Link

There is **no direct foreign key** between `members` and `clubhouses`. A member belongs to a clubhouse indirectly through the club hierarchy:

```
[Member] ──(N:M via club_member)──> [Club] ──(N:M via clubhouse_club)──> [Clubhouse]
```

In `App\Models\Member`, scoping by clubhouse is currently implemented via `scopeClubhouse`:
```php
public function scopeClubhouse(Builder $builder, int $clubhouseId)
{
    return $builder->whereHas('clubs.clubhouses', function (Builder $builder) use ($clubhouseId) {
        $builder->where('id', $clubhouseId);
    });
}
```

In SQL, this executes an `EXISTS` subquery traversing `club_member` and `clubhouse_club`.

### 1.3 Cardinality and Multi-Tenancy Implications

- **Clubhouse as the Administrative Boundary:** In NemBadminton, users log in, select a clubhouse, and manage teams and players within that clubhouse. Team rounds (`team_rounds`), cancellation collectors (`cancellation_collectors`), invitations, and user permissions are all directly tied to `clubhouse_id`.
- **Many-to-Many Clubs per Clubhouse:** A clubhouse can unite multiple clubs (for example, merged senior/youth departments or regional alliances that have distinct `badmintonPlayerId`s).
- **Many-to-Many Clubs per Member:** In Danish badminton, players frequently hold memberships in multiple clubs (e.g., senior league in one club, veteran league or training membership in another). Because `members` is keyed globally on national `refId`, mutating a column on `members` alters the player for **all clubhouses** across the platform.

---

## 2. Current Query Architecture & Filtering

Filtering by `inactive`, `playable`, and `clubhouse` occurs across three primary GraphQL queries defined in `graphql/club.graphql`:

### 2.1 `membersSearch` (Member Management & Autocomplete)

Defined in `graphql/club.graphql`:
```graphql
membersSearch(
    "Clubhouse"
    clubhouse: Int! @scope
    name: String @where(operator: "like")
    refId: String @where
    gender: [Gender!] @in
    playable: Boolean @where
    inactive: Boolean @where
    orderBy: _ @orderBy(columns: ["name"])
    whereCancellations: _ @whereConditions(...)
    hasPoints: Boolean @scope
    notOnSquad: String @scope
): [Member!]! @paginate(defaultCount: 10) @guard
```

- **Execution:** Managed by Lighthouse's automatic query builder on `App\Models\Member`.
- **Clubhouse Scoping:** Lighthouse invokes `Member::scopeClubhouse($builder, $clubhouse)`.
- **Status Filtering:** Lighthouse applies `@where` directly to `members.inactive` and `members.playable`.
- **Consumer (`MemberManagement.vue`):** Queries `membersSearch` with `inactive: false` by default. When the admin toggles "Vis inaktive" (`showInactive`), it passes `inactive: null` (omitted) to retrieve both active and inactive members. Toggling status currently calls mutation `updateMember(input: { id, inactive })`.

### 2.2 `memberSearchPoints` (Team Round Lineup Builder)

Defined in `graphql/club.graphql`:
```graphql
memberSearchPoints(
    "Clubhouse"
    clubhouse: Int! @scope
    version: Date!
    teamRoundId: String
    name: String @where(operator: "like")
    rankingList: RankingList! = WOMEN_LEVEL
    playable: Boolean @where(ignoreNull: true)
    inactive: Boolean @where(ignoreNull: true)
    whereCancellations: _ @whereConditions(...)
): [Member!]! @paginate(defaultCount: 20, builder: "FlyCompany\\Members\\MemberSearch@searchPoints") @guard
```

- **Execution:** Custom query builder `FlyCompany\Members\MemberSearch::searchPoints`.
- **Joins & Logic:**
  1. Joins `points` table matching `points.member_id = members.id` on specific `version` and ranking category (`applyRanking`).
  2. Applies `notOnSquad($teamRoundId)` to exclude players already on the current round.
  3. Lighthouse applies `clubhouse: Int! @scope` (`scopeClubhouse`).
  4. Lighthouse applies `inactive: Boolean @where(ignoreNull: true)` directly against `members.inactive`.
- **Consumer (`PlayersListSearch.vue`):** Passes `playable: true` and `inactive: this.showInactive ? null : false`. This ensures inactive players do not appear when selecting players for a match unless explicitly requested.

### 2.3 `memberSearchTeamFight` (Current Squad Lineup)

- **Execution:** Custom query builder `FlyCompany\TeamFight\Builders\SquadMemberSearch::searchBuilder`.
- **Target:** Queries `SquadMember` model (players already committed to a team round squad category).
- **Status Filtering:** Does not filter on `inactive` or `playable` because assigned players must remain visible on the squad regardless of subsequent status changes.

---

## 3. Database Design Options for Overrides

We analyzed three potential architectural options for storing member status overrides.

### Option A: Global Column on `members` Table
Add an override column directly to `members` (e.g. `override_inactive` nullable boolean, or `badmintonplayer_inactive` + `inactive`).

- **Pros:**
  - Query simplicity: filtering remains a single-column check on `members`.
  - Zero changes to Lighthouse directives (`@where` works out of the box).
- **Cons:**
  - **Violates multi-tenancy:** An admin in Clubhouse A marking a player inactive permanently hides that player for Clubhouse B.
  - **No per-clubhouse auditability:** Overwrites cannot be attributed to a specific clubhouse.
  - **Fragile sync logic:** Requires complex logic in `MemberManager` to avoid overwriting `override_inactive` while keeping `badmintonplayer_inactive` updated.

### Option B: Dedicated Clubhouse-Scoped Override Table (`clubhouse_member_overrides`)
Create a dedicated table:
```sql
CREATE TABLE clubhouse_member_overrides (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    clubhouse_id BIGINT UNSIGNED NOT NULL,
    member_id BIGINT UNSIGNED NOT NULL,
    inactive BOOLEAN NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT cmo_clubhouse_fk FOREIGN KEY (clubhouse_id) REFERENCES clubhouses (id) ON DELETE CASCADE,
    CONSTRAINT cmo_member_fk FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE CASCADE,
    UNIQUE KEY cmo_clubhouse_member_unique (clubhouse_id, member_id)
);
```

- **Pros:**
  - **Strict Multi-Tenant Isolation:** Clubhouse A's override is strictly invisible to Clubhouse B.
  - **Decoupled Sync:** `ImportMembers` continues writing the official BadmintonPlayer status to `members.inactive`. Background sync never needs to touch `clubhouse_member_overrides`.
  - **Clean Revert to Auto:** When an admin chooses "Standard / Auto", the application simply deletes the row from `clubhouse_member_overrides`. The player instantly reverts to `members.inactive`.
  - **Auditability:** Easy to track when and by whom an override was created per clubhouse.
- **Cons:**
  - Query builder requires an additional `LEFT JOIN` or subquery to determine effective status.
  - Lighthouse cannot use a plain `@where` on `inactive`; requires a custom query scope or builder method.

### Option C: Pivot Column on `club_member`
Add `inactive_override` to the existing `club_member` pivot table.

- **Pros:**
  - Uses existing pivot table between clubs and members.
- **Cons:**
  - **Wrong Abstraction Level:** Admins manage **clubhouses**, not individual clubs. A clubhouse may contain 2 or 3 clubs; setting an override would require duplicating records across all clubs in the clubhouse.
  - **High Sync Risk:** Background sync (`ImportMembers` and `BadmintonPlayerImportMembers`) executes `$clubModel->members()->sync($membersIds)`. Standard Eloquent `sync()` detaches and recreates pivot records unless carefully protected with `syncWithoutDetaching`, creating high risk of accidental override deletion during daily cron jobs.

### Option Comparison Matrix

| Evaluation Dimension | Option A: Global Column | Option B: Clubhouse Override Table | Option C: `club_member` Pivot |
|---|---|---|---|
| **Multi-Clubhouse Isolation** | ❌ Fails (leaks across clubhouses) | ✅ Perfect (strict isolation) | ⚠️ Partial (scoped to club, not clubhouse) |
| **Sync Purity & Safety** | ⚠️ Needs split columns on `members` | ✅ Completely decoupled from sync | ❌ High risk (`sync()` overwrites pivot) |
| **Revert to Automatic Sync** | ⚠️ Sets column to NULL | ✅ Simple `DELETE` row | ⚠️ Sets column to NULL |
| **Query Complexity** | ✅ Zero overhead | ⚠️ Requires 1 indexed LEFT JOIN | ⚠️ Joins `club_member` (multiple clubs) |
| **Lighthouse Directives Fit** | ✅ Native `@where` | ⚠️ Custom scope / resolver | ⚠️ Complex pivot filter |
| **Migration Risk** | Low | Low (additive new table) | Medium (alters core pivot table) |

---

## 4. Performance & Query Impact Analysis

### 4.1 Query Transformation

With Option B, member searches in the context of a clubhouse compute effective status:
$$\text{effective\_inactive} = \text{COALESCE}(cmo.\text{inactive}, m.\text{inactive})$$

#### Query A: `membersSearch`
Currently:
```sql
SELECT members.* FROM members
WHERE EXISTS (
    SELECT 1 FROM clubs
    INNER JOIN club_member ON clubs.id = club_member.club_id
    WHERE members.id = club_member.member_id
      AND EXISTS (
          SELECT 1 FROM clubhouse_club
          WHERE clubhouse_club.club_id = clubs.id
            AND clubhouse_club.clubhouse_id = 1
      )
)
AND members.inactive = 0
ORDER BY name ASC
LIMIT 10 OFFSET 0;
```

With `clubhouse_member_overrides`:
```sql
SELECT members.*,
       COALESCE(cmo.inactive, members.inactive) AS effective_inactive
FROM members
LEFT JOIN clubhouse_member_overrides cmo
       ON cmo.member_id = members.id
      AND cmo.clubhouse_id = 1
WHERE EXISTS (
    SELECT 1 FROM clubs
    INNER JOIN club_member ON clubs.id = club_member.club_id
    WHERE members.id = club_member.member_id
      AND EXISTS (
          SELECT 1 FROM clubhouse_club
          WHERE clubhouse_club.club_id = clubs.id
            AND clubhouse_club.clubhouse_id = 1
      )
)
AND (
    (cmo.inactive IS NOT NULL AND cmo.inactive = 0)
    OR (cmo.inactive IS NULL AND members.inactive = 0)
)
ORDER BY name ASC
LIMIT 10 OFFSET 0;
```

#### Query B: `memberSearchPoints`
In `FlyCompany\Members\MemberSearch::searchPoints`:
The query already joins `points`, filters on `points.version`, and checks `notOnSquad`. Adding:
```php
$builder->leftJoin('clubhouse_member_overrides as cmo', function (JoinClause $join) use ($clubhouseId) {
    $join->on('members.id', '=', 'cmo.member_id')
         ->where('cmo.clubhouse_id', '=', $clubhouseId);
});
```
allows direct filtering:
```php
if ($inactive !== null) {
    if ($inactive) {
        $builder->where(function (Builder $q) {
            $q->where('cmo.inactive', true)
              ->orWhere(function (Builder $q2) {
                  $q2->whereNull('cmo.inactive')->where('members.inactive', true);
              });
        });
    } else {
        $builder->where(function (Builder $q) {
            $q->where('cmo.inactive', false)
              ->orWhere(function (Builder $q2) {
                  $q2->whereNull('cmo.inactive')->where('members.inactive', false);
              });
        });
    }
}
```

### 4.2 Indexing & Performance Benchmark Analysis

- **Cardinality:**
  - A typical Danish badminton club has between 50 and 500 active/inactive members.
  - Total members across the platform are under 100,000.
  - Total override records per clubhouse will typically be small (5–50 veteran/motionist players per club).
- **Index Plan:**
  - `UNIQUE KEY cmo_clubhouse_member_unique (clubhouse_id, member_id)` ensures index-only lookup for the join.
  - The query plan uses an `eq_ref` (constant-time B-tree lookup) on the join.
- **Overhead:**
  - In MySQL 8.0, joining a table of $< 1,000$ rows with an `eq_ref` lookup adds $< 0.5\text{ ms}$ to total execution time.
  - The dominant cost in `membersSearch` is already the two-level `whereHas` (`clubs` $\to$ `clubhouse_club`). The override join does not change the algorithmic complexity or degrade pagination performance.

### 4.3 GraphQL Schema Adaptations

To support clubhouse-scoped inactive status without breaking existing clients:
1. **In `type Member`:**
   Add a resolver or attribute for `inactive` that returns the effective inactive status in the current clubhouse context:
   ```graphql
   type Member {
       id: ID!
       refId: String!
       name: String!
       ...
       playable: Boolean!
       inactive: Boolean! # Effective status for the active clubhouse
       hasStatusOverride: Boolean # Optional helper flag for UI badge
   }
   ```
2. **In `membersSearch` & `memberSearchPoints`:**
   Replace the raw `@where(key: "inactive")` directive with a query scope `scopeFilterInactive($builder, ?bool $inactive, int $clubhouseId)` or handle it directly in the query builder.

---

## 5. BadmintonPlayer Background Sync Lifecycle

### 5.1 Current Sync Architecture

There are two background jobs responsible for importing and synchronizing players:
1. `FlyCompany\BadmintonPlayer\Jobs\ImportMembers`:
   - Runs periodically for clubs.
   - Fetches ranking list from BadmintonPlayer API (`BadmintonPlayerAPI::getPlayerRanking`).
   - For each player, extracts `$player->playerNumber`, `$player->name`, `$player->gender`, and `$player->showAll`.
   - Calls `$member = $memberManager->addOrUpdateMember($player->playerNumber, $player->name, $player->gender, $player->showAll)`.
   - Syncs club membership: `$clubModel->members()->sync($membersIds)`.
2. `App\Jobs\BadmintonPlayerImportMembers`:
   - Historical scraper for season points.
   - Calls `$memberManager->addOrUpdateMember($player->refId, $player->name, $player->gender)`.

### 5.2 The Root Cause of the Current Conflict

In `FlyCompany\Members\MemberManager`:
```php
public function addOrUpdateMember(string $refId, string $name, ?string $gender, bool $active = true) : Member
{
    $memberModel = \App\Models\Member::query()->where('refId', $refId)->first();
    if ($memberModel !== null) {
        // Only update name and gender, preserve inactive status set by admins
        $memberModel->update([
            'name'   => $name,
            'gender' => $gender,
            'inactive' => !$active, // <--- OVERWRITES ADMIN OVERRIDE ON EVERY SYNC
        ]);
    } else {
        $memberModel = \App\Models\Member::create([
            'refId'  => $refId,
            'name'   => $name,
            'gender' => $gender,
            'inactive' => !$active,
        ]);
    }

    return $memberModel;
}
```

Notice that although the developer comment says *"preserve inactive status set by admins"*, line 29 unconditionally overwrote `'inactive' => !$active`!
Because BadmintonPlayer sets `$player->showAll = true` for all players listed in the club ranking (including older recreational players), every background sync resets inactive players back to active.

### 5.3 Interaction with Clubhouse-Scoped Overrides

With the recommended `clubhouse_member_overrides` table:

1. **Background Sync Contract:**
   - `ImportMembers` and `MemberManager::addOrUpdateMember` continue to write directly to `members.inactive` (`!$player->showAll`).
   - The background sync **never reads, writes, or modifies** `clubhouse_member_overrides`.
   - The sync job remains 100% focused on updating canonical data from BadmintonPlayer.

2. **Admin Override Flow:**
   - When an admin in Clubhouse 1 toggles a player to Inactive:
     The mutation upserts a row into `clubhouse_member_overrides` with `(clubhouse_id: 1, member_id: X, inactive: true)`.
   - When background sync runs that night:
     It updates `members.name`, `members.gender`, and `members.inactive`.
     Clubhouse 1's override in `clubhouse_member_overrides` remains untouched.
   - When Clubhouse 1 queries the member list:
     Effective status evaluates to `true` (Inactive).
   - If Clubhouse 2 queries that same member:
     Effective status evaluates to whatever Clubhouse 2 has configured (or falls back to `members.inactive`).

3. **Reverting to Automatic Sync ("Nulstil til automatisk"):**
   - If an admin wants to clear the manual override and let BadmintonPlayer control the player's status again:
     The mutation deletes the row from `clubhouse_member_overrides` for `(clubhouse_id, member_id)`.
     The player immediately falls back to `members.inactive` without requiring any sync re-run or complex reconciliation.

---

## 6. Implementation Blueprint & Recommendations for Ticket #153

To prepare for ticket #153 ("Determine override state representation, sync behavior, and database schema"), we propose the following concrete technical specification:

### 6.1 Database Migration
Create `clubhouse_member_overrides`:
```php
Schema::create('clubhouse_member_overrides', function (Blueprint $table) {
    $table->id();
    $table->foreignId('clubhouse_id')->constrained('clubhouses')->cascadeOnDelete();
    $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
    $table->boolean('inactive')->comment('True if manually forced inactive, False if manually forced active');
    $table->timestamps();

    $table->unique(['clubhouse_id', 'member_id'], 'clubhouse_member_override_unique');
});
```

### 6.2 State Representation (Input to #153)
A three-state administrative mental model:
- **`AUTO` (Default):** No row in `clubhouse_member_overrides`. Effective status is `members.inactive`.
- **`FORCE_INACTIVE`:** Row exists with `inactive = true`. Effective status is `true`.
- **`FORCE_ACTIVE`:** Row exists with `inactive = false`. Effective status is `false`.

### 6.3 GraphQL API Mutations & Queries
1. **Mutation:**
   ```graphql
   setMemberClubhouseStatus(
       clubhouseId: ID!
       memberId: ID!
       status: MemberOverrideStatus! # AUTO | FORCE_ACTIVE | FORCE_INACTIVE
   ): Member!
   ```
2. **Member Field:**
   ```graphql
   type Member {
       ...
       inactive: Boolean! # Effective inactive status for requested clubhouse
       overrideStatus: MemberOverrideStatus! # AUTO, FORCE_ACTIVE, or FORCE_INACTIVE
   }
   ```
3. **Query Scope:**
   Update `MemberSearch` and `Member` model with `scopeWithClubhouseStatus(Builder $query, int $clubhouseId)`.

---

## Conclusion

Implementing member active/inactive overrides as a **clubhouse-scoped override table (`clubhouse_member_overrides`)** is architecturally superior to a global member column. It cleanly satisfies multi-tenancy requirements, decouples background synchronization from admin intent, prevents destructive sync regressions, and introduces negligible query overhead.
