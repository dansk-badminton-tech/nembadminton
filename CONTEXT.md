# NemBadminton

A platform for Danish badminton clubhouses to manage team tournaments, squad selections, player rankings, and match logistics according to Badminton Danmark rules.

## Language

### Core Entities

**Member**:
A badminton player tracked globally in the system with master ranking data from badmintonplayer.dk, shared across clubhouses.
_Avoid_: User, account

**User**:
An authenticated account with login credentials, permissions, and roles within a clubhouse (`coach`, `player`, `club-admin`, `super-admin`).
_Avoid_: Member, player

**Clubhouse**:
A badminton club organization within the platform grouping members, users, teams, and seasons.
_Avoid_: Club, tenant

**Season**:
A tournament competition cycle (e.g. 2024/2025) under which teams, rounds, and rankings operate.
_Avoid_: Year, period

### Teams & Rosters

**Team**:
An organizational team of the clubhouse competing in a specific season and tournament tier (e.g., 1. hold, 2. hold).
_Avoid_: Squad, lineup

**Team Roster**:
The designated pool of clubhouse-affiliated members associated with a Team for the season who are eligible to play its events.
_Avoid_: Squad, player list, trup

### Events & Availability

**Event**:
A scheduled match or tournament date associated with a Team and a specific round number (`round_number`) for which player availability is required.
_Avoid_: TeamRound, game date, match

**Availability**:
A member's declared status for a specific Event ("Kan" or "Kan ikke"), optionally accompanied by a note.
_Avoid_: Cancellation, afbud, RSVP

### Match Planning

**Team Round**:
A specific tournament round (identified by an integer `round` number, e.g. Runde 3) in a season comprising squads across tournament tiers.
_Avoid_: Event, match day

**Squad**:
A formal lineup sheet of categories and player pairings for a Team in a Team Round.
_Avoid_: Team, roster

**Scenario**:
An alternative draft version of a squad lineup (e.g., Plan A, Plan B) evaluated during planning before finalizing.
_Avoid_: Template, duplicate squad
