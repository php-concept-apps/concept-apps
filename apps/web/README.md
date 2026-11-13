# Web

Browser app assembly scaffold: session + CSRF + Twig + Eloquent.  
Own database `concept_web` (same MySQL container as other apps, separate schema).  
UI: [Arsha](https://bootstrapmade.com/arsha-free-bootstrap-html-template-corporate/) — Twig partials under `resources/views/frontend/partials/`.  
Assets layout (same as skeleton/landing):
- `public/vendor/` — Composer publish (`twbs/bootstrap`, `twbs/bootstrap-icons`; gitignored)
- `public/libs/` — third-party (AOS/GLightbox/php-email-form for public; OverlayScrollbars for admin)
- `public/assets/` — Arsha public UI
- `public/assets/admin/` — AdminLTE (`adminlte.min.css/js` + `app.*` overlays)
Authoring UI: `/admin` (AdminLTE). Auth + posts CRUD come next.

## Endpoints

| Method | Path | Notes |
|--------|------|--------|
| `GET` | `/` | public scaffold (Arsha) |
| `GET` | `/admin/login` | session login (guest) |
| `POST` | `/admin/login` | authenticate |
| `GET` | `/admin` | authoring dashboard (auth required) |
| `POST` | `/admin/logout` | sign out |
| `*` | `/admin/posts` | posts CRUD |
| `*` | `/admin/categories` | categories CRUD |
| `*` | `/admin/tags` | tags CRUD |

Demo user: `ada@example.com` / `secret`.

## Database

Schema lives in `concept_web` (separate from `concept_api`).

| Command | Notes |
|---------|--------|
| `composer console db:migrate` | apply migrations |
| `composer console db:seed` | seed users; in `APP_ENV=dev` also categories/tags/posts (Faker) |

Demo content (dev): ~8 categories, ~18 tags, ~45 posts. Author: Ada.

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
