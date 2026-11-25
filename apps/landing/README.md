# Landing

Browser marketing assembly: session + CSRF + Twig + Orbit UI + FormRequest.  
No database. Bootstrap comes from Composer (`twbs/*`) via `publish-assets` → `public/vendor/` (gitignored).  
AOS lives in tracked `public/libs/aos`.

Packagist: [`php-concept/app-landing`](https://packagist.org/packages/php-concept/app-landing).  
Split repo: [php-concept-apps/app-landing](https://github.com/php-concept-apps/app-landing).

## Endpoints

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/` | landing + FormRequest demo (`#try-it`) |
| `POST` | `/echo` | FormRequest (`name`, `email`) → redirect + flash |
| `GET` | `/hello/{name}` | route param → Twig |

## Run

```bash
composer create-project php-concept/app-landing
# or: git clone https://github.com/php-concept-apps/app-landing.git && cd app-landing

cp .env.example .env
docker compose up -d --build
docker compose exec landing composer install

curl -s -c /tmp/landing.ck -b /tmp/landing.ck http://localhost:8082/ | head
```

Monorepo (all apps + shared `docker/`): [concept-apps](https://github.com/php-concept-apps/concept-apps).
