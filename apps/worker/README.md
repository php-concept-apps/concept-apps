# Worker

Long-running background process against the shared API database.  
Stack: Masking + Logging + Database. No HTTP, console, session, or view.

**Purpose:** consume work in a loop (jobs, pipelines, outbox, sync) — not one-shot ops (`cli`) and not request/response (`api`/`landing`).

Smoke entry runs one DB tick; `--loop` repeats every 5s.

Packagist: [`php-concept/app-worker`](https://packagist.org/packages/php-concept/app-worker).  
Split repo: [php-concept-apps/app-worker](https://github.com/php-concept-apps/app-worker).

## Run

```bash
composer create-project php-concept/app-worker
# or: git clone https://github.com/php-concept-apps/app-worker.git && cd app-worker

cp .env.example .env
docker compose up -d --build
docker compose exec worker composer install

docker compose exec worker php bin/worker.php
docker compose exec worker php bin/worker.php --loop
```

Monorepo (all apps + shared `docker/`): [concept-apps](https://github.com/php-concept-apps/concept-apps).
