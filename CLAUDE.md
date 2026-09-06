# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Rules for Claude

After writing or editing any PHP code, always run `/fix-phpcs` before considering the task complete.

Before committing, run `/code-review` — every push to `main` triggers a live deploy with no staging environment, so code should arrive reviewed on any branch.

Run `/security-review` when touching mu-plugins, especially any code that reads `$_GET`/`$_POST`, redirects, or changes per-environment behaviour.

## What this repo is

A Composer-based WordPress boilerplate. WordPress core, all plugins, and all themes are installed by Composer and are **not committed**. The only committed code is config files, mu-plugins, and the tooling scripts in `bin/`.

## Local commands

```bash
# First-time setup: install everything and activate git hooks
composer install    # installs WordPress core + plugins + themes + captainhook hooks
yarn install        # installs eslint and @wordpress/eslint-plugin

# Rebuild with fresh dependency resolution (drops composer.lock first)
yarn build          # rm -f composer.lock && composer update

# Add/update a specific dependency without a full rebuild
composer update     # resolves against existing composer.lock when present

# Database
yarn db:pull        # Pull DB from a remote environment
yarn db:push        # Push DB to a remote environment
yarn db:recover     # Import a local dump interactively

# Backups
yarn backup         # Back up database and files
yarn backup:db
yarn backup:files

# Help
yarn :help          # List all available commands
yarn db:help
```

There are no test suites in this project. Linting runs automatically via the captainhook pre-commit hook (see below). To run linters manually:

```bash
# PHP — checks staged or specific files against WordPress Coding Standards
vendor/bin/phpcs path/to/file.php
vendor/bin/phpcs content/mu-plugins/

# JS — checks staged or specific files
node_modules/.bin/eslint path/to/file.js
```

## Architecture

### Dependency management

`composer.json` is the complete recipe for a working WordPress install. Running `composer install` or `composer update`:

