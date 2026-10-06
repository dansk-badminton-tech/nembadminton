# Contributing to Nembadminton

Thank you for contributing to Nembadminton. Use these checks before submitting a pull request.

## PHP coding standards

We target **PHP 8.4**. Syntax linting, formatting and static analysis cover `app/` and `local-vendor/*/src/`. These checks run in the `build-test` CI job.

### Code style

Use Laravel Pint's `laravel` preset, configured in `pint.json`. The unused-import rule is disabled to match `.styleci.yml`.

```bash
composer format        # format the scoped PHP files
composer format:check  # check formatting without changing files
```

### Syntax

Check PHP syntax in the same source directories:

```bash
composer lint:syntax
```

### Static analysis

Larastan/PHPStan runs at **level 6**, configured in `phpstan.neon`:

```bash
composer analyse
```

Existing findings are recorded in `phpstan-baseline.neon`. Fix new findings rather than adding them to the baseline. When fixing existing findings, remove the corresponding obsolete baseline entries; avoid regenerating the baseline to hide new errors. The explicit exclusions in `phpstan.neon` are legacy files that PHPStan cannot baseline.

### Running PHP checks with Docker

The `app` container has PHP 8.4 but does **not** have the Composer executable. After installing dependencies and starting the app as described in [README.md](README.md#get-started), run the equivalent commands inside it:

```bash
docker compose exec -T app php vendor/bin/pint
docker compose exec -T app php vendor/bin/pint --test
docker compose exec -T app sh -c "find app local-vendor/*/src -name '*.php' -exec php -l {} +"
docker compose exec -T app php vendor/bin/phpstan analyse --memory-limit=1G
```

## Clubhouse isolation

A User reads and changes only their own Clubhouse's data. No global scope enforces this; each GraphQL field and mutation enforces it on its own ([ADR 0003](docs/adr/0003-clubhouse-isolation-per-graphql-field.md)), so every change to the schema, a policy, a model, or a resolver is held to the rules below. For an in-depth check of a branch before merging, run the `clubhouse-isolation-review` skill.

**Clubhouse data** is anything a Clubhouse owns: directly through `clubhouse_id` (TeamRound, Team, CancellationCollector, Invitation), through a parent that has one (Squad, SquadMember, SquadCategory, SquadPoint, TeamRoundScenario, TeamActivityLog, TeamReceivers and Cancellation, via `team_round_id` or `cancellation_collector_id`), and the Clubhouse's users, memberships and roles.

**Shared data** is the one exception: Members, Clubs, Points and ranking versions from badmintonplayer.dk. Any logged-in User may read and change it across Clubhouses, including `playable`, `inactive` and inactive overrides.

1. **Clubhouse check.** Every field or mutation that reads or writes Clubhouse data requires a logged-in User (`@guard`) and checks the Clubhouse. That check is one of these:
   - a policy (`@canFind`, `@canModel`, `@canResolved`, `@canQuery`) whose method compares `$user->clubhouse_id` with the model's Clubhouse;
   - `@inject(context: "user.clubhouse_id")` on create;
   - a resolver query filtered by `where('clubhouse_id', $user->clubhouse_id)`.

   A permission or role check (`@hasPermission`, `hasPermissionTo`, `hasRole`) says what the User may do, not whose data it is, so it is always paired with a Clubhouse check.
2. **Client ids are foreign until checked.** Every id that comes from the client must be shown to belong to the User's Clubhouse before anything is read or written through it. This covers a Clubhouse id, a foreign key (`teamId`, `teamRoundId`, a collector id) and the `connect` ids in nested input.
3. **Policies compare Clubhouses.** A policy method that guards Clubhouse data compares Clubhouses and returns a `bool` on every path.
4. **Public entry points.** These are reached by an unguessable id or token and are public on purpose: `teamRound(id)`, `cancellationCollectorPublic(sharingId)`, `createCancellation` and `invitation(token)`. Everything reachable through them is public too, so a new field or relation on a type they expose is an isolation change. Expose only what the link's recipient should see. A new public entry point is added to this list in the same pull request.
5. **New tables.** A new table holding Clubhouse data has a `clubhouse_id`, or a parent that leads to one.
6. **Isolation test.** Every new or changed field or mutation that touches Clubhouse data has a GraphQL test in which a User from another Clubhouse is denied or gets no data. See `it_denies_updating_team_in_another_clubhouse` in `tests/GraphQL/TeamsCrudTest.php`.

The super-admin bypass (`Gate::before`) is outside these rules.

## Testing

Run PHP tests (excluding tests that use remote services):

```bash
docker compose exec -T app php vendor/bin/phpunit --exclude-group remote
```

Run JavaScript unit tests:

```bash
yarn test:js
```

For end-to-end tests with Laravel Dusk, follow the [browser testing instructions](README.md#browser-tests-end-to-end). Browser tests run separately from the regular PR checks: add the `ci:browser-tests` label to the PR when its code and UI changes are done.

## Before opening a pull request

Run `composer lint:syntax`, `composer format:check` and `composer analyse`, plus the relevant tests for your change. CI also builds the frontend and validates the Help documentation.
