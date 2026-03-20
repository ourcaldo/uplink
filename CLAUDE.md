# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

UPlink is a PHP gate-flow application serving two domains (alco.camarjaya.co.id and elco.camarjaya.co.id), showing randomized articles with randomized ad placements on each page. Articles are served at `/{slug}` URLs. Both gates point their "next" button to `app.camarjaya.co.id` (no longer looping between gates).

## Architecture

**Entry point:** `index.php` — detects gate via HTTP_HOST, resolves the slug from the URL path, loads a random article if no valid slug is provided (302 redirect), then renders the page with ads.

**Config & logic split:**
- `lib/config.php` — gate URLs, ad code arrays (per-gate and shared Adsense), and the `$adsByGate` mapping
- `lib/functions.php` — all helper functions: traffic filtering, article parsing/catalog, ad slot building, markdown-to-HTML conversion

**Ad system:** Ads are categorized into types (vertical/sidebar, content iframe, popunder, smartlink, non-display scripts, Adsense). `buildPageAds()` enforces a max of 5 non-Adsense display ads per page (2 sidebars + up to 3 content slots). Small banners can stack two per slot. Adsense ads are separate and unlimited. Popunders are mixed into the randomized slot pool so they don't appear every load.

**Content:** Markdown articles live in `content/legal-articles/`. The article catalog is built by globbing `*.md` files from the directory defined in `ARTICLE_DIRECTORY`. Slugs are derived from filenames. A basic `markdownToHtml()` handles headings and paragraphs (no full Markdown parser).

**Routing:** `.htaccess` rewrites all non-file/non-directory requests to `index.php`. Legacy endpoints `alco.php` and `elco.php` redirect to `/`.

## Deployment

- Requires PHP 8+ and Apache with mod_rewrite
- Both domains point to the same codebase; gate is determined by hostname
- No build step, no dependencies, no package manager

## Git Workflow (from copilot-instructions)

- Remote: `https://github.com/ourcaldo/uplink.git`
- Commit and push after every edit
- Before each push, delete the old `uplink.zip`, create a fresh zip of current source, and include it in the commit
- Keep commits small and focused with clear messages
