# Clean reinstall runbook — website-livewire

| Field | Value |
|-------|-------|
| **Date** | May 16, 2026 |
| **Use with** | [architecture.md](./architecture.md) · [security-audit.md](./security-audit.md) |
| **Goal** | Wipe compromised host, redeploy hardened app, rotate secrets |

---

## How to use this doc

| Symbol | Meaning |
|--------|---------|
| **YOU** | Manual steps in cPanel, hosting panel, GitHub, password managers |
| **ME** | Changes in this git repo (tell the agent to implement or open a PR) |
| **BOTH** | You trigger deploy; agent prepares code; you verify on server |

Work **top to bottom**. Do not redeploy to production until **Phase 3 (ME)** is merged and **Phase 2 (secrets)** is planned.

---

## Phase 0 — Before you touch the server

### YOU

- [ ] Read [architecture.md](./architecture.md) §3 (directory map) so you know what you are deleting
- [ ] Block calendar time (~2–3 hours first pass)
- [ ] Do **not** download or open rogue `.php` files on your Mac
- [ ] Optional: export May SSL access log from `~/logs/` if you want to keep forensics (`joaoestima.com-ssl_log-May-2026.gz`)

### ME (agent — request when ready)

- [ ] Bump `livewire/livewire` to **≥ 3.6.4** and `laravel/framework` to patched version
- [ ] Run `composer audit` clean
- [ ] Fix `.github/workflows/deploy_via_ftp.yml` (775, excludes)
- [ ] Add `deploy/public_html.htaccess` (canonical root redirect)
- [ ] Add `composer audit` step to CI (fail on critical)
- [ ] Optional: contact form rate limiting

---

## Phase 1 — Pack up (backup only what you need)

You said you can drop the site and DB. Backup is **minimal**.

### YOU — save locally (no malware)

| Item | Where on server | Save to | Required? |
|------|-----------------|---------|-----------|
| **`.env` on server** | `public_html/website/.env` | Password manager / encrypted note — **copy values, then rotate all** | Yes (for reference) |
| **`public/website/cv.pdf`** | `website/public/website/cv.pdf` | Your Mac / repo | Yes if not in git |
| **Root `.htaccess` (good version)** | `public_html/.htaccess` | Compare with `deploy/public_html.htaccess` in repo after ME step | Optional |
| **Access logs** | `~/logs/*.gz` | `~/Downloads/forensics/` | Optional |
| **Laravel log tail** | `website/storage/logs/laravel-*.log` | View in browser, copy last 200 lines as text | Optional |
| **Database dump** | cPanel → phpMyAdmin | Skip (you will wipe DB) | No |

### Do **not** backup

- Any `.php` with random names (`fjtjhyo.php`, `nclfaeva.php`, shells under `vendor/`, etc.)
- `website_backup/` (infected copy)
- `vendor/` from server (rebuild via **Upload vendor.zip**, not a copy of the old tree)
- `node_modules/`

### ME

- [ ] Confirm `cv.pdf` path exists in repo or document where to upload post-deploy

---

## Phase 2 — Rotate secrets (do before or right after wipe)

Treat every value from the old `.env` as **burned** (it was on a compromised server and may have been in chat logs).

### YOU — rotate in this order

