# BFF

Backend-for-frontend assembly: HTTP JSON API + validation + log masking.  
No database, session, or views.

Packagist: [`php-concept/app-bff`](https://packagist.org/packages/php-concept/app-bff).  
Split repo: [php-concept-apps/app-bff](https://github.com/php-concept-apps/app-bff).

## Endpoints

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/api/health` | liveness |
| `POST` | `/api/echo` | FormRequest (`name`, `email`) |

## Run

```bash
composer create-project php-concept/app-bff
# or: git clone https://github.com/php-concept-apps/app-bff.git && cd app-bff

cp .env.example .env
docker compose up -d --build
docker compose exec bff composer install

curl -s http://localhost:8080/api/health
curl -s -X POST http://localhost:8080/api/echo \
  -H 'Content-Type: application/json' \
  -d '{"name":"Ada","email":"ada@example.com"}'
```

Monorepo (all apps + shared `docker/`): [concept-apps](https://github.com/php-concept-apps/concept-apps).
