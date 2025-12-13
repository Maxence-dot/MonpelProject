<!--
Guidance for AI coding agents working on MonpelProject
Keep concise, concrete and based on discoverable patterns in the repo.
-->
# Copilot / AI Agent Instructions — MonpelProject

Purpose
- Help contributors and AI assistants quickly make safe, correct changes to this small PHP web app (Murder-party creator).

Big picture
- Very small, procedural PHP site serving HTML/CSS/JS from the repo root.
- `index.php` is the main entry; it includes `connexionAll.php` to load configuration and the PDO DB connection.
- No framework or routing—changes usually touch `index.php`, `connexionAll.php`, or static assets (`style.css`).

Key files to inspect
- `index.php` — UI and front controller (contains inline JS and a debug `var_dump($user)`).
- `connexionAll.php` — loads config and creates `$pdo` using `PDO` (expects `DB_DSN` in the config).
- `config.sample.php` — example config; copy to a local config file (see notes).
- `.github/workflows/deploy.yml` — automatic FTP deploy to InfinityFree (uses secrets `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`).

Configuration & environment
- The repo ignores `config.php` / `config.local.php` (see `.gitignore`).
- Local development: create a `config.php` in the project root (not committed). It must include a `DB_DSN` key used by `connexionAll.php`.

Example `config.php` (place outside VCS / in .gitignored file):
```php
<?php
return [
  'DB_HOST' => 'localhost',
  'DB_NAME' => 'murdermaker',
  'DB_USER' => 'root',
  'DB_PASS' => '',
  'DB_DSN'  => 'mysql:host=localhost;dbname=murdermaker;charset=utf8mb4',
];
```

Note: `config.sample.php` doesn't define `DB_DSN`. Ensure the real `config.php` includes it (or `connexionAll.php` will fail).

Run & debug locally
- Quick local server: `php -S localhost:8000` from repo root, then open `http://localhost:8000`.
- To avoid DB errors during UI work, either create a local DB matching config, or use an SQLite DSN in `DB_DSN` for light testing: `'sqlite:' . __DIR__ . '/dev.sqlite'`.
- `connexionAll.php` turns on verbose errors when `$_SERVER['SERVER_NAME']` is `localhost` or `127.0.0.1`.

Deployment
- Deployment is done through `.github/workflows/deploy.yml` using `SamKirkland/FTP-Deploy-Action` to InfinityFree.
- Ensure repository secrets are set: `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`.
- If you introduce a build step (e.g., JS/CSS bundling), update `local-dir` to the build output in the workflow.

Conventions & patterns
- Database: use `PDO` with exceptions and `PDO::FETCH_ASSOC` (seen in `connexionAll.php`).
- Error visibility: enabled only on localhost as detected in `connexionAll.php` — follow that pattern for debugging vs. production.
- Language: UI and comments use French — mirror that for labels and comments when editing UI copy.

Common pitfalls discovered
- `index.php` contains a leftover `var_dump($user)` and a hard-coded test email in the query — remove or replace before production commits.
- `config.sample.php` is missing `DB_DSN` while `connexionAll.php` expects it — add to `config.php` when setting up dev environment.

PR guidance for agents
- Do not commit credentials or `config.php` (it's ignored). If you need a sample change, modify `config.sample.php` or add instructions to README.
- Remove debugging artifacts (e.g., `var_dump`, hard-coded test data) in separate small PRs with a clear message.
- When adding features that require a build step, update `.github/workflows/deploy.yml` so `local-dir` points to the build output.

If anything here is unclear or you want me to add examples (tests, Dockerfile, or a sample `config.php` generator), tell me which part to expand.
