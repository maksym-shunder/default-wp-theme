# AGENTS.md

Token-efficient project map for AI agents. Human prose lives in `CLAUDE.md` + `README.md` — read those only if this file lacks the answer.

## Stack
- WordPress custom theme, slug `default-wp-theme`. Runs inside Local by Flywheel.
- PHP (no build step locally), vanilla JS, native-nested CSS.
- Deps: ACF (required), WooCommerce (optional — see README "If WooCommerce is Not Used").
- No tests, no linter, no `package.json` in repo. `minify.js` runs only on CI.

## Bootstrap chain
`functions.php` → require in order:
1. `inc/theme.php` — menus, editor styles, SVG uploads, dequeue `wp-block-library*`, first-block helpers.
2. `inc/acf/acf.php` → `options-pages.php` + `registration.php`.
3. `inc/enqueue-scripts.php` — `theme_scripts` + editor-iframe assets.
4. `inc/maintenance-page.php` — creates `/maintenance` on theme activation, redirects non-admins when ACF `maintenance_mode` truthy.
5. `inc/helpers.php` — `theme_field()`.
6. `inc/woocommerce.php` — WC hooks (see WC section).

ACF Local JSON wired in `functions.php:3-10` → save/load `acf-json/` at theme root.

New top-level module → add `require_once` here.

## File map

| Path | Purpose |
|---|---|
| `functions.php` | Entry. Requires all `inc/*`. ACF JSON path filters. |
| `inc/theme.php` | Menus, `editor-styles` + `add_editor_style`, SVG mime, dequeue WP block CSS, `get_first_block_name_on_page()`, `get_images_from_first_block_on_page()` (filters first top-level `section/div/article/header/main/footer` for `<img fetchpriority="high">`). |
| `inc/enqueue-scripts.php` | `theme_scripts()` → frontend assets. `auto_enqueue_styles()` globs `assets/css/*.css` + one subdir level, skip exclude list. `enqueue_block_editor_assets` hook → editor iframe gets `fonts.css` + `global.css` + `editor.css`. |
| `inc/preloads.php` | `<link rel="preload">` for critical CSS, first-block CSS, first-block `fetchpriority="high"` images, fonts, JS, jQuery. Included from `header.php`. |
| `inc/maintenance-page.php` | Creates `/maintenance` page on activation; redirects non-`manage_options` users when ACF option `maintenance_mode` truthy. Skips `is_admin()` + `wp-login.php`. |
| `inc/helpers.php` | `theme_field(string $key, $default='', $post_id=false)` — `get_field()` wrapper with fallback. Use in render templates so blocks show something pre-content. |
| `inc/woocommerce.php` | See WC section. |
| `inc/acf/acf.php` | Guards `class_exists('ACF')`, requires options pages + block registration. |
| `inc/acf/options-pages.php` | Registers "Theme Settings" parent + 3 subpages: `global_settings`, `settings_header`, `settings_footer`. |
| `acf-json/group_{site_settings,header,footer}.json` | Field defs for the 3 options subpages, as ACF Local JSON (no PHP field files). |
| `inc/acf/registration.php` | `init` hook globs `template-parts/gutenberg-blocks/*/block.json` → `register_block_type()`. `block_categories_all` filter registers `digiway-blocks`. |
| `acf-json/` | ACF Local JSON sync target. Field group edits in WP admin write back here. |
| `header.php` / `footer.php` / `index.php` / `404.php` / `home.php` / `single.php` / `page-maintenance.php` | Standard WP. |
| `templates/template-{checkout,login,signup,reset-password}.php` | Page templates assigned via WP page editor. Slugs `/customer-login` + `/reset-password` are referenced by WC redirects. |
| `template-parts/components/{basket,product-card,video-controls}.php` + `header/cart-popup.php` | Partials. |
| `template-parts/popups/example-popup.php` | Popup partial. |
| `template-parts/gutenberg-blocks/<name>/` | One block per folder (see Block recipe). |
| `assets/css/global.css` | Base styles. Preloaded + editor iframe. |
| `assets/css/editor.css` | Editor-iframe-only overrides. |
| `assets/css/checkout.css` | Auto-enqueued. |
| `assets/css/swiper.css` | Excluded from auto-enqueue, hand-enqueued in `theme_scripts()`. |
| `assets/css/components/{header,footer,popup,cart-popup,product-box}.css` | `header`, `popup`, `cart-popup` preloaded + excluded from auto-enqueue. `footer`, `product-box` auto-enqueued. |
| `assets/js/main.js` | Always enqueued + preloaded. |
| `assets/js/swiper.min.js` | Hand-enqueued. |
| `assets/js/woocommerce.js` | jQuery dep. Handles `wp_ajax_woocommerce_update_cart_item`. |
| `woocommerce/` | WC template overrides — standard WC paths (`cart/`, `checkout/`, `coupon.php`). |
| `minify.js` | CI-only. Overwrites source in `assets/js/`, `assets/css/`, `template-parts/gutenberg-blocks/`. |
| `.github/workflows/deploy.yml` | On push to `main`: `node minify.js` → SFTP. Secrets: `SERVER` `USERNAME` `PASSWORD` `PORT` `THEME_PATH`. |
| `theme.json` | FSE config. |
| `style.css` | Theme header only. |

