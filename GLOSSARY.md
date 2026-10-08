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
Where a Member was placed (Team, tier, category and position) in the Official Lineups of a Clubhouse's earlier TeamRounds in the same Season. It shows lineups, not matches actually played. It helps the coach place a Member, especially a Youth Player, who is placed by current strength.
_Avoid_: Placement history, match history

**User**:
A system account with login credentials, roles, and clubhouse permissions.
_Avoid_: Member, player

**Clubhouse**:
The organisation a User works in, linked to one or more badminton clubs. Team rounds, teams, cancellation collectors, invitations and memberships belong to a Clubhouse, and a User only reads and changes their own Clubhouse's data (see **Clubhouse isolation** in `CONTRIBUTING.md`). Members, clubs and ranking data are shared across Clubhouses.
_Avoid_: Tenant, organisation, club (a club is the badmintonplayer.dk club)

**TeamRound**:
A specific match round (holdrunde) across a club's teams on a given date.
_Avoid_: Match, fight, team fight

**Team** (hold):
A club's team entered in one season's tournament (e.g. "Højbjerg 1, 2. division, pulje 3"). It lasts the whole season and has a tier (række) and a group (pulje).
_Avoid_: Squad

**Squad** (holdopstilling):
One Team's lineup and match setup for a single TeamRound. A Squad is normally created from a Team, but can be created without one for one-off matches. Conceptually a lineup; the name Squad is kept.
_Avoid_: Team, sub-team

### Lineup Scenarios

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
