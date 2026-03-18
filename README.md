# UPlink PHP Gate Flow

This project implements a looping two-step gate flow:

1. Gate 1 at alco.camarjaya.co.id
2. Gate 2 at elco.camarjaya.co.id
3. Loop back to gate 1 and continue looping

## Files

- index.php: Host-based + slug-based renderer (domain.com/{slug})
- alco.php: Legacy endpoint redirecting to /
- elco.php: Legacy endpoint redirecting to /
- lib/config.php: Gate URLs and ad inventories
- lib/functions.php: Traffic filter, article catalog/slug helpers, random ad slot selection
- content/articles/: Local markdown article library (10 randomizable articles)
- .htaccess: Apache rewrite rules for slug routing
- styles.css: Shared styling

## How Randomization Works

- Article: loaded from local markdown files in content/articles.
- URL format: each article is shown on /{slug}.
- Loop behavior: gate 1 -> gate 2 -> gate 1, with the next slug randomized and different from the current article.
- Ads: each page uses mandatory left and right sidebar ads (vertical when available).
- Non-Adsense limit: maximum 5 gate-specific ads per page (2 sidebars + up to 3 content-slot ads).
- Small banner stacking: when a selected banner is small, an additional ad can be stacked in the same slot if capacity remains.
- Global Adsense: rendered separately and not counted in the 5-ad non-Adsense limit.
- Non-display ads: gate-specific ad codes without iframe format are also rendered on each page (outside the display-banner cap).

## Notes About Traffic Filtering

The built-in filter blocks common automated user agents and obvious scripted requests.
No simple PHP-only solution can guarantee complete blocking of all bots/data-center traffic.
For stronger filtering, place Cloudflare Bot Management or an equivalent WAF in front of this app.

## Deployment

1. Point both domains to this codebase.
2. Ensure PHP 8+ is enabled.
3. Set your vhost document root to this folder so index.php handles host + slug routing.
4. If using Apache, enable mod_rewrite (uses .htaccess).
