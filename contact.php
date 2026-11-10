<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/app/bootstrap.php';

// Yanıt metin değil kod döner; istemci kodu sayfanın dilinde (EN/TR) gösterir.
function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'code' => 'method']);
}

$token = post_str('csrf_token');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    // Oturum süresi dolmuş olabilir: yeni token verilir, istemci yazılanı kaybetmeden bir kez tekrar dener.
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    respond(403, ['ok' => false, 'code' => 'session', 'token' => $_SESSION['csrf_token']]);
}

// Bot tuzağı: insanlar görmediği bu alanı boş bırakır; doluysa kaydetmeden "gönderildi" denir.
if (post_str('website') !== '') {
    respond(200, ['ok' => true]);
}

$name    = trim(post_str('name'));
$email   = trim(post_str('email'));
$phone   = trim(post_str('phone'));
// Tarayıcılar satır sonunu \r\n gönderir; 5000 karakter sınırı textarea ile aynı saysın.
$message = str_replace(["\r\n", "\r"], "\n", trim(post_str('message')));
$topic   = trim(post_str('topic'));

if (!is_utf8($name . $email . $phone . $message . $topic)) {
    respond(400, ['ok' => false, 'code' => 'server']);
}

$fields = [];
if ($name === '') {
    $fields['name'] = 'required';
} elseif (str_length($name) > 200) {
    $fields['name'] = 'too_long';
}
if ($email === '') {
    $fields['email'] = 'required';
} elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $fields['email'] = 'email';
}
if ($message === '') {
    $fields['message'] = 'required';
} elseif (str_length($message) > 5000) {
    $fields['message'] = 'too_long';
}
if ($fields) {
    respond(422, ['ok' => false, 'code' => 'invalid', 'fields' => $fields]);
}

$allowedTopics = ['Merhaba WitVortex!', 'Ürün Fikri', 'İş Birliği', 'Yatırım', 'Kariyer'];
if (!in_array($topic, $allowedTopics, true)) {
    $topic = 'Merhaba WitVortex!';
}

// Aynı bağlantıdan saatte en fazla 5 mesaj: formu otomatik dolduran botlar paneli dolduramaz.
const MESSAGES_PER_HOUR = 5;

// Mesaj panelde okunmak üzere veritabanına yazılır; kayıt olmadan "gönderildi" denmez.
try {
    $ip = client_key();
    $recent = db()->prepare('SELECT COUNT(*) FROM messages WHERE ip = ? AND created_at > NOW() - INTERVAL 1 HOUR');
    $recent->execute([$ip]);
    if ((int) $recent->fetchColumn() >= MESSAGES_PER_HOUR) {
        respond(429, ['ok' => false, 'code' => 'rate']);
    }

    db()->prepare('INSERT INTO messages (name, email, phone, topic, body, ip) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([$name, $email, str_truncate($phone, 50), $topic, $message, $ip]);
} catch (Throwable $e) {
    error_log('[WitVortex contact] ' . $e->getMessage());
    respond(500, ['ok' => false, 'code' => 'server']);
}

respond(200, ['ok' => true]);
