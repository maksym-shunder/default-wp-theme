---
name: secret-guard
description: Checks code for leaked credentials — API keys, tokens, passwords, private keys — and tells the user what to do with each finding. Use when the secret-guard rule flags something while you are working in a file, and whenever the user asks to check for leaked keys before code goes anywhere. Triggers - "check for leaks", "any keys in the code", "security check", "safe to push?", "проверь на утечки", "нет ли ключей в коде", "проверь секреты", "безопасно ли отправлять", "проверь перед коммитом", "перевір на витік", "чи немає ключів у коді". Classifies every finding as a real secret, a publishable key that only needs domain restrictions, or a false positive, then reports file and line with a masked value and a concrete fix - where that kind of key belongs in this WordPress theme (ACF options page, wp-config.php constant, GitHub Secret, wp_localize_script). Reports and stops; never removes a key on its own, never runs git.
allowed-tools: Read, Grep, Glob, Edit
disallowed-tools: Bash
---

# Secret Guard

Find credentials sitting in theme source, classify them, and hand the user a decision —
without changing anything first.

**This skill never runs commands.** `Bash` is removed while it is active, so it cannot
touch git, cannot run a scanner binary, and cannot inspect history. Everything here is
done with `Read`, `Grep` and `Glob`. `Edit` is available for exactly one purpose, in
Step 4c, after explicit confirmation.

## Two ways this starts

- **From the rule** (`.claude/rules/secret-guard.md`) — you were editing or reading a file
  and something credential-shaped is in it. Scope: that file.
- **From the user** — "проверь на утечки", "нет ли ключей". Scope: whatever they named,
  or the whole theme if they did not narrow it.

Both run the same four steps.

## Step 1 — Scan

Grep the scope for the patterns below. They are ordered by signal: the first block almost
never produces a false positive, the last block produces most of them.

### High-confidence — a hit here is a finding

| Rule | Pattern |
|---|---|
| private-key | `-----BEGIN (RSA \|EC \|DSA \|OPENSSH \|PGP )?PRIVATE KEY` |
| aws-access-key | `AKIA[0-9A-Z]{16}` |
| aws-secret | `aws_secret_access_key\s*[=:]\s*['"]?[A-Za-z0-9/+=]{40}` |
| stripe-secret | `\b(sk\|rk)_(live\|test)_[0-9a-zA-Z]{16,}` |
| github-token | `\bgh[pousr]_[0-9A-Za-z]{36}\b` |
| slack-token | `\bxox[baprs]-[0-9A-Za-z-]{10,}` |
| slack-webhook | `hooks\.slack\.com/services/[A-Z0-9/]{20,}` |
| sendgrid-key | `\bSG\.[A-Za-z0-9_-]{20,}\.[A-Za-z0-9_-]{20,}` |
| mailgun-key | `\bkey-[0-9a-f]{32}\b` |
| jwt | `\beyJ[A-Za-z0-9_-]{8,}\.eyJ[A-Za-z0-9_-]{8,}\.[A-Za-z0-9_-]+` |
| url-with-credentials | `[a-z][a-z0-9+.-]*://[^/\s:@]+:[^/\s:@]{3,}@` |

### Medium — a hit needs a look at the line

| Rule | Pattern |
|---|---|
| php-define-credential | `define\(\s*['"][A-Z0-9_]*(KEY\|SECRET\|PASS\|TOKEN\|SALT)[A-Z0-9_]*['"]\s*,\s*['"][^'"]{6,}['"]` |
| generic-assignment | `(?i)(api[_-]?key\|apikey\|secret\|token\|passwd\|password\|client[_-]?secret\|access[_-]?key)\s*[=:]\s*['"][^'"\s]{8,}['"]` |
| auth-header | `(?i)authorization\s*[:=]\s*['"]?(bearer\|basic)\s+[A-Za-z0-9._\-+/=]{16,}` |
| google-api-key | `\bAIza[0-9A-Za-z_-]{35}\b` |

`google-api-key` sits here on purpose — it is almost always a **publishable** key
(Step 2), not a secret.