1. Downloads WordPress core into a temp dir (`.wp-core-tmp`) to avoid clobbering the committed `wp-config.php`.
2. Installs plugins and themes from [WPackagist](https://wpackagist.org) into `content/managed/`.
3. Runs `bin/post-install`, which moves core into `public/` and rebuilds all symlinks.

Nothing in `content/plugins/`, `content/themes/`, or `public/` should ever be committed.

### Directory layout that matters

- `wp-config.php` — committed at the app root (one directory above `public/`). WordPress finds it automatically there. `ABSPATH` inside it points to `public/`.
- `public/` — WordPress core (`wp-load.php`, `wp-admin/`, etc.). Nothing in here is committed.
- `content/` — the custom `wp-content` equivalent. `content/mu-plugins/` is the only subdirectory committed here.
- `content/managed/plugins/` and `content/managed/themes/` — where Composer installs plugins and themes. `bin/post-install` symlinks these into `content/plugins/` and `content/themes/`. Composer-managed extensions are always symlinks; manually installed extensions are real directories.
- `config/` — `global-config.php` is always loaded; the matching env file (`local-config.php`, `production-config.php`, etc.) is loaded automatically based on which file exists. `config/secrets.php` holds WordPress salts — never committed, carried forward across deploys or generated fresh on first deploy.
- `bootstrap/` — PHP files loaded before WordPress boots, via `andrezrv\utils\bootstrap()`.
- `utils/functions.php` — autoload setup, env-file loading, env-detection, and `is_managed_site()`.

### Environment configuration

Environment is determined by the filename of the `.env.{stage}` file present on disk (checked in order: `local`, `development`, `qa`, `staging`, `production`). A global `.env` is also loaded on top. Neither file is in this repo — they live on each environment and are placed manually.

Config files (`config/local-config.php`, etc.) follow the same priority order and are selected automatically by `utils/functions.php`.

`is_managed_site()` (in `utils/functions.php`) returns `false` if an `unmanaged` file exists anywhere up to 5 directory levels above `APPLICATION_PATH`. This lets `av-enable-disable-features` distinguish boilerplate-managed installs from standalone WordPress installs where the deploy system does not control everything.

### mu-plugins and the feature flag system

All custom must-use plugins live in `content/mu-plugins/`. They are built on `av-custom-features`, a small in-house feature flag framework:

- `av-custom-features` — provides `Custom_Feature_Manager` and the `make_custom_feature()` / `get_custom_feature_manager()` functions. A feature is registered by calling `make_custom_feature('name')`, which fires the `andrezrv/custom_features_init` action.
- `av-enable-disable-features` — the single place that decides which features are active per environment and site type. It evaluates callbacks to decide whether to call `disable_feature()` — e.g., mail suppression is only active locally; `recover_default_theme_directory` is only active locally or on unmanaged sites.
- `av-managed-extensions` — marks Composer-managed plugins and themes (identified by their being symlinks into `content/managed/`) as non-deletable in the WordPress admin. Adds a "Managed" filter tab on the plugins screen and blocks deletion both from the admin UI and direct URL access.
- `av-recover-default-theme-directory` — registers the default `wp-content/themes/` directory so bundled WordPress themes are available even with a custom `WP_CONTENT_DIR`. Active locally and on unmanaged sites only (see `av-enable-disable-features`).
- Each other mu-plugin registers its callbacks via `$feature->set_callback('hook', fn)` and attaches them with `$feature->callback('hook')`, which returns a no-op if the feature is disabled.

To add a new environment-conditional behavior: create a mu-plugin that registers a feature via `make_custom_feature()`, define its hook callbacks with `set_callback`, and if it should be off in some environments, add a `disable_feature()` call to `av-enable-disable-features.php`.

### Pre-commit hooks and coding standards

`captainhook.json` configures a pre-commit hook that runs `bin/lint-staged`. That script finds all staged PHP and JS files and checks them:

- **PHP**: `vendor/bin/phpcs` with the `WordPress` ruleset (configured in `phpcs.xml.dist`). Requires `composer install` to be available.
- **JS**: `node_modules/.bin/eslint` with `plugin:@wordpress/eslint-plugin/recommended` (configured in `.eslintrc.json`). Requires `yarn install` to be available.

The hook is installed automatically when `composer install` runs (via `bin/post-install`), so running `composer install` is all that's needed to activate it. The hook is a dev-only concern — the deploy workflow uses `--no-dev` so captainhook is never installed on the server.

### Deployment

Pushes to `main` trigger GitHub Actions. The workflow runs `composer update --no-dev` on GitHub's infrastructure, rsyncs the build to a staging path on the server, then invokes `bin/finish-deploy <site-name> <staging-path> <environment>`. `bin/finish-deploy` uses the `wps` server CLI to take a pre-deploy backup and atomically activate the new release.

`bin/finish-deploy` does the following before the swap:
- Merges forward `content/` items that git/Composer don't manage (cache, drop-ins, etc.), excluding `mu-plugins`, `managed`, and `uploads`.
- Carries forward manually installed plugins and themes (real directories, not symlinks) from the previous release.

After moving the build into `releases/<timestamp>/`:
- Removes `bin/` from the live release (only needed on the runner to invoke this script).
- Prunes `config/` to `${ENVIRONMENT}-config.php` and `global-config.php` only.
- Strips repo/dev artifacts: `composer.json`, `composer.lock`, `.github`, `phpcs.xml.dist`, `captainhook.json`, `CLAUDE.md`, `.claude`, `package.json`, `yarn.lock`, `node_modules`, `LICENSE`, `README.md`, and `.env*.sample` files.
- Carries `config/secrets.php` forward from the current release, or generates it fresh from the WordPress secret-key API on first deploy.
- Updates non-managed plugins and themes (real directories, not symlinks) via `wp-cli` after release activation.

Releases are kept in timestamped directories under `releases/`; `current` is a symlink re-pointed atomically. The 3 most recent releases are retained.
