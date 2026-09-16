<?php
date_default_timezone_set('Asia/Jakarta');
if (!function_exists('loadEnvEnvAdmin')) { function loadEnvEnvAdmin(string $pth): void { if(!file_exists($pth)) return; foreach(file($pth, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $ln){ $ln=trim($ln); if($ln===''||$ln[0]==='#') continue; if(strpos($ln,'=')===false) continue; [$kk,$vv]=explode('=',$ln,2); $kk=trim($kk); $vv=trim($vv); if($kk!==''&&getenv($kk)===false&&!isset($_ENV[$kk])){ putenv("$kk=$vv"); $_ENV[$kk]=$vv; } } } loadEnvEnvAdmin(__DIR__.'/../.env'); }

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $cookieParams = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($cookieParams);
    } else {
        session_set_cookie_params(0, $cookieParams['path'] . '; samesite=' . $cookieParams['samesite'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
    }
    session_name('HIPPMI_ADMIN_SESSION');
    session_start();
    if (!isset($_SESSION['__created_at'])) $_SESSION['__created_at'] = time();
}

if (!defined('ADMIN_USERNAME')) define('ADMIN_USERNAME', getenv('ADMIN_USERNAME') ?: ($_ENV['ADMIN_USERNAME'] ?? 'admin'));
if (!defined('ADMIN_PASSWORD')) define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: ($_ENV['ADMIN_PASSWORD'] ?? 'Admin123!'));

function redirectTo(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function getAdminPDO(): ?PDO
{
    try {
        if (file_exists(__DIR__ . '/../koneksi.php')) {
            require_once __DIR__ . '/../koneksi.php';
            if (function_exists('getDBConnection')) return getDBConnection();
        }
    } catch (Throwable $e) {}
    return null;
}

function isAdminLoggedIn(): bool
{
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) return false;
    if (empty($_SESSION['admin_username'])) return false;
    $pdo = getAdminPDO();
    if ($pdo === null) {
        return $_SESSION['admin_username'] === ADMIN_USERNAME;
    }
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_users` (`id` INT AUTO_INCREMENT PRIMARY KEY, `username` VARCHAR(80) NOT NULL, `password_hash` VARCHAR(255) NOT NULL, `role` VARCHAR(30) NOT NULL DEFAULT 'admin', `last_login_at` DATETIME NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY `uniq_username` (`username`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $u = (string)$_SESSION['admin_username'];
        $id = (int)($_SESSION['admin_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("SELECT id FROM `admin_users` WHERE `id`=:id LIMIT 1");
            $stmt->execute(['id'=>$id]);
            if ($stmt->fetch()) return true;
        }
        $stmt2 = $pdo->prepare("SELECT id FROM `admin_users` WHERE `username`=:u LIMIT 1");
        $stmt2->execute(['u'=>$u]);
        if ($stmt2->fetch()) return true;
        $cntChk = (int)$pdo->query("SELECT COUNT(*) FROM `admin_users`")->fetchColumn();
        if ($cntChk === 0 && $u === ADMIN_USERNAME) return true;
    } catch (Throwable $e) {
        $cnt2 = 0; try { $cnt2 = (int)$pdo->query("SELECT COUNT(*) FROM `admin_users`")->fetchColumn(); } catch(Throwable $ee){}
        if ($cnt2 === 0 && ($_SESSION['admin_username'] ?? '') === ADMIN_USERNAME) return true;
        return false;
    }
    return false;
}

function checkIdleTimeout(): void
{
    $maxIdle = 1800;
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        $last = (int) ($_SESSION['__last_active'] ?? $_SESSION['admin_login_time'] ?? time());
        if (time() - $last > $maxIdle) {
            $_SESSION = [];
            if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
            redirectTo('login.php?timeout=1');
        }
        $_SESSION['__last_active'] = time();
    }
}

function requireAdminLogin(): void
{
    checkIdleTimeout();
    if (!isAdminLoggedIn()) {
        redirectTo('login.php');
    }
}

function checkLoginRateLimit(string $key): ?string
{
    $max = 5; $window = 900;
    $now = time();
    $data = $_SESSION['__login_attempts'][$key] ?? ['count' => 0, 'first' => $now];
    if ($now - (int)$data['first'] > $window) $data = ['count' => 0, 'first' => $now];
    if ((int)$data['count'] >= $max) {
        $remain = $window - ($now - (int)$data['first']);
        return 'Terlalu banyak percobaan. Coba lagi dalam ' . ceil($remain / 60) . ' menit.';
    }
    return null;
}

function registerLoginAttempt(string $key, bool $success): void
{
    $now = time(); $window = 900;
    if (!isset($_SESSION['__login_attempts'][$key])) $_SESSION['__login_attempts'][$key] = ['count' => 0, 'first' => $now];
    $d = &$_SESSION['__login_attempts'][$key];
    if ($now - (int)$d['first'] > $window) $d = ['count' => 0, 'first' => $now];
    if ($success) $d = ['count' => 0, 'first' => $now];
    else $d['count'] = (int)$d['count'] + 1;
}

function currentAdminRole(): string
{
    return (string)($_SESSION['admin_role'] ?? 'admin');
}

function isSuperAdmin(): bool
{
    $r = strtolower(currentAdminRole());
    return $r === 'super_admin' || $r === 'superadmin';
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . escape(csrfToken()) . '">';
}

function verifyCsrf(): bool
{
    $tok = (string) ($_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '');
    $sess = (string) ($_SESSION['csrf_token'] ?? '');
    if ($tok === '' || $sess === '') return false;
    return hash_equals($sess, $tok);
}

function requireCsrf(): void
{
    if (!verifyCsrf()) {
        http_response_code(403);
        exit('CSRF token tidak valid.');
    }
}

function sanitizeKonten(string $html): string
{
    $html = (string) $html;
    $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html);
    $html = preg_replace('#<iframe\b[^>]*>.*?</iframe>#is', '', $html);
    $html = preg_replace('#<object\b[^>]*>.*?</object>#is', '', $html);
    $html = preg_replace('#<embed\b[^>]*>.*?</embed>#is', '', $html);
    $html = preg_replace('#<form\b[^>]*>.*?</form>#is', '', $html);
    $html = preg_replace('#\son\w+\s*=\s*(["\']).*?\1#is', '', $html);
    $html = preg_replace('#\son\w+\s*=\s*[^"\'\s>]+#i', '', $html);
    $html = preg_replace('#href\s*=\s*(["\'])\s*javascript:.*?\1#is', '', $html);
    $html = preg_replace('#href\s*=\s*javascript:[^\s>]+#i', '', $html);
    $html = preg_replace('#src\s*=\s*(["\'])\s*javascript:.*?\1#is', '', $html);
    return $html;
}