Note what `generic-assignment` requires: the keyword, then `=` or `:`, then a **quoted
value of 8+ characters**. Form markup does not satisfy it — in `name="password"` the
keyword is inside the quotes, not before the separator. So login, signup and WooCommerce
templates produce no hits from these rules, and do not need to be excluded from the scan.

### Files that should not be in the theme at all

Flag by name, whatever is inside them:

`*.sql` · `*.sql.gz` · `*.dump` · `.env` and `.env.*` · `wp-config.php` ·
`wp-config-*.php` · `*.pem` · `*.key` · `*.ppk` · `id_rsa*` · `*.p12` · `*.pfx` ·
`*.bak` · `*.zip` · `*.tar.gz` · `.ftp-deploy-sync-state.json` · `debug.log` · `*.log` ·
`local-xdebuginfo.php`

A database dump or a `wp-config.php` copied into the theme "for convenience" is a bigger
leak than any single key — it carries DB credentials, salts, and often customer data.

## Step 2 — Classify

This is the step that decides what you say. Every finding is one of three things.

### A. Secret — must come out

A credential that grants access. SMTP password, DB password, `sk_live_` / `rk_live_`,
private key, FTP or SSH password, private API token, WordPress salts.

Answer: it has to leave the source. And **rotation is not optional** — see Step 4a.

### B. Publishable key — not a leak

A key that is handed to the browser by design. It is *supposed* to be visible; hiding it
is impossible and pointless.

- Google Maps / Google JS API key (`AIza…`)
- Stripe publishable key (`pk_live_`, `pk_test_`)
- reCAPTCHA **site** key (starts `6L…`) — the *secret* key is class A
- Sentry DSN
- Google Analytics / GTM ids (`G-…`, `UA-…`, `GTM-…`)
- Algolia search-only key
- Firebase web config (its `apiKey` is an `AIza…` key)

The real question here is **not** "why is it in the code" but **"is it restricted?"**
An unrestricted Maps key gets scraped and burns the project's quota; a Stripe
publishable key is harmless. So ask:

> Ключ публикуемый — он и так уходит в браузер, прятать его незачем.
> Проверьте только, стоит ли ограничение по домену/referrer в консоли провайдера.
> Если стоит — оставляем и помечаем строку, чтобы проверка больше не срабатывала.

Then offer the allowlist comment from Step 4c.

### C. False positive — say nothing

This list is deliberately short. **Excluding a path is not free** — every rule stops
applying there, including the ones that would catch a real key. A path belongs here only
if credential-shaped text is *expected* in it **and** a real credential could not
plausibly live there.

| Where | What matches | Why it is fine |
|---|---|---|
| `.github/*.yml` | `${{ secrets.NAME }}` | A reference to a GitHub Secret, not the value |
| `.claude/skills/**`, `.claude/rules/**`, `.cursor/rules/**` | every pattern on this page | The rule definitions themselves |
| `assets/js/swiper.min.js` | long base64-ish runs | Minified vendor bundle, never hand-edited |

Paths that look like they belong here and **do not**: `templates/template-login.php` and
its siblings, `woocommerce/**`, `inc/woocommerce.php`, `README.md`, `CLAUDE.md`,
`AGENTS.md`. They contain the *words* `password` and `secret`, but not in a shape these
rules match (see the note in Step 1), so excluding them would suppress nothing — while
blinding the check inside the files that handle authentication, and inside a README,
which is a plausible place for someone to paste a live key into an example config.

One exception inside `.github/*.yml`: if a literal value appears where a
`${{ secrets.* }}` reference belongs — a webhook URL, a password in plain text — that is
class A, and an urgent one, because workflow files are the first thing an attacker reads.

## Step 3 — Report, and stop

Change nothing. Print findings grouped by class, secrets first. Mask every value: first
four characters, then the length.

