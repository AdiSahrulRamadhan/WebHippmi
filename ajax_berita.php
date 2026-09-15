<?php
require_once __DIR__ . '/koneksi.php';
$pdo = getDBConnection();

header('Content-Type: text/html; charset=utf-8');

$searchKeyword = trim((string) ($_GET['q'] ?? ''));
$currentCategory = trim((string) ($_GET['kategori'] ?? ''));
$currentYear = trim((string) ($_GET['tahun'] ?? ''));

// Count - hanya tipe berita
$countSql = "SELECT COUNT(*) FROM `berita` WHERE `status` = 'published' AND `tipe` = 'berita'";
$countParams = [];

if ($searchKeyword !== '') {
    $countSql .= " AND (`judul` LIKE :q1 OR `penulis` LIKE :q2 OR `ringkasan` LIKE :q3 OR `konten` LIKE :q4)";
    $countParams['q1'] = '%' . $searchKeyword . '%';
    $countParams['q2'] = '%' . $searchKeyword . '%';
    $countParams['q3'] = '%' . $searchKeyword . '%';
    $countParams['q4'] = '%' . $searchKeyword . '%';
}

if ($currentCategory !== '' && $currentCategory !== 'Semua') {
    $countSql .= " AND `kategori` = :kat";
    $countParams['kat'] = $currentCategory;
}

if ($currentYear !== '' && $currentYear !== 'Semua') {
    $countSql .= " AND YEAR(`tanggal`) = :yr";
    $countParams['yr'] = $currentYear;
}

$stmtCount = $pdo->prepare($countSql);
$stmtCount->execute($countParams);
$totalPublished = (int) $stmtCount->fetchColumn();

// Pagination
$perPage = 6;
$totalPages = max(1, (int) ceil($totalPublished / $perPage));
$currentPage = isset($_GET['page']) ? max(1, min($totalPages, (int) $_GET['page'])) : 1;
$offset = ($currentPage - 1) * $perPage;

// List query - hanya tipe berita
$listSql = "SELECT * FROM `berita` WHERE `status` = 'published' AND `tipe` = 'berita'";
$listParams = [];

if ($searchKeyword !== '') {
    $listSql .= " AND (`judul` LIKE :q1 OR `penulis` LIKE :q2 OR `ringkasan` LIKE :q3 OR `konten` LIKE :q4)";
    $listParams['q1'] = '%' . $searchKeyword . '%';
    $listParams['q2'] = '%' . $searchKeyword . '%';
    $listParams['q3'] = '%' . $searchKeyword . '%';
    $listParams['q4'] = '%' . $searchKeyword . '%';
}

if ($currentCategory !== '' && $currentCategory !== 'Semua') {
    $listSql .= " AND `kategori` = :kat";
    $listParams['kat'] = $currentCategory;
}

if ($currentYear !== '' && $currentYear !== 'Semua') {
    $listSql .= " AND YEAR(`tanggal`) = :yr";
    $listParams['yr'] = $currentYear;
}

$listSql .= " ORDER BY `tanggal` DESC, `id` DESC LIMIT :limit OFFSET :offset";
$stmtList = $pdo->prepare($listSql);
foreach ($listParams as $k => $v) {
    $stmtList->bindValue(':' . $k, $v);
}
$stmtList->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmtList->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtList->execute();
$newsList = $stmtList->fetchAll();

