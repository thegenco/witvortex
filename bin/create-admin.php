<?php
declare(strict_types=1);

/*
 * Panel admini oluşturur ya da var olanın şifresini değiştirir. Yalnızca terminalden çalışır.
 *   php bin/create-admin.php                  (sorarak)
 *   php bin/create-admin.php ad@ornek.com "Ad Soyad"
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../app/bootstrap.php';

function ask(string $label): string
{
    fwrite(STDOUT, $label);

    return trim((string) fgets(STDIN));
}

function ask_hidden(string $label): string
{
    fwrite(STDOUT, $label);
    $tty = stream_isatty(STDIN);
    if ($tty) {
        shell_exec('stty -echo');
    }
    $value = rtrim((string) fgets(STDIN), "\r\n");
    if ($tty) {
        shell_exec('stty echo');
        fwrite(STDOUT, PHP_EOL);
    }

    return $value;
}

function fail(string $message): never
{
    fwrite(STDERR, "✗ {$message}" . PHP_EOL);
    exit(1);
}

$email = strtolower(trim($argv[1] ?? ask('E-posta: ')));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Geçerli bir e-posta gir.');
}

$name = trim($argv[2] ?? ask('Ad (boş bırakılabilir): '));
if (!is_utf8($name)) {
    fail('Ad geçerli UTF-8 değil.');
}
$name = str_truncate($name, 100);

$password = ask_hidden('Şifre (en az 12 karakter): ');
if (str_length($password) < 12) {
    fail('Şifre en az 12 karakter olmalı.');
}
if (ask_hidden('Şifre (tekrar): ') !== $password) {
    fail('Şifreler aynı değil.');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$db = db();

$stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
$existingId = $stmt->fetchColumn();

if ($existingId) {
    $db->prepare('UPDATE users SET name = ?, password_hash = ? WHERE id = ?')->execute([$name, $hash, $existingId]);
    // Eski şifreyle bırakılmış kilitler temizlenir; açık oturumlar ve cihaz çerezleri şifre değişince geçersizleşir.
    $db->prepare('DELETE FROM login_attempts WHERE email = ?')->execute([$email]);
    fwrite(STDOUT, "✓ Şifre güncellendi: {$email}" . PHP_EOL);
} else {
    $db->prepare('INSERT INTO users (email, name, password_hash) VALUES (?, ?, ?)')->execute([$email, $name, $hash]);
    fwrite(STDOUT, "✓ Admin oluşturuldu: {$email}" . PHP_EOL);
}
