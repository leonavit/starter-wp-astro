# Starter WP × Astro

Production-ready **Astro + Headless WordPress** starter with WPGraphQL,
Tailwind CSS, full RTL/Hebrew support, and built-in fallback content so
the build always succeeds — even without a live CMS.

## Features

- **Perfect content mirroring.** Every Page, Post, and CPT URI from WP is
  generated as a static page via `src/pages/[...uri].astro`.
- **WPGraphQL data layer.** A single query pulls all pages/posts at build
  time. Errors and empty results fall back to seed content.
- **Hebrew-first.** RTL layout, Heebo font, and four seeded pages
  (`/`, `/about`, `/services`, `/contact`) in Hebrew.
- **Tailwind CSS** with `.wp-content` classes that style standard
  Gutenberg blocks (cover, button, image, headings, alignments).
- **SEO baked in.** `BaseLayout.astro` accepts `title`, `description`,
  and `ogImage` — wired to WP SEO data when available.

## Quick start

```bash
cp .env.example .env       # fill in WP_GRAPHQL_URL if you have one
npm install
npm run dev
```

The site builds and runs with no env vars: when `WP_GRAPHQL_URL` is
missing (or still the example placeholder), the build uses the seed
content in `src/lib/wp.ts`.

## Project structure

```
src/
├── components/
│   ├── layout/
│   │   ├── Header.astro
│   │   └── Footer.astro
│   ├── wp-blocks/
│   │   ├── CoreHeading.astro
│   │   └── CoreParagraph.astro
│   └── WpContent.astro      # Raw WP HTML → Tailwind-styled output
├── layouts/
│   ├── BaseLayout.astro     # <head>, SEO, RTL wrapper, header/footer
│   ├── HomeLayout.astro
│   ├── PageLayout.astro
│   ├── ContactLayout.astro
│   └── PostLayout.astro
├── lib/
│   └── wp.ts                # GraphQL client + seed content
├── pages/
│   ├── [...uri].astro       # Master router
│   └── 404.astro
└── styles/
    └── global.css
```

## How routing works

`getStaticPaths()` in `src/pages/[...uri].astro` calls `getAllContent()`,
which queries WordPress via WPGraphQL for all published pages and posts.
Each returned node becomes a route — the homepage (`uri === ""`) is
emitted with `params.uri = undefined`, which Astro maps to the site
root.

Layout selection lives in `pickLayout()`:

| Condition                          | Layout         |
| ---------------------------------- | -------------- |
| `__typename === "Post"`            | `PostLayout`   |
| `uri === ""`                       | `HomeLayout`   |
| WP custom template `HomeLayout`    | `HomeLayout`   |
| WP custom template `ContactLayout` | `ContactLayout`|
| `uri === "contact"`                | `ContactLayout`|
| Anything else                      | `PageLayout`   |

## Fallback seed content

`src/lib/wp.ts` exports `SEED_CONTENT`, a list of four pages used when
WordPress is unreachable or missing them. Edit it freely — the merge
logic in `getAllContent()` adds seeds **only** for URIs the CMS hasn't
already provided, so seeds never overwrite live content.

## Trailing slashes

The Astro config sets `trailingSlash: "never"`. `sanitizeUri()` strips
leading/trailing slashes from every URI returned by WPGraphQL so the
mappings line up.

## Adding a new layout

1. Drop a `.astro` file into `src/layouts/`.
2. Reference it in `pickLayout()` inside `src/pages/[...uri].astro`,
   matching either a WP custom-template slug (recommended) or a URI.

## License

MIT.
