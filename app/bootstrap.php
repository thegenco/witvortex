<?php
declare(strict_types=1);

// app/ altındaki dosyalar yalnızca include edilir; tarayıcıdan doğrudan istenirse 404.
if (PHP_SAPI !== 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

date_default_timezone_set('Europe/Istanbul');

define('WEB_ROOT', dirname(__DIR__));

// cloudflare.com/ips-v4 ve /ips-v6 (Kasım 2026). Değişirse buraya eklenir.
const CLOUDFLARE_RANGES = [
    '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18',
    '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17',
    '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
    '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32',
    '2a06:98c0::/29', '2c0f:f248::/32',
];

/**
 * Veritabanı bilgileri repoda değil, web kökünün dışındaki witvortex-config.php'de durur
 * (repo herkese açık). Sırayla: WITVORTEX_CONFIG ortam değişkeni, web kökünün bir üstü,
 * sunucudaki sabit yol.
 */
function config(): array
{
    static $config = null;

    if ($config === null) {
        $candidates = array_filter([
            getenv('WITVORTEX_CONFIG') ?: null,
            dirname(__DIR__, 2) . '/witvortex-config.php',
            '/var/www/witvortex-config.php',
        ]);

        foreach ($candidates as $path) {
            if (is_file($path)) {
                $config = require $path;
                break;
            }
        }

        if (!is_array($config)) {
            throw new RuntimeException('witvortex-config.php bulunamadı veya geçersiz.');
        }
    }

    return $config;
}

/** Tek, tembel PDO bağlantısı. Oturum saat dilimi UTC: tüm DATETIME değerleri UTC saklanır. */
function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $c = config()['db'];
        $dsn = isset($c['socket'])
            ? "mysql:unix_socket={$c['socket']};dbname={$c['name']};charset=utf8mb4"
            : 'mysql:host=' . ($c['host'] ?? 'localhost') . ';port=' . ($c['port'] ?? 3306) . ";dbname={$c['name']};charset=utf8mb4";

        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec("SET time_zone = '+00:00'");
    }

    return $pdo;
}

function is_local_request(): bool
{
    return in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true);
}

/** Geçerli UTF-8 mi? Tarayıcılar her zaman geçerli gönderir; bozuk baytlar yalnızca botlardan gelir. */
function is_utf8(string $value): bool
{
    return preg_match('//u', $value) === 1;
}

/** Karakter sayısı (mbstring gerekmez). Girdi geçerli UTF-8 olmalı. */
function str_length(string $value): int
{
    return (int) preg_match_all('/./su', $value);
}

/** İlk $max karakter (mbstring gerekmez). Girdi geçerli UTF-8 olmalı. */
function str_truncate(string $value, int $max): string
{
    return preg_match('/^.{0,' . $max . '}/su', $value, $match) ? $match[0] : '';
}

/** POST alanı; dizi gibi beklenmeyen tipler boş metin sayılır. */
function post_str(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? $value : '';
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Dosya değişince URL de değişir; Cloudflare/tarayıcı önbelleği eski sürümü göstermez. */
function asset_url(string $path): string
{
    return '/' . $path . '?v=' . filemtime(WEB_ROOT . '/' . $path);
}

function ip_in_range(string $ip, string $cidr): bool
{
    [$subnet, $bits] = explode('/', $cidr);
    $ipBin = @inet_pton($ip);
    $subnetBin = @inet_pton($subnet);

    if ($ipBin === false || $subnetBin === false || strlen($ipBin) !== strlen($subnetBin)) {
        return false;
    }

    $bytes = intdiv((int) $bits, 8);
    $rest = (int) $bits % 8;
    if (strncmp($ipBin, $subnetBin, $bytes) !== 0) {
        return false;
    }
    if ($rest === 0) {
        return true;
    }
    $mask = chr((0xff << (8 - $rest)) & 0xff);

    return ($ipBin[$bytes] & $mask) === ($subnetBin[$bytes] & $mask);
}

/**
 * Ziyaretçinin IP'si. CF-Connecting-IP başlığına yalnızca istek gerçekten bir Cloudflare
 * sunucusundan geldiyse güvenilir; sunucunun IP'sine doğrudan gelen biri başlığı uyduramaz.
 */
function client_ip(): string
{
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $forwarded = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
    $ip = $remote;

    if (is_string($forwarded) && filter_var($forwarded, FILTER_VALIDATE_IP)) {
        foreach (CLOUDFLARE_RANGES as $range) {
            if (ip_in_range($remote, $range)) {
                $ip = $forwarded;
                break;
            }
        }
    }

    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/** Sınırlama anahtarı: IPv6'da tek kişi genelde bütün bir /64 bloğunu kullanır. */
function client_key(): string
{
    $ip = client_ip();
    $bin = inet_pton($ip);

    if ($bin !== false && strlen($bin) === 16) {
        return inet_ntop(substr($bin, 0, 8) . str_repeat("\0", 8)) . '/64';
    }

    return $ip;
}

/** Veritabanındaki UTC zamanı İstanbul saatiyle gösterir. */
function local_time(string $utc, string $format = 'd.m.Y H:i'): string
{
    return (new DateTimeImmutable($utc, new DateTimeZone('UTC')))
        ->setTimezone(new DateTimeZone('Europe/Istanbul'))
        ->format($format);
}
