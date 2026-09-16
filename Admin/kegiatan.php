<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
requireAdminLogin();
require_once __DIR__ . '/../koneksi.php';

$pdo = getDBConnection();

// Cache-control
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$adminUsername = (string) ($_SESSION['admin_username'] ?? ADMIN_USERNAME);
$adminInitial = strtoupper(substr($adminUsername, 0, 1));
$adminRole = (string) ($_SESSION['admin_role'] ?? 'admin');

$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error'] ?? '';
$featuredLimitPopup = (int) ($_SESSION['featured_limit_popup'] ?? 0);
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['featured_limit_popup']);

/*
|--------------------------------------------------------------------------
| PROSES HAPUS BERITA
|--------------------------------------------------------------------------
*/
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    requireCsrf();
    $stmt = $pdo->prepare("SELECT * FROM `berita` WHERE `id` = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $item = $stmt->fetch();

    if ($item) {
        if (!empty($item['gambar']) && str_starts_with($item['gambar'], 'uploads/berita/')) {
            $filePath = __DIR__ . '/../' . $item['gambar'];
            if (file_exists($filePath)) @unlink($filePath);
        }
        if (!empty($item['penulis_avatar']) && str_starts_with($item['penulis_avatar'], 'uploads/penulis/')) {
            $avPath = __DIR__ . '/../' . $item['penulis_avatar'];
            if (file_exists($avPath)) @unlink($avPath);
        }
        $delStmt = $pdo->prepare("DELETE FROM `berita` WHERE `id` = :id");
        $delStmt->execute(['id' => $id]);

        $_SESSION['flash_success'] = 'Kegiatan "' . $item['judul'] . '" berhasil dihapus.';
    } else {
        $_SESSION['flash_error'] = 'Kegiatan tidak ditemukan.';
    }

    redirectTo('kegiatan.php');
}

if ($action === 'bulk_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) $ids = [$ids];
    $ids = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
    if (empty($ids)) {
        $_SESSION['flash_error'] = 'Tidak ada data yang dipilih.';
        redirectTo('kegiatan.php');
    }
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmtSel = $pdo->prepare("SELECT `gambar`,`penulis_avatar` FROM `berita` WHERE `id` IN ($placeholders)");
    $stmtSel->execute($ids);
    foreach ($stmtSel->fetchAll() as $r) {
        if (!empty($r['gambar']) && str_starts_with($r['gambar'], 'uploads/berita/')) {
            $fp = __DIR__ . '/../' . $r['gambar'];
            if (file_exists($fp)) @unlink($fp);
        }
        if (!empty($r['penulis_avatar']) && str_starts_with($r['penulis_avatar'], 'uploads/penulis/')) {
            $ap = __DIR__ . '/../' . $r['penulis_avatar'];
            if (file_exists($ap)) @unlink($ap);
        }
    }
    $stmtDel = $pdo->prepare("DELETE FROM `berita` WHERE `id` IN ($placeholders)");
    $stmtDel->execute($ids);
    $_SESSION['flash_success'] = count($ids) . ' kegiatan berhasil dihapus.';
    redirectTo('kegiatan.php');
}

if ($action === 'delete_kategori' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $katDel = trim((string) ($_GET['kategori'] ?? ''));
    $kategoriDefaultDel = ['Pendidikan', 'Workshop', 'Seminar', 'Kolaborasi', 'Advokasi', 'Pengumuman'];
    if ($katDel === '' || in_array($katDel, $kategoriDefaultDel, true)) {
        $_SESSION['flash_error'] = 'Kategori bawaan tidak dapat dihapus.';
    } else {
        $cek = $pdo->prepare("SELECT COUNT(*) FROM `berita` WHERE `kategori` = :kat");
        $cek->execute(['kat' => $katDel]);
        $jml = (int) $cek->fetchColumn();
        if ($jml > 0) {
            $updKat = $pdo->prepare("UPDATE `berita` SET `kategori` = 'Pendidikan' WHERE `kategori` = :kat");
            $updKat->execute(['kat' => $katDel]);
            $_SESSION['flash_success'] = 'Kategori "' . $katDel . '" dihapus. ' . $jml . ' kegiatan dipindahkan ke Pendidikan.';
        } else {
            $_SESSION['flash_success'] = 'Kategori "' . $katDel . '" dihapus.';
        }
    }
    redirectTo('kegiatan.php');
}

/*
|--------------------------------------------------------------------------
| PROSES SIMPAN (TAMBAH / EDIT)
|--------------------------------------------------------------------------
*/
$formErrors = [];
$formData = [
    'judul' => '',
    'kategori' => 'Pendidikan',
    'tipe' => 'berita',
    'penulis' => 'Admin HIPPMI',
    'penulis_avatar' => '',
    'ringkasan' => '',
    'konten' => '',
    'gambar' => '',
    'tanggal' => date('Y-m-d'),
    'link_daftar' => '',
    'status' => 'published',
];
$removeExistingGambar = false;
$removeExistingAvatar = false;

