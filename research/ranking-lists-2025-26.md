# 2025/2026 Ranking Lists and Member data from badmintonplayer.dk

Research for #322 (part of map #320). Checked 2026-10-28 against badmintonplayer.dk itself (about 25 requests,
2 s apart) and this repo at `e8bcf095`. Closed issue #301 / branch `research/dh-ranking-versions` were read as
leads only; every claim below was re-checked.

Raw samples: [`ranking-lists-2025-26/samples/`](ranking-lists-2025-26/samples/). The request script used:
[`ranking-lists-2025-26/fetch.py`](ranking-lists-2025-26/fetch.py).

## Verdict

- **Every 2025/2026 Ranking List is still served**, without login, by the endpoint our scraper already uses:
  `POST https://badmintonplayer.dk/SportsResults/Components/WebService1.asmx/GetRankingListPlayers` with
  `seasonid=2025` and `rankinglistversiondate=<m/d/Y>`.
- **The Ranking Lists that count are the ten "<Måned>-ranglisten" versions, dated the 1st of each month from
  2025-07-01 to 2026-04-01.** The site also lists 148 interim versions; no May or June list is labelled.
- **Only exact version dates work.** `10/02/2025` (not a version) returns an empty list. Points must be stored at
  `version = YYYY-MM-01` so a TeamRound's Ranking List finds them.
- **Fetch per club, then fill in per player.** A club's list in one category is 1–3 pages of 100 rows (Højbjerg:
  285 men on HS). Six category lists × ~3 pages × 10 versions ≈ 180 requests per club. A player-filtered request
  costs one request per category and version, so it's the fallback for lineup players missing from the club's list.
- **Member data comes from the list rows**: BadmintonID (refId), name, badmintonplayer.dk player id, and gender
  (from which list they are on). Birthday is the first six digits of the refId; nobody fetches it separately.
- **None of the existing importers can fill a past season as they are.** The low-level
  `BadmintonPlayer::getRankingListPlayersByClub($list, 2025, $clubId, $version)` can, and so can `Enricher::teamMatch`.
