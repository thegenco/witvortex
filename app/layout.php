<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/auth.php';

/** Panel sayfasının başı: güvenlik başlıkları, <head>, girişliyse üst bar. */
function admin_page_start(string $title, ?array $user = null): void
{
    admin_headers();
    ?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · WitVortex Panel</title>
<link rel="icon" href="data:,">
<link rel="stylesheet" href="<?= e(asset_url('admin/admin.css')) ?>">
<script src="<?= e(asset_url('admin/admin.js')) ?>" defer></script>
</head>
<body>
<?php if ($user): ?>
<header class="topbar">
  <a class="topbar-brand" href="/admin/" aria-label="Panel ana sayfası">
    <img src="<?= e(asset_url('assets/img/darklogo.png')) ?>" alt="WitVortex" width="97" height="14">
    <span class="topbar-badge">Panel</span>
  </a>
  <div class="topbar-user">
    <span class="topbar-name"><?= e($user['name'] !== '' ? $user['name'] : $user['email']) ?></span>
    <form method="post" action="/admin/logout.php">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <button class="btn btn-ghost btn-sm" type="submit">Çıkış</button>
    </form>
  </div>
</header>
<?php endif; ?>
<main class="page<?= $user ? '' : ' page-auth' ?>">
<?php
}

function admin_page_end(): void
{
    echo "</main>\n</body>\n</html>\n";
}

/** 404 sayfası (ör. silinmiş veya hiç olmayan mesaj). */
function admin_not_found(array $user): never
{
    http_response_code(404);
    admin_page_start('Bulunamadı', $user);
    ?>
  <div class="empty">
    <h1 class="empty-title">Bu mesaj bulunamadı.</h1>
    <p class="muted">Silinmiş olabilir.</p>
    <a class="btn btn-ghost" href="/admin/">Mesajlara dön</a>
  </div>
<?php
    admin_page_end();
    exit;
}
