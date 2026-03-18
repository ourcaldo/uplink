# UPlink PHP Gate Flow

This project implements a two-step gate flow:

1. Gate 1 at alco.camarjaya.co.id
2. Gate 2 at elco.camarjaya.co.id
3. Final redirect to a hardcoded destination URL

## Files

- index.php: Host-based router (alco host -> gate 1, elco host -> gate 2)
- alco.php: Gate 1 page
- elco.php: Gate 2 page
- redirect.php: Final redirect endpoint
- lib/config.php: Gate URLs, destination URL, and ad inventories
- lib/functions.php: Traffic filter, random article fetch, random ad slot selection
- styles.css: Shared styling

## How Randomization Works

- Article: fetched from Spaceflight News API and selected randomly.
- Ads: each page selects random snippets from that gate's ad pool for top, middle, and bottom slots.

## Notes About Traffic Filtering

The built-in filter blocks common automated user agents and obvious scripted requests.
No simple PHP-only solution can guarantee complete blocking of all bots/data-center traffic.
For stronger filtering, place Cloudflare Bot Management or an equivalent WAF in front of this app.

## Deployment

1. Point both domains to this codebase.
2. Ensure PHP 8+ is enabled.
3. Update FINAL_DESTINATION_URL in lib/config.php.
4. Set your vhost document root to this folder so index.php handles host routing.
