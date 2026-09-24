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

Follow `README.md` under **Testing > Browser tests (End-to-end)**. It is the source of truth for required Docker services, the Vite process, ports, full and filtered test commands, and rerunning failures.

### Running browser tests in CI:

Browser tests are **not** run automatically on push or PR. They must be triggered manually because they are time-consuming and typically only needed before merging.

The workflow is defined in `.github/workflows/browser-testing.yml`.

**From GitHub UI:** Go to Actions > "Browser testing" > "Run workflow".

**From the CLI** (requires [GitHub CLI](https://cli.github.com/)):

```bash
# Run on the current branch
gh workflow run "Browser testing"

# Run on a specific branch
gh workflow run "Browser testing" --ref my-branch

# Watch the run progress
gh run watch
```

Failed tests are automatically retried once via `dusk:fails` before the workflow is marked as failed.

## Agent skills

### Issue tracker

GitHub issues via `gh` CLI. See `docs/agents/issue-tracker.md`.
**All feature specs and research reports must live as GitHub issues** (not as local markdown files under `docs/`). Permanent architectural decision records (ADRs) are the only documentation files kept in the repository under `docs/adr/`.

### Triage labels

Canonical labels (`needs-triage`, `needs-info`, `ready-for-agent`, `ready-for-human`, `wontfix`). See `docs/agents/triage-labels.md`.

### Domain docs

Single-context (`CONTEXT.md` and `docs/adr/`). See `docs/agents/domain.md`.

