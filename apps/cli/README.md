# CLI

Console-only assembly: Logging + Database + Symfony Console.  
No HTTP, session, or views.

Shares the API database schema via `../api/database/migrations`.

## Commands

| Command | Notes |
|---------|--------|
| `app:hello` | smoke |
| `db:ping` | connectivity check |
| `db:migrate` / `db:rollback` | shared API migrations |
| `migration:list` / `migration:paths` | status / configured paths |

## Run (maintainer)

```bash
# from monorepo root
cp docker-compose.dev.yml.example docker-compose.yml
cp apps/cli/composer-dev.json.example apps/cli/composer-dev.json
cp apps/cli/.env.example apps/cli/.env
docker compose up -d --build cli
docker compose exec cli composer install
docker compose exec cli composer phpstan

docker compose exec cli php bin/console.php app:hello
docker compose exec cli php bin/console.php db:ping
docker compose exec cli php bin/console.php db:migrate
```

Or via Composer script: `docker compose exec cli composer console -- app:hello`.
