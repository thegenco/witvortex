<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

function respond(bool $ok, string $message): void
{
    echo json_encode(['ok' => $ok, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    respond(false, 'Geçersiz istek yöntemi.');
}

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    respond(false, 'Oturum doğrulanamadı, sayfayı yenileyip tekrar deneyin.');
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$topic   = trim($_POST['topic'] ?? 'Merhaba WitVortex!');

if ($name === '' || $email === '' || $message === '') {
    http_response_code(422);
    respond(false, 'Lütfen tüm alanları doldurun.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    respond(false, 'Geçerli bir e-posta adresi girin.');
}

$allowedTopics = ['Merhaba WitVortex!', 'Ürün Fikri', 'İş Birliği', 'Yatırım', 'Kariyer'];
if (!in_array($topic, $allowedTopics, true)) {
    $topic = 'Merhaba WitVortex!';
}

$to      = 'merhaba@witvortex.com';
$subject = '[WitVortex] ' . $topic . ' — ' . $name;
$body    = "Konu: {$topic}\nAd Soyad: {$name}\nE-posta: {$email}\nTelefon: {$phone}\n\nMesaj:\n{$message}\n";
$headers = 'From: no-reply@witvortex.com' . "\r\n" . 'Reply-To: ' . $email;

$sent = @mail($to, $subject, $body, $headers);

if (!$sent) {
    error_log('[WitVortex contact] ' . $subject . "\n" . $body);
}

respond(true, 'Gönderildi ✓');
