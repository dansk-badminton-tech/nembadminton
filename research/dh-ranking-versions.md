# Every 2025/2026 ranking version for the players in the DH

Research for issue #301 (part of map #299). Checked 2026-10-23.

## Question

Can we get the ranking points (per category, per ranking version) that applied during 2025/2026 for every
player who appeared in a DH holdkamp, and how?

## Verdict

- **Yes.** badmintonplayer.dk still serves every 2025/2026 ranking version through the same public web service
  our scraper already uses. A club-filtered category list for a past version (`seasonid=2025`,
  `rankinglistversiondate=10/01/2025`) came back in full, with no login.
- **Nine monthly lists matter:** the first list of each month from **2025-08-01 to 2026-04-01**
  (August through April). The site publishes a new version every two or three days (158 versions in the season),
  but §38 stk. 1a uses only the first list of each month, labelled "<Måned>-ranglisten" on the site.
- **The existing importers can't target a past season as they are.** They are hard-wired to the current
  season and the last two months, and the version lookup drops every version before the current season start.
  The low-level call `BadmintonPlayer::getRankingListPlayersByClub($list, 2025, $clubId, $version)` does work
  for a past version, so a one-off scraper can call it directly.
- **Rule:** the first list published in month M applies to lineups from the 10th of M through the 9th of M+1
  (§38 stk. 1a). The frontend recommendation follows this rule. `RealisticSeasonSeeder` does not: it uses
  "newest version on or before the game date", and it has no November, December or March data.
- **Unranked player** = no row on that category's list → no `points` row; the rules and our validator count
  it as 0 points. **Youth players** appear on the senior category lists with an empty class column. The parser
  derives their age group (U15/U17/U19) from the birthdate in the BadmintonID, and the validator recognizes
  them through `points.vintage`.

## 1. Does badmintonplayer.dk serve historical ranking lists, and which versions?

Source: the public ASP.NET web service, two requests made for this research (plus one GET of `/` for the
`SR_CallbackContext` key embedded in the page).

**Version list.** `POST https://badmintonplayer.dk/SportsResults/Components/WebService1.asmx/GetRankingListPlayers`
with `rankinglistid=287, seasonid=2025, getversions=true` (the body `BadmintonPlayer::getVersions()` sends)
returns `d.Versions`: 158 dated versions from 2025-07-01 to 2026-06-29, plus "Seneste". The first version of
each month carries a label:

| Version (Value, m/d/Y) | Label on site | Applies to lineups (§38 stk. 1a) |
|---|---|---|
| 07/01/2025 | Juli Ranglisten | 2025-07-10 – 2025-08-09 |
| 08/01/2025 | August-ranglisten | 2025-08-10 – 2025-09-09 |
| 09/01/2025 | September-ranglisten | 2025-09-10 – 2025-10-09 |
| 10/01/2025 | Oktober-ranglisten | 2025-10-10 – 2025-11-09 |
| 11/01/2025 | November-ranglisten | 2025-11-10 – 2025-12-09 |
| 12/01/2025 | December-ranglisten | 2025-12-10 – 2026-01-09 |
| 01/01/2026 | Januar-ranglisten | 2026-01-10 – 2026-02-09 |
| 02/01/2026 | Februar-ranglisten | 2026-02-10 – 2026-03-09 |
| 03/01/2026 | Marts-ranglisten | 2026-03-10 – 2026-04-09 |
| 04/01/2026 | April-ranglisten | 2026-04-10 – 2026-05-09 |

The other versions in between (e.g. 09/03, 09/05, … 09/29/2025) are interim lists; under §38 stk. 1a they never
govern a lineup. DH rounds run September–April, so the snapshot needs the **August–April** monthly lists
(July only if a round falls before 10 August, which no DH round does).

**Historical content.** The same endpoint with `rankinglistid=288` (single), `param=K` (DS), `clubid=1087`
(BC37 Amager), `seasonid=2025`, `rankinglistversiondate=10/01/2025`, `searchall=true` returned the October 2025
list. Its header reads "Version: 01-10-2025 · Periode: 01-07-2025 til 28-09-2025 · Opdateret: 24-10-2025
13:39:51". Each row has: club rank, national rank in parentheses (not always shown), BadmintonID (`td.playerid`),
name and club (`td.name`), class (`td.clas`, e.g. "SEN A", empty for youth), and points (`td.points`). Pages
hold 100 rows; a pager row links to the next page (`pageindex`).