## Rules (apply to every agent — Claude Code, Codex, Cursor)

Claude Code loads these as skills/rules automatically. Codex and Cursor must follow the same content — read the linked files before the matching task.

- **Git & PRs** → `.claude/rules/git-operations.md`. Never auto-commit/push/force-push; commit only on explicit request. PR text: no AI-tool mentions, no change stats, no test-plan checklists.
- **Styles** → `.claude/rules/styles.md`. Native CSS nesting (but see CSS gotcha below); reuse components (`.primary_button`, `.primary_link`, `.container`); minimize `font-family`/`font-size`/`font-weight` when same as global; a color used once or twice → hardcode, used 3+ times → make a `:root` var in `global.css` and replace.
- **Block creation** → full procedure in `.claude/skills/create-gutenberg-block/SKILL.md`. Codex/Cursor: read that file and follow it step-for-step (it is plain Markdown, no tool needed). Summary in "Block recipe" below.

### Hard conventions (from the skill — do not violate)

- Block root element is always `<section class="<slug>">`. Forward `$block['anchor']` to `id`, merge `$block['className']`.
- **No root padding/margin** on the block — global vertical rhythm spaces blocks via `--section-space`. Do not add `padding: var(--section-space) 0` per block.
- **CSS: flat selectors only** (`.slug__el`). Native nesting `&__el` BEM-concat is NOT supported by browsers and silently drops rules — local dev serves raw CSS, postcss only runs on CI. `&` + space/combinator is fine.
- Buttons/links use the ACF `link` field type (returns `{url,title,target}`), never split `*_label` + `*_url`.
- Block JS must be wrapped in an IIFE (see `example-block/assets/script.js`) — scripts share global scope across blocks.
- Image/file fields: guard `if (!empty($x) && is_array($x))` before `$x['url']` or the editor preview crashes.
- Reuse the globally-enqueued Swiper (`assets/js/swiper.min.js` + `assets/css/swiper.css`) for sliders — no new dep.

## Block recipe

New block = new folder:
```
template-parts/gutenberg-blocks/<name>/
  block.json
  render.php
  assets/
    style.css   (optional, auto-loaded via "style": "file:./assets/style.css")
    script.js   (optional, via "viewScript": "file:./assets/script.js")
```

Plus `acf-json/group_<name>.json` (auto-generated when you edit fields in WP admin).

`block.json` shape (see `template-parts/gutenberg-blocks/example-block/block.json`):
```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 2,
  "name": "digiway/<name>",
  "category": "digiway-blocks",
  "supports": { "anchor": true, "jsx": true },
  "acf": {
    "mode": "preview",
    "blockVersion": 3,
    "autoInlineEditing": true,
    "renderTemplate": "render.php"
  },
  "style": "file:./assets/style.css",
  "viewScript": "file:./assets/script.js"
}
```

Render template gets `$block`, `$is_preview`, etc. Use `theme_field()` not `get_field()`.

**Do NOT reintroduce** (replaced by `block.json` flow): per-block `index.php` with `acf_register_block_type()`, per-block `fields.php` with `acf_add_local_field_group()`, per-block `enqueue_assets` callbacks.

**Do NOT bump** `apiVersion` to 3 without verifying ACF block render path tolerates v3 iframe model.

## CSS loading — three buckets

Decide bucket before adding/moving any CSS.

