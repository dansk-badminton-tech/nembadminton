## About this project

A website for holding turnerings rules in badminton. https://badminton.dk/holdturneringsregler/

## Domain Model

### Members vs Users
- **Members** (spillere): Badminton players with ranking data from badmintonplayer.dk
  - Table: `members`
  - Key fields: refId, name, gender, birthday, playable, inactive
  - Represents players tracked in the system (may or may not have user accounts)
  - Can be marked as `inactive=true` (stopped playing badminton permanently)
  - Can be marked as `playable=false` (temporarily unavailable/injured)

- **Users** (brugere): System accounts with login credentials
  - Table: `users`
  - Key fields: email, password, name, roles
  - Can be associated with a clubhouse for access control
  - Used for authentication and authorization

Testing:

## Running PHP and browser tests (any checkout or git worktree)

Use `bin/test`. On first run it sets the checkout up (`composer install`, `composer run setup`), runs the suite on its own isolated Docker stack (`docker-compose.test.yml`), and removes that stack afterwards, also when tests fail.

```bash
bin/test                         # Unit/GraphQL (excludes the "remote" group)
bin/test --filter SomeTest       # extra arguments go to the test runner
bin/test dusk                    # browser tests; runs `yarn build` first
bin/test dusk --filter LoginTest
```

Do not run tests against the dev stack (`docker compose exec app ...`). Stop `yarn dev` before `bin/test dusk` (it refuses to run while `public/hot` exists).

To look at a checkout's UI, run `bin/preview`. It builds the frontend, starts the app on a random localhost port with a fresh database seeded by `TestingDataSeeder`, and prints the URL and login (`testing@gmail.com` / `Test1234`). Run it again after frontend changes; stop it with `bin/preview down`. It is separate from `bin/test`, so running tests doesn't affect it.

## JavaScript unit testing:
    Tool: Node's built-in test runner (`node:test`)
    Location: tests/js/

Run with `yarn test:js`. Use it for framework-free modules such as the Help documentation logic in `resources/js/admin-v2/help/`.

## End-to-end browser testing:
    Tool: Laravel dusk
    Documentation: https://laravel.com/docs/11.x/dusk#pages
    Location: tests/Browser/

### Structure of end-to-end tests:
1. Test class are extending DuskTestCase
2. We create page class per route in resources/js/admin-v2/router/index.js

### Running the browser tests locally:

Run them with `bin/test dusk` (see above). For running them against the dev stack by hand, follow `README.md` under **Testing > Browser tests (End-to-end)**. It is the source of truth for required Docker services, the Vite process, ports, full and filtered test commands, and rerunning failures.

### Running browser tests in CI:

Browser tests are slow, so they don't run on every PR push. They run when a PR has the `ci:browser-tests` label, and on every push to `master`. The workflow is defined in `.github/workflows/browser-testing.yml`.

- Add the label once the PR's code and UI changes are done. The tests run when the label is added and again on every later push while it stays on; a newer push cancels the older in-progress run.
- Agents: add the label when a PR that changes UI or browser tests is done (`gh pr edit <number> --add-label ci:browser-tests`). Don't use `gh workflow run`.
- A passing run is not required to merge.
- To rerun without pushing, remove the label and add it again, or use Actions > "Browser testing" > "Run workflow".

## Agent skills

### Issue tracker

GitHub issues via `gh` CLI. See `docs/agents/issue-tracker.md`.
**All feature specs and research reports must live as GitHub issues** (not as local markdown files under `docs/`). Permanent architectural decision records (ADRs) are the only documentation files kept in the repository under `docs/adr/`.

### Triage labels

Canonical labels (`needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`). See `docs/agents/triage-labels.md`.

### Domain docs

Single-context (`CONTEXT.md` and `docs/adr/`). See `docs/agents/domain.md`.