Category list ids (from `BadmintonPlayer::getRankingListIdAndParams()`): 287 = level (HL/DL, `gender` M/K,
endpoint `GetRankingListPlayersSenior`), 288 = single, 289 = double, 292 = mixed (`param` M/K picks the gender).

Notes:
- The "Opdateret" stamp is three weeks after the version date, so the site may correct a list after it is
  published. A one-off snapshot captures whatever the site shows today.
- Some names carry a " (g)" suffix after the club name. I did not find what it means; it is not needed for
  the points.
- The public REST API (`local-vendor/badmintonplayer-api`, `Player/ranking`) has only `Current` and `Previous`
  (`RankingPeriodType`), so it **can't** serve 2025/2026 history. The scraping web service is the only route.

## 2. What do the importers fetch, and can they target a past version?

| Code | What it fetches | Past version? |
|---|---|---|
| `app/Console/Commands/BadmintonPlayerPointsImporter.php` → `app/Jobs/BadmintonPlayerImportPoints.php` | For one club: each category list (HS, DS, HD, DD, MxH, MxD, or one via `--rankingList`) for `BadmintonPlayerHelper::getCurrentSeason()`. It gets the season's versions, keeps the **earliest per month** (`filterToRankingMonths`), then only the **last two months** (`->pop(2)`). Stores via `PointsManager::addPointsByRefId`, which **skips players not already in `members`**. | No. Season and month window are fixed; no option for either. A commented-out `generateSeasons()` hints at a multi-season loop that was never wired up. |
| `app/Console/Commands/UpdateAllPoints.php` | Dispatches the job above for every `initialized` club; scheduled daily at 01:30 (`app/Console/Kernel.php`). | No (same job). |
| `app/Console/Commands/BadmintonPlayerMembersImporter.php` → `app/Jobs/BadmintonPlayerImportMembers.php` | For given clubs: the **level** lists (HL, DL) for every month from July of the current season up to now, using the 1st of each month as the version. Creates or updates `members`, stores level points (`category = null`), and **syncs `club_member`** to the players found. | No: the season comes from `Carbon::now()`. |
| `local-vendor/scraper/src/BadmintonPlayer.php` `getVersions(int $season)` | The version list for any season. | **Broken for past seasons:** `BadmintonPlayerHelper::convertToCarbonObjects()` drops every version before `getCurrentSeasonStart()` (1 Aug of the current season), so `getVersions(2025)` returns `[]` today. |
| `BadmintonPlayer::getRankingListPlayersByClub(string $list, int $season, string $clubId, Carbon $version)` | One category list for one club at one version, every page. Returns `Player` objects (name, refId, gender, one `Point` with points, position, vintage). | **Yes.** It takes season and version as arguments. Responses are cached 12 h, keyed on the request parameters. |

Also `local-vendor/badmintonplayer/src/Jobs/ImportPoints.php` imports points from the public API. It can only
reach `Current`/`Previous`.

**For the one-off scrape:** call `getRankingListPlayersByClub()` directly for each DH club × 6 category lists ×
9 monthly versions, hard-coding the version dates from the table above. That skips `getVersions()`.
Write the result straight into the snapshot, not through `PointsManager`, so players missing from `members` aren't
dropped. Rough request count: 54 list-versions per club × 1–3 pages each. That is ~50–160 requests per club, so
it's worth throttling and keeping the cache. If the Liga/1. div tiers are in scope, the level lists aren't
needed: §38 uses only the category lists.

A player who changed club mid-season shows under the club they belonged to at each version (assumption: the
club filter follows the list's club column; not checked). Points are keyed by BadmintonID, so taking the union
over all DH clubs covers them as long as every club they played for is scraped.

## 3. Which version does a TeamRound use: rulebook vs our code

**Rulebook.** DH-reglementet §38 stk. 1 (text from the 2026-07-01 edition, `docs/Holdturneringsreglement-DH-reglementet-2026-07-01.pdf`, p. 19):

- a) "Den først offentliggjorte rangliste i en ny måned er gældende for holdsætning fra den 10. i den
  pågældende måned til og med den 9. i den efterfølgende måned."
