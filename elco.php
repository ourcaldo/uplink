<?php

declare(strict_types=1);

require __DIR__ . '/lib/config.php';
require __DIR__ . '/lib/functions.php';

enforceTrafficFilter();

$article = fetchRandomArticle();
$adSlots = pickAdSlots($adsByGate['elco']);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UPlink Gate 2</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <main class="page-wrap">
        <header class="hero">
            <p class="badge">elco.camarjaya.co.id</p>
            <h1>Gate 2</h1>
            <p>Final step before redirection.</p>
        </header>

        <section class="ad-slot ad-top">
            <?php echo $adSlots['top']; ?>
        </section>

        <article class="article-card">
            <h2><?php echo safeText($article['title']); ?></h2>
            <div class="article-content"><?php echo nl2br(safeText($article['content'])); ?></div>
            <a class="article-link" href="<?php echo safeText($article['url']); ?>" target="_blank" rel="noopener noreferrer">Open original source</a>
        </article>

        <section class="ad-slot ad-middle">
            <?php echo $adSlots['middle']; ?>
        </section>

        <section class="actions">
            <a class="primary-btn" href="/redirect.php">Go to Destination</a>
        </section>

        <section class="ad-slot ad-bottom">
            <?php echo $adSlots['bottom']; ?>
        </section>
    </main>
</body>
</html>
