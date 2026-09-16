<div class="topbar">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-5">
                <div class="topbar-left d-flex align-items-center">
                    <a href="mailto:info@hippmi.org" class="topbar-link me-4 animated-item">
                        <i class="fas fa-envelope me-1 topbar-icon"></i> info@hippmi.org
                    </a>
                    <a href="tel:+6285631540010" class="topbar-link animated-item" style="animation-delay: 0.1s;">
                        <i class="fas fa-phone-alt me-1 topbar-icon"></i> 0856-3154-010
                    </a>
                </div>
            </div>
            <div class="col-lg-6 col-md-7">
                <div class="topbar-right d-flex align-items-center justify-content-md-end">
                    <div class="topbar-social">
                        <a href="#" class="topbar-social-icon animated-item" style="animation-delay: 0.2s;" aria-label="Facebook" data-tooltip="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="topbar-social-icon animated-item" style="animation-delay: 0.3s;" aria-label="Twitter" data-tooltip="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="topbar-social-icon animated-item" style="animation-delay: 0.4s;" aria-label="Instagram" data-tooltip="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="topbar-social-icon animated-item" style="animation-delay: 0.5s;" aria-label="YouTube" data-tooltip="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<nav class="navbar navbar-expand-lg navbar-modern">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center animated-brand" href="index.php">
            <div class="logo-pulse-wrapper">
                <img src="img/Logo.webp" alt="Logo HIPPMI" class="navbar-logo">
            </div>
            <div class="brand-text ms-3">
                <span class="brand-title text-reveal">HIPPMI</span>
                <span class="brand-subtitle text-shimmer">Himpunan Pendidik & Pengajar Muda Indonesia</span>
            </div>
        </a>
        <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="toggler-icon top-bar"></span>
            <span class="toggler-icon middle-bar"></span>
            <span class="toggler-icon bottom-bar"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item" style="animation-delay: 0.1s;">
                    <a class="nav-link animated-link" href="index.php" data-text="Beranda">Beranda</a>
                </li>
                <li class="nav-item" style="animation-delay: 0.2s;">
                    <a class="nav-link animated-link" href="index.php#tentang" data-text="Tentang Kami">Tentang Kami</a>
                </li>
                <li class="nav-item" style="animation-delay: 0.3s;">
                    <a class="nav-link animated-link" href="index.php#visimisi" data-text="Visi & Misi">Visi & Misi</a>
                </li>
                <li class="nav-item" style="animation-delay: 0.4s;">
                    <a class="nav-link animated-link" href="index.php#core-values" data-text="Core Values">Core Values</a>
                </li>
                <li class="nav-item" style="animation-delay: 0.5s;">
                    <a class="nav-link animated-link" href="index.php#struktur" data-text="Struktur Organisasi">Struktur</a>
                </li>
                <li class="nav-item" style="animation-delay: 0.5s;">
                    <a class="nav-link animated-link" href="index.php#program" data-text="Program Kerja">Program</a>
                </li>
                <li class="nav-item" style="animation-delay: 0.6s;">
                    <a class="nav-link animated-link" href="berita.php" data-text="Berita">Berita</a>   
                </li>
                <li class="nav-item" style="animation-delay: 0.7s;">
                    <a class="nav-link animated-link" href="kegiatan.php" data-text="Kegiatan">Kegiatan</a>
                </li>
                <li class="nav-item kontak-item" style="animation-delay: 0.8s;">
                    <a class="nav-link contact-link animated-btn" href="index.php#kontak" data-text="Kontak">
                        <span class="btn-text">Kontak</span>
                        <span class="btn-shine"></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<style>
    .topbar {
        background: linear-gradient(to right, #000000, #111111);
        padding: 10px 0;
        font-size: 0.85rem;
        color: #ffffff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: relative;
        z-index: 100;
        transition: transform 0.3s ease;
    }
    .topbar.hide {
        transform: translateY(-100%);
    }
    .topbar::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    }
    .animated-item {
        opacity: 0;
        animation: fadeInUp 0.6s forwards;
    }
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .topbar-icon {
        background: linear-gradient(135deg, #e30a17, #ff5b5b);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: 1rem;
        transition: all 0.3s;
        display: inline-block;
    }
    .topbar-link:hover .topbar-icon {
        transform: rotate(360deg) scale(1.2);
        animation: none;
    }
    .topbar-link {
        color: #ffffff;
        opacity: 0.8;
        text-decoration: none;
        padding: 3px 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    .topbar-link:hover {
        opacity: 1;
        color: #ffffff;
        transform: scale(1.05); 
    }
    .topbar-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: rgba(227, 10, 23, 0.2);
        transition: all 0.3s;
        z-index: -1;
    }
    .topbar-link:hover::before {
        left: 0;
        animation: sweep 0.5s forwards;
    }
    @keyframes sweep {
        0% {
            left: -100%;
            width: 100%;
        }
        50% {
            left: 0;
            width: 100%;
        }
        100% {
            left: 100%;
            width: 0;
        }
    }
    .topbar-social {
        display: flex;
        gap: 8px;
    }
    .topbar-social-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        color: #ffffff;
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        text-decoration: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    .topbar-social-icon i {
        transition: all 0.3s ease;
    }
    .topbar-social-icon:hover i {
        transform: rotate(360deg);
    }
    .topbar-social-icon::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: -30px;
        left: 50%;
        transform: translateX(-50%);
        padding: 4px 8px;
        background-color: #000;
        color: #fff;
        font-size: 10px;
        border-radius: 3px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    .topbar-social-icon:hover::after {
        opacity: 1;
        visibility: visible;
        bottom: -25px;
        animation: fadeIn 0.3s forwards;
    }
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }
    .topbar-social-icon::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #e30a17, #ff5b5b);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: -1;
    }
    .topbar-social-icon:hover {
        color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 5px 10px rgba(227, 10, 23, 0.3);
    }
    .topbar-social-icon:hover::before {
        opacity: 1;
        animation: ripple 0.6s linear;
    }
    @keyframes ripple {
        0% {
            transform: scale(0.5);
            opacity: 0.5;
        }
        100% {
            transform: scale(1.2);
            opacity: 0;
        }
    }
    .navbar-modern {
        background: linear-gradient(135deg, #e30a17, #d10914);
        padding: 15px 0;
        transition: all 0.4s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        position: relative; 
        width: 100%;
        z-index: 99;
        overflow: hidden;
    }
    .navbar-modern.scrolled {
        position: fixed;
        top: 0;
        padding: 10px 0;
        background: rgba(227, 10, 23, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        animation: navbarShrink 0.5s forwards;
    }
    body {
        padding-top: 0;
    }
    body.scrolled {
        padding-top: 80px;
    }
    @keyframes navbarShrink {
        from {
            padding: 15px 0;
        }
        to {
            padding: 10px 0;
        }
    }
    .navbar-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        opacity: 0.3;
        pointer-events: none;
    }
    .animated-brand {
        opacity: 0;
        animation: slideInLeft 0.6s 0.1s forwards;
    }
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    .logo-pulse-wrapper {
        position: relative;
        display: inline-block;
    }
    .logo-pulse-wrapper::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%) scale(0.8);
        animation: logoPulse 2s infinite;
        opacity: 0;
        pointer-events: none;
        z-index: -1;
    }
    @keyframes logoPulse {
        0% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 0;
        }
        50% {
            opacity: 0.3;
        }
        100% {
            transform: translate(-50%, -50%) scale(1.5);
            opacity: 0;
        }
    }
    .navbar-logo {
        height: 50px;
        width: auto;
        transition: transform 0.3s ease;
        filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.2));
        max-width: 100%;
    }
    .navbar-brand {
        transition: transform 0.3s ease;
    }
    .navbar-brand:hover {
        transform: translateY(-2px);
    }
    .navbar-brand:hover .navbar-logo {
        transform: scale(1.05);
        animation: wobble 1s ease;
    }
    @keyframes wobble {
        0%, 100% {
            transform: scale(1.05) rotate(0deg);
        }
        25% {
            transform: scale(1.05) rotate(-3deg);
        }
        75% {
            transform: scale(1.05) rotate(3deg);
        }
    }
    .brand-text {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }
    .brand-title {
        font-weight: 700;
        font-size: 1.4rem;
        color: #ffffff;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
    }
    .text-reveal {
        position: relative;
        overflow: hidden;
    }
    .text-reveal::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, #fff, transparent);
        animation: textReveal 3s infinite;
        transform: translateX(-100%);
    }
    @keyframes textReveal {
        0% {
            transform: translateX(-100%);
        }
        50%, 100% {
            transform: translateX(100%);
        }
    }
    .brand-subtitle {
        font-size: 0.7rem;
        color: rgba(255, 255, 255, 0.9);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }
    .text-shimmer {
        background: linear-gradient(to right, #fff 0%, rgba(255,255,255,0.8) 50%, #fff 100%);
        background-size: 200% auto;
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: shimmer 3s linear infinite;
    }
    @keyframes shimmer {
        to {
            background-position: 200% center;
        }
    }
    .custom-toggler {
        border: none;
        background-color: transparent;
        width: 44px;
        height: 44px;
        padding: 0;
        position: relative;
        outline: none !important;
        box-shadow: none !important;
        border-radius: 8px;
    }
    .custom-toggler:focus {
        box-shadow: none !important;
        outline: none !important;
    }
    .toggler-icon {
        display: block;
        position: absolute;
        height: 3px;
        width: 30px;
        background: #ffffff;
        border-radius: 2px;
        opacity: 1;
        left: 7px;
        transition: .3s ease-in-out;
    }
    .middle-bar {
        top: 50%;
        transform: translateY(-50%);
    }
    .top-bar {
        top: 30%;
        transform: translateY(-50%);
    }
    .bottom-bar {
        top: 70%;
        transform: translateY(-50%);
    }
    .navbar-toggler[aria-expanded="true"] .top-bar {
        top: 50%;
        transform: translateY(-50%) rotate(45deg);
        background-color: #ffffff;
    }
    .navbar-toggler[aria-expanded="true"] .middle-bar {
        opacity: 0;
    }
    .navbar-toggler[aria-expanded="true"] .bottom-bar {
        top: 50%;
        transform: translateY(-50%) rotate(-45deg);
        background-color: #ffffff;
    }
    @media (max-width: 991.98px) {
        .custom-toggler:hover .top-bar,
        .custom-toggler:hover .bottom-bar,
        .navbar-toggler[aria-expanded="true"]:hover .top-bar,
        .navbar-toggler[aria-expanded="true"]:hover .bottom-bar {
            transform: none;
        }
        .navbar-toggler[aria-expanded="true"]:hover .top-bar {
            top: 50%;
            transform: translateY(-50%) rotate(45deg);
        }
        .navbar-toggler[aria-expanded="true"]:hover .bottom-bar {
            top: 50%;
            transform: translateY(-50%) rotate(-45deg);
        }
        .navbar-nav .nav-item:hover {
            transform: none !important;
            background-color: rgba(255, 255, 255, 0.05) !important;
        }
        .navbar-nav .nav-link:hover {
            transform: none !important;
            color: #ffffff !important;
        }
        .navbar-nav .nav-link:hover::after {
            width: 0 !important;
            opacity: 0 !important;
            box-shadow: none !important;
        }
        .navbar-nav .nav-link:hover::before {
            height: 0 !important;
            animation: none !important;
        }
        .animated-link:hover::before {
            animation: none !important;
            height: 0 !important;
        }
        .navbar-nav .nav-item:hover .nav-link::before {
            transform: none !important;
        }
        .navbar-nav .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            border-left: 3px solid #ffffff;
        }
        .animated-btn:hover {
            transform: none !important;
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
            box-shadow: none !important;
        }
        .animated-btn:hover::before {
            transform: scaleX(0) !important;
        }
        .btn-shine {
            animation: none !important;
            display: none !important;
        }
        .navbar-brand:hover {
            transform: none !important;
        }
        .navbar-brand:hover .navbar-logo {
            transform: none !important;
            animation: none !important;
        }
        .topbar-link:hover .topbar-icon {
            transform: none !important;
            animation: none !important;
        }
        .topbar-link:hover {
            opacity: 0.8 !important;
            transform: none !important;
        }
        .topbar-link:hover::before {
            left: -100% !important;
            animation: none !important;
        }
        .topbar-social-icon:hover {
            transform: none !important;
            box-shadow: none !important;
        }
        .topbar-social-icon:hover::before {
            opacity: 0 !important;
            animation: none !important;
        }
        .topbar-social-icon:hover i {
            transform: none !important;
        }
        .topbar-social-icon:hover::after {
            opacity: 0 !important;
            visibility: hidden !important;
        }
    }
    @keyframes fadeInItem {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .navbar-nav .nav-item {
        opacity: 0;
        animation: fadeInItem 0.5s forwards;
    }
    .animated-link {
        color: #ffffff;
        font-weight: 500;
        padding: 10px 15px;
        position: relative;
        transition: all 0.4s ease;
        margin: 0 3px;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        z-index: 1;
    }
    .animated-link::before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 0;
        background: rgba(255, 255, 255, 0.1);
        transition: all 0.3s;
        z-index: -1;
    }
    .animated-link:hover::before {
        height: 100%;
        animation: fillUp 0.3s forwards;
    }
    @keyframes fillUp {
        from {
            height: 0;
        }
        to {
            height: 100%;
        }
    }
    .animated-link::after {
        content: '';
        position: absolute;
        bottom: 5px;
        left: 50%;
        width: 0;
        height: 2px;
        background-color: #ffffff;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        transform: translateX(-50%);
        opacity: 0;
    }
    .animated-link:hover {
        color: #ffffff;
        transform: translateY(-2px);
    }
    .animated-link:hover::after {
        width: 70%;
        opacity: 1;
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
    }
    /* Active dibuat sama persis dengan tampilan hover dan tetap menyala. */
    .animated-link.active,
    .animated-link.active:hover,
    .animated-link.active:focus {
        color: #ffffff !important;
        transform: translateY(-2px) !important;
        outline: none;
    }
    .animated-link.active::before,
    .animated-link.active:hover::before,
    .animated-link.active:focus::before {
        height: 100%;
        animation: none;
    }
    .animated-link.active::after,
    .animated-link.active:hover::after,
    .animated-link.active:focus::after {
        width: 70%;
        opacity: 1;
        box-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
    }
    /* Hilangkan efek klik hanya pada link yang tidak sedang aktif. */
    .animated-link:not(.active):active,
    .animated-link:not(.active):focus,
    .navbar-nav .nav-link:not(.active):active,
    .navbar-nav .nav-link:not(.active):focus {
        background-color: transparent !important;
        box-shadow: none !important;
        transform: none !important;
        outline: none !important;
        color: inherit !important;
    }
    /* If any scripts add a click-overlay element, hide it visually here (defensive) */
    .nav-click-overlay {
        display: none !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    /* when an animated button is inside an active nav item, don't run hover shine */
    .navbar-nav .nav-link.active .animated-btn:hover,
    .navbar-nav .nav-link.active .animated-btn:hover .btn-shine {
        transform: none !important;
        box-shadow: none !important;
        animation: none !important;
    }
    @keyframes pulseActive {
        0% {
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }
        50% {
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.4);
        }
        100% {
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }
    }
    .animated-link:hover::after {
        width: 70%;
        opacity: 1;
    }
    .animated-btn {
        background-color: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 50px;
        padding: 8px 22px !important;
        margin-left: 10px;
        transition: all 0.3s ease !important;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    .btn-text {
        position: relative;
        z-index: 3;
        transition: all 0.3s ease;
    }
    .btn-shine {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(120deg, 
            rgba(255,255,255,0) 0%, 
            rgba(255,255,255,0.3) 50%,
            rgba(255,255,255,0) 100%);
        animation: shine 3s infinite;
    }
    @keyframes shine {
        0% {
            left: -100%;
        }
        20% {
            left: 100%;
        }
        100% {
            left: 100%;
        }
    }
    .animated-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, rgba(255,255,255,0.1), rgba(255,255,255,0.3), rgba(255,255,255,0.1));
        z-index: -1;
        transform: scaleX(0);
        transform-origin: 0 50%;
        transition: transform 0.5s ease-out;
    }
    .animated-btn:hover::before {
        transform: scaleX(1);
        transition-timing-function: cubic-bezier(0.52, 1.64, 0.37, 0.66);
    }
    .animated-btn::after {
        display: none;
    }
    .animated-btn:hover {
        background-color: #ffffff;
        color: #e30a17 !important;
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    .animated-btn.active,
    .animated-btn.active:hover,
    .animated-btn.active:focus {
        background-color: #ffffff !important;
        color: #e30a17 !important;
        transform: translateY(-3px) scale(1.05) !important;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2) !important;
        outline: none;
    }
    .animated-btn.active::before {
        transform: scaleX(1);
    }
    .animated-btn:active {
        transform: translateY(-1px) scale(0.98);
    }
    .navbar-nav .dropdown-menu {
        border: none;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-top: 15px;
        animation: dropdown-fade 0.3s ease-out;
    }
    @keyframes dropdown-fade {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .navbar-nav {
        display: flex;
        align-items: center;
        gap: 0; 
    }
    .navbar-nav .nav-item .nav-link {
        padding: 10px 6px;
        margin: 0 1px; 
    }
    .berita-item, .kegiatan-item {
        display: inline-flex;
    }
    .berita-item .nav-link, 
    .kegiatan-item .nav-link {
        padding-left: 4px;
        padding-right: 4px;
    }
    .berita-item {
        margin-right: -15px; 
    }
    .kegiatan-item {
        margin-left: -15px; 
    }
    .kontak-item {
        margin-left: 8px;
    }
    .toggler-ripple {
        position: absolute;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        transform: scale(0);
        animation: rippleEffect 0.6s linear;
        pointer-events: none;
    }
    @keyframes rippleEffect {
        to {
            transform: scale(2);
            opacity: 0;
        }
    }
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    @media (max-width: 1366px) and (min-width: 992px) {
        .navbar-modern .brand-text{ display:none !important; }
    }
    @media (max-width: 1199.98px) {
        .navbar-nav .nav-item .nav-link {
            padding: 10px 4px;
            font-size: 0.92rem;
        }
        .berita-item {
            margin-right: -10px;
        }
        .kegiatan-item {
            margin-left: -10px;
        }
    }
    @media (max-width: 991.98px) and (min-width: 768px) {
        .topbar{padding:6px 0}
        .topbar .container > .row{flex-direction:row!important;align-items:center!important;justify-content:space-between!important;flex-wrap:nowrap!important;gap:0}
        .topbar .container > .row > [class*="col-"]{width:auto!important;flex:0 0 auto;display:flex;align-items:center}
        .topbar-left{justify-content:flex-start!important;flex-wrap:nowrap!important;gap:12px}
        .topbar-right{justify-content:flex-end!important;margin-top:0!important}
        .topbar-link{font-size:.78rem;white-space:nowrap}
        .topbar-social{margin-top:0;gap:8px}
        .topbar-social-icon{width:26px;height:26px;font-size:.9rem}
        .navbar-brand{max-width:70%}
        .navbar-logo{height:45px}
        .brand-title{font-size:1.2rem}
        .brand-subtitle{font-size:.65rem}
    }
    @media (max-width: 820px) and (min-width: 768px) {
        .topbar{padding:6px 0}
        .topbar .container > .row{flex-direction:row!important;align-items:center!important;justify-content:space-between!important;flex-wrap:nowrap!important;gap:0}
        .topbar-left{width:auto!important;display:flex!important;justify-content:flex-start!important;padding:0!important;margin-top:0!important;gap:12px;align-items:center!important}
        .topbar-right{width:auto!important;display:flex!important;justify-content:flex-end!important;padding:0!important;margin-top:0!important;align-items:center!important}
        .topbar-link{padding:2px 6px!important;font-size:.78rem!important}
        .topbar-social{gap:8px!important;margin-top:0!important}
        .navbar > .container{max-width:95%}
    }
    @media (max-width: 767.98px) and (min-width: 576px) {
        .topbar {
            padding: 5px 0;
        }
        .topbar .container > .row {
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        .topbar-left, .topbar-right {
            width: 100%;
            justify-content: center;
            text-align: center;
        }
        .topbar-left {
            flex-direction: column;
            gap: 5px;
        }
        .topbar-link {
            padding: 2px 5px;
            font-size: 0.75rem;
        }
        .topbar-icon {
            font-size: 0.9rem;
        }
        .navbar-logo {
            height: 40px;
        }
        .brand-text {
            max-width: calc(100% - 60px);
        }
        .brand-title {
            font-size: 1.1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .brand-subtitle {
            font-size: 0.6rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
    @media (max-width: 991.98px) and (min-width: 576px) {
        .navbar-collapse {
            background: linear-gradient(135deg, #e30a17, #c00812);
            margin: 0 -12px;
            padding: 15px;
        }
        .topbar .container {
            width: 100%;
            padding-left: 15px;
            padding-right: 15px;
        }
    }
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: linear-gradient(135deg, #e30a17, #c00812);
            margin: 0 -12px;
            padding: 15px;
            max-height: 80vh;
            overflow-y: auto;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            animation: navbar-slide-down 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        @keyframes navbar-slide-down {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .navbar-nav .nav-item {
            animation-delay: 0.1s !important;
            animation-duration: 0.3s;
            margin: 4px 0;
            border-radius: 8px;
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }
        .navbar-nav .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.05);
            transform: none;
        }
        .navbar-nav .nav-link {
            padding: 12px 20px;
            border-radius: 0;
            margin: 0;
            display: flex;
            align-items: center;
            font-size: 1rem;
        }
        .navbar-nav .nav-link::after {
            display: none;
        }
        .navbar-nav .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            box-shadow: none;
            animation: none;
            border-left: 3px solid #ffffff;
        }
        .navbar-nav .nav-item:nth-child(1) .nav-link::before {
            content: "\f015";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .navbar-nav .nav-item:nth-child(2) .nav-link::before {
            content: "\f2b9";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .navbar-nav .nav-item:nth-child(3) .nav-link::before {
            content: "\f002";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .navbar-nav .nav-item:nth-child(4) .nav-link::before {
            content: "\f0e8";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .navbar-nav .nav-item:nth-child(5) .nav-link::before {
            content: "\f0ae";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .navbar-nav .berita-item .nav-link::before {
            content: "\f1ea";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .navbar-nav .kegiatan-item .nav-link::before {
            content: "\f073";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        .berita-item,
        .kegiatan-item {
            margin-left: 0;
            margin-right: 0;
        }
        .berita-item .nav-link, 
        .kegiatan-item .nav-link {
            padding: 12px 20px;
        }
        .kontak-item {
            margin: 10px 0 5px 0;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .contact-link {
            margin: 0;
            text-align: center;
            justify-content: center;
            display: flex;
            background-color: transparent;
        }
        .contact-link::before {
            content: "\f0e0";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        .brand-title,
        .brand-subtitle {
            display: none;
        }
        .topbar-left, .topbar-right {
            justify-content: center;
            text-align: center;
            margin-bottom: 5px;
        }
        .custom-toggler {
            width: 44px;
            height: 44px;
            padding: 5px;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            margin-right: -5px;
            transition: all 0.3s ease;
        }
        .custom-toggler:hover,
        .custom-toggler:focus {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .navbar-collapse.collapsing {
            height: 0;
            overflow: hidden;
            transition: height 0.35s ease;
        }
    }
    @media (max-width: 767.98px) {
        .navbar-brand {
            max-width: 70%;
        }
        .navbar-logo {
            height: 45px;
            margin-left: -10px; 
        }
        .brand-title {
            display: none;
        }
        .brand-subtitle {
            display: none;
        }
        .navbar-nav .nav-item {
            margin: 3px 0;
        }
        .navbar-nav .nav-link {
            padding: 10px 15px;
        }
        .navbar-nav .nav-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .navbar-nav .nav-item:last-child {
            border-bottom: none;
        }
        .navbar-collapse {
            padding-bottom: 10px;
        }
    }
    @media (max-width: 575.98px) {
        .topbar {
            display: none;
        }
        .navbar-brand {
            max-width: 65%;
        }
        .navbar-logo {
            height: 40px;
            margin-left: 10px;
        }
        .brand-title, 
        .brand-subtitle {
            display: none !important;
        }
        .navbar-nav .nav-item {
            margin: 2px 0;
        }
        .navbar-collapse {
            background: rgba(227, 10, 23, 0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .kontak-item {
            margin-top: 8px;
            background-color: rgba(255, 255, 255, 0.15);
        }
        .custom-toggler {
            width: 44px;
            height: 44px;
            padding: 0;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            margin-right: -5px;
            z-index: 1000;
        }
        .navbar-toggler[aria-expanded="true"] .top-bar {
            top: 50%;
            transform: translateY(-50%) rotate(45deg);
        }
        .navbar-toggler[aria-expanded="true"] .bottom-bar {
            top: 50%;
            transform: translateY(-50%) rotate(-45deg);
        }
        .custom-toggler:hover .top-bar,
        .custom-toggler:hover .middle-bar,
        .custom-toggler:hover .bottom-bar {
            transform: none;
        }
        .custom-toggler:hover .top-bar {
            top: 30%;
            transform: translateY(-50%);
        }
        .custom-toggler:hover .bottom-bar {
            top: 70%;
            transform: translateY(-50%);
        }
        .navbar-nav .nav-item:active,
        .navbar-nav .nav-link:active,
        .animated-btn:active {
            transform: none !important;
        }
        .animated-btn {
            border: none !important;
        }
        .navbar-nav .nav-item,
        .navbar-nav .nav-link,
        .animated-btn,
        .navbar-brand,
        .navbar-logo {
            transition: none !important;
        }
    }


    /* =========================================================
       FINAL ACTIVE LINK — SAMA DENGAN TAMPILAN HOVER
       Diletakkan paling akhir agar tidak tertimpa aturan lain.
       ========================================================= */
    @media (min-width: 992px) {
        .navbar-modern .navbar-nav .nav-link.animated-link:hover,
        .navbar-modern .navbar-nav .nav-link.animated-link.active,
        .navbar-modern .navbar-nav .nav-link.animated-link.active:hover,
        .navbar-modern .navbar-nav .nav-link.animated-link.active:focus {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
            transform: translateY(-2px) !important;
            box-shadow: none !important;
            outline: none !important;
        }

        .navbar-modern .navbar-nav .nav-link.animated-link:hover::before,
        .navbar-modern .navbar-nav .nav-link.animated-link.active::before,
        .navbar-modern .navbar-nav .nav-link.animated-link.active:hover::before,
        .navbar-modern .navbar-nav .nav-link.animated-link.active:focus::before {
            height: 100% !important;
            background: rgba(255, 255, 255, 0.06) !important;
            animation: none !important;
        }

        .navbar-modern .navbar-nav .nav-link.animated-link:hover::after,
        .navbar-modern .navbar-nav .nav-link.animated-link.active::after,
        .navbar-modern .navbar-nav .nav-link.animated-link.active:hover::after,
        .navbar-modern .navbar-nav .nav-link.animated-link.active:focus::after {
            display: block !important;
            width: 70% !important;
            height: 3px !important;
            bottom: 4px !important;
            opacity: 1 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 5px rgba(255, 255, 255, 0.55) !important;
        }
    }

    /* PATCH mobile: rapi center + hover & active (desktop tidak diubah) */
    @media (max-width: 991.98px) {
        .navbar-collapse{padding-top:8px !important;padding-bottom:14px !important}
        .navbar-nav{align-items:center !important;width:100%}
        .navbar-nav .nav-item{width:100%;max-width:420px;margin:5px auto !important;border:0 !important;background:transparent !important;border-radius:12px;overflow:hidden}
        .navbar-nav .nav-link{justify-content:center !important;text-align:center !important;border-radius:12px !important;background:rgba(255,255,255,.08) !important;border:1px solid rgba(255,255,255,.14) !important;gap:0 !important;transition:all .22s cubic-bezier(.32,.72,0,1) !important;transform:none !important;box-shadow:none !important}
        .navbar-nav .nav-link::after{display:none !important}
        .navbar-nav .nav-link::before{display:none !important;content:none !important}
        .contact-link::before{display:none !important;content:none !important}
        .navbar-nav .nav-link:hover{background:rgba(255,255,255,.18) !important;border-color:rgba(255,255,255,.22) !important;transform:translateY(-1px) !important;color:#fff !important;box-shadow:0 6px 16px rgba(0,0,0,.14) !important}
        .navbar-nav .nav-link.active{color:#e30a17 !important;background:#fff !important;border-color:#fff !important;font-weight:700 !important;box-shadow:0 8px 22px rgba(0,0,0,.18) !important;transform:translateY(-1px) !important;border-left:0 !important}
        .navbar-nav .nav-link.active::before{color:#e30a17 !important}
        .kontak-item{width:100%;max-width:420px;margin:10px auto 4px !important;background:transparent !important;border:0 !important}
        .kontak-item .contact-link{justify-content:center !important;background:#fff !important;color:#e30a17 !important;border-radius:12px !important;font-weight:700 !important;box-shadow:0 8px 20px rgba(0,0,0,.16) !important;border:1px solid #fff !important;transform:none !important}
        .kontak-item .contact-link:hover{background:#0e0e10 !important;color:#fff !important;transform:translateY(-1px) !important;border-color:transparent !important}
        .kontak-item .contact-link.active{background:#fff !important;color:#e30a17 !important}
    }

</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navItems = document.querySelectorAll('.navbar-nav .nav-item');
        navItems.forEach((item, index) => {
            item.style.animationDelay = (0.1 * index) + 's';
        });
        const topbar = document.querySelector('.topbar');
        const navbar = document.querySelector('.navbar-modern');
        let lastScrollTop = 0;
        const navbarHeight = navbar ? navbar.offsetHeight : 0;
        const topbarHeight = topbar ? topbar.offsetHeight : 0;
        window.addEventListener('scroll', function() {
            const st = window.pageYOffset || document.documentElement.scrollTop;
            if (window.scrollY > 5) { 
                if (navbar && !navbar.classList.contains('scrolled')) {
                    navbar.classList.add('scrolled');
                    document.body.classList.add('scrolled');
                }
                if (topbar) {
                    topbar.style.display = 'none'; 
                }
            } else {
                if (navbar && navbar.classList.contains('scrolled')) {
                    navbar.classList.remove('scrolled');
                    document.body.classList.remove('scrolled');
                }
                if (topbar) {
                    topbar.style.display = '';
                }
            }
            lastScrollTop = st <= 0 ? 0 : st; 
        });
        const navLinks = Array.from(document.querySelectorAll('.navbar-nav .nav-link'));

        /*
         * Normalisasi nama halaman agar navbar tetap bekerja pada:
         * /beranda, /beranda.php, /index, dan /index.php.
         */
        function normalizePageName(value) {
            let cleanValue = String(value || '')
                .split('#')[0]
                .split('?')[0]
                .replace(/\\/g, '/')
                .replace(/\/+$/, '');

            cleanValue = cleanValue.split('/').pop().toLowerCase();
            return cleanValue.replace(/\.php$/, '');
        }

        const currentPage = normalizePageName(window.location.pathname);
        const homePageNames = ['', 'index', 'beranda', 'home'];
        const isHomePage = homePageNames.includes(currentPage);

        // Pasangkan setiap link hash dengan section yang mempunyai ID yang sama.
        const sectionLinks = navLinks
            .map(link => {
                const rawHref = link.getAttribute('href') || '';
                const hashPosition = rawHref.indexOf('#');

                if (hashPosition === -1) return null;

                const hash = rawHref.slice(hashPosition);
                const sectionId = decodeURIComponent(hash.slice(1));
                const section = document.getElementById(sectionId);

                return section ? { link, section, hash } : null;
            })
            .filter(Boolean);

        function activateOnly(linkToActivate) {
            navLinks.forEach(link => {
                const isActive = link === linkToActivate;
                link.classList.toggle('active', isActive);

                if (isActive) {
                    link.setAttribute('aria-current', 'page');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        }

        function findPageLink(pageNames) {
            const acceptedPages = Array.isArray(pageNames) ? pageNames : [pageNames];

            return navLinks.find(link => {
                const linkPage = normalizePageName(link.getAttribute('href'));

                // Link index dan beranda diperlakukan sebagai link halaman utama.
                if (acceptedPages.some(page => homePageNames.includes(page))) {
                    return homePageNames.includes(linkPage);
                }

                return acceptedPages.includes(linkPage);
            });
        }

        function findHashLink(hash) {
            return sectionLinks.find(item => item.hash === hash)?.link || null;
        }

        function setActiveLink() {
            // Mendukung route berita maupun berita.php.
            if (currentPage === 'berita') {
                activateOnly(findPageLink('berita'));
                return;
            }

            // Mendukung route kegiatan maupun kegiatan.php.
            if (currentPage === 'kegiatan') {
                activateOnly(findPageLink('kegiatan'));
                return;
            }

            // Jangan menandai section homepage pada halaman lain.
            if (!isHomePage) {
                activateOnly(null);
                return;
            }

            const navbarOffset = navbar ? navbar.offsetHeight : 0;
            const scrollMarker = window.scrollY + Math.max(145, navbarOffset + 45);
            let activeSectionLink = null;

            sectionLinks.forEach(item => {
                if (item.section.getBoundingClientRect().top + window.scrollY <= scrollMarker) {
                    activeSectionLink = item.link;
                }
            });

            // Bila belum mencapai section pertama, aktifkan Beranda.
            activateOnly(activeSectionLink || findPageLink(homePageNames));
        }

        function activateLinkFromHash() {
            if (!isHomePage || !window.location.hash) return false;

            const hashLink = findHashLink(window.location.hash);
            if (!hashLink) return false;

            activateOnly(hashLink);
            return true;
        }

        // Saat URL langsung berisi #core-values dan sejenisnya,
        // active tampil tanpa harus menunggu pengguna melakukan scroll.
        if (!activateLinkFromHash()) {
            setActiveLink();
        }

        window.addEventListener('scroll', setActiveLink, { passive: true });

        window.addEventListener('hashchange', function() {
            activateLinkFromHash();
            window.setTimeout(setActiveLink, 120);
        });

        // Browser kadang baru memindahkan posisi anchor setelah DOMContentLoaded.
        window.addEventListener('load', function() {
            window.requestAnimationFrame(setActiveLink);
            window.setTimeout(setActiveLink, 180);
        });

        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                const href = this.getAttribute('href') || '';

                if (isHomePage && href.includes('#')) {
                    activateOnly(this);
                }

                window.setTimeout(setActiveLink, 180);
            });
        });
        const navbarToggler = document.querySelector('.navbar-toggler');
        if (navbarToggler) {
            navbarToggler.addEventListener('click', function() {
                if (window.navigator && window.navigator.vibrate) {
                    window.navigator.vibrate(50);
                }
                const ripple = document.createElement('span');
                ripple.classList.add('toggler-ripple');
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        }
        const navbarCollapse = document.getElementById('navbarNav');
        if (navbarCollapse) {
            navbarCollapse.addEventListener('show.bs.collapse', function () {
                setTimeout(() => {
                    const items = document.querySelectorAll('.navbar-nav .nav-item');
                    items.forEach((item, index) => {
                        item.style.animationDelay = (0.05 * index) + 's';
                        item.style.animation = 'slideInRight 0.4s forwards';
                    });
                }, 150);
            });
            navbarCollapse.addEventListener('hide.bs.collapse', function () {
                const items = document.querySelectorAll('.navbar-nav .nav-item');
                items.forEach(item => {
                    item.style.animation = '';
                });
            });
        }
        function disableHoverOnMobile() {
            const isMobileOrTablet = window.matchMedia("(max-width: 991.98px)").matches;
            
            if (isMobileOrTablet) {
                const navbarLogo = document.querySelector('.navbar-logo');
                if (navbarLogo) {
                    navbarLogo.removeEventListener('mouseover', logoAnimationHandler);
                }
                document.querySelectorAll('.animated-btn').forEach(function(btn) {
                    btn.removeEventListener('mouseenter', btnShineHandler);
                    btn.removeEventListener('mouseleave', btnShineResetHandler);
                });
                document.body.classList.add('mobile-device');
            } else {
                document.body.classList.remove('mobile-device');
                setupEventListeners();
            }
        }
        function logoAnimationHandler() {
            this.animate([
                { transform: 'rotate(0deg)' },
                { transform: 'rotate(5deg)' },
                { transform: 'rotate(-5deg)' },
                { transform: 'rotate(0deg)' }
            ], {
                duration: 500,
                iterations: 1
            });
        }
        function btnShineHandler() {
            const shine = this.querySelector('.btn-shine');
            if (shine) {
                shine.style.left = '-100%';
                setTimeout(() => {
                    shine.style.transition = 'left 0.3s';
                    shine.style.left = '100%';
                }, 50);
            }
        }
        function btnShineResetHandler() {
            const shine = this.querySelector('.btn-shine');
            if (shine) {
                shine.style.transition = 'none';
            }
        }
        function setupEventListeners() {
            const isMobileOrTablet = window.matchMedia("(max-width: 991.98px)").matches;
            if (!isMobileOrTablet) {
                const navbarLogo = document.querySelector('.navbar-logo');
                if (navbarLogo) {
                    // logo hover animation intentionally disabled to avoid overlapping transforms with nav hover/active
                    // navbarLogo.addEventListener('mouseover', logoAnimationHandler);
                }
                document.querySelectorAll('.animated-btn').forEach(function(btn) {
                    btn.addEventListener('mouseenter', btnShineHandler);
                    btn.addEventListener('mouseleave', btnShineResetHandler);
                });
            }
        }
        function detectTablet() {
            const isMobile = window.matchMedia("(max-width: 767.98px)").matches;
            const isTablet = window.matchMedia("(min-width: 768px) and (max-width: 991.98px)").matches;
            if (isTablet) {
                const topbarLinks = document.querySelectorAll('.topbar-link');
                topbarLinks.forEach(link => {
                    link.style.whiteSpace = 'nowrap';
                    link.classList.remove('me-4');
                    link.classList.add('mx-1');
                });
                const socialIcons = document.querySelectorAll('.topbar-social-icon');
                socialIcons.forEach(icon => {
                    icon.style.margin = '0 3px';
                });
                const topbarContainer = document.querySelector('.topbar .container');
                if (topbarContainer) {
                    topbarContainer.style.maxWidth = '100%';
                    topbarContainer.style.paddingLeft = '10px';
                    topbarContainer.style.paddingRight = '10px';
                }
                const topbarRow = document.querySelector('.topbar .row');
                if (topbarRow) {
                    const colMd5 = topbarRow.querySelector('.col-md-5');
                    const colMd7 = topbarRow.querySelector('.col-md-7');
                    if (colMd5 && colMd7) {
                        colMd5.classList.add('mb-2');
                        colMd5.style.width = '100%';
                        colMd7.style.width = '100%';
                    }
                }
            }
        }
        setupEventListeners();
        disableHoverOnMobile();
        detectTablet();
        window.addEventListener('resize', function() {
            disableHoverOnMobile();
            detectTablet();
        });
        const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
        if (isIOS) {
            document.body.classList.add('ios-device');
            const style = document.createElement('style');
            style.textContent = `
                .ios-device .navbar-modern.scrolled {
                    position: absolute;
                }
                .ios-device {
                    -webkit-overflow-scrolling: touch;
                }
            `;
            document.head.appendChild(style);
        }
    });
</script>