<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Hippmi - Himpunan Pendidik & Pengajar Muda Indonesia</title>
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
        --secondary-color: #ffffff; /* Putih Indonesia */
        --accent-color: #ffc107;
        --dark-color: #333333;
        --light-gray: #f8f9fa;
        --transition: all 0.3s ease;
    }
    
    html {
        scroll-behavior: smooth;
        /* Nilai ini akan diperbarui otomatis lewat JavaScript sesuai tinggi navbar. */
        scroll-padding-top: var(--section-scroll-offset, 110px);
    }

    body {
        font-family: 'Poppins', sans-serif;
        overflow-x: hidden;
    }

    /* Memberi ruang di atas setiap section agar judul tidak tertutup navbar fixed. */
    section[id] {
        scroll-margin-top: var(--section-scroll-offset, 110px);
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
    
    /* Hero Section Styling */
    .hero-section {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('img/background%20beranda.webp');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: white;
        padding: 150px 0 100px;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--primary-color) 50%, var(--secondary-color) 50%);
        z-index: 1;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
    }
    
    .logo-container img {
        max-width: 260px;
        width: auto;
        height: auto;
        border: 0;
        border-radius: 16px;
        padding: 10px 14px;
        background: #ffffff;
        object-fit: contain;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.28);
        transition: var(--transition);
    }
    
    .logo-container img:hover {
        transform: scale(1.05);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
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
    
    /* Cards & Elements Styling */
    .card-custom {
        transition: transform 0.4s, box-shadow 0.4s;
        margin-bottom: 20px;
        height: 100%;
        border: 1px solid rgba(227, 10, 23, 0.2);
        border-radius: 12px;
        overflow: hidden;
    }
    
    .card-custom:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(227, 10, 23, 0.15);
    }
    
    .value-icon {
        font-size: 2.5rem;
        margin-bottom: 20px;
        color: var(--primary-color);
        transition: var(--transition);
    }
    
    .card-custom:hover .value-icon {
        transform: scale(1.1);
    }
    
    .org-structure {
        max-width: 100%;
        height: auto;
        margin: 20px 0;
        border: 2px solid var(--primary-color);
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: var(--transition);
    }
    
    .org-structure:hover {
        transform: scale(1.01);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }
    
    /* Accordion Styling */
    .accordion-button:not(.collapsed) {
        background-color: rgba(227, 10, 23, 0.1);
        color: var(--primary-color);
        font-weight: 500;
    }
    
    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(227, 10, 23, 0.25);
    }
    
    .accordion-item {
        margin-bottom: 10px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    /* Section Background Variations */
    .bg-red-section {
        background-color: var(--primary-color);
        color: var(--secondary-color);
        padding: 15px 0;
        position: relative;
        overflow: hidden;
    }
    
    .bg-red-section::before,
    .bg-red-section::after {
        content: '';
        position: absolute;
        width: 40px;
        height: 40px;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .bg-red-section::before {
        top: -20px;
        left: 10%;
    }
    
    .bg-red-section::after {
        bottom: -20px;
        right: 10%;
    }
    
    .bg-white-section {
        background-color: var(--secondary-color);
    }
    
    .bg-light-section {
        background-color: var(--light-gray);
    }
    
    .bg-pattern {
        background-image: url('https://www.transparenttextures.com/patterns/batik.png');
        background-repeat: repeat;
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
        background-color: #b8070f;
        color: var(--secondary-color);
        box-shadow: 0 5px 15px rgba(227, 10, 23, 0.3);
        transform: translateY(-3px);
    }
    
    .btn-outline-nasional {
        background-color: transparent;
        color: var(--primary-color);
        border: 2px solid var(--primary-color);
        border-radius: 30px;
        padding: 10px 25px;
        font-weight: 500;
        transition: var(--transition);
    }
    
    .btn-outline-nasional:hover {
        background-color: var(--primary-color);
        color: var(--secondary-color);
        box-shadow: 0 5px 15px rgba(227, 10, 23, 0.3);
        transform: translateY(-3px);
    }
    
    /* Divider Styling */
    .indonesia-divider {
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color) 50%, var(--secondary-color) 50%);
        margin: 30px 0;
        position: relative;
    }
    
    .indonesia-divider::before {
        content: '';
        position: absolute;
        top: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 30px;
        height: 30px;
        background-color: #fff;
        border: 2px solid var(--primary-color);
        border-radius: 50%;
    }
    
    /* Card Header Styling */
    .card-header-nasional {
        background-color: var(--primary-color);
        color: var(--secondary-color);
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
    }
    
    /* Contact Form Styling */
    .contact-form .form-control {
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid #ced4da;
        transition: var(--transition);
    }
    
    .contact-form .form-control:focus {
        box-shadow: 0 0 0 3px rgba(227, 10, 23, 0.2);
        border-color: var(--primary-color);
    }
    
    .contact-form label {
        font-weight: 500;
        color: #555;
        margin-bottom: 8px;
    }
    
    /* Floating Elements Animation */
    .floating {
        animation: floating 3s ease-in-out infinite;
    }
    
    @keyframes floating {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
        100% { transform: translateY(0px); }
    }
    
    /* Pulse Animation */
    .pulse {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    /* Hover Effects */
    .hover-lift {
        transition: var(--transition);
    }
    
    .hover-lift:hover {
        transform: translateY(-5px);
    }
    
    /* Custom List Items */
    .custom-list-item {
        padding: 10px 0;
        border-bottom: 1px dashed rgba(0,0,0,0.1);
    }
    
    .custom-list-item:last-child {
        border-bottom: none;
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
        background: #b8070f;
        transform: translateY(-5px);
    }
    
    /* Form Input Animations */
    .form-control {
        transition: var(--transition);
    }
    
    .form-floating .form-control:focus ~ label,
    .form-floating .form-control:not(:placeholder-shown) ~ label {
        color: var(--primary-color);
    }
    
    /* Timeline */
    .timeline-container {
        position: relative;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px 0;
    }
    
    .timeline-container::after {
        content: '';
        position: absolute;
        width: 6px;
        background-color: var(--primary-color);
        top: 0;
        bottom: 0;
        left: 50%;
        margin-left: -3px;
        opacity: 0.3;
    }
    
    .timeline-item {
        padding: 10px 40px;
        position: relative;
        width: 50%;
        box-sizing: border-box;
    }
    
    .timeline-item::after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        right: -10px;
        background-color: white;
        border: 4px solid var(--primary-color);
        top: 15px;
        border-radius: 50%;
        z-index: 1;
    }
    
    .timeline-left {
        left: 0;
    }
    
    .timeline-right {
        left: 50%;
    }
    
    .timeline-left::after {
        right: -10px;
    }
    
    .timeline-right::after {
        left: -10px;
    }
    
    .timeline-content {
        padding: 20px;
        background-color: white;
        position: relative;
        border-radius: 6px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    /* Stats counter */
    .counter {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 10px;
    }
    
    .counter-title {
        font-size: 1.1rem;
        color: #666;
    }
    
    /* Image hover effects */
    .image-hover-effect {
        overflow: hidden;
        border-radius: 10px;
    }
    
    .image-hover-effect img {
        transition: transform 0.5s;
        width: 100%;
    }
    
    .image-hover-effect:hover img {
        transform: scale(1.1);
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
    <!-- Lightweight preloader fallback: runs independently so it still hides the preloader
         even if later scripts fail to load or throw errors. -->
    <script>
        (function(){
            // Hide preloader when DOM is ready
            function hidePreloader() {
                try {
                    var el = document.querySelector('.preloader');
                    if (!el) return;
                    el.style.opacity = '0';
                    setTimeout(function(){ el.style.display = 'none'; }, 500);
                } catch (err) {
                    try { document.querySelector('.preloader').style.display = 'none'; } catch(e){}
                }
            }

            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                // DOM already ready
                hidePreloader();
            } else {
                document.addEventListener('DOMContentLoaded', hidePreloader);
                // Also ensure it hides on full load as a fallback
                window.addEventListener('load', hidePreloader);
            }

            // Final safety: force-hide after 6 seconds in case other events fail
            setTimeout(hidePreloader, 6000);
        })();
    </script>
    
    <!-- Back to Top Button -->
    <a href="#" class="back-to-top">
        <i class="fas fa-chevron-up"></i>
    </a>
    
    <?php include 'navbar.php'; ?>

    <section class="hero-section text-center" id="beranda">
        <div class="container hero-content">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="logo-container mb-4" data-aos="zoom-in" data-aos-duration="800">
                        <img src="img/Logo.webp" alt="Logo HIPPMI" class="floating" onerror="this.onerror=null;this.src='https://via.placeholder.com/200x200?text=HIPPMI';">
                    </div>
                    <h1 class="display-4 fw-bold mb-3" data-aos="fade-up" data-aos-delay="200">Himpunan Pendidik & Pengajar Muda Indonesia</h1>
                    <p class="lead mb-4" data-aos="fade-up" data-aos-delay="400">"Mendidik untuk Negeri, Berkarya untuk Bangsa"</p>
                    <div class="d-flex justify-content-center gap-3" data-aos="fade-up" data-aos-delay="600">
                        <a href="#tentang" class="btn btn-nasional btn-lg px-4">Tentang Kami</a>
                        <a href="#kontak" class="btn btn-outline-light btn-lg px-4">Hubungi Kami</a>
                    </div>
                </div>
            </div>
            
            <!-- Floating elements -->
            <div class="position-absolute" style="top: 15%; left: 5%; opacity: 0.2; z-index: 0;">
                <i class="fas fa-graduation-cap fa-4x floating"></i>
            </div>
            <div class="position-absolute" style="bottom: 15%; right: 10%; opacity: 0.2; z-index: 0;">
                <i class="fas fa-book-open fa-4x floating" style="animation-delay: 1s;"></i>
            </div>
            <div class="position-absolute" style="top: 20%; right: 15%; opacity: 0.2; z-index: 0;">
                <i class="fas fa-pencil-alt fa-3x floating" style="animation-delay: 2s;"></i>
            </div>
        </div>
    </section>
    
    <!-- Banner Nasionalis -->
    <div class="bg-red-section py-3">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h5 class="mb-0" data-aos="fade-up">"Membangun Pendidikan, Memperkuat Negeri"</h5>
                </div>
            </div>
        </div>
    </div>

        <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6 text-center mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="counter" data-target="500">0</div>
                    <div class="counter-title">Anggota Aktif</div>
                </div>
                <div class="col-md-3 col-6 text-center mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="counter" data-target="34">0</div>
                    <div class="counter-title">Provinsi</div>
                </div>
                <div class="col-md-3 col-6 text-center mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="counter" data-target="150">0</div>
                    <div class="counter-title">Program Terselesaikan</div>
                </div>
                <div class="col-md-3 col-6 text-center mb-4" data-aos="fade-up" data-aos-delay="400">
                    <div class="counter" data-target="10000">0</div>
                    <div class="counter-title">Pelajar Terdampak</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tentang Kami - Dipercantik -->
    <style>
        #tentang { background: linear-gradient(180deg, #fff 0%, #fdf6f6 100%); position: relative; }
        #tentang::before { content:''; position:absolute; inset:0; background: radial-gradient(700px 400px at 15% 0%, rgba(227,10,23,0.05), transparent 70%), radial-gradient(600px 320px at 90% 20%, rgba(255,193,7,0.04), transparent 70%); pointer-events:none; }
        #tentang .container { position: relative; z-index: 1; }
        .tentang-badge { display:inline-flex; align-items:center; gap:10px; padding:12px 24px; background:#fff; border:1px solid #ffe2e2; border-radius:999px; color:var(--primary-color); font-size:1.42rem; font-weight:800; letter-spacing:-.02em; box-shadow:0 4px 12px rgba(227,10,23,0.06); line-height:1; }
        @media(max-width:576px){ .tentang-badge{font-size:1.15rem;padding:10px 18px} }
        .tentang-image-wrap { position:relative; border-radius:20px; overflow:hidden; box-shadow:0 16px 40px rgba(27,28,32,0.10); border:1px solid #eef1f6; }
        .tentang-image-wrap img { display:block; width:100%; height:auto; transition: transform .6s ease; }
        .tentang-image-wrap:hover img { transform: scale(1.03); }
        .tentang-image-badge { position:absolute; bottom:14px; left:14px; background:rgba(255,255,255,0.96); backdrop-filter: blur(6px); padding:10px 14px; border-radius:12px; display:flex; align-items:center; gap:10px; box-shadow:0 8px 20px rgba(0,0,0,0.12); border:1px solid #eef1f6; }
        .tentang-goals { list-style:none; padding:0; margin:14px 0 0; }
        .tentang-goals li { display:flex; gap:12px; align-items:flex-start; background:#fff; border:1px solid #eef1f6; border-radius:14px; padding:13px 14px; margin-bottom:10px; box-shadow:0 6px 16px rgba(0,0,0,0.04); transition: transform .25s ease, box-shadow .25s ease; text-align:left; }
        .tentang-goals li:hover { transform: translateY(-3px); box-shadow:0 12px 28px rgba(227,10,23,0.08); border-color: rgba(227,10,23,0.10); }
        .tg-icon { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; background: linear-gradient(135deg, rgba(227,10,23,0.10), rgba(227,10,23,0.04)); color: var(--primary-color); font-size:13px; flex-shrink:0; }
        .tentang-goals li span { font-size:.90rem; line-height:1.6; color:#2b2e36; }
        .tentang-side-card { border:none; border-radius:20px; overflow:hidden; box-shadow:0 14px 40px rgba(27,28,32,0.08); border:1px solid #eef1f6; background:#fff; }
        .tentang-side-card .side-head { background: linear-gradient(135deg, var(--primary-color), #b8070f); color:#fff; padding:24px; position:relative; overflow:hidden; }
        .tentang-side-card .side-head::after { content:''; position:absolute; top:-40px; right:-40px; width:120px; height:120px; background:rgba(255,255,255,0.08); border-radius:50%; }
        .tentang-side-card .side-body { padding:22px; }
        .sasaran-pill { display:inline-flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
        .sasaran-pill span { background:#f8f9ff; border:1px solid #eef1f6; padding:7px 12px; border-radius:999px; font-size:.82rem; font-weight:600; color:#333; }
        .tentang-accordion .accordion-item { border:1px solid #eef1f6; border-radius:12px; overflow:hidden; margin-bottom:10px; box-shadow:0 4px 12px rgba(0,0,0,0.03); }
        .tentang-accordion .accordion-button { font-weight:600; font-size:.92rem; padding:13px 16px; }
        .tentang-accordion .accordion-button:not(.collapsed) { background: rgba(227,10,23,0.06); color: var(--primary-color); box-shadow:none; }
        .tentang-accordion .accordion-button:focus { box-shadow:none; border-color: transparent; }
        .tentang-accordion .accordion-body { background:#fff; padding:14px 16px; }
        .tentang-accordion .accordion-body li { font-size:.88rem; margin-bottom:6px; display:flex; gap:8px; align-items:flex-start; }
        @media (max-width: 768px) { .tentang-image-badge { position: static; margin-top:12px; } .tentang-goals li { padding:12px; } }
    </style>
    <section class="py-5" id="tentang">
        <div class="container">
            <div class="row mb-3">
                <div class="col-md-12 text-center" data-aos="fade-right">
                    <div class="tentang-badge mb-2"><i class="fa-solid fa-landmark"></i> Tentang HIPPMI • Sejak 2023</div>
                    <p class="text-muted mx-auto mb-0" style="max-width:620px; font-size:.94rem;">Jejaring pendidik muda lintas profesi &amp; daerah — ruang belajar, advokasi, dan kolaborasi untuk pendidikan Indonesia.</p>
                </div>
            </div>
            <div class="row g-4 align-items-start">
                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="150">
                    <div class="tentang-image-wrap">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1471&q=80" alt="Tentang HIPPMI">
                        <div class="tentang-image-badge" data-aos="zoom-in" data-aos-delay="400">
                            <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#e30a17,#ff6b6b);display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;"><i class="fa-solid fa-users"></i></div>
                            <div style="line-height:1.2;"><div style="font-weight:700;font-size:.92rem;color:#1b1c20;">Jejaring Nasional</div><div style="font-size:.78rem;color:#6b7280;">Guru • Dosen • Tutor • Ustadz/ah</div></div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h3 style="font-size:1.25rem;font-weight:700;color:#1b1c20;margin-bottom:6px;">Tujuan Pembentukan HIPPMI</h3>
                        <p class="text-muted mb-3" style="font-size:.88rem;">Lima pilar tujuan yang menggerakkan kolaborasi pendidik muda Indonesia.</p>
                        <ul class="tentang-goals">
                            <li data-aos="fade-up" data-aos-delay="100"><div class="tg-icon"><i class="fa-solid fa-network-wired"></i></div><span><strong>Membangun jejaring</strong> antar pendidik muda lintas profesi dan daerah</span></li>
                            <li data-aos="fade-up" data-aos-delay="150"><div class="tg-icon"><i class="fa-solid fa-graduation-cap"></i></div><span><strong>Ruang edukasi berkelanjutan</strong> untuk pengembangan kompetensi anggota</span></li>
                            <li data-aos="fade-up" data-aos-delay="200"><div class="tg-icon"><i class="fa-solid fa-bullhorn"></i></div><span><strong>Wadah advokasi & aspirasi</strong> pendidik muda terhadap kebijakan pendidikan</span></li>
                            <li data-aos="fade-up" data-aos-delay="250"><div class="tg-icon"><i class="fa-solid fa-lightbulb"></i></div><span><strong>Mendorong kolaborasi & inovasi</strong> dalam dunia pendidikan</span></li>
                            <li data-aos="fade-up" data-aos-delay="300"><div class="tg-icon"><i class="fa-solid fa-comments"></i></div><span><strong>Layanan konsultasi & berbagi</strong> pengalaman antarpendidik</span></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="200">
                    <div class="tentang-side-card">
                        <div class="side-head">
                            <div style="display:flex;align-items:center;gap:12px;position:relative;z-index:1;">
                                <div style="width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,0.16);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fa-solid fa-people-group"></i></div>
                                <div><div style="font-weight:700;font-size:1.05rem;">Sasaran Anggota</div><div style="font-size:.82rem;opacity:.92;">Siapa yang bisa bergabung</div></div>
                            </div>
                        </div>
                        <div class="side-body">
                            <div class="sasaran-pill">
                                <span><i class="fa-solid fa-chalkboard-teacher me-1 text-danger"></i> Guru</span>
                                <span><i class="fa-solid fa-user-graduate me-1 text-danger"></i> Dosen</span>
                                <span><i class="fa-solid fa-person-chalkboard me-1 text-danger"></i> Trainer</span>
                                <span><i class="fa-solid fa-book-open-reader me-1 text-danger"></i> Tutor</span>
                                <span><i class="fa-solid fa-mosque me-1 text-danger"></i> Ustadz/ah</span>
                                <span><i class="fa-solid fa-users me-1 text-danger"></i> Praktisi Pendidikan</span>
                            </div>
                            <p class="text-muted mt-3 mb-0" style="font-size:.88rem;">Terbuka untuk seluruh praktisi pendidikan di Indonesia yang ingin bertumbuh bersama.</p>
                            <h4 style="font-size:1.05rem;font-weight:700;color:var(--primary-color);margin:22px 0 14px;">Ruang Lingkup Kegiatan</h4>
                            <div class="accordion tentang-accordion" id="accordionKegiatan">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEdukasi"><i class="fa-solid fa-book-open me-2"></i> Edukasi</button>
                                    </h2>
                                    <div id="collapseEdukasi" class="accordion-collapse collapse show" data-bs-parent="#accordionKegiatan">
                                        <div class="accordion-body">
                                            <ul class="list-unstyled mb-0">
                                                <li><i class="fas fa-arrow-right text-danger me-2 mt-1"></i><span>Workshop, seminar, pelatihan daring/luring</span></li>
                                                <li><i class="fas fa-arrow-right text-danger me-2 mt-1"></i><span>Kelas berbagi ilmu dan praktik terbaik pendidikan</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvokasi"><i class="fa-solid fa-scale-balanced me-2"></i> Advokasi</button>
                                    </h2>
                                    <div id="collapseAdvokasi" class="accordion-collapse collapse" data-bs-parent="#accordionKegiatan">
                                        <div class="accordion-body">
                                            <ul class="list-unstyled mb-0">
                                                <li><i class="fas fa-arrow-right text-danger me-2 mt-1"></i><span>Menyuarakan aspirasi pendidik muda</span></li>
                                                <li><i class="fas fa-arrow-right text-danger me-2 mt-1"></i><span>Menyusun rekomendasi kebijakan pendidikan kepada pihak terkait</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKonsultansi"><i class="fa-solid fa-comments me-2"></i> Konsultansi</button>
                                    </h2>
                                    <div id="collapseKonsultansi" class="accordion-collapse collapse" data-bs-parent="#accordionKegiatan">
                                        <div class="accordion-body">
                                            <ul class="list-unstyled mb-0">
                                                <li><i class="fas fa-arrow-right text-danger me-2 mt-1"></i><span>Konsultasi pengembangan profesi, kurikulum, dan karier pendidik</span></li>
                                                <li><i class="fas fa-arrow-right text-danger me-2 mt-1"></i><span>Pendampingan dan mentoring profesional</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSharing"><i class="fa-solid fa-share-nodes me-2"></i> Sharing antar anggota</button>
                                    </h2>
                                    <div id="collapseSharing" class="accordion-collapse collapse" data-bs-parent="#accordionKegiatan">
                                        <div class="accordion-body">
                                            <ul class="list-unstyled mb-0">
                                                <li><i class="fas fa-arrow-right text-danger me-2 mt-1"></i><span>Forum diskusi dan kolaborasi</span></li>
                                                <li><i class="fas fa-arrow-right text-danger me-2 mt-1"></i><span>Grup komunitas daring dan luring</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLain"><i class="fa-solid fa-ellipsis me-2"></i> Lain-lain</button>
                                    </h2>
                                    <div id="collapseLain" class="accordion-collapse collapse" data-bs-parent="#accordionKegiatan">
                                        <div class="accordion-body">
                                            <ul class="list-unstyled mb-0">
                                                <li><i class="fas fa-arrow-right text-danger me-2 mt-1"></i><span>Kegiatan sosial dan pengabdian kepada masyarakat</span></li>
                                                <li><i class="fas fa-arrow-right text-danger me-2 mt-1"></i><span>Pengembangan program literasi dan inklusi digital</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="indonesia-divider"></div>

    <!-- Visi & Misi (modern cards) -->
    <section class="py-5 bg-light" id="visimisi">
        <div class="container">
            <div class="row mb-3">
                <div class="col-md-12 text-center" data-aos="fade-right">
                    <div class="vm-badge"><i class="fa-solid fa-bullseye"></i> Visi &amp; Misi HIPPMI</div>
                    <p class="text-muted mx-auto mb-0" style="max-width:640px;font-size:.92rem;">Cita dan langkah nyata untuk pendidikan Indonesia yang inklusif dan berdaya saing global.</p>
                </div>
            </div>

            <style>
                #visimisi { background: linear-gradient(180deg, #fff 0%, #fef6f6 100%); position:relative; overflow:hidden; }
                #visimisi::before { content:''; position:absolute; width:520px; height:520px; border-radius:50%; background: radial-gradient(circle, rgba(227,10,23,0.07), transparent 70%); top:-120px; right:-120px; pointer-events:none; }
                #visimisi::after { content:''; position:absolute; width:420px; height:420px; border-radius:50%; background: radial-gradient(circle, rgba(255,193,7,0.06), transparent 70%); bottom:-100px; left:-80px; pointer-events:none; }
                #visimisi .container { position:relative; z-index:1; }
                .vm-badge { display:inline-flex; align-items:center; gap:10px; padding:12px 24px; border-radius:999px; background:#fff; border:1px solid #ffe2e2; color:var(--primary-color); font-size:1.42rem; font-weight:800; letter-spacing:-.02em; text-transform:none; box-shadow:0 4px 14px rgba(227,10,23,0.06); margin-bottom:12px; }
                @media(max-width:576px){ .vm-badge{font-size:1.15rem;padding:10px 18px} }
                .vm-card { border:1px solid #f0e6e6; border-radius:20px; overflow:hidden; transition: all .35s ease; box-shadow:0 10px 30px rgba(27,28,32,0.06); background:#fff; position:relative; }
                .vm-card::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background: linear-gradient(90deg, var(--primary-color), #ff7a7a); opacity:.95; z-index:2; }
                .vm-card:hover { transform: translateY(-8px); box-shadow:0 20px 44px rgba(27,28,32,0.12); border-color: rgba(227,10,23,0.12); }
                .vm-card .vm-header { background: linear-gradient(135deg, var(--primary-color) 0%, #b8070f 100%); color:#fff; padding:24px 24px 22px; text-align:center; position:relative; overflow:hidden; }
                .vm-card .vm-header::after { content:''; position:absolute; width:140px; height:140px; background: rgba(255,255,255,0.08); border-radius:50%; top:-40px; right:-30px; }
                .vm-icon { width:48px; height:48px; border-radius:12px; background: rgba(255,255,255,0.16); display:inline-flex; align-items:center; justify-content:center; font-size:20px; margin-bottom:12px; backdrop-filter: blur(4px); border:1px solid rgba(255,255,255,0.18); position:relative; z-index:1; }
                .vm-card .vm-title { margin:0; font-weight:700; font-size:1.15rem; letter-spacing:-0.01em; position:relative; z-index:1; }
                .vm-card .vm-sub { font-size:.95rem; line-height:1.7; color:#2b2e36; margin:0; position:relative; }
                .vm-quote { font-size:42px; line-height:1; color: rgba(227,10,23,0.09); margin-bottom:8px; }
                .vm-body-visi { padding:28px 26px 30px; text-align:center; }
                .vm-body-misi { padding:22px 20px 18px; }
                .vm-misi-list { list-style:none; counter-reset: misi; padding:0; margin:0; }
                .vm-misi-list li { counter-increment: misi; display:flex; gap:14px; align-items:flex-start; padding:14px 14px; border-radius:12px; margin-bottom:10px; background:#fff; border:1px solid #f2e9e9; transition: all .25s ease; line-height:1.6; font-size:.90rem; color:#2d3038; }
                .vm-misi-list li:hover { background:#fdf6f6; border-color: rgba(227,10,23,0.12); transform: translateX(4px); }
                .vm-misi-list li::before { content: counter(misi); min-width:32px; width:32px; height:32px; border-radius:50%; background: linear-gradient(135deg, var(--primary-color), #ff4d5a); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.82rem; flex-shrink:0; margin-top:1px; box-shadow:0 4px 12px rgba(227,10,23,0.22); }
                @media (max-width: 768px) { .vm-card { border-radius:16px; } .vm-body-visi { padding:22px 18px; } .vm-misi-list li { padding:12px 12px; font-size:.86rem; } }
            </style>

            <div class="row g-4 align-items-stretch">
                <div class="col-lg-5" data-aos="fade-up" data-aos-delay="100">
                    <div class="card vm-card h-100">
                        <div class="vm-header text-center">
                            <div class="vm-icon"><i class="fa-solid fa-eye"></i></div>
                            <h3 class="vm-title">Visi HIPPMI</h3>
                        </div>
                        <div class="vm-body-visi">
                            <div class="vm-quote"><i class="fa-solid fa-quote-left"></i></div>
                            <p class="vm-sub">Menjadi himpunan pendidik dan pengajar muda yang <strong>unggul, progresif, dan kolaboratif</strong> dalam membangun pendidikan Indonesia yang inklusif, berkualitas, dan berdaya saing global.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7" data-aos="fade-up" data-aos-delay="200">
                    <div class="card vm-card h-100">
                        <div class="vm-header text-center">
                            <div class="vm-icon"><i class="fa-solid fa-list-check"></i></div>
                            <h3 class="vm-title">Misi HIPPMI</h3>
                        </div>
                        <div class="vm-body-misi">
                            <ol class="vm-misi-list">
                                <li>Membangun jejaring profesional antar pendidik dan pengajar muda dari berbagai latar belakang untuk saling berbagi ilmu, pengalaman, dan inovasi pendidikan.</li>
                                <li>Meningkatkan kapasitas dan kompetensi pendidik muda melalui kegiatan edukatif seperti pelatihan, seminar, webinar, dan program pengembangan diri.</li>
                                <li>Mendorong peran aktif pendidik muda dalam advokasi kebijakan pendidikan yang berpihak pada kualitas, keadilan, dan inklusivitas.</li>
                                <li>Menyediakan layanan konsultasi dan pendampingan bagi anggota dalam pengembangan karier, kurikulum, maupun tantangan di lapangan.</li>
                                <li>Mewadahi ide dan aksi sosial-keilmuan dari para anggota yang berdampak nyata bagi masyarakat dan dunia pendidikan.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Banner Nasionalis 2 -->
    <div class="bg-red-section py-3">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h5 class="mb-0 pulse">"Dari Pendidik Untuk Indonesia"</h5>
                </div>
            </div>
        </div>
    </div>

    <?php
    require_once __DIR__ . '/koneksi.php';
    try { $pdoCV = getDBConnection(); $cvList = $pdoCV->query("SELECT * FROM `core_values` WHERE `status`='published' ORDER BY `urutan` ASC, `id` ASC")->fetchAll(); } catch(Throwable $e){ $cvList=[]; }
    if(empty($cvList)){
        $cvList=[
            ['judul'=>'Kolaborasi','deskripsi'=>'Kami percaya bahwa kekuatan pendidikan tumbuh dari kerja sama, sinergi, dan dukungan antarpendidik.','icon'=>'fa-handshake'],
            ['judul'=>'Inovasi','deskripsi'=>'Kami mendorong pembaruan ide dan metode pembelajaran yang kreatif, relevan, dan adaptif terhadap zaman.','icon'=>'fa-lightbulb'],
            ['judul'=>'Kompetensi','deskripsi'=>'Kami berkomitmen pada peningkatan kapasitas diri agar menjadi pendidik yang profesional, unggul, dan berdampak.','icon'=>'fa-award'],
            ['judul'=>'Advokasi','deskripsi'=>'Kami berpihak pada nilai-nilai keadilan, kesetaraan akses pendidikan, dan keberpihakan terhadap peserta didik.','icon'=>'fa-scale-balanced'],
            ['judul'=>'Integritas','deskripsi'=>'Kami menjunjung tinggi etika profesi, kejujuran, dan tanggung jawab dalam setiap langkah dan kontribusi.','icon'=>'fa-shield-halved'],
            ['judul'=>'Kebermanfaatan','deskripsi'=>'Kami hadir untuk memberikan nilai dan kontribusi nyata bagi pendidikan, masyarakat, dan bangsa.','icon'=>'fa-heart'],
        ];
    }
    ?>
    <section class="py-5 bg-white-section bg-pattern" id="core-values">
        <div class="container">
            <div class="row mb-3">
                <div class="col-md-12 text-center" data-aos="fade-right">
                    <span style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;background:#fff;border:1px solid #ffe2e2;border-radius:999px;color:#e30a17;font-size:1.42rem;font-weight:800;letter-spacing:-.02em;box-shadow:0 4px 12px rgba(227,10,23,.06);"><i class="fa-solid fa-star"></i> Core Values HIPPMI</span>
                </div>
            </div>
            <style>
                #core-values{position:relative;background:linear-gradient(180deg,#fff 0%,#fef6f6 100%);}
                #core-values::before{content:'';position:absolute;width:520px;height:520px;border-radius:50%;background:radial-gradient(circle,rgba(227,10,23,.06),transparent 72%);top:-120px;right:-100px;pointer-events:none;}
                #core-values .container{position:relative;z-index:1;}
                .cv-card{position:relative;display:flex;gap:16px;align-items:flex-start;padding:20px 18px;background:#fff;border:1px solid #f0e6e6;border-radius:16px;box-shadow:0 8px 20px rgba(27,28,32,.05);transition:transform .32s ease,box-shadow .32s ease,border-color .32s ease;overflow:hidden;}
                .cv-card::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,#e30a17,#ff7a7a);opacity:.9;}
                .cv-card:hover{transform:translateY(-6px);box-shadow:0 16px 36px rgba(27,28,32,.11);border-color:rgba(227,10,23,.12);}
                .cv-icon{width:54px;height:54px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(227,10,23,.10),rgba(227,10,23,.04));color:#e30a17;font-size:22px;flex-shrink:0;border:1px solid rgba(227,10,23,.08);}
                .cv-content h4{margin:0 0 6px;font-size:1.02rem;font-weight:700;color:#1b1c20;}
                .cv-content p{margin:0;color:#5b5e6a;font-size:.88rem;line-height:1.6;}
                .cv-num{margin-left:auto;font-size:.78rem;font-weight:700;color:#e30a17;background:#fff0f1;border:1px solid #ffe2e2;padding:4px 8px;border-radius:999px;flex-shrink:0;align-self:flex-start;}
                @media(max-width:768px){.cv-card{padding:16px 14px;gap:12px;}.cv-icon{width:46px;height:46px;font-size:18px;}}
            </style>
            <div class="row g-4">
                <?php foreach($cvList as $idx=>$cv): $delay=80+($idx%6)*60; $iconCV=!empty($cv['icon'])?$cv['icon']:'fa-star'; $judulCV=htmlspecialchars($cv['judul']); $descCV=htmlspecialchars($cv['deskripsi']); $num=sprintf('%02d',$idx+1); ?>
                <div class="col-md-6" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                    <div class="cv-card">
                        <div class="cv-icon"><i class="fa-solid <?= htmlspecialchars($iconCV) ?>"></i></div>
                        <div class="cv-content"><h4><?= $judulCV ?></h4><p><?= nl2br($descCV) ?></p></div>
                        <span class="cv-num"><?= $num ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div class="indonesia-divider"></div>

    <?php
    require_once __DIR__ . '/koneksi.php';
    try { $pdoStruktur = getDBConnection(); $strukturList = $pdoStruktur->query("SELECT * FROM `struktur_organisasi` WHERE `status`='published' ORDER BY `urutan` ASC, `id` ASC")->fetchAll(); } catch(Throwable $e){ $strukturList=[]; }
    if(empty($strukturList)){
        $strukturList=[
            ['nama_jabatan'=>'Ketua Umum','deskripsi'=>'Pemimpin organisasi yang mengoordinasikan keseluruhan kegiatan dan kebijakan HIPPMI. Bertanggung jawab atas arah visi, misi, dan pencapaian tujuan organisasi secara menyeluruh.','icon'=>'fa-crown','foto'=>''],
            ['nama_jabatan'=>'Dewan Penasihat','deskripsi'=>'Memberikan arahan strategis, nasihat, dan pertimbangan penting bagi jalannya organisasi. Berperan sebagai penyeimbang dan pengarah dalam pengambilan keputusan besar serta menjaga nilai-nilai dasar organisasi.','icon'=>'fa-scale-balanced','foto'=>''],
            ['nama_jabatan'=>'Dewan Pakar','deskripsi'=>'Kelompok profesional, akademisi, atau praktisi ahli yang memberikan kontribusi keilmuan, pemikiran kritis, dan masukan teknis serta terlibat dalam perumusan rekomendasi strategis yang berdampak pada kebijakan Pendidikan.','icon'=>'fa-user-graduate','foto'=>''],
            ['nama_jabatan'=>'Sekretaris Jenderal','deskripsi'=>'Mengelola administrasi, surat-menyurat, dan koordinasi internal organisasi. Berfungsi sebagai penghubung antara Ketua Umum dengan seluruh bidang dan anggota.','icon'=>'fa-file-pen','foto'=>''],
            ['nama_jabatan'=>'Bendahara Umum','deskripsi'=>'Mengelola keuangan organisasi secara transparan dan akuntabel, termasuk pencatatan, pelaporan, dan pengelolaan anggaran untuk mendukung semua kegiatan HIPPMI.','icon'=>'fa-coins','foto'=>''],
            ['nama_jabatan'=>'Bidang Edukasi & Konsultansi','deskripsi'=>'Menginisiasi dan melaksanakan program peningkatan pengetahuan dan keterampilan anggota, serta memberikan layanan konsultansi dan pendampingan bagi anggota dalam hal pengembangan karier, pengelolaan lembaga pendidikan, dan peningkatan kualitas pengajaran.','icon'=>'fa-person-chalkboard','foto'=>''],
            ['nama_jabatan'=>'Bidang Riset & Advokasi','deskripsi'=>'Melaksanakan riset dan kajian kebijakan pendidikan yang relevan, menyusun rekomendasi, serta menyampaikan hasil kajian serta memberikan layanan advokasi kepada pendidik yang menghadapi permasalahan hukum atau ketidakadilan dalam menjalankan profesinya.','icon'=>'fa-magnifying-glass-chart','foto'=>''],
            ['nama_jabatan'=>'Bidang Sharing & Kolaborasi','deskripsi'=>'Memfasilitasi ruang berbagi ide, pengalaman, dan best practices antar anggota HIPPMI, serta menjalin kerja sama dengan berbagai lembaga, komunitas, dan institusi pendidikan.','icon'=>'fa-handshake','foto'=>''],
        ];
    }
    ?>
    <section class="py-5 bg-light" id="struktur">
        <div class="container">
            <div class="row mb-3">
                <div class="col-md-12 text-center" data-aos="fade-right">
                    <span style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;background:#fff;border:1px solid #ffe2e2;border-radius:999px;color:#e30a17;font-size:1.42rem;font-weight:800;letter-spacing:-.02em;box-shadow:0 4px 12px rgba(227,10,23,0.06);"><i class="fa-solid fa-sitemap"></i> Struktur Organisasi HIPPMI</span>
                </div>
            </div>
            <div class="row justify-content-center mb-4">
                <div class="col-md-10 text-center" data-aos="zoom-in">
                    <img src="https://via.placeholder.com/800x400?text=Struktur+Organisasi+HIPPMI" class="org-structure shadow" alt="Struktur Organisasi HIPPMI">
                </div>
            </div>
            <style>
                .struktur-card{border:0;border-radius:16px;overflow:hidden;transition:transform .35s ease,box-shadow .35s ease;box-shadow:0 8px 18px rgba(0,0,0,0.06);background:#fff;position:relative;padding:0;border:1px solid #f0e6e6;}
                .struktur-card .struktur-top{height:5px;background:linear-gradient(90deg,#e30a17,#ff7a7a);}
                .struktur-card .struktur-body{padding:22px 20px;}
                .struktur-card h4{color:var(--primary-color);margin-bottom:.5rem;font-size:1.06rem;font-weight:700;}
                .struktur-card p{color:#444;margin-bottom:0;font-size:.90rem;line-height:1.65;}
                .struktur-card:hover{transform:translateY(-8px);box-shadow:0 18px 40px rgba(0,0,0,0.11);}
                .struktur-icon{width:42px;height:42px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg,rgba(227,10,23,0.10),rgba(227,10,23,0.04));color:#e30a17;font-size:18px;margin-bottom:12px;border:1px solid rgba(227,10,23,0.08);}
                .struktur-foto{width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,0.10);margin-bottom:10px;}
            </style>
            <div class="row">          
                <?php foreach($strukturList as $idx=>$s): $delay=100+($idx%6)*100; $anim=$idx%2===0?'fade-right':'fade-left'; $foto=!empty($s['foto'])?$s['foto']:''; $icon=!empty($s['icon'])?$s['icon']:'fa-sitemap'; $nama=htmlspecialchars($s['nama_jabatan']); $desc=htmlspecialchars($s['deskripsi']); ?>
                <div class="col-md-6 mb-4" data-aos="<?= $anim ?>" data-aos-delay="<?= $delay ?>">
                    <div class="card struktur-card h-100">
                        <div class="struktur-top"></div>
                        <div class="struktur-body">
                            <?php if($foto): ?><img src="<?= htmlspecialchars($foto) ?>" alt="<?= $nama ?>" class="struktur-foto"><?php else: ?><div class="struktur-icon"><i class="fa-solid <?= htmlspecialchars($icon) ?>"></i></div><?php endif; ?>
                            <h4 class="card-title"><?= $nama ?></h4>
                            <p><?= nl2br($desc) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Banner Nasionalis 3 -->
    <div class="bg-red-section py-3">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h5 class="mb-0 pulse">"Bersatu dalam Keberagaman, Unggul dalam Pendidikan"</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Program Kerja -->
    <section class="py-5 bg-white-section bg-pattern" id="program">
        <div class="container">
            <div class="row mb-3">
                <div class="col-md-12 text-center" data-aos="fade-right">
                    <div class="pk-badge-top" style="font-size:1.42rem;font-weight:800;letter-spacing:-.02em;padding:12px 24px;"><i class="fa-solid fa-briefcase"></i> Program Kerja HIPPMI</div>
                    <p class="text-muted mx-auto mb-0" style="max-width:640px;font-size:.92rem;">Rencana strategis bertahap — dari pengenalan hingga kontribusi nasional.</p>
                </div>
            </div>
            <style>
                #program{position:relative;background:linear-gradient(180deg,#fff 0%,#fdf6f6 100%);overflow:hidden;}
                #program::before{content:'';position:absolute;width:560px;height:560px;border-radius:50%;background:radial-gradient(circle,rgba(227,10,23,.06),transparent 72%);top:-160px;right:-160px;pointer-events:none;}
                #program .container{position:relative;z-index:1;}
                .pk-badge-top{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;background:#fff;border:1px solid #ffe2e2;border-radius:999px;color:#e30a17;font-size:.76rem;font-weight:700;letter-spacing:.04em;box-shadow:0 4px 12px rgba(227,10,23,.06);margin-bottom:12px;}
                .pk-card{border:1px solid #f0e6e6;border-radius:20px;overflow:hidden;background:#fff;box-shadow:0 10px 28px rgba(27,28,32,.06);transition:transform .35s ease,box-shadow .35s ease;height:100%;display:flex;flex-direction:column;position:relative;}
                .pk-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#e30a17,#ff7a7a);opacity:.95;}
                .pk-card:hover{transform:translateY(-8px);box-shadow:0 18px 44px rgba(27,28,32,.12);border-color:rgba(227,10,23,.12);}
                .pk-head{padding:22px 22px 16px;display:flex;gap:14px;align-items:center;background:linear-gradient(135deg,#e30a17 0%,#b8070f 100%);color:#fff;position:relative;overflow:hidden;}
                .pk-head::after{content:'';position:absolute;width:120px;height:120px;background:rgba(255,255,255,.08);border-radius:50%;top:-38px;right:-32px;}
                .pk-icon{width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;position:relative;z-index:1;}
                .pk-head-meta{position:relative;z-index:1;line-height:1.25;}
                .pk-head-meta strong{font-size:1rem;display:block;}
                .pk-head-meta span{font-size:.82rem;opacity:.92;display:block;margin-top:2px;}
                .pk-pill{margin-left:auto;position:relative;z-index:1;background:rgba(255,255,255,.18);border:1px solid rgba(255,255,255,.22);padding:6px 12px;border-radius:999px;font-size:.78rem;font-weight:700;white-space:nowrap;backdrop-filter:blur(4px);}
                .pk-body{padding:20px 18px 18px;flex:1;display:flex;flex-direction:column;}
                .pk-focus{font-size:.92rem;font-weight:700;color:#1b1c20;line-height:1.55;margin:0 0 14px;background:#fdf6f6;border:1px solid #ffe2e2;border-radius:12px;padding:12px 14px;}
                .pk-list{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;}
                .pk-list li{display:flex;gap:10px;align-items:flex-start;background:#fff;border:1px solid #f2e9e9;border-radius:12px;padding:10px 12px;font-size:.86rem;line-height:1.55;color:#2d3038;transition:all .22s ease;}
                .pk-list li:hover{background:#fdf6f6;border-color:rgba(227,10,23,.12);transform:translateX(3px);}
                .pk-letter{min-width:26px;width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,#e30a17,#ff4d5a);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.72rem;flex-shrink:0;margin-top:1px;box-shadow:0 3px 10px rgba(227,10,23,.18);}
                @media(max-width:768px){.pk-head{padding:18px 16px 14px;}.pk-body{padding:16px 14px 14px;}.pk-focus{font-size:.86rem;padding:10px 12px;}.pk-list li{font-size:.82rem;padding:9px 10px;}}
            </style>
            <div class="row g-4 align-items-stretch">
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="80">
                    <div class="pk-card">
                        <div class="pk-head"><div class="pk-icon"><i class="fa-solid fa-bolt"></i></div><div class="pk-head-meta"><strong>Jangka Pendek</strong><span>Pengenalan & konsolidasi</span></div><span class="pk-pill">0 – 1 Tahun</span></div>
                        <div class="pk-body">
                            <p class="pk-focus">Fokus: Pengenalan, konsolidasi internal, dan penguatan identitas organisasi</p>
                            <ul class="pk-list">
                                <li><span class="pk-letter">a</span><span>Sosialisasi HIPPMI secara daring/luring.</span></li>
                                <li><span class="pk-letter">b</span><span>Webinar Inspiratif dan Edukatif.</span></li>
                                <li><span class="pk-letter">c</span><span>Recruitment &amp; Orientasi Keanggotaan tahap awal.</span></li>
                                <li><span class="pk-letter">d</span><span>Pembentukan Tim Kerja dan Divisi di pusat dan wilayah (jika ada).</span></li>
                                <li><span class="pk-letter">e</span><span>Merumuskan AD/ART organisasi.</span></li>
                                <li><span class="pk-letter">f</span><span>Pembuatan Media Digital HIPPMI (Website, Instagram, Facebook, dll).</span></li>
                                <li><span class="pk-letter">g</span><span>Penulisan Kajian Singkat Isu Pendidikan Terkini.</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="160">
                    <div class="pk-card">
                        <div class="pk-head"><div class="pk-icon"><i class="fa-solid fa-network-wired"></i></div><div class="pk-head-meta"><strong>Jangka Menengah</strong><span>Kapasitas &amp; ekspansi jaringan</span></div><span class="pk-pill">1 – 3 Tahun</span></div>
                        <div class="pk-body">
                            <p class="pk-focus">Fokus: Penguatan kapasitas anggota, ekspansi jaringan, dan kontribusi kebijakan</p>
                            <ul class="pk-list">
                                <li><span class="pk-letter">a</span><span>Pelatihan Pendidik Muda HIPPMI: pelatihan berjenjang bagi guru, dosen, tutor, ustaz muda.</span></li>
                                <li><span class="pk-letter">b</span><span>Advokasi Kebijakan Publik: audiensi dan penyampaian rekomendasi kepada pemerintah pusat/daerah.</span></li>
                                <li><span class="pk-letter">c</span><span>Program Mentoring &amp; Magang Edukatif bekerja sama dengan lembaga pendidikan.</span></li>
                                <li><span class="pk-letter">d</span><span>HIPPMI Peduli Pendidikan: pengabdian masyarakat dan pengajaran sukarela.</span></li>
                                <li><span class="pk-letter">e</span><span>Pendirian status berbadan hukum HIPPMI.</span></li>
                                <li><span class="pk-letter">f</span><span>Kongres HIPPMI Pertama: penyusunan AD/ART final, pemilihan kepengurusan nasional.</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12" data-aos="fade-up" data-aos-delay="240">
                    <div class="pk-card">
                        <div class="pk-head"><div class="pk-icon"><i class="fa-solid fa-rocket"></i></div><div class="pk-head-meta"><strong>Jangka Panjang</strong><span>Kontribusi strategis nasional</span></div><span class="pk-pill">3+ Tahun</span></div>
                        <div class="pk-body">
                            <p class="pk-focus">Fokus: Kontribusi strategis dalam sistem pendidikan nasional</p>
                            <ul class="pk-list">
                                <li><span class="pk-letter">a</span><span>Sekolah Pemimpin Pendidik Indonesia (SPPI): pengembangan calon pemimpin pendidikan masa depan.</span></li>
                                <li><span class="pk-letter">b</span><span>Konferensi Nasional Pendidik Muda: mempertemukan pendidik dari berbagai daerah.</span></li>
                                <li><span class="pk-letter">c</span><span>Konsorsium Nasional Inovasi Pendidikan: kerja sama lintas sektor untuk pengembangan sistem pendidikan yang lebih inklusif.</span></li>
                                <li><span class="pk-letter">d</span><span>Regulasi Kemitraan Strategis dengan Pemerintah dan Swasta di bidang pendidikan.</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="indonesia-divider"></div>

    <!-- Kontak -->
    <section class="py-5 bg-light" id="kontak">
        <div class="container">
            <div class="row mb-3">
                <div class="col-md-12 text-center" data-aos="fade-right">
                    <span style="display:inline-flex;align-items:center;gap:10px;padding:12px 24px;background:#fff;border:1px solid #ffe2e2;border-radius:999px;color:#e30a17;font-size:1.42rem;font-weight:800;letter-spacing:-.02em;box-shadow:0 4px 12px rgba(227,10,23,0.06);"><i class="fa-solid fa-paper-plane"></i> Hubungi Kami</span>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <style>
                        .contact-card { border-radius: 14px; padding: 28px; text-align: center; background: #fff; box-shadow: 0 8px 30px rgba(0,0,0,0.06); transition: transform 0.3s ease, box-shadow 0.3s ease; }
                        .contact-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.12); }
                        .contact-card h4 { color: var(--primary-color); font-weight:700; }
                        .contact-claim { margin: 8px 0 18px 0; color: #444; }
                        .contact-list { list-style: none; padding: 0; margin: 12px 0 0 0; }
                        .contact-list li { margin: 10px 0; font-weight:600; }
                        .contact-list a { color: var(--primary-color); text-decoration: none; }
                        .contact-list i { margin-right: 8px; color: var(--primary-color); }
                    </style>

                    <div class="contact-card" data-aos="zoom-in">
                        <h4>Inisiator HIPPMI</h4>
                        <p class="contact-claim">Dr. Hardika Prayudi Styawan, S.Pd., M.Pd., M.M.</p>

                        <ul class="contact-list">
                            <li><i class="fas fa-phone"></i> <a href="tel:+6285631540010">0856-3154-010</a></li>
                            <li><i class="fas fa-envelope"></i> <a href="mailto:info@hippmi.org">info@hippmi.org</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <!-- Custom Script -->
    <script>
        try {
            if (typeof AOS !== 'undefined' && AOS && typeof AOS.init === 'function') {
                const isMob = window.matchMedia('(max-width: 767.98px)').matches;
                if (isMob) {
                    document.querySelectorAll('[data-aos-delay]').forEach(el=>{
                        const d = parseInt(el.getAttribute('data-aos-delay')||'0',10);
                        if(d>60) el.setAttribute('data-aos-delay','50');
                    });
                    document.querySelectorAll('[data-aos]').forEach(el=>{
                        el.removeAttribute('data-aos-anchor');
                        el.removeAttribute('data-aos-offset');
                        el.setAttribute('data-aos-anchor-placement','top-bottom');
                    });
                }
                AOS.init({
                    duration: isMob ? 420 : 550,
                    once: true,
                    offset: isMob ? 24 : 60,
                    delay: 0,
                    anchorPlacement: 'top-bottom',
                    easing: 'ease-out',
                    mirror: false,
                    disable: false
                });
                const rafRefresh = ()=>{ try{AOS.refreshHard();}catch(e){} };
                setTimeout(rafRefresh, 80);
                setTimeout(rafRefresh, 350);
                window.addEventListener('load', ()=> setTimeout(rafRefresh, 180));
                let t;
                window.addEventListener('scroll', ()=>{
                    clearTimeout(t);
                    t = setTimeout(rafRefresh, 80);
                }, {passive:true});
                if (isMob) {
                    const forceVisible = ()=>{
                        document.querySelectorAll('[data-aos]:not(.aos-animate)').forEach(el=>{
                            const r = el.getBoundingClientRect();
                            if (r.top < window.innerHeight * 0.92) el.classList.add('aos-animate');
                        });
                    };
                    window.addEventListener('scroll', forceVisible, {passive:true});
                    setTimeout(forceVisible, 700);
                    setTimeout(()=>{
                        document.querySelectorAll('[data-aos]:not(.aos-animate)').forEach(el=>{
                            const r = el.getBoundingClientRect();
                            if (r.top < window.innerHeight * 1.15) el.classList.add('aos-animate');
                        });
                    }, 1400);
                }
            }
        } catch (e) {
            console.warn('AOS init failed:', e);
            document.querySelectorAll('[data-aos]').forEach(el=>el.classList.add('aos-animate'));
        }
        
        // Preloader (additional guard in main script)
        try {
            window.addEventListener('load', function() {
                var pre = document.querySelector('.preloader');
                if (pre) {
                    pre.style.opacity = '0';
                    setTimeout(function() { pre.style.display = 'none'; }, 500);
                }
            });
        } catch (e) {
            console.warn('Preloader hide failed in main script:', e);
        }
        
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
        
        // Smooth scroll dengan offset dinamis agar section tidak tertutup navbar
        try {
            function getSectionScrollOffset() {
                const navbar = document.querySelector('.navbar-modern, nav.navbar, nav');
                const navbarHeight = navbar ? navbar.getBoundingClientRect().height : 80;
                const extraGap = window.innerWidth <= 991.98 ? 14 : 24;

                return Math.ceil(navbarHeight + extraGap);
            }

            function updateSectionScrollOffset() {
                const offset = getSectionScrollOffset();
                document.documentElement.style.setProperty('--section-scroll-offset', offset + 'px');
                return offset;
            }

            function scrollToSection(targetId, smooth = true, updateUrl = true) {
                if (!targetId || targetId === '#') return false;

                let targetElement = null;
                try {
                    targetElement = document.querySelector(targetId);
                } catch (error) {
                    targetElement = null;
                }

                if (!targetElement) return false;

                const offset = updateSectionScrollOffset();
                const targetTop =
                    targetElement.getBoundingClientRect().top +
                    window.pageYOffset -
                    offset;

                window.scrollTo({
                    top: Math.max(0, targetTop),
                    behavior: smooth ? 'smooth' : 'auto'
                });

                if (updateUrl && window.history && window.history.pushState) {
                    window.history.pushState(null, '', targetId);
                }

                return true;
            }

            /*
             * Mendukung href seperti:
             * #tentang
             * beranda#tentang
             * beranda.php#tentang
             */
            document.querySelectorAll('a[href*="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href') || '';
                    const hashPosition = href.indexOf('#');
                    if (hashPosition === -1) return;

                    const targetId = href.substring(hashPosition);
                    if (targetId === '#') return;

                    if (document.querySelector(targetId)) {
                        e.preventDefault();
                        scrollToSection(targetId, true, true);
                    }
                });
            });

            /*
             * Koreksi posisi saat halaman dibuka langsung memakai hash,
             * misalnya /beranda#core-values.
             */
            window.addEventListener('load', function() {
                updateSectionScrollOffset();

                if (window.location.hash) {
                    setTimeout(function() {
                        scrollToSection(window.location.hash, false, false);
                    }, 150);
                }
            });

            window.addEventListener('hashchange', function() {
                if (window.location.hash) {
                    scrollToSection(window.location.hash, true, false);
                }
            });

            window.addEventListener('resize', updateSectionScrollOffset);
            updateSectionScrollOffset();
        } catch (e) {
            console.warn('Smooth scroll setup failed:', e);
        }
        
        // Form submission with animation
        const contactForm = document.querySelector('.contact-form');
        if(contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Here you would normally handle the form submission via AJAX
                
                // Visual feedback
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...';
                
                // Simulate form submission
                setTimeout(() => {
                    submitBtn.innerHTML = '<i class="fas fa-check me-2"></i> Terkirim!';
                    submitBtn.classList.remove('btn-nasional');
                    submitBtn.classList.add('btn-success');
                    
                    // Reset form
                    this.reset();
                    
                    // Reset button after delay
                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.classList.remove('btn-success');
                        submitBtn.classList.add('btn-nasional');
                        submitBtn.disabled = false;
                    }, 3000);
                    
                }, 2000);
            });
        }
        
        // Add parallax effect to hero section
        window.addEventListener('scroll', function() {
            const hero = document.querySelector('.hero-section');
            if(hero) {
                const scrollPosition = window.pageYOffset;
                hero.style.backgroundPosition = '50% ' + (50 + scrollPosition * 0.05) + '%';
            }
        });
        
        // Counter animation (guarded and robust)
        try {
            (function() {
                const counterEls = document.querySelectorAll('.counter');
                if (!counterEls || counterEls.length === 0) return;

                const speed = 200;

                function animateCounters() {
                    counterEls.forEach(counter => {
                        const target = parseInt(counter.getAttribute('data-target')) || 0;
                        const count = parseInt(counter.innerText) || 0;
                        const increment = Math.ceil(Math.max(1, target / speed));

                        if (count < target) {
                            counter.innerText = Math.min(target, count + increment);
                        } else {
                            counter.innerText = target;
                        }
                    });
                }

                // Run animation in intervals until all reach their target
                function runAnimationUntilComplete() {
                    const stillRunning = Array.from(counterEls).some(el => parseInt(el.innerText) < (parseInt(el.getAttribute('data-target')) || 0));
                    if (stillRunning) {
                        animateCounters();
                        setTimeout(runAnimationUntilComplete, 30);
                    }
                }

                // Find enclosing section safely
                var counterSection = null;
                try {
                    counterSection = counterEls[0].closest('section');
                } catch (e) { counterSection = null; }

                if (counterSection && 'IntersectionObserver' in window) {
                    const options = { threshold: 0.5 };
                    const observer = new IntersectionObserver((entries, obs) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                runAnimationUntilComplete();
                                obs.unobserve(entry.target);
                            }
                        });
                    }, options);
                    observer.observe(counterSection);
                } else {
                    // If no intersection observer, run after short delay
                    setTimeout(runAnimationUntilComplete, 300);
                }
            })();
        } catch (e) {
            console.warn('Counter setup failed:', e);
        }
    </script>
</body>
</html>