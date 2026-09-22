# NemBadminton

A platform for holding tournament and team match management in Danish badminton, adhering strictly to Badminton Danmark rules and ranking validations.

## Language

### Core Entities

**Member**:
A badminton player with official ranking data from badmintonplayer.dk.
_Avoid_: User (unless referring to system login account)

**User**:
A system account with login credentials, roles, and clubhouse permissions.
_Avoid_: Member, player

**TeamRound**:
A specific match round (holdrunde) across a club's teams on a given date.
_Avoid_: Match, fight, team fight

**Squad**:
A specific club team (e.g. Hold 1, Hold 2) competing within a TeamRound.
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

**Available Member**:
A Member who is not assigned to any Squad in the Scenario currently being edited.
_Avoid_: Unused player, unassigned player

### User Documentation

**User Guide**:
Durable, publicly accessible instructions that explain how to use a feature in NemBadminton.
_Avoid_: Release announcement, feature specification

**Release Announcement**:
A short, publicly accessible summary of a newly released change that links to a User Guide when more guidance is available.
_Avoid_: User Guide, changelog
