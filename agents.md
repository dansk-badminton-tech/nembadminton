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

## End-to-end browser testing:
    Tool: Laravel dusk
    Documentation: https://laravel.com/docs/11.x/dusk#pages
    Location: tests/Browser/

### Structure of end-to-end tests:
1. Test class are extending DuskTestCase
2. We create page class per route in resources/js/admin-v2/router/index.js

### Running the browser tests locally:

To run the browser tests, execute the following command in your terminal:

```bash
docker compose run artisan dusk
```

This command will launch the browser tests defined in the `tests/Browser` directory.
