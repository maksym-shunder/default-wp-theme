---
paths: ["**/*.php", "**/*.js", "**/*.json", "**/*.yml", "**/*.yaml", "**/*.css"]
---

# Secret Guard

Applies whenever you read, create, or edit one of the matched files.

## The check

Before you finish working on a file, scan the **whole file** — not only the lines you
changed — for credentials: API keys, tokens, passwords, private keys, connection strings
with inline credentials, and credential-shaped assignments.

It does not matter who wrote the line. A key a human pasted in last month counts exactly
the same as one you generated a minute ago.

## Never write one yourself

Never put a literal credential into theme source. When code you are writing needs a key,
follow the placement table in `.claude/skills/secret-guard/SKILL.md` →
"Where keys belong in this theme". Short version:

- Client-editable value (map key, analytics ID, social links) → ACF Global Settings.
- Server credential (SMTP, payment secret, private API) → constant in `wp-config.php`,
  which lives outside this repository.
- Deploy credential (FTP, SSH, Slack) → GitHub Secret, referenced as `${{ secrets.NAME }}`.
- Public value the frontend needs → `wp_localize_script()` in `inc/enqueue-scripts.php`.

Never a literal in `assets/js/*.js`, `inc/*.php`, `functions.php`, a block `render.php`,
or a default value inside `acf-json/*.json`.

## On a finding — stop

1. **Stop.** Do not finish the edit, do not fix it quietly, do not move on to the next task.
2. **Change nothing.** No edits, no moving the key, no deleting the line.
3. **Report it** and wait for the user's decision.
4. **Mask the value** — first 4 characters and the length, never the whole credential.
   The report ends up in chat history; do not copy a live secret into it.

Then read `.claude/skills/secret-guard/SKILL.md` for classification, the report format,
and how to handle each answer the user can give.

## Not every match is a leak

Three classes, three different answers. Do not warn about them the same way.

| Class | Example | Answer |
|---|---|---|
| Secret | SMTP password, `sk_live_`, private key, FTP password | Must come out of the code. Treat as compromised — rotate. |
| Publishable key | Google Maps key, `pk_live_`, reCAPTCHA site key, Sentry DSN | Not a leak — it ships to the browser by design. Ask whether it is domain-restricted. |
| False positive | `password` in a login form label, `${{ secrets.X }}` in a workflow | Say nothing at all. |

The repo-specific allowlist lives in the skill. Read it before reporting anything under
`templates/`, `woocommerce/`, or `.github/`.

## Never

- **Never run git** in this project — not `commit` or `push`, and not read-only commands
  either. If you need to know whether something was already committed, ask the user.
- **Never "hide" a key** by renaming the variable, splitting the string, or base64-ing it.
  That is not a fix; it only makes the leak harder to find later.
