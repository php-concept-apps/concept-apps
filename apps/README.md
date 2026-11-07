# Apps

One directory = one assembly.

Examples (not created yet):

| Directory | Intent |
|-----------|--------|
| `bff/` | HTTP API + validation + masking, no DB/session/view |
| `api/` | JSON API |
| `cli/` | Console-only |
| `worker/` | Persistent / queue worker entry |

Each app owns its `composer.json`, `bootstrap/`, `routes/`, and entrypoint.
