<?php
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$blog = null;

if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $blog = $stmt->fetch();
}

if (!$blog) {
    header('Location: ' . url('blogs'), true, 302);
    exit;
}

// Non-critical view counter
try {
    $pdo->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?")->execute([$blog['id']]);
} catch (PDOException $e) {
    error_log('View counter failed: ' . $e->getMessage());
}

$page       = 'blog';
$page_title = ($blog['meta_title'] ?: $blog['title']) . ' | ' . SITE_NAME;
$page_desc  = $blog['meta_description'] ?: mb_strimwidth(strip_tags($blog['excerpt']), 0, 155, '…');
$page_image = url($blog['image']);

$share_url = url('blog/' . $blog['slug']);

$related = rows(
    $pdo,
    "SELECT title, slug, tag, excerpt, image, created_at
     FROM blogs
     WHERE status = 'published' AND id <> ? AND (tag = ? OR 1=1)
     ORDER BY (tag = ?) DESC, created_at DESC
     LIMIT 3",
    [$blog['id'], $blog['tag'], $blog['tag']]
);

include __DIR__ . '/includes/header.php';
?>

<main>

<section class="page-hero">
  <div class="container-sm">
    <p class="crumbs">
      <a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp;
      <a href="<?= e(url('blogs')) ?>">Blog</a> &nbsp;/&nbsp; <?= e($blog['tag']) ?>
    </p>
    <span class="eyebrow"><i class="fas fa-tag"></i> <?= e($blog['tag']) ?></span>
    <h1><?= e($blog['title']) ?></h1>
    <div class="article-meta" style="margin-top:20px">
      <span><i class="far fa-calendar"></i><?= date('M d, Y', strtotime($blog['created_at'])) ?></span>
      <span><i class="far fa-user"></i><?= e($blog['author'] ?: SITE_NAME . ' Team') ?></span>
      <span><i class="far fa-clock"></i><?= max(1, (int)round(str_word_count(strip_tags($blog['content'])) / 200)) ?> min read</span>
    </div>
  </div>
</section>

<article class="section">
  <div class="container-sm">

    <?php if ($blog['image']): ?>
      <img class="article-hero-img" src="<?= e(url($blog['image'])) ?>" alt="<?= e($blog['title']) ?>">
    <?php endif; ?>

    <div class="article-body" style="margin-top:38px">
      <?php if ($blog['excerpt']): ?>
        <p class="article-lead"><?= e($blog['excerpt']) ?></p>
      <?php endif; ?>

      <?php
      /* Content is authored in the admin TinyMCE editor, so HTML is intentional here. */
      echo $blog['content'];
      ?>
    </div>

    <div class="share-row" style="margin-top:46px;padding-top:28px;border-top:1px solid var(--line)">
      <strong style="color:var(--ink);margin-right:6px">Share:</strong>
      <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($share_url) ?>" target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
      <a href="https://twitter.com/intent/tweet?url=<?= urlencode($share_url) ?>&text=<?= urlencode($blog['title']) ?>" target="_blank" rel="noopener" aria-label="Share on X"><i class="fab fa-x-twitter"></i></a>
      <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($share_url) ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><i class="fab fa-linkedin-in"></i></a>
      <a href="https://api.whatsapp.com/send?text=<?= urlencode($blog['title'] . ' ' . $share_url) ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a>
      <a href="<?= e(url('blogs')) ?>" class="btn btn-ghost btn-sm" style="margin-left:auto;width:auto;height:auto">
        <i class="fas fa-arrow-left"></i> All Articles
      </a>
    </div>

  </div>
</article>

<?php if ($related): ?>
<section class="section section-soft">
  <div class="container">
    <div class="section-head is-center">
      <h2>Keep <span class="text-brand">Reading</span></h2>
    </div>
    <div class="grid grid-3">
      <?php foreach ($related as $post): ?>
        <article class="post-card reveal">
          <a href="<?= e(url('blog/' . $post['slug'])) ?>" class="post-thumb">
            <img src="<?= e(url($post['image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
          </a>
          <div class="post-body">
            <span class="post-tag"><?= e($post['tag']) ?></span>
            <h3><a href="<?= e(url('blog/' . $post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
            <div class="post-meta">
              <span><?= date('M d, Y', strtotime($post['created_at'])) ?></span>
              <a href="<?= e(url('blog/' . $post['slug'])) ?>" class="link-arrow">Read <span>→</span></a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
