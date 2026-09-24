# Nembadminton

[Nembadminton](https://nembadminton.dk) er udviklet som et bidrag til badmintonsporten i Danmark for at gøre det nemmere at være træner og frivillig.

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

Start the application services. `app` remains available on port 80, while the Dusk application uses `artisan-serve` on port 8000:

```bash
docker compose up -d app artisan-serve selenium
```

Keep the Vite development server running in a separate terminal. Dusk pages use the URL in `public/hot` when that file exists, so the browser page will be blank if Vite is unavailable.

```bash
yarn run dev
```

Run the browser tests:

```bash
docker compose run --rm artisan dusk
```

Run one test class or method:

```bash
docker compose run --rm artisan dusk --filter=TeamFightConstruct13KampsTest
docker compose run --rm artisan dusk --filter=test_user_can_construct_holdrunde_with_13_kamps_hold
```

**Re-run only failed tests:**

```bash
docker compose run --rm artisan dusk:fails
```

The host can open the Dusk application at `http://localhost:8000`. Selenium shares the Compose network and uses `APP_URL=http://artisan-serve`, which reaches the service directly on its internal port 80.

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
