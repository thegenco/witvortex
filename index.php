<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Dosya değişince URL de değişir; Cloudflare/tarayıcı önbelleği eski sürümü göstermez.
function asset(string $path): string
{
    return $path . '?v=' . filemtime(__DIR__ . '/' . $path);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title data-tr="WitVortex — Dijital Ürün Stüdyosu">WitVortex — Digital Product Studio</title>
<meta name="description" data-tr="WitVortex; ürün, deneyim ve teknolojiyi tek çatı altında birleştiren bağımsız bir dijital ürün stüdyosu." content="WitVortex is an independent digital product studio bringing product, experience, and technology together under one roof.">
<link rel="icon" href="data:,">
<script>document.documentElement.classList.add('js');</script>
<link rel="stylesheet" href="<?php echo asset('assets/css/style.css'); ?>">
</head>
<body>

<?php include 'includes/header.php'; ?>
<main id="main">
<?php include 'includes/hero.php'; ?>
<?php include 'includes/manifesto.php'; ?>
<?php include 'includes/what-we-do.php'; ?>
<?php include 'includes/contact.php'; ?>
</main>
<?php include 'includes/footer.php'; ?>

<script src="<?php echo asset('assets/js/main.js'); ?>"></script>
</body>
</html>