```
SECRET GUARD — 2 finding(s). Nothing has been changed.

[1] SECRET · inc/mailer.php:12 · php-define-credential
    define('SMTP_PASS', 'hunt…' + 18 chars)
    Даёт доступ к почтовому ящику проекта.
    Куда: константа в wp-config.php (вне репозитория),
          читать через defined('SMTP_PASS') ? SMTP_PASS : ''
    Ротация: обязательна, если этот файл уже уходил в коммит.

[2] PUBLISHABLE · assets/js/main.js:4 · google-api-key
    const MAP_KEY = 'AIza…' + 39 chars
    Это не утечка — ключ и так уходит в браузер.
    Проверьте: ограничение по HTTP referrer в Google Cloud Console.
    Если ограничение стоит — можно оставить и пометить строку.

Ничего не изменено. Скажите по каждому пункту, что делаем.
```

Then wait. Do not proceed to Step 4 on your own initiative.

## Step 4 — After the user decides

### 4a. "Убрать" — move it out

1. **Rotation comes first.** If the file was ever committed with this value, the key is
   compromised — deleting the line does not un-leak it. You cannot check history (no
   git), so ask plainly: *«Этот файл уже уходил в коммит с этим значением?»* If the
   answer is yes, or unknown, say: rotate the key first, then clean the code.
2. Move the value to its proper home — the table below.
3. Replace the literal with the read pattern for that home.
4. Re-scan the file.

### 4b. "Это публикуемый ключ"

Confirm the restriction is actually configured, then offer 4c. If it is not restricted,
say so — an unrestricted key is a real problem even though it is not a secret.

### 4c. "Оставляем" — the allowlist comment

Only after an explicit yes. Propose the line, show it, and insert it **only** when the
user confirms. This is the single edit this skill is allowed to make.

Use the scanner-readable form, so this same decision also silences gitleaks in CI later:

```php
// gitleaks:allow — публичный ключ Maps, ограничен по referrer в Google Console. Олег, 2026-08-28
```

```js
const MAP_KEY = 'AIza…'; // gitleaks:allow — публичный ключ Maps, ограничен по referrer. Олег, 2026-08-28
```

Three parts, all required: the `gitleaks:allow` marker, the reason, and **who decided and
when**. Without a name the exemption is anonymous and nobody can review it later.

Never write this comment next to a class A secret. If the user insists on keeping a real
credential in the code, say once, plainly, that the comment documents the risk but does
not reduce it — then leave the decision with them and record it as they asked.

## Where keys belong in this theme

| Kind of value | Home | How code reads it |
|---|---|---|
| Client-editable (map key, analytics id, socials) | ACF Global Settings → `acf-json/group_site_settings.json` | `get_field('map_api_key', 'option')` |
| Server credential (SMTP, payment secret, private API) | Constant in `wp-config.php` — outside this repository | `defined('SMTP_PASS') ? SMTP_PASS : ''` |
| Deploy credential (FTP, SSH, Slack webhook) | GitHub Secret | `${{ secrets.NAME }}` in `.github/*.yml`, never a literal |
| Public value the frontend needs | `wp_localize_script()` / `wp_add_inline_script()` in `inc/enqueue-scripts.php` | `window.themeData.mapKey` |
| Anything else | Not in the theme | — |

Never a literal in `assets/js/*.js`, `functions.php`, `inc/*.php`, a block `render.php`,
`block.json`, or a default value in `acf-json/*.json`.

**Minification is not protection.** `minify.js` runs on CI and rewrites `assets/js/` in
place, but a key inside it ships to production exactly as written and is readable in
DevTools in two clicks.

## What this does not catch

Say this out loud when someone treats the check as a guarantee.

- A key in a file nobody opened this session — the scan only sees what it is pointed at.
- Anything already in git history. Removing a line today does not remove yesterday's commit.
- A key committed by a human working without the agent.
- A credential inside a binary: an image, a screenshot, a PDF. `.github/Deploy-Instruction.pdf`
  is exactly the kind of file where FTP access ends up — worth one manual look.
- A custom internal token that matches none of the patterns.

The only deterministic barrier is a scanner that runs on every commit — `gitleaks` in a
pre-commit hook or in GitHub Actions. This skill reduces how often a key reaches a file;
it cannot promise one never reaches the remote.

## For CI

`references/gitleaks.toml` holds the same rules and the same allowlist in gitleaks
format. When this moves to GitHub Actions, that file is what gets copied — not this one.
Keep the two in sync: a pattern added here should be added there, and the allowlist in
both files must stay identical.
