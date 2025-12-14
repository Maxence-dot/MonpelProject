# Architecture overview

Goal: keep the project small, modular, and easy to navigate.

Recommended layout (what we follow):

- Controllers/: HTTP entry points — thin controllers that call Services
- Services/: Business logic, orchestrates DB + Models + external APIs
- Models/: Plain data objects (POPO) representing DB entities
- Database/: DB helpers / connection / migrations runner
- migrations/: Migration files (timestamped)
- api/: JSON API endpoints (require Controllers)
- views/: server-side HTML views if any
- assets/: static assets (css, js, icons)

Notes:
- Use driver-aware migrations (sqlite vs mysql) to simplify local dev.
- Keep controllers thin — put logic in Services.
- Store prompt versions and AI explanations in the DB for auditability.

Files added:
- `Models/Party.php`, `Models/User.php`, `Models/Player.php` (simple POPOs)
- `migrations/20251215_rename_session_to_users.php` (safe migration to ensure `users` table)

If you want, I can next:
- Convert `Services` to return `Models` objects,
- Add a `Models/Repository` layer and `UserRepository`,
- Rename/move controller files into subfolders (e.g. `Controllers/Api/`).

Que souhaitez-vous que je fasse ensuite ?