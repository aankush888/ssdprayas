<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'blogs';
$page_title = 'Blog | ' . SITE_NAME;
$page_desc  = 'Insights on AI education, NEP 2020, educator training and school innovation from the SSD Prayas team.';

$per_page = 9;
$current  = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($current - 1) * $per_page;

$tag   = trim($_GET['tag'] ?? '');
$where = "status = 'published'";
$args  = [];
if ($tag !== '') {
    $where .= " AND tag = ?";
    $args[] = $tag;
}

$total = (int)scalar($pdo, "SELECT COUNT(*) FROM blogs WHERE $where", $args);
$pages = max(1, (int)ceil($total / $per_page));

$posts = rows(
    $pdo,
    "SELECT title, slug, tag, excerpt, image, created_at
     FROM blogs WHERE $where
     ORDER BY created_at DESC
     LIMIT $per_page OFFSET $offset",
    $args
);

$tags = rows($pdo, "SELECT DISTINCT tag FROM blogs WHERE status = 'published' AND tag <> '' ORDER BY tag");

include __DIR__ . '/includes/header.php';
?>

<main>

<section class="page-hero">
  <div class="container">
    <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; Blog</p>
    <span class="eyebrow"><i class="fas fa-newspaper"></i> Latest Insights</span>
    <h1>The <span class="grad-text">SSD Prayas</span> Blog</h1>
    <p>AI education, NEP 2020, educator training and what is actually working inside Indian classrooms.</p>
  </div>
</section>

<section class="section">
  <div class="container">

    <?php if ($tags): ?>
      <div class="audience-pills" style="margin-bottom:36px">
        <a href="<?= e(url('blogs')) ?>" class="audience-pill <?= $tag === '' ? 'p-blue' : '' ?>">
          <i class="fas fa-layer-group"></i> All Posts
        </a>
        <?php foreach ($tags as $t): ?>
          <a href="<?= e(url('blogs?tag=' . urlencode($t['tag']))) ?>"
             class="audience-pill <?= $tag === $t['tag'] ? 'p-green' : '' ?>">
            <i class="fas fa-tag"></i> <?= e($t['tag']) ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="grid grid-3">
      <?php if (!$posts): ?>
        <div class="empty">
          <i class="fas fa-feather-pointed"></i>
          <h3>No articles yet</h3>
          <p>New posts are on the way. Check back shortly.</p>
        </div>
      <?php else: ?>
        <?php foreach ($posts as $post): ?>
          <article class="post-card reveal">
            <a href="<?= e(url('blog/' . $post['slug'])) ?>" class="post-thumb">
              <img src="<?= e(url($post['image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
            </a>
            <div class="post-body">
              <span class="post-tag"><?= e($post['tag']) ?></span>
              <h3><a href="<?= e(url('blog/' . $post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
              <p><?= e(mb_strimwidth($post['excerpt'], 0, 130, '…')) ?></p>
              <div class="post-meta">
                <span><i class="far fa-calendar"></i> <?= date('M d, Y', strtotime($post['created_at'])) ?></span>
                <a href="<?= e(url('blog/' . $post['slug'])) ?>" class="link-arrow">Read <span>→</span></a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if ($pages > 1): ?>
      <div style="display:flex;justify-content:center;gap:10px;margin-top:46px;flex-wrap:wrap">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a href="<?= e(url('blogs?page=' . $i . ($tag !== '' ? '&tag=' . urlencode($tag) : ''))) ?>"
             class="btn btn-sm <?= $i === $current ? 'btn-primary' : 'btn-ghost' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
