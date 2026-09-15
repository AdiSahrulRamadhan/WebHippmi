<?php
require_once __DIR__ . '/koneksi.php';
$pdo = getDBConnection();
header('Content-Type: text/html; charset=utf-8');

$_search = trim((string) ($_GET['q'] ?? ''));
$_kat = trim((string) ($_GET['kategori'] ?? ''));
$_from = trim((string) ($_GET['dari'] ?? ''));
$_to = trim((string) ($_GET['sampai'] ?? ''));
$_page = max(1, (int) ($_GET['page'] ?? 1));
$perPageK = 6;

$whereK = "WHERE `status` = 'published' AND `tipe` = 'kegiatan'";
$paramsK = [];
if ($_search !== '') { $whereK .= " AND (`judul` LIKE :q1 OR `penulis` LIKE :q2 OR `ringkasan` LIKE :q3)"; $paramsK['q1']='%'.$_search.'%'; $paramsK['q2']='%'.$_search.'%'; $paramsK['q3']='%'.$_search.'%'; }
if ($_kat !== '' && $_kat !== 'Semua') { $whereK .= " AND `kategori` = :kat"; $paramsK['kat'] = $_kat; }
if ($_from !== '') { $whereK .= " AND `tanggal` >= :dari"; $paramsK['dari'] = $_from; }
if ($_to !== '') { $whereK .= " AND `tanggal` <= :sampai"; $paramsK['sampai'] = $_to; }

$cntK = $pdo->prepare("SELECT COUNT(*) FROM `berita` $whereK");
$cntK->execute($paramsK);
$totalK = (int) $cntK->fetchColumn();
$totalPagesK = max(1, (int) ceil($totalK / $perPageK));
$_page = min($_page, $totalPagesK);
$offsetK = ($_page - 1) * $perPageK;

$listKSql = "SELECT * FROM `berita` $whereK ORDER BY `tanggal` DESC, `id` DESC LIMIT :lim OFFSET :off";
$stmtK = $pdo->prepare($listKSql);
foreach ($paramsK as $k=>$v) $stmtK->bindValue(':'.$k, $v);
$stmtK->bindValue(':lim', $perPageK, PDO::PARAM_INT);
$stmtK->bindValue(':off', $offsetK, PDO::PARAM_INT);
$stmtK->execute();
$kegiatanList = $stmtK->fetchAll();