function buildFilterQuery($overrides = []) {
    $base = [
        'q' => trim((string) ($_GET['q'] ?? '')),
        'kategori' => trim((string) ($_GET['kategori'] ?? '')),
        'tahun' => trim((string) ($_GET['tahun'] ?? '')),
    ];
    foreach ($overrides as $k => $v) {
        $base[$k] = $v;
    }
    $parts = [];
    foreach ($base as $k => $v) {
        if ($v !== '' && $v !== 'Semua') {
            $parts[] = urlencode($k) . '=' . urlencode($v);
        }
    }
    return $parts ? '?' . implode('&', $parts) : '';
}
?>
<div class="row news-row">
    <?php if (empty($newsList)): ?>
        <div class="col-12 py-5 text-center text-muted" data-aos="fade-up">
            <i class="fas fa-newspaper fa-3x mb-3 text-secondary" style="opacity: 0.4;"></i>
            <h5>Belum ada berita ditemukan</h5>
            <p class="small">Silakan coba kata kunci lain atau pilih kategori/tahun yang berbeda.</p>
            <a href="berita.php" class="btn btn-outline-danger btn-sm mt-2 rounded-pill px-3 ajax-reset">Lihat Semua Berita</a>
        </div>
    <?php else: ?>
        <?php $delay = 100; foreach ($newsList as $item): ?>
            <?php
                $itemImg = !empty($item['gambar']) ? $item['gambar'] : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=800';
                $itemAvatar = !empty($item['penulis_avatar']) ? $item['penulis_avatar'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100';
                $itemJson = htmlspecialchars(json_encode([
                    'id' => $item['id'],
                    'judul' => $item['judul'],
                    'kategori' => $item['kategori'],
                    'penulis' => $item['penulis'],
                    'tanggal' => formatTanggalIndonesia($item['tanggal']),
                    'gambar' => $itemImg,
                    'konten' => $item['konten'],
                    'views' => $item['views']
                ]), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="col-md-6 news-card-container" data-aos="fade-up" data-aos-delay="<?= $delay; ?>">
                <div class="news-card">
                    <div class="news-img-container">
                        <span class="news-category"><?= htmlspecialchars($item['kategori']); ?></span>
                        <img src="<?= htmlspecialchars($itemImg); ?>" alt="<?= htmlspecialchars($item['judul']); ?>" class="card-img-top" onerror="this.src='https://placehold.co/600x400?text=Berita+HIPPMI';">
                    </div>
                    <div class="card-body">
                        <div class="news-date">
                            <i class="far fa-calendar-alt"></i> <?= formatTanggalIndonesia($item['tanggal']); ?>
                        </div>
                        <h3 class="news-title" role="button" tabindex="0" style="cursor:pointer;" onclick="openNewsModal(<?= $itemJson; ?>)" onkeydown="if(event.key==='Enter') openNewsModal(<?= $itemJson; ?>)"><?= htmlspecialchars($item['judul']); ?></h3>
                        <p class="news-excerpt"><?= htmlspecialchars($item['ringkasan']); ?></p>
                        <button type="button" class="btn-read-more" onclick="openNewsModal(<?= $itemJson; ?>)">Lihat Selengkapnya</button>
                        <div class="news-footer">
                            <div class="news-author">
                                <img src="<?= htmlspecialchars($itemAvatar); ?>" alt="<?= htmlspecialchars($item['penulis']); ?>" onerror="this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100';">
                                <span class="news-author-name"><?= htmlspecialchars($item['penulis']); ?></span>
                            </div>
                            <div class="news-stats">
                                <span><i class="far fa-eye"></i> <?= number_format($item['views']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $delay = ($delay % 600) + 100; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php if ($totalPages > 1): ?>
<div class="pagination-custom" data-aos="fade-up">
    <?php
        $catStr = buildFilterQuery(['kategori' => $currentCategory]);
    ?>
    <?php if ($currentPage > 1): ?>
        <div class="page-item-custom">
            <a href="ajax_berita?page=<?= $currentPage - 1; ?><?= $catStr; ?>" class="page-link-custom ajax-pagination">
                <i class="fas fa-chevron-left"></i>
            </a>
        </div>
    <?php endif; ?>
    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <div class="page-item-custom">
            <a href="ajax_berita?page=<?= $p; ?><?= $catStr; ?>" class="page-link-custom ajax-pagination <?= $p === $currentPage ? 'active' : ''; ?>">
                <?= $p; ?>
            </a>
        </div>
    <?php endfor; ?>
    <?php if ($currentPage < $totalPages): ?>
        <div class="page-item-custom">
            <a href="ajax_berita?page=<?= $currentPage + 1; ?><?= $catStr; ?>" class="page-link-custom ajax-pagination">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
