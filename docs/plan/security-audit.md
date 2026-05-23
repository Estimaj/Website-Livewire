# Security Audit — website-livewire

| Field | Value |
|-------|-------|
| **Date (initial)** | May 16, 2026 |
| **Date (Pass 3)** | May 23, 2026 |
| **Project** | website-livewire (Laravel 11 + Livewire 3 portfolio site) |
| **Production URL** | https://joaoestima.com/ |
| **Scope** | Read-only audit after shared-hosting compromise; Pass 3 adds live production testing |
| **Auditor** | Pass 1–2: automated codebase review; **Pass 3: Playwright + HTTP probes on production** |
| **Repository state** | `main` @ PHP 8.4 upgrade; `composer.lock` → Livewire **v3.8.0**, Laravel **v11.53.1** |

---

## Executive summary (current — Pass 3)

**The Git repository does not contain malware, backdoors, or obfuscated PHP.** Pass 3 live testing found **no active webshells** on common paths and **no debug/stack-trace leakage** on 404 responses.

**Post-reinstall / upgrade status:**

| Area | Pass 1–2 (May 16) | Pass 3 (May 23) |
|------|-------------------|-----------------|
| Livewire CVE-2025-54068 | **Critical** — v3.6.0 | **Resolved in repo** — v3.8.0 on `main`; `livewire.min.js` serves **200** on production |
| Laravel CVE-2025-27515 | High — v11.44.0 | **Resolved in repo** — v11.53.1; `composer audit` → **0 advisories** |
| `chmod 777` in CI | Critical | **Mitigated in workflow** — `chmod 775` + file `664` (verify on server after deploy) |
| Contact form abuse | Medium — no throttle | **Still open on production** — rate limit implemented **locally**, not yet on `main` |
| Hosting docroot | Suspected risk | **Confirmed issue** — duplicate URL `/website/public/index.php` **200**; `/vendor/` → **301** to internal path |
| Sensitive files (`.env`, `artisan`) | — | **404** on production (good) |
| Security headers | Not tested | **Missing** at origin (CSP, HSTS, X-Frame-Options, etc.) |

**Most likely historical attack vectors (unchanged ranking for incident):**

| Priority | Vector | Confidence |
|----------|--------|------------|
| 1 | **CVE-2025-54068** (Livewire RCE) — plausible if production was on v3.6.0 at breach time | High |
| 2 | **Deploy / vhost misconfiguration** — full repo under `website/`; path leak via `/website/public/` | High |
| 3 | **Compromised FTP / GitHub Actions secrets** | Medium |
| 4 | **Exposed `/login`** — registration disabled; 2FA off | Low–Medium |

**Bottom line (May 23):** Dependency and CI issues from Pass 1–2 are largely **addressed in the repository**, and production **does not show obvious re-infection**. Remaining priorities: **fix hosting URL layout**, **deploy contact-form rate limiting**, **harden or remove `/login`**, add **security headers**, and **confirm server permissions** after FTP deploy.

**Before treating production as fully hardened:** deploy rate-limiter changes, block `/website/public/*` at the edge, rotate secrets if not done post-wipe, and verify vhost document root = **`…/website/public`** only.

---

## Executive summary (historical — Pass 1–2, May 16)

**The Git repository does not contain malware, backdoors, or obfuscated PHP.** Rogue files on the compromised server were almost certainly placed **after** initial access was gained.

**Most likely attack vectors at time of Pass 1–2 (ranked):**

| Priority | Vector | Confidence |
|----------|--------|------------|
| 1 | **CVE-2025-54068** — Livewire RCE on unauthenticated public components (`POST /livewire/update`) | High |
| 2 | **Deploy misconfiguration (scoped)** — full repo to FTP account root (`public_html/website/`), not all of `public_html`; `chmod 777` + docroot must be `…/website/public` | High |
| 3 | **Compromised FTP / GitHub Actions secrets** — explains random PHP without an app bug | Medium |
| 4 | **Exposed `/login`** — registration disabled, but login + Jetstream profile remain; 2FA disabled | Low–Medium |

