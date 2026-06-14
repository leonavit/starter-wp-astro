# WP × Astro Starter — WordPress Theme

A **minimal WordPress theme** that powers the headless Astro frontend.
WordPress Core only — **no plugins** required.

## What it provides

- **Theme Options admin page** under "🎨 הגדרות תבנית" with 4 tabs:
  General, Contact, Social, SEO.
- **REST endpoint** at `/wp-json/wpas/v1/options` returning every value
  as one JSON document the Astro build consumes.
- All values live in a single `wp_options` row keyed by
  `WPAS_OPTION_KEY` ("wpas_theme_options").
- Two registered nav menus (`primary`, `footer`) — automatically
  exposed via WP REST's `/wp-json/wp/v2/menu-items` endpoint when used
  on a block theme or via classic menus management.

## Install

1. Copy the `wp-astro-starter/` folder into `wp-content/themes/`.
2. WP-Admin → Appearance → Themes → activate **WP × Astro Starter**.
3. WP-Admin → Settings → Permalinks → Save (refreshes REST routes).
4. WP-Admin → 🎨 הגדרות תבנית → fill in your business info.
5. Verify by visiting `/wp-json/wpas/v1/options` in your browser.

## Wiring it to Astro

In the Astro project's `.env`:

```
WP_REST_URL=https://your-wp-site.com/wp-json
```

The Astro lib (`src/lib/wp-options.ts`) fetches
`${WP_REST_URL}/wpas/v1/options` at build time and falls back to
`src/config/site.ts` if the endpoint is unreachable.

## File map

```
wp-astro-starter/
├── style.css                  # theme header (required by WP)
├── functions.php              # bootstrap, requires the inc/ files
├── README.md
├── assets/
│   ├── admin.css              # styles for the Theme Options page
│   └── admin.js               # hours repeater + media picker
└── inc/
    ├── helpers.php            # shared field renderers, sanitizers
    ├── theme-options.php      # admin menu page, tabs, save handler
    ├── section-general.php    # brand identity (name, logo, color)
    ├── section-contact.php    # phone, email, WhatsApp, address, hours
    ├── section-social.php     # Facebook, Instagram, LinkedIn, ...
    ├── section-seo.php        # GA4, GTM, Pixel, Search Console
    └── rest-api.php           # /wp-json/wpas/v1/options
```

## Adding a new section

1. Create `inc/section-mything.php` with three functions:
   - `wpas_default_mything_settings()` → array of defaults.
   - `wpas_sanitize_mything_settings($input)` → sanitized array.
   - `wpas_render_section_mything($key)` → admin UI.
2. `require_once` it from `functions.php`.
3. Add a tab entry in `wpas_theme_options_tabs()`
   (`inc/theme-options.php`).
4. Register the section's sanitizer call in
   `wpas_sanitize_theme_options()`.
5. Add the section's defaults to `wpas_get_theme_options()` and the
   payload in `wpas_rest_get_options()`.

## Conventions

- All callables are prefixed `wpas_*`.
- All option arrays are stored under the single `WPAS_OPTION_KEY`
  row, namespaced by section (`general`, `contact`, ...).
- Sanitizers always merge against defaults so a missing input never
  blanks the value.
- Media fields store `{ attachment_id, url }` and resolve to a URL at
  REST output time via `wpas_attachment_url()`.
