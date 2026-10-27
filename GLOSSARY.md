# Nembadminton

A platform for holding tournament and team match management in Danish badminton, adhering strictly to Badminton Danmark rules and ranking validations.

## Language

### Core Entities

**Member**:
A badminton player with official ranking data from badmintonplayer.dk.
_Avoid_: User (unless referring to system login account)

**Youth Player** (ungdomsspiller):
A Member whose ranking places them in the U15, U17 or U19 age group. Under §38 stk. 5 a Youth Player is placed by current playing strength in senior play, so their place in a lineup is the coach's judgement and not decided by ranking points alone.
_Avoid_: Young player, junior

**Season History** (sæsonhistorik):
Where a Member was placed (Team, tier, category and position) in the Official Lineups of a Clubhouse's earlier TeamRounds in the same Season. Earlier means a lower round number, not an earlier date (§45): a moved match keeps its round, and TeamRounds in the same round are not earlier than each other. A TeamRound without a round number has no earlier TeamRounds and is left out of the others' history. It shows lineups, not matches actually played. It helps the coach place a Member, especially a Youth Player, who is placed by current strength.
_Avoid_: Placement history, match history

**Category Rank** (kategoriplads):
A place's rank in one category across a Clubhouse's Squads in one TeamRound. The places are ordered by Squad order (highest team first) and then by the category's number: the first men's single on the highest team is rank 1, the second men's single on that team rank 2, and the first men's single on the next team rank 3. In doubles and mixed doubles both partners stand on the same place and share its Category Rank. Each category (HS, DS, HD, DD, MD) is its own ladder; places in different categories are never compared. An empty place keeps its rank.
_Avoid_: Position (the ranking-list position), placement

**Season Phase** (fase):
The part of the season a TeamRound belongs to, decided by its round number in the relevant tournament context, not its date (§45): **group stage** (grundspil) is round 1–7, or 1–9 in Badmintonligaen (§9 stk. 1); **play-offs** (slutspil) are rounds 8–11 below Liga and Liga's medal rounds 10–12 (quarterfinal, semifinal, gold/bronze); **qualification** (kvalifikation) is the separate qualification match, in round 12 (§27 stk. 1). Liga medal finals are playoffs even though they share round 12 with qualification. A promotion playoff group named "Kvalifikation til …" is not the qualification-match phase. A TeamRound without a round number has an unknown Season Phase.
_Avoid_: Tournament phase (a badmintonplayer.dk group type), stage

**DH Tournament** (DH-turneringen):
Badminton Danmark's national senior team tournament, the tiers from Badmintonligaen to Danmarksserien: Badmintonligaen, 1. division, 2. division, 3. division and Danmarksserien. The regional series below it are not part of it.
_Avoid_: Holdturneringen (also used for the regional series), senior tournament

**Playoff Participation Requirement** (deltagelseskrav):
The appearances a Member needs to play for a Badmintonligaen or 1. division Team in the playoffs or qualification (§32 stk. 1): at least 4 (Badmintonligaen) or 3 (1. division) earlier placements for the club on any Team in the DH Tournament, and at least one such placement played no later than 20 October. A Team's tier in the group stage decides which number applies, also in qualification (§27 stk. 3). Players on a Paper Squad are exempt. The tournament committee (Holdturneringsudvalget) can grant an exemption, so an unmet requirement is a warning.
_Avoid_: Final round eligibility (§38 stk. 8), minimum matches

**Match Kind** (kampart):
The nature of a Squad's match, distinguishing a Liga medal final from a separate qualification match even when their round numbers coincide. It belongs to that Squad's match setup and is the same in every Scenario of the TeamRound.
_Avoid_: Season Phase, tournament phase

**User**:
A system account with login credentials, roles, and clubhouse permissions.
_Avoid_: Member, player

**Clubhouse**:
The organisation a User works in, linked to one or more badminton clubs. Team rounds, teams, cancellation collectors, invitations and memberships belong to a Clubhouse, and a User only reads and changes their own Clubhouse's data (see **Clubhouse isolation** in `CONTRIBUTING.md`). Members, clubs and ranking data are shared across Clubhouses.
_Avoid_: Tenant, organisation, club (a club is the badmintonplayer.dk club)

**TeamRound**:
One round (holdrunde) across a club's Teams, identified by its round number (§45). Its Squads can be played on different dates, because Badmintonligaen has its own schedule and a holdkamp can be moved; each Squad keeps the date its match is played. Its Ranking List decides which Squad a Member may stand on.
_Avoid_: Match, fight, team fight

**Ranking List** (rangliste):
A monthly publication of the Members' points on badmintonplayer.dk, identified by its date. A TeamRound has one Ranking List, and it decides which Squad each Member may stand on (§38 stk. 4), for every Squad of the TeamRound. A Squad can have its own Ranking List; it only decides the order of the Members on that Squad (§38 stk. 2–3) and the points shown there. When the club's Squads in a round play on both sides of the 9th/10th of a month, the earliest of their Ranking Lists is the TeamRound's (§38 stk. 1b): the coach enters the date of the club's first match in the round as the TeamRound's playing date, and the Ranking List is suggested from it. Nembadminton does not work it out from the Squads' dates.
_Avoid_: Ranking version (the code name), rangliste-version

