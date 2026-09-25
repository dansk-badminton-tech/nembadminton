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

## Testing

Run PHP tests (excluding tests that use remote services):

```bash
docker compose exec -T app php vendor/bin/phpunit --exclude-group remote
```

Run JavaScript unit tests:

```bash
yarn test:js
```

For end-to-end tests with Laravel Dusk, follow the [browser testing instructions](README.md#browser-tests-end-to-end). Browser tests run separately from the regular PR checks.

## Before opening a pull request

Run `composer lint:syntax`, `composer format:check` and `composer analyse`, plus the relevant tests for your change. CI also builds the frontend and validates the Help documentation.
