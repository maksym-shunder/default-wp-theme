---
name: create-gutenberg-block
description: Scaffold a new ACF-driven Gutenberg block in this WordPress theme from a Figma link or an image/screenshot. Use this skill whenever the user wants to create, build, scaffold, or add a Gutenberg block, a custom block, an ACF block, or a "digiway" block — and provides a Figma URL (figma.com/...), a Figma file/node reference, or any image, screenshot, mockup, or design reference. Also trigger when the user says "make this section a block", "turn this design into a block", "add a new block called X", "I need a block for [feature]", or pastes a Figma frame URL with intent to implement it. ALSO TRIGGER for the same intent expressed in Ukrainian or Russian — including phrases like "створи новий блок", "зроби блок", "додай блок", "новий блок", "блок гутенберг", "блок для секції", "перетвори цей дизайн в блок", "зроби з цього блок", "создай блок", "сделай блок", "новый блок", "добавь блок", "блок для", "сделай из этого блок", "превратить в блок" — and any other Cyrillic phrasing equivalent to "create a (new) Gutenberg/ACF block" with or without a Figma/image reference. Produces the four canonical files (block.json, render.php, assets/style.css, acf-json/group_<slug>.json) in the exact shape the theme's block-registration system expects, reuses existing CSS color variables from global.css, and uses safe render patterns so image/file fields don't crash the block editor preview.
---

# Create Gutenberg Block

Scaffold a new ACF-driven Gutenberg block in this theme. The theme registers blocks by globbing `template-parts/gutenberg-blocks/*/block.json` in `inc/acf/registration.php`, so dropping a correctly-shaped folder is enough — no PHP registration code needed.

## Inputs the user may provide

- **Figma URL** (`figma.com/...` or `figma.com/design/...` or a specific node URL) — fetch design data via Figma MCP.
- **Image / screenshot / mockup** — local path or image attached to the conversation. Read visually.
- **Optional**: desired block slug (e.g. "hero-banner"), block title, content fields they want.

If the user gives an ambiguous request ("add a block"), ask for the design reference + intended slug before generating anything.

## Workflow

1. Gather design data (Figma MCP or vision).
2. Decide block slug + title + field list.
3. Map colors to existing CSS variables; only invent new ones when no existing variable fits.
4. Write the four files atomically.
5. Verify with the checklist at the end.

## Step 1 — Gather design data

### Figma path

Use Figma MCP tools to read the design:

- `mcp__plugin_figma_figma__get_design_context` — primary tool. Pass the Figma URL or node ID. Returns layout, hierarchy, text content, image refs, color tokens, spacing, typography.
- `mcp__plugin_figma_figma__get_variable_defs` — pull defined variables (colors, spacing, font sizes) so you can match them to the theme's existing variables instead of hardcoding hex.
- `mcp__plugin_figma_figma__get_screenshot` — only if you also need a visual reference to verify layout. Don't call by default.

Don't invoke `/figma-use` or `use_figma` — those are for writing TO Figma. This skill only reads.

If the URL doesn't resolve (no auth, deleted node), tell the user and ask for either re-shared access or an image fallback.

### Image path

Read the image directly (Read tool on the file path, or look at the attached image). Extract:

- Visual structure: section, columns, grid, list, card layout.
- Text content: headings, paragraphs, button labels.
- Images: how many, aspect ratios, decorative vs content.
- Colors: dominant colors, accents, text colors. Map to existing CSS vars (next step).
- Interactive elements: buttons, links, video, accordion, slider — these may need scripts.

If the image is low-resolution or ambiguous on a specific detail, ask the user one focused question rather than guessing.

## Step 2 — Reuse colors from `assets/css/global.css`

The theme defines its design tokens as CSS custom properties in `:root` at `assets/css/global.css:1-8`. **Read that block first** to see what's currently defined. As of writing, the variables include:

```
--font
--text-color
--title-color
--accent-color
--section-space
```

Note: `theme.json` palette is intentionally empty — colors live in CSS variables, not Gutenberg theme.json. Don't add palette entries to `theme.json`.

Rules:

- If the Figma/image color matches an existing variable (visually close enough — within ~5% hue/lightness), use `var(--name)`.
- If a clearly new accent appears (e.g. a brand secondary), add a new variable to the `:root` block in `assets/css/global.css` with a descriptive name (`--secondary-color`, `--success-color`, etc.) and use it in the block CSS. Don't add it to `theme.json`.
- Never hardcode the same hex twice — promote to a variable on the second use.
- Text color defaults: titles → `var(--title-color)`, body → `var(--text-color)`.

