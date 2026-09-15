<?php
require_once __DIR__ . '/config.php';

if (isAdminLoggedIn()) {
    redirectTo('dashboard.php');
}

$error = '';
$username = '';
$logoutSuccess = isset($_GET['logout']) && $_GET['logout'] === 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } else {
        $ok = false;
        $foundId = 0;
        $foundRole = 'admin';
        $foundUser = '';
        try {
            require_once __DIR__ . '/../koneksi.php';
            $pdo = getDBConnection();
            $pdo->exec("CREATE TABLE IF NOT EXISTS `admin_users` (`id` INT AUTO_INCREMENT PRIMARY KEY, `username` VARCHAR(80) NOT NULL, `password_hash` VARCHAR(255) NOT NULL, `role` VARCHAR(30) NOT NULL DEFAULT 'admin', `last_login_at` DATETIME NULL, `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, UNIQUE KEY `uniq_username` (`username`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            if ((int)$pdo->query("SELECT COUNT(*) FROM `admin_users`")->fetchColumn() === 0) {
                $hash = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
                $pdo->prepare("INSERT IGNORE INTO `admin_users` (`username`,`password_hash`,`role`) VALUES (:u,:p,'super_admin')")->execute(['u'=>ADMIN_USERNAME,'p'=>$hash]);
            }
            $stmt = $pdo->prepare("SELECT * FROM `admin_users` WHERE LOWER(`username`)=LOWER(:u) LIMIT 1");
            $stmt->execute(['u'=>$username]);
            $row = $stmt->fetch();
            if ($row && password_verify($password, $row['password_hash'])) {
                $ok = true;
                $foundId = (int)$row['id'];
                $foundRole = (string)($row['role'] ?? 'admin');
                $foundUser = (string)$row['username'];
                $newHash = null;
                if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) $newHash = password_hash($password, PASSWORD_DEFAULT);
                if ($newHash !== null) {
                    $pdo->prepare("UPDATE `admin_users` SET `password_hash`=:p WHERE `id`=:id")->execute(['p'=>$newHash,'id'=>$foundId]);
                }
                $pdo->prepare("UPDATE `admin_users` SET `last_login_at`=NOW() WHERE `id`=:id")->execute(['id'=>$foundId]);
            } elseif (hash_equals(strtolower(ADMIN_USERNAME), strtolower($username)) && hash_equals(ADMIN_PASSWORD, $password)) {
                $ok = true;
            }
        } catch (Throwable $e) {
            $ok = hash_equals(strtolower(ADMIN_USERNAME), strtolower($username)) && hash_equals(ADMIN_PASSWORD, $password);
        }

        if ($ok) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $foundUser !== '' ? $foundUser : ADMIN_USERNAME;
            $_SESSION['admin_id'] = $foundId > 0 ? $foundId : 0;
            $_SESSION['admin_role'] = $foundRole;
            $_SESSION['admin_login_time'] = time();
            $_SESSION['admin_last_login_db'] = date('Y-m-d H:i:s');
            session_write_close();
            redirectTo('dashboard.php');
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin HIPPMI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --primary:#e30a17; --primary-dark:#a90610; --primary-soft:#fff0f1; --dark:#202026; --text:#44444c; --muted:#7c7c85; --border:#e3e3e8; --white:#fff; }
        *{box-sizing:border-box}
        body{min-height:100vh;margin:0;font-family:Poppins,sans-serif;background:radial-gradient(circle at 15% 20%,rgba(255,255,255,.18),transparent 25%),radial-gradient(circle at 85% 80%,rgba(255,255,255,.12),transparent 30%),linear-gradient(135deg,var(--primary),var(--primary-dark))}
        button,input{font:inherit}
        .login-wrapper{min-height:100vh;padding:30px 20px;display:flex;flex-direction:column;justify-content:center;align-items:center}
        .login-card{width:100%;max-width:430px;padding:38px;background:var(--white);border-radius:24px;box-shadow:0 30px 80px rgba(65,0,7,.3)}
        .login-brand{margin-bottom:27px;text-align:center}
        .logo-wrapper{width:110px;height:85px;margin:0 auto 18px;padding:8px;display:flex;justify-content:center;align-items:center;background:#fff;border:1px solid var(--border);border-radius:17px;box-shadow:0 10px 25px rgba(0,0,0,.07)}
        .logo-wrapper img{display:block;max-width:95px;max-height:68px;object-fit:contain}
        .login-brand h1{margin:0 0 7px;color:var(--dark);font-size:27px;font-weight:700}
        .login-brand p{margin:0;color:var(--muted);font-size:13px;line-height:1.7}
        .alert{margin-bottom:20px;padding:13px 15px;display:flex;align-items:flex-start;gap:10px;border-radius:11px;font-size:12px;line-height:1.6}
        .alert i{margin-top:3px}
        .alert-danger{color:#a00610;background:#fff0f1;border:1px solid #ffc6ca}
        .alert-success{color:#147245;background:#ecfff5;border:1px solid #b8efd1}
        .form-group{margin-bottom:19px}
        .form-group label{display:block;margin-bottom:8px;color:var(--text);font-size:13px;font-weight:600}
        .input-wrapper{position:relative}
        .input-wrapper input{width:100%;height:52px;padding:0 48px;color:var(--text);background:#fff;border:1px solid var(--border);border-radius:12px;outline:none;transition:border-color .25s,box-shadow .25s}
        .input-wrapper input::placeholder{color:#a0a0a8}
        .input-wrapper input:focus{border-color:var(--primary);box-shadow:0 0 0 4px rgba(227,10,23,.1)}
        .input-icon{position:absolute;top:50%;left:17px;color:var(--primary);transform:translateY(-50%);pointer-events:none}
        .password-toggle{position:absolute;top:50%;right:9px;width:37px;height:37px;color:#777780;background:transparent;border:0;border-radius:8px;cursor:pointer;transform:translateY(-50%)}
        .password-toggle:hover{color:var(--primary);background:var(--primary-soft)}
        .login-button{width:100%;min-height:52px;margin-top:5px;padding:12px 20px;display:flex;justify-content:center;align-items:center;gap:10px;color:#fff;background:linear-gradient(135deg,var(--primary),var(--primary-dark));border:0;border-radius:12px;box-shadow:0 12px 25px rgba(227,10,23,.25);font-weight:600;cursor:pointer;transition:transform .25s,box-shadow .25s}
        .login-button:hover{transform:translateY(-2px);box-shadow:0 17px 32px rgba(227,10,23,.32)}
        .login-button:active{transform:translateY(0)}
        .login-footer{margin-top:25px;text-align:center}
        .login-footer a{display:inline-flex;align-items:center;gap:8px;color:var(--muted);font-size:12px;text-decoration:none}
        .login-footer a:hover{color:var(--primary)}
        .copyright{margin:20px 0 0;color:rgba(255,255,255,.82);font-size:11px;text-align:center}
        @media(max-width:480px){ .login-card{padding:29px 20px;border-radius:19px} .login-brand h1{font-size:23px} }
    </style>
</head>
<body>
    <main class="login-wrapper">
        <section class="login-card">
            <div class="login-brand">
                <div class="logo-wrapper"><img src="../img/Logo.webp" alt="Logo HIPPMI"></div>
                <h1>Admin HIPPMI</h1>
                <p>Silakan masuk untuk mengelola website HIPPMI.</p>
            </div>
            <?php if ($logoutSuccess): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i><span>Anda berhasil keluar dari dashboard.</span></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i><span><?= escape($error); ?></span></div>
            <?php endif; ?>
            <form method="post" action="" autocomplete="off">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" id="username" name="username" value="<?= escape($username); ?>" placeholder="Masukkan username" autocomplete="username" required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan password"><i class="fa-solid fa-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="login-button"><span>Masuk ke Dashboard</span><i class="fa-solid fa-arrow-right-to-bracket"></i></button>
            </form>
            <div class="login-footer"><a href="../beranda.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke website</a></div>
        </section>
        <p class="copyright">&copy; <?= date('Y'); ?> HIPPMI. Seluruh hak dilindungi.</p>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pi=document.getElementById('password'), t=document.getElementById('togglePassword');
            if(!pi||!t) return;
            t.addEventListener('click', function(){
                const h=pi.type==='password';
                pi.type=h?'text':'password';
                const ic=this.querySelector('i');
                if(ic){ ic.classList.toggle('fa-eye',!h); ic.classList.toggle('fa-eye-slash',h); }
                this.setAttribute('aria-label',h?'Sembunyikan password':'Tampilkan password');
            });
        });
    </script>
</body>
</html>
