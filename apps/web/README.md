# Web

Classic browser assembly: session + CSRF + Twig + Orbit UI + FormRequest.  
No database. Bootstrap comes from Composer (`twbs/*`) via `publish-assets` → `public/vendor/` (gitignored).  
AOS lives in tracked `public/libs/aos`.

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
cp apps/web/composer-dev.json.example apps/web/composer-dev.json
cp apps/web/.env.example apps/web/.env
docker compose up -d --build web
docker compose exec web composer install
docker compose exec web composer phpstan

curl -s -c /tmp/web.ck -b /tmp/web.ck http://localhost:8082/ | head
# extract CSRF from HTML, then:
# curl -s -c /tmp/web.ck -b /tmp/web.ck -X POST http://localhost:8082/echo \
#   -d '_csrf_token=…&name=Ada&email=ada@example.com' -D -
```

Readers: `composer install` (Packagist) in `apps/web`, then point the web server at `apps/web/public`.
