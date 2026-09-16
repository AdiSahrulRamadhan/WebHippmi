<?php
require_once __DIR__ . '/koneksi.php';
$pdo = getDBConnection();
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
$calSql = "SELECT `id`,`judul`,`kategori`,`tanggal`,`konten`,`ringkasan`,`gambar`,`link_daftar`,`penulis`,`penulis_avatar` FROM `berita` WHERE `status`='published' AND `tipe`='kegiatan' ORDER BY `tanggal` ASC";
$calEventsRaw = $pdo->query($calSql)->fetchAll();
$kategoriKegiatan = ['Workshop','Seminar','Pelatihan','Kolaborasi','Advokasi'];
$calendarEvents = [];
foreach ($calEventsRaw as $ce) {
    $kl = strtolower($ce['kategori'] ?? '');
    $cls = 'event-category-workshop';
    if (str_contains($kl,'seminar')) $cls='event-category-seminar';
    elseif (str_contains($kl,'pelatihan')) $cls='event-category-pelatihan';
    elseif (str_contains($kl,'kolaborasi')) $cls='event-category-kolaborasi';
    elseif (str_contains($kl,'advokasi')) $cls='event-category-advokasi';
        $calendarEvents[] = [
            'title' => $ce['judul'],
            'start' => $ce['tanggal'],
            'classNames' => [$cls],
            'extendedProps' => [
                'id' => $ce['id'],
                'kategori' => $ce['kategori'],
                'konten' => $ce['konten'],
                'ringkasan' => $ce['ringkasan'],
                'gambar' => $ce['gambar'] ?? '',
                'link_daftar' => $ce['link_daftar'] ?? '',
                'tanggal' => formatTanggalIndonesia($ce['tanggal']),
                'penulis' => $ce['penulis'] ?? ''
            ]
        ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kegiatan - HIPPMI</title>
    <link rel="icon" type="image/webp" href="img/Logo.webp">
    <link rel="icon" type="image/png" href="img/Logo.png">
    <link rel="apple-touch-icon" href="img/Logo.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e30a17; /* Merah Indonesia */
            --primary-dark: #b8070f;
            --secondary-color: #ffffff; /* Putih Indonesia */
            --accent-color: #ffc107;
            --dark-color: #333333;
            --light-gray: #f8f9fa;
            --text-muted: #6c757d;
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }
        
        /* Preloader */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .preloader-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .preloader-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Page Header Styling */
        .page-header {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1505373877841-8d25f7d46678?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0 50px;
            position: relative;
            overflow: hidden;
        }
        
        .page-header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color) 50%, var(--secondary-color) 50%);
            z-index: 1;
        }
        
        /* Section Styling */
        .section-header {
            position: relative;
            margin-bottom: 40px;
            color: var(--primary-color);
            font-weight: 600;
            display: inline-block;
        }
        
        .section-header:after {
            content: "";
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
        }

        /* Calendar Styling - diperbagus */
        .calendar-container {
            background: linear-gradient(180deg, #fff 0%, #fffefe 100%);
            border: 1px solid #eef1f6;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 14px 36px rgba(27,28,32,0.07);
            overflow: hidden;
        }
        #calendar {
            height: 700px;
        }
        .fc .fc-toolbar {
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 18px !important;
        }
        .fc .fc-toolbar-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.35rem;
            letter-spacing: -0.02em;
        }
        .fc .fc-button {
            border-radius: 999px !important;
            padding: 7px 14px !important;
            font-size: 0.82rem !important;
            font-weight: 600 !important;
            text-transform: capitalize;
            box-shadow: 0 4px 12px rgba(227,10,23,0.12);
            transition: var(--transition);
        }
        .fc .fc-button-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)) !important;
            border-color: var(--primary-color) !important;
        }
        .fc .fc-button-primary:hover {
            background: var(--primary-dark) !important;
            border-color: var(--primary-dark) !important;
            transform: translateY(-1px);
        }
        .fc .fc-button-primary:disabled { opacity: .45; }
        .fc-theme-standard .fc-scrollgrid {
            border: 1px solid #eef1f6 !important;
            border-radius: 14px;
            overflow: hidden;
        }
        .fc-theme-standard th {
            background: #fafbfc;
            border-color: #eef1f6 !important;
        }
        .fc-theme-standard td { border-color: #eef1f6 !important; }
        .fc .fc-col-header-cell-cushion {
            padding: 12px 8px !important;
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            text-decoration: none !important;
        }
        .fc .fc-daygrid-day-number {
            padding: 8px 10px !important;
            font-weight: 600;
            color: var(--dark-color);
            text-decoration: none !important;
        }
        .fc .fc-daygrid-day.fc-day-today {
            background: rgba(227,10,23,0.06) !important;
        }
        .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
            background: var(--primary-color);
            color: #fff;
            border-radius: 999px;
            width: 30px;
            height: 30px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 6px;
            padding: 0 !important;
        }
        .fc-event {
            cursor: pointer;
            border: none !important;
            border-radius: 8px !important;
            padding: 4px 8px !important;
            font-size: .78rem !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .fc .fc-daygrid-event { margin-top: 4px !important; }
        .fc-h-event .fc-event-title { font-weight: 600; }
        .fc-theme-standard .fc-list {
            border: 1px solid #eef1f6 !important;
            border-radius: 14px;
            overflow: hidden;
        }
        .fc-list-day-cushion { background: #fafbfc !important; }
        
        /* Event Categories */
        .event-category-workshop {
            background-color: #ff6b6b !important;
        }
        
        .event-category-seminar {
            background-color: #48dbfb !important;
        }
        
        .event-category-pelatihan {
            background-color: #1dd1a1 !important;
        }
        
        .event-category-kolaborasi {
            background-color: #5f27cd !important;
        }
        
        .event-category-advokasi {
            background-color: #ff9f43 !important;
        }
        
        /* Event Category List */
        .event-categories {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .event-category-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
        }
        
        .category-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        /* Event Cards */
        .event-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: var(--transition);
            margin-bottom: 30px;
            height: 100%;
            display: flex;
            flex-direction: column;
            background-color: #fff;
        }
        
        .event-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(227, 10, 23, 0.15);
        }
        
        .event-img-container {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .event-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }
        
        .event-card:hover .event-img-container img {
            transform: scale(1.1);
        }
        
        .event-category-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 1;
        }
        
        .event-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        .event-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        
        .event-info {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .event-info i {
            width: 20px;
            margin-right: 8px;
            color: var(--primary-color);
        }
        
        .event-description {
            margin-top: 15px;
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.6;
            flex-grow: 1;
        }
        
        .event-footer {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-register {
            padding: 8px 25px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
        }
        
        .btn-register:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            color: white;
            box-shadow: 0 5px 15px rgba(227, 10, 23, 0.2);
        }

        /* Filter Section - diperbagus */
        .filter-section {
            margin-bottom: 30px;
            background: linear-gradient(180deg, #fff 0%, #fdf8f8 100%);
            border: 1px solid rgba(227,10,23,0.08);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 10px 28px rgba(27,28,32,0.06);
        }
        .filter-header {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 16px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-header::before {
            content: '';
            width: 28px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), #ff9a9e);
            border-radius: 99px;
            display: inline-block;
        }
        .filter-group {
            margin-bottom: 18px;
            padding: 12px 14px;
            background: #fafbfc;
            border: 1px solid #eef1f6;
            border-radius: 12px;
        }
        .filter-label {
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
            font-size: 0.86rem;
            color: var(--dark-color);
        }
        .filter-category-btn {
            background: #fff;
            border: 1px solid #e6e7ee;
            border-radius: 999px;
            padding: 6px 14px;
            margin-right: 8px;
            margin-bottom: 8px;
            font-size: 0.84rem;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            display: inline-block;
            text-decoration: none !important;
        }
        .filter-category-btn:hover, .filter-category-btn.active {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: #fff;
            border-color: transparent;
            box-shadow: 0 6px 18px rgba(227,10,23,0.22);
            transform: translateY(-1px);
        }
        .search-filter {
            position: relative;
            margin-bottom: 18px;
        }
        .search-filter .search-input {
            width: 100%;
            padding: 12px 16px;
            padding-right: 46px;
            border: 1px solid #e6e7ee;
            border-radius: 999px;
            font-size: 0.9rem;
            background: #fff;
            transition: var(--transition);
        }
        .search-filter .search-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(227,10,23,0.12);
            border-color: var(--primary-color);
        }
        .search-filter .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: #fff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 6px 14px rgba(227,10,23,0.22);
        }
        .search-filter .search-btn:hover {
            transform: translateY(-50%) scale(1.05);
        }
        .filter-apply-btn {
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 600;
            letter-spacing: 0.2px;
            box-shadow: 0 8px 18px rgba(227,10,23,0.18);
        }
        .filter-reset-link {
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 600;
            background: #fff;
            border: 1px solid #e6e7ee;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 15px;
            border: none;
            overflow: hidden;
        }
        
        .event-modal-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            border: none;
        }
        
        .event-modal-title {
            font-weight: 600;
            font-size: 1.3rem;
        }
        
        .modal-body {
            padding: 25px;
        }
        
        .modal-footer {
            border-top: 1px solid #f0f0f0;
            padding: 15px 25px;
        }
        
        .event-modal-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .event-modal-info {
            margin-bottom: 20px;
        }
        
        .event-modal-info-item {
            display: flex;
            margin-bottom: 10px;
        }
        
        .event-modal-info-icon {
            width: 30px;
            color: var(--primary-color);
        }
        
        .event-modal-info-text {
            flex: 1;
        }
        
        .event-modal-description {
            line-height: 1.8;
            color: var(--text-muted);
            margin-bottom: 20px;
        }
        
        .btn-modal-register {
            padding: 10px 30px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 1rem;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-modal-register:hover {
            background: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(227, 10, 23, 0.2);
        }

        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: #fff;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            z-index: 99;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .back-to-top.active {
            opacity: 1;
            visibility: visible;
            bottom: 30px;
        }
        
        .back-to-top:hover {
            background: var(--primary-dark);
            transform: translateY(-5px);
        }
        
        /* Newsletter Section */
        .newsletter-section {
            background: linear-gradient(rgba(227, 10, 23, 0.9), rgba(227, 10, 23, 0.9)), url('https://images.unsplash.com/photo-1497633762265-9d179a990aa6?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80');
            background-size: cover;
            background-position: center;
            padding: 60px 0;
            color: white;
            position: relative;
        }
        
        .newsletter-form {
            position: relative;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .newsletter-input {
            width: 100%;
            padding: 15px 20px;
            padding-right: 150px;
            border-radius: 50px;
            border: none;
            font-size: 1rem;
        }
        
        .newsletter-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }
        
        .newsletter-btn {
            position: absolute;
            right: 5px;
            top: 5px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .newsletter-btn:hover {
            background: var(--primary-dark);
        }
        
        /* Media Queries - mobile fix */
        @media (max-width: 992px) {
            #calendar {
                height: 600px;
            }
            .filter-section {
                margin-bottom: 30px;
                position: static !important;
                top: auto !important;
            }
        }
        @media (max-width: 768px) {
            .page-header {
                padding: 70px 0 30px;
            }
            .page-header .display-4 {
                font-size: 1.9rem;
                line-height: 1.3;
                padding: 0 10px;
                word-break: break-word;
            }
            .page-header .lead {
                font-size: 0.95rem;
                line-height: 1.6;
                padding: 0 14px;
            }
            #calendar {
                height: 500px;
            }
            .calendar-container {
                padding: 16px 12px;
                border-radius: 16px;
            }
            .fc .fc-toolbar {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }
            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                flex-wrap: wrap;
                gap: 6px;
            }
            .fc .fc-toolbar-title {
                font-size: 1.15rem !important;
                text-align: center;
                width: 100%;
            }
            .event-category-item {
                font-size: 0.74rem;
            }
            .event-title {
                font-size: 1.08rem;
            }
            .custom-tab {
                padding: 12px 10px;
                font-size: 0.9rem;
            }
        }
        @media (max-width: 576px) {
            .page-header {
                padding: 60px 0 28px;
            }
            .page-header .display-4 {
                font-size: 1.55rem;
            }
            .page-header .lead {
                font-size: 0.88rem;
            }
            .section-header {
                font-size: 1.3rem;
            }
            #calendar {
                height: 420px;
            }
            .calendar-container {
                padding: 12px 8px;
            }
            .event-categories {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 8px 10px;
                align-items: flex-start;
            }
            .btn-register {
                padding: 8px 15px;
                font-size: 0.8rem;
            }
            .custom-tabs {
                border-radius: 12px;
            }
            .custom-tab {
                padding: 10px 8px;
                font-size: 0.84rem;
            }
            .filter-section {
                padding: 16px;
                border-radius: 16px;
            }
            .fc .fc-button {
                padding: 6px 10px !important;
                font-size: 0.74rem !important;
            }
            .fc .fc-toolbar-title {
                font-size: 1.05rem !important;
            }
        }
        @media (max-width: 375px) {
            .page-header .display-4 {
                font-size: 1.35rem;
            }
            #calendar {
                height: 380px;
            }
            .fc .fc-toolbar {
                gap: 6px;
            }
        }

        /* Tab Navigation */
        .custom-tabs {
            display: flex;
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .custom-tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            background-color: #fff;
            color: var(--dark-color);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 3px solid transparent;
        }
        
        .custom-tab.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .custom-tab:hover:not(.active) {
            background-color: #f9f9f9;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-content">
            <div class="preloader-spinner"></div>
            <h4 class="mt-3">Loading...</h4>
        </div>
    </div>
    
    <!-- Back to Top Button -->
    <a href="#" class="back-to-top">
        <i class="fas fa-chevron-up"></i>
    </a>
    
    <?php include 'navbar.php'; ?>

    <!-- Page Header -->
    <header class="page-header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Kegiatan</h1>
            <p class="lead mb-4" data-aos="fade-up" data-aos-delay="200">Jangan lewatkan berbagai kegiatan menarik yang diselenggarakan oleh HIPPMI</p>
        </div>
    </header>
    
    <!-- Banner Nasionalis -->
    <div class="bg-red-section py-3">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h5 class="mb-0" data-aos="fade-up">"Membangun Pendidikan, Membentuk Generasi"</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <!-- Tab Navigation -->
            <div class="custom-tabs mb-4" data-aos="fade-up">
                <div class="custom-tab active" data-tab="list">
                    <i class="fas fa-list me-2"></i> Daftar Kegiatan
                </div>
                <div class="custom-tab" data-tab="calendar">
                    <i class="far fa-calendar-alt me-2"></i> Kalender
                </div>
            </div>
            
            <!-- List View (Default Active) -->
            <div class="tab-content active" id="list-view">
                <div class="row">
                    <!-- Filter Sidebar - diperbagus + AJAX -->
                    <div class="col-lg-3" data-aos="fade-right">
                        <div class="filter-section sticky-lg-top" style="top: 100px; z-index: 100;">
                            <h4 class="filter-header">Filter Kegiatan</h4>
                            <div class="search-filter mb-4">
                                <input type="text" id="kegSearchInput" class="search-input" placeholder="Cari kegiatan..." value="<?= htmlspecialchars($_search); ?>">
                                <button type="button" id="kegSearchBtn" class="search-btn"><i class="fas fa-search"></i></button>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label"><i class="fa-solid fa-layer-group me-1 text-danger"></i> Kategori</label>
                                <div id="kegKategoriWrap">
                                    <?php $cats = array_merge(['Semua'], $kategoriKegiatan); foreach ($cats as $ck): $isAct = ($_kat==='' && $ck==='Semua') || $_kat===$ck; ?>
                                    <button type="button" class="filter-category-btn keg-cat-btn <?= $isAct ? 'active' : ''; ?>" data-kategori="<?= $ck === 'Semua' ? '' : htmlspecialchars($ck); ?>"><?= htmlspecialchars($ck); ?></button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label class="filter-label"><i class="fa-regular fa-calendar me-1 text-danger"></i> Tanggal</label>
                                <div class="mb-3">
                                    <label class="form-label" style="font-size:0.82rem;">Dari</label>
                                    <input type="date" id="kegDari" class="form-control" value="<?= htmlspecialchars($_from); ?>">
                                </div>
                                <div>
                                    <label class="form-label" style="font-size:0.82rem;">Hingga</label>
                                    <input type="date" id="kegSampai" class="form-control" value="<?= htmlspecialchars($_to); ?>">
                                </div>
                            </div>
                            <button type="button" id="kegApplyBtn" class="btn btn-danger w-100 filter-apply-btn"><i class="fa-solid fa-filter me-1"></i> Terapkan Filter</button>
                            <button type="button" id="kegResetBtn" class="btn filter-reset-link w-100 mt-2" style="<?= ($_search!==''||$_kat!==''||$_from!==''||$_to!=='') ? '' : 'display:none;' ?>"><i class="fa-solid fa-rotate-left me-1"></i> Reset</button>
                        </div>
                    </div>
                    
                    <!-- Events List - dinamis AJAX 6/page -->
                    <div class="col-lg-9">
                    <div id="keg-results">
                        <div class="row">
                            <?php if (empty($kegiatanList)): ?>
                                <div class="col-12 py-5 text-center text-muted">
                                    <i class="fas fa-calendar-times fa-3x mb-3" style="opacity:0.35;"></i>
                                    <h5>Belum ada kegiatan</h5>
                                    <p class="small">Tambahkan kegiatan via Admin → Kelola Kegiatan.</p>
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
                                        $avSrc = !empty($ev['penulis_avatar']) ? (str_starts_with($ev['penulis_avatar'],'http') ? $ev['penulis_avatar'] : $ev['penulis_avatar']) : '';
                                    ?>
                                    <div class="col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?= $delayK; ?>">
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
                                    <?php $delayK = ($delayK+100)%500; endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($totalPagesK > 1): ?>
                        <div class="d-flex justify-content-center mt-4 keg-pagination-wrap">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <?php if ($_page > 1): ?>
                                    <li class="page-item"><a class="page-link ajax-keg-page" href="ajax_kegiatan?page=<?= $_page-1; ?><?= ($_kat!==''?'&kategori='.urlencode($_kat):''); ?><?= ($_search!==''?'&q='.urlencode($_search):''); ?><?= ($_from!==''?'&dari='.urlencode($_from):''); ?><?= ($_to!==''?'&sampai='.urlencode($_to):''); ?>">&laquo;</a></li>
                                    <?php endif; ?>
                                    <?php for ($p=1;$p<=$totalPagesK;$p++): ?>
                                    <li class="page-item <?= $p===$_page ? 'active' : ''; ?>"><a class="page-link ajax-keg-page <?= $p===$_page?'active':''; ?>" href="ajax_kegiatan?page=<?= $p; ?><?= ($_kat!==''?'&kategori='.urlencode($_kat):''); ?><?= ($_search!==''?'&q='.urlencode($_search):''); ?><?= ($_from!==''?'&dari='.urlencode($_from):''); ?><?= ($_to!==''?'&sampai='.urlencode($_to):''); ?>"><?= $p; ?></a></li>
                                    <?php endfor; ?>
                                    <?php if ($_page < $totalPagesK): ?>
                                    <li class="page-item"><a class="page-link ajax-keg-page" href="ajax_kegiatan?page=<?= $_page+1; ?><?= ($_kat!==''?'&kategori='.urlencode($_kat):''); ?><?= ($_search!==''?'&q='.urlencode($_search):''); ?><?= ($_from!==''?'&dari='.urlencode($_from):''); ?><?= ($_to!==''?'&sampai='.urlencode($_to):''); ?>">&raquo;</a></li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>
                    </div>
                    </div>
                </div>
            </div>
            
            <!-- Calendar View -->
            <div class="tab-content" id="calendar-view">
                <div class="row mb-4">
                    <div class="col-12" data-aos="fade-up">
                        <!-- Event Categories -->
                        <div class="event-categories">
                            <div class="event-category-item">
                                <span class="category-dot event-category-workshop"></span>
                                <span>Workshop</span>
                            </div>
                            <div class="event-category-item">
                                <span class="category-dot event-category-seminar"></span>
                                <span>Seminar</span>
                            </div>
                            <div class="event-category-item">
                                <span class="category-dot event-category-pelatihan"></span>
                                <span>Pelatihan</span>
                            </div>
                            <div class="event-category-item">
                                <span class="category-dot event-category-kolaborasi"></span>
                                <span>Kolaborasi</span>
                            </div>
                            <div class="event-category-item">
                                <span class="category-dot event-category-advokasi"></span>
                                <span>Advokasi</span>
                            </div>
                        </div>
                        
                        <!-- Calendar Container -->
                        <div class="calendar-container" data-aos="fade-up">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-md-8" data-aos="fade-up">
                    <h2 class="mb-3">Dapatkan Informasi Kegiatan Terbaru</h2>
                    <p class="mb-4">Daftar newsletter HIPPMI untuk mendapatkan update terbaru mengenai kegiatan pendidikan</p>
                    <form class="newsletter-form">
                        <input type="email" class="newsletter-input" placeholder="Masukkan alamat email Anda" required>
                        <button type="submit" class="newsletter-btn">Berlangganan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Kegiatan Detail Modal (dinamis) -->
    <div class="modal fade" id="kegiatanDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius:20px;overflow:hidden;">
                <div class="modal-header py-3 px-4" style="background:linear-gradient(135deg,var(--primary-color),var(--primary-dark));color:#fff;">
                    <span class="badge bg-white text-danger px-2 py-1 rounded-pill" id="kegModalCat" style="font-size:11px;">Kegiatan</span>
                    <h5 class="modal-title fs-6 text-white text-truncate mb-0 ms-2" id="kegModalTitle" style="max-width:480px;">Detail Kegiatan</h5>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <img src="" id="kegModalImg" class="img-fluid rounded-4 mb-4 w-100" style="max-height:380px;object-fit:cover;" alt="Kegiatan" onerror="this.src='https://placehold.co/800x400?text=Kegiatan+HIPPMI';">
                    <h3 class="fw-bold mb-3" id="kegModalHeading"></h3>
                    <div class="d-flex align-items-center text-muted small mb-3 gap-4 flex-wrap border-bottom pb-3">
                        <span><i class="far fa-calendar-alt me-2 text-danger"></i><span id="kegModalDate"></span></span>
                        <span><i class="far fa-user me-2 text-danger"></i><span id="kegModalPenulis"></span></span>
                    </div>
                    <div id="kegModalKonten" class="lh-lg text-secondary" style="text-align:justify;text-justify:inter-word;font-size:0.98rem;"></div>
                    <div id="kegModalDaftarWrap" class="mt-3" style="display:none;">
                        <a id="kegModalDaftarBtn" href="#" target="_blank" rel="noopener" class="btn-register d-inline-flex align-items-center gap-2 text-decoration-none"><i class="fa-solid fa-arrow-up-right-from-square"></i> Daftar Sekarang</a>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4"><button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button></div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <!-- Custom Script -->
    <script>
        AOS.init({
            duration: 800,
            once: false,
            offset: 100
        });
        
        // Preloader
        window.addEventListener('load', function() {
            document.querySelector('.preloader').style.opacity = '0';
            setTimeout(function() {
                document.querySelector('.preloader').style.display = 'none';
            }, 500);
        });
        
        // Back to Top Button
        window.addEventListener('scroll', function() {
            var backToTop = document.querySelector('.back-to-top');
            if (window.pageYOffset > 300) {
                backToTop.classList.add('active');
            } else {
                backToTop.classList.remove('active');
            }
        });
        
        document.querySelector('.back-to-top').addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Tab Navigation
        document.querySelectorAll('.custom-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.custom-tab').forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Show the corresponding tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId + '-view').classList.add('active');
                
                if (tabId === 'calendar' && typeof calendarInstance !== 'undefined' && calendarInstance) {
                    setTimeout(function(){ calendarInstance.render(); }, 50);
                }
            });
        });
        
        // AJAX Filter/Search/Pagination for Kegiatan (6/page, tanpa refresh)
        (function(){
            var resultsEl = document.getElementById('keg-results');
            if(!resultsEl) return;
            var searchInput = document.getElementById('kegSearchInput');
            var searchBtn = document.getElementById('kegSearchBtn');
            var applyBtn = document.getElementById('kegApplyBtn');
            var resetBtn = document.getElementById('kegResetBtn');
            var dariEl = document.getElementById('kegDari');
            var sampaiEl = document.getElementById('kegSampai');
            var loading = false;

            function getActiveKategori(){
                var a = document.querySelector('.keg-cat-btn.active');
                return a ? (a.getAttribute('data-kategori') || '') : '';
            }
            function setActiveKategori(cat){
                document.querySelectorAll('.keg-cat-btn').forEach(function(b){
                    b.classList.toggle('active', (b.getAttribute('data-kategori')||'') === cat);
                });
            }
            function buildUrl(page){
                var p = new URLSearchParams();
                var q = searchInput ? searchInput.value.trim() : '';
                if(q) p.set('q', q);
                var kat = getActiveKategori();
                if(kat) p.set('kategori', kat);
                var dari = dariEl ? dariEl.value : '';
                if(dari) p.set('dari', dari);
                var sampai = sampaiEl ? sampaiEl.value : '';
                if(sampai) p.set('sampai', sampai);
                if(page) p.set('page', page);
                return 'ajax_kegiatan?' + p.toString();
            }
            function updateResetVisibility(){
                if(!resetBtn) return;
                var q = searchInput ? searchInput.value.trim() : '';
                var kat = getActiveKategori();
                var dari = dariEl ? dariEl.value : '';
                var sampai = sampaiEl ? sampaiEl.value : '';
                resetBtn.style.display = (q || kat || dari || sampai) ? '' : 'none';
            }
            function fetchKegiatan(url){
                if(loading) return;
                loading = true;
                fetch(url).then(function(r){ return r.text(); }).then(function(html){
                    resultsEl.innerHTML = html;
                    loading = false;
                    bindPagination();
                    if(window.AOS) AOS.refresh();
                    var clean = url.replace(/^ajax_kegiatan/, 'kegiatan.php');
                    history.replaceState(null, '', clean === 'kegiatan.php?' ? 'kegiatan.php' : clean);
                    updateResetVisibility();
                }).catch(function(e){ loading = false; console.error(e); });
            }
            function bindPagination(){
                document.querySelectorAll('.ajax-keg-page').forEach(function(a){
                    a.addEventListener('click', function(e){
                        e.preventDefault();
                        fetchKegiatan(this.getAttribute('href'));
                    });
                });
            }
            document.querySelectorAll('.keg-cat-btn').forEach(function(btn){
                btn.addEventListener('click', function(){
                    setActiveKategori(this.getAttribute('data-kategori')||'');
                    fetchKegiatan(buildUrl(1));
                });
            });
            if(searchBtn) searchBtn.addEventListener('click', function(e){ e.preventDefault(); fetchKegiatan(buildUrl(1)); });
            if(searchInput) searchInput.addEventListener('keypress', function(e){ if(e.key==='Enter'){ e.preventDefault(); fetchKegiatan(buildUrl(1)); }});
            if(applyBtn) applyBtn.addEventListener('click', function(e){ e.preventDefault(); fetchKegiatan(buildUrl(1)); });
            if(resetBtn) resetBtn.addEventListener('click', function(e){
                e.preventDefault();
                if(searchInput) searchInput.value = '';
                if(dariEl) dariEl.value = '';
                if(sampaiEl) sampaiEl.value = '';
                setActiveKategori('');
                fetchKegiatan(buildUrl(1));
            });
            bindPagination();
            updateResetVisibility();
        })();
        
        function openKegiatanModal(data){
            document.getElementById('kegModalCat').textContent = data.kategori || 'Kegiatan';
            document.getElementById('kegModalTitle').textContent = data.judul || '';
            document.getElementById('kegModalHeading').textContent = data.judul || '';
            document.getElementById('kegModalDate').textContent = data.tanggal || '';
            document.getElementById('kegModalPenulis').textContent = data.penulis || '';
            document.getElementById('kegModalImg').src = data.gambar || '';
            document.getElementById('kegModalKonten').innerHTML = data.konten || data.ringkasan || '';
            var wrap = document.getElementById('kegModalDaftarWrap');
            var btn = document.getElementById('kegModalDaftarBtn');
            if (wrap && btn) {
                if (data.link_daftar) { btn.href = data.link_daftar; wrap.style.display = 'block'; }
                else { wrap.style.display = 'none'; }
            }
            if (data.id) {
                fetch('ajax_views.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'id='+encodeURIComponent(data.id)})
                .then(function(r){ return r.json(); }).catch(function(){});
            }
            new bootstrap.Modal(document.getElementById('kegiatanDetailModal')).show();
        }
        var calendarInstance = null;
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            calendarInstance = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                locale: 'id',
                events: <?= json_encode($calendarEvents, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>,
                eventClick: function(info) {
                    var p = info.event.extendedProps;
                    openKegiatanModal({
                        id: p.id,
                        judul: info.event.title,
                        kategori: p.kategori,
                        tanggal: p.tanggal || info.event.startStr,
                        penulis: p.penulis || '',
                        gambar: p.gambar || '',
                        konten: p.konten || '',
                        ringkasan: p.ringkasan || '',
                        views: p.views || 0,
                        link_daftar: p.link_daftar || ''
                    });
                },
                eventDidMount: function(info) {
                    new bootstrap.Tooltip(info.el, {
                        title: info.event.title + '<br><i class="far fa-calendar-alt"></i> ' + (info.event.extendedProps.tanggal || info.event.startStr),
                        placement: 'top',
                        trigger: 'hover',
                        html: true
                    });
                }
            });
            calendarInstance.render();
        });
        
        // Newsletter Form Submission
        const newsletterForm = document.querySelector('.newsletter-form');
        
        if(newsletterForm) {
            newsletterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const input = this.querySelector('.newsletter-input');
                const originalBtnText = this.querySelector('.newsletter-btn').innerText;
                
                // Visual feedback
                this.querySelector('.newsletter-btn').innerText = 'Mengirim...';
                
                // Simulate form submission
                setTimeout(() => {
                    alert('Terima kasih telah berlangganan newsletter HIPPMI!');
                    input.value = '';
                    this.querySelector('.newsletter-btn').innerText = originalBtnText;
                }, 1500);
            });
        }
    </script>
</body>
</html>