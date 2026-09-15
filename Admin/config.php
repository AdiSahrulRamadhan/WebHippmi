<?php
date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_name('HIPPMI_ADMIN_SESSION');
    session_start();
}

const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD = 'Admin123!';

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
        if ($u === ADMIN_USERNAME) return true;
    } catch (Throwable $e) {
        return $_SESSION['admin_username'] === ADMIN_USERNAME;
    }
    return false;
}

function requireAdminLogin(): void
{
    if (!isAdminLoggedIn()) {
        redirectTo('login.php');
    }
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
