# API

JSON API assembly: HTTP + validation + log masking + Eloquent database + console + bearer token auth.  
No session or views. App-owned JSON envelope (`ApiResponseFactory`).

Catalog schema for load tests: `categories` (tree) → `products` (+ JSON attrs) ↔ `tags` (M2M).  
Auth: `users` + `api_tokens` (sha256 hash stored; plaintext token returned once at login).

## Endpoints

| Method | Path | Auth | Notes |
|--------|------|------|--------|
| `GET` | `/api/health` | — | liveness |
| `GET` | `/api/db` | — | DB probe + catalog row counts |
| `GET` | `/api/products` | — | paginated catalog (`page`, `per_page`, `status`, `category_id`, `q`) |
| `GET` | `/api/products/{id}` | — | show product |
| `POST` | `/api/products` | Bearer | create product (+ optional `tag_ids`) |
| `PATCH` | `/api/products/{id}` | Bearer | update product |
| `DELETE` | `/api/products/{id}` | Bearer | delete product |
| `POST` | `/api/auth/login` | — | issue token (`email`, `password`, optional `token_name`) |
| `GET` | `/api/auth/me` | Bearer | current user (`User` arg resolver) |
| `POST` | `/api/auth/logout` | Bearer | revoke current token |
| `POST` | `/api/echo` | — | FormRequest (`name`, `email`) |

## Console

```bash
docker compose exec api composer console db:migrate
docker compose exec api composer console db:fill-catalog 1000
# load test volume:
docker compose exec api composer console db:fill-catalog 100000 --chunk=1000
docker compose exec api composer console auth:user ada@example.com secret 'Ada'
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
curl -s -X POST http://localhost:8081/api/echo \
  -H 'Content-Type: application/json' \
  -d '{"name":"Ada","email":"ada@example.com"}'
```

### Auth (Bearer)

```bash
docker compose exec api composer console auth:user ada@example.com secret 'Ada'

# login → payload.token
TOKEN=$(curl -s -X POST http://localhost:8081/api/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"ada@example.com","password":"secret","token_name":"cli"}' \
  | php -r 'echo json_decode(stream_get_contents(STDIN))->payload->token;')

curl -s http://localhost:8081/api/auth/me -H "Authorization: Bearer $TOKEN"

curl -s -X POST http://localhost:8081/api/auth/logout -H "Authorization: Bearer $TOKEN"
```

Write routes without a token return `{ok:false,error:{message:"Unauthorized",code:401}}`.

### Products CRUD

`status` is `ProductStatus`: `draft` | `active` | `archived`.  
`category_id` / `tag_ids` must exist (after migrate + fill, or create your own).  
Create / update / delete require `Authorization: Bearer <token>`.

```bash
# list (pagination + filters) — public
curl -s 'http://localhost:8081/api/products?page=1&per_page=5&status=active'
curl -s 'http://localhost:8081/api/products?category_id=1&per_page=5'
# q matches name / sku / slug (substring). After create below, this returns the demo row:
# curl -s 'http://localhost:8081/api/products?q=SKU-100'

# create (auth)
curl -s -X POST http://localhost:8081/api/products \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "category_id": 1,
    "sku": "SKU-100",
    "name": "Demo product",
    "slug": "demo-product",
    "description": "Optional text",
    "price": 19.99,
    "currency": "USD",
    "stock": 10,
    "status": "active",
    "weight_grams": 500,
    "attributes": {"brand": "Acme", "color": "black"},
    "tag_ids": [1, 2]
  }'

# show — public
curl -s http://localhost:8081/api/products/1

# update (PATCH — only sent fields)
curl -s -X PATCH http://localhost:8081/api/products/1 \
  -H 'Content-Type: application/json' \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"stock": 9, "name": "Demo product updated", "tag_ids": [1]}'

# delete
curl -s -X DELETE http://localhost:8081/api/products/1 \
  -H "Authorization: Bearer $TOKEN"
```

Readers: `composer install` (Packagist) in `apps/api`, then point the web server at `apps/api/public`.
