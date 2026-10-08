<?php
require_once __DIR__ . '/includes/functions.php';

$slug = trim($_GET['slug'] ?? '');
$blog = null;

if ($slug === 'how-school-students-in-india-can-learn-artificial-intelligence') {
    header('Location: ' . url('blog/artificial-intelligence-for-kids-india'), true, 301);
    exit;
}

if ($slug !== '') {
    $stmt = $pdo->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'published'");
    $stmt->execute([$slug]);
    $blog = $stmt->fetch();
}

if (!$blog) {
    header('Location: ' . url('blogs'), true, 302);
    exit;
}

// Lightweight view counter (throttled per session to prevent DB write-lock storms)
$view_key = 'viewed_blog_' . (int)$blog['id'];
if (empty($_SESSION[$view_key])) {
    $_SESSION[$view_key] = time();
    try {
        $pdo->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?")->execute([$blog['id']]);
    } catch (PDOException $e) {
        error_log('View counter failed: ' . $e->getMessage());
    }
}

$page       = 'blog';
$page_title = $blog['meta_title'] ?: $blog['title'];
$page_desc  = $blog['meta_description'] ?: mb_strimwidth(strip_tags($blog['excerpt']), 0, 155, '…');
$page_keywords = !empty($blog['meta_keywords']) ? $blog['meta_keywords'] : null;
$page_image = url(blog_image($blog));
$page_image_alt = !empty($blog['image_alt']) ? $blog['image_alt'] : $blog['title'];

// Open Graph & Twitter Cards
$page_og_title = !empty($blog['og_title']) ? $blog['og_title'] : $page_title;
$page_og_desc  = !empty($blog['og_description']) ? $blog['og_description'] : $page_desc;
$page_og_image = !empty($blog['og_image']) ? url($blog['og_image']) : $page_image;
$page_twitter_title = !empty($blog['twitter_title']) ? $blog['twitter_title'] : $page_og_title;
$page_twitter_desc  = !empty($blog['twitter_description']) ? $blog['twitter_description'] : $page_og_desc;
$page_twitter_image = !empty($blog['twitter_image']) ? url($blog['twitter_image']) : $page_og_image;

$share_url       = url('blog/' . $blog['slug']);
$page_canonical  = $share_url;

$blog_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $blog['title'],
    'image' => [ $page_image ],
    'datePublished' => date('c', strtotime($blog['created_at'])),
    'dateModified' => date('c', strtotime($blog['updated_at'] ?: $blog['created_at'])),
    'author' => [
        '@type' => 'Person',
        'name' => $blog['author'] ?: (SITE_NAME . ' Team'),
    ],
    'publisher' => [
        '@type' => 'EducationalOrganization',
        'name' => SITE_NAME,
        'logo' => [
            '@type' => 'ImageObject',
            'url' => url('assets/' . SITE_LOGO),
        ],
    ],
    'description' => $page_desc,
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $share_url,
    ],
];

$breadcrumb_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => url('/'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Blogs',
            'item' => url('blogs'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $blog['title'],
            'item' => $share_url,
        ],
    ],
];

// Build custom schema output: Article + FAQ + Breadcrumbs
$schemas = [];
if (!empty(trim($blog['schema_article'] ?? ''))) {
    $art_raw = trim($blog['schema_article']);
    $schemas[] = (stripos($art_raw, '<script') !== false) ? $art_raw : '<script type="application/ld+json">' . $art_raw . '</script>';
} else {
    $schemas[] = '<script type="application/ld+json">' . json_encode($blog_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}

if (!empty(trim($blog['schema_faq'] ?? ''))) {
    $faq_raw = trim($blog['schema_faq']);
    $schemas[] = (stripos($faq_raw, '<script') !== false) ? $faq_raw : '<script type="application/ld+json">' . $faq_raw . '</script>';
}

$schemas[] = '<script type="application/ld+json">' . json_encode($breadcrumb_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
$custom_schema = implode("\n", $schemas);

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
      <img class="article-hero-img" src="<?= e(blog_image_url($blog)) ?>" alt="<?= e($page_image_alt) ?>">
    <?php endif; ?>

    <div class="article-body" style="margin-top:38px">
      <?php if ($blog['excerpt']): ?>
        <p class="article-lead"><?= e($blog['excerpt']) ?></p>
      <?php endif; ?>

      <?php
      /* Content authored in admin: renders HTML or preserves line breaks if plain text */
      if (strip_tags($blog['content']) === $blog['content']) {
          echo nl2br(e($blog['content']));
      } else {
          echo $blog['content'];
      }
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
            <img src="<?= e(blog_image_url($post)) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
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
