<?php

declare(strict_types=1);

require __DIR__ . '/lib/config.php';
require __DIR__ . '/lib/functions.php';

enforceTrafficFilter();

$host = strtolower($_SERVER['HTTP_HOST'] ?? '');
$gate = (strpos($host, 'elco.camarjaya.co.id') !== false) ? 'elco' : 'alco';
$nextGateUrl = ($gate === 'alco') ? GATE_TWO_URL : GATE_ONE_URL;
$nextGateLabel = ($gate === 'alco') ? 'Continue to Gate 2' : 'Continue to Gate 1';

$catalog = getArticleCatalog();
if (count($catalog) === 0) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No article files found in content/articles.';
    exit;
}

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$requestPath = is_string($requestPath) ? trim($requestPath, '/') : '';
$requestedSlug = strtolower(rawurldecode($requestPath));
$requestedSlug = trim($requestedSlug);

$previousSlug = strtolower(trim((string) ($_GET['prev'] ?? '')));

$article = null;
if ($requestedSlug !== '') {
    $article = getArticleBySlug($catalog, $requestedSlug);
}

if (!is_array($article)) {
    $random = pickRandomArticle($catalog, [$previousSlug]);
    if (!is_array($random)) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Unable to pick random article.';
        exit;
    }

    header('Location: /' . rawurlencode($random['slug']), true, 302);
    exit;
}

$nextArticle = pickRandomArticle($catalog, [$article['slug'], $previousSlug]);
if (!is_array($nextArticle)) {
    $nextArticle = $article;
}

$nextUrl = rtrim($nextGateUrl, '/') . '/' . rawurlencode($nextArticle['slug']) . '?prev=' . rawurlencode((string) $article['slug']);

$pageAds = buildPageAds($adsByGate[$gate], $adsenseAds);
$gateTitle = ($gate === 'alco') ? 'Gate 1' : 'Gate 2';
$pageTitle = (string) $article['title'] . ' | ' . $gateTitle;

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo safeText($pageTitle); ?></title>
    <meta name="title" content="<?php echo safeText($pageTitle); ?>">
    <meta property="og:title" content="<?php echo safeText($pageTitle); ?>">
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <main class="page-wrap">
        <header class="hero">
            <p class="badge"><?php echo safeText($host); ?></p>
            <h1><?php echo safeText($gateTitle); ?></h1>
            <p>Article path: /<?php echo safeText((string) $article['slug']); ?></p>
        </header>

        <?php if (count($pageAds['nonDisplay']) > 0): ?>
            <section class="non-display-ads">
                <?php foreach ($pageAds['nonDisplay'] as $adCode): ?>
                    <div class="non-display-ad"><?php echo $adCode; ?></div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <div class="content-layout">
            <aside class="side-rail side-left">
                <?php if ($pageAds['leftSidebar'] !== ''): ?>
                    <div class="ad-unit"><?php echo $pageAds['leftSidebar']; ?></div>
                <?php endif; ?>
            </aside>

            <section class="main-content">
                <section class="ad-slot ad-top">
                    <?php foreach ($pageAds['slots']['top'] as $adCode): ?>
                        <div class="ad-unit"><?php echo $adCode; ?></div>
                    <?php endforeach; ?>
                    <?php if ($pageAds['adsense']['top'] !== ''): ?>
                        <div class="ad-unit adsense-unit"><?php echo $pageAds['adsense']['top']; ?></div>
                    <?php endif; ?>
                </section>

                <article class="article-card">
                    <h2><?php echo safeText((string) $article['title']); ?></h2>
                    <div class="article-content markdown-body"><?php echo markdownToHtml((string) $article['content']); ?></div>
                </article>

                <section class="ad-slot ad-middle">
                    <?php foreach ($pageAds['slots']['middle'] as $adCode): ?>
                        <div class="ad-unit"><?php echo $adCode; ?></div>
                    <?php endforeach; ?>
                    <?php if ($pageAds['adsense']['middle'] !== ''): ?>
                        <div class="ad-unit adsense-unit"><?php echo $pageAds['adsense']['middle']; ?></div>
                    <?php endif; ?>
                </section>

                <section class="actions">
                    <a class="primary-btn" href="<?php echo safeText($nextUrl); ?>"><?php echo safeText($nextGateLabel); ?></a>
                    <?php if (($pageAds['smartlink'] ?? '') !== ''): ?>
                        <div class="inline-smartlink"><?php echo $pageAds['smartlink']; ?></div>
                    <?php endif; ?>
                </section>

                <section class="ad-slot ad-bottom">
                    <?php foreach ($pageAds['slots']['bottom'] as $adCode): ?>
                        <div class="ad-unit"><?php echo $adCode; ?></div>
                    <?php endforeach; ?>
                    <?php if ($pageAds['adsense']['bottom'] !== ''): ?>
                        <div class="ad-unit adsense-unit"><?php echo $pageAds['adsense']['bottom']; ?></div>
                    <?php endif; ?>
                </section>
            </section>

            <aside class="side-rail side-right">
                <?php if ($pageAds['rightSidebar'] !== ''): ?>
                    <div class="ad-unit"><?php echo $pageAds['rightSidebar']; ?></div>
                <?php endif; ?>
            </aside>
        </div>
    </main>
</body>
</html>
