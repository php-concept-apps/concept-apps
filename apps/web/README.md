# Web

Browser app assembly: session + CSRF + Twig + Eloquent.  
Database schema: `concept_web`.  
UI: [Arsha](https://bootstrapmade.com/arsha-free-bootstrap-html-template-corporate/) — Twig partials under `resources/views/frontend/partials/`.  
Assets:
- `public/vendor/` — Composer publish (`twbs/bootstrap`, `twbs/bootstrap-icons`; gitignored)
- `public/libs/` — third-party (AOS/GLightbox/php-email-form for public; OverlayScrollbars for admin)
- `public/assets/` — Arsha public UI
- `public/assets/admin/` — AdminLTE (`adminlte.min.css/js` + `app.*` overlays)

Authoring UI: `/admin` (AdminLTE).

Packagist: [`php-concept/app-web`](https://packagist.org/packages/php-concept/app-web).  
Split repo: [php-concept-apps/app-web](https://github.com/php-concept-apps/app-web).

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

## Run

```bash
composer create-project php-concept/app-web
# or: git clone https://github.com/php-concept-apps/app-web.git && cd app-web

cp .env.example .env
docker compose up -d --build
docker compose exec web composer install
docker compose exec web composer console db:migrate
docker compose exec web composer console db:seed

curl -s http://localhost:8083/ | head
```

Demo content (dev seed): ~8 categories, ~18 tags, ~45 posts. Author: Ada.

| Command | Notes |
|---------|--------|
| `composer console db:migrate` | apply migrations |
| `composer console db:seed` | seed users; in `APP_ENV=dev` also categories/tags/posts |
| `composer phpstan` | static analysis (`require-dev`) |

Monorepo (all apps + shared `docker/`): [concept-apps](https://github.com/php-concept-apps/concept-apps).
