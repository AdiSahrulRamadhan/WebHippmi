<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/*
|--------------------------------------------------------------------------
| Hapus seluruh session
|--------------------------------------------------------------------------
*/
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $cookieParameters = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $cookieParameters['path'] ?: '/',
        $cookieParameters['domain'] ?? '',
        (bool) ($cookieParameters['secure'] ?? false),
        (bool) ($cookieParameters['httponly'] ?? true)
    );
}

session_unset();
session_destroy();

/*
|--------------------------------------------------------------------------
| Cegah halaman dashboard muncul dari cache
|--------------------------------------------------------------------------
*/
header(
    'Cache-Control: no-store, no-cache, must-revalidate, max-age=0'
);

header('Pragma: no-cache');
header('Expires: 0');

/*
|--------------------------------------------------------------------------
| Kembali ke halaman login
|--------------------------------------------------------------------------
*/
header('Location: login.php?logout=success');
exit;