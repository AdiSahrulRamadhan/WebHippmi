<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
requireAdminLogin();
require_once __DIR__ . '/../koneksi.php';
$pdo = getDBConnection();
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
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM `struktur_organisasi` WHERE `id`=:id LIMIT 1");
    $stmt->execute(['id'=>$id]);
    $item = $stmt->fetch();
    if ($item) {
        if (!empty($item['foto']) && str_starts_with($item['foto'],'uploads/struktur/')) {
            $fp = __DIR__ . '/../' . $item['foto']; if(file_exists($fp)) @unlink($fp);
        }
        $pdo->prepare("DELETE FROM `struktur_organisasi` WHERE `id`=:id")->execute(['id'=>$id]);
        $_SESSION['flash_success']='Jabatan "'.$item['nama_jabatan'].'" berhasil dihapus.';
    } else $_SESSION['flash_error']='Data tidak ditemukan.';
    redirectTo('struktur.php');
}
if ($action === 'bulk_delete' && $_SERVER['REQUEST_METHOD']==='POST') {
    $ids = $_POST['ids'] ?? [];
    if(!is_array($ids)) $ids=[$ids];
    $ids=array_values(array_filter(array_map('intval',$ids),fn($v)=>$v>0));
    if(empty($ids)){ $_SESSION['flash_error']='Tidak ada data yang dipilih.'; redirectTo('struktur.php'); }
    $ph=implode(',',array_fill(0,count($ids),'?'));
    $sel=$pdo->prepare("SELECT `foto` FROM `struktur_organisasi` WHERE `id` IN ($ph)"); $sel->execute($ids);
    foreach($sel->fetchAll() as $r){ if(!empty($r['foto'])&&str_starts_with($r['foto'],'uploads/struktur/')){ $fp=__DIR__.'/../'.$r['foto']; if(file_exists($fp))@unlink($fp);} }
    $pdo->prepare("DELETE FROM `struktur_organisasi` WHERE `id` IN ($ph)")->execute($ids);
    $_SESSION['flash_success']=count($ids).' data berhasil dihapus.'; redirectTo('struktur.php');
}
if ($action==='toggle_status' && $id>0){
    $stmt=$pdo->prepare("SELECT status,nama_jabatan FROM `struktur_organisasi` WHERE `id`=:id LIMIT 1"); $stmt->execute(['id'=>$id]); $it=$stmt->fetch();
    if($it){ $ns=$it['status']==='published'?'draft':'published'; $pdo->prepare("UPDATE `struktur_organisasi` SET `status`=:st WHERE `id`=:id")->execute(['st'=>$ns,'id'=>$id]); $_SESSION['flash_success']='Status "'.$it['nama_jabatan'].'" diubah ke '.$ns; }
    redirectTo('struktur.php');
}
if ($action==='move' && $id>0){
    $dir=$_GET['dir']??'up';
    $cur=$pdo->prepare("SELECT id,urutan FROM `struktur_organisasi` WHERE `id`=:id LIMIT 1"); $cur->execute(['id'=>$id]); $c=$cur->fetch();
    if($c){
        if($dir==='up'){
            $other=$pdo->prepare("SELECT id,urutan FROM `struktur_organisasi` WHERE `urutan` < :u ORDER BY `urutan` DESC, `id` DESC LIMIT 1"); $other->execute(['u'=>$c['urutan']]);
        } else {
            $other=$pdo->prepare("SELECT id,urutan FROM `struktur_organisasi` WHERE `urutan` > :u ORDER BY `urutan` ASC, `id` ASC LIMIT 1"); $other->execute(['u'=>$c['urutan']]);
        }
        $o=$other->fetch();
        if($o){ $pdo->prepare("UPDATE `struktur_organisasi` SET `urutan`=:u WHERE `id`=:id")->execute(['u'=>$o['urutan'],'id'=>$c['id']]); $pdo->prepare("UPDATE `struktur_organisasi` SET `urutan`=:u WHERE `id`=:id")->execute(['u'=>$c['urutan'],'id'=>$o['id']]); }
    }
    redirectTo('struktur.php');
}

