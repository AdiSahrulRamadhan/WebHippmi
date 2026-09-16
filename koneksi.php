<?php

/**
 * Koneksi Database & Helper HIPPMI
 */

if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void {
        if (!file_exists($path)) return;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            [$k,$v] = explode('=', $line, 2);
            $k = trim($k); $v = trim($v);
            if ($k !== '' && getenv($k) === false && !isset($_ENV[$k])) {
                putenv("$k=$v"); $_ENV[$k] = $v;
            }
        }
    }
    loadEnv(__DIR__ . '/.env');
}
define('DB_HOST', getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost'));
define('DB_USER', getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? 'root'));
define('DB_PASS', getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? ''));
define('DB_NAME', getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'db_hippmi'));

/**
 * Mendapatkan koneksi PDO ke database
 * Otomatis membuat database dan tabel jika belum ada
 */
function getDBConnection(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    try {
        // Buat database jika belum ada
        $initDsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
        $initPdo = new PDO($initDsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $initPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Sambungkan ke database db_hippmi
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        // Inisialisasi tabel dan seed data awal
        initDatabaseTables($pdo);

        return $pdo;
    } catch (PDOException $e) {
        error_log('DB connect failed: '.$e->getMessage());
        http_response_code(500);
        die('Koneksi Database Gagal. Silakan coba lagi nanti.');
    }
}

/**
 * Inisialisasi tabel berita dan data awal
 */
function initDatabaseTables(PDO $pdo): void
{
    $createTableQuery = "
    CREATE TABLE IF NOT EXISTS `berita` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `judul` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `kategori` VARCHAR(100) NOT NULL DEFAULT 'Pendidikan',
        `tipe` ENUM('berita','kegiatan') NOT NULL DEFAULT 'berita',
        `penulis` VARCHAR(100) NOT NULL DEFAULT 'Admin HIPPMI',
        `penulis_avatar` VARCHAR(255) NULL,
        `ringkasan` TEXT NOT NULL,
        `konten` LONGTEXT NOT NULL,
        `gambar` VARCHAR(255) NULL,
        `link_daftar` VARCHAR(500) NULL,
        `tanggal` DATE NOT NULL,
        `views` INT NOT NULL DEFAULT 0,
        `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
        `status` ENUM('published', 'draft') NOT NULL DEFAULT 'published',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`slug`),
        INDEX (`kategori`),
        INDEX (`tipe`),
        INDEX (`status`),
        INDEX (`tanggal`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($createTableQuery);
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `struktur_organisasi` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nama_jabatan` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `deskripsi` TEXT NOT NULL,
        `icon` VARCHAR(100) NOT NULL DEFAULT 'fa-sitemap',
        `foto` VARCHAR(255) NULL,
        `urutan` INT NOT NULL DEFAULT 0,
        `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`slug`),
        INDEX (`status`),
        INDEX (`urutan`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `core_values` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `judul` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `deskripsi` TEXT NOT NULL,
        `icon` VARCHAR(100) NOT NULL DEFAULT 'fa-star',
        `urutan` INT NOT NULL DEFAULT 0,
        `status` ENUM('published','draft') NOT NULL DEFAULT 'published',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`slug`),
        INDEX (`status`),
        INDEX (`urutan`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS `admin_users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(80) NOT NULL,
        `password_hash` VARCHAR(255) NOT NULL,
        `role` VARCHAR(30) NOT NULL DEFAULT 'admin',
        `last_login_at` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uniq_username` (`username`),
        INDEX (`role`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    try {
        $hasTipe = $pdo->query("SHOW COLUMNS FROM `berita` LIKE 'tipe'")->fetch();
        if (!$hasTipe) {
            $pdo->exec("ALTER TABLE `berita` ADD COLUMN `tipe` ENUM('berita','kegiatan') NOT NULL DEFAULT 'berita' AFTER `kategori`");
            $pdo->exec("CREATE INDEX idx_tipe ON `berita` (`tipe`)");
        }
    } catch (Throwable $e) {}
    try {
        $hasLink = $pdo->query("SHOW COLUMNS FROM `berita` LIKE 'link_daftar'")->fetch();
        if (!$hasLink) {
            $pdo->exec("ALTER TABLE `berita` ADD COLUMN `link_daftar` VARCHAR(500) NULL AFTER `gambar`");
        }
    } catch (Throwable $e) {}

    $countStmt = $pdo->query("SELECT COUNT(*) FROM `berita`");
    $total = (int) $countStmt->fetchColumn();
    if ($total === 0) {
        seedInitialNews($pdo);
    }
    $countStruktur = (int) $pdo->query("SELECT COUNT(*) FROM `struktur_organisasi`")->fetchColumn();
    if ($countStruktur === 0) {
        seedStrukturOrganisasi($pdo);
    }
    $countCV = (int) $pdo->query("SELECT COUNT(*) FROM `core_values`")->fetchColumn();
    if ($countCV === 0) {
        seedCoreValues($pdo);
    }
    $countAdmin = (int) $pdo->query("SELECT COUNT(*) FROM `admin_users`")->fetchColumn();
    if ($countAdmin === 0) {
        seedAdminUsers($pdo);
    }
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS `site_settings` (`kunci` VARCHAR(100) PRIMARY KEY, `nilai` TEXT NULL, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $hasRow = (int) $pdo->query("SELECT COUNT(*) FROM `site_settings` WHERE `kunci`='struktur_gambar_utama'")->fetchColumn();
        if ($hasRow === 0) {
            $fallback = 'https://static.vecteezy.com/system/resources/previews/002/206/204/original/organizational-chart-tree-diagram-template-free-vector.jpg';
            $ins = $pdo->prepare("INSERT IGNORE INTO `site_settings` (`kunci`,`nilai`) VALUES ('struktur_gambar_utama', :v)");
            $ins->execute(['v' => $fallback]);
        }
    } catch (Throwable $e) {}
}

/**
 * Seed data awal berita dari konten asli website
 */
function seedInitialNews(PDO $pdo): void
{
    $initialData = [
        [
            'judul' => 'Kongres Nasional HIPPMI 2023: Menguatkan Pendidikan Indonesia di Era Digital',
            'slug' => 'kongres-nasional-hippmi-2023-menguatkan-pendidikan-indonesia-di-era-digital',
            'kategori' => 'Pendidikan',
            'penulis' => 'Dr. Hardika P.S.',
            'penulis_avatar' => 'https://randomuser.me/api/portraits/men/32.jpg',
            'ringkasan' => 'Kongres Nasional HIPPMI 2023 berhasil merumuskan langkah-langkah strategis bagi para pendidik muda dalam menghadapi revolusi kecerdasan buatan dan kurikulum berbasis teknologi.',
            'konten' => '<p>Kongres Nasional HIPPMI 2023 diselenggarakan di Jakarta dengan dihadiri oleh ratusan delegasi perwakilan pendidik dan pengajar muda dari berbagai penjuru nusantara. Kegiatan ini menjadi wadah konsolidasi nasional untuk merumuskan arah dan strategi pendidikan menghadapi era transformasi digital yang kian cepat.</p><p>Dalam pidato pembukaannya, Ketua Umum HIPPMI menekankan pentingnya penguasaan literasi digital bagi guru-guru muda tanpa meninggalkan nilai-nilai moral dan kebangsaan. Forum ini menghasilkan manifesto pendidikan pemuda yang akan disampaikan langsung ke kementerian terkait.</p>',
            'gambar' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80',
            'tanggal' => '2023-07-10',
            'views' => 1520,
            'is_featured' => 1,
            'status' => 'published',
        ],
        [
            'judul' => 'Workshop Pengembangan Kurikulum Merdeka Belajar',
            'slug' => 'workshop-pengembangan-kurikulum-merdeka-belajar',
            'kategori' => 'Workshop',
            'penulis' => 'Sarah Wijaya',
            'penulis_avatar' => 'https://randomuser.me/api/portraits/women/44.jpg',
            'ringkasan' => 'HIPPMI mengadakan workshop pengembangan kurikulum merdeka belajar untuk para pendidik muda di Jakarta. Kegiatan ini dihadiri oleh lebih dari 200 pendidik dari berbagai daerah.',
            'konten' => '<p>Workshop intensif yang diadakan HIPPMI ini bertujuan untuk membekali tenaga pengajar muda dengan modul-modul ajar kreatif berbasis proyek (Project-Based Learning) sesuai dengan esensi Kurikulum Merdeka.</p><p>Para peserta diajak untuk langsung merancang RPP diferensiasi serta asesmen diagnostik yang ramah terhadap bakat unik masing-masing siswa di ruang kelas.</p>',
            'gambar' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80',
            'tanggal' => '2023-07-05',
            'views' => 523,
            'is_featured' => 0,
            'status' => 'published',
        ],
        [
            'judul' => 'Seminar Nasional: AI dalam Pendidikan Indonesia',
            'slug' => 'seminar-nasional-ai-dalam-pendidikan-indonesia',
            'kategori' => 'Seminar',
            'penulis' => 'Rendi Pratama',
            'penulis_avatar' => 'https://randomuser.me/api/portraits/men/67.jpg',
            'ringkasan' => 'Seminar nasional yang membahas pemanfaatan kecerdasan buatan (AI) dalam dunia pendidikan Indonesia. Para pakar dan praktisi berbagi pengalaman dan wawasan.',
            'konten' => '<p>Kecerdasan Buatan (AI) bukan lagi sekadar masa depan, melainkan realitas masa kini dalam kelas pembelajaran. Seminar ini mengupas tuntas etika pemanfaatan generative AI oleh guru dan siswa, serta strategi evaluasi pembelajaran yang relevan.</p>',
            'gambar' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80',
            'tanggal' => '2026-09-25',
            'views' => 412,
            'is_featured' => 1,
            'status' => 'published',
        ],
        [
            'judul' => 'Kolaborasi HIPPMI dengan Kementerian Pendidikan',
            'slug' => 'kolaborasi-hippmi-dengan-kementerian-pendidikan',
            'kategori' => 'Kolaborasi',
            'penulis' => 'Dina Maharani',
            'penulis_avatar' => 'https://randomuser.me/api/portraits/women/22.jpg',
            'ringkasan' => 'HIPPMI menandatangani nota kesepahaman (MoU) dengan Kementerian Pendidikan untuk program pengembangan kompetensi pendidik muda di Indonesia.',
            'konten' => '<p>Nota kesepahaman ini memayungi berbagai program pelatihan sertifikasi pengajar, distribusi relawan pendidik ke pelosok negeri, serta riset kebijakan pendidikan tingkat dasar dan menengah.</p>',
            'gambar' => 'https://images.unsplash.com/photo-1519452575417-564c1401ecc0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80',
            'tanggal' => '2023-06-15',
            'views' => 678,
            'is_featured' => 0,
            'status' => 'published',
        ],
        [
            'judul' => 'Advokasi Kebijakan Pendidikan di Era Digital',
            'slug' => 'advokasi-kebijakan-pendidikan-di-era-digital',
            'kategori' => 'Advokasi',
            'penulis' => 'Ahmad Ridwan',
            'penulis_avatar' => 'https://randomuser.me/api/portraits/men/32.jpg',
            'ringkasan' => 'Tim Advokasi HIPPMI menyampaikan rekomendasi kebijakan pendidikan untuk menghadapi tantangan di era digital kepada para pemangku kebijakan.',
            'konten' => '<p>Naskah akademik dan rekomendasi kebijakan yang disusun HIPPMI menyoroti kesenjangan akses infrastruktur internet di daerah 3T serta perlunya standardisasi kesejahteraan bagi guru-guru honorer muda.</p>',
            'gambar' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80',
            'tanggal' => '2023-06-08',
            'views' => 345,
            'is_featured' => 0,
            'status' => 'published',
        ],
        [
            'judul' => 'Workshop Metode Pembelajaran Inovatif',
            'slug' => 'workshop-metode-pembelajaran-inovatif',
            'kategori' => 'Workshop',
            'penulis' => 'Ratna Dewi',
            'penulis_avatar' => 'https://randomuser.me/api/portraits/women/68.jpg',
            'ringkasan' => 'Kegiatan workshop yang fokus pada pengembangan metode pembelajaran inovatif bagi para pendidik muda di seluruh Indonesia.',
            'konten' => '<p>Peserta mempelajari teknik gamifikasi materi pelajaran, simulasi interaktif, serta pembuatan konten video micro-learning agar siswa tidak jenuh saat belajar mandiri maupun tatap muka.</p>',
            'gambar' => 'https://images.unsplash.com/photo-1531545514256-b1400bc00f31?ixlib=rb-4.0.3&auto=format&fit=crop&w=1374&q=80',
            'tanggal' => '2026-10-20',
            'views' => 289,
            'is_featured' => 0,
            'status' => 'published',
        ],
        [
            'judul' => 'Pembukaan Pendaftaran Anggota HIPPMI Periode 2023',
            'slug' => 'pembukaan-pendaftaran-anggota-hippmi-periode-2023',
            'kategori' => 'Pengumuman',
            'penulis' => 'Budi Santoso',
            'penulis_avatar' => 'https://randomuser.me/api/portraits/men/42.jpg',
            'ringkasan' => 'HIPPMI membuka pendaftaran anggota baru untuk periode 2023. Pendaftaran dibuka untuk pendidik dan pengajar muda di seluruh Indonesia.',
            'konten' => '<p>Peluang emas bagi guru, dosen muda, tutor komunitas, dan pemerhati pendidikan usia 18-35 tahun untuk bergabung bersama jejaring pendidikan terbesar di Indonesia.</p>',
            'gambar' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80',
            'tanggal' => '2023-05-25',
            'views' => 876,
            'is_featured' => 0,
            'status' => 'published',
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO `berita` (
            `judul`, `slug`, `kategori`, `penulis`, `penulis_avatar`,
            `ringkasan`, `konten`, `gambar`, `tanggal`, `views`, `is_featured`, `status`
        ) VALUES (
            :judul, :slug, :kategori, :penulis, :penulis_avatar,
            :ringkasan, :konten, :gambar, :tanggal, :views, :is_featured, :status
        )
    ");

    foreach ($initialData as $row) {
        $stmt->execute($row);
    }
}

function seedStrukturOrganisasi(PDO $pdo): void
{
    $data = [
        ['nama_jabatan'=>'Ketua Umum','deskripsi'=>'Pemimpin organisasi yang mengoordinasikan keseluruhan kegiatan dan kebijakan HIPPMI. Bertanggung jawab atas arah visi, misi, dan pencapaian tujuan organisasi secara menyeluruh.','icon'=>'fa-crown','urutan'=>1],
        ['nama_jabatan'=>'Dewan Penasihat','deskripsi'=>'Memberikan arahan strategis, nasihat, dan pertimbangan penting bagi jalannya organisasi. Berperan sebagai penyeimbang dan pengarah dalam pengambilan keputusan besar serta menjaga nilai-nilai dasar organisasi.','icon'=>'fa-scale-balanced','urutan'=>2],
        ['nama_jabatan'=>'Dewan Pakar','deskripsi'=>'Kelompok profesional, akademisi, atau praktisi ahli yang memberikan kontribusi keilmuan, pemikiran kritis, dan masukan teknis serta terlibat dalam perumusan rekomendasi strategis yang berdampak pada kebijakan Pendidikan.','icon'=>'fa-user-graduate','urutan'=>3],
        ['nama_jabatan'=>'Sekretaris Jenderal','deskripsi'=>'Mengelola administrasi, surat-menyurat, dan koordinasi internal organisasi. Berfungsi sebagai penghubung antara Ketua Umum dengan seluruh bidang dan anggota.','icon'=>'fa-file-pen','urutan'=>4],
        ['nama_jabatan'=>'Bendahara Umum','deskripsi'=>'Mengelola keuangan organisasi secara transparan dan akuntabel, termasuk pencatatan, pelaporan, dan pengelolaan anggaran untuk mendukung semua kegiatan HIPPMI.','icon'=>'fa-coins','urutan'=>5],
        ['nama_jabatan'=>'Bidang Edukasi & Konsultansi','deskripsi'=>'Menginisiasi dan melaksanakan program peningkatan pengetahuan dan keterampilan anggota, serta memberikan layanan konsultansi dan pendampingan bagi anggota dalam hal pengembangan karier, pengelolaan lembaga pendidikan, dan peningkatan kualitas pengajaran.','icon'=>'fa-person-chalkboard','urutan'=>6],
        ['nama_jabatan'=>'Bidang Riset & Advokasi','deskripsi'=>'Melaksanakan riset dan kajian kebijakan pendidikan yang relevan, menyusun rekomendasi, serta menyampaikan hasil kajian serta memberikan layanan advokasi kepada pendidik yang menghadapi permasalahan hukum atau ketidakadilan dalam menjalankan profesinya.','icon'=>'fa-magnifying-glass-chart','urutan'=>7],
        ['nama_jabatan'=>'Bidang Sharing & Kolaborasi','deskripsi'=>'Memfasilitasi ruang berbagi ide, pengalaman, dan best practices antar anggota HIPPMI, serta menjalin kerja sama dengan berbagai lembaga, komunitas, dan institusi pendidikan.','icon'=>'fa-handshake','urutan'=>8],
    ];
    $stmt = $pdo->prepare("INSERT INTO `struktur_organisasi` (`nama_jabatan`,`slug`,`deskripsi`,`icon`,`urutan`,`status`) VALUES (:nama_jabatan,:slug,:deskripsi,:icon,:urutan,'published')");
    foreach ($data as $r) {
        $stmt->execute(['nama_jabatan'=>$r['nama_jabatan'],'slug'=>createSlug($r['nama_jabatan']),'deskripsi'=>$r['deskripsi'],'icon'=>$r['icon'],'urutan'=>$r['urutan']]);
    }
}

function seedCoreValues(PDO $pdo): void
{
    $data = [
        ['judul'=>'Kolaborasi','deskripsi'=>'Kami percaya bahwa kekuatan pendidikan tumbuh dari kerja sama, sinergi, dan dukungan antarpendidik.','icon'=>'fa-handshake','urutan'=>1],
        ['judul'=>'Inovasi','deskripsi'=>'Kami mendorong pembaruan ide dan metode pembelajaran yang kreatif, relevan, dan adaptif terhadap zaman.','icon'=>'fa-lightbulb','urutan'=>2],
        ['judul'=>'Kompetensi','deskripsi'=>'Kami berkomitmen pada peningkatan kapasitas diri agar menjadi pendidik yang profesional, unggul, dan berdampak.','icon'=>'fa-award','urutan'=>3],
        ['judul'=>'Advokasi','deskripsi'=>'Kami berpihak pada nilai-nilai keadilan, kesetaraan akses pendidikan, dan keberpihakan terhadap peserta didik.','icon'=>'fa-scale-balanced','urutan'=>4],
        ['judul'=>'Integritas','deskripsi'=>'Kami menjunjung tinggi etika profesi, kejujuran, dan tanggung jawab dalam setiap langkah dan kontribusi.','icon'=>'fa-shield-halved','urutan'=>5],
        ['judul'=>'Kebermanfaatan','deskripsi'=>'Kami hadir untuk memberikan nilai dan kontribusi nyata bagi pendidikan, masyarakat, dan bangsa.','icon'=>'fa-heart','urutan'=>6],
    ];
    $stmt = $pdo->prepare("INSERT INTO `core_values` (`judul`,`slug`,`deskripsi`,`icon`,`urutan`,`status`) VALUES (:judul,:slug,:deskripsi,:icon,:urutan,'published')");
    foreach ($data as $r) {
        $stmt->execute(['judul'=>$r['judul'],'slug'=>createSlug($r['judul']),'deskripsi'=>$r['deskripsi'],'icon'=>$r['icon'],'urutan'=>$r['urutan']]);
    }
}

function seedAdminUsers(PDO $pdo): void
{
    $hash = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO `admin_users` (`username`,`password_hash`,`role`,`last_login_at`) VALUES (:u,:p,'super_admin',NOW())");
    $stmt->execute(['u'=>ADMIN_USERNAME,'p'=>$hash]);
}

/**
 * Format tanggal Indonesia: contoh "10 Juli 2023"
 */
function formatTanggalIndonesia(string $dateString): string
{
    if (!$dateString) {
        return '-';
    }

    $bulanIndonesia = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $time = strtotime($dateString);
    if (!$time) {
        return $dateString;
    }

    $tgl = (int) date('j', $time);
    $bln = (int) date('n', $time);
    $thn = date('Y', $time);

    return $tgl . ' ' . ($bulanIndonesia[$bln] ?? date('F', $time)) . ' ' . $thn;
}

/**
 * Membuat slug URL dari judul
 */
function createSlug(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);

    return $text ?: 'berita-' . time();
}

