<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/bootstrap.php';

// Ubuntu'nun oturum temizleyicisi boşta kalan oturum dosyalarını ~24 dk sonra siler;
// süre bunun altında tutulur ki davranış öngörülebilir olsun.
const ADMIN_IDLE_TIMEOUT = 20 * 60;
const ADMIN_ABSOLUTE_TIMEOUT = 12 * 60 * 60; // her durumda 12 saatte bir yeniden giriş
const LOGIN_WINDOW_MINUTES = 15;
const LOGIN_MAX_PER_EMAIL = 5;
const LOGIN_MAX_PER_IP = 20;

// Bu tarayıcıda daha önce başarıyla giriş yapıldığını kanıtlayan çerez. Saldırgan admin
// e-postasını hatalı denemelerle kilitlese bile, bu çereze sahip tarayıcı giriş yapabilir.
const DEVICE_COOKIE = 'wv_device';
const DEVICE_COOKIE_DAYS = 180;

// Kullanıcı yokken de aynı sürede bir hash doğrulanır; yanıt süresinden hesabın varlığı anlaşılmaz.
const DUMMY_PASSWORD_HASH = '$2y$12$U8MTVQmPjfqE8lNGQP23x.NxJSDeq8KNKBdADPysXbVGgHFkcUD8W';

/** Panel oturumu: sitenin oturumundan ayrı çerez, yalnızca /admin/ yolunda, JS'ten okunamaz. */
function admin_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('wv_admin');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/admin/',
        'secure' => !is_local_request(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
}

function csrf_token(): string
{
    admin_session_start();

    return $_SESSION['csrf'];
}

function csrf_valid(): bool
{
    return hash_equals(csrf_token(), post_str('csrf'));
}

/**
 * Durum değiştiren her POST'ta çağrılır. Oturum dolmuşsa girişe yönlendirir;
 * oturum varken token tutmuyorsa (ör. eski sekme) anlaşılır bir sayfa gösterir.
 */
function verify_csrf(): void
{
    if (csrf_valid()) {
        return;
    }

    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/login.php?expired=1');
        exit;
    }

    require_once __DIR__ . '/layout.php';
    http_response_code(403);
    admin_page_start('İşlem yapılmadı', current_admin());
    ?>
  <div class="empty">
    <h1 class="empty-title">İşlem yapılmadı.</h1>
    <p class="muted">Sayfa eskimiş olabilir. Panele dönüp tekrar dene.</p>
    <a class="btn btn-primary" href="/admin/">Panele dön</a>
  </div>
<?php
    admin_page_end();
    exit;
}

/** Panel sayfalarının güvenlik başlıkları: çerçeveye alınamaz, önbelleğe ve arama motorlarına girmez. */
function admin_headers(): void
{
    header('X-Frame-Options: DENY');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store');
    header('X-Robots-Tag: noindex, nofollow');

    if (!is_local_request()) {
        // Tarayıcı bu alan adına bir daha asla düz HTTP ile gitmez (şifre formu araya girilerek çalınamaz).
        header('Strict-Transport-Security: max-age=15552000');
    }
}

function admin_logout(): void
{
    admin_session_start();
    $_SESSION = [];
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $params['path'],
        'secure' => $params['secure'],
        'httponly' => true,
        'samesite' => $params['samesite'],
    ]);
    session_destroy();
}

/** Şifre hash'inden türetilir; şifre değişince eski oturumlar ve cihaz çerezleri geçersizleşir. */
function password_fingerprint(string $passwordHash): string
{
    return hash('sha256', $passwordHash);
}

function device_token(int $userId, string $passwordHash): string
{
    return hash_hmac('sha256', 'device|' . $userId, $passwordHash);
}

/** Oturumdaki admin; süre dolmuşsa, şifre değişmişse veya kullanıcı silinmişse null. */
function current_admin(): ?array
{
    admin_session_start();
    $id = $_SESSION['admin_id'] ?? null;

    if (!$id) {
        return null;
    }

    $now = time();
    if ($now - ($_SESSION['last_seen'] ?? 0) > ADMIN_IDLE_TIMEOUT
        || $now - ($_SESSION['login_at'] ?? 0) > ADMIN_ABSOLUTE_TIMEOUT) {
        admin_logout();

        return null;
    }

    $stmt = db()->prepare('SELECT id, email, name, password_hash FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user || !hash_equals($_SESSION['pwv'] ?? '', password_fingerprint($user['password_hash']))) {
        admin_logout();

        return null;
    }

    $_SESSION['last_seen'] = $now;
    unset($user['password_hash']);

    return $user;
}

