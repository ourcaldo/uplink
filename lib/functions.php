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
            'summary' => 'A quick overview of redirect flows, latency, and reliability basics.',
            'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Redirections',
        ],
        [
            'title' => 'HTTP Status Codes: 301, 302, and 307',
            'summary' => 'Learn how each redirect code behaves and where to use them safely.',
            'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status',
        ],
    ];

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Accept: application/json\r\nUser-Agent: UPlink/1.0\r\n",
            'timeout' => 5,
        ],
    ]);

    $response = @file_get_contents(ARTICLE_API_URL, false, $context);
    if ($response === false) {
        return $defaultArticles[array_rand($defaultArticles)];
    }

    $data = json_decode($response, true);
    if (!is_array($data) || !isset($data['results']) || !is_array($data['results']) || count($data['results']) === 0) {
        return $defaultArticles[array_rand($defaultArticles)];
    }

    $item = $data['results'][array_rand($data['results'])];

    return [
        'title' => (string) ($item['title'] ?? 'Featured Article'),
        'summary' => (string) ($item['summary'] ?? 'Open the article for full details.'),
        'url' => (string) ($item['url'] ?? 'https://example.com'),
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
