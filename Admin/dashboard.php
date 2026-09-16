<?php

require_once __DIR__ . '/config.php';
requireAdminLogin();
require_once __DIR__ . '/../koneksi.php';

$pdo = getDBConnection();
$totalBerita = 0;
$totalKegiatan = 0;
$totalStruktur = 0;
$totalCore = 0;
$totalAdminUser = 0;
$beritaPublished = 0;
$beritaDraft = 0;
$chartKategori = [];
$chartKategoriCount = [];
try {
    $totalBerita = (int) $pdo->query("SELECT COUNT(*) FROM `berita` WHERE `tipe`='berita'")->fetchColumn();
    $totalKegiatan = (int) $pdo->query("SELECT COUNT(*) FROM `berita` WHERE `tipe`='kegiatan'")->fetchColumn();
    $beritaPublished = (int) $pdo->query("SELECT COUNT(*) FROM `berita` WHERE `status`='published'")->fetchColumn();
    $beritaDraft = (int) $pdo->query("SELECT COUNT(*) FROM `berita` WHERE `status`='draft'")->fetchColumn();
    $totalStruktur = (int) $pdo->query("SELECT COUNT(*) FROM `struktur_organisasi`")->fetchColumn();
    $totalCore = (int) $pdo->query("SELECT COUNT(*) FROM `core_values`")->fetchColumn();
    $totalAdminUser = (int) $pdo->query("SELECT COUNT(*) FROM `admin_users`")->fetchColumn();
    $katStmt = $pdo->query("SELECT `kategori`, COUNT(*) c FROM `berita` GROUP BY `kategori` ORDER BY c DESC LIMIT 5");
    foreach($katStmt->fetchAll() as $r){ $chartKategori[]=$r['kategori']; $chartKategoriCount[]=(int)$r['c']; }
} catch (Exception $e) {}

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$adminUsername = (string) ($_SESSION['admin_username'] ?? ADMIN_USERNAME);
$adminId = (int) ($_SESSION['admin_id'] ?? 0);
$adminRole = (string) ($_SESSION['admin_role'] ?? 'admin');
$adminInitial = strtoupper(substr($adminUsername, 0, 1));

$loginTimeRaw = null;
try {
    if ($adminId > 0) {
        $stmt = $pdo->prepare("SELECT `last_login_at` FROM `admin_users` WHERE `id`=:id LIMIT 1");
        $stmt->execute(['id'=>$adminId]);
        $r = $stmt->fetch();
        if ($r && !empty($r['last_login_at'])) $loginTimeRaw = (string)$r['last_login_at'];
    }
    if ($loginTimeRaw === null) {
        $stmt2 = $pdo->prepare("SELECT `last_login_at` FROM `admin_users` WHERE `username`=:u LIMIT 1");
        $stmt2->execute(['u'=>$adminUsername]);
        $r2 = $stmt2->fetch();
        if ($r2 && !empty($r2['last_login_at'])) $loginTimeRaw = (string)$r2['last_login_at'];
    }
} catch (Throwable $e) {}
if ($loginTimeRaw !== null && $loginTimeRaw !== '') {
    $loginTime = date('d-m-Y H:i', strtotime($loginTimeRaw));
} elseif (isset($_SESSION['admin_last_login_db']) && $_SESSION['admin_last_login_db'] !== '') {
    $loginTime = date('d-m-Y H:i', strtotime((string)$_SESSION['admin_last_login_db']));
} elseif (isset($_SESSION['admin_login_time'])) {
    $loginTime = date('d-m-Y H:i', (int)$_SESSION['admin_login_time']);
} else {
    $loginTime = date('d-m-Y H:i');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard Admin HIPPMI</title>
    <link rel="icon" type="image/webp" href="../img/Logo.webp">
    <link rel="icon" type="image/png" href="../img/Logo.png">
    <link rel="apple-touch-icon" href="../img/Logo.png">

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

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
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:28px;padding:26px}.form-group{margin-bottom:20px}.form-group label{display:block;margin-bottom:8px;font-size:12px;font-weight:600;color:var(--dark)}.form-group label span{color:var(--primary)}.form-control{width:100%;padding:12px 14px;border:1px solid var(--border);border-radius:10px;font-size:13px;color:var(--text);outline:none;transition:border-color .2s;font-family:inherit}.form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(227,10,23,.1)}textarea.form-control{resize:vertical}.form-card{background:#fafbfc;border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:20px}.form-card h4{margin:0 0 16px;font-size:14px;font-weight:700;color:var(--dark)}
.modal-backdrop{position:fixed;inset:0;background:rgba(16,16,19,.65);backdrop-filter:blur(4px);z-index:2000;display:none;align-items:center;justify-content:center;padding:20px}.modal-backdrop.show{display:flex}.modal-box{background:var(--white);border-radius:20px;max-width:440px;width:100%;padding:30px;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,.3);animation:modalFadeIn .2s ease}@keyframes modalFadeIn{from{transform:scale(.95);opacity:0}to{transform:scale(1);opacity:1}}.modal-icon-del{width:65px;height:65px;border-radius:50%;background:var(--primary-soft);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 16px}.modal-box h4{margin:0 0 8px;font-size:18px;color:var(--dark)}.modal-box p{margin:0 0 22px;font-size:13px;color:var(--muted);line-height:1.6}.modal-actions{display:grid;grid-template-columns:1fr 1fr;gap:12px}
@media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr)}.form-grid{grid-template-columns:1fr}.search-box input{width:200px}}@media(max-width:900px){.sidebar{transform:translateX(-100%)}.sidebar.show{transform:translateX(0)}.main-wrapper{margin-left:0}.menu-toggle.mobile-only{display:flex!important;align-items:center;justify-content:center;width:42px;height:42px}.content-body{padding:20px 16px}.topbar{padding:0 16px;min-height:64px}}
@media(max-width:768px){.stats-grid{grid-template-columns:1fr}.filter-form{display:grid;grid-template-columns:1fr 1fr;gap:10px;width:100%}.filter-form .search-box{grid-column:1/-1;width:100%}.filter-form .search-box input{width:100%}.filter-form .select-filter{width:100%}.panel-header{flex-direction:column;align-items:flex-start}.panel-actions{width:100%}.panel-actions .btn-primary{width:100%;justify-content:center}.form-grid{padding:16px}}

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
    
