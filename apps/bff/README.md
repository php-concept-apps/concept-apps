# BFF

Backend-for-frontend assembly: HTTP JSON API + validation + log masking.  
No database, session, or views.

## Endpoints

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/api/health` | liveness |
| `POST` | `/api/echo` | FormRequest (`name`, `email`) |

## Run (maintainer)

Symlink DX is intended **inside Docker** (`composer-dev.json` path repos → `/var/www/concept-*`).

```bash
# from monorepo root
cp docker-compose.dev.yml.example docker-compose.yml
cp apps/bff/composer-dev.json.example apps/bff/composer-dev.json
cp apps/bff/.env.example apps/bff/.env
docker compose up -d --build
docker compose exec bff composer install

curl -s http://localhost:8080/api/health
curl -s -X POST http://localhost:8080/api/echo \
  -H 'Content-Type: application/json' \
  -d '{"name":"Ada","email":"ada@example.com"}'
```

Readers: `composer install` (Packagist) in `apps/bff`, then point the web server at `apps/bff/public`.
