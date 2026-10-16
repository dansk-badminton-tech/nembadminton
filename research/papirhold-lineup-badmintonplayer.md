# How a Paper Squad (papirhold) is entered on badmintonplayer.dk

Research for issue #278 (part of #225). Checked 2026-10-16.

## Question

Is a Paper Squad (oversidderhold/papirhold, DH-reglementet §38 stk. 7) entered on badmintonplayer.dk as a
full normal lineup (every category HS/DS/HD/DD/MD, every slot, a named player per slot), exactly like a played
team match? Is it publicly visible on the match page? Does the §39 lineup deadline apply?

## Verdict

- **Full lineup, a player in every slot: confirmed.** Every paper match checked (41 matches, two seasons, all
  five tiers) has the tier's complete match sheet with a named player in every slot.
- **Each player sits in a specific category and slot: confirmed.** The page shows "1. MD", "2. HS", "3. HD", etc.,
  the same rows as a played match in that tier.
- **Publicly visible: confirmed.** Match pages load anonymously (no login), through the same public web
  service as every other match.
- **§39 deadline applies: not confirmed.** The regulation does not exempt paper squads from §39, but §39 does not
  mention them either, the public page shows no submission timestamp, and no Badminton Danmark guidance on
  paper-squad deadlines was found.

## Method

badmintonplayer.dk's "Stilling" page is a JS app whose content comes from the public ASP.NET web service
`POST https://badmintonplayer.dk/SportsResults/Components/WebService1.asmx/GetLeagueStanding` with the hash
parameters (`subPage, seasonID, leagueGroupID, ageGroupID, regionID, leagueGroupTeamID, leagueMatchID, clubID,
playerID`) plus the `SR_CallbackContext` key embedded in the page HTML. `subPage=4` lists a pool's matches,
`subPage=5` returns a match page. No login is needed. Every match in every paper pool was fetched and each
category row was checked for the expected number of players (1 for HS/DS, 2 for HD/DD/MD).

Match page URL format: `https://badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,<season>,<pool>,1,1,,<matchId>,,`

## Findings

### 1. Paper pools: one per tier, opponent "Ukendt modstander"

Season 2025/2026 (`BADDAN SEN 2025/2026`), each pool titled "Oversidder-runder/papirhold":

| Pool | Tier | Matches | Match sheet |
|---|---|---|---|
| 18801 | Badmintonligaen (oversidder-runder) | 3 | 9 rows: 1-2 MD, 1-2 DS, 1-2 HS, 1 DD, 1-2 HD |
| 18802 | 1. division (oversidder-runder) | 6 | 9 rows (same as Liga) |
| 18803 | 2. division (oversidder-runder) | 3 | 13 rows: 1-2 MD, 1-2 DS, 1-4 HS, 1-2 DD, 1-3 HD |
| 18804 | 3. division (oversidder-runde) | 6 | 13 rows |
| 18805 | Danmarksserien (oversidder-runde) | 2 | 13 rows |

All 20 matches are round 12 (18-04-2026), home team = the club's team, away team = "Ukendt modstander",
marked "Afgjort uden kamp (afbud/udeblivelse)", "Reserver: Ingen". 224 category rows checked, 0 with a
missing or extra player.

Season 2024/2025: pools 17495 (Liga, 6 matches), 17595 (1. div, 4), 17596 (2. div, 4), 17597 (3. div, 5),
17598 (DS, 2). 21 matches, 233 rows, 0 with a missing or extra player.

Season 2026/2027 has no paper pools yet.

The rows are exactly those of a played match in the same tier, e.g. Liga grundspil match 484961 (9 rows) and
2. division match 485365 (13 rows).

### 2. Example match pages

- **Badmintonligaen** - Greve vs Ukendt modstander, match 506867, 9-0 / 12-0:
  https://badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,2025,18801,1,1,,506867,,
  1. MD Rikke Søby Hansen / Jeppe Søby Hansen; 2. MD Fiona Hallberg / Calvin Lundsgaard Kvolbæk;
  1. DS Polina Buhrova; 2. DS Yevheniia Kantemyr; 1. HS William Bøgebjerg; 2. HS David Kim Kristensen;
  1. DD Kantemyr / Buhrova; 1. HD Jeppe Søby Hansen / Calvin Lundsgaard Kvolbæk; 2. HD Jonas Kudsk / Tobias Kudsk.
  Players appear in several categories (e.g. Jeppe Søby Hansen in 1. MD and 1. HD), as in a normal lineup.
- **1. division** - Hillerød vs Ukendt modstander, match 506871:
  https://badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,2025,18802,1,1,,506871,,