if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM `berita` WHERE `id` = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $existing = $stmt->fetch();

    if (!$existing) {
        $_SESSION['flash_error'] = 'Kegiatan yang akan diedit tidak ditemukan.';
        redirectTo('kegiatan.php');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $formData = $existing;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['create', 'edit'], true)) {
    if (!verifyCsrf()) $formErrors[] = 'Sesi habis / CSRF tidak valid. Silakan refresh halaman dan coba lagi.';
    if (empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) $formErrors[] = 'Data terlalu besar atau gagal terkirim. Coba file lebih kecil.';
    $formData['judul'] = trim((string) ($_POST['judul'] ?? ''));
    $katInput = trim((string) ($_POST['kategori'] ?? ''));
    $katBaru = trim((string) ($_POST['kategori_baru'] ?? ''));
    if ($katInput === '__new__') {
        $formData['kategori'] = $katBaru !== '' ? $katBaru : 'Pendidikan';
    } else {
        $formData['kategori'] = $katInput !== '' ? $katInput : 'Pendidikan';
    }
    $formData['tipe'] = 'kegiatan';
    $formData['penulis'] = trim((string) ($_POST['penulis'] ?? 'Admin HIPPMI'));
    $formData['penulis_avatar'] = trim((string) ($_POST['penulis_avatar'] ?? ''));
    $formData['ringkasan'] = trim((string) ($_POST['ringkasan'] ?? ''));
    $formData['konten'] = sanitizeKonten(trim((string) ($_POST['konten'] ?? '')));
    $formData['tanggal'] = trim((string) ($_POST['tanggal'] ?? date('Y-m-d')));
    $formData['link_daftar'] = trim((string) ($_POST['link_daftar'] ?? ''));
    $formData['status'] = in_array($_POST['status'] ?? '', ['published', 'draft'], true) ? $_POST['status'] : 'published';
    $customUrlGambar = trim((string) ($_POST['gambar_url'] ?? ''));
    $customUrlAvatar = trim((string) ($_POST['penulis_avatar_url'] ?? ''));
    $removeExistingGambar = $action === 'edit' && (($_POST['hapus_gambar'] ?? '0') === '1');
    $removeExistingAvatar = $action === 'edit' && (($_POST['hapus_avatar'] ?? '0') === '1');

    // Validasi
    if ($formData['judul'] === '') {
        $formErrors[] = 'Judul berita wajib diisi.';
    }
    if ($formData['ringkasan'] === '') {
        $formErrors[] = 'Ringkasan singkat berita wajib diisi.';
    }
    if ($formData['konten'] === '') {
        $formErrors[] = 'Isi lengkap berita wajib diisi.';
    }
    if ($formData['tanggal'] === '') {
        $formErrors[] = 'Tanggal berita wajib diisi.';
    }

    $uploadedGambarPath = null;
    if (isset($_FILES['gambar_file']) && $_FILES['gambar_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['gambar_file'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $fileInfo = pathinfo($file['name']);
        $ext = strtolower($fileInfo['extension'] ?? '');
        if (!in_array($ext, $allowedExtensions, true)) {
            $formErrors[] = 'Format file gambar tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.';
        } elseif ($file['size'] > 5 * 1024 * 1024) {
            $formErrors[] = 'Ukuran gambar maksimal 5 MB.';
        } else {
            $mimeOk = true;
            if (function_exists('finfo_open')) {
                $finfoTmp = finfo_open(FILEINFO_MIME_TYPE);
                $mimeTmp = $finfoTmp ? finfo_file($finfoTmp, $file['tmp_name']) : '';
                if ($finfoTmp) finfo_close($finfoTmp);
                if (!in_array($mimeTmp, ['image/jpeg','image/png','image/webp','image/gif'], true)) {
                    $formErrors[] = 'File bukan gambar valid (MIME: '.htmlspecialchars($mimeTmp).').';
                    $mimeOk = false;
                } elseif (@getimagesize($file['tmp_name']) === false) {
                    $formErrors[] = 'File gambar tidak valid.';
                    $mimeOk = false;
                }
            }
            if ($mimeOk) {
                $uploadDir = __DIR__ . '/../uploads/berita/';
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);
                $newFileName = 'berita_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destination = $uploadDir . $newFileName;
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $uploadedGambarPath = 'uploads/berita/' . $newFileName;
                    if ($action === 'edit' && !empty($existing['gambar']) && str_starts_with($existing['gambar'], 'uploads/berita/')) {
                        $oldFilePath = __DIR__ . '/../' . $existing['gambar'];
                        if (file_exists($oldFilePath)) @unlink($oldFilePath);
                    }
                } else {
                    $formErrors[] = 'Gagal mengunggah file gambar ke server.';
                }
            }
        }
    }
    if ($removeExistingGambar) {
        $formData['gambar'] = '';
    } elseif ($uploadedGambarPath !== null) {
        $formData['gambar'] = $uploadedGambarPath;
    } elseif ($customUrlGambar !== '') {
        $formData['gambar'] = $customUrlGambar;
    } elseif ($action === 'edit') {
        $formData['gambar'] = $existing['gambar'];
    } elseif (empty($formData['gambar'])) {
        $formData['gambar'] = 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80';
    }

    $uploadedAvatarPath = null;
    if (isset($_FILES['penulis_avatar_file']) && $_FILES['penulis_avatar_file']['error'] === UPLOAD_ERR_OK) {
        $avFile = $_FILES['penulis_avatar_file'];
        $avExt = strtolower(pathinfo($avFile['name'], PATHINFO_EXTENSION) ?? '');
        if (!in_array($avExt, ['jpg','jpeg','png','webp','gif'], true)) {
            $formErrors[] = 'Format foto penulis tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.';
        } elseif ($avFile['size'] > 2 * 1024 * 1024) {
            $formErrors[] = 'Ukuran foto penulis maksimal 2 MB.';
        } else {
            $mimeAvOk = true;
            if (function_exists('finfo_open')) {
                $fAv = finfo_open(FILEINFO_MIME_TYPE);
                $mimeAv = $fAv ? finfo_file($fAv, $avFile['tmp_name']) : '';
                if ($fAv) finfo_close($fAv);
                if (!in_array($mimeAv, ['image/jpeg','image/png','image/webp','image/gif'], true)) {
                    $formErrors[] = 'Foto penulis bukan gambar valid.';
                    $mimeAvOk = false;
                } elseif (@getimagesize($avFile['tmp_name']) === false) {
                    $formErrors[] = 'Foto penulis tidak valid.';
                    $mimeAvOk = false;
                }
            }
            if ($mimeAvOk) {
                $avDir = __DIR__ . '/../uploads/penulis/';
                if (!is_dir($avDir)) @mkdir($avDir, 0777, true);
                $avName = 'penulis_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $avExt;
                $avDest = $avDir . $avName;
                if (move_uploaded_file($avFile['tmp_name'], $avDest)) {
                    $uploadedAvatarPath = 'uploads/penulis/' . $avName;
                    if ($action === 'edit' && !empty($existing['penulis_avatar']) && str_starts_with($existing['penulis_avatar'], 'uploads/penulis/')) {
                        $oldAv = __DIR__ . '/../' . $existing['penulis_avatar'];
                        if (file_exists($oldAv)) @unlink($oldAv);
                    }
                } else {
                    $formErrors[] = 'Gagal mengunggah foto penulis.';
                }
            }
        }
    }
    if ($uploadedAvatarPath !== null) {
        $formData['penulis_avatar'] = $uploadedAvatarPath;
    } elseif ($customUrlAvatar !== '') {
        $formData['penulis_avatar'] = $customUrlAvatar;
    } elseif ($action === 'edit') {
        $formData['penulis_avatar'] = $existing['penulis_avatar'] ?? '';
    }

    if (empty($formErrors) && $formData['tanggal'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $formData['tanggal'])) {
        $formErrors[] = 'Format tanggal tidak valid (YYYY-MM-DD).';
    }
    if (!empty($formData['link_daftar']) && !filter_var($formData['link_daftar'], FILTER_VALIDATE_URL)) {
        $formErrors[] = 'Link pendaftaran harus URL valid (https://...).';
    }
    if (empty($formErrors)) {
        try {
            $slug = createSlug($formData['judul']);
            $slugCheckQuery = "SELECT id FROM `berita` WHERE `slug` = :slug" . ($action === 'edit' ? " AND `id` != :id" : "") . " LIMIT 1";
            $slugStmt = $pdo->prepare($slugCheckQuery);
            $params = ['slug' => $slug];
            if ($action === 'edit') $params['id'] = $id;
            $slugStmt->execute($params);
            if ($slugStmt->fetch()) $slug .= '-' . time();
            if ($action === 'create') {
                $insStmt = $pdo->prepare("INSERT INTO `berita` (`judul`,`slug`,`kategori`,`tipe`,`penulis`,`penulis_avatar`,`ringkasan`,`konten`,`gambar`,`link_daftar`,`tanggal`,`status`) VALUES (:judul,:slug,:kategori,:tipe,:penulis,:penulis_avatar,:ringkasan,:konten,:gambar,:link_daftar,:tanggal,:status)");
                $insStmt->execute(['judul'=>$formData['judul'],'slug'=>$slug,'kategori'=>$formData['kategori'],'tipe'=>$formData['tipe'],'penulis'=>$formData['penulis'],'penulis_avatar'=>$formData['penulis_avatar'],'ringkasan'=>$formData['ringkasan'],'konten'=>$formData['konten'],'gambar'=>$formData['gambar'],'link_daftar'=>$formData['link_daftar']!==''?$formData['link_daftar']:null,'tanggal'=>$formData['tanggal'],'status'=>$formData['status']]);
                $_SESSION['flash_success'] = 'Kegiatan baru berhasil diterbitkan!';
                redirectTo('kegiatan.php');
            } elseif ($action === 'edit') {
                $updStmt = $pdo->prepare("UPDATE `berita` SET `judul`=:judul,`slug`=:slug,`kategori`=:kategori,`tipe`=:tipe,`penulis`=:penulis,`penulis_avatar`=:penulis_avatar,`ringkasan`=:ringkasan,`konten`=:konten,`gambar`=:gambar,`link_daftar`=:link_daftar,`tanggal`=:tanggal,`status`=:status WHERE `id`=:id");
                $updStmt->execute(['judul'=>$formData['judul'],'slug'=>$slug,'kategori'=>$formData['kategori'],'tipe'=>$formData['tipe'],'penulis'=>$formData['penulis'],'penulis_avatar'=>$formData['penulis_avatar']!==''?$formData['penulis_avatar']:null,'ringkasan'=>$formData['ringkasan'],'konten'=>$formData['konten'],'gambar'=>$formData['gambar']!==''?$formData['gambar']:null,'link_daftar'=>$formData['link_daftar']!==''?$formData['link_daftar']:null,'tanggal'=>$formData['tanggal'],'status'=>$formData['status'],'id'=>$id]);
                $oldGambarK = (string)($existing['gambar'] ?? '');
                if ($removeExistingGambar && $oldGambarK!=='' && str_starts_with($oldGambarK,'uploads/berita/')) { $oldFileK=__DIR__.'/../'.$oldGambarK; if(file_exists($oldFileK)) @unlink($oldFileK); }
                if ($removeExistingAvatar) { $oldAvK=(string)($existing['penulis_avatar']??''); if($oldAvK!=='' && str_starts_with($oldAvK,'uploads/penulis/')){ $oldAvFileK=__DIR__.'/../'.$oldAvK; if(file_exists($oldAvFileK)) @unlink($oldAvFileK); } }
                $_SESSION['flash_success'] = 'Kegiatan berhasil diperbarui!';
                redirectTo('kegiatan.php');
            }
        } catch (Throwable $e) { error_log('kegiatan save fail: '.$e->getMessage()); $formErrors[]='Gagal menyimpan: '.htmlspecialchars($e->getMessage()); if(isset($uploadedGambarPath) && $uploadedGambarPath && file_exists(__DIR__.'/../'.$uploadedGambarPath)) @unlink(__DIR__.'/../'.$uploadedGambarPath); if(isset($uploadedAvatarPath) && $uploadedAvatarPath && file_exists(__DIR__.'/../'.$uploadedAvatarPath)) @unlink(__DIR__.'/../'.$uploadedAvatarPath); }
    }
}

/*
|--------------------------------------------------------------------------
| DATA UNTUK INDEX (TABEL LIST BERITA)
|--------------------------------------------------------------------------
*/
$searchKeyword = trim((string) ($_GET['q'] ?? ''));
$filterKategori = trim((string) ($_GET['kategori'] ?? ''));
$filterStatus = trim((string) ($_GET['status'] ?? ''));

$sql = "SELECT * FROM `berita` WHERE `tipe` = 'kegiatan'";
$sqlParams = [];

if ($searchKeyword !== '') {
    $sql .= " AND (`judul` LIKE :q1 OR `penulis` LIKE :q2 OR `ringkasan` LIKE :q3 OR `konten` LIKE :q4)";
    $sqlParams['q1'] = '%' . $searchKeyword . '%';
    $sqlParams['q2'] = '%' . $searchKeyword . '%';
    $sqlParams['q3'] = '%' . $searchKeyword . '%';
    $sqlParams['q4'] = '%' . $searchKeyword . '%';
}
if ($filterKategori !== '') {
    $sql .= " AND `kategori` = :kat";
    $sqlParams['kat'] = $filterKategori;
}
if ($filterStatus !== '') {
    $sql .= " AND `status` = :st";
    $sqlParams['st'] = $filterStatus;
}

$allowedSort = ['id'=>'id','judul'=>'judul','kategori'=>'kategori','penulis'=>'penulis','tanggal'=>'tanggal','status'=>'status','views'=>'views'];
$sortKey = trim((string)($_GET['sort'] ?? 'tanggal'));
if (!isset($allowedSort[$sortKey])) $sortKey = 'tanggal';
$sortDir = strtolower(trim((string)($_GET['dir'] ?? 'desc'))) === 'asc' ? 'ASC' : 'DESC';
$sortCol = $allowedSort[$sortKey];
$sql .= " ORDER BY `$sortCol` $sortDir, `id` DESC";
$listStmt = $pdo->prepare($sql);
$listStmt->execute($sqlParams);
$beritaList = $listStmt->fetchAll();
function adminSortUrl(string $col, string $currentKey, string $currentDir): string {
    $params = $_GET;
    $params['sort'] = $col;
    $params['dir'] = ($currentKey === $col && $currentDir === 'ASC') ? 'desc' : 'asc';
    return 'kegiatan.php?' . http_build_query($params);
}
function adminSortIcon(string $col, string $currentKey, string $currentDir): string {
    if ($currentKey !== $col) return ' <i class="fa-solid fa-sort" style="opacity:0.35;margin-left:4px;"></i>';
    return $currentDir === 'ASC' ? ' <i class="fa-solid fa-sort-up" style="margin-left:4px;color:var(--primary);"></i>' : ' <i class="fa-solid fa-sort-down" style="margin-left:4px;color:var(--primary);"></i>';
}

// Hitung statistik
$statTotal = (int) $pdo->query("SELECT COUNT(*) FROM `berita` WHERE `tipe` = 'kegiatan'")->fetchColumn();
$statPublished = (int) $pdo->query("SELECT COUNT(*) FROM `berita` WHERE `tipe` = 'kegiatan' AND `status` = 'published'")->fetchColumn();
$statDraft = (int) $pdo->query("SELECT COUNT(*) FROM `berita` WHERE `tipe` = 'kegiatan' AND `status` = 'draft'")->fetchColumn();


$kategoriDefault = ['Pendidikan', 'Workshop', 'Seminar', 'Kolaborasi', 'Advokasi', 'Pengumuman'];
try {
    $kategoriDistinct = $pdo->query("SELECT DISTINCT `kategori` FROM `berita` WHERE `kategori` IS NOT NULL AND `kategori` <> '' ORDER BY `kategori` ASC")->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) { $kategoriDistinct = []; }
$kategoriList = array_values(array_unique(array_merge($kategoriDefault, $kategoriDistinct)));
sort($kategoriList);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kegiatan - Admin HIPPMI</title>
    <link rel="icon" type="image/webp" href="../img/Logo.webp">
    <link rel="icon" type="image/png" href="../img/Logo.png">
    <link rel="apple-touch-icon" href="../img/Logo.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

    <style>
