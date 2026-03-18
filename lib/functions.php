<?php

declare(strict_types=1);

function getClientIp(): string
{
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
        $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? '',
    ];

    foreach ($candidates as $candidate) {
        if ($candidate === '') {
            continue;
        }

        $parts = explode(',', $candidate);
        foreach ($parts as $part) {
            $ip = trim($part);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

function isLikelyAutomatedTraffic(): bool
{
    $userAgent = strtolower(trim($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if ($userAgent === '') {
        return true;
    }

    $botPatterns = [
        'bot', 'crawler', 'spider', 'slurp', 'preview', 'httpclient', 'curl',
        'wget', 'python-requests', 'headless', 'lighthouse', 'monitor', 'validator',
    ];

    foreach ($botPatterns as $pattern) {
        if (strpos($userAgent, $pattern) !== false) {
            return true;
        }
    }

    if (isset($_SERVER['HTTP_SEC_CH_UA_PLATFORM']) && stripos((string) $_SERVER['HTTP_SEC_CH_UA_PLATFORM'], 'linux') !== false) {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) || trim((string) $_SERVER['HTTP_ACCEPT_LANGUAGE']) === '') {
            return true;
        }
    }

    return false;
}

function enforceTrafficFilter(): void
{
    if (!isLikelyAutomatedTraffic()) {
        return;
    }

    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Access denied</title></head><body><h1>Access denied</h1><p>Automated traffic is not allowed.</p></body></html>';
    exit;
}

function parseArticleFromFile(string $filePath): ?array
{
    $raw = @file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') {
        return null;
    }

    $raw = trim(str_replace(["\r\n", "\r"], "\n", $raw));
    $lines = explode("\n", $raw);
    $title = '';

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '') {
            continue;
        }

        if (strpos($trimmed, '# ') === 0) {
            $title = trim(substr($trimmed, 2));
            break;
        }

        $title = $trimmed;
        break;
    }

    if ($title === '') {
        $title = pathinfo($filePath, PATHINFO_FILENAME);
    }

    // Prevent duplicated title in UI: the page already renders article title above body.
    $content = preg_replace('/^\s*#\s+.+\n?/', '', $raw, 1);
    $content = is_string($content) ? trim($content) : $raw;
    if ($content === '') {
        $content = $raw;
    }

    $slug = strtolower((string) pathinfo($filePath, PATHINFO_FILENAME));
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', (string) $slug);
    $slug = trim((string) $slug, '-');
    if ($slug === '') {
        return null;
    }

    return [
        'slug' => $slug,
        'title' => $title,
        'content' => $content,
    ];
}

function getArticleCatalog(): array
{
    $files = glob(ARTICLE_DIRECTORY . '/*.md');
    if (!is_array($files) || count($files) === 0) {
        return [];
    }

    $catalog = [];
    foreach ($files as $filePath) {
        $article = parseArticleFromFile($filePath);
        if (!is_array($article)) {
            continue;
        }

        $catalog[$article['slug']] = $article;
    }

    return $catalog;
}

function getArticleBySlug(array $catalog, string $slug): ?array
{
    $normalized = strtolower(trim($slug));
    if ($normalized === '' || !isset($catalog[$normalized])) {
        return null;
    }

    return $catalog[$normalized];
}

function pickRandomArticle(array $catalog, array $excludeSlugs = []): ?array
{
    if (count($catalog) === 0) {
        return null;
    }

    $exclude = array_fill_keys(array_map('strtolower', $excludeSlugs), true);
    $pool = [];
    foreach ($catalog as $slug => $article) {
        if (!isset($exclude[strtolower((string) $slug)])) {
            $pool[] = $article;
        }
    }

    if (count($pool) === 0) {
        $pool = array_values($catalog);
    }

    return $pool[array_rand($pool)] ?? null;
}

function pickAdSlots(array $adsPool): array
{
    if (count($adsPool) === 0) {
        return ['top' => '', 'middle' => '', 'bottom' => ''];
    }

    $pool = $adsPool;
    shuffle($pool);

    $top = $pool[0] ?? $adsPool[array_rand($adsPool)];
    $middle = $pool[1] ?? $adsPool[array_rand($adsPool)];
    $bottom = $pool[2] ?? $adsPool[array_rand($adsPool)];

    return [
        'top' => $top,
        'middle' => $middle,
        'bottom' => $bottom,
    ];
}

function isAdsenseAd(string $adCode): bool
{
    return strpos($adCode, 'googlesyndication.com/pagead/js/adsbygoogle.js') !== false
        || strpos($adCode, 'class="adsbygoogle"') !== false;
}

function getAdDimensions(string $adCode): array
{
    $width = null;
    $height = null;

    if (preg_match('/"width"\s*:\s*(\d+)/', $adCode, $widthMatch) === 1) {
        $width = (int) $widthMatch[1];
    }

    if (preg_match('/"height"\s*:\s*(\d+)/', $adCode, $heightMatch) === 1) {
        $height = (int) $heightMatch[1];
    }

    return ['width' => $width, 'height' => $height];
}