$formErrors=[];
$formData=['nama_jabatan'=>'','deskripsi'=>'','icon'=>'fa-sitemap','foto'=>'','urutan'=>0,'status'=>'published'];
$removeExistingFoto=false;
$iconOptions=['fa-crown'=>'Ketua Umum','fa-scale-balanced'=>'Dewan Penasihat','fa-user-graduate'=>'Dewan Pakar','fa-file-pen'=>'Sekretaris','fa-coins'=>'Bendahara','fa-person-chalkboard'=>'Edukasi','fa-magnifying-glass-chart'=>'Riset','fa-handshake'=>'Kolaborasi','fa-sitemap'=>'Umum','fa-users'=>'Users','fa-star'=>'Star','fa-shield-halved'=>'Shield'];

if($action==='edit' && $id>0){
    $stmt=$pdo->prepare("SELECT * FROM `struktur_organisasi` WHERE `id`=:id LIMIT 1"); $stmt->execute(['id'=>$id]); $existing=$stmt->fetch();
    if(!$existing){ $_SESSION['flash_error']='Data tidak ditemukan.'; redirectTo('struktur.php'); }
    if($_SERVER['REQUEST_METHOD']!=='POST') $formData=$existing;
}
if($_SERVER['REQUEST_METHOD']==='POST' && in_array($action,['create','edit'],true)){
    $formData['nama_jabatan']=trim((string)($_POST['nama_jabatan']??''));
    $formData['deskripsi']=trim((string)($_POST['deskripsi']??''));
    $formData['icon']=trim((string)($_POST['icon']??'fa-sitemap'));
    $formData['urutan']=(int)($_POST['urutan']??0);
    $formData['status']=in_array($_POST['status']??'', ['published','draft'],true)?$_POST['status']:'published';
    $customFotoUrl=trim((string)($_POST['foto_url']??''));
    $removeExistingFoto=$action==='edit' && ($_POST['hapus_foto']??'0')==='1';
    if($formData['nama_jabatan']==='') $formErrors[]='Nama jabatan wajib diisi.';
    if($formData['deskripsi']==='') $formErrors[]='Deskripsi wajib diisi.';
    if($customFotoUrl!==''){
        $fotoUrlScheme=strtolower((string)parse_url($customFotoUrl,PHP_URL_SCHEME));
        if(filter_var($customFotoUrl,FILTER_VALIDATE_URL)===false || !in_array($fotoUrlScheme,['http','https'],true)){
            $formErrors[]='URL foto harus berupa alamat http:// atau https:// yang valid.';
        }
    }
    if($formData['urutan']<=0){
        $maxU=(int)$pdo->query("SELECT COALESCE(MAX(urutan),0) FROM `struktur_organisasi`")->fetchColumn();
        $formData['urutan']=$maxU+1;
    }
    $uploadedFoto=null;
    if(isset($_FILES['foto_file']) && $_FILES['foto_file']['error']===UPLOAD_ERR_OK){
        $f=$_FILES['foto_file']; $ext=strtolower(pathinfo($f['name'],PATHINFO_EXTENSION)??'');
        if(!in_array($ext,['jpg','jpeg','png','webp','gif'],true)) $formErrors[]='Format foto tidak didukung.';
        elseif($f['size']>2*1024*1024) $formErrors[]='Ukuran foto maksimal 2 MB.';
        else {
            $dir=__DIR__.'/../uploads/struktur/'; if(!is_dir($dir)) @mkdir($dir,0777,true);
            $name='struktur_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext; $dest=$dir.$name;
            if(move_uploaded_file($f['tmp_name'],$dest)){ $uploadedFoto='uploads/struktur/'.$name; } else $formErrors[]='Gagal mengunggah foto.';
        }
    }
    if($uploadedFoto!==null) $formData['foto']=$uploadedFoto;
    elseif($customFotoUrl!=='') $formData['foto']=$customFotoUrl;
    elseif($removeExistingFoto) $formData['foto']='';
    elseif($action==='edit') $formData['foto']=$existing['foto']??'';

    if(empty($formErrors)){
        $slug=createSlug($formData['nama_jabatan']);
        $chk=$pdo->prepare("SELECT id FROM `struktur_organisasi` WHERE `slug`=:slug".($action==='edit'?" AND `id`!=:id":"")." LIMIT 1");
        $p=['slug'=>$slug]; if($action==='edit')$p['id']=$id; $chk->execute($p); if($chk->fetch()) $slug.='-'.time();
        if($action==='create'){
            $pdo->prepare("INSERT INTO `struktur_organisasi` (`nama_jabatan`,`slug`,`deskripsi`,`icon`,`foto`,`urutan`,`status`) VALUES (:nama_jabatan,:slug,:deskripsi,:icon,:foto,:urutan,:status)")->execute(['nama_jabatan'=>$formData['nama_jabatan'],'slug'=>$slug,'deskripsi'=>$formData['deskripsi'],'icon'=>$formData['icon'],'foto'=>$formData['foto']?:null,'urutan'=>$formData['urutan'],'status'=>$formData['status']]);
            $_SESSION['flash_success']='Struktur "'.$formData['nama_jabatan'].'" berhasil ditambahkan.'; redirectTo('struktur.php');
        } else {
            $pdo->prepare("UPDATE `struktur_organisasi` SET `nama_jabatan`=:nama_jabatan,`slug`=:slug,`deskripsi`=:deskripsi,`icon`=:icon,`foto`=:foto,`urutan`=:urutan,`status`=:status WHERE `id`=:id")->execute(['nama_jabatan'=>$formData['nama_jabatan'],'slug'=>$slug,'deskripsi'=>$formData['deskripsi'],'icon'=>$formData['icon'],'foto'=>$formData['foto']?:null,'urutan'=>$formData['urutan'],'status'=>$formData['status'],'id'=>$id]);
            $oldFoto=(string)($existing['foto']??'');
            if($oldFoto!=='' && $oldFoto!==(string)$formData['foto'] && str_starts_with($oldFoto,'uploads/struktur/')){
                $oldFile=__DIR__.'/../'.$oldFoto;
                if(file_exists($oldFile)) @unlink($oldFile);
            }
            $_SESSION['flash_success']='Struktur berhasil diperbarui.'; redirectTo('struktur.php');
        }
    }
}

