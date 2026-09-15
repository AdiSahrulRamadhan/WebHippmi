<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

if (isAdminLoggedIn()) {
    redirectTo('dashboard.php');
}

if (
    isset($_GET['logout'])
    && $_GET['logout'] === 'success'
) {
    redirectTo('login.php?logout=success');
}

redirectTo('login.php');