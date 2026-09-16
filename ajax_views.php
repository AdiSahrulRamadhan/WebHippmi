<?php
require_once __DIR__ . '/koneksi.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'Method Not Allowed']); exit; }
$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }
$key = 'viewed_' . $id;
$now = time();
$last = (int) ($_SESSION[$key] ?? 0);
if ($last > 0 && ($now - $last) < 60) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT `views` FROM `berita` WHERE `id` = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $new = (int) $stmt->fetchColumn();
    echo json_encode(['ok'=>true,'views'=>$new,'cached'=>true]);
    exit;
}
$_SESSION[$key] = $now;
$pdo = getDBConnection();
$chk = $pdo->prepare("SELECT `id` FROM `berita` WHERE `id`=:id LIMIT 1");
$chk->execute(['id'=>$id]);
if (!$chk->fetch()) { http_response_code(404); echo json_encode(['ok'=>false]); exit; }
$pdo->prepare("UPDATE `berita` SET `views` = `views` + 1 WHERE `id` = :id")->execute(['id' => $id]);
$stmt = $pdo->prepare("SELECT `views` FROM `berita` WHERE `id` = :id LIMIT 1");
$stmt->execute(['id' => $id]);
$new = (int) $stmt->fetchColumn();
echo json_encode(['ok' => true, 'views' => $new]);
