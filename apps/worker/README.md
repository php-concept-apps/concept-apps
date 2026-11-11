# Worker

Long-running background process against the shared API database.  
Stack: Masking + Logging + Database. No HTTP, console, session, or view.

**Purpose:** consume work in a loop (jobs, pipelines, outbox, sync) — not one-shot ops (`cli`) and not request/response (`api`/`web`).

Smoke entry runs one DB tick; `--loop` repeats every 5s.

## Run (maintainer)

```bash
cp apps/worker/composer-dev.json.example apps/worker/composer-dev.json
cp apps/worker/.env.example apps/worker/.env
docker compose up -d --build worker
docker compose exec worker composer install
docker compose exec worker composer phpstan

docker compose exec worker php bin/worker.php
docker compose exec worker php bin/worker.php --loop
```
