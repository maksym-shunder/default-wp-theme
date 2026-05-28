# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A custom WordPress theme (slug: `default-wp-theme`) with WooCommerce integration and an ACF-driven Gutenberg block system. It lives inside a Local by Flywheel site at `app/public/wp-content/themes/default-wp-theme/`. There is no standalone app — code runs inside WordPress.

**Required plugin:** Advanced Custom Fields (ACF). **Optional:** WooCommerce (see README "If WooCommerce is Not Used" for the removal checklist).

## Commands

There is no `package.json` checked in; `minify.js` is run by CI, not locally.

- **Run minifier locally** (overwrites source files in place — usually only useful when debugging the deploy step):
  ```
  npm install --no-save terser postcss postcss-nesting cssnano
  node minify.js
  ```
- **Deploy:** push to `main` (or merge a PR into `main`). `.github/workflows/deploy.yml` runs `node minify.js` then SFTPs the theme using the `SERVER` / `USERNAME` / `PASSWORD` / `PORT` / `THEME_PATH` GitHub Secrets. `node_modules`, `package.json`, and `package-lock.json` are excluded from the rsync.
- **No tests, no linter, no build step for local development** — WordPress consumes the PHP/CSS/JS directly.

## Architecture

### Bootstrap

`functions.php` is the single entry point. It `require_once`s every module under `inc/` in this order: `theme.php` → `acf/acf.php` → `enqueue-scripts.php` → `maintenance-page.php` → `helpers.php` → `woocommerce.php`. New top-level modules should be added here.

### Gutenberg blocks use block.json + ACF render

`inc/acf/registration.php` runs a scanner on the `init` hook that globs `template-parts/gutenberg-blocks/*/block.json` and calls core `register_block_type()` on each directory. ACF reads the `acf` namespace inside `block.json` and handles server rendering via the named `renderTemplate`. The same registration file also registers the `digiway-blocks` block category via `block_categories_all`. A new block = a new folder with this layout:

```
template-parts/gutenberg-blocks/<block-name>/
├── block.json        # apiVersion 2 metadata + "acf": { "mode": "auto", "blockVersion": 3, "autoInlineEditing": true, "renderTemplate": "render.php" }
├── render.php        # server render template ($block, $is_preview, etc. available)
└── assets/
    ├── style.css     # auto-enqueued via "style": "file:./assets/style.css" — loads in editor iframe AND frontend
    └── script.js     # optional, via "viewScript": "file:./assets/script.js"
```

No `index.php`, no `fields.php` per block — both are gone. Field groups for blocks live in **ACF Local JSON** under the theme-root `acf-json/` directory. `functions.php` registers `acf/settings/save_json` and `acf/settings/load_json` filters that point ACF at this directory, so editing a block's fields in the WP admin syncs back to it as a JSON diff.

**Block API & ACF mode:** `apiVersion: 2` + `acf.mode: "auto"` + `acf.blockVersion: 3` + `acf.autoInlineEditing: true` is the chosen combo. It gives the "preview in canvas + fields in a popover from the block toolbar" UX. Do not bump `apiVersion` to 3 unless you've verified the ACF block render path tolerates the v3 iframe model.

**Editor preview styling:** `inc/theme.php` declares `add_theme_support('editor-styles')` + `add_editor_style('assets/css/global.css')`. `inc/enqueue-scripts.php` additionally hooks `enqueue_block_editor_assets` to force `global.css` + `fonts.css` into the editor iframe so block previews match the frontend visually. Per-block `style.css` already loads in both contexts via the `file:` reference in `block.json`.

**`theme_field()` helper** (`inc/helpers.php`) wraps `get_field()` with a default-value fallback. Render templates should use it instead of raw `get_field()` so a block always renders something visible in the editor before content is added.

Drop a folder in with a `block.json` and the matching `acf-json/group_<name>.json` and it's live. Frontend-only assets are auto-loaded by core (lazy, only when the block renders).