function isVerticalAd(string $adCode): bool
{
    $dims = getAdDimensions($adCode);
    if (!is_int($dims['width']) || !is_int($dims['height'])) {
        return false;
    }

    return $dims['width'] <= 200 && $dims['height'] >= 250;
}

function isSmallBannerAd(string $adCode): bool
{
    $dims = getAdDimensions($adCode);
    if (!is_int($dims['width']) || !is_int($dims['height'])) {
        return false;
    }

    $area = $dims['width'] * $dims['height'];
    return $dims['height'] <= 90 || $dims['width'] <= 320 || $area <= 45000;
}

function isIframeFormatAd(string $adCode): bool
{
    return strpos($adCode, '"format":"iframe"') !== false
        || strpos($adCode, "'format' : 'iframe'") !== false
        || strpos($adCode, '"format" : "iframe"') !== false;
}

function buildPageAds(array $gateAdsPool, array $adsensePool): array
{
    $nonAdsense = array_values(array_filter($gateAdsPool, static function ($adCode): bool {
        return !isAdsenseAd((string) $adCode);
    }));

    $nonDisplayAds = [];
    $displayAds = [];
    foreach ($nonAdsense as $adCode) {
        if (isIframeFormatAd((string) $adCode)) {
            $displayAds[] = $adCode;
            continue;
        }

        $nonDisplayAds[] = $adCode;
    }

    $vertical = [];
    $content = [];

    foreach ($displayAds as $adCode) {
        if (isVerticalAd($adCode)) {
            $vertical[] = $adCode;
            continue;
        }

        $content[] = $adCode;
    }

    shuffle($vertical);
    shuffle($content);

    $used = [];
    $maxNonAdsense = 5;
    $usedCount = 0;

    $leftSidebar = '';
    $rightSidebar = '';

    $nextSidebar = static function () use (&$vertical, &$content, &$used): string {
        while (count($vertical) > 0) {
            $candidate = array_shift($vertical);
            if (!isset($used[$candidate])) {
                return $candidate;
            }
        }

        while (count($content) > 0) {
            $candidate = array_shift($content);
            if (!isset($used[$candidate])) {
                return $candidate;
            }
        }

        return '';
    };

    $leftSidebar = $nextSidebar();
    if ($leftSidebar !== '') {
        $used[$leftSidebar] = true;
        $usedCount++;
    }

    $rightSidebar = $nextSidebar();
    if ($rightSidebar !== '') {
        $used[$rightSidebar] = true;
        $usedCount++;
    }

    $contentPool = [];
    foreach (array_merge($content, $vertical) as $adCode) {
        if (!isset($used[$adCode])) {
            $contentPool[] = $adCode;
        }
    }

    $nextContentAd = static function () use (&$contentPool, &$used): ?string {
        while (count($contentPool) > 0) {
            $candidate = array_shift($contentPool);
            if (!isset($used[$candidate])) {
                return $candidate;
            }
        }

        return null;
    };

    $slots = [
        'top' => [],
        'middle' => [],
        'bottom' => [],
    ];

    foreach (['top', 'middle', 'bottom'] as $slotName) {
        if ($usedCount >= $maxNonAdsense) {
            break;
        }

        $primary = $nextContentAd();
        if ($primary === null) {
            continue;
        }

        $slots[$slotName][] = $primary;
        $used[$primary] = true;
        $usedCount++;

        if (isSmallBannerAd($primary) && $usedCount < $maxNonAdsense) {
            $secondary = $nextContentAd();
            if ($secondary !== null) {
                $slots[$slotName][] = $secondary;
                $used[$secondary] = true;
                $usedCount++;
            }
        }
    }

    $adsenseShuffled = $adsensePool;
    shuffle($adsenseShuffled);
    $adsenseSlots = [
        'top' => $adsenseShuffled[0] ?? '',
        'middle' => $adsenseShuffled[1] ?? ($adsenseShuffled[0] ?? ''),
        'bottom' => $adsenseShuffled[2] ?? ($adsenseShuffled[0] ?? ''),
    ];

    return [
        'nonDisplay' => $nonDisplayAds,
        'leftSidebar' => $leftSidebar,
        'rightSidebar' => $rightSidebar,
        'slots' => $slots,
        'adsense' => $adsenseSlots,
    ];
}

function safeText(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function markdownToHtml(string $markdown): string
{
    $normalized = str_replace(["\r\n", "\r"], "\n", trim($markdown));
    if ($normalized === '') {
        return '';
    }

    $blocks = preg_split('/\n\s*\n/', $normalized) ?: [];
    $html = [];

    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }

        $escaped = safeText($block);

        if (strpos($block, '### ') === 0) {
            $html[] = '<h3>' . safeText(trim(substr($block, 4))) . '</h3>';
            continue;
        }

        if (strpos($block, '## ') === 0) {
            $html[] = '<h2>' . safeText(trim(substr($block, 3))) . '</h2>';
            continue;
        }

        if (strpos($block, '# ') === 0) {
            $html[] = '<h1>' . safeText(trim(substr($block, 2))) . '</h1>';
            continue;
        }

        $html[] = '<p>' . nl2br($escaped) . '</p>';
    }

    return implode("\n", $html);
}
