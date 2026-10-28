# What the app shows when browsing an Official Lineup of a past TeamRound

Research for issue #323 (part of map #320). Codebase only, read at `master` `e8bcf095`, 2026-10-28.

## Question

When a User opens a 2025/2026 TeamRound in a Clubhouse, which rule checks and warnings appear on the Official
Lineup, do any depend on "now" instead of the TeamRound's own date, what data must a TeamRound, Squad, Team,
Member and points row hold for them to work, and how does a User with several Clubhouses move between them?

## Verdict

- **There is no read-only view.** A past TeamRound opens in the same editor as a current one
  (`/c-{clubhouseId}/team-fight/{id}/edit`, `TeamFight.vue`). Opening it writes nothing; every check runs as an
  advisory GraphQL mutation that only reads. The Lineup is still editable and Scenarios can be created.
- **Finding past TeamRounds works.** The TeamRound list filters by game date per season; on 2026-10-28 the
  second button is "2025/2026" (previous season), and "Vis alle" shows everything.
- **Almost every check is anchored to the TeamRound, not to now**: Ranking List, round number, Season and
  Squad dates. The Ranking List comparison across Squads (§38 stk. 4) reads `points` rows at the TeamRound's
  exact Ranking List date, so the reconstruction must import points for each Ranking List it uses.
- **Five things follow "now"** (see section 3): the Permanent Cancellation tag (current `members.playable`),
  the U15/U17/U19 age-group tag next to each player (current season), the badmintonplayer.dk holdkamp link
  (current season), the Ranking List dropdown in the TeamRound settings (versions present in `points` for the
  User's current Clubhouse's clubs), and the manual `housekeeping:points-retention` command, which deletes old
  `points` rows. None is a rule-check result except Permanent Cancellation; the age-group tag is display only.
- **A User has exactly one active Clubhouse** (`users.clubhouse_id`). There is no mutation or UI to switch it.
  Every policy and several resolvers compare against `users.clubhouse_id`, so the `/c-{id}` in the URL does not
  change what a User may see. Roles are scoped per Clubhouse (Spatie teams) and `primary_role_id` is global.
  Browsing several reconstructed Clubhouses needs either one login per Clubhouse or a tool that sets
  `users.clubhouse_id` (plus roles in each Clubhouse).

## 1. Checks and warnings on the Official Lineup

`TeamFight.vue` runs `validate()` after every load of the `teamRound` query (`resources/js/admin-v2/views/team-fight/TeamFight.vue`
around lines 445–460 and 935–975). Results show in `ValidationStatus.vue` (status tags and warning boxes) and
per player in `TeamTable.vue`. Without a selected Scenario the editor shows the Official Lineup.

### Status tags (`ValidationStatus.vue`)

| Tag | Rule | Mutation | Input | Anchored to |
|---|---|---|---|---|
| Hold fuldendt | every slot filled | `validateBasicSquads` | lineup from editor | lineup only. When it fails, the two checks below don't run unless the coach switches it off |
| Kategorivalidering | order on a Squad (§38 stk. 2–3) | `validateSquads` | lineup with each player's stored **squad points** (`squad_points`) | the Squad's stored points (Squad's own Ranking List, else TeamRound's, at placement time). Paper Squads skipped |
| Niveauvalidering | Ranking List across Squads (§38 stk. 4) | `validateTeamRoundCrossSquads(teamRoundId, scenarioId)` | server reads the lineup and `points` rows `where version = teamRound.version` (`local-vendor/team-fight/src/CrossSquadsLineup.php`) | TeamRound's Ranking List. A TeamRound without one falls back to stored squad points. A player without a `points` row at that date has nothing to compare (no warning) |
| Reserver (§43) | Reserve Player stronger than the replaced player (§43 stk. 3) | `validateReserves` | Squad reserve records; replaced player's points from `SquadReserve.replacedPoints` = `points` at `squad.version ?? teamRound.version` | Squad's / TeamRound's Ranking List. Shown only when the lineup has a Reserve Player |
| Papirhold (§38 stk. 7) | paper-team rule | `validatePaperSquads(teamRoundId, input)` | Season History before the TeamRound | round number + Season. Shown only when a Squad is a Paper Squad |
| Tidligere hold (§38 stk. 8) | earlier appearance on same or lower Team | `validateFinalRoundEligibility` | Season History | round number + Season. Shown only in round 11/12 (12 for Badmintonligaen); **off by default**, coach switches it on |
| Slutspilskrav (§32) | Playoff Participation Requirement | `validatePlayoffParticipation` | Season History | round number, Season and the TeamRound's Season start year for the 20 October deadline. Shown from round 10 (Liga) / 8 (1. division) |

