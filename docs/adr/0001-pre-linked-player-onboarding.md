# Pre-linked User Registration for Player Availability

## Context
NemBadminton players are tracked as `Member` records imported from badmintonplayer.dk, while login accounts are `User` records. To submit match availability reliably, receive notifications, and participate in the platform, players need authenticated `User` accounts. However, standard registration flows (sign up, search for and claim a player profile) introduce friction and drop-off.

## Decision
Players are required to have an authenticated `User` account with the `player` role to manage match availability. To minimize friction, coaches and club admins generate 1-click pre-linked invite links (via email or direct Messenger sharing). The invite binds the new account directly to the specific `Member` (`player_id`) and `Clubhouse`. The player only needs to set a password (or sign in with Google) to immediately land in their authenticated player portal.

## Consequences
- **Pros**: Clean user identity, secure authenticated endpoints, permanent access without link amnesia, enabled direct notifications/emails, and a real foundation for the two-sided player/coach platform.
- **Trade-offs**: Requires an upfront onboarding step for each player. Coaches must distribute the pre-linked invites at the start of the season to onboard their rosters.
