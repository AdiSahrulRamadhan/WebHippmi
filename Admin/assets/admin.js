document.addEventListener('DOMContentLoaded', function () {
    /*
    |--------------------------------------------------------------------------
    | Tampilkan dan sembunyikan password
    |--------------------------------------------------------------------------
    */
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    if (passwordInput && togglePassword) {
        togglePassword.addEventListener('click', function () {
            const icon = this.querySelector('i');
            const passwordIsHidden =
                passwordInput.type === 'password';

            passwordInput.type = passwordIsHidden
                ? 'text'
                : 'password';

            if (icon) {
                icon.classList.toggle(
                    'fa-eye',
                    !passwordIsHidden
                );

                icon.classList.toggle(
                    'fa-eye-slash',
                    passwordIsHidden
                );
            }

            this.setAttribute(
                'aria-label',
                passwordIsHidden
                    ? 'Sembunyikan password'
                    : 'Tampilkan password'
            );
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Sidebar mobile
    |--------------------------------------------------------------------------
    */
    const sidebar = document.getElementById('adminSidebar');
    const menuToggle = document.getElementById('menuToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById(
        'sidebarOverlay'
    );

    function openSidebar() {
        if (!sidebar || !sidebarOverlay) {
            return;
        }

        sidebar.classList.add('show');
        sidebarOverlay.classList.add('show');
        document.body.classList.add('sidebar-open');
    }

    function closeSidebar() {
        if (!sidebar || !sidebarOverlay) {
            return;
        }

        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    }

    if (menuToggle) {
        menuToggle.addEventListener('click', openSidebar);
    }

    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }

    window.addEventListener('resize', function () {
        if (window.innerWidth > 991) {
            closeSidebar();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Modal Konfirmasi Logout
    |--------------------------------------------------------------------------
    */
    const logoutForm = document.getElementById('logoutForm');
    const logoutModal = document.getElementById('logoutModal');
    const openLogoutModal = document.getElementById(
        'openLogoutModal'
    );

    const closeLogoutModal = document.getElementById(
        'closeLogoutModal'
    );

    const cancelLogout = document.getElementById(
        'cancelLogout'
    );

    const confirmLogout = document.getElementById(
        'confirmLogout'
    );

    const logoutModalOverlay = document.getElementById(
        'logoutModalOverlay'
    );

    function showLogoutModal() {
        if (!logoutModal) {
            return;
        }

        logoutModal.classList.add('show');
        logoutModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');

        setTimeout(function () {
            if (confirmLogout) {
                confirmLogout.focus();
            }
        }, 100);
    }

    function hideLogoutModal() {
        if (!logoutModal) {
            return;
        }

        logoutModal.classList.remove('show');
        logoutModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');

        if (openLogoutModal) {
            openLogoutModal.focus();
        }
    }

    if (openLogoutModal) {
        openLogoutModal.addEventListener('click', function () {
            showLogoutModal();
        });
    }

    if (closeLogoutModal) {
        closeLogoutModal.addEventListener('click', function () {
            hideLogoutModal();
        });
    }

    if (cancelLogout) {
        cancelLogout.addEventListener('click', function () {
            hideLogoutModal();
        });
    }

    if (logoutModalOverlay) {
        logoutModalOverlay.addEventListener(
            'click',
            function () {
                hideLogoutModal();
            }
        );
    }
    if (confirmLogout) {
        confirmLogout.addEventListener('click', function () {
            this.disabled = true;

            this.innerHTML = `
                <i class="fa-solid fa-spinner fa-spin"></i>
                Sedang keluar...
            `;
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Tombol Escape
    |--------------------------------------------------------------------------
    */
    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape'
            && logoutModal
            && logoutModal.classList.contains('show')
        ) {
            hideLogoutModal();
            return;
        }

        if (event.key === 'Escape') {
            closeSidebar();
        }
    });
});