**Team** (hold):
A club's team entered in one season's tournament (e.g. "Højbjerg 1, 2. division, pulje 3"). It lasts the whole season and has a tier (række) and a group (pulje).
_Avoid_: Squad

**Squad** (holdopstilling):
One Team's match setup for a single TeamRound, and its part of each **Lineup** of that TeamRound. A Squad is normally created from a Team, but can be created without one for one-off matches. Its match setup is the same in every Scenario; its players can differ. The name Squad is kept.
_Avoid_: Team, sub-team

**Match Format** (kampformat):
The matches a Squad plays, by category and number (e.g. 2 MD, 2 DS, 2 HS, 1 DD, 2 HD); the Squad's category places follow it. It belongs to the Squad's match setup, so it is the same in every Scenario. In the DH tournament the tier decides it: Badmintonligaen and 1. division play 9 matches, and 2. division, 3. division and Danmarksserien play 13 (§37). A qualification match for a place in Badmintonligaen or 1. division is played in the 1. division format, so a 2. division Team plays 9 matches there (§27 stk. 2). A kreds sets its own formats (§5 stk. 2).
_Avoid_: Match count, antal kampe, squad size

**Paper Squad** (papirhold):
A Squad entered for a Team whose match is not played in a TeamRound where a lower Team of the club does play (§38 stk. 7). It is lined up like any other Squad and keeps its place in the Squad order, so lower Squads are compared against it. Its own players must meet only the paper-team rule: they stand only in categories they have played this season, and they have played on the same or a lower Team before. A Paper Squad's placements count as played, except for the 20 October deadline (§32 stk. 1 c). The coach marks it on the Squad, so it holds in every Scenario of the TeamRound, and can mark or unmark it later, because a match can be cancelled after the lineup is made (§35A).
_Avoid_: Oversidderhold, bye team, dummy team

**Reserve Player** (reservespiller):
A Member marked on one Squad as having replaced a player there after injury, illness or force majeure before the lineups were exchanged (§43 stk. 1). The mark records which player the Reserve Player replaces ("X for Y") and belongs to the Squad, not to a category. The Squad shows the lineup as played: the Reserve Player stands in the Squad's slots instead of the replaced player, and the normal checks validate that lineup. A Reserve Player with more points than the replaced player, in a category they play on the Squad, is a warning (§43 stk. 3). The Reserve Player is an Available Member, or comes from a lower Squad of the same lineup and also keeps their own slot there; it is the only way a Member stands on two Squads of a TeamRound. A Member is only a Reserve Player when the mark is set; it is never inferred from where they were placed.
_Avoid_: Substitute, stand-in, afløser

**Reserve Quarantine** (karantæne):
The ban on a Reserve Player playing on any Team in the next round after the round they were a reserve in (§44). The next round is the round number one higher, whatever its date (§45); a TeamRound without a round number gives no Reserve Quarantine. Only a Reserve Player in an Official Lineup is in Reserve Quarantine.
_Avoid_: Suspension, karantæneperiode

### Lineup Scenarios

**Lineup** (opstilling):
The Members placed in the category places of all Squads of a TeamRound, in one Scenario. A Squad's match setup (date, venue, tier, Paper Squad mark) is not part of it and is the same in every Lineup of the TeamRound.
_Avoid_: Roster, team sheet

**Scenario**:
An internal, named alternative lineup (e.g. "Plan A", "Plan B") containing the category slots and player assignments across all squads of a TeamRound for drafting without affecting the official roster or notifying players.
_Avoid_: Sandbox, draft folder, shadow round

**Official Lineup**:
The currently active, authoritative lineup of squads for a TeamRound that is visible to players and eligible for external export and notifications.
_Avoid_: Published round, live round

**Promotion**:
The atomic transition of a draft scenario to become the official lineup, preserving the outgoing official lineup as an alternative draft scenario.
_Avoid_: Publish, merge, partial sync

**Permanent Cancellation** (permanent afbud):
A Member marked as unable to play in any TeamRound until the mark is removed, e.g. because of injury (`playable=false`). Unlike an afbud, it is not tied to dates or a TeamRound.
_Avoid_: Midlertidigt utilgængelig, unplayable, unavailable

**Available Member**:
A Member who is not assigned to any Squad in the Scenario currently being edited.
_Avoid_: Unused player, unassigned player

### User Documentation

**User Guide**:
Durable, publicly accessible instructions that explain how to use a feature in Nembadminton.
_Avoid_: Release announcement, feature specification

**Release Announcement**:
A short, publicly accessible summary of a newly released change that links to a User Guide when more guidance is available.
_Avoid_: User Guide, changelog

**Guide Journey**:
The end-to-end holdrunde workflow that the Vejledninger overview presents in order: set up the TeamRound, handle afbud, make the Official Lineup, and share it. Each User Guide declares its **Journey Stage** and whether it is the stage's **step**, an **optional** or **alternative** branch, or a **troubleshooting** branch.
_Avoid_: Guide category, guide section
