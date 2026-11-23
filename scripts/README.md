# Scripts — concept-apps

Карта assemblies → split-репо: [`../packages.json`](../packages.json).

| Скрипт | Роль |
|--------|------|
| `align-package-deps.sh` | Вирівняти `php-concept/*` у apps + path versions у `composer-dev` + prod locks |
| `create-split-repos.sh` | Один раз: порожні GitHub-репо `app-*` в org |
| `split-packages.sh` | Локальний fallback split (звичайний реліз — через CI) |

Composer update — у Docker (`docker compose exec php …`), з мережею до Packagist/GitHub.

---

## Коли запускати align

Після того, як **extensions**, **bricks** і **components** уже в тегах з вирівняними cross-deps (CI Split green):

1. extensions: align → tag → Split  
2. bricks: align (`--bricks` / `--extensions`) → tag → Split  
3. components: align (`--components` / `--extensions`) → tag → Split  
4. **apps:** `align-package-deps.sh`

Інакше Composer знову впреться в старі internals опублікованих пакетів.

`core-2` / `stack` скрипт **не** чіпає, доки не передаси явно.

---

## `align-package-deps.sh`

**Що робить**

- У кожному `apps/*/composer.json` (і `require-dev`): constraint для наявних `php-concept/extension-*` / `brick-*` / `component-*` (або точковий пакет).
- У `composer-dev.json` / `.example`: ті самі require + pin у path `repositories[].options.versions` (`^X.Y` → `X.Y.0`).
- Потім `composer update --no-install` у **prod** `composer.json`: без lock — повний resolve; з lock — лише змінені пакети.

**Звідки constraint (без аргументів)**

| Префікс | Дефолт |
|---------|--------|
| `extension-*` | останній тег `../concept-extensions` |
| `brick-*` | останній тег `../concept-bricks` |
| `component-*` | останній тег `../concept-components` |

**Запуск**

```bash
docker compose exec php ./scripts/align-package-deps.sh
docker compose exec php ./scripts/align-package-deps.sh --dry-run
docker compose exec php ./scripts/align-package-deps.sh --no-update
docker compose exec php ./scripts/align-package-deps.sh --extensions ^3.0 --bricks ^3.0 --components ^1.0
docker compose exec php ./scripts/align-package-deps.sh --app web
docker compose exec php ./scripts/align-package-deps.sh --set php-concept/component-=^1.0
```

---

## Split (tag → CI)

На `push` тега `v*` workflow `.github/workflows/split.yml` сплітить кожен `apps/*` у `php-concept-apps/app-*` з тим самим тегом.

### Secret (один раз)

1. Fine-grained PAT: Resource owner **`php-concept-apps`**, All repositories (або всі `app-*`), **Contents: Read and write**.
2. На monorepo:

```bash
gh secret set SPLIT_PUSH_TOKEN --repo php-concept-apps/concept-apps
# paste PAT
```

### Репозиторії (один раз)

```bash
./scripts/create-split-repos.sh
```

### Реліз

```bash
git push origin main
git tag vX.Y.Z && git push origin vX.Y.Z
# CI Split → Packagist на app-* (create-project / clone)
```

Fallback локально: `./scripts/split-packages.sh --tag vX.Y.Z`

---

## Типовий bump після extensions/bricks/components релізу

```bash
cd /var/www/concept/concept-apps

docker compose exec php ./scripts/align-package-deps.sh --dry-run
docker compose exec php ./scripts/align-package-deps.sh

git add -A && git status
git commit -m "Bump php-concept extension-*, brick-*, component-* constraints."
git push origin main
```

---

## Чого не робити

| ❌ | ✅ |
|----|----|
| Bump apps, поки split extensions/bricks/components ще зі старими cross-deps | Спочатку їхній tag + CI Split |
| Тегати apps без `SPLIT_PUSH_TOKEN` | Спочатку secret, потім `v*` |
| Бампати `stack`/`core-2` «за компанію» без потреби | Окремий `PKG:CONSTRAINT` |
