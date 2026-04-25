# Nembadminton

[nembadminton](https://nembadminton.dk) udviklet som et bidrag til badmintonsporten i Danmark for at gøre det nemmere at være træner og frivillig.

## Get started
Kør følgende kommandoer i din terminal
```
docker-compose run --rm composer install
cp .env.example .env
docker-compose up -d app
docker-compose run --rm artisan key:generate
docker-compose run --rm artisan migrate
docker-compose run --rm artisan badmintonplayer-api-import:club
docker-compose run --rm artisan db:seed RolesAndPermissionsSeeder
yarn install
yarn run dev
```
Opsætning af auth via password
```
docker-compose run --rm artisan passport:keys
docker-compose run --rm artisan passport:client --password

Ændre i .env, variablerne PASSPORT_CLIENT_ID og PASSPORT_CLIENT_SECRET
```

Opret en bruger `http://localhost/new-user`

## Testing

### Browser tests (End-to-end)

Browser tests use [Laravel Dusk](https://laravel.com/docs/11.x/dusk) and are located in `tests/Browser/`.

**Run locally:**

```bash
docker compose run artisan dusk
```

**Re-run only failed tests:**

```bash
docker compose run artisan dusk:fails
```

**Run in CI:**

Browser tests are **not** run automatically on push or PR — they must be triggered manually since they are time-consuming. Typically only needed before merging.

From the GitHub UI: Go to Actions > "Browser testing" > "Run workflow".

From the CLI (requires [GitHub CLI](https://cli.github.com/)):

```bash
# Run on the current branch
gh workflow run "Browser testing"

# Run on a specific branch
gh workflow run "Browser testing" --ref my-branch

# Watch the run progress
gh run watch
```

Failed tests are automatically retried once via `dusk:fails` before the workflow is marked as failed.

## Projekt management

**Forslår en feature:** https://github.com/dansk-badminton-tech/nembadminton/issues/new/choose

**Fundet en fejl?:** https://github.com/dansk-badminton-tech/nembadminton/issues/new/choose

**Project:** https://github.com/dansk-badminton-tech/nembadminton/projects/2
