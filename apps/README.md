# Apps

One directory = one assembly.

| Directory | Status | Intent |
|-----------|--------|--------|
| `bff/` | ready (minimal) | HTTP JSON + validation + masking; no DB/session/view |
| `api/` | ready (minimal) | JSON API + Eloquent; no session/view |
| `cli/` | — | Console-only |
| `worker/` | — | Persistent / queue worker entry |

Each app owns its `composer.json`, `bootstrap/`, `routes/`, and entrypoint.

**Convention:** every app ships `phpstan.neon` (level 10) + `composer phpstan` (`require-dev`).
