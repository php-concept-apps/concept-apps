# Apps

One directory = one assembly.

| Directory | Status | Intent |
|-----------|--------|--------|
| `bff/` | ready (minimal) | HTTP JSON + validation + masking; no DB/session/view |
| `api/` | ready (minimal) | JSON API + Eloquent; no session/view |
| `web/` | ready (minimal) | Session + CSRF + Twig views + FormRequest; no DB |
| `cli/` | ready (minimal) | Console + DB; shared API migrations; no HTTP |
| `worker/` | ready (minimal) | Background tick/loop + DB; logging + masking; no HTTP |

Each app owns its `composer.json`, `bootstrap/`, `routes/`, and entrypoint.

**Convention:** every app ships `phpstan.neon` (level 10) + `composer phpstan` (`require-dev`).
