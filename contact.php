<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Yanıt metin değil kod döner; istemci kodu sayfanın dilinde (EN/TR) gösterir.
function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'code' => 'method']);
}

$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    // Oturum süresi dolmuş olabilir: yeni token verilir, istemci yazılanı kaybetmeden bir kez tekrar dener.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    respond(403, ['ok' => false, 'code' => 'session', 'token' => $_SESSION['csrf_token']]);
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$topic   = trim($_POST['topic'] ?? 'Merhaba WitVortex!');

$fields = [];
if ($name === '') {
    $fields['name'] = 'required';
}
if ($email === '') {
    $fields['email'] = 'required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $fields['email'] = 'email';
}
if ($message === '') {
    $fields['message'] = 'required';
}
if ($fields) {
    respond(422, ['ok' => false, 'code' => 'invalid', 'fields' => $fields]);
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

respond(200, ['ok' => true]);
