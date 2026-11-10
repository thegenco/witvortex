<?php
declare(strict_types=1);

require __DIR__ . '/../app/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit;
}

// Oturum zaten bitmişse (süre doldu, başka sekmede çıkış yapıldı) hata değil, giriş sayfası.
admin_session_start();
if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

verify_csrf();
admin_logout();

header('Location: /admin/login.php');
exit;
