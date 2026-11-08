# Apps

One directory = one assembly.

| Directory | Status | Intent |
|-----------|--------|--------|
| `bff/` | ready (minimal) | HTTP JSON + validation + masking; no DB/session/view |
| `api/` | — | JSON API |
| `cli/` | — | Console-only |
| `worker/` | — | Persistent / queue worker entry |

Each app owns its `composer.json`, `bootstrap/`, `routes/`, and entrypoint.
