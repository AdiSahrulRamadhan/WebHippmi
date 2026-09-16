<footer class="footer-modern">
    <div class="footer-animated-bg">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    <div class="container position-relative py-5">
        <div class="text-center mb-5 footer-logo-section" data-aos="fade-up">
            <div class="footer-logo-container mb-3">
                <img src="img/Logo.webp" class="footer-logo" alt="Logo HIPPMI">
            </div>
            <h4 class="text-white mb-2">Himpunan Pendidik & Pengajar Muda Indonesia</h4>
            <div class="indonesia-mini-divider mx-auto my-3"></div>
            <p class="text-white fst-italic">"Mendidik untuk Negeri, Berkarya untuk Bangsa"</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4 mb-4 mb-md-0" data-aos="fade-right" data-aos-delay="100">
                <div class="footer-card">
                    <div class="footer-card-header">
                        <i class="fas fa-map-marked-alt"></i>
                        <h5>Lokasi</h5>
                    </div>
                    <div class="footer-card-body">
                        <address class="mb-0">
                            Jl. Pendidikan No. 45<br>
                            Jakarta, Indonesia<br>
                            Kode Pos: 12345
                        </address>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="200">
                <div class="footer-card">
                    <div class="footer-card-header">
                        <i class="fas fa-phone-alt"></i>
                        <h5>Kontak</h5>
                    </div>
                    <div class="footer-card-body">
                        <p class="mb-2">
                            <a href="tel:+6285631540010" class="footer-link">
                                <i class="fas fa-phone-alt me-2"></i>0856-3154-010
                            </a>
                        </p>
                        <p class="mb-0">
                            <a href="mailto:info@hippmi.org" class="footer-link">
                                <i class="fas fa-envelope me-2"></i>info@hippmi.org
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-left" data-aos-delay="300">
                <div class="footer-card">
                    <div class="footer-card-header">
                        <i class="fas fa-share-alt"></i>
                        <h5>Media Sosial</h5>
                    </div>
                    <div class="footer-card-body">
                        <div class="social-icons">
                            <a href="#" class="social-icon" data-tooltip="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-icon" data-tooltip="Twitter"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-icon" data-tooltip="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-icon" data-tooltip="YouTube"><i class="fab fa-youtube"></i></a>
                            <a href="#" class="social-icon" data-tooltip="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-5" data-aos="fade-up" data-aos-delay="400">
            <div class="col-lg-8 mx-auto">
                <div class="footer-newsletter">
                    <h5>Dapatkan Informasi Terbaru</h5>
                    <p class="mb-3">Daftar untuk menerima update dan informasi terkini dari HIPPMI</p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Masukkan email Anda" required>
                            <button class="btn btn-newsletter" type="submit">Daftar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-copyright-dark">
        <div class="container">
            <div class="row align-items-center py-3">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0 text-white">&copy; 2025 Himpunan Pendidik & Pengajar Muda Indonesia</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="d-inline-block position-relative footer-flag-container">
                        <img src="https://flagcdn.com/w40/id.png" srcset="https://flagcdn.com/w80/id.png 2x" width="40" height="27" alt="Bendera Indonesia" class="footer-flag" loading="lazy" onerror="this.onerror=null;this.src='https://upload.wikimedia.org/wikipedia/commons/9/9f/Flag_of_Indonesia.svg';">
                        <span class="ms-2 footer-flag-text text-white">Bangga Menjadi Bagian dari Indonesia</span>
                        <div class="flag-glow"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
