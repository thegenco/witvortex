<?php
declare(strict_types=1);

require __DIR__ . '/../app/layout.php';

$user = require_admin();

const PER_PAGE = 25;

$filter = ($_GET['filter'] ?? '') === 'unread' ? 'unread' : 'all';
$page = max(1, (int) ($_GET['page'] ?? 1));
$where = $filter === 'unread' ? 'WHERE read_at IS NULL' : '';

$db = db();
$unreadCount = (int) $db->query('SELECT COUNT(*) FROM messages WHERE read_at IS NULL')->fetchColumn();
$total = (int) $db->query("SELECT COUNT(*) FROM messages $where")->fetchColumn();
$pages = max(1, (int) ceil($total / PER_PAGE));
$page = min($page, $pages);

$stmt = $db->prepare(
    "SELECT id, name, email, topic, LEFT(body, 180) AS excerpt, read_at, created_at
       FROM messages $where
   ORDER BY created_at DESC, id DESC
      LIMIT :limit OFFSET :offset"
);
$stmt->bindValue(':limit', PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':offset', ($page - 1) * PER_PAGE, PDO::PARAM_INT);
$stmt->execute();
$messages = $stmt->fetchAll();

$notice = flash();

function page_url(string $filter, int $page): string
{
    $query = array_filter(['filter' => $filter === 'unread' ? 'unread' : null, 'page' => $page > 1 ? $page : null]);

    return '/admin/' . ($query ? '?' . http_build_query($query) : '');
}

admin_page_start('Mesajlar', $user);
?>
  <div class="page-head">
    <h1 class="page-title">Mesajlar</h1>
    <nav class="tabs" aria-label="Mesaj filtresi">
      <a class="tab" href="/admin/"<?= $filter === 'all' ? ' aria-current="page"' : '' ?>><span class="tab-label">Tümü</span></a>
      <a class="tab" href="/admin/?filter=unread"<?= $filter === 'unread' ? ' aria-current="page"' : '' ?>>
        <span class="tab-label">Okunmamış</span><?php if ($unreadCount > 0): ?> <span class="tab-count"><?= $unreadCount ?></span><?php endif; ?>
      </a>
    </nav>
  </div>

  <?php if ($notice): ?>
    <p class="notice" role="status"><?= e($notice) ?></p>
  <?php endif; ?>

  <?php if (!$messages): ?>
    <div class="empty">
      <?php if ($filter === 'unread'): ?>
        <p class="empty-title">Okunmamış mesaj yok.</p>
        <p class="muted">Yeni bir mesaj geldiğinde burada görünecek.</p>
      <?php else: ?>
        <p class="empty-title">Henüz mesaj yok.</p>
        <p class="muted">Sitedeki iletişim formundan gelen mesajlar burada görünecek.</p>
      <?php endif; ?>
    </div>
  <?php else: ?>
    <ul class="inbox">
      <?php foreach ($messages as $m): ?>
        <?php $unread = $m['read_at'] === null; ?>
        <li>
          <a class="inbox-row<?= $unread ? ' is-unread' : '' ?>" href="/admin/message.php?id=<?= (int) $m['id'] ?>">
            <?php if ($unread): ?><span class="sr-only">Okunmamış: </span><?php endif; ?>
            <span class="inbox-dot" aria-hidden="true"></span>
            <span class="inbox-main">
              <span class="inbox-line">
                <span class="inbox-from"><?= e($m['name']) ?></span>
                <span class="inbox-topic"><?= e($m['topic']) ?></span>
              </span>
              <span class="inbox-excerpt"><?= e($m['excerpt']) ?></span>
            </span>
            <time class="inbox-time" datetime="<?= e(local_time($m['created_at'], DATE_ATOM)) ?>"><?= e(local_time($m['created_at'])) ?></time>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

    <?php if ($pages > 1): ?>
      <nav class="pager" aria-label="Sayfalar">
        <?php if ($page > 1): ?>
          <a class="btn btn-ghost btn-sm" href="<?= e(page_url($filter, $page - 1)) ?>">← Daha yeni</a>
        <?php endif; ?>
        <span class="muted"><?= $page ?> / <?= $pages ?></span>
        <?php if ($page < $pages): ?>
          <a class="btn btn-ghost btn-sm" href="<?= e(page_url($filter, $page + 1)) ?>">Daha eski →</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
<?php
admin_page_end();
