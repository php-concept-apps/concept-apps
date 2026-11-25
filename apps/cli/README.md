# CLI

Console-only assembly: Logging + Database + Symfony Console.  
No HTTP, session, or views.

Shares the API database schema via `../api/database/migrations` (monorepo layout).  
In the split package, point migrations at a local copy of the API schema or mount `app-api`.

Packagist: [`php-concept/app-cli`](https://packagist.org/packages/php-concept/app-cli).  
Split repo: [php-concept-apps/app-cli](https://github.com/php-concept-apps/app-cli).

## Commands

| Command | Notes |
|---------|--------|
| `app:hello` | smoke |
| `db:ping` | connectivity check |
| `db:migrate` / `db:rollback` | shared API migrations |
| `migration:list` / `migration:paths` | status / configured paths |

## Run

```bash
composer create-project php-concept/app-cli
# or: git clone https://github.com/php-concept-apps/app-cli.git && cd app-cli

cp .env.example .env
docker compose up -d --build
docker compose exec cli composer install

docker compose exec cli composer console -- app:hello
docker compose exec cli composer console -- db:ping
docker compose exec cli composer console -- db:migrate
```

Monorepo (all apps + shared `docker/`): [concept-apps](https://github.com/php-concept-apps/concept-apps).