/* FIX mobile topbar subtitle hide (topbar-title) */
@media(max-width:767px){
  .topbar{padding:12px 16px !important;min-height:70px !important}
  .topbar-title h2{font-size:18px !important}
  .topbar-title p{display:none !important}
}

@media(max-width:767px){
  .btn-view-site span{display:none !important}
  .btn-view-site{width:42px !important;padding:0 !important;justify-content:center !important}
  .topbar-right{gap:8px !important}
}

@media(max-width:767px){
  .view-website-button span{display:none !important}
  .view-website-button{width:42px !important;padding:0 !important;justify-content:center !important}
}


        /* OVERRIDE topbar to match Kelola (72px) */
        .topbar{height:72px !important;min-height:72px !important;padding:0 32px !important}
        @media(max-width:900px){.topbar{padding:0 16px !important;min-height:64px !important}}
        @media(max-width:767px){
          .topbar{padding:12px 16px !important;min-height:70px !important}
          .topbar-title h2{font-size:18px !important}
          .topbar-title p{display:none !important}
          .btn-view-site span,.view-website-button span{display:none !important}
          .btn-view-site,.view-website-button{width:42px !important;padding:0 !important;justify-content:center !important}
        }
</style>
</head>

<body>

    <div class="admin-layout">

    <div
        class="sidebar-overlay"
        id="sidebarOverlay"
    ></div>

    <aside
        class="sidebar"
        id="adminSidebar"
    >
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

            <a href="dashboard.php" class="sidebar-link active">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>

            <span class="menu-label">Kelola Konten</span>

            <a href="berita.php" class="sidebar-link">
                <i class="fa-solid fa-newspaper"></i>
                <span>Kelola Berita</span>
            </a>
            <a href="kegiatan.php" class="sidebar-link">
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

    <div class="main-wrapper">

        <header class="topbar">
            <div class="topbar-title">
                <button type="button" class="menu-toggle mobile-only" id="menuToggle"><i class="fa-solid fa-bars"></i></button>
                <button type="button" class="collapse-toggle" id="collapseToggle" aria-label="Toggle Sidebar"><i class="fa-solid fa-angles-left" id="collapseIcon"></i></button>
                <div>
                    <h2>Dashboard</h2>
                    <p style="margin:0;font-size:11px;color:var(--muted);">Panel pengelolaan website HIPPMI</p>
                </div>
            </div>
            <div class="topbar-right"><a href="../beranda.php" class="btn-view-site" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i><span>Lihat Website</span></a></div>
        </header>

        <div class="content-body" style="padding:0; background:transparent; border:none; box-shadow:none;"><main class="dashboard-main">

            <section class="welcome-card">

                <div class="welcome-content">

                    <span class="welcome-label">
                        Selamat datang
                    </span>

                    <h2>
                        Halo, <?= escape($adminUsername); ?>!
                    </h2>

                    <p>
                        Anda telah masuk ke halaman administrasi
                        website HIPPMI.
                    </p>

                    <div class="login-time">
                        <i class="fa-regular fa-clock"></i>

                        Login pada:

                        <strong>
                            <?= escape($loginTime); ?> WIB
                        </strong>
                    </div>

                </div>

                <div class="welcome-icon">
                    <i class="fa-solid fa-user-shield"></i>
                </div>

            </section>

            <section class="statistics">

                <article class="stat-card">
                    <div class="stat-icon red">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>

                    <div class="stat-info">
                        <span>Total Berita</span>
                        <strong><?= $totalBerita; ?></strong>
                        <small>Tersimpan di database</small>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>

                    <div class="stat-info">
                        <span>Total Kegiatan</span>
                        <strong><?= $totalKegiatan; ?></strong>
                        <small>Tersimpan di database</small>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon green">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                    <div class="stat-info">
                        <span>Status Website</span>
                        <strong class="status">Aktif</strong>
                        <small>Website dapat diakses</small>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>

                    <div class="stat-info">
                        <span>Status Login</span>
                        <strong class="status">Aman</strong>
                        <small>Session admin aktif</small>
                    </div>
                </article>

            </section>

            <section class="dashboard-grid">

                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <h3>Grafik Konten</h3>
                            <p>Status berita & sebaran kategori</p>
                        </div>
                        <i class="fa-solid fa-chart-simple" style="color:var(--primary)"></i>
                    </div>
                    <div style="display:grid;grid-template-columns:110px 1fr;gap:16px;align-items:center;">
                        <div style="width:110px;height:110px;border-radius:50%;background:conic-gradient(var(--primary) 0 calc(<?= $totalBerita+$totalKegiatan>0 ? ($beritaPublished/($totalBerita+$totalKegiatan)*360) : 0 ?>deg),#e5e7eb 0 360deg);display:grid;place-items:center;">
                            <div style="width:74px;height:74px;border-radius:50%;background:#fff;display:grid;place-items:center;flex-direction:column;text-align:center;line-height:1.1;">
                                <span style="font-size:18px;font-weight:800;color:var(--dark)"><?= $beritaPublished ?></span>
                                <span style="font-size:8px;color:var(--muted)">Published</span>
                            </div>
                        </div>
                        <div style="display:grid;gap:8px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:#fff0f1;border:1px solid #ffd0d3;border-radius:8px;font-size:11px;"><span><i class="fa-solid fa-circle" style="color:var(--primary);font-size:7px;margin-right:6px"></i>Published</span><strong><?= $beritaPublished ?></strong></div>
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:#f3f4f6;border:1px solid var(--border);border-radius:8px;font-size:11px;"><span><i class="fa-solid fa-circle" style="color:#9ca3af;font-size:7px;margin-right:6px"></i>Draft</span><strong><?= $beritaDraft ?></strong></div>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:2px;">
                                <?php foreach($chartKategori as $i=>$k): $w = ($chartKategoriCount[$i]/max(1,($totalBerita+$totalKegiatan)))*100; ?>
                                <span style="font-size:9px;padding:4px 8px;background:#fafafd;border:1px solid var(--border);border-radius:999px"><?= escape($k) ?> · <?= (int)$chartKategoriCount[$i] ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:14px;display:grid;grid-template-columns:repeat(4,1fr);gap:10px;text-align:center;">
                        <div style="padding:10px;background:#fff0f1;border:1px solid #ffd0d3;border-radius:10px"><div style="font-size:16px;font-weight:800;color:var(--primary)"><?= $totalBerita ?></div><div style="font-size:9px;color:var(--muted)">Berita</div></div>
                        <div style="padding:10px;background:#ecf5ff;border:1px solid #bfdbfe;border-radius:10px"><div style="font-size:16px;font-weight:800;color:#176abc"><?= $totalKegiatan ?></div><div style="font-size:9px;color:var(--muted)">Kegiatan</div></div>
                        <div style="padding:10px;background:#f3f4f6;border:1px solid var(--border);border-radius:10px"><div style="font-size:16px;font-weight:800;color:var(--dark)"><?= $totalStruktur ?></div><div style="font-size:9px;color:var(--muted)">Struktur</div></div>
                        <div style="padding:10px;background:#fef3c7;border:1px solid #fde68a;border-radius:10px"><div style="font-size:16px;font-weight:800;color:#92400e"><?= $totalCore ?></div><div style="font-size:9px;color:var(--muted)">Core Values</div></div>
                    </div>
                    <div style="margin-top:12px;height:6px;background:#f3f4f6;border-radius:999px;overflow:hidden;display:flex">
                        <div style="width:<?= $totalBerita+$totalKegiatan>0 ? round($totalBerita/($totalBerita+$totalKegiatan)*100) : 0 ?>%;background:var(--primary)"></div>
                        <div style="flex:1;background:#93c5fd"></div>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:9px;color:var(--muted);margin-top:4px"><span>Berita <?= $totalBerita+$totalKegiatan>0 ? round($totalBerita/($totalBerita+$totalKegiatan)*100) : 0 ?>%</span><span>Kegiatan <?= $totalBerita+$totalKegiatan>0 ? round($totalKegiatan/($totalBerita+$totalKegiatan)*100) : 0 ?>%</span></div>
                </div>

                <div class="panel">

                    <div class="panel-header">
                        <div>
                            <h3>Informasi Sistem</h3>

                            <p>
                                Status administrasi
                            </p>
                        </div>

                        <i class="fa-solid fa-circle-info"></i>
                    </div>

                    <div class="system-row">
                        <span>Nama aplikasi</span>
                        <strong>Admin HIPPMI</strong>
                    </div>

                    <div class="system-row">
                        <span>Versi PHP</span>
                        <strong><?= escape(PHP_VERSION); ?></strong>
                    </div>

                    <div class="system-row">
                        <span>Zona waktu</span>
                        <strong>Asia/Jakarta</strong>
                    </div>

                    <div class="system-row">
                        <span>Session</span>

                        <strong class="active-status">
                            Aktif
                        </strong>
                    </div>

                </div>

            </section>

        </main>
            </div>

        <footer class="footer">
            <p>
                &copy; <?= date('Y'); ?> HIPPMI.
                Seluruh hak dilindungi.
            </p>
        </footer>

    </div>

    </div>

    <!-- Modal logout -->
    <div
        class="logout-modal"
        id="logoutModal"
        aria-hidden="true"
    >
        <div
            class="logout-overlay"
            id="logoutOverlay"
        ></div>

        <div
            class="logout-dialog"
            role="dialog"
            aria-modal="true"
        >
            <button
                type="button"
                class="modal-close"
                id="closeLogoutModal"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="modal-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>

            <h3>Konfirmasi Keluar</h3>

            <p>
                Apakah Anda yakin ingin keluar dari dashboard admin?
            </p>

            <!-- Form logout langsung -->
            <form
                method="post"
                action="logout.php"
                id="logoutForm"
            ><?= csrfField() ?>
                <div class="modal-actions">

                    <button
                        type="button"
                        class="cancel-button"
                        id="cancelLogout"
                    >
                        <i class="fa-solid fa-xmark"></i>
                        Tidak
                    </button>

                    <button
                        type="submit"
                        class="confirm-button"
                        id="confirmLogout"
                    >
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Ya, Keluar
                    </button>

                </div>
            </form>
        </div>
    </div>

    <script>