The visibility rules for the last two are in `final-round-validation.mjs` and `playoff-participation.js`; both use
`squad.effectiveTier` and `teamRound.round`.

### Per-player tags (`TeamTable.vue`, from fields on `SquadMember` in `graphql/team-round.graphql`)

| Tag | Source | Anchored to |
|---|---|---|
| Red highlight + tooltip | results of the two strength checks above | as above |
| U15/U17/U19 | `SquadMember.vintage` → `Util::calculateVintage(birthday)` with no season, i.e. **current season** | **now** |
| Under 15 år (§31 stk. 1) | `validateTooYoungPlayers(input, seasonStartYear)`; birthday from the refId vs `teamRound.seasonStartYear` (Season, else game date) | TeamRound |
| Afbud / Permanent afbud | `PlacedPlayerCancellationResolver`: afbud tied to the TeamRound, afbudslink afbud on its `game_date`, or `members.playable = false` | afbud: TeamRound; **Permanent afbud: now** |
| Optaget på … (parallel allocation) | `ParallelAllocationResolver`: same Clubhouse, Season and round number, other TeamRound | round number + Season |
| Reserve på Hold N / Reserve for … | Squad reserve records in the lineup | TeamRound |
| Karantæne (Reserve Quarantine, §44) | `ReserveQuarantineResolver`: Reserve Player in the Official Lineup of round − 1, same Clubhouse and Season | round number + Season |
| "Point er redigeret manuelt" icon | `squad_points.corrected_manually` | stored |
| Sæsonhistorik button | `TeamRound.seasonHistory` / `SeasonHistoryLoader::upTo` | round number + Season |

Squad header: Paper Squad tag, playing date/place from the Squad, the Squad's own Ranking List tag, and a
badmintonplayer.dk link built from `externalTeamFightID` and **the current season**.

Header of the page: TeamRound name (or "Holdrunde nr. N"), `gameDate`, Ranking List month (`version`).

## 2. What each check needs

From `graphql/team-round.graphql`, `app/SeasonHistory/SeasonHistoryLoader.php`, `app/Enums/SeasonPhase.php` and the
validators. Fields that give a `CANNOT_VERIFY` or silently skip a check when missing are marked.

**TeamRound** (`team_rounds`)
- `clubhouse_id` — every policy and history query.
- `season_id` — Season History, Reserve Quarantine, parallel allocation, §32, §38 stk. 7/8. Missing ⇒ no
  history (`MISSING_SEASON`). Must exist in `seasons` (`SeasonSeeder` seeds 2025–2028).
- `round` — Season Phase, history order ("earlier" = lower round, §45), Reserve Quarantine (round − 1), which of
  the §32 / §38 stk. 8 tags show. Missing ⇒ `MISSING_ROUND`, no quarantine.
- `version` (Ranking List date) — must equal a `points.version` **exactly** (`where version = …`); else the
  cross-Squad check compares nothing. Null ⇒ falls back to stored squad points.
- `game_date` — header, afbudslink afbud, fallback date of a Squad without `playing_datetime`, season start
  year when `season_id` is null.
- One official `team_round_scenarios` row, or categories with `team_round_scenario_id = null` and no official
  Scenario — both count as the Official Lineup (`SeasonHistoryLoader::officialCategories`).