<style>
    .footer-modern {
        background: linear-gradient(135deg, #e30a17, #b8070f);
        color: white;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    .footer-animated-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
    }
    .shape {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
    }
    .shape-1 {
        width: 300px;
        height: 300px;
        top: -150px;
        right: -50px;
        animation: float 8s infinite ease-in-out;
    }
    .shape-2 {
        width: 200px;
        height: 200px;
        bottom: -100px;
        left: 10%;
        animation: float 12s infinite ease-in-out reverse;
    }
    .shape-3 {
        width: 100px;
        height: 100px;
        top: 30%;
        left: 20%;
        animation: float 10s infinite ease-in-out 1s;
    }
    .shape-4 {
        width: 80px;
        height: 80px;
        bottom: 20%;
        right: 10%;
        animation: float 7s infinite ease-in-out 0.5s;
    }
    @keyframes float {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        50% {
            transform: translateY(-20px) rotate(5deg);
        }
    }
    .footer-logo-container {
        display: inline-block;
        padding: 10px;
        background-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        transition: transform 0.3s, box-shadow 0.3s;
        position: relative;
        max-width: 180px;
        margin: 0 auto;
    }
    .footer-logo-container::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, #e30a17, #ffffff, #e30a17);
        z-index: -1;
        animation: glowing 2s linear infinite;
    }
    @keyframes glowing {
        0% { background-position: 0 0; }
        50% { background-position: 400% 0; }
        100% { background-position: 0 0; }
    }
    .footer-logo-container:hover {
        transform: scale(1.05);
        box-shadow: 0 0 30px rgba(255, 255, 255, 0.4);
    }
    .footer-logo {
        max-width: 100%;
        height: auto;
        display: block;
    }
    .indonesia-mini-divider {
        height: 4px;
        width: 80px;
        background: linear-gradient(90deg, #e30a17 50%, white 50%);
        border-radius: 2px;
    }
    .footer-card {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
    }
    .footer-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }
    .footer-card-header {
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
    }
    .footer-card-header i {
        font-size: 24px;
        margin-right: 10px;
        background: linear-gradient(to right, white, #ffcccc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .footer-card-header h5 {
        margin-bottom: 0;
        font-weight: 600;
    }
    .footer-card-body {
        padding: 15px;
    }
    .social-icons {
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    .social-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.1);
        color: white;
        transition: all 0.3s;
        position: relative;
        text-decoration: none;
    }
    .social-icon:hover {
        background-color: white;
        color: #e30a17;
        transform: translateY(-5px);
    }
    .social-icon::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: -40px;
        left: 50%;
        transform: translateX(-50%);
        background-color: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s;
        white-space: nowrap;
    }
    .social-icon:hover::after {
        opacity: 1;
    }
    .footer-link {
        color: white;
        text-decoration: none;
        transition: color 0.3s, transform 0.3s;
        display: inline-block;
    }
    .footer-link:hover {
        color: #ffcccc;
        transform: translateX(5px);
    }
    .footer-newsletter {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }
    .newsletter-form .form-control {
        border: none;
        background-color: rgba(255, 255, 255, 0.2);
        color: white;
        border-radius: 50px 0 0 50px;
        padding: 12px 20px;
    }
    .newsletter-form .form-control::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }
    .newsletter-form .form-control:focus {
        box-shadow: none;
        background-color: rgba(255, 255, 255, 0.3);
    }
    .btn-newsletter {
        background-color: white;
        color: #e30a17;
        border-radius: 0 50px 50px 0;
        padding: 0 25px;
        font-weight: 600;
        border: none;
        transition: all 0.3s;
    }
    .btn-newsletter:hover {
        background-color: #ffcccc;
        color: #b8070f;
    }
    .footer-copyright-dark {
        background-color: #000000;
        position: relative;
        overflow: hidden;
    }
    .footer-copyright-dark::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    }
    .footer-flag-container {
        display: inline-flex;
        align-items: center;
        padding: 5px 15px;
        border-radius: 50px;
        background-color: rgba(255, 255, 255, 0.1);
        transition: all 0.3s;
    }
    .footer-flag-container:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }
    .footer-flag {
        width: 40px;
        height: 27px;
        aspect-ratio: 3/2;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid rgba(255,255,255,.22);
        background: #fff;
        filter: drop-shadow(0 0 2px rgba(255, 255, 255, 0.5));
        animation: wave 3s ease-in-out infinite;
        flex-shrink: 0;
    }
    .footer-flag-container{gap:2px}
    @keyframes wave {
        0%, 100% {
            transform: rotate(0deg);
        }
        25% {
            transform: rotate(5deg);
        }
        75% {
            transform: rotate(-5deg);
        }
    }
    .flag-glow {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
        z-index: -1;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
            opacity: 0.5;
        }
        50% {
            transform: scale(1.05);
            opacity: 0.7;
        }
    }
    .footer-flag-text {
        font-weight: 500;
    }
    .footer-modern .row{justify-content:center}
    .footer-card{text-align:center}
    .footer-card-header{justify-content:center;text-align:center}
    .footer-card-body{text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center}
    .footer-card-body address{text-align:center}
    .footer-card-body p{text-align:center;width:100%;display:flex;justify-content:center}
    .social-icons{justify-content:center}
    .footer-newsletter{text-align:center}
    .footer-copyright-dark .row{justify-content:center;text-align:center}
    .footer-copyright-dark .row > div{text-align:center !important;justify-content:center;align-items:center;display:flex;flex-direction:column}
    .footer-flag-container{justify-content:center;margin:0 auto}
    @media(min-width:768px) and (max-width:991.98px){ .footer-modern [data-aos]{opacity:1!important;transform:none!important} .footer-modern [data-aos].aos-animate{opacity:1!important;transform:none!important} .footer-modern .social-icons{gap:6px;flex-wrap:wrap} .footer-modern .social-icon{width:32px;height:32px;font-size:.85rem} .footer-modern .footer-card-header{padding:12px} .footer-modern .footer-card-header i{font-size:18px} .footer-modern .footer-card-header h5{font-size:.95rem} }
    @media(min-width:992px) and (max-width:1366px){ .footer-modern [data-aos]{opacity:1!important;transform:none!important} .footer-modern [data-aos].aos-animate{opacity:1!important;transform:none!important} }
</style>
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        function footerAosFix(){ try{ if(window.innerWidth>=768&&window.innerWidth<=1366){ document.querySelectorAll('.footer-modern [data-aos]').forEach(function(el){ el.classList.add('aos-animate'); }); } if(window.AOS&&AOS.refreshHard) AOS.refreshHard(); else if(window.AOS&&AOS.refresh) AOS.refresh(); }catch(e){} }
        setTimeout(footerAosFix, 120);
        window.addEventListener('load', function(){ setTimeout(footerAosFix, 180); });
    });
</script>