const sidebar=document.getElementById('adminSidebar'),overlay=document.getElementById('sidebarOverlay'),menuToggle=document.getElementById('menuToggle'),sidebarClose=document.getElementById('sidebarClose'),collapseToggle=document.getElementById('collapseToggle'),collapseIcon=document.getElementById('collapseIcon');
function isDesktop(){return window.innerWidth>900}
function openSidebar(){sidebar.classList.add('show');overlay.classList.add('show');}
function closeSidebar(){sidebar.classList.remove('show');overlay.classList.remove('show');}
if(menuToggle)menuToggle.addEventListener('click',()=>{if(!isDesktop())openSidebar()});
if(sidebarClose)sidebarClose.addEventListener('click',closeSidebar);
if(overlay)overlay.addEventListener('click',closeSidebar);
function updateCollapseIcon(){ if(!collapseIcon) return; const c=document.body.classList.contains('sidebar-collapsed'); collapseIcon.className=c?'fa-solid fa-angles-right':'fa-solid fa-angles-left';}
if(collapseToggle){ if(localStorage.getItem('hippmi_sidebar_collapsed')==='1'&&isDesktop()){document.body.classList.add('sidebar-collapsed');updateCollapseIcon();} collapseToggle.addEventListener('click',()=>{document.body.classList.toggle('sidebar-collapsed');localStorage.setItem('hippmi_sidebar_collapsed',document.body.classList.contains('sidebar-collapsed')?'1':'0');updateCollapseIcon();});}
window.addEventListener('resize',()=>{if(window.innerWidth>900)closeSidebar();else{document.body.classList.remove('sidebar-collapsed');updateCollapseIcon();}});
const openLogoutBtn=document.getElementById('openLogoutBtn'),logoutModal=document.getElementById('logoutModal');
if(openLogoutBtn)openLogoutBtn.addEventListener('click',()=>{logoutModal.classList.add('show');});
function closeLogout(){logoutModal.classList.remove('show');}
if(logoutModal) logoutModal.addEventListener('click',e=>{if(e.target===logoutModal)closeLogout()});
function openDeleteModal(btn){const url=btn.getAttribute('data-delete-url'),title=btn.getAttribute('data-delete-title');document.getElementById('confirmDeleteBtn').href=url;document.getElementById('deleteModalText').textContent='Hapus "'+title+'" ? Tidak dapat dibatalkan.';document.getElementById('deleteModal').classList.add('show');}
function closeDeleteModal(){document.getElementById('deleteModal').classList.remove('show');}
document.getElementById('deleteModal').addEventListener('click',e=>{if(e.target===document.getElementById('deleteModal'))closeDeleteModal()});
document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeDeleteModal();closeLogout();closeSidebar();}});
const selectAll=document.getElementById('selectAll'),rowCheckboxes=document.querySelectorAll('.row-checkbox'),bulkBar=document.getElementById('bulkBar'),bulkCount=document.getElementById('bulkCount'),bulkCancel=document.getElementById('bulkCancel');
function updateBulk(){const c=document.querySelectorAll('.row-checkbox:checked').length;bulkCount.textContent=c; if(c>0) bulkBar.classList.add('show'); else bulkBar.classList.remove('show'); if(selectAll) selectAll.checked=c===rowCheckboxes.length&&c>0;}
if(selectAll) selectAll.addEventListener('change',()=>{rowCheckboxes.forEach(cb=>cb.checked=selectAll.checked);updateBulk();});
rowCheckboxes.forEach(cb=>cb.addEventListener('change',updateBulk));
if(bulkCancel) bulkCancel.addEventListener('click',()=>{rowCheckboxes.forEach(cb=>cb.checked=false);if(selectAll)selectAll.checked=false;updateBulk();});
document.getElementById('bulkDeleteForm').addEventListener('submit',e=>{const c=document.querySelectorAll('.row-checkbox:checked').length; if(c===0){e.preventDefault();alert('Pilih minimal 1 data.');} else if(!confirm('Hapus '+c+' data terpilih?')) e.preventDefault();});
const fotoInput=document.getElementById('fotoFileInput'),fotoPreview=document.getElementById('fotoPreview');
if(fotoInput&&fotoPreview) fotoInput.addEventListener('change',()=>{const f=fotoInput.files[0]; if(!f) return; const url=URL.createObjectURL(f); fotoPreview.innerHTML='<img src="'+url+'" alt="preview">';});
setTimeout(()=>{document.querySelectorAll('.alert').forEach(a=>{a.classList.add('alert-hide');setTimeout(()=>a.style.display='none',500)});},4000);
</script>

</body>
</html>