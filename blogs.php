<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'blogs';
$page_title = 'The SSD Prayas Blog | Insights & Stories on AI Education';
$page_desc  = 'Stories, ideas and insights on AI education, NEP 2020, educator training and what is really working inside Indian classrooms.';
$page_robots = 'noindex, follow';

$per_page = 5; // 5 blog cards + 1 newsletter card on page 1 for a balanced 6-card grid
$current  = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($current - 1) * $per_page;

$tag    = trim($_GET['tag'] ?? '');
$search = trim($_GET['search'] ?? '');

$where = "status = 'published'";
$args  = [];

if ($tag !== '') {
    $where .= " AND LOWER(tag) = LOWER(?)";
    $args[] = $tag;
}

if ($search !== '') {
    $where .= " AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
    $args[] = "%$search%";
    $args[] = "%$search%";
    $args[] = "%$search%";
}

$total = (int)scalar($pdo, "SELECT COUNT(*) FROM blogs WHERE $where", $args);
$pages = max(1, (int)ceil($total / $per_page));

$posts = rows(
    $pdo,
    "SELECT id, title, slug, tag, excerpt, image, content, created_at
     FROM blogs WHERE $where
     ORDER BY created_at DESC, id ASC
     LIMIT $per_page OFFSET $offset",
    $args
);

function get_blog_tag_class($tag) {
    $t = strtolower(trim($tag));
    if ($t === 'ai') return 'tag-blue';
    if ($t === 'innovation') return 'tag-green';
    if ($t === 'policy') return 'tag-red';
    if ($t === 'future skills') return 'tag-purple';
    return 'tag-blue';
}

function get_blog_read_time($post) {
    $slug = $post['slug'] ?? '';
    if (strpos($slug, 'cbse') !== false) return '5 min read';
    if (strpos($slug, 'future-of-smart') !== false) return '6 min read';
    if (strpos($slug, 'nep-2020') !== false) return '4 min read';
    if (strpos($slug, 'top-5') !== false) return '7 min read';
    if (strpos($slug, 'educators-can-bring') !== false) return '5 min read';
    $words = str_word_count(strip_tags(($post['excerpt'] ?? '') . ' ' . ($post['content'] ?? '')));
    return max(3, ceil($words / 60)) . ' min read';
}

$category_pills = [
    ['tag' => '',              'label' => 'All Posts',     'icon' => 'fas fa-layer-group'],
    ['tag' => 'Ai',            'label' => 'AI',            'icon' => 'fas fa-book-open'],
    ['tag' => 'Future Skills', 'label' => 'Future Skills', 'icon' => 'far fa-lightbulb'],
    ['tag' => 'Innovation',    'label' => 'Innovation',    'icon' => 'fas fa-gear'],
    ['tag' => 'Policy',        'label' => 'Policy',        'icon' => 'far fa-file-alt'],
];

include __DIR__ . '/includes/header.php';
?>

