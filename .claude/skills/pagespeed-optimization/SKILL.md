---
name: pagespeed-optimization
description: Runs Google PageSpeed Insights against one or more pages of a WordPress site and returns a prioritised, plugin-specific fix checklist. Use when the user asks to audit page speed, improve Core Web Vitals, fix a poor PSI score, or produce a performance action plan. Supports single-page and multi-page modes and emits either an in-chat checklist, an Excel tracker, or both.
---

# PageSpeed Optimization

A senior WordPress performance engineer workflow. Every finding from Google PageSpeed Insights is translated into a concrete, click-by-click fix inside the user's plugin stack.

Default assumed stack (confirm with user before relying on it):
- Cache: WP Fastest Cache
- Optimisation: Perfmatters
- Images: ShortPixel
- Theme: Kadence (has its own performance toggles that override Perfmatters for fonts, lazy-load, etc.)

## When invoked, ask two questions first

1. **Output format** — in-chat checklist, Excel tracker (`.xlsx`), or both?
2. **Scope** — just the homepage, or a prioritised multi-page crawl?

Do not run any PSI tests until both answers are confirmed.

## Multi-page mode

1. Fetch the sitemap (`/sitemap.xml` or `/sitemap_index.xml`) or crawl from the homepage.
2. Strip out posts, tag archives, category archives, author archives, date archives, attachments, and paginated URLs.
3. Rank the remaining URLs by commercial importance using this priority:
   1. Service / product pages
   2. Pricing
   3. Contact
   4. About
   5. Homepage (always include)
   6. Other top-level landing pages
4. Cap the list at **8 pages**.
5. Present the final list to the user and wait for confirmation before running any tests.

## Running the audit

For each confirmed URL:

1. Fetch the PSI report. PSI is JavaScript-rendered — use a browser-capable fetch tool, not a plain HTTP GET.
2. Capture: Performance score, LCP, CLS, INP, TBT, FCP, TTFB, and every failing / warning audit.
3. For each failing audit, map it to the exact plugin + setting:
   - Render-blocking resources → Perfmatters → Assets → Delay JS / Defer JS (check Kadence → Performance first; Kadence overrides these)
   - Unused CSS → Perfmatters → Assets → Remove Unused CSS
   - Image formats / sizing → ShortPixel → Bulk Optimize + WebP/AVIF delivery
   - Cache policy / TTL → WP Fastest Cache → Cache settings + Browser caching
   - Google Fonts → Kadence → Performance → Local fonts (NOT Perfmatters; Kadence overrides)
   - Third-party scripts → Perfmatters → Script Manager
   - Preconnect / preload → Perfmatters → Preload
4. Tag every fix as one of:
   - **Global** — fix once, whole site benefits
   - **Page-Specific** — only relevant to that URL

## Output

### In-chat checklist
Group by Global first, then Page-Specific (sub-grouped per URL). Each item:
- `[ ]` Checkbox
- Plugin → Section → Exact setting
- Expected metric impact (which audit it resolves)

### Excel tracker
Columns: `Scope` (Global / Page), `URL`, `Category`, `Plugin`, `Setting path`, `Action`, `Target audit`, `Expected impact`, `Status`, `Notes`.

## Perfmatters JSON handling

If the user provides an exported Perfmatters JSON config:
1. Parse it.
2. Apply the recommended setting changes directly to the JSON.
3. Return a properly formatted JSON file the user can re-import.
Never hand-edit around unknown keys — preserve the exact schema of the export.

## Iteration rules

- After each run, ask the user what was wrong or missing and edit this skill file directly in response — do not ask the user to edit it.
- When a new plugin / theme is encountered, add its override rules to the stack notes above.
- Keep recommendations click-by-click. No vague advice like "optimise images" — always name the plugin, section, and toggle.