- **2. division** - Drive vs Ukendt modstander, match 506876, 13-0 / 16-0:
  https://badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,2025,18803,1,1,,506876,,
  1.-4. HS Johan P. Philipsen, Simon Friis Amossen, Magnus Malfilatre, Nikolaj Filt Albrectsen;
  1. DD Emilie Bønlykke Faarborg / Ida Holte Thorius; 2. DD Christine Thier / Anna Engelhardt Søeborg;
  1. HD Amossen / Joachim Suder; and so on for all 13 rows.
- **3. division** - abc Aalborg, match 506879:
  https://badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,2025,18804,1,1,,506879,,
- **Danmarksserien** - Drive 2, match 506886:
  https://badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,2025,18805,1,1,,506886,,

Anomaly: Højbjerg match 506868 (Liga pool 18801) shows result 13-0 and points 0-0 although the sheet has 9 rows.
The lineup itself is complete. Probably an administrative data-entry quirk; it does not change the answer.

### 3. A paper squad also appears on an ordinary match page

A paper squad is not only entered in the "Oversidder-runder" pools. When the opponent cancels, the paper squad
is entered on the ordinary match page:

- Danmarksserien pulje 7, match 464245, Karlslunde 2 vs Drive 3, 11-01-2025, "Afgjort uden kamp", 0-13:
  https://badmintonplayer.dk/DBF/HoldTurnering/Stilling/#5,2024,16885,1,1,,464245,,
  Drive 3's paper squad fills all 13 rows (both clubs' lineups are shown). Badminton Danmark's ruling on this
  paper squad ([Kendelse kamp 464188](https://badminton.dk/wp-content/uploads/2025/02/Kendelse-i-Danmarksserien-kamp-464188-Drive-4-SAIF-Kbh.-2-170125.pdf))
  says "Karlslunde meldte afbud til kampen. Dermed stillede Drive 3 med et papirhold", judges two named players
  on it under §38 stk. 7, and strips specific categories (1.-4. HS, 1.-3. HD) on the lower team.
- [Kendelse kamp 462511 Lyngby-Højbjerg (2024)](https://badminton.dk/wp-content/uploads/2024/04/2024-04-22-Kendelse-i-kamp-462511-Lyngby-Hojbjerg.pdf):
  the paper squads have their own match numbers (462532, 462536), and the protesting club knew which player
  stood on Højbjerg 3's paper squad, i.e. the lineup is public.

### 4. Visibility

All match pages above were fetched anonymously through the public web service the website itself uses. The
paper match page has the same layout as any match page: header (Kampnr, Runde, Tid, Spillested, Hjemmehold,
Udehold, Resultat, Point), the note "Afgjort uden kamp (afbud/udeblivelse)", the category table with the club's
players and "Ukendt modstander" in the opponent column, empty set scores, and "Reserver: Ingen".

### 5. Deadline (§39)

`docs/Holdturneringsreglement-DH-reglementet-2026-07-01.pdf`:

- §38 stk. 7: "Et oversidderhold/"papirhold" skal benyttes i seniorholdkampe, der ikke afvikles, og hvor
  klubbens underliggende hold spiller i samme runde. Et papirhold skal mindst bestå af det antal spillere af
  hvert køn, som har været benyttet flest gange i hidtidige spillerunder ..." and "For spillere på 'papirhold'
  er det udelukkende §38 stk. 7, der skal være overholdt." This exempts the players from the ranking-order
  and lower-team rules in stk. 2-4; it says nothing about §39.
- §39 stk. 1: "Holdopstillingerne skal indtastes via Badmintonplayer efter følgende tidsfrister:
  Badmintonligaen og 1. division: Tre timer før seniorholdkampens begyndelsestidspunkt ... 2.- og 3. division
  samt Danmarksserien: En time før ..." It does not mention paper squads.
- §54 stk. 7: an illegal player on a paper squad is an aggravating circumstance.
- Paper matches have their own start time on badmintonplayer.dk (e.g. 506867 "lø 18-04-2026 14:00"), so a
  §39 deadline could be computed, but nothing public says it is applied to paper squads.
- The public match page does not show when the lineup was entered.
- Searches of badminton.dk found no guidance on paper-squad deadlines.

The deadline question stays open. Ask Badminton Danmark (hold@badminton.dk) if it matters.

## Sources

- badmintonplayer.dk public web service `GetLeagueStanding` (pools 18801-18805, 17495, 17595-17598, 16885,
  17880, 17906), fetched 2026-10-16.
- `docs/Holdturneringsreglement-DH-reglementet-2026-07-01.pdf`: §35A, §38 stk. 7, §39, §54 stk. 7.
- Badminton Danmark rulings linked above.