:root{--primary:#e30a17;--primary-dark:#b50711;--primary-soft:#fff0f1;--dark:#1b1c20;--text:#3c3d44;--muted:#797a85;--border:#e6e6ec;--background:#f6f7fb;--white:#fff;--sidebar-width:270px;--sidebar-collapsed:72px;--success:#15803d;--success-soft:#ecfdf5;--shadow-sm:0 4px 15px rgba(0,0,0,.04)}
*{box-sizing:border-box}html{max-width:100%;overflow-x:hidden}body{margin:0;font-family:Poppins,sans-serif;color:var(--text);background:var(--background);min-height:100vh;overflow-x:hidden}a{text-decoration:none;color:inherit}
.admin-layout{display:flex;min-height:100vh}.sidebar{width:var(--sidebar-width);background:var(--dark);color:#fff;display:flex;flex-direction:column;position:fixed;top:0;bottom:0;left:0;z-index:1000;transition:width .3s,transform .3s;overflow:hidden}.main-wrapper{margin-left:var(--sidebar-width);flex:1;display:flex;flex-direction:column;min-height:100vh;min-width:0;transition:margin-left .3s}
@media(min-width:901px){body.sidebar-collapsed .sidebar{width:var(--sidebar-collapsed)}body.sidebar-collapsed .main-wrapper{margin-left:var(--sidebar-collapsed)}body.sidebar-collapsed .brand-information,body.sidebar-collapsed .menu-label,body.sidebar-collapsed .sidebar-link span,body.sidebar-collapsed .admin-information,body.sidebar-collapsed .logout-button span{display:none!important}body.sidebar-collapsed .sidebar-brand{justify-content:center;padding:24px 10px}body.sidebar-collapsed .sidebar-link{justify-content:center;padding:11px 10px;gap:0}body.sidebar-collapsed .admin-profile{justify-content:center;padding:10px 6px}body.sidebar-collapsed .sidebar-footer{padding:12px 8px}}
.sidebar-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:999;opacity:0;visibility:hidden;transition:opacity .3s}.sidebar-overlay.show{opacity:1;visibility:visible}
.collapse-toggle{display:none;width:42px;height:42px;background:#f5f5f7;border:1px solid var(--border);border-radius:9px;color:var(--dark);cursor:pointer;align-items:center;justify-content:center}.sidebar-close-btn{display:none;width:36px;height:36px;margin-left:auto;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:8px;color:#fff;cursor:pointer;align-items:center;justify-content:center;flex-shrink:0}
@media(min-width:901px){.collapse-toggle{display:flex}.menu-toggle.mobile-only{display:none!important}.sidebar-close-btn{display:none!important}}@media(max-width:900px){.collapse-toggle{display:none!important}.sidebar-close-btn{display:flex!important}.menu-toggle.mobile-only{display:flex!important}}
.sidebar-brand{padding:24px 20px;display:flex;align-items:center;gap:12px;border-bottom:1px solid rgba(255,255,255,.08)}.sidebar-brand img{width:44px;height:44px;object-fit:contain;background:#fff;border-radius:10px;padding:4px}.brand-information strong{display:block;font-size:17px;font-weight:700}.brand-information span{font-size:11px;color:rgba(255,255,255,.5)}
.sidebar-navigation{flex:1;padding:20px 14px;overflow-y:auto}.menu-label{display:block;margin:18px 12px 8px;color:rgba(255,255,255,.4);font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase}
.sidebar-link{min-height:46px;margin-bottom:6px;padding:11px 14px;display:flex;align-items:center;gap:13px;color:rgba(255,255,255,.7);border-radius:10px;font-size:13px;font-weight:500;transition:all .25s}.sidebar-link i{width:20px;text-align:center;font-size:15px}.sidebar-link:hover{color:#fff;background:rgba(255,255,255,.08);transform:translateX(3px)}.sidebar-link.active{color:#fff;background:linear-gradient(135deg,var(--primary),var(--primary-dark));box-shadow:0 10px 20px rgba(227,10,23,.25)}
.sidebar-footer{padding:16px;border-top:1px solid rgba(255,255,255,.08)}.admin-profile{display:flex;align-items:center;gap:12px;padding:10px;background:rgba(255,255,255,.05);border-radius:12px;margin-bottom:12px}.avatar{width:38px;height:38px;border-radius:50%;background:var(--primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px}.admin-information strong{display:block;font-size:13px;color:#fff}.admin-information span{font-size:11px;color:rgba(255,255,255,.5)}.logout-button{width:100%;height:42px;display:flex;align-items:center;justify-content:center;gap:8px;background:rgba(227,10,23,.15);color:#ff767d;border:1px solid rgba(227,10,23,.3);border-radius:10px;font-size:12px;font-weight:600;cursor:pointer;transition:all .25s}.logout-button:hover{background:var(--primary);color:#fff;border-color:var(--primary)}
.topbar{height:72px;background:var(--white);border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;padding:0 32px;position:sticky;top:0;z-index:900}.menu-toggle{display:none;background:transparent;border:0;font-size:20px;color:var(--dark);cursor:pointer}.topbar-title{display:flex;align-items:center;gap:12px}.topbar-title h2{margin:0;font-size:18px;font-weight:700;color:var(--dark)}.topbar-right{display:flex;align-items:center;gap:14px}.btn-view-site{padding:8px 16px;display:inline-flex;align-items:center;gap:8px;background:var(--primary-soft);color:var(--primary);border-radius:10px;font-size:12px;font-weight:600;transition:all .2s}.btn-view-site:hover{background:var(--primary);color:#fff}
.content-body{padding:30px 32px;flex:1;min-width:0;max-width:100%;overflow-x:hidden}
.alert{padding:14px 18px;border-radius:12px;display:flex;align-items:center;gap:12px;margin-bottom:24px;font-size:13px;transition:opacity .4s,transform .4s}.alert-hide{opacity:0;transform:translateY(-8px);pointer-events:none}.alert-success{background:var(--success-soft);color:var(--success);border:1px solid #bbf7d0}.alert-danger{background:var(--primary-soft);color:var(--primary);border:1px solid #fecdd3}
.stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:26px}.stat-card{background:var(--white);border-radius:16px;padding:20px;display:flex;align-items:center;gap:16px;box-shadow:var(--shadow-sm);border:1px solid var(--border)}.stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px}.stat-icon.red{background:var(--primary-soft);color:var(--primary)}.stat-icon.green{background:var(--success-soft);color:var(--success)}.stat-icon.yellow{background:#fffbeb;color:#b45309}.stat-info span{display:block;font-size:12px;color:var(--muted);margin-bottom:4px}.stat-info strong{font-size:22px;font-weight:700;color:var(--dark)}
.panel{background:var(--white);border-radius:18px;box-shadow:var(--shadow-sm);border:1px solid var(--border);overflow:hidden;margin-bottom:30px;width:100%;max-width:100%}.panel-header{padding:22px 26px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px}.panel-header h3{margin:0;font-size:16px;font-weight:700;color:var(--dark)}.panel-header p{margin:3px 0 0;font-size:12px;color:var(--muted)}.btn-primary{background:linear-gradient(135deg,var(--primary),var(--primary-dark));color:#fff;padding:10px 20px;border-radius:10px;font-size:13px;font-weight:600;border:0;display:inline-flex;align-items:center;gap:8px;cursor:pointer;box-shadow:0 6px 15px rgba(227,10,23,.2);transition:all .25s}.btn-primary:hover{transform:translateY(-2px);box-shadow:0 10px 22px rgba(227,10,23,.3)}.btn-secondary{background:#f1f2f6;color:var(--text);padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;border:1px solid var(--border);display:inline-flex;align-items:center;gap:8px;cursor:pointer}
.filter-bar{padding:16px 26px;background:#fafbfc;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px}.filter-form{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.search-box{position:relative}.search-box input{height:40px;padding:0 16px 0 38px;border:1px solid var(--border);border-radius:10px;font-size:12px;width:250px;outline:none;background:#fff}.search-box i{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px}.select-filter{height:40px;padding:0 14px;border:1px solid var(--border);border-radius:10px;font-size:12px;color:var(--text);background:#fff;outline:none}
.bulk-bar{display:none;align-items:center;justify-content:space-between;gap:12px;padding:10px 26px;background:#fff7ed;border-bottom:1px solid #fed7aa;font-size:12px;color:#9a3412}.bulk-bar.show{display:flex}.checkbox-cell{width:42px;text-align:center}.row-checkbox,#selectAll{width:16px;height:16px;accent-color:var(--primary);cursor:pointer}
.table-responsive{overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;width:100%;max-width:100%;display:block;border-top:1px solid var(--border)}.data-table{min-width:720px;width:100%;border-collapse:collapse;text-align:left}.data-table th{background:#fafbfc;padding:14px 20px;font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;border-bottom:1px solid var(--border);white-space:nowrap}.data-table th.sortable a{color:inherit;display:inline-flex;align-items:center}.data-table td{padding:14px 20px;border-bottom:1px solid var(--border);font-size:12px;vertical-align:middle}.data-table tbody tr:hover{background:#fafbfc}
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:600}.badge-published{background:var(--success-soft);color:var(--success);border:1px solid #bbf7d0}.badge-draft{background:#f3f4f6;color:#6b7280;border:1px solid #e5e7eb}.badge-role-super{background:#fef3c7;color:#92400e;border:1px solid #fde68a}.badge-role-admin{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}
.action-buttons{display:flex;align-items:center;gap:6px;flex-wrap:wrap}.btn-action{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border);background:#fff;color:var(--muted);font-size:12px;cursor:pointer;transition:all .2s}.btn-action:hover{color:#fff}.btn-action.edit:hover{background:#3b82f6;border-color:#3b82f6}.btn-action.delete:hover{background:var(--primary);border-color:var(--primary)}
.form-grid{display:grid;grid-template-columns:2fr 1fr;gap:28px;padding:26px}.form-group{margin-bottom:20px}.form-group label{display:block;margin-bottom:8px;font-size:12px;font-weight:600;color:var(--dark)}.form-group label span{color:var(--primary)}.form-control{width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:10px;font-size:13px;color:var(--text);outline:none;transition:border-color .2s;font-family:inherit}.form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(227,10,23,.1)}textarea.form-control{resize:vertical}.form-card{background:#fafbfc;border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:20px}.form-card h4{margin:0 0 16px;font-size:14px;font-weight:700;color:var(--dark)}
.modal-backdrop{position:fixed;inset:0;background:rgba(16,16,19,.65);backdrop-filter:blur(4px);z-index:2000;display:none;align-items:center;justify-content:center;padding:20px}.modal-backdrop.show{display:flex}.modal-box{background:var(--white);border-radius:20px;max-width:440px;width:100%;padding:30px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3);animation:modalFadeIn .2s ease}@keyframes modalFadeIn{from{transform:scale(.95);opacity:0}to{transform:scale(1);opacity:1}}.modal-icon-del{width:65px;height:65px;border-radius:50%;background:var(--primary-soft);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 16px}.modal-box h4{margin:0 0 8px;font-size:18px;color:var(--dark)}.modal-box p{margin:0 0 22px;font-size:13px;color:var(--muted);line-height:1.6}.modal-actions{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr)}.form-grid{grid-template-columns:1fr}.search-box input{width:200px}}@media(max-width:900px){.sidebar{transform:translateX(-100%)}.sidebar.show{transform:translateX(0)}.main-wrapper{margin-left:0}.menu-toggle.mobile-only{display:flex!important;align-items:center;justify-content:center;width:42px;height:42px}.content-body{padding:20px 16px}.topbar{padding:0 16px;min-height:64px}}
@media(max-width:768px){.stats-grid{grid-template-columns:1fr}.filter-form{display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%}.filter-form .search-box{grid-column:1/-1;width:100%}.filter-form .search-box input{width:100%}.filter-form .select-filter{width:100%}.panel-header{flex-direction:column;align-items:center;text-align:center}.panel-header>div{width:100%;text-align:center}.panel-header h3,.panel-header p{text-align:center;width:100%}.panel-actions{width:100%}.panel-actions .btn-primary{width:100%;justify-content:center}.form-grid{padding:16px}}

        /* Dashboard specific */
/* Dashboard specific (welcome, stats, grid) */
.dashboard-main {
            flex: 1;
            width: 100%;
            max-width: 1500px;
            margin: 0 auto;
            padding: 28px;
        }

        .welcome-card {
            position: relative;
            margin-bottom: 22px;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            overflow: hidden;
            color: #ffffff;
            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    var(--primary-dark)
                );
            border-radius: 17px;
            box-shadow: 0 16px 38px rgba(227, 10, 23, 0.23);
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -90px;
            right: 80px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .welcome-card::after {
            content: '';
            position: absolute;
            right: -60px;
            bottom: -120px;
            width: 270px;
            height: 270px;
            background: rgba(255, 255, 255, 0.07);
            border-radius: 50%;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
            max-width: 680px;
        }

        .welcome-label {
            display: inline-block;
            margin-bottom: 8px;
            padding: 5px 11px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50px;
            font-size: 9px;
            font-weight: 600;
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        .welcome-content h2 {
            margin: 0 0 9px;
            font-size: 28px;
        }

        .welcome-content p {
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 12px;
            line-height: 1.8;
        }

        .login-time {
            margin-top: 17px;
            display: flex;
            align-items: center;
            gap: 7px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 10px;
        }

        .login-time strong {
            color: #ffffff;
        }

        .welcome-icon {
            position: relative;
            z-index: 2;
            width: 100px;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #ffffff;
            background: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 25px;
            font-size: 42px;
            transform: rotate(5deg);
        }

        .statistics {
            margin-bottom: 22px;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 17px;
        }

        .stat-card {
            min-width: 0;
            padding: 19px;
            display: flex;
            align-items: center;
            gap: 13px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 7px 22px rgba(30, 30, 35, 0.04);
        }

        .stat-icon {
            width: 51px;
            height: 51px;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 13px;
            font-size: 19px;
        }

        .stat-icon.red {
            color: #d60a16;
            background: #fff0f1;
        }

        .stat-icon.orange {
            color: #d36b00;
            background: #fff4e8;
        }

        .stat-icon.green {
            color: #16814e;
            background: #eafaf2;
        }

        .stat-icon.blue {
            color: #176abc;
            background: #ecf5ff;
        }

        .stat-info {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .stat-info span {
            color: var(--muted);
            font-size: 10px;
        }

        .stat-info strong {
            margin: 3px 0;
            color: var(--dark);
            font-size: 22px;
        }

        .stat-info strong.status {
            font-size: 16px;
        }

        .stat-info small {
            overflow: hidden;
            color: #999aa2;
            font-size: 8px;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns:
                minmax(0, 1.5fr)
                minmax(280px, 1fr);
            gap: 20px;
        }

        .panel {
            padding: 21px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(30, 30, 35, 0.04);
        }

        .panel-header {
            margin-bottom: 18px;
            padding-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
        }

        .panel-header h3 {
            margin: 0;
            color: var(--dark);
            font-size: 15px;
        }

        .panel-header p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 9px;
        }

        .panel-header > i {
            color: var(--primary);
        }

        .quick-links {
            display: grid;
            gap: 10px;
        }

        .quick-link {
            padding: 13px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
            background: #fafafd;
            border: 1px solid var(--border);
            border-radius: 11px;
            text-decoration: none;
        }

        .quick-link:hover {
            background: var(--primary-soft);
            border-color: #ffc7cb;
        }

        .quick-link-icon {
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--primary);
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: 10px;
        }

        .quick-link-content {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .quick-link-content strong {
            color: var(--dark);
            font-size: 11px;
        }

        .quick-link-content span {
            margin-top: 2px;
            color: var(--muted);
            font-size: 8px;
        }

        .system-row {
            padding: 12px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            border-bottom: 1px dashed var(--border);
            font-size: 10px;
        }

        .system-row:last-child {
            border-bottom: 0;
        }

        .system-row span {
            color: var(--muted);
        }

        .system-row strong {
            color: var(--dark);
            font-size: 10px;
            text-align: right;
        }

        .system-row .active-status {
            color: #16814e;
        }

        .footer {
            padding: 17px 28px;
            color: var(--muted);
            background: #ffffff;
            border-top: 1px solid var(--border);
            font-size: 9px;
            text-align: center;
        }

        .footer p {
            margin: 0;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            z-index: 990;
            visibility: hidden;
            background: rgba(0, 0, 0, 0.58);
            opacity: 0;
        }

        .sidebar-overlay.show {
            visibility: visible;
            opacity: 1;
        }

        .logout-modal {
            position: fixed;
            inset: 0;
            z-index: 3000;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
            opacity: 0;
            pointer-events: none;
            transition:
                opacity 0.25s ease,
                visibility 0.25s ease;
        }

        .logout-modal.show {
            visibility: visible;
            opacity: 1;
            pointer-events: auto;
        }

        .logout-overlay {
            position: absolute;
            inset: 0;
            background: rgba(16, 16, 19, 0.68);
            backdrop-filter: blur(4px);
        }

        .logout-dialog {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
            padding: 31px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
            text-align: center;
            transform: translateY(25px) scale(0.96);
            transition: transform 0.28s ease;
        }

        .logout-modal.show .logout-dialog {
            transform: translateY(0) scale(1);
        }

        .modal-close {
            position: absolute;
            top: 14px;
            right: 14px;
            width: 36px;
            height: 36px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #81818a;
            background: #f3f3f5;
            border: 0;
            border-radius: 50%;
            cursor: pointer;
        }

        .modal-close:hover {
            color: #ffffff;
            background: var(--primary);
        }

        .modal-icon {
            width: 76px;
            height: 76px;
            margin: 0 auto 19px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--primary);
            background: var(--primary-soft);
            border: 1px solid #ffd0d3;
            border-radius: 50%;
            font-size: 29px;
        }

        .logout-dialog h3 {
            margin: 0 0 9px;
            color: var(--dark);
            font-size: 21px;
        }

        .logout-dialog p {
            max-width: 320px;
            margin: 0 auto 25px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.7;
        }

        .modal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 11px;
        }

        .cancel-button,
        .confirm-button {
            min-height: 48px;
            padding: 10px 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            border-radius: 11px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .cancel-button {
            color: #55565e;
            background: #f3f3f5;
            border: 1px solid #dddde3;
        }

        .confirm-button {
            color: #ffffff;
            background:
                linear-gradient(
                    135deg,
                    var(--primary),
                    var(--primary-dark)
                );
            border: 0;
        }

        .confirm-button:disabled {
            cursor: wait;
            opacity: 0.75;
        }

        @media (max-width: 1199px) {
            .statistics {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
                transition: width 0.3s ease, transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-close-btn,
            .menu-toggle {
                display: block;
            }

            .main-wrapper, .content {
                margin-left: 0;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .topbar {
                min-height: 70px;
                padding: 12px 15px;
            }

            .topbar-left h1 {
                font-size: 18px;
            }

            .topbar-left p,
            .topbar-profile div:last-child {
                display: none;
            }

            .website-button {
                width: 42px;
                padding: 0;
                justify-content: center;
            }

            .website-button span {
                display: none;
            }

            .topbar-profile {
                padding-left: 0;
                border-left: 0;
            }

            .dashboard-main {
                padding: 18px 14px;
            }

            .welcome-icon {
                display: none;
            }

            .statistics {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .sidebar {
                width: min(290px, 86vw);
            }

            .modal-actions {
                grid-template-columns: 1fr;
            }

            .confirm-button {
                order: 1;
            }

            .cancel-button {
                order: 2;
            }
        }
    
/* FIX preserve berita/kegiatan tables after dashboard style sync */
.kategori-terdaftar-bar{padding:10px 18px;background:#fff;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;gap:8px 8px;align-items:center}
.kategori-terdaftar-bar .cat-label{font-size:10px;font-weight:700;color:var(--muted);letter-spacing:.04em;margin-right:4px}
.cat-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:600;line-height:1}
.cat-hint{font-size:10px;color:var(--muted);margin-left:6px}
.news-title-cell{max-width:280px}
.news-title-cell strong{font-size:13px;line-height:1.4;color:var(--dark);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;word-break:break-word}
.table-thumb, .data-table .table-thumb{width:58px !important;height:44px !important;min-width:58px;max-width:58px;max-height:44px;object-fit:cover;border-radius:8px;border:1px solid var(--border);display:block;background:#f1f2f6}


@media(max-width:767px){
  .topbar{padding:12px 16px;min-height:70px}
  .topbar-title h2{font-size:18px !important}
  .topbar-title p{display:none !important}
}


@media(max-width:767px){
  .btn-view-site span{display:none !important}
  .btn-view-site{width:42px !important;padding:0 !important;justify-content:center !important}
  .topbar-right{gap:8px !important}
  .topbar{padding:12px 16px !important;min-height:70px !important}
}
/* FIX preserve berita/kegiatan tables after dashboard style sync */
.kategori-terdaftar-bar{padding:10px 18px;background:#fff;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;gap:8px 8px;align-items:center}
.kategori-terdaftar-bar .cat-label{font-size:10px;font-weight:700;color:var(--muted);letter-spacing:.04em;margin-right:4px}
.cat-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;font-size:11px;font-weight:600;line-height:1}
.cat-hint{font-size:10px;color:var(--muted);margin-left:6px}
.news-title-cell{max-width:280px}
.news-title-cell strong{font-size:13px;line-height:1.4;color:var(--dark);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;word-break:break-word}
.table-thumb, .data-table .table-thumb{width:58px !important;height:44px !important;min-width:58px;max-width:58px;max-height:44px;object-fit:cover;border-radius:8px;border:1px solid var(--border);display:block;background:#f1f2f6}


@media(max-width:767px){
  .topbar{padding:12px 16px;min-height:70px}
  .topbar-title h2{font-size:18px !important}
  .topbar-title p{display:none !important}
}


@media(max-width:767px){
  .btn-view-site span{display:none !important}
  .btn-view-site{width:42px !important;padding:0 !important;justify-content:center !important}
  .topbar-right{gap:8px !important}
  .topbar{padding:12px 16px !important;min-height:70px !important}
}

/* EDIT form rapi: kiri lebih lebar, cegah meluber */
.form-grid > div{min-width:0}
.form-left,.form-right{min-width:0}
.form-control{max-width:100%;box-sizing:border-box}


/* Pratinjau gambar kecil agar tidak meluber */
.image-preview-container{height:130px !important;max-height:130px !important}
.image-preview-container img{height:130px !important;max-height:130px !important;object-fit:cover}
#imagePreviewBox{height:130px !important;max-height:130px !important}
#previewImg{height:130px !important;max-height:130px !important;object-fit:cover}


/* CKEditor 5 – Isi Lengkap agar rapi */
.ck-editor__editable{ min-height:300px; max-height:520px; font-size:13px; line-height:1.7; }
.ck.ck-toolbar{ border-radius:10px 10px 0 0 !important; border-color:var(--border) !important; }
.ck.ck-editor__main>.ck-editor__editable{ border-radius:0 0 10px 10px !important; border-color:var(--border) !important; background:#fff !important; }
.ck.ck-editor__editable:not(.ck-editor__nested-editable).ck-focused{ border-color:var(--primary) !important; box-shadow:0 0 0 3px rgba(227,10,23,.1) !important; }

</style>
</head>
<body>

<div class="admin-layout">
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="adminSidebar">
                <div class="sidebar-brand">
            <img src="../img/Logo.webp" alt="Logo HIPPMI">
            <div class="brand-information">
                <strong>HIPPMI</strong>
                <span>Admin Panel</span>
            </div>
            <button type="button" class="sidebar-close-btn" id="sidebarClose" aria-label="Tutup Menu"><i class="fa-solid fa-angles-left"></i></button>
        </div>

<nav class="sidebar-navigation">

            <span class="menu-label">Menu utama</span>

            <a href="dashboard.php" class="sidebar-link">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <span class="menu-label">Kelola Konten</span>

            <a href="berita.php" class="sidebar-link">
                <i class="fa-solid fa-newspaper"></i>
                <span>Kelola Berita</span>
            </a>
            <a href="kegiatan.php" class="sidebar-link active">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Kelola Kegiatan</span>
            </a>
            <a href="struktur.php" class="sidebar-link">
                <i class="fa-solid fa-sitemap"></i>
                <span>Struktur Organisasi</span>
            </a>
            <a href="core_values.php" class="sidebar-link">
                <i class="fa-solid fa-star"></i>
                <span>Core Values</span>
            </a>
            <?php if(isSuperAdmin()): ?>
            <span class="menu-label">Kelola Admin</span>

            <a href="admin_users.php" class="sidebar-link">
                <i class="fa-solid fa-users-gear"></i>
                <span>Kelola Admin</span>
            </a>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer"><div class="admin-profile"><div class="avatar"><?= escape($adminInitial) ?></div><div class="admin-information"><strong><?= escape($adminUsername) ?></strong><span><?= escape(ucwords(str_replace('_',' ', $adminRole ?? "Administrator"))) ?></span></div></div><button type="button" class="logout-button" id="openLogoutBtn"><i class="fa-solid fa-right-from-bracket"></i><span>Keluar</span></button></div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-title">
                <button type="button" class="menu-toggle mobile-only" id="menuToggle" aria-label="Buka Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <button type="button" class="collapse-toggle" id="collapseToggle" aria-label="Toggle Sidebar">
                    <i class="fa-solid fa-angles-left" id="collapseIcon"></i>
                </button>
                <h2>Kelola Kegiatan HIPPMI</h2>
                    <p style="margin:0;font-size:11px;color:var(--muted);" class="d-none d-sm-block">Kelola jadwal dan kegiatan</p>
            </div>
            <div class="topbar-right"><a href="../kegiatan.php" class="btn-view-site" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i><span>Lihat Publik</span></a></div>
        </header>

        <!-- Body -->
        <main class="content-body">

            <?php if ($flashSuccess !== ''): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span><?= escape($flashSuccess); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($flashError !== ''): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?= escape($flashError); ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($formErrors)): ?>
                <div class="alert alert-danger" style="flex-direction: column; align-items: flex-start;">
                    <div style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>Mohon perbaiki kesalahan berikut:</span>
                    </div>
                    <ul style="margin: 8px 0 0 20px; padding: 0;">
                        <?php foreach ($formErrors as $err): ?>
                            <li><?= escape($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($action === 'index'): ?>

                <!-- STATS CARDS -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon red">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>
                        <div class="stat-info">
                            <span>Total Kegiatan</span>
                            <strong><?= $statTotal; ?></strong>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <span>Published</span>
                            <strong><?= $statPublished; ?></strong>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon yellow">
                            <i class="fa-solid fa-file-pen"></i>
                        </div>
                        <div class="stat-info">
                            <span>Draft</span>
                            <strong><?= $statDraft; ?></strong>
                        </div>
                    </div>
</div>

                <!-- TABLE PANEL -->
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <h3>Daftar Kegiatan</h3>
                            <p>Kelola seluruh kegiatan yang ditampilkan pada halaman Kegiatan HIPPMI</p>
                        </div>
                        <div class="panel-actions">
                            <a href="kegiatan.php?action=create" class="btn-primary">
                                <i class="fa-solid fa-plus"></i>
                                <span>Tambah Kegiatan Baru</span>
                            </a>
                        </div>
                    </div>

                    <!-- Toolbar Filter & Search -->
                    <div class="filter-bar">
                     <form method="get" action="kegiatan.php" class="filter-form" id="adminFilterForm">
                         <div class="search-box">
                             <i class="fa-solid fa-search"></i>
                             <input type="text" name="q" id="adminSearchInput" value="<?= escape($searchKeyword); ?>" placeholder="Cari judul / penulis / kategori...">
                             <button type="submit" class="search-clear" id="adminSearchClear" style="display: none;"><i class="fa-solid fa-xmark"></i></button>
                         </div>

                          <select name="kategori" id="adminKategoriFilter" class="select-filter">
                              <option value="">Semua Kategori</option>
                              <?php foreach ($kategoriList as $kat): ?>
                                  <option value="<?= $kat; ?>" <?= $filterKategori === $kat ? 'selected' : ''; ?>><?= $kat; ?></option>
                              <?php endforeach; ?>
                          </select>

                          <select name="status" id="adminStatusFilter" class="select-filter">
                              <option value="">Semua Status</option>
                              <option value="published" <?= $filterStatus === 'published' ? 'selected' : ''; ?>>Published</option>
                              <option value="draft" <?= $filterStatus === 'draft' ? 'selected' : ''; ?>>Draft</option>
                          </select>

                         <button type="submit" class="btn-secondary" style="height: 40px; padding: 0 14px;" id="adminFilterBtn">
                             <i class="fa-solid fa-filter"></i> Filter
                         </button>

                         <?php $hasAdminFilter = ($searchKeyword !== '' || $filterKategori !== '' || $filterStatus !== ''); ?>
                         <button type="button" class="btn-secondary" style="height: 40px; padding: 0 14px; <?= $hasAdminFilter ? '' : 'display: none;'; ?>" id="adminResetBtn">
                             <i class="fa-solid fa-xmark"></i> Reset
                         </button>
                     </form>
                     <!-- Indikator loading filter -->
                     <div id="adminFilterLoading" class="text-end" style="display:none; color: var(--muted); font-size: 0.85rem; margin-top: 6px;">
                         <i class="fa-solid fa-spinner fa-pulse"></i> Memfilter...
                      </div>
                      </div>

                     <div class="kategori-terdaftar-bar">
                         <span class="cat-label">KATEGORI TERDAFTAR:</span>
                         <?php foreach ($kategoriList as $kItem): ?>
                             <?php $isDefault = in_array($kItem, $kategoriDefault, true); ?>
                             <span class="cat-pill" style="<?= $isDefault ? 'background:#f3f4f6;color:#4b5563;border:1px solid #e5e7eb;' : 'background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;' ?>">
                                 <?= escape($kItem); ?>
                                 <?php if (!$isDefault): ?>
                                      <button type="button" onclick="openKategoriDeleteModal('<?= addslashes($kItem); ?>')" style="width:18px;height:18px;border-radius:50%;background:rgba(37,99,235,0.12);display:inline-flex;align-items:center;justify-content:center;color:#2563eb;border:0;cursor:pointer;" title="Hapus kategori"><i class="fa-solid fa-xmark" style="font-size:10px;"></i></button>
                                 <?php else: ?>
                                     <i class="fa-solid fa-lock" style="font-size:9px;opacity:0.45;" title="Kategori bawaan"></i>
                                 <?php endif; ?>
                             </span>
                         <?php endforeach; ?>
                         <span class="cat-hint">(kustom bisa hapus ✕, bawaan terkunci)</span>
                     </div>

                     <div class="bulk-bar" id="bulkBar">
                         <div><input type="checkbox" id="selectAllTop" style="accent-color: var(--primary); width:14px; height:14px; vertical-align:middle; margin-right:6px;"> <strong><span id="bulkCount">0</span> terpilih</strong> <span style="color: var(--muted);">— centang baris untuk hapus massal</span></div>
                         <div style="display:flex; gap:8px;">
                             <button type="button" class="btn-secondary" id="clearSelectionBtn" style="height:36px; padding:0 14px;">Batal Pilih</button>
                             <button type="button" class="btn-primary" id="bulkDeleteBtn" style="height:36px; padding:0 14px; background: var(--primary);"><i class="fa-solid fa-trash"></i> Hapus Terpilih</button>
                         </div>
                     </div>

                     <!-- Table Data -->
                     <form id="bulkDeleteForm" method="post" action="kegiatan.php?action=bulk_delete"><?= csrfField() ?>
                     <div class="table-responsive">
                        <div style="display:flex; align-items:center; gap:8px; padding:8px 14px; border-bottom:1px solid var(--border); background:#fafbfc; position:sticky; left:0; min-width:980px;">
                            <i class="fa-solid fa-arrows-left-right" style="color: var(--muted);"></i>
                            <span style="font-size:11px; color: var(--muted);">Geser tabel ke kanan/kiri</span>
                        </div>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="checkbox-cell" style="width:72px; text-align:center;"><label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:11px;font-weight:700;color:var(--muted);"><input type="checkbox" id="selectAll" title="Pilih semua"><span>All</span></label></th>
                                    <th class="sortable" style="width: 50px;"><a href="<?= adminSortUrl('id', $sortKey, $sortDir); ?>" style="text-decoration:none;">No<?= adminSortIcon('id', $sortKey, $sortDir); ?></a></th>
                                    <th style="width: 75px;">Gambar</th>
                                    <th class="sortable"><a href="<?= adminSortUrl('judul', $sortKey, $sortDir); ?>" style="text-decoration:none;">Judul<?= adminSortIcon('judul', $sortKey, $sortDir); ?></a></th>
                                    <th class="sortable"><a href="<?= adminSortUrl('kategori', $sortKey, $sortDir); ?>" style="text-decoration:none;">Kategori<?= adminSortIcon('kategori', $sortKey, $sortDir); ?></a></th>
                                    <th class="sortable"><a href="<?= adminSortUrl('penulis', $sortKey, $sortDir); ?>" style="text-decoration:none;">Penulis<?= adminSortIcon('penulis', $sortKey, $sortDir); ?></a></th>
                                    <th class="sortable"><a href="<?= adminSortUrl('tanggal', $sortKey, $sortDir); ?>" style="text-decoration:none;">Tanggal<?= adminSortIcon('tanggal', $sortKey, $sortDir); ?></a></th>
                                    <th class="sortable"><a href="<?= adminSortUrl('status', $sortKey, $sortDir); ?>" style="text-decoration:none;">Status<?= adminSortIcon('status', $sortKey, $sortDir); ?></a></th>
                                    <th class="sortable" style="text-align:center;"><a href="<?= adminSortUrl('views', $sortKey, $sortDir); ?>" style="text-decoration:none;justify-content:center;width:100%;">Kali Dibaca<?= adminSortIcon('views', $sortKey, $sortDir); ?></a></th>
                                    <th style="width: 120px; text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($beritaList)): ?>
                                    <tr>
                                        <td colspan="11" style="text-align: center; padding: 40px 20px; color: var(--muted);">
                                            <i class="fa-solid fa-folder-open" style="font-size: 36px; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                                            Belum ada data kegiatan yang sesuai.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($beritaList as $row): ?>
                                        <tr>
                                            <td class="checkbox-cell"><input type="checkbox" name="ids[]" value="<?= $row['id']; ?>" class="row-checkbox"></td>
                                            <td><?= $no++; ?></td>
                                            <td>
                                                <?php
                                                    $imgSrc = !empty($row['gambar']) ? $row['gambar'] : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=200';
                                                    if (!str_starts_with($imgSrc, 'http://') && !str_starts_with($imgSrc, 'https://')) {
                                                        $imgSrc = '../' . $imgSrc;
                                                    }
                                                ?>
                                                <img src="<?= escape($imgSrc); ?>" alt="Thumb" class="table-thumb" onerror="this.src='https://placehold.co/120x80?text=No+Image';">
                                            </td>
                                            <td class="news-title-cell" title="<?= escape($row['judul']); ?>">
                                                <strong><?= escape($row['judul']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-cat"><?= escape($row['kategori']); ?></span>
                                            </td>
                                            <td>
                                                <span style="display:inline-flex;align-items:center;gap:8px;">
                                                    <?php
                                                        $av = $row['penulis_avatar'] ?? '';
                                                        $avUrl = $av ? (str_starts_with($av,'http') ? $av : '../'.$av) : '';
                                                    ?>
                                                    <?php if ($avUrl): ?>
                                                        <img src="<?= escape($avUrl); ?>" alt="av" style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid var(--border);" onerror="this.style.display='none'">
                                                    <?php endif; ?>
                                                    <?= escape($row['penulis']); ?>
                                                </span>
                                            </td>
                                            <td><?= formatTanggalIndonesia($row['tanggal']); ?></td>
                                            <td>
                                                <?php if ($row['status'] === 'published'): ?>
                                                    <span class="badge badge-published">
                                                        <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Published
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-draft">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align:center;font-weight:600;color:var(--dark);"><i class="far fa-eye" style="opacity:0.45;margin-right:4px;"></i><?= number_format((int)($row['views'] ?? 0)); ?></td>
                                            <td style="text-align: right;">
                                                <div class="action-buttons" style="justify-content: flex-end;">
                                                    <a href="kegiatan.php?action=edit&id=<?= $row['id']; ?>" class="btn-action edit" title="Ubah Berita">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                    <button type="button" class="btn-action delete" title="Hapus Berita" onclick="openDeleteModal(<?= $row['id']; ?>, '<?= addslashes(escape($row['judul'])); ?>')">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    </form>
                    <div id="bulkConfirmBackdrop" class="modal-backdrop" style="z-index:2150;">
                        <div class="modal-box">
                            <div class="modal-icon-del"><i class="fa-solid fa-trash-can"></i></div>
                            <h4>Hapus <span id="bulkDeleteCountLabel">0</span> berita terpilih?</h4>
                            <p>Tindakan ini tidak dapat dibatalkan. File gambar terkait juga akan dihapus.</p>
                            <div class="modal-actions">
                                <button type="button" class="btn-secondary" id="bulkCancelBtn" style="justify-content:center;">Batal</button>
                                <button type="button" class="btn-primary" id="bulkConfirmBtn" style="justify-content:center;background:var(--primary);">Ya, Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif (in_array($action, ['create', 'edit'], true)): ?>

                <!-- FORM TAMBAH / EDIT BERITA -->
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <h3><?= $action === 'create' ? 'Tambah Kegiatan Baru' : 'Ubah Kegiatan: ' . escape($formData['judul']); ?></h3>
                            <p><?= $action === 'create' ? 'Isi formulir di bawah ini untuk menerbitkan kegiatan baru di halaman Kegiatan HIPPMI' : 'Perbarui informasi kegiatan dan simpan perubahan'; ?></p>
                        </div>
                        <div class="panel-actions">
                            <a href="kegiatan.php" class="btn-secondary" onclick="if(document.referrer && document.referrer.indexOf('kegiatan.php')!==-1){history.back();return false;}">
                                <i class="fa-solid fa-arrow-left"></i>
                                <span>Kembali ke Daftar</span>
                            </a>
                        </div>
                    </div>

                    <form id="kegiatanForm" method="post" action="kegiatan.php?action=<?= $action; ?><?= $action === 'edit' ? '&id=' . $id : ''; ?>" enctype="multipart/form-data" novalidate><?= csrfField() ?>
                        <div class="form-grid">
                            <!-- Kolom Kiri: Konten Utama -->
                            <div class="form-left">
                                <div class="form-group">
                                    <label for="judul">Judul Kegiatan <span>*</span></label>
                                    <input type="text" id="judul" name="judul" class="form-control" value="<?= escape($formData['judul']); ?>" placeholder="Contoh: Workshop Penguatan Guru Muda Se-Indonesia" required autofocus>
                                </div>

                                <div class="form-group">
                                    <label for="ringkasan">Ringkasan Kegiatan <span>*</span></label>
                                    <textarea id="ringkasan" name="ringkasan" class="form-control" rows="3" placeholder="Tuliskan 1-2 kalimat ringkasan yang menarik pembaca..." required><?= escape($formData['ringkasan']); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="konten">Deskripsi Lengkap Kegiatan <span>*</span></label>
                                    <textarea id="konten" name="konten" class="form-control" rows="12" placeholder="Tulis isi berita lengkap di sini (dapat menggunakan paragraf atau teks html sederhana)..."><?= escape($formData['konten']); ?></textarea>
                                    <small style="color: var(--muted); display: block; margin-top: 6px; font-size: 11px;">
                                        Tips: Gunakan pemisah paragraf untuk memudahkan pengunjung membaca berita.
                                    </small>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Meta & Gambar -->
                            <div class="form-right">
                                <div class="form-card">
                                    <h4>Publikasi & Status</h4>

                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="published" <?= $formData['status'] === 'published' ? 'selected' : ''; ?>>Terbitkan Sekarang (Published)</option>
                                            <option value="draft" <?= $formData['status'] === 'draft' ? 'selected' : ''; ?>>Simpan Sebagai Draft</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="kategori">Kategori <span>*</span></label>
                                        <select id="kategori_select" name="kategori" class="form-control" required onchange="handleKategoriChange(this.value)">
                                            <?php foreach ($kategoriList as $kat): ?>
                                                <option value="<?= $kat; ?>" <?= $formData['kategori'] === $kat ? 'selected' : ''; ?>><?= $kat; ?></option>
                                            <?php endforeach; ?>
                                            <option value="__new__">+ Tambah Kategori Baru...</option>
                                        </select>
                                        <input type="text" id="kategori_baru" name="kategori_baru" class="form-control" placeholder="Tulis kategori baru, mis. Pengumuman" style="display:none;margin-top:8px;" maxlength="100">
                                        <small id="kategori_hint" style="color:var(--muted);font-size:11px;display:block;margin-top:4px;">Pilih kategori atau tambah baru. Semua menyesuaikan otomatis.</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="tanggal">Tanggal Kegiatan <span>*</span></label>
                                        <input type="date" id="tanggal" name="tanggal" class="form-control" value="<?= escape($formData['tanggal']); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="link_daftar">Link Pendaftaran (Daftar)</label>
                                        <input type="url" id="link_daftar" name="link_daftar" class="form-control" value="<?= escape($formData['link_daftar'] ?? ''); ?>" placeholder="https://forms.gle/... atau https://...">
                                        <small style="color:var(--muted);font-size:11px;display:block;margin-top:4px;">Kosongkan jika belum ada. Tombol Daftar di halaman Kegiatan akan link ke sini.</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="penulis">Nama Penulis / Kontributor <span>*</span></label>
                                        <input type="text" id="penulis" name="penulis" class="form-control" value="<?= escape($formData['penulis']); ?>" placeholder="Admin HIPPMI" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="penulis_avatar_file">Foto Penulis</label>
                                        <input type="file" id="penulis_avatar_file" name="penulis_avatar_file" class="form-control" accept="image/*" onchange="previewAvatarImage(this)">
                                        <small style="color:var(--muted);font-size:11px;display:block;margin-top:4px;">JPG/PNG/WEBP, maks 2MB. Kosongkan jika tidak ganti.</small>
                                        <?php
                                            $avPreview = $formData['penulis_avatar'] ?? '';
                                            if (!empty($avPreview) && !str_starts_with($avPreview, 'http')) $avPreview = '../' . $avPreview;
                                        ?>
                                        <div style="margin-top:8px;display:flex;align-items:center;gap:10px;">
                                            <img id="avatarPreviewImg" src="<?= !empty($avPreview) ? escape($avPreview) : 'https://placehold.co/80x80?text=Foto'; ?>" alt="Avatar" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--border);">
                                            <input type="url" id="penulis_avatar_url" name="penulis_avatar_url" class="form-control" style="flex:1;" value="<?= !empty($formData['penulis_avatar']) && str_starts_with($formData['penulis_avatar'],'http') ? escape($formData['penulis_avatar']) : ''; ?>" placeholder="Atau URL foto https://..." oninput="previewAvatarUrl(this.value)">
                                        </div>
                                        <?php if($action==='edit' && !empty($existing['penulis_avatar'])): ?>
                                        <input type="hidden" name="hapus_avatar" id="hapusAvatarInput" value="<?= $removeExistingAvatar ? '1' : '0' ?>">
                                        <button type="button" class="btn-secondary" id="hapusAvatarBtn" style="width:100%;justify-content:center;margin-top:8px;color:var(--primary);border-color:#fecdd3;background:var(--primary-soft);"><i class="fa-solid <?= $removeExistingAvatar ? 'fa-rotate-left' : 'fa-trash-can' ?>"></i> <?= $removeExistingAvatar ? 'Batalkan Hapus Foto Penulis' : 'Hapus Foto Penulis / URL' ?></button>
                                        <small id="hapusAvatarHelp" style="display:block;margin-top:6px;font-size:11px;color:var(--muted);text-align:center;"><?= $removeExistingAvatar ? 'Foto penulis akan dihapus saat Simpan ditekan.' : 'Hapus foto & URL penulis saat ini.' ?></small>
                                        <?php endif; ?>
                                    </div>
</div>

                                <div class="form-card">
                                    <h4>Gambar Sampul</h4>

                                    <div class="form-group">
                                        <label for="gambar_file">Unggah File Gambar</label>
                                        <input type="file" id="gambar_file" name="gambar_file" class="form-control" accept="image/*" onchange="previewUploadImage(this)">
                                        <small style="color: var(--muted); font-size: 11px; display: block; margin-top: 4px;">Format: JPG, PNG, WEBP, GIF (Maks. 5MB)</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="gambar_url">Atau Masukkan Tautan URL Gambar</label>
                                        <input type="url" id="gambar_url" name="gambar_url" class="form-control" value="<?= !empty($formData['gambar']) && str_starts_with($formData['gambar'], 'http') ? escape($formData['gambar']) : ''; ?>" placeholder="https://..." oninput="previewUrlImage(this.value)">
                                    </div>

                                    <?php if($action==='edit' && !empty($existing['gambar'])): ?>
                                    <input type="hidden" name="hapus_gambar" id="hapusGambarInput" value="<?= $removeExistingGambar ? '1' : '0' ?>">
                                    <button type="button" class="btn-secondary" id="hapusGambarBtn" style="width:100%;justify-content:center;margin-top:4px;color:var(--primary);border-color:#fecdd3;background:var(--primary-soft);"><i class="fa-solid <?= $removeExistingGambar ? 'fa-rotate-left' : 'fa-trash-can' ?>"></i> <?= $removeExistingGambar ? 'Batalkan Hapus Gambar / URL' : 'Hapus Gambar / URL Saat Ini' ?></button>
                                    <small id="hapusGambarHelp" style="display:block;margin-top:6px;font-size:11px;color:var(--muted);text-align:center;"><?= $removeExistingGambar ? 'Gambar sampul akan dihapus saat Simpan ditekan.' : 'Hapus file atau link sampul saat ini.' ?></small>
                                    <?php endif; ?>
                                    <label style="font-size: 11px; color: var(--muted); display: block; margin-bottom: 6px;">Pratinjau Gambar:</label>
                                    <div class="image-preview-container" id="imagePreviewBox">
                                        <?php
                                            $previewSrc = $formData['gambar'] ?? '';
                                            if (!empty($previewSrc) && !str_starts_with($previewSrc, 'http')) {
                                                $previewSrc = '../' . $previewSrc;
                                            }
                                        ?>
                                        <?php if (!empty($previewSrc)): ?>
                                            <img src="<?= escape($previewSrc); ?>" id="previewImg" alt="Pratinjau">
                                        <?php else: ?>
                                            <div class="image-preview-empty" id="previewPlaceholder">
                                                <i class="fa-regular fa-image"></i>
                                                <span>Belum ada gambar dipilih</span>
                                            </div>
                                            <img src="" id="previewImg" alt="Pratinjau" style="display: none;">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px;">
                                    <i class="fa-solid fa-floppy-disk"></i>
                                    <span><?= $action === 'create' ? 'Terbitkan Kegiatan' : 'Simpan Perubahan'; ?></span>
                                </button>
                                <a href="kegiatan.php" class="btn-secondary" style="width:100%;justify-content:center;margin-top:10px;">Batal</a>
                            </div>
                        </div>
                    </form>
                </div>

            <?php endif; ?>

        </main>
    </div>

</div>

<!-- Modal Hapus Kategori -->
<div class="modal-backdrop" id="kategoriDeleteModal" style="z-index:2145;">
    <div class="modal-box">
        <div class="modal-icon-del" style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;"><i class="fa-solid fa-tag"></i></div>
        <h4>Hapus Kategori?</h4>
        <p>Apakah Anda yakin ingin menghapus kategori <strong id="kategoriDeleteName">...</strong>? Kegiatan dengan kategori ini akan dipindah ke <strong>Pendidikan</strong>.</p>
        <form id="kategoriDeleteForm" method="post" action=""><?= csrfField() ?><div class="modal-actions"><button type="button" class="btn-secondary" onclick="closeKategoriDeleteModal()" style="justify-content:center;">Batal</button><button type="submit" class="btn-primary" style="justify-content:center;background:var(--primary);">Ya, Hapus</button></div></form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal-backdrop" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon-del">
            <i class="fa-solid fa-trash-can"></i>
        </div>
        <h4>Hapus Kegiatan?</h4>
        <p>Apakah Anda yakin ingin menghapus <strong id="deleteNewsTitle">...</strong>? Tindakan ini tidak dapat dibatalkan.</p>
        <form id="deleteForm" method="post" action=""><?= csrfField() ?><div class="modal-actions"><button type="button" class="btn-secondary" onclick="closeDeleteModal()" style="justify-content: center;">Batal</button><button type="submit" class="btn-primary" style="background: var(--primary); justify-content: center;">Ya, Hapus</button></div></form>
    </div>
</div>

<script>
    function handleKategoriChange(val){
        var inp=document.getElementById('kategori_baru');
        if(!inp) return;
        if(val==='__new__'){ inp.style.display='block'; inp.required=true; inp.focus(); }
        else { inp.style.display='none'; inp.required=false; }
    }
    (function(){ var sel=document.getElementById('kategori_select'); if(sel && sel.value==='__new__') handleKategoriChange('__new__'); })();

    const adminSidebar = document.getElementById('adminSidebar');
    const menuToggle = document.getElementById('menuToggle');
    const collapseToggle = document.getElementById('collapseToggle');
    const collapseIcon = document.getElementById('collapseIcon');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    function updateCollapseIcon(){
        if(!collapseIcon) return;
        var collapsed=document.body.classList.contains('sidebar-collapsed');
        collapseIcon.className = collapsed ? 'fa-solid fa-angles-right' : 'fa-solid fa-angles-left';
    }
    function isDesktop(){ return window.innerWidth > 900; }
    if (menuToggle) {
        menuToggle.addEventListener('click', function () {
            if(!isDesktop()){
                adminSidebar.classList.toggle('show');
                if(sidebarOverlay) sidebarOverlay.classList.toggle('show');
            }
        });
    }
    if (collapseToggle) {
        if(localStorage.getItem('hippmi_sidebar_collapsed')==='1'){
            document.body.classList.add('sidebar-collapsed');
            updateCollapseIcon();
        }
        collapseToggle.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-collapsed');
            var col=document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('hippmi_sidebar_collapsed', col ? '1' : '0');
            updateCollapseIcon();
        });
    }
    var sidebarCloseBtn = document.getElementById('sidebarClose');
    if(sidebarCloseBtn) sidebarCloseBtn.addEventListener('click', function(){ adminSidebar.classList.remove('show'); if(sidebarOverlay) sidebarOverlay.classList.remove('show'); document.body.classList.remove('sidebar-open'); });
    if(sidebarOverlay){
        sidebarOverlay.addEventListener('click', function(){
            adminSidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        });
    }
    window.addEventListener('resize', function(){
        if(isDesktop()){
            adminSidebar.classList.remove('show');
            if(sidebarOverlay) sidebarOverlay.classList.remove('show');
        } else {
            document.body.classList.remove('sidebar-collapsed');
            updateCollapseIcon();
        }
    });

    // Modal Hapus
    const deleteModal = document.getElementById('deleteModal');
    const deleteNewsTitle = document.getElementById('deleteNewsTitle');
    const deleteForm = document.getElementById('deleteForm');

    function openDeleteModal(id, title) {
        deleteNewsTitle.textContent = title;
        deleteForm.action = 'kegiatan.php?action=delete&id=' + id;
        deleteModal.classList.add('show');
    }

    function closeDeleteModal() {
        deleteModal.classList.remove('show');
    }
    function openKategoriDeleteModal(name){
        document.getElementById('kategoriDeleteName').textContent = name;
        document.getElementById('kategoriDeleteForm').action = 'kegiatan.php?action=delete_kategori&kategori=' + encodeURIComponent(name);
        document.getElementById('kategoriDeleteModal').classList.add('show');
    }
    function closeKategoriDeleteModal(){ document.getElementById('kategoriDeleteModal').classList.remove('show'); }
    (function(){
        var km=document.getElementById('kategoriDeleteModal');
        if(km) km.addEventListener('click', function(e){ if(e.target===km) closeKategoriDeleteModal(); });
        window.addEventListener('keydown', function(e){ if(e.key==='Escape'){ var km2=document.getElementById('kategoriDeleteModal'); if(km2&&km2.classList.contains('show')) closeKategoriDeleteModal(); }});
    })();

    // Image preview
    function previewUploadImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const img = document.getElementById('previewImg');
                const placeholder = document.getElementById('previewPlaceholder');
                img.src = e.target.result;
                img.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewUrlImage(url) {
        if (url.trim() !== '') {
            const img = document.getElementById('previewImg');
            const placeholder = document.getElementById('previewPlaceholder');
            img.src = url;
            img.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        }
    }
    function previewAvatarImage(input) {
        if (input.files && input.files[0]) {
            const r = new FileReader();
            r.onload = function(e) { const im=document.getElementById('avatarPreviewImg'); if(im){ im.src=e.target.result; } };
            r.readAsDataURL(input.files[0]);
        }
    }
    function previewAvatarUrl(url) {
        if (url.trim() !== '') { const im=document.getElementById('avatarPreviewImg'); if(im) im.src=url; }
    }

    // AJAX Search & Filter untuk kelola berita (tanpa refresh)
    (function() {
        const filterForm = document.getElementById('adminFilterForm');
        if (!filterForm) return;
        const searchInput = document.getElementById('adminSearchInput');
        const kategoriFilter = document.getElementById('adminKategoriFilter');
        const statusFilter = document.getElementById('adminStatusFilter');
        const filterBtn = document.getElementById('adminFilterBtn');
        const resetBtn = document.getElementById('adminResetBtn');
        const loadingEl = document.getElementById('adminFilterLoading');
        const tableEl = document.querySelector('table.data-table');
        const tbodyEl = tableEl ? tableEl.querySelector('tbody') : null;

        function buildUrl() {
            const params = new URLSearchParams();
            const q = searchInput.value.trim();
            if (q) params.set('q', q);
            if (kategoriFilter.value) params.set('kategori', kategoriFilter.value);
            if (statusFilter.value) params.set('status', statusFilter.value);
            return 'kegiatan?' + params.toString();
        }

        function updateResetVisibility() {
            if (!resetBtn) return;
            const url = new URLSearchParams(buildUrl().replace('berita.php?', ''));
            resetBtn.style.display = (url.toString() !== '') ? '' : 'none';
        }

        function applyFilter() {
            if (loadingEl) loadingEl.style.display = 'block';
            if (filterBtn) filterBtn.disabled = true;
            fetch(buildUrl())
                .then(function(r) { return r.text(); })
                .then(function(html) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTbody = doc.querySelector('table.data-table tbody');
                    if (tbodyEl && newTbody) tbodyEl.innerHTML = newTbody.innerHTML;
                    // sync reset button state & search clear icon from response
                    const respReset = doc.querySelector('#adminResetBtn');
                    if (resetBtn) resetBtn.style.display = respReset ? '' : 'none';
                    const respClear = doc.querySelector('#adminSearchClear');
                    // update address bar
                    history.replaceState(null, '', buildUrl());
                    bindFormEvents();
                })
                .catch(function(err) { console.error(err); })
                .finally(function() {
                    if (loadingEl) loadingEl.style.display = 'none';
                    if (filterBtn) filterBtn.disabled = false;
                });
        }

        function bindFormEvents() {
            if (!filterForm) return;
            filterForm.removeEventListener('submit', onFormSubmit);
            filterForm.addEventListener('submit', onFormSubmit);
            if (kategoriFilter) {
                kategoriFilter.removeEventListener('change', onFilterChange);
                kategoriFilter.addEventListener('change', onFilterChange);
            }
            if (statusFilter) {
                statusFilter.removeEventListener('change', onFilterChange);
                statusFilter.addEventListener('change', onFilterChange);
            }
            if (resetBtn) {
                resetBtn.removeEventListener('click', onReset);
                resetBtn.addEventListener('click', onReset);
            }
        }

        function onFormSubmit(e) {
            e.preventDefault();
            applyFilter();
        }
        function onFilterChange(e) {
            e.preventDefault();
            applyFilter();
        }
        function onReset(e) {
            e.preventDefault();
            if (searchInput) searchInput.value = '';
            if (kategoriFilter) kategoriFilter.value = '';
            if (statusFilter) statusFilter.value = '';
            applyFilter();
        }

        bindFormEvents();
    })();

    (function(){
        function syncBulk(){
            var checks = document.querySelectorAll('.row-checkbox');
            var checked = document.querySelectorAll('.row-checkbox:checked');
            var c = checked.length;
            var bulkBar = document.getElementById('bulkBar');
            var bulkCount = document.getElementById('bulkCount');
            if(bulkBar) bulkBar.classList.toggle('show', c > 0);
            if(bulkCount) bulkCount.textContent = c;
            var sa = document.getElementById('selectAll');
            var saTop = document.getElementById('selectAllTop');
            if(sa) { sa.checked = checks.length>0 && c===checks.length; sa.indeterminate = c>0 && c<checks.length; }
            if(saTop) { saTop.checked = checks.length>0 && c===checks.length; saTop.indeterminate = c>0 && c<checks.length; }
        }
        document.addEventListener('change', function(e){
            if(e.target.classList.contains('row-checkbox')) syncBulk();
            if(e.target.id === 'selectAll' || e.target.id === 'selectAllTop'){
                var checkedNow = e.target.checked;
                document.querySelectorAll('.row-checkbox').forEach(function(c){ c.checked = checkedNow; });
                var other = document.getElementById(e.target.id === 'selectAll' ? 'selectAllTop' : 'selectAll');
                if(other) { other.checked = checkedNow; other.indeterminate = false; }
                syncBulk();
            }
        });
        var clearBtn = document.getElementById('clearSelectionBtn');
        if(clearBtn) clearBtn.addEventListener('click', function(){ document.querySelectorAll('.row-checkbox').forEach(function(c){ c.checked=false; }); syncBulk(); });
        var bulkBtn = document.getElementById('bulkDeleteBtn');
        var bulkBackdrop = document.getElementById('bulkConfirmBackdrop');
        var bulkCancel = document.getElementById('bulkCancelBtn');
        var bulkConfirm = document.getElementById('bulkConfirmBtn');
        var bulkCountLabel = document.getElementById('bulkDeleteCountLabel');
        function openBulkConfirm(){ var n=document.querySelectorAll('.row-checkbox:checked').length; if(n===0) return; if(bulkCountLabel) bulkCountLabel.textContent=n; if(bulkBackdrop) bulkBackdrop.classList.add('show'); }
        function closeBulkConfirm(){ if(bulkBackdrop) bulkBackdrop.classList.remove('show'); }
        if(bulkBtn) bulkBtn.addEventListener('click', openBulkConfirm);
        if(bulkCancel) bulkCancel.addEventListener('click', closeBulkConfirm);
        if(bulkBackdrop) bulkBackdrop.addEventListener('click', function(e){ if(e.target===bulkBackdrop) closeBulkConfirm(); });
        if(bulkConfirm) bulkConfirm.addEventListener('click', function(){ var f=document.getElementById('bulkDeleteForm'); if(f) f.submit(); });
        window.addEventListener('keydown', function(e){ if(e.key==='Escape') closeBulkConfirm(); });
        // keep tbody fresh after AJAX filter
        var origSync = syncBulk;
        var filterT = document.getElementById('adminFilterForm');
        if(filterT){
            var mo = new MutationObserver(function(){ syncBulk(); });
            var tb = document.querySelector('table.data-table tbody');
            if(tb) mo.observe(tb, {childList:true});
        }
        syncBulk();
    })();
    (function(){
        var alerts = document.querySelectorAll('.alert');
        if (alerts.length) {
            setTimeout(function(){
                alerts.forEach(function(a){
                    a.classList.add('alert-hide');
                    setTimeout(function(){ a.style.display='none'; }, 420);
                });
            }, 3500);
        }
    })();



const openLogoutBtn=document.getElementById('openLogoutBtn'),logoutModal=document.getElementById('logoutModal');
if(openLogoutBtn)openLogoutBtn.addEventListener('click',()=>{logoutModal.classList.add('show');});
function closeLogout(){logoutModal.classList.remove('show');}
if(logoutModal) logoutModal.addEventListener('click',e=>{if(e.target===logoutModal)closeLogout()});
(function(){
    var hgBtn=document.getElementById('hapusGambarBtn'), hgIn=document.getElementById('hapusGambarInput');
    if(hgBtn && hgIn){
        hgBtn.addEventListener('click', function(){
            var rm = hgIn.value !== '1';
            hgIn.value = rm ? '1' : '0';
            hgBtn.innerHTML = rm ? '<i class="fa-solid fa-rotate-left"></i> Batalkan Hapus Gambar / URL' : '<i class="fa-solid fa-trash-can"></i> Hapus Gambar / URL Saat Ini';
            var help=document.getElementById('hapusGambarHelp');
            if(help) help.textContent = rm ? 'Gambar sampul akan dihapus saat Simpan ditekan.' : 'Hapus file atau link sampul saat ini.';
            var fileEl=document.getElementById('gambar_file'), urlEl=document.getElementById('gambar_url');
            var previewBox=document.getElementById('imagePreviewBox');
            if(rm){ if(fileEl) fileEl.value=''; if(urlEl) urlEl.value=''; if(previewBox) previewBox.innerHTML='<div class="image-preview-empty" style="color:var(--primary);"><i class="fa-solid fa-trash-can"></i> Gambar akan dihapus setelah perubahan disimpan</div>'; }
            else { if(previewBox && previewBox.textContent.indexOf('akan dihapus')!==-1) location.reload(); }
        });
    }
    var avBtn=document.getElementById('hapusAvatarBtn'), avIn=document.getElementById('hapusAvatarInput');
    if(avBtn && avIn){
        avBtn.addEventListener('click', function(){
            var rm = avIn.value !== '1';
            avIn.value = rm ? '1' : '0';
            avBtn.innerHTML = rm ? '<i class="fa-solid fa-rotate-left"></i> Batalkan Hapus Foto Penulis' : '<i class="fa-solid fa-trash-can"></i> Hapus Foto Penulis / URL';
            var help=document.getElementById('hapusAvatarHelp');
            if(help) help.textContent = rm ? 'Foto penulis akan dihapus saat Simpan ditekan.' : 'Hapus foto & URL penulis saat ini.';
            var fileEl=document.getElementById('penulis_avatar_file'), urlEl=document.getElementById('penulis_avatar_url');
            if(rm){ if(fileEl) fileEl.value=''; if(urlEl) urlEl.value=''; var img=document.getElementById('avatarPreviewImg'); if(img) img.src='https://placehold.co/80x80?text=Foto'; }
        });
    }
})();
</script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const el = document.getElementById('konten');
        const form = document.getElementById('kegiatanForm') || (el && el.closest('form'));
        function stripHtml(s){ return (s||'').replace(/<[^>]*>/g,'').replace(/&nbsp;/g,' ').trim(); }
        function syncValidate(e){
            if(window.beritaEditor && el) el.value = window.beritaEditor.getData();
            if(!form) return;
            const judul=form.querySelector('[name="judul"]'), ringkasan=form.querySelector('[name="ringkasan"]'), tanggal=form.querySelector('[name="tanggal"]'), katSel=form.querySelector('[name="kategori"]'), katBaru=form.querySelector('[name="kategori_baru"]');
            let msg='';
            if(!judul||!judul.value.trim()) msg='Judul kegiatan wajib diisi.';
            else if(!ringkasan||!ringkasan.value.trim()) msg='Ringkasan wajib diisi.';
            else if(!stripHtml(el?el.value:'')) msg='Deskripsi lengkap kegiatan wajib diisi.';
            else if(!tanggal||!tanggal.value) msg='Tanggal wajib diisi.';
            else if(katSel&&katSel.value==='__new__'&&(!katBaru||!katBaru.value.trim())) msg='Kategori baru wajib diisi.';
            const link=form.querySelector('[name="link_daftar"]'); if(!msg && link && link.value.trim() && !/^https?:\/\/.+/i.test(link.value.trim())) msg='Link pendaftaran harus URL valid (https://...).';
            if(msg){ e.preventDefault(); alert(msg); try{ (msg.includes('Judul')?judul:msg.includes('Ringkasan')?ringkasan:msg.includes('Deskripsi')||msg.includes('Isi')?(window.beritaEditor?window.beritaEditor.editing.view.focus():el.focus()):msg.includes('Tanggal')?tanggal:msg.includes('Kategori')?katBaru:link).focus(); }catch(_){} return false; }
            const btn=form.querySelector('button[type="submit"]'); if(btn){ btn.disabled=true; btn.style.opacity='0.75'; btn.innerHTML='<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...'; }
        }
        if(form) form.addEventListener('submit', syncValidate);
        if(!el) return;
        if(typeof ClassicEditor==='undefined'){ console.warn('CKEditor gagal load'); return; }
        ClassicEditor.create(el, { placeholder:'Tulis isi lengkap kegiatan di sini...', toolbar:['heading','|','bold','italic','underline','link','bulletedList','numberedList','blockQuote','insertTable','undo','redo'] }).then(editor=>{ window.beritaEditor=editor; }).catch(err=>console.warn('CKEditor failed',err));
    });
</script>

<div class="modal-backdrop" id="logoutModal" style="z-index:3000;"><div class="modal-box" style="max-width:420px;"><button type="button" style="position:absolute;top:14px;right:14px;width:36px;height:36px;border-radius:50%;border:0;background:#f3f3f5;cursor:pointer;" onclick="closeLogout()"><i class="fa-solid fa-xmark"></i></button><div class="modal-icon-del" style="background:#fff0f1;color:var(--primary);border:1px solid #ffd0d3;"><i class="fa-solid fa-right-from-bracket"></i></div><h4>Keluar Admin?</h4><p>Sesi akan diakhiri.</p><form method="post" action="logout.php"><div class="modal-actions"><button type="button" class="btn-secondary" style="justify-content:center;" onclick="closeLogout()">Batal</button><button type="submit" class="btn-primary" style="justify-content:center;">Ya, Keluar</button></div></form></div></div>
</body>
</html>