## Step 3 — Decide naming

- **Slug** (kebab-case): e.g. `hero-banner`, `feature-grid`, `image-text-split`. Used as folder name and block name suffix.
- **Underscored slug**: e.g. `hero_banner`. Used for ACF group key + field keys (existing convention — see `acf-json/group_example_block.json`).
- **Block name**: `digiway/<slug>` (namespace required for category to apply).
- **Title**: Human-friendly Title Case, e.g. "Hero Banner".
- **Field names** (snake_case): `title`, `subtitle`, `image`, `cta_label`, `cta_url`, `items` (repeater), etc.

## Step 4 — Write the four files

All paths relative to theme root (`default-wp-theme/`).

### 4a. `template-parts/gutenberg-blocks/<slug>/block.json`

Match the example-block shape exactly. `apiVersion` stays at 2 — do not bump to 3 (ACF v3 iframe model not verified per theme CLAUDE.md). `acf.mode` is `preview` (matches `example-block`).

```json
{
	"$schema": "https://schemas.wp.org/trunk/block.json",
	"apiVersion": 2,
	"name": "digiway/<slug>",
	"title": "<Title>",
	"description": "<short one-line description>",
	"category": "digiway-blocks",
	"icon": "<dashicon-name>",
	"keywords": [],
	"textdomain": "digiway",
	"supports": {
		"anchor": true,
		"jsx": true
	},
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

Omit the `viewScript` line if the block has no JS. Omit `style` only if there's truly no CSS (rare). Pick an icon from [dashicons](https://developer.wordpress.org/resource/dashicons/) that matches the block purpose.

### 4b. `template-parts/gutenberg-blocks/<slug>/render.php`

Use `theme_field()` (defined in `inc/helpers.php`) for every field read so the editor preview shows defaults before the user fills anything in. Always escape output. Forward `$block['anchor']` and merge `$block['className']`.

**Root element must be `<section>`.** Every block's outermost rendered element is a `<section>` tag with the block's root class. Never use `<div>`, `<article>`, or any other tag at the root — `<section>` carries the semantic + theme convention for vertical-rhythm targeting.

**Image/file safety**: ACF image and file fields with `return_format: array` return either an array (when set) or `false`/`''` (when empty). Subscripting that empty value with `$img['url']` crashes the editor preview iframe and leaves the user looking at a "block has encountered an error" message. Always guard:

```php
$image = theme_field('image', false);
if (!empty($image) && is_array($image)) {
	$image_url = $image['url'];
	$image_alt = $image['alt'] ?? '';
	$image_w   = $image['width'] ?? null;
	$image_h   = $image['height'] ?? null;
}
```

Apply the same pattern to file fields (`$video['url']`, `$video['mime_type']`, etc.). Never write `$image['url']` without a prior `is_array($image)` check.

Full render template skeleton:

```php
<?php
$title    = theme_field('title', __('Default title', 'digiway'));
$subtitle = theme_field('subtitle', '');
$image    = theme_field('image', false);
$cta_url  = theme_field('cta_url', '');
$cta_label = theme_field('cta_label', __('Learn more', 'digiway'));

$block_anchor  = $block['anchor'] ?? '';
$block_classes = '<slug>';
if (!empty($block['className'])) {
	$block_classes .= ' ' . $block['className'];
}
?>

<section
	class="<?= esc_attr($block_classes) ?>"
	<?php if ($block_anchor): ?>id="<?= esc_attr($block_anchor) ?>"<?php endif; ?>
>
	<div class="container">
		<?php if ($title): ?>
			<h2 class="<?= esc_attr($block_classes) ?>__title"><?= esc_html($title) ?></h2>
		<?php endif; ?>

		<?php if ($subtitle): ?>
			<p class="<?= esc_attr($block_classes) ?>__subtitle"><?= esc_html($subtitle) ?></p>
		<?php endif; ?>

		<?php if (!empty($image) && is_array($image)): ?>
			<img
				class="<?= esc_attr($block_classes) ?>__image"
				src="<?= esc_url($image['url']) ?>"
				alt="<?= esc_attr($image['alt'] ?? '') ?>"
				<?php if (!empty($image['width']) && !empty($image['height'])): ?>
					width="<?= esc_attr($image['width']) ?>"
					height="<?= esc_attr($image['height']) ?>"
				<?php endif; ?>
				loading="lazy"
			>
		<?php endif; ?>

		<?php if ($cta_url && $cta_label): ?>
			<a class="primary_button" href="<?= esc_url($cta_url) ?>">
				<?= esc_html($cta_label) ?>
			</a>
		<?php endif; ?>
	</div>
