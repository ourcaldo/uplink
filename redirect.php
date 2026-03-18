<?php

declare(strict_types=1);

require __DIR__ . '/lib/config.php';

header('Location: ' . FINAL_DESTINATION_URL, true, 302);
exit;