- b) If a club's teams in the same round need different lists, the **earliest** list is used to select
  players for the team that plays first. Once a team has finished the tournament (stk. 8), the earliest list
  from that team's final round governs selection for lower teams. After selection, each team is ordered with
  its own current monthly list.

badminton.dk/holdturneringsregler/ lists only the 2026-07-01 DH edition; no 2025/2026 DH edition is linked.
The 10th-to-9th rule is older than that edition: our frontend has implemented it since 2022 (commit
`fe1a67c`, "Make recommendation for ranking list"). So it is safe to assume it held in 2025/2026, but the exact
2025/2026 wording wasn't checked.

Consequence for the snapshot: one TeamRound can need **two** versions (teams that play on either side of the
10th, and the stk. 1b "earliest list" rule). Storing all nine monthly lists for every player is simpler than
picking one per round.

**Our code today:**
- `TeamRound.version` / `Squad.version` hold a date. Changing `TeamRound.version` copies `points` rows with
  exactly that `version` into `squad_points` (`SquadManager::updatePointsOnAllSquadsInTeamRound`,
  `local-vendor/team-fight/src/SquadManager.php`). A squad's own `version` overrides the round's.
- The UI **recommends** a version by the rule: `resources/js/admin-v2/views/common/ranking-version.js`
  (`isRecommendedRankingVersionByPlayingDate`: from the 10th of the version's month to the 9th of the next).
  When a month has several versions in our DB, it recommends the one written most recently
  (`RankingVersionUtil::newestPerMonth`, the `newestRankingVersions` query). The user still picks the version.
- `database/seeds/RealisticSeasonSeeder.php` `RANKING_VERSIONS = ['2025-09-02','2025-10-02','2026-01-02','2026-02-02']`
  and `rankingVersion()` picks "newest version on or before the game date". That disagrees with §38 stk. 1a
  for rounds before the 10th, e.g. round 2 on 2025-10-04 gets October (rule: September), and round 8 on
  2026-02-07 gets February (rule: January). It's a showcase, so this is harmless there, but the replay should
  use the 10th rule.
- `database/seeds/points_data.php` holds versions `2025-07-02, 08-02, 09-02, 10-02, 2026-01-02, 02-02` only
  (no Nov, Dec, Mar, Apr) and only category rows (no level rows). The site's monthly lists are dated the
  **1st**, and 2025-08-02 / 09-02 / 10-02 are not versions on the site today. The `-02` dates probably come
  from the public API's `versionDate` (the export's `created_at` is 2025-08-12, which fits the API importer).
  Not verified. Whatever date the snapshot stores must match the `version` that `TeamRound.version` points at.

## 4. Unranked and youth players

**Unranked.** A player not placed on a category list simply has no row on it, so no `points` row is stored for
that category. §38 stk. 4: "Er en spiller på et lavere rangerende hold ikke indplaceret på ranglisten i en eller
flere kategorier betragtes spilleren som at have 0 point". `TeamValidator::categoryPoints()`
(`local-vendor/team-fight/src/TeamValidator.php`) catches `PointNotFoundInCategoryException` and returns 0,
which matches. §31 stk. 3 also requires players to be "indplaceret på ranglisten i de anvendte kategorier", so
a lineup player with no row in the category they played is itself a fact worth surfacing, not a data gap.
Players missing from `members` entirely are skipped by `addPointsByRefId`, so the replay must create members
for everyone in an Official Lineup.

**Youth.** Youth players are on the same senior category lists (e.g. 2008- and 2014-born players on BC37's
October DS list), with an empty `td.clas`. `Parser::rankingListPlayers()` takes the age group from the class
text when it matches `SEN|U09…U23`. Otherwise it falls back to `Util::calculateVintageByRefId()`: the birthdate
is the first six digits of the BadmintonID, and the age group is computed for the season start. The result is
stored in `points.vintage`. The validator marks a player as youth when a point's vintage is U15, U17 or U19
(`TeamValidator.php` ~l. 352), and GLOSSARY "Youth Player" follows §38 stk. 5: youth players are placed by playing
strength, not points alone. `points_data.php` today has vintages SEN, U15, U17, U19 and `U9`.

## Open points for the spec

- Exact version date to store (`-01` as on the site vs the `-02` convention in existing data).
- Which DH clubs and tiers are in scope (sets the request count).
- Whether to also store level (HL/DL) points; not needed for §38.
