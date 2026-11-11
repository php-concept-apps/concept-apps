# Landing

Browser marketing assembly: session + CSRF + Twig + Orbit UI + FormRequest.  
No database. Bootstrap comes from Composer (`twbs/*`) via `publish-assets` → `public/vendor/` (gitignored).  
AOS lives in tracked `public/libs/aos`.

The `web` name is reserved for a future fullstack (HTML + DB) assembly.

## Endpoints

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/` | landing + FormRequest demo (`#try-it`) |
| `POST` | `/echo` | FormRequest (`name`, `email`) → redirect + flash |
| `GET` | `/hello/{name}` | route param → Twig |

## Run (maintainer)

Symlink DX is intended **inside Docker** (`composer-dev.json` path repos → `/var/www/concept-*`).

```bash
# from monorepo root
cp docker-compose.dev.yml.example docker-compose.yml
cp apps/landing/composer-dev.json.example apps/landing/composer-dev.json
cp apps/landing/.env.example apps/landing/.env
docker compose up -d --build landing
docker compose exec landing composer install
docker compose exec landing composer phpstan

curl -s -c /tmp/landing.ck -b /tmp/landing.ck http://localhost:8082/ | head
# extract CSRF from HTML, then:
# curl -s -c /tmp/landing.ck -b /tmp/landing.ck -X POST http://localhost:8082/echo \
#   -d '_csrf_token=…&name=Ada&email=ada@example.com' -D -
```

Readers: `composer install` (Packagist) in `apps/landing`, then point the web server at `apps/landing/public`.
