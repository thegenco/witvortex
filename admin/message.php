<?php
declare(strict_types=1);

require __DIR__ . '/../app/layout.php';

$user = require_admin();

$id = filter_var($_POST['id'] ?? $_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false || $id === null) {
    admin_not_found($user);
}

$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    switch ($_POST['action'] ?? '') {
        case 'delete':
            $db->prepare('DELETE FROM messages WHERE id = ?')->execute([$id]);
            flash('Mesaj silindi.');
            header('Location: /admin/');
            exit;
        case 'unread':
            $db->prepare('UPDATE messages SET read_at = NULL WHERE id = ?')->execute([$id]);
            flash('Mesaj okunmadı olarak işaretlendi.');
            header('Location: /admin/');
            exit;
        default:
            http_response_code(400);
            exit;
    }
}

$stmt = $db->prepare('SELECT id, name, email, phone, topic, body, read_at, created_at FROM messages WHERE id = ?');
$stmt->execute([$id]);
$message = $stmt->fetch();

if (!$message) {
    admin_not_found($user);
}

// Açılan mesaj okundu sayılır.
if ($message['read_at'] === null) {
    $db->prepare('UPDATE messages SET read_at = NOW() WHERE id = ?')->execute([$id]);
}

// Adres URL'ye kodlanarak konur: '?bcc=...' gibi uydurulmuş bir adres yanıta gizli alıcı ekleyemez.
$mailto = 'mailto:' . strtr(rawurlencode($message['email']), ['%40' => '@']);
$replyHref = $mailto . '?subject=' . rawurlencode('Re: ' . $message['topic']);

admin_page_start($message['name'], $user);
?>
  <a class="back" href="/admin/">← Mesajlar</a>

  <article class="message">
    <header class="message-head">
      <p class="message-topic"><?= e($message['topic']) ?></p>
      <h1 class="message-from"><?= e($message['name']) ?></h1>

      <dl class="message-meta">
        <div>
          <dt>E-posta</dt>
          <dd><a href="<?= e($mailto) ?>"><?= e($message['email']) ?></a></dd>
        </div>
        <div>
          <dt>Telefon</dt>
          <dd><?php if ($message['phone'] !== ''): ?><a href="tel:<?= e(preg_replace('/[^0-9+]/', '', $message['phone'])) ?>"><?= e($message['phone']) ?></a><?php else: ?><span class="muted">—</span><?php endif; ?></dd>
        </div>
        <div>
          <dt>Tarih</dt>
          <dd><time datetime="<?= e(local_time($message['created_at'], DATE_ATOM)) ?>"><?= e(local_time($message['created_at'])) ?></time></dd>
        </div>
      </dl>
    </header>

    <div class="message-body"><?= e($message['body']) ?></div>

    <footer class="message-actions">
      <a class="btn btn-primary" href="<?= e($replyHref) ?>">E-postayla yanıtla</a>

      <form method="post" action="/admin/message.php">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
        <input type="hidden" name="action" value="unread">
        <button class="btn btn-ghost" type="submit">Okunmadı yap</button>
      </form>

      <form method="post" action="/admin/message.php" data-confirm="Bu mesaj kalıcı olarak silinecek. Emin misin?">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
        <input type="hidden" name="action" value="delete">
        <button class="btn btn-danger" type="submit">Sil</button>
      </form>
    </footer>
  </article>
<?php
admin_page_end();
