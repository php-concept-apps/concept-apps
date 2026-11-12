# Web

Browser app assembly scaffold: session + CSRF + Twig + Eloquent.  
Own database `concept_web` (same MySQL container as other apps, separate schema).  
Blog domain comes later — this step is bootable glue only.

## Endpoints

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/` | scaffold home |

## Run (maintainer)

```bash
# from monorepo root
cp docker-compose.dev.yml.example docker-compose.yml
cp apps/web/composer-dev.json.example apps/web/composer-dev.json
cp apps/web/.env.example apps/web/.env
docker compose up -d --build web
docker compose exec web composer install
docker compose exec web composer phpstan

curl -s http://localhost:8083/ | head
```

If MySQL volume already existed before `concept_web` init SQL was added:

```bash
docker compose exec db mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS concept_web;"
```

Readers: `composer install` (Packagist) in `apps/web`, then point the web server at `apps/web/public`.
