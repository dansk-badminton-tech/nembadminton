# Notify all players on a team round (via Laravel Notifications)

**Date:** 2026-07-20
**Status:** Approved

## Overview

Add a **"Alle spillere på holdrunden"** recipient option to the existing team
notify page (`TeamFightNotify.vue`). The backend resolves each player on the
round to their linked **User** account, then dispatches the existing
`TeamPublish` or `TeamUpdated` **Notification** (chosen by the selected type) to
those users via `Notification::send()`. Channel selection stays governed by each
notification's `via()` (respecting `subscriptionSettings->email`). Players with
**no linked user** are skipped and reported back to the UI. One
`TeamActivityLog` row records the send.

Using the Notification system (instead of raw `Mail::bcc`) provides per-user
channel flexibility (mail / WebPush / database) and inherently preserves privacy
since each user is notified individually.

## Domain facts

- A player on a team round is a `SquadMember` reached via
  `TeamRound → Squad → SquadCategory → SquadMember` (`member_ref_id`).
- The reachable email lives on `users.email`. The authoritative Member↔User link
  is `users.player_id ↔ members.refId` (unique per `[player_id, clubhouse_id]`).
  `SquadMember::user()` already joins `member_ref_id ↔ player_id`.
- `members.email` is **deprecated** — must not be used.
- A member may have **no linked user** → not reachable → skipped and reported.
- Existing infra:
  - `RecipientType` enum: `MANUAL_EMAILS`, `TEST_SELF`.
  - GraphQL `ReceiverMethod` enum + `SendTeamNotificationInput` +
    `sendTeamNotification` mutation returning `TeamRound!`.
  - `FlyCompany\TeamFight\Notifier` with `sendManualEmails` / `sendTestSelf`.
  - `TeamActivityLog` model + `logNotificationSent` / `logTestEmailSent`.
  - Notifications `TeamPublish` (mail only) and `TeamUpdated`
    (database + WebPush + mail gated by `subscriptionSettings->email`).
  - Frontend already references a `platform` recipient type in
    `cannotPublish` / `hasValidRecipients` / `publish` / `totalPlayersCount`,
    but has **no UI card** and **no backend support**.

## Decisions

- **Recipient scope:** all players on the round who have a linked user.
- **Unreachable players:** skip and report (players with no linked user).
- **Trigger/UI:** new recipient option in the existing notify UI, named
  `platform` on the frontend / `PLATFORM` on the backend.
- **Notification classes:** reuse existing `TeamPublish` and `TeamUpdated`,
  selecting one based on the chosen `TeamNotificationType`.
- **Opt-out:** respect each notification's existing `via()` rules
  (`TeamUpdated` mail gated by `subscriptionSettings->email`).
- **Custom message:** add optional `?string $message` arg to both notifications
  and include it in the mail (and WebPush/array where sensible).

## Section 1 — Notifications (`app/Notifications/`)

- **`TeamPublish`**: add optional `?string $message = null` constructor arg;
  when present, include it as an extra line in `toMail()`. `via()` unchanged
  (mail only). Keeps `ShouldQueue`.
- **`TeamUpdated`**: add optional `?string $message = null` constructor arg;
  include in `toMail()`, and surface in `toArray()`/`toWebPush()` body where
  sensible. `via()` unchanged (database + WebPush always, mail gated by
  `subscriptionSettings->email`). Keeps `ShouldQueue`.

## Section 2 — Recipient resolution

Add `sendToPlatformPlayers(TeamRound $team, TeamNotificationType $type, ?string $message): array`
to `FlyCompany\TeamFight\Notifier`:

1. Traverse `team.squads.categories.players` → distinct `member_ref_id`s.
2. Load `User` where `player_id IN (...)` **and**
   `clubhouse_id = team.clubhouse_id`.
3. **Reachable** = players with a matching user; **skipped** = player names with
   no matching user.
4. Build the notification instance from `$type` (`TeamPublish` or
   `TeamUpdated`) with `$message`, then `Notification::send($users, $notification)`.
5. Write **one** `TeamActivityLog`: action from `$type`,
   `recipient_type = PLATFORM`, `recipient_count` = reachable user count,
   `recipients_summary`, `metadata = { user_ids: [...], skipped_players: [...names] }`.
6. Return `['sentCount' => N, 'skippedPlayers' => [...names]]`.

## Section 3 — Enum, GraphQL, resolver

- `app/Enums/RecipientType.php`: add `case PLATFORM = 'PLATFORM';`.
- `graphql/team-round.graphql`:
  - Add `PLATFORM` to `ReceiverMethod`.
  - Add payload type:
    ```graphql
    type SendTeamNotificationResult {
        teamRound: TeamRound!
        sentCount: Int!
        skippedPlayers: [String!]!
    }
    ```
  - Change `sendTeamNotification` to return `SendTeamNotificationResult!`.
- `SendTeamNotification.php`: add `PLATFORM` branch calling
  `sendToPlatformPlayers`; wrap **all** branches into the payload shape
  (`MANUAL_EMAILS`/`TEST_SELF` return their own count with `skippedPlayers: []`).

## Section 4 — Frontend (`TeamFightNotify.vue`)

- Add a third recipient card **"Alle spillere på holdrunden"** (icon
  `account-group`) selecting `recipientType = 'platform'`, showing estimated
  `totalPlayersCount`. Existing `platform` branches need no logic change.
- `performPublish`: for `platform` send `emails: []`; update the mutation to
  select `sentCount` + `skippedPlayers`.
- Success snackbar: `"Beskeden er sendt til {sentCount} spillere"`. If
  `skippedPlayers.length > 0`, show a warning listing skipped names
  ("Følgende spillere har ingen konto og blev sprunget over: …").
- Activity-log rendering: PLATFORM entries store `user_ids`/`skipped_players`
  (no `emails`), so show a minimal summary
  ("X spillere / Y sprunget over") for those entries.

## Section 5 — Testing

- **PHPUnit** (`tests/GraphQL/TeamsTest.php`): use `Notification::fake()`. Seed a
  round with players where some `member_ref_id`s have linked users (varying
  `subscriptionSettings->email`) and some don't. Assert
  `Notification::assertSentTo` the right users with the right class,
  `sentCount`/`skippedPlayers` correct, and a `TeamActivityLog` row with
  `recipient_type = PLATFORM`. Verify opt-out user is still notified (channel
  gating is inside `via()`, not asserted at send level).
- **Dusk** (manual, per AGENTS.md): extend notify page object with the new card;
  happy-path test.

## Out of scope (YAGNI)

- SMS/phone (no infra).
- New notification classes (reuse the two existing).
- Per-player selection or new opt-out settings.
