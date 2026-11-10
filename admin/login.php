<?php
declare(strict_types=1);

require __DIR__ . '/../app/layout.php';

if (current_admin()) {
    header('Location: /admin/');
    exit;
}

$error = null;
$notice = isset($_GET['expired']) ? 'Oturumun süresi doldu, tekrar giriş yap.' : null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(post_str('email'));

    if (!csrf_valid()) {
        // Sayfa uzun süre açık kaldıysa oturum silinmiş olabilir; deneme sayılmaz, form yeni token'la döner.
        $error = 'Sayfanın süresi doldu, tekrar dene.';
    } else {
        $result = attempt_login($email, post_str('password'));

        if ($result === 'ok') {
            header('Location: /admin/');
            exit;
        }

        http_response_code($result === 'locked' ? 429 : 401);
        $error = $result === 'locked'
            ? 'Çok fazla hatalı deneme. 15 dakika sonra tekrar dene.'
            : 'E-posta veya şifre hatalı.';
    }
}

$errorAttrs = $error ? ' aria-invalid="true" aria-describedby="login-error"' : '';

admin_page_start('Giriş');
?>
  <section class="auth">
    <img class="auth-logo" src="<?= e(asset_url('assets/img/darklogo.png')) ?>" alt="WitVortex" width="97" height="14">
    <h1 class="auth-title">Panele giriş</h1>

    <?php if ($error): ?>
      <p class="alert" id="login-error" role="alert"><?= e($error) ?></p>
    <?php elseif ($notice): ?>
      <p class="notice" role="status"><?= e($notice) ?></p>
    <?php endif; ?>

    <form class="form" method="post" action="/admin/login.php">
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

      <div class="field">
        <label for="email">E-posta</label>
        <input id="email" name="email" type="email" autocomplete="username" required value="<?= e($email) ?>"<?= $errorAttrs ?><?= $email === '' ? ' autofocus' : '' ?>>
      </div>

      <div class="field">
        <label for="password">Şifre</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required<?= $errorAttrs ?><?= $email !== '' ? ' autofocus' : '' ?>>
      </div>

      <button class="btn btn-primary btn-block" type="submit">Giriş yap</button>
    </form>
  </section>
<?php
admin_page_end();
