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

function fetchRandomArticle(): array
{
    $defaultArticles = [
        [
            'title' => 'How URL Redirection Works',
            'content' => "URL redirection sends visitors from one URL to another URL.\n\nA server can redirect with status codes such as 301, 302, and 307, each with slightly different browser behavior.\n\nGood redirect chains should be short to reduce latency and improve user experience.",
        ],
        [
            'title' => 'HTTP Status Codes: 301, 302, and 307',
            'content' => "A 301 status usually means permanent redirect.\n\nA 302 is commonly used for temporary movement.\n\nA 307 keeps the method and body unchanged during redirect, which can matter for POST requests.",
        ],
    ];

    $files = glob(ARTICLE_DIRECTORY . '/*.md');
    if (!is_array($files) || count($files) === 0) {
        return $defaultArticles[array_rand($defaultArticles)];
    }

    $filePath = $files[array_rand($files)];
    $raw = @file_get_contents($filePath);
    if ($raw === false || trim($raw) === '') {
        return $defaultArticles[array_rand($defaultArticles)];
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

    return [
        'title' => $title,
        'content' => $content,
    ];
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