| Bucket | Mechanism | Files |
|---|---|---|
| Critical / preloaded | Manual `<link rel="preload">` in `inc/preloads.php`. Excluded from auto-enqueue in `inc/enqueue-scripts.php:8-14`. | `global.css`, `components/header.css`, `components/popup.css`, `components/cart-popup.css` |
| Auto-enqueued | `auto_enqueue_styles()` globs `assets/css/*.css` + `assets/css/**/*.css` (one level), `filemtime()` cache-bust. | everything not in exclude list |
| First-block | `inc/preloads.php:42-54` calls `get_first_block_name_on_page()`, preloads `template-parts/gutenberg-blocks/<name>/assets/style.css`. Per-block CSS itself auto-loaded by core via `block.json` `style:` key. | per-block |

**Rule:** add to exclude list in `auto_enqueue_styles()` ↔ add matching `<link rel="preload">` in `inc/preloads.php`. Keep both in sync.

`swiper.css` is excluded + hand-enqueued in `theme_scripts()` (not preloaded).

## Editor iframe parity

Block previews must match frontend.
- `inc/theme.php:17-23` → `add_theme_support('editor-styles')` + `add_editor_style([fonts.css, global.css, editor.css])`.
- `inc/enqueue-scripts.php:81-99` → `enqueue_block_editor_assets` re-enqueues the same trio with `filemtime()` versioning.
- Per-block CSS auto-loads in editor via `block.json` `style:` key.

## WooCommerce hooks (`inc/woocommerce.php`)

| Line | Behavior |
|---|---|
| 4-7 | ACF option `disable_payments` → `woocommerce_is_purchasable` + variation purchasable → false. |
| 10-11 | Moves payment block: removed from `woocommerce_checkout_order_review`, added to `woocommerce_checkout_after_customer_details` (priority 25). |
| 13-18 | `add_theme_support('woocommerce')`. |
| 22-30 | Enables Gutenberg for `product` post type. |
| 34-41 | Adds `show_in_rest` to `product_cat` + `product_tag`. |
| 45-58 | `woocommerce_add_to_cart_fragments` → `.header__basket` re-renders `template-parts/components/basket`; `.widget_shopping_cart_content` re-renders `woocommerce/cart/mini-cart`. |
| 61-76 | `login_form_lostpassword` → redirect `/reset-password?reset=success|failed`. |
| 79-89 | `wp_login_failed` → redirect `/customer-login?login=failed` (skips wp-admin referrers). |
| 91-111 | `wp_ajax(_nopriv)_woocommerce_update_cart_item` → AJAX qty update. Frontend in `assets/js/woocommerce.js`. |
| 114-116 | Override `woocommerce_order_button_text` → "Place your order". |

Slugs `/customer-login` + `/reset-password` MUST exist with login/reset-password page templates assigned.

## ACF options pages

| Subpage slug | Field file | Read pattern |
|---|---|---|
| `global_settings` | `acf-json/group_site_settings.json` | `get_field('<name>', 'option')` |
| `settings_header` | `acf-json/group_header.json` | `get_field('<name>', 'option')` |
| `settings_footer` | `acf-json/group_footer.json` | `get_field('<name>', 'option')` |

Known option keys in use: `maintenance_mode`, `disable_payments`, `header_logo` (`.url`).

## Deploy

Push to `main` → GH Actions runs `node minify.js` → SFTP via secrets `SERVER` `USERNAME` `PASSWORD` `PORT` `THEME_PATH`. `node_modules`, `package.json`, `package-lock.json` excluded.

**WARNING:** `minify.js` overwrites source files in place. Safe only on CI (throwaway). Never `node minify.js` locally on uncommitted work.

## Verification

No tests, no linter, no build. To verify:
- PHP syntax: `php -l <file>`.
- Visual: load page in Local site browser.
- Block: check WP admin block inserter under "Digiway Blocks" category + add block to a page.
- CSS: DevTools Network — confirm preload + enqueue both not present (no double-load).

## Don't

- Don't add per-block `index.php` / `fields.php` / `enqueue_assets`. Use `block.json`.
- Don't bump block `apiVersion` to 3.
- Don't double-load CSS (preload + auto-enqueue).
- Don't run `node minify.js` locally without committing.
- Don't remove jQuery without removing the WC `enqueue_script('cart-js', ...)` jQuery dep + the jQuery preload lines in `inc/preloads.php:75-86`.
- Don't reintroduce Windows path workarounds in block enqueue — core handles `file:` URLs cross-OS.
