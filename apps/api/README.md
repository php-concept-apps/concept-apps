# API

JSON API assembly: HTTP + validation + log masking + Eloquent database + console.  
No session or views. App-owned JSON envelope (`ApiResponseFactory`).

Catalog schema for load tests: `categories` (tree) → `products` (+ JSON attrs) ↔ `tags` (M2M).

## Endpoints

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/api/health` | liveness |
| `GET` | `/api/db` | DB probe + catalog row counts |
| `GET` | `/api/products` | paginated catalog (`page`, `per_page`, `status`, `category_id`, `q`) |
| `POST` | `/api/echo` | FormRequest (`name`, `email`) |

## Console

```bash
docker compose exec api composer console db:migrate
docker compose exec api composer console db:fill-catalog 1000
# load test volume:
docker compose exec api composer console db:fill-catalog 100000 --chunk=1000
```

`db:fill-catalog` uses Faker and chunked bulk inserts (products + `product_tag`).

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
curl -s 'http://localhost:8081/api/products?page=1&per_page=5&status=active'
curl -s -X POST http://localhost:8081/api/echo \
  -H 'Content-Type: application/json' \
  -d '{"name":"Ada","email":"ada@example.com"}'
```

Readers: `composer install` (Packagist) in `apps/api`, then point the web server at `apps/api/public`.