function buildQS($overrides = []) {
    $base = ['q'=>trim((string)($_GET['q']??'')),'kategori'=>trim((string)($_GET['kategori']??'')),'dari'=>trim((string)($_GET['dari']??'')),'sampai'=>trim((string)($_GET['sampai']??''))];
    foreach ($overrides as $k=>$v) $base[$k]=$v;
    $p=[]; foreach($base as $k=>$v){ if($v!=='' && $v!=='Semua') $p[]=urlencode($k).'='.urlencode($v); }
    return $p ? '&'.implode('&',$p) : '';
}
?>
<div class="row">
    <?php if (empty($kegiatanList)): ?>
        <div class="col-12 py-5 text-center text-muted">
            <i class="fas fa-calendar-times fa-3x mb-3" style="opacity:0.35;"></i>
            <h5>Belum ada kegiatan</h5>
            <p class="small">Coba ubah kata kunci atau filter tanggal/kategori.</p>
        </div>
    <?php else: ?>
        <?php $delayK=0; foreach ($kegiatanList as $ev): ?>
            <?php
                $evImg = !empty($ev['gambar']) ? $ev['gambar'] : 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=800';
                $badgeClass = 'event-category-workshop';
                $kl = strtolower($ev['kategori']);
                if (str_contains($kl,'seminar')) $badgeClass='event-category-seminar';
                elseif (str_contains($kl,'pelatihan')) $badgeClass='event-category-pelatihan';
                elseif (str_contains($kl,'kolaborasi')) $badgeClass='event-category-kolaborasi';
                elseif (str_contains($kl,'advokasi')) $badgeClass='event-category-advokasi';
                $evJson = htmlspecialchars(json_encode([
                    'id'=>$ev['id'],
                    'judul'=>$ev['judul'],
                    'kategori'=>$ev['kategori'],
                    'penulis'=>$ev['penulis'],
                    'penulis_avatar'=>$ev['penulis_avatar'] ?? '',
                    'tanggal'=>formatTanggalIndonesia($ev['tanggal']),
                    'gambar'=>$evImg,
                    'konten'=>$ev['konten'],
                    'ringkasan'=>$ev['ringkasan'],
                    'views'=>(int)($ev['views']??0),
                    'link_daftar'=>$ev['link_daftar'] ?? ''
                ]), ENT_QUOTES,'UTF-8');
                $daftarUrl = trim((string)($ev['link_daftar'] ?? ''));
            ?>
            <div class="col-md-6 mb-4">
                <div class="event-card">
                    <div class="event-img-container">
                        <span class="event-category-badge <?= $badgeClass; ?>"><?= htmlspecialchars($ev['kategori']); ?></span>
                        <img src="<?= htmlspecialchars($evImg); ?>" alt="<?= htmlspecialchars($ev['judul']); ?>" onerror="this.src='https://placehold.co/600x400?text=Kegiatan+HIPPMI';">
                    </div>
                    <div class="event-body">
                        <h3 class="event-title"><?= htmlspecialchars($ev['judul']); ?></h3>
                        <div class="event-info"><i class="far fa-calendar-alt"></i><span><?= formatTanggalIndonesia($ev['tanggal']); ?></span></div>
                        <div class="event-info"><i class="far fa-user"></i><span><?= htmlspecialchars($ev['penulis']); ?></span></div>
                        <p class="event-description" style="text-align:justify;"><?= htmlspecialchars($ev['ringkasan']); ?></p>
                        <div class="event-footer" style="gap:8px;flex-wrap:wrap;">
                            <button type="button" class="btn-register border-0" onclick='openKegiatanModal(<?= $evJson; ?>)'>Lihat Detail</button>
                            <?php if ($daftarUrl !== ''): ?>
                                <a href="<?= htmlspecialchars($daftarUrl); ?>" target="_blank" rel="noopener" class="btn-register" style="background:#fff;color:var(--primary-color);border:1px solid var(--primary-color);">Daftar</a>
                            <?php else: ?>
                                <span class="btn-register" style="background:#f3f4f6;color:var(--text-muted);border:1px solid #eee;cursor:not-allowed;opacity:0.7;">Daftar</span>
                            <?php endif; ?>
                            <span class="text-muted small ms-auto"><i class="far fa-eye me-1"></i> <?= number_format((int)($ev['views']??0)); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php if ($totalPagesK > 1): ?>
<div class="d-flex justify-content-center mt-4">
    <nav aria-label="Page navigation">
        <ul class="pagination">
            <?php if ($_page > 1): ?>
            <li class="page-item"><a class="page-link ajax-keg-page" href="ajax_kegiatan?page=<?= $_page-1; ?><?= buildQS(); ?>">&laquo;</a></li>
            <?php endif; ?>
            <?php for ($p=1;$p<=$totalPagesK;$p++): ?>
            <li class="page-item <?= $p===$_page ? 'active' : ''; ?>"><a class="page-link ajax-keg-page <?= $p===$_page ? 'active' : ''; ?>" href="ajax_kegiatan?page=<?= $p; ?><?= buildQS(); ?>"><?= $p; ?></a></li>
            <?php endfor; ?>
            <?php if ($_page < $totalPagesK): ?>
            <li class="page-item"><a class="page-link ajax-keg-page" href="ajax_kegiatan?page=<?= $_page+1; ?><?= buildQS(); ?>">&raquo;</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
<?php endif; ?>
