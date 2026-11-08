# concept-apps

Runnable **Concept 2** app assemblies (monorepo).

Each app under `apps/<name>` is a complete stack recipe — not a step-by-step tutorial.

| Repo | Role |
|------|------|
| **concept-apps** (this) | Ready-made assemblies (`apps/bff`, `apps/cli`, …) |
| [concept-recipes](https://github.com/php-concept/concept-recipes) | Archived linear “create an app” walkthrough |

## Layout

```
apps/           # one directory = one assembly (added later)
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
docker compose up -d --build
docker compose exec php composer install   # COMPOSER=composer-dev.json from compose
```

`composer-dev.json`, `composer-dev.lock`, and `docker-compose.yml` are gitignored.

## Docker

```bash
cp docker-compose.yml.example docker-compose.yml
docker compose up -d --build
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
| `db` | MySQL on host port **3306** |