| Secret | Where to change |
|--------|-----------------|
| **FTP password** | cPanel → FTP Accounts → change password |
| **GitHub Actions** | Repo → Settings → Secrets → `FTP_PRODUCTION_PASSWORD` (and server/user if needed) |
| **`APP_KEY`** | New `.env` on server after deploy: `php artisan key:generate` |
| **MySQL password** | cPanel → MySQL → change user password for `joaoesti_website_livewire` (or create new DB + user) |
| **SMTP / mail password** | cPanel email / hosting mail panel |
| **Telegram bot token** | [@BotFather](https://t.me/BotFather) → revoke / new token |
| **Sentry DSN** | Sentry project → rotate DSN if offered |
| **Admin API** | Only if this site’s `.env` had API keys (check old `.env`) |

### YOU — GitHub

- [ ] Settings → Actions → review workflow runs / unauthorized changes
- [ ] Confirm only trusted users have repo access

---

## Phase 3 — Harden the repo (ME)

**Stop here until the agent finishes and you merge/push to `main`.**

Expected repo changes:

1. **`composer.json` / `composer.lock`** — Livewire ≥ 3.6.4, Laravel patch
2. **`.github/workflows/deploy_via_ftp.yml`** — `chmod 775` (not 777); exclude `public/hot`, `.env`, etc.
3. **`deploy/public_html.htaccess`** — copy to server manually (FTP does not upload `public_html/` parent)
4. **`.github/workflows/composer-audit.yml`** (optional) — audit on PR/push

### YOU after ME

- [ ] Pull latest `main`
- [ ] Skim PR / diff
- [ ] Push triggers deploy — **wait until Phase 4 wipe is done** before pushing, or push to a branch first

**Recommended:** ME completes Phase 3 → **you wipe (Phase 4)** → **then push to `main`** for first clean deploy.

---

## Phase 4 — Wipe the server (YOU — cPanel File Manager)

### 4.1 Delete under `public_html/`

Delete **everything** inside `public_html/` including:

| Path | Notes |
|------|--------|
| `public_html/index.php` | Attacker file |
| `public_html/.htaccess` | Compromised — will replace |
| `public_html/website/` | Full Laravel tree |
| `public_html/website_backup/` | **Must delete** — second infected copy |
| Any other folders/files | `mail` stays under `~/mail`, not here |

Leave `public_html/` as an **empty directory** (or only `.well-known` if host requires it for SSL).

### 4.2 Delete rogue PHP under `~/logs/`

| Path | Notes |
|------|--------|
| `logs/fjtjhyo.php` | Webshell |
| `logs/nclfaeva.php` | Webshell |
| Any other `*.php` in `logs/` | Delete all |

**Keep** `*.gz` log archives if you want forensics.

### 4.3 Do **not** delete (usually)

| Path | Notes |
|------|--------|
| `~/mail/` | Email |
| `~/.cpanel/` | Panel config |
| `~/ssl/` or AutoSSL caches | Let cPanel manage |

### 4.4 Optional — database

| Action | When |
|--------|------|
| Drop all tables or create new empty DB | You said DB can be wiped |
| cPanel → MySQL → phpMyAdmin → drop `joaoesti_website_livewire` tables or recreate DB | Before first `migrate` |

### 4.5 Checklist before Phase 5

- [ ] `public_html/` empty (except `.well-known` if needed)
- [ ] No `*.php` in `~/logs/`
- [ ] `website_backup/` gone
- [ ] FTP password already rotated
- [ ] GitHub secret updated
- [ ] Phase 3 code merged locally

---

## Phase 5 — First clean deploy (BOTH)

### 5.1 Install root redirect (YOU — one-time, FTP cannot do this)

FTP deploys only to `public_html/website/`. The domain hits `public_html/` first.

1. File Manager → `public_html` → **Create / upload** `.htaccess`
2. Paste contents from repo: **`deploy/public_html.htaccess`**
3. Confirm there is **no** `index.php` at `public_html/` root

### 5.2 Trigger deploy (YOU)

`vendor/` is **not** uploaded by the app FTP workflow (file-by-file vendor times out on this host). See [architecture.md §8.4](./architecture.md#84-composer-dependencies-vendor).

1. Merge hardened `main`
2. GitHub Actions → **Deploy via FTP Production** → run workflow → wait for green check
3. GitHub Actions → **Upload vendor.zip** → run workflow (check **Force** on a brand-new empty `website/`)
4. File Manager → `public_html/website/` → **Extract** `vendor.zip` next to `artisan` → **delete** the zip

When Composer dependencies change later: run **Upload vendor.zip** again and extract. Do not add `vendor/` back to `deploy_via_ftp.yml`.

### 5.3 Create server `.env` (YOU)

1. File Manager → `public_html/website/.env`
2. Copy from your local `.env.production` template with **new** secrets:

```env
APP_ENV=production
APP_DEBUG=false
# APP_KEY= — run artisan key:generate on server or paste from php artisan key:generate --show locally
```

3. Set new DB password, mail, Telegram, Sentry

### 5.4 First-time Laravel on server (YOU)

If host has **Terminal** in cPanel (or SSH):

```bash
cd ~/public_html/website
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

If **no** terminal: use cPanel **PHP** tools or run migrate once via temporary route (avoid) — prefer asking host to enable Terminal or SSH.

### 5.5 Permissions (YOU — if errors in logs)

| Path | Permission |
|------|----------------|
| `website/storage` | 775 |
| `website/bootstrap/cache` | 775 |
| Other dirs | 755 |
| Files | 644 |

Do **not** use 777.

### 5.6 Cron (YOU)

cPanel → Cron Jobs → every minute:

```bash
cd /home/joaoesti/public_html/website && php artisan schedule:run >> /dev/null 2>&1
```

Adjust username/path if panel shows a different home directory.

---

## Phase 6 — Verify (YOU)

| Test | Expected |
|------|----------|
| `https://joaoestima.com/` | Homepage loads |
| Contact form submit | Works; Telegram/mail if configured |
| CV download | Downloads PDF |
| `https://joaoestima.com/.env` | 403 or 404 |
| `https://joaoestima.com/vendor/` | 403 or 404 |
| `https://joaoestima.com/website_backup/` | 404 |
| File Manager → `website/public/` | Only `index.php` (+ assets), no random `.php` |
| `~/logs/` | No `.php` files |

### ME (optional follow-up)

- [ ] Add post-deploy checklist to README or this doc from your verification notes
- [ ] Rate limit contact form
- [ ] Remove or disable unused `/login` routes for production

---

## Work split summary

| Phase | YOU | ME (agent) |
|-------|-----|------------|
| 0 Prepare | Read docs, schedule | — |
| 1 Backup | `.env` values, cv.pdf, optional logs | Confirm assets in repo |
| 2 Secrets | Rotate FTP, DB, mail, Telegram, GitHub | — |
| 3 Harden code | Review & merge PR | Composer, workflow, htaccess template, audit CI |
| 4 Wipe | Delete `public_html/*`, `logs/*.php` | — |
| 5 Deploy | Root `.htaccess`, `.env`, artisan, cron, unzip `vendor.zip` | App FTP + **Upload vendor.zip** |
| 6 Verify | Browser + File Manager checks | Follow-up hardening |

---

## Suggested order for “working together”

```
Day 1 — ME: Phase 3 (repo hardening) → you review PR
Day 1 — YOU: Phase 2 (rotate FTP + GitHub secrets)
Day 2 — YOU: Phase 4 (wipe) → Phase 5.1 (root .htaccess) → push main (deploy)
Day 2 — YOU: Phase 5.3–5.6 (.env, migrate, cron, verify)
Day 3 — ME: optional rate limits, doc updates from your verification
```

**Tell the agent:** “Implement Phase 3 from reinstall-runbook” when you are ready for repo changes.

---

## Quick cPanel delete list (copy-paste checklist)

```
public_html/
  [ ] Delete index.php (hacker)
  [ ] Delete .htaccess (replace later from deploy/public_html.htaccess)
  [ ] Delete website/ (entire folder)
  [ ] Delete website_backup/ (entire folder)
  [ ] Delete any other files/folders

logs/
  [ ] Delete fjtjhyo.php
  [ ] Delete nclfaeva.php
  [ ] Delete yzzxjezl.php
  [ ] Delete any other .php
```

---

*Update this runbook after first successful clean deploy with any host-specific paths or commands you had to use.*
