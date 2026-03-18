<?php

declare(strict_types=1);

$host = strtolower($_SERVER['HTTP_HOST'] ?? '');

if (strpos($host, 'elco.camarjaya.co.id') !== false) {
    require __DIR__ . '/elco.php';
    exit;
}

require __DIR__ . '/alco.php';