**Squad** (`squads`)
- `order` — Squad order and Category Rank.
- `playerLimit` plus `squad_categories` (category + name like "1. HD") — the Match Format; "Hold fuldendt".
- `team_id` → Team — tier via `effectiveTier()` (Team's tier, else `squads.tier`). Missing Team on an earlier
  round ⇒ §38 stk. 7/8 list it under "uncounted rounds" (`MISSING_TEAM`).
- `tier` — used only without a Team. Must parse with `CanonicalTournamentTier::tryFromRawName` (e.g.
  "Badmintonligaen", "1. division") or §32/§38 stk. 8 and Season Phase treat it as unknown.
- `playing_datetime` — the Squad's own date; §32's 20 October deadline uses it, else the TeamRound's
  `game_date` (`MISSING_HISTORY_DATE` without either).
- `is_paper_squad`, `version` (Squad's own Ranking List, optional), `external_team_fight_id` (link).

**Team** (`teams`): `clubhouse_id`, `season_id`, `tier_id` → `tournament_tiers.tier_name` (or `custom_tier_name`),
`name`, `group_name`.

**Placed player** (`squad_members` + `squad_points`): `member_ref_id`, `name`, `gender`; one `squad_points` row per
category with `points`, `position`, `vintage`, `version`, `corrected_manually`. These are what the order on a Squad
is checked against; `addSquadMemberByRefId` copies them from `points` at placement.

**Member** (`members`): `refId` (birthday is its first six digits, used for Under 15 and the age-group tag),
`playable` (Permanent afbud tag), `inactive` (hides from search only). Must be a Member of a club of the
Clubhouse to appear in search and for Reserve Player records.

**points** (`points`): `member_id`, `category`, `points`, `position`, `vintage` (U15/U17/U19 marks a Youth Player
in the strength checks, `TeamValidator::hasYoungPlayer`), `version` = the Ranking List date. Needed for every
Ranking List a TeamRound or Squad uses.

**Reserve records** (`squad_reserves`): `squad_id`, `team_round_scenario_id` (null = Official Lineup without
Scenario), `reserve_ref_id`, `replaced_ref_id`, `replaced_name`.

Not in the code: **Match Kind**. `SeasonPhase::fromRound` maps every round 12 to qualification, Liga medal
finals included.

## 3. What depends on "now"

| Where | What | Effect on a 2025/2026 TeamRound browsed in 2026/2027 |
|---|---|---|
| `SquadMember::getVintage()` → `Util::calculateVintage($birthday)` (`local-vendor/badmintonplayer-api/src/Util.php:15`), shown via `isYoungPlayer` in `TeamTable.vue` | age group against the **current** season | a 2025/2026 U19 who is senior now loses the tag; the strength checks still use `points.vintage` from the Ranking List, so their results are unaffected. `SlotPlayer` in Season History uses the same `getVintage()` |
| `PlacedPlayerCancellationResolver` | `members.playable = false` | a Member with a Permanent Cancellation **today** shows "Permanent afbud" on every past lineup. The map's rule (never change `playable`) leaves this as is |
| `TeamTable.vue:403`, `TeamFightPublic.vue:171` | `badmintonPlayerUrl(id, getCurrentSeason())` | the holdkamp link opens season 2026 instead of 2025 |
| `RankingVersionSelect` → `rankingVersions` / `newestRankingVersions` (`local-vendor/club/src/RankingVersionUtil.php`) | versions present in `points` for members of the clubs of `user.clubhouse` | the settings dropdown offers only versions imported for the User's current Clubhouse; a stored version still shows in the header |
| `housekeeping:points-retention {months}` (`app/Console/Commands/HousekeepingPointsRetention.php`) | deletes `points` older than now − N months | not scheduled in `Kernel.php`; if run, old Ranking Lists vanish and the cross-Squad check quietly finds nothing. `squad_points` stay |
| `ListTeamFights.vue` season buttons | current and previous season by today's date | 2025/2026 is the "previous season" button until July 2027, then under "Tidligere sæsoner" |
| Importers (`BadmintonPlayerImportPoints`, `RankingVersions` in `local-vendor/badmintonplayer`) | current season, last two months | not used when browsing; matters only for how points get into the database (see `research/dh-ranking-versions.md` on its branch) |

Everything else (Reserve Quarantine, parallel allocation, Season History, Paper Squad, §32, §38 stk. 8, Under 15,
afbud) keys on the TeamRound's `season_id`, `round`, `game_date`, `version` and the Squads' dates.

## 4. Several Clubhouses per User

- `users.clubhouse_id` is the one active Clubhouse ("Selected primary clubhouse" in `graphql/users.graphql`);
  `clubhouse_user` (`User::clubhouses()`) is the membership list, exposed as `me.clubhouses` but used by no
  component for switching.
- Policies compare against `users.clubhouse_id`: `ClubhousePolicy::view`, `TeamRoundPolicy::view/update/delete`,
  `viewSeasonHistory`. `ReserveQuarantineResolver`, `ParallelAllocationResolver` and `SquadReplacedPointsResolver`
  read it too. Opening `/c-{other id}/…` gives authorization errors, not the other Clubhouse.
- `SetClubhouseId` middleware sets the Spatie permission team to `users.clubhouse_id`, so roles are per Clubhouse;
  the User needs roles (e.g. coach/club-admin with `VIEW_TEAMROUNDS`) in each one.
- `users.primary_role_id` is one global field. A User whose primary role is player sees only TeamRounds they
  stand on (`TeamRound::scopeVisibleTo`) and no Season History of others; the browsing User must not be a player.
- No GraphQL mutation or UI changes `clubhouse_id`. It is set at Clubhouse creation (`ClubhouseSeeder`, sign-up)
  and when accepting an invitation (`Invitation::connectClubhouse`, which also attaches the membership).
- `App.vue` provides `clubhouseId` from `me.clubhouse.id`; after login the app routes to that Clubhouse.

So today: **one User per reconstructed Clubhouse** works with no app change; **one User on all of them** needs
something outside the UI (an artisan command or tinker that sets `users.clubhouse_id` and grants roles per
Clubhouse), and the User sees one Clubhouse at a time. The Clubhouse name is the only way the UI tells them apart
(menu and Klubhus page).

## 5. Preview interplay

`PreviewDataSeeder` already gives the testing Clubhouse (id 1, `testing@gmail.com`) a synthetic 2025/2026 Season
(`RealisticSeasonSeeder`, BC37 Amager) and a 2026/2027 one (`PaperSquadSeasonSeeder`). Reconstructed data in that
Clubhouse would mix with them, so reconstructed Clubhouses need to be their own. `RealisticSeasonSeeder` picks
"newest version on or before the game date", not the §38 stk. 1a rule.

## Sources

- `resources/js/admin-v2/router/index.js`, `views/team-fight/{TeamFight,TeamTable,ValidationStatus,ListTeamFights,TeamFightPlayerList,ReserveSection}.vue`,
  `views/team-fight/{final-round-validation.mjs,playoff-participation.js,too-young.js,helper.js}`, `helpers.js`,
  `views/common/{RankingVersionSelect.vue,ranking-version.js}`, `views/App.vue`, `views/tenant/Default.vue`,
  `resources/js/queries/teamRound.graphql`
- `graphql/{validate,team-round,users,clubhouse}.graphql`
- `local-vendor/team-fight/src/{CrossSquadsLineup,TeamValidator}.php`,
  `src/GraphQL/Mutations/Validate.php`, `src/GraphQL/Queries/{ReserveQuarantine,ParallelAllocation,PlacedPlayerCancellation,SquadReplacedPoints}Resolver.php`
- `local-vendor/badmintonplayer-api/src/Util.php`, `local-vendor/club/src/RankingVersionUtil.php`,
  `local-vendor/members/src/PointsManager.php`
- `app/SeasonHistory/{SeasonHistoryLoader,PlayoffParticipationRequirement}.php`, `app/Enums/SeasonPhase.php`,
  `app/GraphQL/Mutations/{ValidatePlayoffParticipation,ValidateFinalRoundEligibility,Invitation}.php`
- `app/Models/{TeamRound,Squad,Team,SquadMember,User}.php`, `app/Policies/{TeamRound,Clubhouse}Policy.php`,
  `app/Http/Middleware/SetClubhouseId.php`, `app/Console/Kernel.php`, `app/Console/Commands/HousekeepingPointsRetention.php`
- `database/seeds/{PreviewDataSeeder,TestingDataSeeder,SeasonSeeder,ClubhouseSeeder,RealisticSeasonSeeder,PaperSquadSeasonSeeder}.php`