$searchKeyword=trim((string)($_GET['q']??''));
$filterStatus=trim((string)($_GET['status']??''));
$sql="SELECT * FROM `struktur_organisasi` WHERE 1=1"; $params=[];
if($searchKeyword!==''){ $sql.=" AND (`nama_jabatan` LIKE :q1 OR `deskripsi` LIKE :q2)"; $params['q1']='%'.$searchKeyword.'%'; $params['q2']='%'.$searchKeyword.'%'; }
if($filterStatus!==''){ $sql.=" AND `status`=:st"; $params['st']=$filterStatus; }
$allowedSort=['urutan'=>'urutan','nama_jabatan'=>'nama_jabatan','status'=>'status','id'=>'id'];
$sortKey=trim((string)($_GET['sort']??'urutan')); if(!isset($allowedSort[$sortKey]))$sortKey='urutan';
$sortDir=strtolower(trim((string)($_GET['dir']??'asc')))==='desc'?'DESC':'ASC';
$sortCol=$allowedSort[$sortKey];
$sql.=" ORDER BY `$sortCol` $sortDir, `id` ASC";
$stmt=$pdo->prepare($sql); $stmt->execute($params); $list=$stmt->fetchAll();
function strukturSortUrl(string $col,string $curKey,string $curDir):string{ $p=$_GET; $p['sort']=$col; $p['dir']=($curKey===$col&&$curDir==='ASC')?'desc':'asc'; return 'struktur.php?'.http_build_query($p); }
function strukturSortIcon(string $col,string $curKey,string $curDir):string{ if($curKey!==$col) return ' <i class="fa-solid fa-sort" style="opacity:.35;margin-left:4px;"></i>'; return $curDir==='ASC'?' <i class="fa-solid fa-sort-up" style="margin-left:4px;color:var(--primary);"></i>':' <i class="fa-solid fa-sort-down" style="margin-left:4px;color:var(--primary);"></i>'; }
$statTotal=(int)$pdo->query("SELECT COUNT(*) FROM `struktur_organisasi`")->fetchColumn();
$statPublished=(int)$pdo->query("SELECT COUNT(*) FROM `struktur_organisasi` WHERE `status`='published'")->fetchColumn();
$statDraft=(int)$pdo->query("SELECT COUNT(*) FROM `struktur_organisasi` WHERE `status`='draft'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Kelola Struktur Organisasi - Admin HIPPMI</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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

</style>
</head>
<body>
<div class="admin-layout">
<div class="sidebar-overlay" id="sidebarOverlay"></div>
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
            <a href="kegiatan.php" class="sidebar-link">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Kelola Kegiatan</span>
            </a>
            <a href="struktur.php" class="sidebar-link active">
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
<div class="topbar-title"><button type="button" class="menu-toggle mobile-only" id="menuToggle"><i class="fa-solid fa-bars"></i></button><button type="button" class="collapse-toggle" id="collapseToggle"><i class="fa-solid fa-angles-left" id="collapseIcon"></i></button><div><h2>Kelola Struktur Organisasi</h2><p style="margin:0;font-size:11px;color:var(--muted);">Atur jabatan & urutan tampil di beranda</p></div></div>
<div class="topbar-right"><a href="../beranda.php#struktur" class="btn-view-site" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i><span>Lihat Publik</span></a></div>
        </header>
<div class="content-body">
<?php if($flashSuccess): ?><div class="alert alert-success" id="flashSuccess"><i class="fa-solid fa-circle-check"></i><span><?= escape($flashSuccess) ?></span></div><?php endif; ?>
<?php if($flashError): ?><div class="alert alert-danger" id="flashError"><i class="fa-solid fa-circle-exclamation"></i><span><?= escape($flashError) ?></span></div><?php endif; ?>

<?php if(in_array($action,['create','edit'],true)): ?>
<div class="panel">
<div class="panel-header"><div><h3><?= $action==='create'?'Tambah Jabatan':'Edit Jabatan' ?></h3><p><?= $action==='create'?'Tambah struktur baru yang tampil di beranda':'Perbarui data struktur' ?></p></div><a href="struktur.php" class="btn-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali</a></div>
<?php if(!empty($formErrors)): ?><div style="margin:20px 26px 0;padding:14px 16px;background:#fff0f1;border:1px solid #fecdd3;border-radius:12px;color:#e30a17;font-size:12px;"><strong><i class="fa-solid fa-triangle-exclamation"></i> Periksa kembali:</strong><ul style="margin:8px 0 0;padding-left:18px;"><?php foreach($formErrors as $e): ?><li><?= escape($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" action="struktur.php?action=<?= $action ?><?= $action==='edit'?'&id='.$id:'' ?>">
<div class="form-grid">
<div>
<div class="form-group"><label>Nama Jabatan <span>*</span></label><input type="text" name="nama_jabatan" class="form-control" placeholder="Contoh: Ketua Umum" value="<?= escape($formData['nama_jabatan']) ?>" required></div>
<div class="form-group"><label>Deskripsi <span>*</span></label><textarea name="deskripsi" class="form-control" rows="5" placeholder="Deskripsi tugas & peran jabatan" required><?= escape($formData['deskripsi']) ?></textarea></div>
<div class="form-group"><label>Icon (Font Awesome)</label><select name="icon" class="form-control">
<?php foreach($iconOptions as $val=>$label): ?><option value="<?= escape($val) ?>" <?= $formData['icon']===$val?'selected':'' ?>><?= escape($label) ?> (<?= escape($val) ?>)</option><?php endforeach; ?>
</select><small style="font-size:11px;color:var(--muted);">Preview: <i class="fa-solid <?= escape($formData['icon']) ?>" style="color:var(--primary);"></i> <?= escape($formData['icon']) ?></small></div>
<div class="form-group"><label>Urutan Tampil</label><input type="number" name="urutan" class="form-control" min="1" value="<?= (int)$formData['urutan'] ?>"><small style="font-size:11px;color:var(--muted);">Angka kecil tampil lebih atas. Kosongkan untuk otomatis di akhir.</small></div>
</div>
<div>
<div class="form-card"><h4><i class="fa-solid fa-image" style="color:var(--primary);"></i> Foto (opsional)</h4>
<div class="form-group"><label>Upload Foto</label><input type="file" name="foto_file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif" id="fotoFileInput"><small style="font-size:11px;color:var(--muted);">JPG/PNG/WEBP maks 2MB. Jika ada foto, icon tidak tampil.</small></div>
<div class="form-group"><label for="fotoUrlInput">Atau URL Foto</label><input type="url" name="foto_url" id="fotoUrlInput" class="form-control" placeholder="https://contoh.com/foto.jpg" value="<?= escape(!empty($formData['foto']) && !str_starts_with((string)$formData['foto'],'uploads/') ? (string)$formData['foto'] : '') ?>"><small style="font-size:11px;color:var(--muted);">Gunakan URL lengkap yang diawali http:// atau https://.</small></div>
<div class="image-preview-container" id="fotoPreview"><?php if($removeExistingFoto): ?><div class="image-preview-empty" style="color:var(--primary);"><i class="fa-solid fa-trash-can"></i>Gambar akan dihapus setelah perubahan disimpan</div><?php elseif(!empty($formData['foto'])): ?><img src="<?= escape(str_starts_with((string)$formData['foto'],'uploads/')?'../'.$formData['foto']:$formData['foto']) ?>" alt="Preview" style="width:100%;height:100%;object-fit:cover;"><?php else: ?><div class="image-preview-empty"><i class="fa-solid fa-image"></i>Belum ada foto</div><?php endif; ?></div>
<?php if($action==='edit' && !empty($existing['foto'])):
$storedFoto=(string)$existing['foto'];
$storedFotoIsUpload=str_starts_with($storedFoto,'uploads/');
$storedFotoSrc=$storedFotoIsUpload?'../'.$storedFoto:$storedFoto;
$deleteFotoLabel=$storedFotoIsUpload?'Hapus Gambar Saat Ini':'Hapus URL Saat Ini';
?>
<input type="hidden" name="hapus_foto" id="hapusFotoInput" value="<?= $removeExistingFoto?'1':'0' ?>">
<button type="button" class="btn-secondary" id="hapusFotoBtn" data-delete-label="<?= escape($deleteFotoLabel) ?>" data-original-src="<?= escape($storedFotoSrc) ?>" data-original-url="<?= escape($storedFotoIsUpload?'':$storedFoto) ?>" style="width:100%;justify-content:center;margin-top:12px;color:var(--primary);border-color:#fecdd3;background:var(--primary-soft);"><i class="fa-solid <?= $removeExistingFoto?'fa-rotate-left':'fa-trash-can' ?>"></i> <?= $removeExistingFoto?'Batalkan Hapus':escape($deleteFotoLabel) ?></button>
<small id="hapusFotoHelp" style="display:block;margin-top:7px;font-size:11px;color:var(--muted);text-align:center;"><?= $removeExistingFoto?'Penghapusan akan dilakukan saat tombol Simpan Perubahan ditekan.':'Gambar/URL baru dapat dipilih sebagai pengganti.' ?></small>
<?php endif; ?>
</div>
<div class="form-card"><h4><i class="fa-solid fa-toggle-on" style="color:var(--primary);"></i> Status</h4>
<div class="form-group"><label>Status Publikasi</label><select name="status" class="form-control"><option value="published" <?= $formData['status']==='published'?'selected':'' ?>>Published — tampil di publik</option><option value="draft" <?= $formData['status']==='draft'?'selected':'' ?>>Draft — sembunyikan</option></select></div>
</div>
<button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:13px;"><i class="fa-solid fa-floppy-disk"></i> <?= $action==='create'?'Simpan Struktur':'Simpan Perubahan' ?></button>
<a href="struktur.php" class="btn-secondary" style="width:100%;justify-content:center;margin-top:10px;">Batal</a>
</div>
</div>
</form>
</div>
<?php else: ?>
<div class="stats-grid">
<div class="stat-card"><div class="stat-icon red"><i class="fa-solid fa-sitemap"></i></div><div class="stat-info"><span>Total Jabatan</span><strong><?= $statTotal ?></strong></div></div>
<div class="stat-card"><div class="stat-icon green"><i class="fa-solid fa-eye"></i></div><div class="stat-info"><span>Published</span><strong><?= $statPublished ?></strong></div></div>
<div class="stat-card"><div class="stat-icon yellow"><i class="fa-solid fa-eye-slash"></i></div><div class="stat-info"><span>Draft</span><strong><?= $statDraft ?></strong></div></div>
</div>
<div class="panel">
<div class="panel-header"><div><h3>Daftar Struktur Organisasi</h3><p>Urutkan dengan tombol ↑↓ — urutan menentukan posisi di beranda</p></div><div class="panel-actions"><a href="struktur.php?action=create" class="btn-primary"><i class="fa-solid fa-plus"></i> Tambah Jabatan</a></div></div>
<div class="filter-bar">
<form method="get" action="struktur.php" class="filter-form" id="filterForm">
<div class="search-box"><i class="fa-solid fa-magnifying-glass"></i><input type="text" name="q" placeholder="Cari jabatan / deskripsi..." value="<?= escape($searchKeyword) ?>"></div>
<select name="status" class="select-filter"><option value="">Semua Status</option><option value="published" <?= $filterStatus==='published'?'selected':'' ?>>Published</option><option value="draft" <?= $filterStatus==='draft'?'selected':'' ?>>Draft</option></select>
<button type="submit" class="btn-secondary"><i class="fa-solid fa-filter"></i> Filter</button>
<a href="struktur.php" class="btn-secondary">Reset</a>
</form>
</div>
<form method="post" action="struktur.php?action=bulk_delete" id="bulkDeleteForm">
<div class="bulk-bar" id="bulkBar"><div><strong id="bulkCount">0</strong> dipilih</div><div style="display:flex;gap:8px;"><button type="button" class="btn-secondary" style="padding:7px 14px;font-size:12px;" id="bulkCancel">Batal</button><button type="submit" class="btn-primary" style="background:#e30a17;padding:7px 14px;font-size:12px;" id="bulkDeleteBtn"><i class="fa-solid fa-trash"></i> Hapus Terpilih</button></div></div>
<div class="table-responsive">
<table class="data-table"><thead><tr><th class="checkbox-cell" style="width:72px;text-align:center;"><label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;font-size:11px;font-weight:700;color:var(--muted);"><input type="checkbox" id="selectAll" title="Pilih semua"><span>All</span></label></th><th>Foto/Icon</th><th class="sortable"><a href="<?= escape(strukturSortUrl('urutan',$sortKey,$sortDir)) ?>">Urutan<?= strukturSortIcon('urutan',$sortKey,$sortDir) ?></a></th><th class="sortable"><a href="<?= escape(strukturSortUrl('nama_jabatan',$sortKey,$sortDir)) ?>">Jabatan<?= strukturSortIcon('nama_jabatan',$sortKey,$sortDir) ?></a></th><th>Deskripsi</th><th class="sortable"><a href="<?= escape(strukturSortUrl('status',$sortKey,$sortDir)) ?>">Status<?= strukturSortIcon('status',$sortKey,$sortDir) ?></a></th><th>Aksi</th></tr></thead><tbody>
<?php if(empty($list)): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted);"><i class="fa-solid fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>Belum ada data struktur.<br><a href="struktur.php?action=create" class="btn-primary" style="margin-top:12px;">Tambah Jabatan Pertama</a></td></tr>
<?php else: foreach($list as $row): ?>
<tr>
<td class="checkbox-cell"><input type="checkbox" class="row-checkbox" name="ids[]" value="<?= (int)$row['id'] ?>"></td>
<td><?php if(!empty($row['foto'])): ?><img src="<?= escape(str_starts_with($row['foto'],'uploads/')?'../'.$row['foto']:$row['foto']) ?>" alt="" style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid var(--border);"><?php else: ?><div style="width:44px;height:44px;border-radius:10px;background:#fff0f1;display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:18px;"><i class="fa-solid <?= escape($row['icon']) ?>"></i></div><?php endif; ?></td>
<td><span class="badge badge-urutan">#<?= (int)$row['urutan'] ?></span></td>
<td style="min-width:160px;"><strong style="font-size:13px;color:var(--dark);"><?= escape($row['nama_jabatan']) ?></strong><br><small style="font-size:10px;color:var(--muted);"><i class="fa-solid <?= escape($row['icon']) ?>" style="margin-right:4px;"></i><?= escape($row['icon']) ?></small></td>
<td style="max-width:320px;"><span style="font-size:11px;color:var(--muted);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= escape(mb_strimwidth($row['deskripsi'],0,120,'...')) ?></span></td>
<td><?php if($row['status']==='published'): ?><span class="badge badge-published"><i class="fa-solid fa-eye"></i> Published</span><?php else: ?><span class="badge badge-draft">Draft</span><?php endif; ?></td>
<td><div class="action-buttons">
<a href="struktur.php?action=move&id=<?= (int)$row['id'] ?>&dir=up" class="btn-action up" title="Naikkan"><i class="fa-solid fa-arrow-up"></i></a>
<a href="struktur.php?action=move&id=<?= (int)$row['id'] ?>&dir=down" class="btn-action up" title="Turunkan"><i class="fa-solid fa-arrow-down"></i></a>
<a href="struktur.php?action=edit&id=<?= (int)$row['id'] ?>" class="btn-action edit" title="Edit"><i class="fa-solid fa-pen"></i></a>
<button type="button" class="btn-action delete" data-delete-url="struktur.php?action=delete&id=<?= (int)$row['id'] ?>" data-delete-title="<?= escape($row['nama_jabatan']) ?>" onclick="openDeleteModal(this)"><i class="fa-solid fa-trash"></i></button>
</div></td>
</tr>
<?php endforeach; endif; ?>
</tbody></table>
</div>
</form>
</div>
<?php endif; ?>
</div>
</div>
</div>
<div class="modal-backdrop" id="deleteModal"><div class="modal-box"><div class="modal-icon-del"><i class="fa-solid fa-trash"></i></div><h4>Hapus Jabatan?</h4><p id="deleteModalText">Data akan dihapus permanen.</p><div class="modal-actions"><button type="button" class="btn-secondary" style="justify-content:center;" onclick="closeDeleteModal()">Batal</button><a href="#" id="confirmDeleteBtn" class="btn-primary" style="justify-content:center;background:var(--primary);">Hapus</a></div></div></div>
<div class="modal-backdrop" id="logoutModal" style="z-index:3000;"><div class="modal-box" style="max-width:420px;"><button type="button" style="position:absolute;top:14px;right:14px;width:36px;height:36px;border-radius:50%;border:0;background:#f3f3f5;cursor:pointer;" onclick="closeLogout()"><i class="fa-solid fa-xmark"></i></button><div class="modal-icon-del" style="background:#fff0f1;color:var(--primary);border:1px solid #ffd0d3;"><i class="fa-solid fa-right-from-bracket"></i></div><h4>Keluar Admin?</h4><p>Sesi akan diakhiri.</p><form method="post" action="logout.php"><div class="modal-actions"><button type="button" class="btn-secondary" style="justify-content:center;" onclick="closeLogout()">Batal</button><button type="submit" class="btn-primary" style="justify-content:center;">Ya, Keluar</button></div></form></div></div>
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
const bulkDeleteForm=document.getElementById('bulkDeleteForm');
if(bulkDeleteForm) bulkDeleteForm.addEventListener('submit',e=>{const c=document.querySelectorAll('.row-checkbox:checked').length; if(c===0){e.preventDefault();alert('Pilih minimal 1 data.');} else if(!confirm('Hapus '+c+' data terpilih?')) e.preventDefault();});
const fotoInput=document.getElementById('fotoFileInput'),fotoPreview=document.getElementById('fotoPreview'),fotoUrlInput=document.getElementById('fotoUrlInput'),hapusFotoInput=document.getElementById('hapusFotoInput'),hapusFotoBtn=document.getElementById('hapusFotoBtn'),hapusFotoHelp=document.getElementById('hapusFotoHelp');
function setPreviewImage(src){
    if(!fotoPreview) return;
    const img=document.createElement('img');
    img.src=src; img.alt='Preview'; img.style.width='100%'; img.style.height='100%'; img.style.objectFit='cover';
    fotoPreview.replaceChildren(img);
}
function setDeleteButtonState(pending){
    if(!hapusFotoBtn||!hapusFotoInput) return;
    hapusFotoInput.value=pending?'1':'0';
    hapusFotoBtn.innerHTML=pending?'<i class="fa-solid fa-rotate-left"></i> Batalkan Hapus':'<i class="fa-solid fa-trash-can"></i> '+hapusFotoBtn.dataset.deleteLabel;
    if(hapusFotoHelp) hapusFotoHelp.textContent=pending?'Penghapusan akan dilakukan saat tombol Simpan Perubahan ditekan.':'Gambar/URL baru dapat dipilih sebagai pengganti.';
}
if(hapusFotoBtn&&hapusFotoInput&&fotoPreview){
    hapusFotoBtn.addEventListener('click',()=>{
        const pending=hapusFotoInput.value!=='1';
        setDeleteButtonState(pending);
        if(pending){
            if(fotoInput) fotoInput.value='';
            if(fotoUrlInput) fotoUrlInput.value='';
            fotoPreview.innerHTML='<div class="image-preview-empty" style="color:var(--primary);"><i class="fa-solid fa-trash-can"></i>Gambar akan dihapus setelah perubahan disimpan</div>';
        } else {
            if(fotoUrlInput) fotoUrlInput.value=hapusFotoBtn.dataset.originalUrl||'';
            setPreviewImage(hapusFotoBtn.dataset.originalSrc||'');
        }
    });
}
if(fotoInput&&fotoPreview) fotoInput.addEventListener('change',()=>{
    const f=fotoInput.files[0]; if(!f) return;
    if(fotoUrlInput) fotoUrlInput.value='';
    setDeleteButtonState(false);
    setPreviewImage(URL.createObjectURL(f));
});
if(fotoUrlInput&&fotoPreview) fotoUrlInput.addEventListener('change',()=>{
    const value=fotoUrlInput.value.trim(); if(!value) return;
    if(fotoInput) fotoInput.value='';
    setDeleteButtonState(false);
    try{ const parsed=new URL(value); if(['http:','https:'].includes(parsed.protocol)) setPreviewImage(parsed.href); }catch(error){}
});
setTimeout(()=>{document.querySelectorAll('.alert').forEach(a=>{a.classList.add('alert-hide');setTimeout(()=>a.style.display='none',500)});},4000);
</script>
</body>
</html>
