# lc-skeleton2026

Standalone base WordPress theme. Check it out, rename it, build on it — this
is a one-time-checkout base, not a live dependency: fixes made here aren't
intended to sync back into projects already forked from it.

## This is Bootstrap-*named*, not Bootstrap

Class names (`container`, `row`, `col-6`, `btn`, `navbar`, `d-flex`, etc.)
follow Bootstrap's conventions for familiarity — but every one of them is
implemented in this theme's own lightweight CSS. There is no Bootstrap
dependency, no Sass, no jQuery, no Popper. Don't assume parity with actual
Bootstrap docs/behaviour, especially for anything not covered below.

## What's in, what's not

- **Layout**: CSS Grid, not flexbox, for `.row`/`.col-*` (`.navbar` itself is
  flexbox — a single-row toolbar is a legitimate flex case). Nested `.row`
  uses `subgrid` where supported, as progressive enhancement only. `.col-*`
  spans are only meaningful relative to their own `.row`'s column count
  (12 by default) — you can't mix a 12-col span and a 5-col span in the same
  `.row`. For "N similar items, however many fit" layouts (card grids,
  feature lists) use `.grid-auto` instead — `repeat(auto-fit, minmax(...))`,
  no fixed column count, no breakpoint classes, tune the minimum item width
  per instance with `style="--grid-auto-min: 10rem"`.
- **Nav**: functional responsive nav + mobile toggle, vanilla JS
  (`src/js/nav-toggle.js`), no Bootstrap JS.
- **Modals**: native `<dialog>` (`src/js/dialog.js`), not a JS component.
- **Forms**: minimal, deliberately unopinionated base — expect to override it.
- **Buttons/cards**: no framework opinion at all — bring your own per project.
- **Tables**: real but optional (`src/css/tables.css`) — uncomment the
  `@import` in `src/css/theme.css` if a project needs one.
- **Icons**: inline SVG, no icon font.
- **No sidebars, no comments, no tags, no author archives, no `archive.php`,
  no `search.php`** — all confirmed rare-to-never in real usage; add them
  per-project if a project genuinely needs one.
- **WooCommerce**: not here at all. Ecommerce projects use the Understrap
  parent/child setup separately — this skeleton is for everything else.

## Design tokens

`src/css/tokens.css` — CSS custom properties (`--col-*`, `--fs-*`, `--space-*`,
etc.), no Sass variables. `src/build/generate-theme-json.js` reads this file
to produce `theme.json` so colors/font sizes show up in the block editor.

Breakpoints are the one exception — they live in `src/build/tokens.config.js`,
not `tokens.css`, because a CSS custom property can't be read inside an
`@media` condition. `src/css/nav.css` hand-duplicates the `lg` breakpoint in
a literal `@media` query for the same reason — keep it in sync with that
file if a breakpoint ever changes.

## Browser support

Modern evergreen only (last 2 versions of Chrome/Firefox/Safari/Edge, see
`.browserslistrc`). No IE11, no legacy fallback layer. Grid, subgrid
(progressive enhancement), `clamp()`, native `<dialog>`, `:has()`, and CSS
nesting are all used without hesitation.

## Build

```
npm install
npm run watch      # rebuilds CSS/JS on save
npm run watch-bs   # same, plus browser-sync live reload (proxies localhost/)
npm run dist       # one-off full build
npm run generate-theme-json   # regenerate theme.json from tokens.css
```

`npm run css` runs three steps: `generate-utilities.js` (produces
`src/css/utilities.css` — the breakpoint-suffixed grid/utility classes — and
`src/css/blocks.css`, a concatenation of `src/blocks/*.css`), then
PostCSS (`postcss-import` + `postcss-nesting` + `autoprefixer`), then
minification.

## Adding a block

```
./add_block.sh
```

Scaffolds the PHP render template, an ACF JSON field group, and registers
the block in `inc/blocks.php`. It does **not** create a CSS file — if the
block needs custom styles, just add `src/blocks/{block-slug}.css`; it's
picked up automatically on the next build, no registration step. Load order
for these is alphabetical by filename, not registration order.
