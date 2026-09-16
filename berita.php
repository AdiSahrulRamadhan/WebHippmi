<?php
require_once __DIR__ . '/koneksi.php';
$pdo = getDBConnection();

// Query Berita Utama (Featured) - slider hanya tipe berita
$stmtFeatured = $pdo->prepare("
    SELECT * FROM `berita` 
    WHERE `status` = 'published' AND `is_featured` = 1 AND `tipe` = 'berita'
    ORDER BY `tanggal` DESC, `id` DESC
");
$stmtFeatured->execute();
$featuredNewsAll = $stmtFeatured->fetchAll();

if (empty($featuredNewsAll)) {
    $stmtFeaturedFallback = $pdo->prepare("
        SELECT * FROM `berita` 
        WHERE `status` = 'published' AND `tipe` = 'berita'
        ORDER BY `tanggal` DESC, `id` DESC 
        LIMIT 1
    ");
    $stmtFeaturedFallback->execute();
    $fallbackNews = $stmtFeaturedFallback->fetch();
    $featuredNewsAll = $fallbackNews ? [$fallbackNews] : [];
}

$featuredNews = $featuredNewsAll[0] ?? null;

// Filter Kategori, Tahun & Pencarian
$searchKeyword = trim((string) ($_GET['q'] ?? ''));
$currentCategory = trim((string) ($_GET['kategori'] ?? ''));
$currentYear = trim((string) ($_GET['tahun'] ?? ''));

// Hitung total untuk pagination - hanya tipe berita
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

// Query Daftar Berita Grid - hanya tipe berita
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

// Query Kegiatan Mendatang - maks 3 terdekat, tanggal >= hari ini
$stmtUpcoming = $pdo->prepare("
    SELECT * FROM `berita` 
    WHERE `status` = 'published'
    AND `tanggal` >= CURDATE()
    ORDER BY `tanggal` ASC, `id` ASC
    LIMIT 3
");
$stmtUpcoming->execute();
$upcomingEvents = $stmtUpcoming->fetchAll();

// Query 4 Berita Terbaru untuk Sidebar - hanya tipe berita
$stmtRecent = $pdo->query("
    SELECT * FROM `berita` 
    WHERE `status` = 'published' AND `tipe` = 'berita'
    ORDER BY `tanggal` DESC, `id` DESC 
    LIMIT 4
");
$recentNewsList = $stmtRecent->fetchAll();

// Query Hitung Kategori untuk Sidebar - hanya tipe berita
$stmtCatCount = $pdo->query("
    SELECT `kategori`, COUNT(*) as `total` 
    FROM `berita` 
    WHERE `status` = 'published' AND `tipe` = 'berita'
    GROUP BY `kategori`
");
$categoriesCount = $stmtCatCount->fetchAll(PDO::FETCH_KEY_PAIR);

$defaultCatsB = ['Pendidikan', 'Workshop', 'Seminar', 'Kolaborasi', 'Advokasi', 'Pengumuman'];
try { $catsB = $pdo->query("SELECT DISTINCT `kategori` FROM `berita` WHERE `status`='published' AND `tipe`='berita' AND `kategori`<>'' ORDER BY `kategori` ASC")->fetchAll(PDO::FETCH_COLUMN); } catch (Throwable $e) { $catsB = []; }
$allCategories = array_values(array_unique(array_merge($defaultCatsB, $catsB))); sort($allCategories);

// Daftar tahun untuk filter - hanya tipe berita
$stmtYears = $pdo->query("SELECT DISTINCT YEAR(`tanggal`) AS `yr` FROM `berita` WHERE `status` = 'published' AND `tipe` = 'berita' ORDER BY `yr` DESC");
$yearList = $stmtYears->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita - HIPPMI</title>
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
    <!-- Swiper Slider CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
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
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80');
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
        
        /* Enhanced News Card Styling */
        .news-card {
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
        
        .news-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(227, 10, 23, 0.15);
        }
        
        .news-img-container {
            overflow: hidden;
            position: relative;
            height: 220px;
        }
        
        .news-img-container img {
            transition: transform 0.8s ease;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .news-card:hover .news-img-container img {
            transform: scale(1.1);
        }
        
        .news-category {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 1;
            box-shadow: 0 3px 8px rgba(227, 10, 23, 0.25);
        }
        
        .card-body {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        .news-date {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        
        .news-date i {
            margin-right: 6px;
            color: var(--primary-color);
        }
        
        .news-title {
            color: var(--dark-color);
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 15px;
            transition: var(--transition);
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }
        
        .news-card:hover .news-title {
            color: var(--primary-color);
        }
        
        .news-excerpt {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.6;
            flex-grow: 1;
            text-align: justify;
            text-decoration: none;
        }
        
        .news-card p, .news-card a { text-decoration: none; }
        .news-excerpt { text-align: justify; text-justify: inter-word; }
        .btn-read-more {
            display: inline-block;
            padding: 8px 20px;
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none !important;
            margin-top: 15px;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .btn-read-more:hover {
            background: var(--primary-color) !important;
            color: white !important;
            border-color: var(--primary-color) !important;
            transform: translateY(-3px);
            box-shadow: 0 5px 12px rgba(227, 10, 23, 0.25);
        }
        .news-full-content,
        .news-full-content p {
            text-align: justify;
            text-justify: inter-word;
            hyphens: auto;
        }
        
        .news-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            margin-top: auto;
            border-top: 1px solid #eee;
        }
        
        .news-author {
            display: flex;
            align-items: center;
        }
        
        .news-author img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 10px;
            object-fit: cover;
            border: 2px solid rgba(227, 10, 23, 0.1);
        }
        
        .news-author-name {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .news-stats {
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        
        .news-stats i {
            color: var(--primary-color);
            opacity: 0.8;
        }
        
        .news-card-container {
            margin-bottom: 30px;
        }
        
        /* Fix card height issue */
        .row.news-row {
            display: flex;
            flex-wrap: wrap;
        }
        
        .row.news-row > div {
            margin-bottom: 30px;
        }
        
        /* Featured News Styling */
        .featured-news {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: var(--transition);
        }
        
        .featured-news:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(227, 10, 23, 0.2);
        }
        
        .featured-img {
            height: 500px;
            width: 100%;
            object-fit: cover;
        }
        
        .featured-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 30px;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
        }
        
        .featured-category {
            display: inline-block;
            background: var(--primary-color);
            color: white;
            padding: 5px 15px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .featured-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .featured-meta {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.9rem;
        }
        
        .featured-author {
            display: flex;
            align-items: center;
        }
        
        .featured-author img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            border: 2px solid white;
        }
        
        /* Search and Filter */
        .search-box {
            position: relative;
            margin-bottom: 30px;
        }
        
        .search-input {
            width: 100%;
            padding: 15px 20px;
            border-radius: 50px;
            border: 1px solid #eee;
            padding-right: 50px;
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(227, 10, 23, 0.15);
            border-color: var(--primary-color);
            outline: none;
        }
        
        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary-color);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }
        
         .search-btn:hover {
             background: var(--primary-dark);
         }

         /* Filter Dropdown */
         .search-filter-bar {
             position: relative;
         }

         .filter-dropdown {
             position: absolute;
             top: 100%;
             right: 0;
             z-index: 1000;
             background: #fff;
             border: 1px solid #eee;
             border-radius: 12px;
             box-shadow: 0 10px 30px rgba(0,0,0,0.15);
             width: 260px;
             padding: 15px;
             opacity: 0;
             visibility: hidden;
             transform: translateY(-10px);
             transition: all 0.3s ease;
             pointer-events: none;
         }

         .filter-dropdown.show {
             opacity: 1;
             visibility: visible;
             transform: translateY(0);
             pointer-events: auto;
         }

         .filter-dropdown-header {
             display: flex;
             justify-content: space-between;
             align-items: center;
             margin-bottom: 12px;
         }

         .filter-close-btn {
             background: none;
             border: none;
             color: var(--text-muted);
             cursor: pointer;
             font-size: 1rem;
             padding: 0;
         }

         .filter-label-small {
             font-size: 0.8rem;
             color: var(--dark-color);
             margin-bottom: 5px;
             display: block;
             font-weight: 500;
         }

         .filter-dropdown .form-select {
             border-radius: 8px;
             margin-bottom: 12px;
             border: 1px solid #ced4da;
             font-size: 0.9rem;
         }

         .category-filter {
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            margin-bottom: 30px;
            overflow-x: auto;
            padding-bottom: 10px;
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
        
        .category-filter::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }
        
        .category-btn {
            white-space: nowrap;
            padding: 8px 20px;
            border-radius: 30px;
            background: #f5f5f5;
            color: var(--dark-color);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9rem;
            text-decoration: none !important;
        }
        
        .category-btn.active, .category-btn:hover {
            background: var(--primary-color);
            color: white;
            text-decoration: none !important;
        }
        
        /* Pagination */
        .pagination-custom {
            display: flex;
            justify-content: center;
            margin-top: 50px;
        }
        
        .page-item-custom {
            margin: 0 5px;
        }
        
        .page-link-custom {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: var(--dark-color);
            border: 1px solid #eee;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .page-link-custom:hover, .page-link-custom.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Sidebar */
        .sidebar-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .sidebar-header {
            background: var(--primary-color);
            color: white;
            padding: 15px 20px;
        }
        
        .sidebar-title {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .sidebar-body {
            padding: 20px;
        }
        
        .recent-post {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .recent-post:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .recent-post-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .recent-post-info {
            flex: 1;
        }
        
        .recent-post-date {
            color: var(--text-muted);
            font-size: 0.8rem;
            margin-bottom: 5px;
        }
        
        .recent-post-title {
            font-size: 0.95rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .recent-post-title a {
            color: var(--dark-color);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .recent-post-title a:hover {
            color: var(--primary-color);
        }
        
        /* Event styling */
        .event-details p {
            font-size: 0.9rem;
            color: var(--dark-color);
            line-height: 1.5;
        }

        .list-group-item {
            transition: var(--transition);
            border-radius: 10px;
            padding: 15px;
        }

        .list-group-item:hover {
            background-color: rgba(248, 249, 250, 0.7);
        }

        .sidebar-body .list-group {
            margin-bottom: 0;
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
        
        /* Button Styling */
        .btn-nasional {
            background-color: var(--primary-color);
            color: var(--secondary-color);
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 500;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .btn-nasional:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
            z-index: -1;
        }
        
        .btn-nasional:hover:before {
            left: 100%;
        }
        
        .btn-nasional:hover {
            background-color: var(--primary-dark);
            color: var(--secondary-color);
            box-shadow: 0 5px 15px rgba(227, 10, 23, 0.3);
            transform: translateY(-3px);
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
        
        /* Galeri Swiper (jangan mempengaruhi Berita Utama) */
        .swiper-container {
            width: 100%;
            padding-top: 20px;
            padding-bottom: 50px;
        }
        .swiper-container .swiper-slide {
            background-position: center;
            background-size: cover;
            width: 300px;
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }
        .swiper-container .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        .swiper-container .swiper-slide:hover img { transform: scale(1.1); }
        .swiper-container .swiper-slide-content {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            padding: 20px;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
        }
        .swiper-slide-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .swiper-slide-category {
            background: var(--primary-color);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            margin-bottom: 10px;
            display: inline-block;
        }
        /* Featured Swiper - ukuran persis 1:1 dengan single featured-news */
        .featured-swiper {
            width: 100%;
            padding-bottom: 40px;
        }
        .featured-swiper .swiper-wrapper {
            align-items: stretch;
        }
        .featured-swiper .swiper-slide {
            width: 100% !important;
            height: auto !important;
            border-radius: 15px;
            overflow: hidden;
            background: transparent;
            flex-shrink: 0;
            display: flex;
        }
        .featured-swiper .featured-news {
            margin-bottom: 0;
            width: 100%;
            flex: 1 0 auto;
            border-radius: 15px;
        }
        .featured-swiper .featured-img {
            display: block;
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .featured-swiper .swiper-button-next,
        .featured-swiper .swiper-button-prev {
            color: var(--primary-color);
            background: rgba(255, 255, 255, 0.9);
            width: 44px;
            height: 44px;
            border-radius: 50%;
        }

        .featured-swiper .swiper-pagination,
        .swiper-container .swiper-pagination {
            bottom: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 6px;
        }
        .featured-swiper .swiper-pagination-bullet,
        .swiper-container .swiper-pagination-bullet {
            width: 8px;
            height: 8px;
            background: rgba(255,255,255,0.65);
            border: 1.5px solid rgba(227,10,23,0.35);
            opacity: 1;
            transition: all 0.3s ease;
            box-shadow: 0 1px 6px rgba(0,0,0,0.15);
        }
        .featured-swiper .swiper-pagination-bullet-active,
        .swiper-container .swiper-pagination-bullet-active {
            width: 26px;
            border-radius: 99px;
            background: var(--primary-color);
            border-color: var(--primary-color);
            box-shadow: 0 2px 10px rgba(227,10,23,0.45);
        }

        /* Media Queries */
        @media (max-width: 992px) {
            .featured-img {
                height: 400px;
            }
            
            .featured-title {
                font-size: 1.5rem;
            }
            
            .sidebar {
                margin-top: 40px;
            }
        }
        
        @media (max-width: 768px) {
            .featured-img {
                height: 350px;
            }
            
            .featured-title {
                font-size: 1.3rem;
            }
            
            .news-title {
                font-size: 1.1rem;
            }
            
            .featured-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
        
        @media (max-width: 576px) {
            .featured-img {
                height: 300px;
            }
            
            .featured-overlay {
                padding: 15px;
            }
            
            .featured-title {
                font-size: 1.2rem;
            }
            .section-header {
                display: block;
                text-align: center;
                width: 100%;
            }
            .section-header:after {
                left: 50%;
                transform: translateX(-50%);
            }
            .page-header h1,
            .page-header .lead {
                text-align: center;
            }
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
            <h1 class="display-4 fw-bold mb-3" data-aos="fade-up">Berita</h1>
            <p class="lead mb-4" data-aos="fade-up" data-aos-delay="200">Informasi terkini mengenai perkembangan HIPPMI</p>
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

     <!-- Featured News -->
     <?php if (!empty($featuredNewsAll)): ?>
         <section class="py-5 bg-light-section">
             <div class="container">
                 <div class="row mb-4">
                     <div class="col-md-12" data-aos="fade-right">
                         <h2 class="section-header">Berita Utama</h2>
                     </div>
                 </div>
                 <?php if (count($featuredNewsAll) > 1): ?>
                     <div class="swiper featured-swiper" data-aos="zoom-in">
                         <div class="swiper-wrapper">
                             <?php foreach ($featuredNewsAll as $featuredNews): ?>
                                 <?php
                                     $featImg = !empty($featuredNews['gambar']) ? $featuredNews['gambar'] : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=1470';
                                     $featAvatar = !empty($featuredNews['penulis_avatar']) ? $featuredNews['penulis_avatar'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100';
                                     $featJson = htmlspecialchars(json_encode([
                                         'id' => $featuredNews['id'],
                                         'judul' => $featuredNews['judul'],
                                         'kategori' => $featuredNews['kategori'],
                                         'penulis' => $featuredNews['penulis'],
                                         'tanggal' => formatTanggalIndonesia($featuredNews['tanggal']),
                                         'gambar' => $featImg,
                                         'konten' => $featuredNews['konten'],
                                         'views' => $featuredNews['views']
                                     ]), ENT_QUOTES, 'UTF-8');
                                 ?>
                                 <div class="swiper-slide">
                                     <div class="featured-news" style="cursor: pointer;" onclick="openNewsModal(<?= $featJson; ?>)">
                                         <img src="<?= htmlspecialchars($featImg); ?>" alt="Featured News" class="featured-img" onerror="this.src='https://placehold.co/1200x600?text=Berita+Utama';">
                                         <div class="featured-overlay">
                                             <span class="featured-category"><?= htmlspecialchars($featuredNews['kategori']); ?></span>
                                             <h2 class="featured-title"><?= htmlspecialchars($featuredNews['judul']); ?></h2>
                                             <div class="featured-meta">
                                                 <div class="featured-author">
                                                     <img src="<?= htmlspecialchars($featAvatar); ?>" alt="Author" onerror="this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100';">
                                                     <span><?= htmlspecialchars($featuredNews['penulis']); ?></span>
                                                 </div>
                                                 <div class="featured-date">
                                                     <i class="far fa-calendar-alt me-1"></i> <?= formatTanggalIndonesia($featuredNews['tanggal']); ?>
                                                 </div>
                                                 <div class="featured-views">
                                                     <i class="far fa-eye me-1"></i> <?= number_format($featuredNews['views']); ?> Views
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             <?php endforeach; ?>
                          </div>
                          <div class="swiper-pagination"></div>
                      </div>
                  <?php else: ?>
                     <?php $featuredNews = $featuredNewsAll[0]; ?>
                     <?php
                         $featImg = !empty($featuredNews['gambar']) ? $featuredNews['gambar'] : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=1470';
                         $featAvatar = !empty($featuredNews['penulis_avatar']) ? $featuredNews['penulis_avatar'] : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100';
                         $featJson = htmlspecialchars(json_encode([
                             'id' => $featuredNews['id'],
                             'judul' => $featuredNews['judul'],
                             'kategori' => $featuredNews['kategori'],
                             'penulis' => $featuredNews['penulis'],
                             'tanggal' => formatTanggalIndonesia($featuredNews['tanggal']),
                             'gambar' => $featImg,
                             'konten' => $featuredNews['konten'],
                             'views' => $featuredNews['views']
                         ]), ENT_QUOTES, 'UTF-8');
                     ?>
                     <div class="row" data-aos="zoom-in">
                         <div class="col-12">
                             <div class="featured-news" style="cursor: pointer;" onclick="openNewsModal(<?= $featJson; ?>)">
                                 <img src="<?= htmlspecialchars($featImg); ?>" alt="Featured News" class="featured-img" onerror="this.src='https://placehold.co/1200x600?text=Berita+Utama';">
                                 <div class="featured-overlay">
                                     <span class="featured-category"><?= htmlspecialchars($featuredNews['kategori']); ?></span>
                                     <h2 class="featured-title"><?= htmlspecialchars($featuredNews['judul']); ?></h2>
                                     <div class="featured-meta">
                                         <div class="featured-author">
                                             <img src="<?= htmlspecialchars($featAvatar); ?>" alt="Author" onerror="this.src='https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100';">
                                             <span><?= htmlspecialchars($featuredNews['penulis']); ?></span>
                                         </div>
                                         <div class="featured-date">
                                             <i class="far fa-calendar-alt me-1"></i> <?= formatTanggalIndonesia($featuredNews['tanggal']); ?>
                                         </div>
                                         <div class="featured-views">
                                             <i class="far fa-eye me-1"></i> <?= number_format($featuredNews['views']); ?> Views
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                     </div>
                 <?php endif; ?>
             </div>
         </section>
     <?php endif; ?>
    
    <!-- Main Content -->
    <section class="py-5 bg-white-section">
        <div class="container">
            <div class="row">
                <!-- News List -->
                <div class="col-lg-8">
                     <!-- Search and Filters -->
                     <div class="search-filter-bar mb-4" data-aos="fade-up">
                         <div class="position-relative">
                             <input type="text" id="newsSearchInput" class="search-input" placeholder="Cari berita atau kegiatan..." value="<?= htmlspecialchars($searchKeyword); ?>">
                             <button id="newsSearchBtn" type="submit" class="search-btn">
                                 <i class="fas fa-search"></i>
                             </button>
                             <button id="filterToggleBtn" type="button" class="search-btn" style="right: 55px;">
                                 <i class="fas fa-sliders-h"></i>
                             </button>
                         </div>
                         <!-- Filter Dropdown -->
                         <div id="filterDropdown" class="filter-dropdown">
                             <div class="filter-dropdown-header">
                                 <strong>Filter</strong>
                                 <button id="filterCloseBtn" type="button" class="filter-close-btn"><i class="fas fa-times"></i></button>
                             </div>
                             <div class="filter-dropdown-body">
                                 <label class="filter-label-small">Kategori</label>
                                 <select id="filterKategori" class="form-select form-select-sm">
                                     <option value="">Semua Kategori</option>
                                     <?php foreach ($allCategories as $cat): ?>
                                         <option value="<?= $cat; ?>" <?= $currentCategory === $cat ? 'selected' : ''; ?>><?= htmlspecialchars($cat); ?></option>
                                     <?php endforeach; ?>
                                 </select>
                                 <label class="filter-label-small">Tahun</label>
                                 <select id="filterTahun" class="form-select form-select-sm">
                                     <option value="">Semua Tahun</option>
                                     <?php foreach ($yearList as $yr): ?>
                                         <option value="<?= $yr; ?>" <?= $currentYear == $yr ? 'selected' : ''; ?>><?= $yr; ?></option>
                                     <?php endforeach; ?>
                                 </select>
                                 <button id="filterApplyBtn" type="button" class="btn btn-danger w-100 btn-sm">Terapkan</button>
                             </div>
                         </div>
                     </div>

                     <div class="category-filter" data-aos="fade-up" data-aos-delay="100">
                         <a href="ajax_berita.php<?= !empty($searchKeyword) ? '?q=' . urlencode($searchKeyword) : ''; ?><?= !empty($currentCategory) ? (empty($searchKeyword) ? '?' : '&') . 'kategori=' . urlencode($currentCategory) : ''; ?><?= !empty($currentYear) && $currentYear !== 'Semua' ? (strpos((!empty($searchKeyword) ? 'q=' . urlencode($searchKeyword) : '') . (!empty($currentCategory) ? 'kategori=' . urlencode($currentCategory) : ''), '?') === false ? '?' : '&') . 'tahun=' . urlencode($currentYear) : ''; ?>" class="category-btn ajax-filter <?= empty($currentCategory) || $currentCategory === 'Semua' ? 'active' : ''; ?>" data-q="<?= htmlspecialchars($searchKeyword); ?>" data-kategori="<?= htmlspecialchars($currentCategory); ?>" data-tahun="<?= htmlspecialchars($currentYear); ?>">Semua</a>
                         <?php foreach ($allCategories as $cat): ?>
                             <a href="ajax_berita?kategori=<?= urlencode($cat); ?><?= !empty($searchKeyword) ? '&q=' . urlencode($searchKeyword) : ''; ?><?= !empty($currentYear) && $currentYear !== 'Semua' ? '&tahun=' . urlencode($currentYear) : ''; ?>" class="category-btn ajax-filter <?= $currentCategory === $cat ? 'active' : ''; ?>" data-q="<?= htmlspecialchars($searchKeyword); ?>" data-kategori="<?= $cat; ?>" data-tahun="<?= htmlspecialchars($currentYear); ?>"><?= htmlspecialchars($cat); ?></a>
                         <?php endforeach; ?>
                     </div>

                     <!-- News Grid -->
                     <div id="news-results">
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

                     <!-- Pagination -->
                     <?php if ($totalPages > 1): ?>
                         <div class="pagination-custom" data-aos="fade-up">
                             <?php if ($currentPage > 1): ?>
                                 <div class="page-item-custom">
                                     <a href="ajax_berita?page=<?= $currentPage - 1; ?><?= !empty($currentCategory) && $currentCategory !== 'Semua' ? '&kategori=' . urlencode($currentCategory) : ''; ?><?= !empty($searchKeyword) ? '&q=' . urlencode($searchKeyword) : ''; ?><?= !empty($currentYear) && $currentYear !== 'Semua' ? '&tahun=' . urlencode($currentYear) : ''; ?>" class="page-link-custom ajax-pagination">
                                         <i class="fas fa-chevron-left"></i>
                                     </a>
                                 </div>
                             <?php endif; ?>
                             <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                 <div class="page-item-custom">
                                     <a href="ajax_berita?page=<?= $p; ?><?= !empty($currentCategory) && $currentCategory !== 'Semua' ? '&kategori=' . urlencode($currentCategory) : ''; ?><?= !empty($searchKeyword) ? '&q=' . urlencode($searchKeyword) : ''; ?><?= !empty($currentYear) && $currentYear !== 'Semua' ? '&tahun=' . urlencode($currentYear) : ''; ?>" class="page-link-custom ajax-pagination <?= $p === $currentPage ? 'active' : ''; ?>">
                                         <?= $p; ?>
                                     </a>
                                 </div>
                             <?php endfor; ?>
                             <?php if ($currentPage < $totalPages): ?>
                                 <div class="page-item-custom">
                                     <a href="ajax_berita?page=<?= $currentPage + 1; ?><?= !empty($currentCategory) && $currentCategory !== 'Semua' ? '&kategori=' . urlencode($currentCategory) : ''; ?><?= !empty($searchKeyword) ? '&q=' . urlencode($searchKeyword) : ''; ?><?= !empty($currentYear) && $currentYear !== 'Semua' ? '&tahun=' . urlencode($currentYear) : ''; ?>" class="page-link-custom ajax-pagination">
                                         <i class="fas fa-chevron-right"></i>
                                     </a>
                                 </div>
                             <?php endif; ?>
                         </div>
                     <?php endif; ?>
                      </div>
                </div>
                <!-- Sidebar -->
                <div class="col-lg-4 sidebar">
                    <!-- Recent Posts -->
                    <div class="sidebar-card" data-aos="fade-left">
                        <div class="sidebar-header">
                            <h3 class="sidebar-title">Berita Terbaru</h3>
                        </div>
                        <div class="sidebar-body">
                            <?php if (empty($recentNewsList)): ?>
                                <p class="text-muted small mb-0">Belum ada berita terbaru.</p>
                            <?php else: ?>
                                <?php foreach ($recentNewsList as $rec): ?>
                                    <?php
                                        $recImg = !empty($rec['gambar']) ? $rec['gambar'] : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=200';
                                        $recJson = htmlspecialchars(json_encode([
                                            'id' => $rec['id'],
                                            'judul' => $rec['judul'],
                                            'kategori' => $rec['kategori'],
                                            'penulis' => $rec['penulis'],
                                            'tanggal' => formatTanggalIndonesia($rec['tanggal']),
                                            'gambar' => $recImg,
                                            'konten' => $rec['konten'],
                                            'views' => $rec['views']
                                        ]), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <div class="recent-post" style="cursor: pointer;" onclick="openNewsModal(<?= $recJson; ?>)">
                                        <img src="<?= htmlspecialchars($recImg); ?>" class="recent-post-img" alt="Thumb" onerror="this.src='https://placehold.co/100x100?text=News';">
                                        <div class="recent-post-info">
                                            <div class="recent-post-date"><?= formatTanggalIndonesia($rec['tanggal']); ?></div>
                                            <h4 class="recent-post-title">
                                                <a href="javascript:void(0)"><?= htmlspecialchars($rec['judul']); ?></a>
                                            </h4>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Categories -->
                    <div class="sidebar-card" data-aos="fade-left" data-aos-delay="100">
                        <div class="sidebar-header">
                            <h3 class="sidebar-title">Kategori</h3>
                        </div>
                        <div class="sidebar-body">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($allCategories as $cat): ?>
                                    <?php $cnt = $categoriesCount[$cat] ?? 0; ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <a href="berita.php?kategori=<?= urlencode($cat); ?>" class="text-decoration-none text-dark <?= $currentCategory === $cat ? 'fw-bold text-danger' : ''; ?>">
                                            <?= htmlspecialchars($cat); ?>
                                        </a>
                                        <span class="badge rounded-pill bg-danger"><?= $cnt; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Kegiatan Mendatang -->
                    <div class="sidebar-card" data-aos="fade-left" data-aos-delay="200">
                        <div class="sidebar-header">
                            <h3 class="sidebar-title">Kegiatan Mendatang</h3>
                        </div>
                        <div class="sidebar-body">
                            <div class="list-group">
                                <?php if (empty($upcomingEvents)): ?>
                                    <p class="text-muted small mb-0">Belum ada kegiatan mendatang.</p>
                                <?php else: ?>
                                    <?php foreach ($upcomingEvents as $event): ?>
                                        <?php
                                            $evImgU = !empty($event['gambar']) ? $event['gambar'] : 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?w=600';
                                            $evAvU = !empty($event['penulis_avatar']) ? $event['penulis_avatar'] : '';
                                            $evJsonU = htmlspecialchars(json_encode([
                                                'id' => $event['id'],
                                                'judul' => $event['judul'],
                                                'kategori' => $event['kategori'],
                                                'penulis' => $event['penulis'],
                                                'tanggal' => formatTanggalIndonesia($event['tanggal']),
                                                'gambar' => $evImgU,
                                                'penulis_avatar' => $evAvU,
                                                'konten' => $event['konten'],
                                                'ringkasan' => $event['ringkasan'],
                                                'views' => (int)($event['views'] ?? 0)
                                            ]), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <div class="list-group-item border-0 px-0<?= $event !== reset($upcomingEvents) ? ' mt-3' : ''; ?>" style="cursor:pointer;transition:background 0.2s;border-radius:10px;" onclick='openNewsModal(<?= $evJsonU; ?>)' onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background='transparent'">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1 text-danger"><?= htmlspecialchars($event['judul']); ?></h5>
                                            </div>
                                            <div class="event-details">
                                                <p class="mb-1"><i class="far fa-calendar-alt me-2 text-danger"></i><?= formatTanggalIndonesia($event['tanggal']); ?></p>
                                                <p class="mb-1"><i class="fas fa-tag me-2 text-danger"></i><?= htmlspecialchars($event['kategori']); ?></p>
                                                <p class="mb-0 text-muted small"><?= htmlspecialchars($event['ringkasan']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <div class="mt-3 text-center">
                                <a href="kegiatan.php" class="btn-read-more">Lihat Semua Kegiatan</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Image Gallery Section -->
    <section class="py-5 bg-light-section">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-12" data-aos="fade-right">
                    <h2 class="section-header">Galeri Kegiatan</h2>
                </div>
            </div>
            <!-- Swiper Gallery -->
            <div class="swiper-container" data-aos="fade-up">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <img src="https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80" alt="Gallery Image">
                        <div class="swiper-slide-content">
                            <span class="swiper-slide-category">Workshop</span>
                            <h3 class="swiper-slide-title">Workshop Kurikulum Merdeka</h3>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="https://images.unsplash.com/photo-1531545514256-b1400bc00f31?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1374&q=80" alt="Gallery Image">
                        <div class="swiper-slide-content">
                            <span class="swiper-slide-category">Seminar</span>
                            <h3 class="swiper-slide-title">Seminar AI in Education</h3>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80" alt="Gallery Image">
                        <div class="swiper-slide-content">
                            <span class="swiper-slide-category">Advokasi</span>
                            <h3 class="swiper-slide-title">Forum Advokasi Kebijakan</h3>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80" alt="Gallery Image">
                        <div class="swiper-slide-content">
                            <span class="swiper-slide-category">Pelatihan</span>
                            <h3 class="swiper-slide-title">Pelatihan Pendidik Digital</h3>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <img src="https://images.unsplash.com/photo-1519452575417-564c1401ecc0?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1470&q=80" alt="Gallery Image">
                        <div class="swiper-slide-content">
                            <span class="swiper-slide-category">Kolaborasi</span>
                            <h3 class="swiper-slide-title">MoU dengan Kementerian</h3>
                        </div>
                    </div>
                </div>
                <!-- Swiper Pagination -->
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>
    
    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-md-8" data-aos="fade-up">
                    <h2 class="mb-3">Dapatkan Informasi Terbaru</h2>
                    <p class="mb-4">Daftar newsletter HIPPMI untuk mendapatkan update terbaru mengenai kegiatan dan berita pendidikan</p>
                    <form class="newsletter-form">
                        <input type="email" class="newsletter-input" placeholder="Masukkan alamat email Anda" required>
                        <button type="submit" class="newsletter-btn">Berlangganan</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Detail Berita -->
    <div class="modal fade" id="newsDetailModal" tabindex="-1" aria-labelledby="newsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); color: #fff;">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-white text-danger px-2 py-1 rounded-pill" id="modalCategory" style="font-size: 11px;">Kategori</span>
                        <h5 class="modal-title fs-6 text-white text-truncate mb-0" id="newsModalTitle" style="max-width: 480px;">Detail Berita</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <img src="" id="modalImage" class="img-fluid rounded-4 mb-4 w-100" style="max-height: 380px; object-fit: cover; box-shadow: 0 5px 20px rgba(0,0,0,0.08);" alt="Sampul Berita" onerror="this.src='https://placehold.co/800x400?text=Berita+HIPPMI';">
                    <h3 class="fw-bold mb-3 text-dark" id="modalHeadingTitle" style="line-height: 1.4;"></h3>
                    <div class="d-flex align-items-center text-muted small mb-4 pb-3 border-bottom gap-4 flex-wrap">
                        <span><i class="far fa-user me-2 text-danger"></i> <strong id="modalAuthor" class="text-dark"></strong></span>
                        <span><i class="far fa-calendar-alt me-2 text-danger"></i> <span id="modalDate"></span></span>
                        <span><i class="far fa-eye me-2 text-danger"></i> <span id="modalViews"></span> Kali Dibaca</span>
                    </div>
                    <div class="news-full-content lh-lg text-secondary" id="modalBodyContent" style="font-size: 0.98rem;">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <!-- Custom Script -->
    <script>
        function openNewsModal(data) {
            document.getElementById('modalCategory').textContent = data.kategori || 'Berita';
            document.getElementById('newsModalTitle').textContent = data.judul || '';
            document.getElementById('modalHeadingTitle').textContent = data.judul || '';
            document.getElementById('modalAuthor').textContent = data.penulis || 'Admin HIPPMI';
            document.getElementById('modalDate').textContent = data.tanggal || '';
            document.getElementById('modalImage').src = data.gambar || '';
            document.getElementById('modalBodyContent').innerHTML = data.konten || '';
            var vEl = document.getElementById('modalViews');
            var cur = parseInt(data.views || 0, 10);
            vEl.textContent = cur;
            if (data.id) {
                fetch('ajax_views.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/x-www-form-urlencoded'},
                    body: 'id=' + encodeURIComponent(data.id)
                }).then(function(r){ return r.json(); }).then(function(j){
                    if (j && j.ok && typeof j.views !== 'undefined') {
                        vEl.textContent = j.views;
                    } else if (j && typeof j.views !== 'undefined') {
                        vEl.textContent = j.views;
                    } else {
                        vEl.textContent = cur + 1;
                    }
                }).catch(function(){ vEl.textContent = cur + 1; });
            }
             const modalEl = document.getElementById('newsDetailModal');
             const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
             modal.show();
         }

         // AJAX News Search & Filter (tanpa refresh)
         (function() {
             const resultsEl = document.getElementById('news-results');
             if (!resultsEl) return;

             const searchInput = document.getElementById('newsSearchInput');
             const searchBtn = document.getElementById('newsSearchBtn');
             const filterToggleBtn = document.getElementById('filterToggleBtn');
             const filterDropdown = document.getElementById('filterDropdown');
             const filterCloseBtn = document.getElementById('filterCloseBtn');
             const filterApplyBtn = document.getElementById('filterApplyBtn');
             const filterKategori = document.getElementById('filterKategori');
             const filterTahun = document.getElementById('filterTahun');

             let loading = false;

             function getActiveCategory() {
                 const active = document.querySelector('.category-btn.ajax-filter.active');
                 return active ? active.getAttribute('data-kategori') : '';
             }

             function syncDropdownToActive() {
                 const kat = getActiveCategory();
                 if (filterKategori) {
                     filterKategori.value = kat;
                 }
             }

              function buildUrl(page) {
                  const params = new URLSearchParams();
                  const q = searchInput ? searchInput.value.trim() : '';
                  if (q) params.set('q', q);
                  const kat = getActiveCategory();
                  if (kat) params.set('kategori', kat);
                  const yr = filterTahun ? filterTahun.value : '';
                  if (yr) params.set('tahun', yr);
                  if (page) params.set('page', page);
                  return 'ajax_berita?' + params.toString();
              }

             function setActiveCategory(kategori) {
                 document.querySelectorAll('.category-btn.ajax-filter').forEach(function(btn) {
                     btn.classList.toggle('active', btn.getAttribute('data-kategori') === kategori);
                 });
             }

             function fetchNews(url) {
                 if (loading) return;
                 loading = true;
                 fetch(url)
                     .then(function(r) { return r.text(); })
                     .then(function(html) {
                         resultsEl.innerHTML = html;
                         loading = false;
                         bindResultsEvents();
                         if (window.AOS) AOS.refresh();
                     })
                     .catch(function(err) { loading = false; console.error(err); });
             }

             function bindResultsEvents() {
                  // Pagination
                  document.querySelectorAll('.ajax-pagination').forEach(function(link) {
                      link.addEventListener('click', function(e) {
                          e.preventDefault();
                          var cleanHref = this.href.replace(/^ajax_berita\.php/, 'ajax_berita');
                          fetchNews(cleanHref);
                          history.replaceState(null, '', cleanHref.replace(/^ajax_berita/, 'berita.php'));
                      });
                  });
                 // Category buttons
                 document.querySelectorAll('.category-btn.ajax-filter').forEach(function(link) {
                     link.addEventListener('click', function(e) {
                         e.preventDefault();
                         const kat = this.getAttribute('data-kategori');
                         setActiveCategory(kat);
                         syncDropdownToActive();
                         fetchNews(buildUrl(1));
                     });
                 });
                 // Reset
                 document.querySelectorAll('.ajax-reset').forEach(function(link) {
                     link.addEventListener('click', function(e) {
                         e.preventDefault();
                         if (searchInput) searchInput.value = '';
                         if (filterTahun) filterTahun.value = '';
                         setActiveCategory('');
                         fetchNews(buildUrl(1));
                     });
                 });
             }

             syncDropdownToActive();

             if (searchBtn) {
                 searchBtn.addEventListener('click', function(e) {
                     e.preventDefault();
                     fetchNews(buildUrl(1));
                 });
             }
             if (searchInput) {
                 searchInput.addEventListener('keypress', function(e) {
                     if (e.key === 'Enter') {
                         e.preventDefault();
                         fetchNews(buildUrl(1));
                     }
                 });
             }
             if (filterToggleBtn) {
                 filterToggleBtn.addEventListener('click', function(e) {
                     e.preventDefault();
                     filterDropdown.classList.toggle('show');
                 });
             }
             if (filterCloseBtn) {
                 filterCloseBtn.addEventListener('click', function(e) {
                     e.preventDefault();
                     filterDropdown.classList.remove('show');
                 });
             }
             if (filterApplyBtn) {
                 filterApplyBtn.addEventListener('click', function(e) {
                     e.preventDefault();
                     const kat = filterKategori ? filterKategori.value : '';
                     setActiveCategory(kat);
                     filterDropdown.classList.remove('show');
                     fetchNews(buildUrl(1));
                 });
             }
             // Tutup dropdown saat klik di luar
             document.addEventListener('click', function(e) {
                 if (filterDropdown && filterDropdown.contains(e.target)) return;
                 if (filterToggleBtn && e.target === filterToggleBtn) return;
                 if (filterDropdown && filterDropdown.classList.contains('show')) {
                     filterDropdown.classList.remove('show');
                 }
             });

             bindResultsEvents();
         })();

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
        
          var featuredSwiperEl = document.querySelector('.featured-swiper');
          if (featuredSwiperEl && featuredSwiperEl.querySelectorAll('.swiper-slide').length > 1) {
              var featuredSwiper = new Swiper('.featured-swiper', {
                  loop: true,
                  effect: 'fade',
                  fadeEffect: { crossFade: true },
                  allowTouchMove: true,
                  pagination: {
                      el: '.featured-swiper .swiper-pagination',
                      clickable: true,
                  },
                  autoplay: {
                      delay: 4000,
                      disableOnInteraction: false,
                  },
              });
          } else if (featuredSwiperEl) {
              var featuredSwiper = new Swiper('.featured-swiper', {
                  loop: false,
                  effect: 'fade',
                  fadeEffect: { crossFade: true },
                  pagination: {
                      el: '.featured-swiper .swiper-pagination',
                      clickable: true,
                  },
                  autoplay: false,
              });
          }

         var swiper = new Swiper('.swiper-container', {
            effect: 'coverflow',
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 'auto',
            coverflowEffect: {
                rotate: 20,
                stretch: 0,
                depth: 200,
                modifier: 1,
                slideShadows: true,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            breakpoints: {
                320: {
                    slidesPerView: 1,
                },
                640: {
                    slidesPerView: 2,
                },
                768: {
                    slidesPerView: 3,
                },
                1024: {
                    slidesPerView: 3,
                },
            }
        });
        
        // Category Filter Buttons
        const categoryBtns = document.querySelectorAll('.category-btn');
        
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                categoryBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Here you would typically filter the news items
                // For demo purposes, we're just toggling the active class
            });
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