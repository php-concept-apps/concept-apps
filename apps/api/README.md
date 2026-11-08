# API

JSON API assembly: HTTP + validation + log masking + Eloquent database.  
No session or views. App-owned JSON envelope (`ApiResponseFactory`).

## Endpoints

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/api/health` | liveness |
| `GET` | `/api/db` | DB connection probe (`SELECT 1`) |
| `POST` | `/api/echo` | FormRequest (`name`, `email`) |

## Run (maintainer)

Symlink DX is intended **inside Docker** (`composer-dev.json` path repos → `/var/www/concept-*`).

```bash
# from monorepo root
cp docker-compose.dev.yml.example docker-compose.yml
cp apps/api/composer-dev.json.example apps/api/composer-dev.json
cp apps/api/.env.example apps/api/.env
docker compose up -d --build api   # api + depends_on db (not bff/php)
docker compose exec api composer install
docker compose exec api composer phpstan

curl -s http://localhost:8081/api/health
curl -s http://localhost:8081/api/db
curl -s -X POST http://localhost:8081/api/echo \
  -H 'Content-Type: application/json' \
  -d '{"name":"Ada","email":"ada@example.com"}'
```

Readers: `composer install` (Packagist) in `apps/api`, then point the web server at `apps/api/public`.