</section>
```

Escaping rules:
- Text in HTML body → `esc_html()`.
- HTML attributes → `esc_attr()`.
- URLs → `esc_url()`.
- Rich text from a `wysiwyg` field → `wp_kses_post()`.
- Trusted ACF content where HTML should pass through (rare) → comment why.

Use `loading="lazy"` for below-the-fold images. For an above-the-fold hero image, use `fetchpriority="high"` instead — `inc/theme.php` extracts those for preloading.

### 4c. `template-parts/gutenberg-blocks/<slug>/assets/style.css`

Native CSS nesting is supported (deploy runs `postcss-nesting` before `cssnano`). Scope everything under the block's root class. Use the theme's existing variables.

```css
.<slug>__title {
	font-size: 40px;
	color: var(--title-color);
	margin-bottom: 16px;
}

.<slug>__subtitle {
	color: var(--text-color);
	margin-bottom: 32px;
}

.<slug>__image {
	display: block;
	width: 100%;
	height: auto;
	border-radius: 8px;
}

@media (max-width: 767px) {
	.<slug>__title {
		font-size: 24px;
	}
}
```

**Do NOT set top/bottom padding or margin on the block root.** Vertical spacing between blocks is handled globally — the theme already enforces `--section-space` between sibling `<section>` blocks (see `inc/theme.php` / `global.css`). Adding `padding: var(--section-space) 0` per block double-spaces and breaks rhythm. Only add vertical padding if the block needs internal padding beyond the global rule (e.g. a full-bleed colored section with extra breathing room) — and document why in a one-line comment.

Use flat selectors (`.<slug>__name`), not `&__name` BEM concat — native browser CSS nesting doesn't support that form and local dev serves raw, unprocessed CSS.

Don't import other CSS files from here. Block CSS auto-loads in editor + frontend via the `style:` key in `block.json`.

If the block needs JS, create `assets/script.js`. It's auto-enqueued only on pages where the block actually renders, so feel free to scope freely. Plain vanilla JS — no module imports, no build step.

**Always wrap block JS in an IIFE.** Block scripts share the global scope with every other block script on the page. Top-level `const`/`let`/`function` declarations collide across blocks (e.g. two blocks both declaring `const items = ...` at top level → "Identifier 'items' has already been declared", whole script throws, every block on the page breaks). Wrap everything in an IIFE so all locals stay scoped to the block. Match the `example-block` pattern:

```js
(function () {
	document.addEventListener('DOMContentLoaded', function () {
		// block-local variables and event listeners go here
		const root = document.querySelector('.<slug>');
		if (!root) return;

		// ... block logic ...
	});
})();
```

Rules inside the IIFE:

- `document.querySelector('.<slug>')` (or `querySelectorAll` if the block can render multiple times per page) to scope DOM lookups to this block. Bail early with `if (!root) return;` so the script no-ops on pages that don't render the block (defensive — usually core only loads it when present, but the IIFE still runs).
- Don't attach handlers to `window` / `document` for events the block doesn't own. Listen on the block root.
- Don't leak helper functions onto `window.*`. If two blocks both need the same helper, put it in `assets/js/main.js` (theme-wide) — don't copy it into both block scripts.

### 4d. `acf-json/group_<slug_underscore>.json`

Match the existing shape exactly. Key fields:

- `key`: `group_<slug_underscore>`
- `title`: human-friendly group title
- `fields[]`: each field has `key: field_<slug_underscore>_<field_name>`, `name: <field_name>`, plus type-specific config
- `location`: bind to the block by name `digiway/<slug>`

Template:

```json
{
	"key": "group_<slug_underscore>",
	"title": "<Title>",
	"fields": [
		{
			"key": "field_<slug_underscore>_title",
			"label": "Title",
			"name": "title",
			"type": "text"
		},
		{
			"key": "field_<slug_underscore>_subtitle",
			"label": "Subtitle",
			"name": "subtitle",
			"type": "textarea",
			"rows": 3
		},
		{
			"key": "field_<slug_underscore>_image",
			"label": "Image",
			"name": "image",
			"type": "image",
			"return_format": "array",
			"preview_size": "medium"
		},
		{
			"key": "field_<slug_underscore>_cta_label",
			"label": "CTA Label",
			"name": "cta_label",
			"type": "text",
			"wrapper": { "width": "50%" }
		},
		{
			"key": "field_<slug_underscore>_cta_url",
			"label": "CTA URL",
			"name": "cta_url",
			"type": "url",
			"wrapper": { "width": "50%" }
		}
	],
	"location": [
		[
			{
				"param": "block",
				"operator": "==",
				"value": "digiway/<slug>"
			}
		]
	],
	"menu_order": 0,
	"position": "normal",
	"style": "default",
	"label_placement": "top",
	"instruction_placement": "label",
	"hide_on_screen": "",
	"active": true,
	"description": "",
	"show_in_rest": 0
}
```

**Field type cookbook:**

| ACF type | Use for | Required config |
|---|---|---|
| `text` | Short single-line text | — |
| `textarea` | Multi-line text | `"rows": N` |
| `wysiwyg` | Rich text (paragraphs, lists, links) | `"toolbar": "basic"` for limited |
| `image` | Single image | `"return_format": "array"`, `"preview_size": "medium"` |
| `file` | Video/PDF/other binary | `"return_format": "array"`, `"mime_types": "mp4,webm"` |
| `url` | Plain URL string only (rare — prefer `link`) | — |
| `link` | **Default for any button or link** — label + URL + target | `"return_format": "array"` |
| `select` | Predefined choice | `"choices": { "key": "Label" }` |
| `true_false` | Boolean toggle | `"ui": 1` |
| `repeater` | List of structured items | `"sub_fields": [...]`, `"layout": "block"`, `"button_label": "Add item"` |
| `group` | Nested named struct | `"sub_fields": [...]`, `"layout": "block"` |

For `repeater`/`group` sub_fields, keep using the `field_<slug>_<parent>_<child>` key naming pattern to stay readable.

**Buttons and links — always use `link`.** Never split a CTA into separate `cta_label` (text) + `cta_url` (url) fields. One `link` field returns `{ url, title, target }` and gives editors the WP link picker (internal pages, external URLs, target). In `render.php`:

```php
$cta        = $item['cta'] ?? false;
$cta_url    = (!empty($cta) && is_array($cta)) ? ($cta['url']    ?? '') : '';
$cta_label  = (!empty($cta) && is_array($cta)) ? ($cta['title']  ?? '') : '';
$cta_target = (!empty($cta) && is_array($cta)) ? ($cta['target'] ?? '') : '';
?>
<?php if ($cta_label && $cta_url): ?>
	<a
		class="..."
		href="<?= esc_url($cta_url) ?>"
		<?php if ($cta_target): ?>target="<?= esc_attr($cta_target) ?>" rel="noopener"<?php endif; ?>
	>
		<?= esc_html($cta_label) ?>
	</a>
