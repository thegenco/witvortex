<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
<link rel="stylesheet" href="assets/css/style.css">
<!-- deploy-check: 2026-11-09 -->
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/hero.php'; ?>
<?php include 'includes/manifesto.php'; ?>
<?php include 'includes/what-we-do.php'; ?>
<?php include 'includes/contact.php'; ?>
<?php include 'includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>
