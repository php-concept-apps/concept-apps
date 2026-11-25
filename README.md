# CONCEPT-APPS

Monorepo of runnable **Concept 2** app assemblies. Source of truth for the split packages under [php-concept-apps](https://github.com/php-concept-apps) (`app-api`, `app-web`, …).

| App | Directory | Host | Packagist |
|-----|-----------|------|-----------|
| BFF | `apps/bff` | http://localhost:8080 | `php-concept/app-bff` |
| API | `apps/api` | http://localhost:8081 | `php-concept/app-api` |
| Landing | `apps/landing` | http://localhost:8082 | `php-concept/app-landing` |
| Web | `apps/web` | http://localhost:8083 | `php-concept/app-web` |
| CLI | `apps/cli` | — (console) | `php-concept/app-cli` |
| Worker | `apps/worker` | — (background) | `php-concept/app-worker` |

MySQL is published on host port **3306**. Details per app: [apps/README.md](apps/README.md).

---

## Run a single app locally

Pick **one** Compose file, copy it once, then start only the service you need (`depends_on` still starts `db`).

### Packagist (published packages)

```bash
cp docker-compose.yml.example docker-compose.yml

# example: API only
cp apps/api/.env.example apps/api/.env
docker compose up -d --build api
docker compose exec api composer install
```

Open http://localhost:8081 (or the port for your app from the table above).

### Sibling symlinks (maintainer DX)

Requires sibling checkouts next to this repo: `concept-core-2`, `concept-stack`, `concept-extensions`, `concept-bricks`, `concept-components`.

```bash
cp docker-compose.dev.yml.example docker-compose.yml

# example: API only
cp apps/api/composer-dev.json.example apps/api/composer-dev.json
cp apps/api/.env.example apps/api/.env
docker compose up -d --build api
docker compose exec api composer install   # COMPOSER=composer-dev.json
```

`docker-compose.yml`, `apps/*/composer-dev.json`, and `apps/*/composer-dev.lock` are gitignored.

### Other apps

Same pattern — replace `api` with `bff`, `landing`, `web`, `cli`, or `worker`:

```bash
cp apps/<name>/.env.example apps/<name>/.env
# symlink mode also needs:
# cp apps/<name>/composer-dev.json.example apps/<name>/composer-dev.json

docker compose up -d --build <name>
docker compose exec <name> composer install
```

To start everything: `docker compose up -d --build` (no service name).

---

## Release (tag → Split)

On `git push` of a `v*` tag, [`.github/workflows/split.yml`](.github/workflows/split.yml) mirrors each `apps/*` into `php-concept-apps/app-*` with the **same tag**.

### One-time setup

```bash
# empty GitHub repos app-* in org php-concept-apps
./scripts/create-split-repos.sh

# PAT: resource owner php-concept-apps, Contents: Read and write (app-* or all repos)
gh secret set SPLIT_PUSH_TOKEN --repo php-concept-apps/concept-apps
```

### Ship a version

```bash
git push origin main
git tag vX.Y.Z
git push origin vX.Y.Z
# CI Split → php-concept-apps/app-* (+ same tag) → Packagist
```

Local fallback (if CI is unavailable): `./scripts/split-packages.sh --tag vX.Y.Z`.

Bump `php-concept/*` constraints after extensions/bricks/components releases: see [scripts/README.md](scripts/README.md).

---

## Layout

```
apps/              # one directory = one assembly → split app-*
docker/            # shared Apache / PHP / MySQL (monorepo only)
packages.json      # monorepo path → split repo name
scripts/           # align-package-deps, create-split-repos, split fallback
.github/workflows/ # tag → Split
```