<?php endif; ?>
```

## Reuse existing components

Before styling a button, link, input, badge, or any common UI element from scratch, **check what's already in the theme** and reuse it. Custom block-scoped styles are the last resort.

Existing reusable classes (verify against `assets/css/global.css` since this list will drift):

- **Buttons** — `.primary_button` (accent-color background, used theme-wide). Apply directly to the rendered `<a>` or `<button>`. If the design needs a visual variant that doesn't exist yet (e.g. white-bg button), add a modifier class to `global.css` (`.primary_button--light`) rather than a new block-local class — so the next block that needs the same variant doesn't reinvent it.
- **Links** — `.primary_link` (underlined inline link).
- **Container** — `.container` (max-width + responsive horizontal padding). Always wrap block content in `<div class="container">` unless the design is intentionally edge-to-edge.
- **Inputs/forms** — check `woocommerce/` and `templates/template-{login,signup,...}.php` for the existing form-field styles before re-styling.

Process:

1. Grep `assets/css/global.css` for the element you're about to add (`grep -in "button\|link\|input\|container" assets/css/global.css`).
2. If a matching class exists → use it on the rendered element. Don't duplicate its styles into the block CSS.
3. If the design needs a variant → add a modifier class to the existing component in `global.css`, then use both classes on the element.
4. Only when nothing close exists → create a block-local class.

This keeps visual language consistent and avoids 6 different button styles across 6 blocks.

`show_in_rest` stays `0` unless the block needs to expose data to the REST API (rare).

## Step 5 — Verify

Before declaring done, walk this checklist:

1. **Files exist** at all four paths (use `ls` on the slug folder and the acf-json file).
2. **JSON validates** — run `php -r 'json_decode(file_get_contents("acf-json/group_<slug>.json")); echo json_last_error_msg();'` and confirm "No error".
3. **PHP syntax** — `php -l template-parts/gutenberg-blocks/<slug>/render.php`.
4. **No double color hardcoding** — `grep -E '#[0-9a-fA-F]{3,6}' template-parts/gutenberg-blocks/<slug>/assets/style.css` should return only colors that aren't yet variables; if you see `--accent-color`'s hex (`#CFDBC3`) hardcoded, replace with `var(--accent-color)`.
5. **Image guard present** — if the block has any `image`/`file` field, confirm `render.php` uses `if (!empty($x) && is_array($x))` before subscripting.
6. **Block name matches everywhere** — `digiway/<slug>` appears in `block.json` `name` and in `acf-json/group_<slug>.json` `location[0][0].value`.
7. **Slug underscore consistency** — `group_<slug_underscore>` and `field_<slug_underscore>_*` all use underscores (not hyphens). Folder name and `block.json` `name` use hyphens.
8. **Tell the user how to test**: open WP admin → edit any page → block inserter → "Digiway Blocks" category → insert "<Title>" block → confirm fields appear in the toolbar popover, defaults render in preview, filling fields updates preview, saving works.

