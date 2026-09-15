<?php
require_once __DIR__ . '/koneksi.php';
$pdo = getDBConnection();
$id = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
if ($id > 0) {
    $pdo->prepare("UPDATE `berita` SET `views` = `views` + 1 WHERE `id` = :id")->execute(['id' => $id]);
    $stmt = $pdo->prepare("SELECT `views` FROM `berita` WHERE `id` = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $new = (int) $stmt->fetchColumn();
    header('Content-Type: application/json');
    echo json_encode(['ok' => true, 'views' => $new]);
    exit;
}
http_response_code(400);
header('Content-Type: application/json');
echo json_encode(['ok' => false]);