- **Youth Players are on the senior category lists** (most of a club's rows) but **not on the level list**
  (Tilmeldingsniveau). A Member that is in a category list without a national placement still has points.

## 1. Which lists, which versions, and how the site dates them

### Lists

The ranking page's tabs (from the `Html` of any `GetRankingListPlayers` response) are:

| Tab | `rankinglistid` | `param` | `gender` | Our code |
|---|---|---|---|---|
| Tilmeldingsniveau (level) | 287 | | `M` / `K` | `HL` / `DL`, endpoint `GetRankingListPlayersSenior` |
| HS / DS | 288 | `M` / `K` | | `HS` / `DS` |
| HD / DD | 289 | `M` / `K` | | `HD` / `DD` |
| MD H / MD D | 292 | `M` / `K` | | `MxH` / `MxD` |

Source: `samples/versions-season-2025.json` request, and `BadmintonPlayer::getRankingListIdAndParams()`
(`local-vendor/scraper/src/BadmintonPlayer.php`). These are the senior lists; youth players are on them too (§4).
§38 compares category points, so the six category lists are what a TeamRound needs. The level list is optional.

### Versions

`GetRankingListPlayers` with `rankinglistid=287, seasonid=2025, getversions=true` returns `d.Versions`: "Seneste"
plus 158 dated versions, 2025-07-01 to 2026-06-29 (`samples/versions-season-2025.json`). The first version of a
month carries a label:

| `Value` (m/d/Y) | Text on the site | Ranking List for playing dates (§38 stk. 1a, ADR-0006) |
|---|---|---|
| 07/01/2025 | 01-07-2025 Juli Ranglisten | 2025-07-10 – 2025-08-09 |
| 08/01/2025 | 01-08-2025 August-ranglisten | 2025-08-10 – 2025-09-09 |
| 09/01/2025 | 01-09-2025 September-ranglisten | 2025-09-10 – 2025-10-09 |
| 10/01/2025 | 01-10-2025 Oktober-ranglisten | 2025-10-10 – 2025-11-09 |
| 11/01/2025 | 01-11-2025 November-ranglisten | 2025-11-10 – 2025-12-09 |
| 12/01/2025 | 01-12-2025 December-ranglisten | 2025-12-10 – 2026-01-09 |
| 01/01/2026 | 01-01-2026 Januar-ranglisten | 2026-01-10 – 2026-02-09 |
| 02/01/2026 | 01-02-2026 Februar-ranglisten | 2026-02-10 – 2026-03-09 |
| 03/01/2026 | 01-03-2026 Marts-ranglisten | 2026-03-10 – 2026-04-09 |
| 04/01/2026 | 01-04-2026 April-ranglisten | 2026-04-10 – 2026-05-09 |

The rest (every 2–3 days, e.g. 09/03, 09/05 … 2025) are interim versions; under §38 stk. 1a they never decide a
lineup. A holdkamp after 2026-05-09 would have no labelled list; the 2026-05-01 interim version exists but isn't
labelled, so the spec should say what happens then (likely nothing: DH rounds end in April).

For comparison, `seasonid=2026` (fetched the same day) has 51 dated versions and labels only Aug–Oct 2026; its
07/01/2026 version is **not** labelled. "Juli" is not reliably labelled, but July lists never decide a DH round.

**How the site dates a list.** A list's header reads, e.g. for October:
`Version: 01-10-2025 · Periode: 01-07-2025 til 28-09-2025 · Opdateret: 24-10-2025 13:39:51`; April:
`Version: 01-04-2026 · Periode: 01-07-2025 til 29-03-2026 · Opdateret: 06-04-2026 22:26:00`. So the version
date is the 1st, the points cover matches up to a few days before, and the site **edits a published list in
place** ("Opdateret" weeks later) instead of publishing a new version. A scrape today gets the list as last
corrected, which may differ from what a coach saw on the day.

**Only exact version dates.** `rankinglistversiondate=10/02/2025` returns a response with the tabs but no list
and no `Version:` header (`samples/club25-hojbjerg-HS-2025-10-02-not-a-version.json`). The repo's seed data uses
`-02` dates (`database/seeds/points_data.php`, `RealisticSeasonSeeder::RANKING_VERSIONS`), which are **not**
versions on the site; a reconstruction must store `2025-10-01` etc. and point the TeamRound's Ranking List at it.

## 2. Can past versions be fetched, per club or per player, and at what cost?

Request body (as `BadmintonPlayer::getRankingListPlayersHtml()` sends it): `seasonid`, `rankinglistid`, `param`,
`gender`, `clubid`, `playerid`, `pageindex`, `rankinglistversiondate`, `rankinglistagegroupid=15`,
`searchall=true`, plus `callbackcontextkey`, a token scraped from `SR_CallbackContext = '…'` on the home page
(one GET). No login. Responses took 0.2–1 s, once 35 s.

### Per club

`clubid=<badmintonplayer.dk club id>` returns the club's rows, 100 per page, `pageindex` from 0; a page without
rows ends the list (`NoPlayersException` in `Parser::rankingListPlayers`). Højbjerg (club 25), October 2025:

| List | Rows | Pages |
|---|---|---|
| HS | 285 | 3 |
| DS | 100+ (page 0 full) | ≥2 |
| HL (level, `GetRankingListPlayersSenior`) | 44 | 1 |

April 2026 HS: 290 rows, 3 pages. **Cost:** about 6 lists × 2–3 pages × 10 versions ≈ 150–180 requests per
club, fewer if the command fetches only the versions its TeamRounds use. At 2 s spacing that is ~6 min per club.
The print view (`/DBF/Ranglister/Udskriv/?params=15,288,2025,10/01/2025,,,25,True,…`) is also paged at 100 rows,
so it saves nothing.

Club ids come from `clublistclientscript.aspx?unionid=1` (`BadmintonPlayer::getClubs()`), e.g. Højbjerg 25,
Hvidovre 1094, Skælskør 327, Gentofte 1092, Skovshoved 1099, Odense OBK 1392, Hjørring 1497, Skagen 1500,
Aarhus AB 1617, Solrød Strand 39, Værløse 42. Negative ids are team alliances (e.g. `-2` Team Skælskør-Slagelse,
`-3` Vendsyssel); they have no ranking list of their own. `Enricher::resolveClubs()` maps some of them to member
clubs.

### The club filter at a past version is not a fixed "club on that date"

Buster Blidegaard (090130-01, player id 268040) is on Højbjerg's October HS list but not on its April list.
Filtered by player instead, he shows under "Brøruphus Efterskole" at **both** versions with the same 1743 points;
his profile lists two clubs, "Højbjerg" and "Brøruphus Efterskole (Ungdom)". So a player with two clubs (or a
club change) can be missing from the club list at some versions. A lineup player missing from the club's list
must be looked up per player, never treated as unranked straight away.

### Per player

`clubid=""`, `playerid=<badmintonplayer.dk player id>` returns that player's single row on the list at that
version (`samples/player3019-HS-2025-10-01.json`). It costs one request per category list and version: 3 lists
(the player's gender) × versions needed. The player id is the number after `#` in the name link
(`/DBF/Spiller/VisSpiller/#3019`), which is also what holdkamp pages link to (`Parser::extractNameAndId`).

To go from a player id to a refId: `GetPlayerProfile` (`playerid`) returns `playernumber` (refId), `playername`,
`clubid`, `clubname` (current club). `SearchPlayer` (`playernumber`) returns player id, refId, name, club id,
club name and **gender** (`SPSel1('3019','020311-01','Mads Vestergaard','25','Højbjerg','M')`,
`samples/searchplayer-020311-01.json`). The profile HTML also lists linked user accounts, so we don't keep a
sample of it.

### Public REST API

`local-vendor/badmintonplayer-api` (`https://badmintonplayer.dk/publicapi/v1/`, login required) only offers
`Player/ranking?rankingType=Current|Previous` (`RankingPeriodType`). It can't reach 2025/2026.

## 3. What the existing code can and can't do for a past season

| Code | What it does | Past season? Why |
|---|---|---|
| `app/Jobs/BadmintonPlayerImportPoints.php` | For one club: the six category lists, `BadmintonPlayerHelper::getCurrentSeason()`, versions from `getVersions()`, first per month, **last two months only** (`->pop(2)`); stores via `PointsManager::addPointsByRefId`. | **No.** Season is the current one; `getVersions()` returns `[]` for past seasons (next row); `addPointsByRefId` does `firstOrFail()`, so players who aren't Members yet are skipped. |
| `BadmintonPlayer::getVersions(int $season)` + `BadmintonPlayerHelper::convertToCarbonObjects()` | Gets the season's versions. | **Broken for past seasons:** `convertToCarbonObjects()` drops versions before `getCurrentSeasonStart()` (1 Aug of the current season, i.e. 2026-08-01 today). It also drops the current season's own July versions. |
| `BadmintonPlayerHelper::filterToRankingMonths()` | Keeps the first version per month. | Works, and matches §38 stk. 1a (the labelled 1st). Groups by month number only, so it assumes one season's versions. |
| `app/Jobs/BadmintonPlayerImportMembers.php` | For given clubs: level lists (HL/DL) for the 1st of every month from July of the current season to now. Creates/updates Members via `MemberManager::addOrUpdateMember`, stores level points, and **syncs `club_member`** to the players found. | **No**, season comes from `Carbon::now()`. And it misses every Youth Player (level lists hold seniors only, §4). Reusing it would break the map's rule that historical data never changes a Member's club, `inactive` or `playable`: `addOrUpdateMember` overwrites name and gender and sets `inactive = false` for `override_inactive = 'AUTO'` Members, and the job re-syncs `club_member`. |
| `BadmintonPlayer::getRankingListPlayersByClub($list, $season, $clubId, $version)` | One category list, one club, one version, all pages; returns `Player` (name, refId, gender from the list, one `Point` with points, position, vintage). | **Yes.** Season and version are arguments. |
| `BadmintonPlayer::getPlayerByBadmintonPlayerId($id, $version, $season)` | `GetPlayerProfile` → `SearchPlayer` → the player's three category lists. | **Yes**, ~8 requests per player (each list pages until empty); profile and search aren't cached. |
| `Enricher::teamMatch($team, $clubId, $season, $version)` | The "Tidligere holdkamp" path: club lists for all six categories, matched to lineup players **by name**, fallback per player id; adds a 0-point level row for youth without one. | **Yes**, but name matching is fragile (two players with the same name; suffixes like "(EU)"). Matching on player id or refId is safer. |
| `BadmintonPlayer::sendRequestAndGetBody()` | Caches each response in the Laravel cache for 12 h (`43200`), keyed on the request parameters without the token. | Usable for the disk cache the map wants if it gets a file-backed cache store and a longer TTL. |
| `Parser::rankingListPlayers()` | Parses rows. | Works on past lists. Position is read from the 2nd cell, which is the national rank `(22)`; it's empty for rows without a national rank, so position becomes 0. |

The low-level calls above all go to the same endpoints a browser uses; nothing in them assumes the current season
except `getVersions()`'s filter.

## 4. Unranked players and Youth Players

**Points without a national placement.** Many rows have a club placement but an empty national rank: on
Højbjerg's October HS list 144 of 285 rows. They have points, and the profile shows 0 matches in that category
(e.g. Mads Vestergaard: HS 1308 points, 0 matches). The parser stores their points like any other row.
Whether such a player counts as "indplaceret på ranglisten" (§38 stk. 4: not placed = 0 points) is a rules question
I didn't settle; today's validator uses whatever points row exists.

**Unranked.** A player with no row on a category list at that version (checked per player, not only per club) has
no `points` row. `TeamValidator::categoryPoints()` treats that as 0 (§38 stk. 4), and ADR-0006 lets them play.

**Youth Players.** They are on the senior category lists: 233 of Højbjerg's 285 October HS rows are born 2007
or later, with an empty class cell (`td.clas`). Some seniors also have an empty class. The parser takes the age group
from the class when it reads `SEN|U09…U23`, and otherwise derives it from the refId's birthdate at the season
(`Util::calculateVintageByRefId`, Jan 1 of the season's second year), storing it in `points.vintage`; the
validator recognises a Youth Player by `vintage` U15/U17/U19. Youth Players are **not** on the level list:
Højbjerg's October HL list has 44 rows, all born 2006 or earlier.

## 5. Member data: where each field comes from

| Field | Source |
|---|---|
| `refId` | `td.playerid` (BadmintonID, e.g. `020311‑01`; the site uses a non-breaking hyphen `&#8209;` that the parser replaces with `-`). |
| `name` | Text of the `td.name a` link. It includes markers like `(EU)` and `(udl.)` (e.g. "Toma Junior Popov (EU)"), so they end up in the Member's name. |
| `gender` | Which list the row is on (`param`/`gender` `M`/`K`), or `SearchPlayer`. |
| `birthday` | First six digits of the refId (`Member::getBirthday()`, `ymd`). No importer writes the `members.birthday` column. Two-digit years pivot at 1970 in PHP, so a player born before 1970 would get a 20xx date; senior DH players rarely are. |
| badmintonplayer.dk player id | `#<id>` in the name link; needed to join holdkamp lineups (which link player ids) to ranking rows. The `members` table has no column for it. |
| club | The club text after the name; sometimes ends in " (g)" (meaning unknown). Not needed, since the map keeps Members' clubs untouched. |

## Implications for the spec

1. Store points at version `YYYY-MM-01` for the ten labelled versions (or only those the TeamRounds use).
2. Fetch the six category lists per club per version, then look up lineup players missing from them per player id.
   Join lineup players by player id, not name.
3. Write Members and points directly (create Member by refId if missing; never update club, `inactive`,
   `playable`); don't call `BadmintonPlayerImportMembers` or `MemberManager::addOrUpdateMember` as they are.
4. Don't rely on `getVersions()` for a past season; hard-code or fix its filter.
5. Team-alliance clubs (negative ids) need their member clubs' lists.
6. The level list is optional and doesn't contain Youth Players.