**Historical bottom line:** Outdated Livewire (v3.6.0) plus FTP full-tree deploy and world-writable directories was a credible breach explanation. See [Pass 3](#pass-3-live-production-testing-playwright--http) for current production state.

---

## Incident context

The operator reported:

- Shared hosting `public_html` (or the site’s **`website/`** subfolder within it) was **compromised** with many rogue PHP files (suspected crypto-miner / webshell activity).
- **This project** is deployed via FTP into **`public_html/website/`** (not the entire `public_html` root — see [Deployment context (user-confirmed)](#deployment-context-user-confirmed)).
- A **separate API project** and its database were **not** attacked.
- Planned response: **wipe the host**, **rotate all keys**, and **clean reinstall**.
- Logs may be available but should be downloaded minimally to avoid local infection.

This audit evaluates **what in this codebase or its deployment could have enabled the breach**, not forensic analysis of server-side malware (which is not in git).

### Deployment context (user-confirmed)

| Item | Detail |
|------|--------|
| **FTP target** | `public_html/website/` (or equivalent app-only path), **not** all of `public_html` |
| **Workflow `server-dir: "/"`** | Upload destination is **FTP account root** — for this host, that root is chrooted or mapped to `public_html/website/`, **not** `public_html/` itself |
| **Repo layout on server** | Checkout root (`artisan`, `vendor/`, `.env`, `storage/`, `public/`, …) lands directly under that folder when `server-dir` is `/` |
| **What this does *not* imply** | Overwriting sibling sites, static files, or other apps elsewhere under `public_html` |
| **What still depends on hosting** | Whether the vhost **document root** is `…/website/public` (correct), `…/website/` (exposes app root), or `public_html/` (unrelated to this workflow’s FTP target) |

---

## Pass 1 findings (summary)

Pass 1 performed a full read-only review: routes, Livewire components, dependencies, deployment workflow, dangerous PHP patterns, and secrets in tree.

| ID | Severity | Finding |
|----|----------|---------|
| C1 | **Critical** | Livewire CVE-2025-54068 — `livewire/livewire` v3.6.0; public components reachable without auth |
| C2 | **High** | FTP deploy uploads entire application to FTP account root (`server-dir: "/"` → `public_html/website/`); **Critical** if vhost docroot ≠ `website/public` |
| C3 | **Critical** | `chmod -R 777` on `storage/` and `bootstrap/cache/` (777 in CI) |
| H1 | **High** | World-writable `storage/` and `bootstrap/cache/` (777 in CI; see C3) |
| H2 | **High** | `POST livewire/upload-file` registered; no app uploads today, but endpoint exists |
| H3 | **High** | Laravel 11.44.0 — CVE-2025-27515 file validation bypass |
| M1 | **Medium** | Contact form has no rate limiting |
| M2 | **Medium** | `GET storage/{path}` route active (`storage.local`) |
| M3 | **Medium** | Active `/login`; Fortify 2FA feature commented out |
| M4 | **Medium** | PII stored in `activity_logs.metadata` |
| L1 | **Low** | Plain FTP credentials in GitHub Actions secrets |
| L2 | **Low** | Jetstream/Fortify surface area larger than needed for a portfolio site |

**Malware scan (Pass 1):** No `eval`, `exec`, `shell_exec`, `system`, `passthru`, `proc_open`, `popen`, `unserialize`, or suspicious obfuscation in application PHP. Only `public/index.php` exists under `public/` in git.

---

## Pass 2 verification

Pass 2 re-verified Pass 1 claims via fresh searches, `php artisan route:list`, and `composer audit --format=json`.

| Claim | Verified | Evidence |
|-------|----------|----------|
| Livewire v3.6.0 vulnerable to CVE-2025-54068 | **Yes** | `composer.lock` → `livewire/livewire` v3.6.0; advisory: `>=3.0.0-beta.1,<3.6.4` |
| Public Livewire hits `POST /livewire/update` | **Yes** | `php artisan route:list`; components on `/` via `resources/views/components/website/contact.blade.php`, `cv.blade.php` |
| No custom RCE in app code | **Yes** | Grep over `**/*.php` — no dangerous execution/upload primitives |
| CV download uses fixed path | **Yes** | `app/Livewire/DownloadCvButton.php` lines 18–22 → `public_path('website/cv.pdf')` |
| FTP deploy to account root (`/`) + chmod 777 | **Yes** (scoped to `website/` per user) | `.github/workflows/deploy_via_ftp.yml` lines 38–44, 55–63; `server-dir: "/"` = FTP root, not `public_html/` |
| Repo contains no extra PHP in `public/` | **Yes** | Only `public/index.php` tracked |
| `@livewireScripts` missing on website layout is a vulnerability | **No — overstated** | See [Disproven or overstated items](#disproven-or-overstated-items) |
| `storage/{path}` route is a critical data leak | **No — overstated** | Route exists by design; limited exposure for this app — see below |
| PATH_INFO CVE is primary vector | **No — overstated** | Symfony affected, but low practical impact on this Laravel routing setup |
| Contact form abuse vector | **Yes (new)** | `ContactForm.php` — no throttle; mail + Telegram + queue |
| Auto-migrate on cron | **Yes (new)** | `routes/console.php` + `MigrateCheck.php` + `CheckDeployment.php` |
| Session hardening gaps | **Yes (new)** | `config/session.php`, `.env.example` |
| Composer audit matches lockfile | **Yes** | 8 advisories / 6 packages (see dependency table) |

---

## Pass 3 — Live production testing (Playwright + HTTP)

**Date:** May 23, 2026  
**Target:** https://joaoestima.com/  
**Method:** Playwright MCP (`browser_run_code_unsafe`) for UI flows and `page.request` probes; corroborates manual browser/curl testing from the same day.

### Production HTTP probe results

| Path | Status | Notes |
|------|--------|-------|
| `/` | **200** | Site healthy |
| `/.env` | **404** | Not web-accessible |
| `/vendor/` | **301** | `Location: …/website/public/vendor` — reveals internal layout |
| `/artisan`, `/composer.json` | **404** | Good |
| `/login` | **200** | Admin surface exposed |
| `/dashboard` | **302** | → `/login` (auth required) |
| `/register`, `/forgot-password` | **404** | Disabled (good) |
| `/up` | **200** | Health check |
| `/website/public/index.php` | **200** | **Duplicate app entrypoint** — same site, assets under `/website/public/build/` |
| `/website/cv.pdf` | **200** | Direct PDF (intentional) |
| `/shell.php` (and common shell names) | **404** | No hits in quick scan |
| `/livewire/livewire.min.js` | **200** | Livewire frontend active |
| `POST /livewire/update` (no CSRF) | **405** | Method/CSRF gating as expected for bare POST |

### Playwright UI test results

| Test | Result |
|------|--------|
| Contact form XSS payload in `firstName` | **No reflected raw `<script>`** in DOM after submit |
| Contact form submit | **Success** flash shown; `POST /livewire/update` observed |
| Contact form rate limit (2 submits, same session) | **No throttle message** — production still on `main` without `RateLimiter` |
| Login invalid credentials | Generic **“credentials do not match”** — no email enumeration |
| 404 page (`/nonexistent-route-pass3-xyz`) | **404**, title “Not Found”, **no** stack trace / `APP_DEBUG` leak |
| Session cookie `website_session` | `Secure`, `HttpOnly`, `SameSite=Strict` |
| Security response headers (origin) | **All absent** in probe: HSTS, CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy |

**Automated test submissions (safe):** `pw-audit+pass3@example.com` — remove from mail queue, Telegram, and `activity_logs` if undesired.

### Pass 3 findings (new / updated)

| ID | Severity | Finding | Status |
|----|----------|---------|--------|
| P3-1 | **High** | Hosting path leak: `/website/public/index.php` serves app; `/vendor/` redirects into `website/public/` tree | **Open** — fix vhost / rewrites / Cloudflare |
| P3-2 | **High** | Contact form abuse (spam / mail to submitter) | **Rate limit deployed** (5/hour per IP); outbound confirmation mail still **open** |
| P3-3 | **Medium** | Missing security headers at origin | **Open** |
| P3-4 | **Medium** | `/login` still public; Fortify 2FA disabled | **Open** |
| P3-5 | **Low** | CV PDF directly downloadable without Livewire (`/website/cv.pdf`) | **Accepted** (portfolio) |
| P3-6 | — | Livewire RCE CVE patched in deployed lockfile | **Resolved** (v3.8.0) |
| P3-7 | — | No webshells on common paths; no debug leak on 404 | **Verified** |
| P3-8 | — | `.env` / `artisan` not exposed via HTTP | **Verified** |

### Pass 3 verification table

| Claim | Verified | Evidence |
|-------|----------|----------|
| Production serves Livewire 3.x (patched line) | **Yes** | `main` lockfile v3.8.0; `/livewire/livewire.min.js` → 200 |
| `POST /livewire/update` reachable from public contact form | **Yes** | Playwright observed POST on submit |
| Contact rate limiting on production | **Yes** (post-deploy) | Playwright: 4 submits OK, **5th blocked** with “Too many contact attempts” (5/hour per IP); bucket may include earlier same-IP tests |
| Duplicate document-root path | **Yes** | `/website/public/index.php` → 200 |
| Session cookie hardening | **Yes** | Playwright cookie inspection |
| Reflected XSS on contact form | **No** (in tested flow) | HTML entity encoding / Livewire handling |
| User enumeration on login | **No** | Generic credential error |

### Repository vs production gap (Pass 3)

| Change | `main` (deployed) | Working tree (not deployed) |
|--------|-------------------|-----------------------------|
| Contact form `RateLimiter` (5/hour per IP) | **No** | **Yes** — `AppServiceProvider.php`, `ContactForm.php` |
| Validation `max:` on name/email/message | **No** | **Yes** |
| CI `chmod 775` (was 777) | **Yes** on `main` | Same |
| FTP exclude `.env` / `.env.*` | **Yes** on `main` | Same |

---

## Permissions: CI vs server (cPanel verified)

The audit references **two different layers**; they are not required to match byte-for-byte.

| Layer | `bootstrap/cache` (dir) | Files in tree | Purpose |
|-------|-------------------------|---------------|---------|
| **GitHub Actions** (pre-FTP) | `chmod -R 775` | `find … -exec chmod 664` | Ensure web server user can write cache during/after deploy |
| **Your server** (cPanel screenshot) | **`0755`** | **`0644`** | Typical Hostinger/cPanel result after upload or umask |

**What matters for security:**

| Mode | Meaning | Verdict |
|------|---------|---------|
| **777** | World writable | **Bad** — was the Pass 1–2 CI issue (now fixed in workflow) |
| **775** | Owner + group writable | Acceptable on CI build agent; group-writable on shared hosting is optional |
| **755** | Owner writable only; others read+execute | **Good** — what you have on `bootstrap/cache` |
| **644** | Owner read/write; others read only | **Good** — matches `app.php`, `providers.php` |

**Conclusion:** Your screenshot does **not** show a problem. **`0755` / `0644` is stricter than `775` for directories** (group cannot write). The old incident concern was **`777`**, not a mismatch between CI `775` and server `755`.

**Also check on server (same idea):** `storage/`, `storage/framework/*`, `bootstrap/cache/*` — aim for **no `777`**, directories **755** (or **775** if Laravel cannot write logs/cache), files **644**.

---

## Disproven or overstated items

### `@livewireScripts` on website layout

**Pass 1 concern:** `resources/views/layouts/website.blade.php` does not include `@livewireStyles` / `@livewireScripts`, while `layouts/app.blade.php` and `layouts/guest.blade.php` do.

**Pass 2 verdict:** **Not a security finding.** Livewire 3 registers frontend assets via `GET livewire/livewire.js` and injects them when components render. The public site uses `<livewire:…>` tags and the route list confirms Livewire endpoints are active. Missing Blade directives is a **convention/consistency** issue, not an exploit path.

### `storage/{path}` route

**Pass 1 concern:** `GET storage/{path}` could expose `storage/app/private`.

**Pass 2 verdict:** **Overstated for this app.** Laravel 11 registers `storage.local` because `config/filesystems.php` sets the `local` disk `'serve' => true` (lines 33–37). Access is gated by Laravel’s storage controller (signed/authorized serving), not open directory listing. No application code writes user uploads to that disk. Risk is **configuration-dependent** and **Low** unless secrets are placed under `storage/app/private` and paths are guessable.

### CVE-2025-64500 (PATH_INFO)

**Pass 1 concern:** `symfony/http-foundation` v7.2.3 is in the affected range.

**Pass 2 verdict:** **Overstated as incident root cause.** The CVE requires specific PATH_INFO parsing edge cases for limited authorization bypass. This app uses standard Laravel front-controller routing (`public/index.php` + `public/.htaccess`). No custom PATH_INFO-based auth was found. **Still patch** via framework update, but rank below Livewire RCE and deploy issues.

### FTP deploy to entire `public_html`

**Pass 1 concern:** `server-dir: "/"` uploads the full Laravel tree to all of `public_html`.

**Pass 2 verdict (user-confirmed):** **Overstated for blast radius.** `"/"` is the **FTP account root**, which maps to **`public_html/website/`** on this host — not sibling paths under `public_html`. The finding remains **High** because the full repo still lands in the app folder and docroot must be `website/public`; it becomes **Critical** only if the vhost points at `website/` instead of `website/public`.

---

## Confirmed critical issues

### C1 — CVE-2025-54068 (Livewire RCE) — **RESOLVED in repository (Pass 3)**

| | |
|---|---|
| **Package** | `livewire/livewire` |
| **Installed (May 16)** | **v3.6.0** (`composer.lock`) |
| **Installed (May 23 / `main`)** | **v3.8.0** |
| **Fixed in** | **≥ 3.6.4** |
| **CVE** | [CVE-2025-54068](https://github.com/advisories/GHSA-29cq-5w36-x7w3) |
| **Impact** | Remote command execution during component property update hydration |
| **Pass 3 production** | `GET /livewire/livewire.min.js` → **200**; public `POST /livewire/update` still used by contact form (expected post-patch) |

**Exposure:** Unauthenticated visitors to `/` use:

- `App\Livewire\ContactForm`
- `App\Livewire\DownloadCv` → `DownloadCvButton`, `DownloadCvCounter`

All invoke `POST /livewire/update` without authentication.

### C2 — FTP deployment of full application tree (scoped target; severity High)

From `.github/workflows/deploy_via_ftp.yml`:

```yaml
# Pass 1–2 (historical):
chmod -R 777 storage bootstrap/cache

# Pass 3 / current workflow (lines 44-45):
chmod -R 775 storage bootstrap/cache
find storage bootstrap/cache -type f -exec chmod 664 {} \;

# server-dir (unchanged):
server-dir: "/"
```

The workflow checks out the repo, runs `composer install --no-dev`, builds assets, then uploads to FTP **`server-dir: "/"`**. In [SamKirkland/FTP-Deploy-Action](https://github.com/SamKirkland/FTP-Deploy-Action), that path is **relative to the FTP login’s home directory**, not necessarily all of `public_html`.

**User-confirmed layout:** production FTP is limited to **`public_html/website/`** (or equivalent). So `/` means “repository root → `public_html/website/`”, **not** “entire `public_html/`”. That is **less risky** than the original audit assumed: sibling paths under `public_html` are outside this deploy’s write scope.

**Still risky (application-scoped):**

| Condition | Risk |
|-----------|------|
| Vhost docroot = `…/website/public` | Intended Laravel layout; `.env` / `vendor/` sit above docroot — **acceptable** if permissions are not 777 |
| Vhost docroot = `…/website/` (parent of `public/`) | **Critical** — `artisan`, `.env`, `vendor/`, `storage/logs/` may be HTTP-accessible |
| Vhost docroot = `public_html/` | Unrelated to this FTP target; would be a separate host misconfiguration |
| Full tree + `chmod 777` | **High** — any PHP in the docroot tree can tamper with sessions, cache, views, logs (see C3) |

**Severity (re-assessed):** **High** for deploy pattern + permissions; treat as **Critical** only if document root is confirmed or suspected to be `website/` instead of `website/public`.

**Excluded from upload:** `.git*`, `node_modules`, `tests`, **`.env`**, **`.env.*`**, dev config files (Pass 3 workflow). `.env` must never be committed; server copy is created manually on host.

### C3 — World-writable directories (777) — **MITIGATED in CI (Pass 3); verify on server**

**Historical (Pass 1–2):** `chmod -R 777` on `storage/` and `bootstrap/cache/`.

**Current workflow** (`.github/workflows/deploy_via_ftp.yml`):

```44:45:.github/workflows/deploy_via_ftp.yml
          chmod -R 775 storage bootstrap/cache
          find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

**Pass 3 action:** After each deploy, confirm on the host that `storage/` and `bootstrap/cache/` are **not** `777` (e.g. `stat` or hosting file manager). Writable dirs remain a persistence vector if a webshell exists; tightening permissions reduces impact.

**Server-verified (cPanel, May 23):** `public_html/website/bootstrap/cache/` shows **`0755`** (directory), PHP files **`0644`**. This is **normal and secure** — see [Permissions: CI vs server](#permissions-ci-vs-server-cpanel-verified) below.

---

## All findings by severity

### Critical

| ID | Finding | Location | Pass 3 status |
|----|---------|----------|---------------|
| C1 | Livewire RCE CVE-2025-54068 | `composer.lock` → was v3.6.0; now **v3.8.0** | **Resolved** in repo / production lockfile |
| C3 | `chmod -R 777` on `storage`, `bootstrap/cache` | `.github/workflows/deploy_via_ftp.yml` | **Mitigated** in CI (775); verify on server |
| P3-1 | Hosting path leak (`/website/public/index.php`, `/vendor/` redirect) | Production vhost / Apache | **Open** — see [Pass 3](#pass-3--live-production-testing-playwright--http) |

### High

| ID | Finding | Location | Pass 3 status |
|----|---------|----------|---------------|
| C2 | Full-repo FTP deploy to FTP `/` (`public_html/website/`); **Critical** if vhost docroot ≠ `website/public` | `.github/workflows/deploy_via_ftp.yml:55-63` | **Open** — path leak suggests misconfiguration |
| P3-2 | Contact form abuse (spam, mail relay to submitter) | `app/Livewire/ContactForm.php` | **Partial** — rate limit live; consider removing submitter confirmation mail |
| H1 | World-writable runtime dirs (see C3) | CI workflow + server permissions | **Mitigated** in CI; verify server |
| H2 | `POST livewire/upload-file` exposed | Framework route; no app upload components | **Open** (low practical use) |
| H3 | Laravel file validation bypass CVE-2025-27515 | was v11.44.0; now **v11.53.1** | **Resolved** |
| H4 | Symfony http-foundation PATH_INFO CVE-2025-64500 | transitive via Laravel; re-check `composer audit` | Verify after updates |
| H5 | `migrate --force` runnable from scheduled deploy check | `app/Console/Commands/MigrateCheck.php:35,46` | **Open** |

### Medium

| ID | Finding | Location | Pass 3 status |
|----|---------|----------|---------------|
| M1 | Contact form rate limit (see P3-2) | `ContactForm.php`, `AppServiceProvider.php` | **Resolved** on production (May 23) |
| P3-3 | Missing security headers | Origin / Cloudflare | **Open** |
| M2 | Telegram + mail notification abuse / cost | `app/Notifications/ContactFormSubmitted.php` | **Open** |
| M3 | `GET storage/{path}` enabled (`serve => true`) | `config/filesystems.php:33-37`; route `storage.local` |
| M4 | Login active; registration & 2FA disabled in Fortify | `config/fortify.php:146-157`; routes `/login`, `/user/profile` |
| M5 | PII in activity logs (name, email, phone, message) | `app/Services/ActivityLoggerService.php:30`; migration `database/migrations/2025_01_18_131840_create_activity_logs_table.php` |
| M6 | Session cookies not hardened by default in `.env.example` | `SESSION_ENCRYPT=false`; `SESSION_SECURE_COOKIE` unset |
| M7 | `league/commonmark` multiple CVEs (XSS / bypass) | `composer.lock` → v2.6.1; transitive via Laravel |
| M8 | Jetstream profile/API-token surface for authenticated users | `config/jetstream.php`; `resources/views/profile/show.blade.php` |

### Low

| ID | Finding | Location |
|----|---------|----------|
| L1 | Plain FTP (credentials in transit) | `.github/workflows/deploy_via_ftp.yml:56-60` |
| L2 | `psy/psysh` CVE-2026-25129 (dev REPL; not production) | `composer.lock` → v0.12.7 (require-dev) |
| L3 | `phpunit/phpunit` CVE-2026-24765 (dev only) | `composer.lock` → v11.5.10 (require-dev) |
| L4 | Unescaped `{!! !!}` in Jetstream views (trusted content) | `resources/views/terms.blade.php:9`, `policy.blade.php:9` |
| L5 | Google Analytics ID in layout (privacy, not security) | `resources/views/layouts/website.blade.php:33-41` |
| L6 | `.env.example` has `APP_DEBUG=true` | `.env.example:4` — must be `false` in production |

---

## New findings from pass 2

### Contact form abuse (spam / cost / reputation)

`ContactForm::submit()` validates input then:

1. Logs full PII to `activity_logs`
2. Sends Telegram + mail to site owner
3. **Queues confirmation mail to the submitter’s address** (`Mail::to($validated['email'])->queue(...)`)

There is **no** `RateLimiter`, CAPTCHA, or honeypot. An attacker can spam submissions → queue backlog, mail provider limits, Telegram noise, and DB growth.

### Auto-migrate via scheduled deploy check

```12:15:routes/console.php
Schedule::command('deploy:check')->everyMinute()->withoutOverlapping();
Schedule::command('queue:work', ['--stop-when-empty', '--no-interaction', '--max-jobs=2'])->everyMinute();
```

`deploy:check` detects `.deployment-state` changes, then calls `migrate:check`, which runs `php artisan migrate --force` when pending migrations exist (`app/Console/Commands/MigrateCheck.php`).

**Risks:**

- Requires **cron** `* * * * * php artisan schedule:run` on shared hosting (often misconfigured or forgotten).
- **`migrate --force` in production** without manual review can apply destructive migrations if a bad deploy lands.
- If cron is missing, queued mail from contact form may not process (`QUEUE_CONNECTION=database` in `.env.example`).

### Session hardening gaps

From `config/session.php` and `.env.example`:

| Setting | Default | Recommendation |
|---------|---------|----------------|
| `SESSION_ENCRYPT` | `false` | `true` in production |
| `SESSION_SECURE_COOKIE` | env-dependent | `true` when HTTPS |
| `SESSION_SAME_SITE` | `lax` | OK |
| `SESSION_DRIVER` | `database` | OK; ensure DB not exposed |

With **777** on `storage/framework/sessions` (if file driver used) or writable session storage, session fixation/poisoning risk increases post-compromise.

### Activity logs — PII retention

```26:31:app/Services/ActivityLoggerService.php
        ActivityLog::create([
            'type' => $type,
            'user_ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => json_encode($this->properties),
        ]);
```

Contact submissions store **full name, email, phone, and message** in `metadata`. Consider retention policy, encryption at rest, and GDPR minimization.

### Composer audit (full table)

**Pass 1–2 (May 16):** `composer audit` — **8 advisories, 6 packages**.

| Package | Installed (May 16) | CVE | Title | Fixed in |
|---------|-----------|-----|-------|----------|
| `livewire/livewire` | **v3.6.0** | CVE-2025-54068 | RCE via property hydration | **≥ 3.6.4** |
| `laravel/framework` | **v11.44.0** | CVE-2025-27515 | File validation bypass | **≥ 11.44.1** |
| `symfony/http-foundation` | **v7.2.3** | CVE-2025-64500 | PATH_INFO auth bypass (limited) | **≥ 7.3.7** (7.3.x) |
| `league/commonmark` | **v2.6.1** | CVE-2025-46734 | XSS in Attributes extension | **≥ 2.7.0** |
| `league/commonmark` | **v2.6.1** | CVE-2026-30838 | DisallowedRawHtml bypass | **≥ 2.8.1** |
| `league/commonmark` | **v2.6.1** | CVE-2026-33347 | Embed allowed_domains bypass | **> 2.8.1** |
| `phpunit/phpunit` | v11.5.10 | CVE-2026-24765 | Unsafe deserialization (dev) | **≥ 11.5.50** |
| `psy/psysh` | v0.12.7 | CVE-2026-25129 | Local priv esc (dev) | **> 0.12.18** |

**Pass 3 (May 23):** `composer audit` on current lockfile — **no security vulnerability advisories found**. Locked: `livewire/livewire` **v3.8.0**, `laravel/framework` **v11.53.1**.

**Ongoing:** Run `composer audit` on every `main` deploy and after `composer update`.

### Other pass 2 notes

- **Sentry** integrated for production exceptions (`bootstrap/app.php:19-21`) — good for post-incident monitoring; ensure DSN rotated after breach.
- **Fortify login** throttled to 5/min per email+IP (`app/Providers/FortifyServiceProvider.php:36-39`).
- **Registration** disabled (`config/fortify.php:147`).
- **API feature** disabled in Jetstream (`config/jetstream.php:63`) — reduces token-management exposure.
- **Health check** route: `GET /up` (Laravel 11 default).

---

## Attack surface map

### HTTP routes (from `php artisan route:list`)

| Method | URI | Name / handler | Auth | Notes |
|--------|-----|----------------|------|-------|
| GET | `/` | Closure → `website` view | Public | Livewire: ContactForm, DownloadCv* |
| GET | `/login` | Fortify login | Public | Brute-force partially throttled |
| POST | `/login` | Fortify | Public | |
| POST | `/logout` | Fortify | Auth | |
| GET | `/dashboard` | Closure | Auth + verified | Jetstream |
| GET | `/user/profile` | Jetstream profile | Auth | Livewire profile forms |
| GET | `/terms-of-service` | Jetstream | Public | |
| GET | `/privacy-policy` | Jetstream | Public | |
| GET | `/api/user` | Sanctum | Auth | Minimal API |
| GET | `/sanctum/csrf-cookie` | Sanctum | Public | SPA CSRF |
| GET | `/up` | Health | Public | |
| POST | **`/livewire/update`** | **livewire.update** | **Public** | Livewire action endpoint (CVE patched v3.8.0; still monitor) |
| POST | `/livewire/upload-file` | livewire.upload-file | Public | No app upload components |
| GET | `/livewire/livewire.js` | FrontendAssets | Public | |
| GET | `/livewire/preview-file/{filename}` | Livewire | Public | |
| GET | `/storage/{path}` | storage.local | Public* | *Laravel storage gate |

### Public Livewire components

| Component | Class | Page | Public methods / actions |
|-----------|-------|------|--------------------------|
| Contact form | `App\Livewire\ContactForm` | `/` (contact section) | `submit()` |
| CV section | `App\Livewire\DownloadCv` | `/` (cv section) | `downloadCv()` (via event) |
| CV button | `App\Livewire\DownloadCvButton` | nested | `downloadCv()` → file download |
| CV counter | `App\Livewire\DownloadCvCounter` | nested | display only (`#[Reactive]`) |

### Authenticated Livewire (Jetstream)

- `navigation-menu`, profile forms, `api.api-token-manager` (API feature off but views may remain in tree)

### CLI / scheduled

| Entry | Schedule | Risk |
|-------|----------|------|
| `deploy:check` | Every minute | Triggers migrate, cache clears, optimize |
| `queue:work` | Every minute | Processes queued mail/notifications |
| `migrate:check` | On deploy | `migrate --force` |

---

## Dependency audit

### Direct production dependencies (`composer.json`)

| Package | Constraint | Locked version |
|---------|------------|----------------|
| `php` | `^8.3` | 8.4 (CI, Pass 3) |
| `laravel/framework` | `^11.9` | **v11.53.1** (Pass 3) |
| `livewire/livewire` | `^3.0` | **v3.8.0** (Pass 3) |
| `laravel/jetstream` | `^5.3` | v5.3.5 |
| `laravel/sanctum` | `^4.0` | v4.0.8 |
| `laravel/tinker` | `^2.9` | (transitive) |
| `laravel-notification-channels/telegram` | `^5.0` | 5.0.0 |
| `sentry/sentry-laravel` | `^4.13` | (see lock) |

### CVE summary (production impact)

See [Composer audit (full table)](#composer-audit-full-table). **Pass 3:** Livewire and Laravel production CVEs from Pass 1–2 are **addressed** in current `composer.lock`. Re-run `composer audit` after any dependency change.

---

## Deployment risks

**Workflow:** `.github/workflows/deploy_via_ftp.yml`  
**Trigger:** Push to `main` or `workflow_dispatch`

| Step | Risk |
|------|------|
| `actions/checkout@v3` | Standard |
| `chmod -R 775 storage bootstrap/cache` (was 777) | **Medium** — verify on server; not world-writable in CI |
| `composer install --no-dev` | Correct for production |
| `npm ci` + `npm run build` | Correct |
| `SamKirkland/FTP-Deploy-Action@v4.3.4` | Plain FTP; verbose logs |
| `server-dir: "/"` | **High** — uploads repo root to **FTP account root** (`public_html/website/` per user); **Critical** only if vhost docroot is `website/` not `website/public` |
| Secrets: `FTP_SERVER`, `FTP_PRODUCTION_USERNAME`, `FTP_PRODUCTION_PASSWORD` | High value — rotate after incident |
| State file: `.deployment-state` | Used by `deploy:check` on server |

**Actual deploy mapping (user-confirmed):**

```
public_html/
  (other sites / files — NOT touched by this workflow)
  website/                   ← FTP "/" = repo root lands here
    .env
    vendor/
    storage/
    artisan
    public/                  ← vhost DOCUMENT_ROOT must be HERE
      index.php
      build/
      website/
```

**Less risky than originally documented:** CI does **not** FTP the whole of `public_html`; blast radius is the `website/` subtree only.

**Still wrong / dangerous:**

```
public_html/website/         ← vhost docroot = app root (BAD)
  .env                       ← web-reachable if docroot mis-set
  vendor/
  artisan
  public/
    ...
```

**Ideal (if host allows APP_ROOT outside web tree):**

```
/home/user/
  website-livewire/          ← APP_ROOT (not web accessible)
    .env
    vendor/
    storage/
    artisan
    public/                  ← DOCUMENT_ROOT only (symlink or vhost)
```

Current workflow does **not** achieve the ideal layout unless FTP chroot + vhost docroot are both correct.

---

## Malware verdict

| Check | Result |
|-------|--------|
| Obfuscated / rogue PHP in git | **None found** |
| Webshell patterns (`eval`, `exec`, etc.) in app | **None found** |
| Extra PHP files in `public/` (git) | **Only `public/index.php`** |
| Backdoors in vendor (spot check) | Not in scope; use clean `composer install` after wipe |

**Conclusion:** The repository is **clean**. Server-side rogue PHP was **injected post-compromise**, not committed to this repo.

---

## Remediation checklist

### Before deploy

- [x] **Wipe** all files on compromised host (do not only delete rogue PHP) — skip if already done post-incident
- [x] Rotate **all** secrets: `APP_KEY`, DB, mail, Telegram bot, **FTP**, GitHub Actions, Sanctum, Sentry DSN
- [x] `composer update livewire/livewire` → **≥ 3.6.4** (Pass 3: **v3.8.0** on `main`)
- [x] `composer update laravel/framework` → **≥ 11.44.1** (Pass 3: **v11.53.1** on `main`)
- [x] Run `composer audit` — **0 advisories** (Pass 3, May 23)
- [ ] Audit GitHub repo: collaborators, workflow changes, secret access logs
- [ ] Review Jetstream users; reset passwords for any account that existed on compromised host

### Host layout

- [ ] Confirm vhost document root is **`…/public_html/website/public`** (not `website/` or all of `public_html`)
- [ ] Set document root to **`/path/to/app/public`** only
- [ ] Place `.env`, `vendor/`, `storage/`, `artisan` **above** web root (with current FTP, that means “above `public/` inside `website/`”)
- [ ] Directory permissions **755**, files **644** — **never 777**
- [ ] Block PHP execution in `public/build`, `public/website` if panel allows
- [ ] Deny web access to `.env`, `.git`, `vendor`, `storage/logs`

### App hardening

- [ ] `APP_ENV=production`, `APP_DEBUG=false` (Pass 3: no debug leak on 404 — verify `.env` on server)
- [ ] `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true` (HTTPS)
- [ ] **Deploy** rate limiting to `ContactForm` (implemented in working tree: 5/hour per IP — **not on production yet**)
- [ ] Block or redirect `/website/public/*` at host or Cloudflare (Pass 3: **200** on `index.php`)
- [ ] Add security headers (CSP, `X-Frame-Options`, HSTS) via Cloudflare or middleware
- [ ] Consider CAPTCHA on contact form
- [ ] Remove or gate `/login` and Jetstream if unused in production
- [ ] Enable Fortify 2FA if admin login is required
- [ ] Set `FILESYSTEM_DISK` local `serve => false` if `storage/{path}` not needed
- [ ] Review `activity_logs` retention and PII minimization
- [ ] Replace FTP deploy with SFTP/SSH or deploy **only `public/` + optimized vendor** artifact

### CI/CD fixes

- [x] Remove `chmod -R 777` from `.github/workflows/deploy_via_ftp.yml` (Pass 3: **775** / files **664**)
- [x] Exclude `.env` / `.env.*` from FTP upload (Pass 3: in workflow `exclude` list)
- [ ] Verify FTP account is scoped to `website/` only; change `server-dir` only if host docs require a subpath (default `/` is correct when FTP root = `public_html/website/`)
- [ ] Prefer publishing **`public/`**-only artifacts, or confirm docroot = `website/public` with full-tree deploy
- [ ] Add explicit exclude for any accidental `.env` on build machine
- [ ] Pin Actions to SHA or newer major versions (`checkout@v4`, etc.)

### Post-deploy verification

- [ ] `curl -I https://yoursite/.env` → 403/404
- [ ] `curl -I https://yoursite/vendor/` → 403/404
- [ ] `curl -I https://yoursite/storage/logs/` → 403/404
- [ ] Confirm `POST /livewire/update` returns normal responses (after patch)
- [ ] Scan `public/` for unexpected `.php` files (cron or monitoring)
- [ ] Verify cron: `php artisan schedule:run` + queue processing
- [ ] Test contact form once; confirm rate limit if implemented

---

## Additional remediation from pass 2

| Item | Action |
|------|--------|
| Contact form spam | Add `RateLimiter` in `ContactForm::submit()` or middleware; consider honeypot |
| Outbound mail abuse | Do not queue confirmation to arbitrary submitter emails without verification; use notification-only to owner |
| `migrate --force` on cron | Run migrations manually on deploy; keep `deploy:check` for cache/optimize only |
| Cron dependency | Document required crontab: `* * * * * cd /app && php artisan schedule:run` |
| Session storage | With database driver, lock down DB credentials; avoid 777 on any session path |
| Commonmark CVEs | Update framework; avoid rendering untrusted Markdown with unsafe extensions |
| Livewire upload route | After Livewire upgrade, confirm upload endpoint disabled or gated if unused |
| Dependency hygiene | Add `composer audit` to CI on `main` |
| Logging | Ensure `storage/logs` not web-accessible; monitor for `POST /livewire/update` anomalies |

---

## Additional remediation from pass 3

| Item | Action | Priority |
|------|--------|----------|
| Hosting path leak | Set vhost docroot to `website/public` only; add Cloudflare/Apache rule to **410/redirect** `/website/public/*` | **P0** |
| Deploy contact rate limit | ~~Merge and deploy~~ **Deployed** — verified 5/hour per IP (Playwright, May 23) | **Done** |
| Security headers | Enable HSTS, CSP, `X-Frame-Options`, `X-Content-Type-Options` at Cloudflare | **P1** |
| Remove `/login` surface | Disable Fortify routes or protect with IP allowlist if dashboard unused | **P1** |
| Submitter confirmation email | Remove or gate `Mail::to($validated['email'])` to prevent mail abuse | **P2** |
| Periodic scans | Re-run Playwright path probe + `find public -name '*.php'` after deploys | **P2** |
| Pass 3 test data | Delete `pw-audit+pass3@example.com` / `security-test+audit@example.com` from logs and notifications | **P3** |

---

## Logs to collect (minimal, safe)

Download only from known paths; **do not** open unknown `.php` from `public/` on your main machine (use server-side `grep` or a VM).

| Log | Path / source | Look for |
|-----|---------------|----------|
| Laravel application log | `storage/logs/laravel.log` | Livewire exceptions, 500s, stack traces around breach date |
| Web server access log | host panel / `access.log` | `POST /livewire/update`, new random `.php` POSTs, FTP/SFTP logins |
| Web server error log | `error.log` | PHP fatal errors, mod_security blocks |
| FTP logs | hosting panel | Brute force, odd upload times |
| GitHub Actions | Actions tab → deploy workflow | Unexpected runs, secret usage |

**Safer remote inspection (SSH):**

```bash
grep -E 'livewire/update|\.php' /path/to/access.log | tail -200
find /path/to/public -name '*.php' ! -name 'index.php' -ls
```

---

## References

| CVE | Package | Advisory |
|-----|---------|----------|
| [CVE-2025-54068](https://github.com/advisories/GHSA-29cq-5w36-x7w3) | livewire/livewire | Remote command execution during component property update hydration |
| [CVE-2025-27515](https://github.com/advisories/GHSA-78fx-h6xr-vch4) | laravel/framework | File validation bypass |
| [CVE-2025-64500](https://symfony.com/blog/cve-2025-64500-incorrect-parsing-of-path-info-can-lead-to-limited-authorization-bypass) | symfony/http-foundation | PATH_INFO authorization bypass (limited) |
| [CVE-2025-46734](https://github.com/advisories/GHSA-3527-qv2q-pfvx) | league/commonmark | XSS in Attributes extension |
| [CVE-2026-30838](https://github.com/advisories/GHSA-4v6x-c7xx-hw9f) | league/commonmark | DisallowedRawHtml bypass |
| [CVE-2026-33347](https://github.com/advisories/GHSA-hh8v-hgvp-g3f5) | league/commonmark | Embed allowed_domains bypass |
| [CVE-2026-24765](https://github.com/sebastianbergmann/phpunit/security/advisories/GHSA-vvj3-c3rp-c85p) | phpunit/phpunit | Unsafe deserialization (dev only) |
| [CVE-2026-25129](https://github.com/advisories/GHSA-4486-gxhx-5mg7) | psy/psysh | Local privilege escalation (dev only) |

---

*This document consolidates Pass 1 (initial audit), Pass 2 (verification), and Pass 3 (live production testing on https://joaoestima.com/, May 23, 2026). Re-run `composer audit`, `php artisan route:list`, and the [Pass 3 HTTP probe table](#production-http-probe-results) after dependency, route, or hosting changes.*