## Common pitfalls

- **Editor preview crash with "block has encountered an error"** — almost always an unguarded `$image['url']` or `$file['url']` in `render.php`. Fix per Step 4b.
- **Block appears uncategorized** — `block.json` `name` missing `digiway/` prefix, or `category` not set to `digiway-blocks`.
- **Fields don't appear in the editor** — `acf-json/group_<slug>.json` `location[0][0].value` doesn't match `block.json` `name` exactly. Both must be `digiway/<slug>`.
- **CSS doesn't load on frontend** — wrong `style:` path in `block.json`. Must be `file:./assets/style.css` (relative).
- **CSS loads on frontend but not in editor preview** — same fix; the `file:` ref handles both contexts as long as the path is correct.
- **Image preload not happening for hero blocks** — add `fetchpriority="high"` to the `<img>` tag. `inc/theme.php::get_images_from_first_block_on_page()` scans for that attribute inside the first top-level `<section>` and `inc/preloads.php` injects preload links.
- **Trying to use `theme.json` palette** — palette is empty by design. Colors are CSS vars in `assets/css/global.css`. Don't change this.
- **New top-level CSS file added to `assets/css/`** — irrelevant here; per-block CSS lives inside the block folder. If you genuinely need a shared CSS file across blocks, follow the bucket rules in `AGENTS.md` (preload vs auto-enqueue).

## Anti-patterns — do not do these

- Don't create per-block `index.php` calling `acf_register_block_type()`. Replaced by `block.json` flow.
- Don't create per-block `fields.php` calling `acf_add_local_field_group()`. Fields live in `acf-json/`.
- Don't add per-block `enqueue_assets` callbacks. Core handles via `block.json` `style:` / `viewScript:`.
- Don't bump `apiVersion` to 3.
- Don't import the block CSS into `global.css` or any auto-enqueued sheet. Block CSS must stay scoped.
- Don't use `get_field()` directly in render templates — use `theme_field()` so previews have defaults.
- Don't split a button into `*_label` + `*_url` text fields — use a single `link` field. See "Buttons and links" above.
- Don't re-style an element from scratch when a global component (`.primary_button`, `.primary_link`, etc.) already covers it. See "Reuse existing components" above.
- Don't use `&__name` BEM concat in CSS — native browser CSS nesting doesn't support it and rules will silently drop (local dev serves raw, postcss only runs on CI). Use flat selectors like `.block__name`, or `&` followed by space/combinator only.
- Don't set top/bottom padding or margin on the block root. Global vertical rhythm handles inter-block spacing. See "assets/style.css" above.
- Don't use anything other than `<section>` as the block root element. See "render.php" above.
- Don't write block JS without wrapping it in an IIFE. Top-level declarations leak into global scope and collide with other blocks' scripts. See "assets/script.js" above.
