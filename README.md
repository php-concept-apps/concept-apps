# concept-apps

Runnable **Concept 2** app assemblies (monorepo).

Each app under `apps/<name>` is a complete stack recipe — not a step-by-step tutorial.

| Repo | Role |
|------|------|
| **concept-apps** (this) | Ready-made assemblies (`apps/bff`, `apps/api`, …) |
| [concept-recipes](https://github.com/php-concept/concept-recipes) | Archived linear “create an app” walkthrough |

## Layout

```
apps/           # one directory = one assembly
components/     # shared Concept\Components\* scaffolds (opt-in per app)
docker/         # shared Apache/PHP config
composer.json   # Packagist (readers / CI)
```

## Composer (mode C)

**Readers / CI** — Packagist:

```bash
composer install
```

**Maintainer DX** — path repos + symlinks to sibling trees under `/var/www/concept/`:

```bash
cp composer-dev.json.example composer-dev.json
cp docker-compose.dev.yml.example docker-compose.yml
docker compose up -d --build          # all services
# or only what you need, e.g.:
# docker compose up -d --build bff    # + db via depends_on
# docker compose up -d --build api
# docker compose up -d --build web
docker compose exec php composer install   # COMPOSER=composer-dev.json from compose
```

`composer-dev.json`, `composer-dev.lock`, and `docker-compose.yml` are gitignored.

## Docker

```bash
cp docker-compose.yml.example docker-compose.yml
docker compose up -d --build          # all services in the file
# docker compose up -d --build api    # one app (+ db)
```

Two Compose examples (same as recipes):

| File | Use |
|------|-----|
| `docker-compose.yml.example` | Local run via Packagist (`composer.json`) |
| `docker-compose.dev.yml.example` | Local run + sibling symlinks (`composer-dev.json`) |

Copy either to `docker-compose.yml` (gitignored).

| Service | Role |
|---------|------|
| `php` | shell / Composer at monorepo root |
| `bff` | HTTP → `apps/bff` on host port **8080** |
| `api` | HTTP → `apps/api` on host port **8081** |
| `web` | HTTP → `apps/web` on host port **8082** |
| `cli` | Console / migrate entry → `apps/cli` (no published ports) |
| `db` | MySQL on host port **3306** |

`docker compose up` without a service name starts **everything**. Name the service (`bff`, `api`, `web`, `cli`, …) to limit the set; `depends_on` still pulls `db`.
