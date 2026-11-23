# Scripts — concept-apps

| Скрипт | Роль |
|--------|------|
| `align-package-deps.sh` | Вирівняти `php-concept/*` у apps + path versions у `composer-dev` + prod locks |

Composer update — у Docker (`docker compose exec php …`), з мережею до Packagist/GitHub.

---

## Коли запускати

Після того, як **extensions** і **bricks** уже в тегах з вирівняними cross-deps (CI Split green):

1. extensions: align → tag → Split  
2. bricks: align (`--bricks` / `--extensions`) → tag → Split  
3. **apps:** `align-package-deps.sh`

Інакше Composer знову впреться в старі internals опублікованих пакетів.

`core-2` / `stack` скрипт **не** чіпає, доки не передаси явно.

---

## `align-package-deps.sh`

**Що робить**

- У кожному `apps/*/composer.json` (і `require-dev`): constraint для наявних `php-concept/extension-*` / `brick-*` (або точковий пакет).
- У `composer-dev.json` / `.example`: ті самі require + pin у path `repositories[].options.versions` (`^X.Y` → `X.Y.0`).
- Потім `composer update --no-install` у **prod** `composer.json`: без lock — повний resolve; з lock — лише змінені пакети.

**Звідки constraint (без аргументів)**

| Префікс | Дефолт |
|---------|--------|
| `extension-*` | останній тег `../concept-extensions` |
| `brick-*` | останній тег `../concept-bricks` |

**Запуск**

```bash
# обидві сімʼї з тегів siblings (зараз → ^3.0 / ^3.0)
docker compose exec php ./scripts/align-package-deps.sh

docker compose exec php ./scripts/align-package-deps.sh --dry-run
docker compose exec php ./scripts/align-package-deps.sh --no-update
docker compose exec php ./scripts/align-package-deps.sh --extensions ^3.0 --bricks ^3.0
docker compose exec php ./scripts/align-package-deps.sh --app web
docker compose exec php ./scripts/align-package-deps.sh extension-config:^3.0
docker compose exec php ./scripts/align-package-deps.sh --set php-concept/extension-=^3.0
```

---

## Типовий bump після extensions `v3.0.x` + bricks `v3.0.0`

```bash
cd /var/www/concept/concept-apps

docker compose exec php ./scripts/align-package-deps.sh --dry-run   # перегляд
docker compose exec php ./scripts/align-package-deps.sh

# review → commit apps/*/composer.json + composer.lock (+ composer-dev.json.example)
git add -A
git status
git commit -m "Bump php-concept extension-* and brick-* to ^3.0."
git push origin main
```

Dev path pins оновлюються в `composer-dev.json.example` (у git) і в локальному `composer-dev.json` (gitignore), якщо файл є.

---

## Чого не робити

| ❌ | ✅ |
|----|----|
| Bump apps, поки split extensions/bricks ще зі старими cross-deps | Спочатку їхній tag + CI Split |
| Чекати package locks у extensions/bricks | Там лише root lock; apps — свої prod locks |
| Бампати `stack`/`core-2` «за компанію» без потреби | Окремий `PKG:CONSTRAINT` |