**Things this replaced** (do not reintroduce): per-block `index.php` calling `acf_register_block_type()`, per-block `fields.php` calling `acf_add_local_field_group()`, per-block `enqueue_assets` callbacks with Windows path workarounds. Core resolves `file:` URLs correctly on every OS now.

### CSS loading is split between three mechanisms

This is the most counterintuitive part of the codebase. **Before adding or moving a CSS file, decide which bucket it belongs in:**

1. **Critical / above-the-fold:** manually preloaded in `inc/preloads.php` with `rel="preload" as="style" onload="this.rel='stylesheet'"`. Currently: `global.css`, `components/header.css`, `components/popup.css`, `components/cart-popup.css`. These files are listed in the exclude array in `inc/enqueue-scripts.php::auto_enqueue_styles()` so they are **not** double-loaded.
2. **Auto-enqueued:** every other `*.css` under `assets/css/` (root + one subdir level via `glob('**/*.css', GLOB_BRACE)`) is enqueued by `auto_enqueue_styles()` with `filemtime()` as the cache-buster.
3. **First-block CSS:** `inc/preloads.php` calls `get_first_block_name_on_page()` (defined in `inc/theme.php`) and preloads `template-parts/gutenberg-blocks/<name>/assets/style.css` if present. Also preloads any `<img fetchpriority="high">` inside the first top-level section of the post content (see `get_images_from_first_block_on_page()`).

If you add a file to the exclude list in `auto_enqueue_styles()`, you almost always need to add a matching `<link rel="preload">` in `inc/preloads.php` — and vice versa.

### Maintenance mode

`inc/maintenance-page.php` creates a `/maintenance` page on theme activation. When the ACF option `maintenance_mode` (Global Settings) is truthy, `template_redirect` sends every non-admin (anyone without `manage_options`) to that page. Admin and `wp-login.php` are excluded.

### WooCommerce integration points

`inc/woocommerce.php` does several non-obvious things; check it before touching cart/checkout flows:

- Moves the payment block from `woocommerce_checkout_order_review` to `woocommerce_checkout_after_customer_details` (changes checkout layout).
- Registers cart fragments for `.header__basket` (re-renders `template-parts/components/basket`) and `.widget_shopping_cart_content` (re-renders `woocommerce/cart/mini-cart`).
- Adds `wp_ajax_woocommerce_update_cart_item` for in-place quantity changes — the frontend handler lives in `assets/js/woocommerce.js`.
- Redirects login failures to `/customer-login?login=failed` and password-reset attempts to `/reset-password?reset=success|failed`. These slugs must match the pages that use `templates/template-login.php` and `templates/template-reset-password.php`.
- The `disable_payments` ACF option flips `woocommerce_is_purchasable` to false globally.

WooCommerce template overrides live under `woocommerce/` and follow standard WC template-hierarchy paths.

### ACF options pages

`inc/acf/options-pages.php` registers a top-level "Theme Settings" page with three subpages: **Global Settings**, **Settings Header**, **Settings Footer**. Field groups live in `inc/acf/options-pages-fields/settings-{global,header,footer}.php` and are required from `inc/acf/acf.php`. Reads use `get_field('<name>', 'option')`.

### Custom page templates

`templates/template-{checkout,login,signup,reset-password}.php` are WordPress page templates — assign them via the page editor's Template dropdown. Login/signup/reset-password are designed to work with WooCommerce's customer account flows; the redirect targets in `inc/woocommerce.php` assume the corresponding pages exist at the expected slugs (`/customer-login`, `/reset-password`).

## Deploy minifies in place — keep this in mind

`minify.js` **overwrites the source files** in `assets/js/`, `assets/css/`, and `template-parts/gutenberg-blocks/` with minified output. This only happens on the CI runner (the workspace there is throwaway), so committed source stays readable. But: never run `node minify.js` locally against a working tree you care about without committing first. CSS is processed through `postcss-nesting` → `cssnano`, so native CSS nesting is supported in source.