<main class="page-blogs">

  <!-- Section 1: Hero Banner -->
  <section class="blog-hero">
    <div class="container blog-hero-container">
      <div class="blog-hero-content">
        <p class="crumbs">
          <a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; <span>Blogs</span>
        </p>

        <span class="pill-badge pill-blue">
          <i class="fas fa-book-open"></i> LATEST INSIGHTS
        </span>

        <h1 class="blog-hero-title">
          The <span class="text-blue">SSD Prayas</span> Blog
        </h1>

        <p class="blog-hero-desc">
          Stories, ideas and insights on AI education, NEP 2020, educator training and what's really working inside Indian classrooms.
        </p>

        <div class="blog-trust-row">
          <div class="blog-trust-item">
            <div class="bti-icon bti-blue">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="bti-text">
              <strong>Real Classrooms</strong>
              <span>Real Impact</span>
            </div>
          </div>

          <div class="blog-trust-item">
            <div class="bti-icon bti-blue">
              <i class="fas fa-users"></i>
            </div>
            <div class="bti-text">
              <strong>Expert Insights</strong>
              <span>from Educators</span>
            </div>
          </div>

          <div class="blog-trust-item">
            <div class="bti-icon bti-blue">
              <i class="fas fa-chart-line"></i>
            </div>
            <div class="bti-text">
              <strong>A Brighter Future</strong>
              <span>for Every Learner</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sticky Note on top right matching design -->
    <div class="blog-sticky-note">
      <div class="bsn-pin"><i class="fas fa-thumbtack"></i></div>
      <div class="bsn-items">
        <span>AI Education</span>
        <span>Opportunities</span>
        <span>Real Stories</span>
        <span>Policy Updates</span>
      </div>
      <div class="bsn-stripes">
        <span class="bsn-stripe s-orange"></span>
        <span class="bsn-stripe s-green"></span>
      </div>
    </div>
  </section>

  <!-- Section 2: Filter & Search Bar -->
  <section class="blog-bar-section">
    <div class="container">
      <div class="blog-bar-wrap">
        <div class="blog-filter-pills">
          <?php foreach ($category_pills as $p): 
            $is_active = (strcasecmp($tag, $p['tag']) === 0);
          ?>
            <a href="<?= e(url('blogs' . ($p['tag'] !== '' ? '?tag=' . urlencode($p['tag']) : '') . ($search !== '' ? ($p['tag'] !== '' ? '&' : '?') . 'search=' . urlencode($search) : ''))) ?>"
               class="filter-pill <?= $is_active ? 'is-active' : '' ?>">
              <i class="<?= e($p['icon']) ?>"></i> <?= e($p['label']) ?>
            </a>
          <?php endforeach; ?>
        </div>

        <form method="GET" action="<?= e(url('blogs')) ?>" class="blog-search-form">
          <?php if ($tag !== ''): ?>
            <input type="hidden" name="tag" value="<?= e($tag) ?>">
          <?php endif; ?>
          <i class="fas fa-search search-icon"></i>
          <input type="text" name="search" class="blog-search-input" placeholder="Search articles..." value="<?= e($search) ?>">
        </form>
      </div>
    </div>
  </section>

  <!-- Section 3: Blog Cards Grid -->
  <section class="section-blog-grid">
    <div class="container">
      <?php if (empty($posts) && $search !== ''): ?>
        <div class="empty-blogs-box">
          <i class="fas fa-magnifying-glass"></i>
          <h3>No articles found for "<?= e($search) ?>"</h3>
          <p>Try searching for different keywords or explore our category filters.</p>
          <a href="<?= e(url('blogs')) ?>" class="btn-blue-pill" style="margin-top:16px;">View All Posts</a>
        </div>
      <?php elseif (empty($posts)): ?>
        <div class="empty-blogs-box">
          <i class="fas fa-feather-pointed"></i>
          <h3>No articles published yet</h3>
          <p>New stories and insights are being crafted. Check back soon!</p>
        </div>
      <?php else: ?>
        <div class="blog-cards-grid">
          <?php foreach ($posts as $post): 
            $tag_cls   = get_blog_tag_class($post['tag']);
            $read_time = get_blog_read_time($post);
          ?>
            <article class="blog-card">
              <a href="<?= e(url('blog/' . $post['slug'])) ?>" class="blog-card-thumb">
                <img src="<?= e(blog_image_url($post)) ?>" alt="<?= e($post['title']) ?>" class="blog-thumb-img" loading="lazy">
              </a>
              <div class="blog-card-body">
                <span class="blog-tag-pill <?= e($tag_cls) ?>"><?= e(strtoupper($post['tag'])) ?></span>
                <h2 class="blog-card-title">
                  <a href="<?= e(url('blog/' . $post['slug'])) ?>"><?= e($post['title']) ?></a>
                </h2>
                <p class="blog-card-excerpt"><?= e(mb_strimwidth($post['excerpt'], 0, 145, '...')) ?></p>
                <div class="blog-card-footer">
                  <div class="bcf-meta">
                    <span><i class="far fa-calendar-alt"></i> <?= date('M d, Y', strtotime($post['created_at'])) ?></span>
                    <span class="meta-sep">|</span>
                    <span><i class="far fa-clock"></i> <?= e($read_time) ?></span>
                  </div>
                  <a href="<?= e(url('blog/' . $post['slug'])) ?>" class="blog-read-more">Read More <span>→</span></a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>

          <!-- Newsletter Card (Card 6 in the 3-column layout) -->
          <?php if ($current === 1 && empty($search)): ?>
            <article class="blog-card blog-newsletter-card">
              <div>
                <div class="bnc-icon-badge">
                  <i class="fas fa-book-open"></i>
                </div>
                <h3 class="bnc-title">Knowledge Builds Better <span style="color:#2563eb;">Classrooms</span></h3>
                <p class="bnc-desc">Get the latest articles, resources and success stories delivered to your inbox.</p>
                <form class="bnc-form" action="<?= e(url('contact')) ?>" method="POST">
                  <input type="hidden" name="action" value="newsletter">
                  <input type="email" name="email" required class="bnc-input" placeholder="Enter your email">
                  <button type="submit" class="bnc-submit-btn">
                    Subscribe Now <span>→</span>
                  </button>
                </form>
              </div>
              <div class="bnc-calligraphy">
                <div class="bnc-callig-text">
                  Learn<br>Share<br>Grow<br><strong style="font-size:14px;color:#0284c7;">Together</strong>
                </div>
                <svg class="bnc-swash" viewBox="0 0 100 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M5 18C30 5 70 20 95 6" stroke="#FF9933" stroke-width="3" stroke-linecap="round"/>
                  <path d="M15 20C40 7 75 22 95 10" stroke="#138808" stroke-width="3" stroke-linecap="round"/>
                </svg>
              </div>
            </article>
          <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php 
        $total_pages_to_show = max(3, $pages);
        ?>
        <div class="blog-pagination">
          <a href="<?= e(url('blogs?page=' . max(1, $current - 1) . ($tag !== '' ? '&tag=' . urlencode($tag) : '') . ($search !== '' ? '&search=' . urlencode($search) : ''))) ?>" class="page-circle-btn" aria-label="Previous page">
            <i class="fas fa-chevron-left" style="font-size:11px"></i>
          </a>
          <?php for ($i = 1; $i <= $total_pages_to_show; $i++): ?>
            <a href="<?= e(url('blogs?page=' . $i . ($tag !== '' ? '&tag=' . urlencode($tag) : '') . ($search !== '' ? '&search=' . urlencode($search) : ''))) ?>"
               class="page-circle-btn <?= $i === $current ? 'is-active' : '' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>
          <a href="<?= e(url('blogs?page=' . min($total_pages_to_show, $current + 1) . ($tag !== '' ? '&tag=' . urlencode($tag) : '') . ($search !== '' ? '&search=' . urlencode($search) : ''))) ?>" class="page-circle-btn" aria-label="Next page">
            <i class="fas fa-chevron-right" style="font-size:11px"></i>
          </a>
        </div>

      <?php endif; ?>
    </div>
  </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
