# WordPress Boilerplate

A Composer-based WordPress setup with a structured deployment workflow, environment configuration, and a set of local development commands.

## Getting started

1. Clone this repository.
2. Copy `.env.sample` to `.env.local` and fill in your local database credentials.
3. Copy `.env` and replace every `generate-a-unique-value-here` placeholder with a real value — use the [WordPress secret key generator](https://api.wordpress.org/secret-key/1.1/salt/).
4. Run `composer install` to download WordPress core, plugins, and themes and rebuild symlinks.
5. Run `yarn install` to make the local commands available.

## File structure

```
.
├── bin/
│   ├── post-install            # Composer hook — see Dependency management
│   ├── finish-deploy           # Server-side deploy script, invoked by CI
│   ├── backup-site             # Back up database and/or files locally
│   ├── clear-backups           # Remove local database and/or file backups
│   ├── db-sync                 # Pull/push/recover database dumps between local and remote
│   ├── load-local-environment  # Sourced by other bin scripts — checks PATH requirements
│   └── help                    # Prints available yarn commands
├── bootstrap/                  # Utility code loaded before everything else — not environment-specific
├── config/
│   ├── global-config.php       # Loaded in every environment
│   ├── local-config.php        # Local development config
│   └── production-config.php   # Production config
├── content/                    # wp-content equivalent
│   ├── plugins/                # Third-party plugins — installed by Composer, not committed
│   ├── themes/                 # Themes — installed by Composer, not committed
│   ├── uploads/                # Symlink: shared/uploads (prod) or local dir (dev)
│   └── cache/                  # Written by caching plugins at runtime — never committed
├── public/                     # WordPress core — installed by Composer, not committed
│   └── wp-config.php           # The one file in public/ that IS committed
├── utils/                      # Utility PHP files
├── composer.json
├── package.json
└── .gitignore
```

None of the `.env` files live in version control in a real project. The ones included here are templates — replace all placeholder values before use.

## Configuration

`wp-config.php` loads `config/global-config.php`, which applies in every environment, along with whichever environment-specific config file matches where the site is currently running (`local-config.php`, `production-config.php`). Only one environment's config file survives a given deploy — see Deployment process below.

## Environment configuration

There are two kinds of environment files:

- A single **global `.env`** file, always loaded regardless of environment. Holds auth keys and salts.
- **Per-environment files** (`.env.local`, `.env.production`, etc.) providing whatever needs to differ between environments — database credentials, site URL, cache settings.

In real use these live directly on each environment and are placed manually. No deploy, backup, or restore process ever touches them.

## Namespace

PHP files in this boilerplate use `your_project` as a namespace placeholder. Search-replace it with your own vendor name before use.

## Dependency management

`composer.json` is what actually builds a working WordPress install. Running `composer install` (or `update`) downloads WordPress core itself, installs every plugin and theme, and — via its own post-install hook (`bin/post-install`) — puts core in place and rebuilds the symlinks the site depends on.

**WordPress core** comes from `johnpbloch/wordpress-core`, installed into a temporary folder first rather than directly into `public/`. `bin/post-install` then moves it into place. This two-step avoids any chance of the installer overwriting the committed `wp-config.php`.

**Free plugins and themes** come from [WPackagist](https://wpackagist.org), which mirrors the official WordPress.org directory as installable packages.

## Symlinks

Rebuilt automatically on every `composer install`/`update`, in both local and production environments:

| Symlink | Points to |
|---|---|
| `public/content` | `../content` |
| `public/favicons` | `../assets/favicons` |
| `public/favicon.ico` | `../assets/favicons/favicon.ico` |
| `public/favicon.svg` | `../assets/favicons/favicon.svg` |
| `content/uploads` | `../../shared/uploads` (local) |

In production, `content/uploads` gets pointed at the real server path separately, after the build actually lands there.

## Local commands

Run `yarn :help` to list all available commands. Run `yarn <group>:help` for details on a specific group.

### Backup

| Command | Description |
|---|---|
| `yarn backup` | Back up database and files |
| `yarn backup:db` | Back up database only |
| `yarn backup:files` | Back up files only |
| `yarn db:backup` | Alias for backup:db |

### Cleanup

| Command | Description |
|---|---|
| `yarn cleanup` | Remove all database and file backups |
| `yarn cleanup:db` | Remove database backups only |
| `yarn cleanup:files` | Remove file backups only |

### Database sync

| Command | Description |
|---|---|
| `yarn db:backup` | Back up local database only |
| `yarn db:pull` | Pull database from a remote environment |
| `yarn db:push` | Push database to a remote environment |
| `yarn db:recover` | Import a local database dump interactively |
| `yarn db:restore` | Alias for `db:recover` |

## Must-use plugins

This boilerplate does not include any mu-plugins. The `content/mu-plugins/` directory is the right place to add your own — anything committed there loads automatically on every request, before regular plugins, with no activation step.

A useful pattern for managing mu-plugins across environments is a small feature flag layer: each plugin registers a named feature with a manager, and a separate plugin enables or disables features based on the current environment. This makes it straightforward to, for example, suppress outgoing email or deactivate analytics plugins locally without changing any code. The source project this boilerplate is based on implements this pattern and can serve as a reference.

## Deployment architecture

```
/var/www/your-site.com/
├── releases/
│   ├── 1000000000/       # One full, isolated build per deploy
│   └── 1000000001/
├── shared/
│   └── uploads/          # Persistent — survives every deploy
├── current -> releases/1000000001/    # The web server actually points here
└── bin/                  # Never touched by any deploy
```

The web server always points at `current`, which is just a symlink re-pointed to a new release on every deploy.

## Deployment process

**Triggers**: manual `workflow_dispatch` by default. Automatic deploys on push to `main` are available but commented out in `deploy.yml` — uncomment the `push` trigger once you're confident in the setup.

**Build stage** (runs on GitHub Actions):
1. Check out the repo, set up PHP.
2. Run `composer update` to build a complete, working copy of the site.
3. Transfer that build to a staging location on the server.
4. Hand off to `finish-deploy` on the server to actually go live.

**`finish-deploy`**, running on the server:
1. Merges forward anything the previous release generated at runtime that neither git nor Composer manages (cache, plugin-written files).
2. Takes a safety backup immediately before anything goes live.
3. Moves the new build into `releases/`.
4. Removes anything that shouldn't be part of a live release — `bin/`, unused config files, `composer.json`, `.github/`.
5. Confirms the release is complete before proceeding. If something's missing, the broken release is set aside and the live site is left untouched.
6. Makes the new release live and restarts the necessary services.
7. Cleans up old releases, keeping the 3 most recent.

## GitHub Actions secrets

Set these in the repository's **Settings → Secrets and variables → Actions**:

| Secret | Description |
|---|---|
| `DEPLOY_SSH_KEY` | Private SSH key for the deploy user on the production server |
| `DEPLOY_HOST` | Production server hostname or IP |
| `DEPLOY_USER` | SSH user on the production server |
| `SITE_NAME` | Site name as it appears in `/var/www/` on the server (e.g. `your-site.com`) |

*Note:* You may need to create new secrets and workflows for each environment you want to deploy to.

If your `composer.json` references private repositories (e.g. premium plugins hosted on GitHub), add a separate SSH key secret for each one and load them in a dedicated SSH agent step before `composer update` runs — then replace that agent with a fresh one carrying only `DEPLOY_SSH_KEY` before connecting to the server. This avoids offering the wrong keys to the production server and hitting its `MaxAuthTries` limit.

## Server-side tools

Provisioning, post-deployment tasks, and database management are handled by a dedicated server-tools project, which lives outside this repository.

## Scheduled deploys and keep-alive

GitHub's scheduled workflow trigger is unreliable for anything that genuinely needs to run on time — it silently stops firing after 60 days with no repo activity, and even when active it can be delayed by hours under load. For this reason, both the daily deploy schedule and the keep-alive commit are better managed externally via a Cloudflare Worker (or any equivalent cron service) that hits the `workflow_dispatch` endpoint of each workflow on the desired schedule. This removes the dependency on GitHub's scheduler entirely.

The `keep-alive.yml` workflow is kept in the repo as a fallback for environments where an external scheduler isn't set up yet. It creates an empty commit weekly so that scheduled workflows don't get auto-disabled — but it is not a substitute for an external trigger if the deploy schedule actually matters.