function require_admin(): array
{
    $user = current_admin();

    if (!$user) {
        // Oturumsuz bir işlem (POST) neredeyse her zaman süresi dolmuş bir oturumdur; bunu söyle.
        header('Location: /admin/login.php' . ($_SERVER['REQUEST_METHOD'] === 'POST' ? '?expired=1' : ''));
        exit;
    }

    return $user;
}

/**
 * Son 15 dakikada bu e-postaya 5 veya bu IP'den 20 hatalı deneme varsa giriş kilitli.
 * Tanınmış cihaz (DEVICE_COOKIE) e-posta kilidinden muaftır, IP kilidinden değil.
 */
function login_locked(string $emailKey, string $ipKey, bool $trustedDevice): bool
{
    $db = db();
    $db->exec('DELETE FROM login_attempts WHERE attempted_at < NOW() - INTERVAL 1 DAY');

    $stmt = $db->prepare(
        'SELECT COALESCE(SUM(email = ?), 0) AS by_email, COALESCE(SUM(ip = ?), 0) AS by_ip
           FROM login_attempts
          WHERE (email = ? OR ip = ?)
            AND attempted_at > NOW() - INTERVAL ' . LOGIN_WINDOW_MINUTES . ' MINUTE'
    );
    $stmt->execute([$emailKey, $ipKey, $emailKey, $ipKey]);
    $row = $stmt->fetch();

    return (!$trustedDevice && (int) $row['by_email'] >= LOGIN_MAX_PER_EMAIL)
        || (int) $row['by_ip'] >= LOGIN_MAX_PER_IP;
}

/** @return 'ok'|'invalid'|'locked' */
function attempt_login(string $email, string $password): string
{
    $email = strtolower(trim($email));
    // Sütuna sığmayan veya bozuk baytlı girdiler (yalnızca botlar) özetiyle kaydedilir.
    $emailKey = strlen($email) <= 254 && is_utf8($email) ? $email : hash('sha256', $email);
    $ipKey = client_key();

    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $stmt->execute([$emailKey]);
    $user = $stmt->fetch() ?: null;

    $deviceCookie = $_COOKIE[DEVICE_COOKIE] ?? '';
    $trustedDevice = $user !== null
        && is_string($deviceCookie)
        && hash_equals(device_token((int) $user['id'], $user['password_hash']), $deviceCookie);

    if (login_locked($emailKey, $ipKey, $trustedDevice)) {
        return 'locked';
    }

    $valid = password_verify($password, $user['password_hash'] ?? DUMMY_PASSWORD_HASH) && $user !== null;

    if (!$valid) {
        db()->prepare('INSERT INTO login_attempts (ip, email) VALUES (?, ?)')->execute([$ipKey, $emailKey]);

        return 'invalid';
    }

    $storedHash = $user['password_hash'];
    if (password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
        $storedHash = password_hash($password, PASSWORD_DEFAULT);
        db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$storedHash, $user['id']]);
    }

    db()->prepare('DELETE FROM login_attempts WHERE email = ?')->execute([$emailKey]);
    db()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);

    // Oturum sabitleme saldırısına karşı girişte yeni oturum kimliği.
    admin_session_start();
    session_regenerate_id(true);
    $_SESSION = [
        'admin_id' => (int) $user['id'],
        'pwv' => password_fingerprint($storedHash),
        'login_at' => time(),
        'last_seen' => time(),
        'csrf' => bin2hex(random_bytes(32)),
    ];

    setcookie(DEVICE_COOKIE, device_token((int) $user['id'], $storedHash), [
        'expires' => time() + DEVICE_COOKIE_DAYS * 86400,
        'path' => '/admin/',
        'secure' => !is_local_request(),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    return 'ok';
}

/** Bir sonraki sayfada bir kez gösterilecek bildirim (yenileyince tekrar çıkmaz). */
function flash(?string $message = null): ?string
{
    admin_session_start();

    if ($message !== null) {
        $_SESSION['flash'] = $message;

        return null;
    }

    $stored = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_string($stored) ? $stored : null;
}
