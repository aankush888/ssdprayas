<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

// Ensure pages_seo table is initialized
sync_pages_seo_table($pdo);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ==========================================
    // ACTION: SAVE CORE PAGE SEO & CONTENT
    // ==========================================
    if ($action === 'save_page_seo') {
        $page_key         = trim($_POST['page_key'] ?? '');
        $page_name        = trim($_POST['page_name'] ?? '');
        $page_url         = trim($_POST['page_url'] ?? '');
        $meta_title       = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_keywords    = trim($_POST['meta_keywords'] ?? '');
        $h1_heading       = trim($_POST['h1_heading'] ?? '');
        $content          = trim($_POST['content'] ?? '');
        $canonical_url    = trim($_POST['canonical_url'] ?? '');
        $og_title         = trim($_POST['og_title'] ?? '');
        $og_description   = trim($_POST['og_description'] ?? '');
        $og_image         = trim($_POST['og_image'] ?? '');
        $schema_json      = trim($_POST['schema_json'] ?? '');

        if ($page_key === '') {
            flash_set('error', 'Page identifier (page_key) is required.');
            header('Location: seo.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO pages_seo 
                    (page_key, page_name, page_url, meta_title, meta_description, meta_keywords, h1_heading, content, canonical_url, og_title, og_description, og_image, schema_json, updated_at)
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE
                    page_name = VALUES(page_name),
                    page_url = VALUES(page_url),
                    meta_title = VALUES(meta_title),
                    meta_description = VALUES(meta_description),
                    meta_keywords = VALUES(meta_keywords),
                    h1_heading = VALUES(h1_heading),
                    content = VALUES(content),
                    canonical_url = VALUES(canonical_url),
                    og_title = VALUES(og_title),
                    og_description = VALUES(og_description),
                    og_image = VALUES(og_image),
                    schema_json = VALUES(schema_json),
                    updated_at = NOW()
            ");
            $stmt->execute([
                $page_key, $page_name, $page_url, $meta_title, $meta_description,
                $meta_keywords, $h1_heading, $content, $canonical_url,
                $og_title, $og_description, $og_image, $schema_json
            ]);

            flash_set('success', "SEO & Content for '{$page_name}' successfully updated!");
            header("Location: seo.php?tab=pages&key=" . urlencode($page_key));
            exit;
        } catch (PDOException $e) {
            error_log('SEO Page save failed: ' . $e->getMessage());
            flash_set('error', 'Failed to save SEO settings: ' . $e->getMessage());
            header("Location: seo.php?tab=pages&key=" . urlencode($page_key));
            exit;
        }
    }

    // ==========================================
    // ACTION: QUICK UPDATE BLOG SEO & CONTENT
    // ==========================================
    if ($action === 'save_blog_seo') {
        $blog_id          = (int)($_POST['blog_id'] ?? 0);
        $title            = trim($_POST['title'] ?? '');
        $meta_title       = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_keywords    = trim($_POST['meta_keywords'] ?? '');
        $excerpt          = trim($_POST['excerpt'] ?? '');
        $content          = $_POST['content'] ?? null;
        $status           = enum_or($_POST['status'] ?? '', ['published', 'draft'], 'published');

        if (!$blog_id || $title === '') {
            flash_set('error', 'Valid blog article ID and Title are required.');
            header('Location: seo.php?tab=blogs');
            exit;
        }

        try {
            if ($content !== null) {
                $stmt = $pdo->prepare("
                    UPDATE blogs 
                    SET title = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, excerpt = ?, content = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$title, $meta_title, $meta_description, $meta_keywords, $excerpt, $content, $status, $blog_id]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE blogs 
                    SET title = ?, meta_title = ?, meta_description = ?, meta_keywords = ?, excerpt = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$title, $meta_title, $meta_description, $meta_keywords, $excerpt, $status, $blog_id]);
            }

            flash_set('success', "Blog article SEO & Content for '{$title}' updated successfully!");
            header('Location: seo.php?tab=blogs');
            exit;
        } catch (PDOException $e) {
            error_log('Blog SEO save failed: ' . $e->getMessage());
            flash_set('error', 'Failed to update blog SEO: ' . $e->getMessage());
            header('Location: seo.php?tab=blogs');
            exit;
        }
    }

    // ==========================================
    // ACTION: CREATE NEW SEO BLOG POST
    // ==========================================
    if ($action === 'create_seo_blog') {
        $title            = trim($_POST['title'] ?? '');
        $slug             = slugify($_POST['slug'] ?? '') ?: slugify($title);
        $tag              = trim($_POST['tag'] ?? 'AI Education');
        $meta_title       = trim($_POST['meta_title'] ?? '') ?: $title;
        $meta_description = trim($_POST['meta_description'] ?? '');
        $meta_keywords    = trim($_POST['meta_keywords'] ?? '');
        $excerpt          = trim($_POST['excerpt'] ?? '');
        $content          = trim($_POST['content'] ?? '');
        $status           = enum_or($_POST['status'] ?? '', ['published', 'draft'], 'published');
        $author           = 'SSD Prayas Team';
        $image            = 'assets/img/blog-1.jpg';

        if ($title === '') {
            flash_set('error', 'Post Title / H1 is required.');
            header('Location: seo.php?tab=blogs');
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO blogs 
                    (title, slug, tag, excerpt, content, image, author, status, meta_title, meta_description, meta_keywords, created_at, updated_at)
                VALUES 
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$title, $slug, $tag, $excerpt, $content, $image, $author, $status, $meta_title, $meta_description, $meta_keywords]);

            flash_set('success', "New SEO blog post '{$title}' published successfully!");
            header('Location: seo.php?tab=blogs');
            exit;
        } catch (PDOException $e) {
            error_log('New SEO blog save failed: ' . $e->getMessage());
            $msg = str_contains($e->getMessage(), 'Duplicate')
                 ? 'A post with that URL slug already exists.'
                 : 'Failed to create blog post: ' . $e->getMessage();
            flash_set('error', $msg);
            header('Location: seo.php?tab=blogs');
            exit;
        }
    }
}

// Fetch all Core Pages from pages_seo
$pages = rows($pdo, "SELECT * FROM pages_seo ORDER BY id ASC");
if (empty($pages)) {
    // Re-run population if table was just created
    require_once __DIR__ . '/../includes/functions.php';
    sync_pages_seo_table($pdo);
    $pages = rows($pdo, "SELECT * FROM pages_seo ORDER BY id ASC");
}

// Active page for editing
$selected_key = trim($_GET['key'] ?? ($pages[0]['page_key'] ?? 'home'));
$current_page = null;
foreach ($pages as $p) {
    if ($p['page_key'] === $selected_key) {
        $current_page = $p;
        break;
    }
}
if (!$current_page && !empty($pages)) {
    $current_page = $pages[0];
    $selected_key = $current_page['page_key'];
}

// Fetch Blog Posts
$q_blog = trim($_GET['q_blog'] ?? '');
$w_blog = '1=1';
$a_blog = [];
if ($q_blog !== '') {
    $w_blog .= ' AND (title LIKE ? OR meta_keywords LIKE ? OR tag LIKE ?)';
    array_push($a_blog, "%$q_blog%", "%$q_blog%", "%$q_blog%");
}
$blogs = rows($pdo, "SELECT id, title, slug, tag, status, views, meta_title, meta_description, meta_keywords, excerpt, content, created_at, updated_at FROM blogs WHERE $w_blog ORDER BY created_at DESC", $a_blog);

// Calculate Stats
$tot_pages_count = count($pages);
$tot_blogs_count = count($blogs);
$tot_published_blogs = count(array_filter($blogs, fn($b) => $b['status'] === 'published'));

// Active Tab
$active_tab = $_GET['tab'] ?? 'pages';
if (!in_array($active_tab, ['pages', 'blogs', 'simulator', 'audit'])) {
    $active_tab = 'pages';
}

$admin_title  = 'SEO Manager';
$admin_active = 'seo';
$admin_sub    = 'Optimize Meta Titles, Descriptions, Keywords, H1 Headings, and Content for higher Google rankings.';
include __DIR__ . '/_layout.php';
?>

<style>
/* ========================================================
   SEO MANAGER ENHANCED STYLING
   ======================================================== */
.seo-header-strip {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 18px;
    padding: 26px 30px;
    color: #fff;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
    position: relative;
    overflow: hidden;
}
.seo-header-strip::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 320px;
    height: 320px;
    background: radial-gradient(circle, rgba(26, 115, 232, 0.25) 0%, transparent 70%);
    pointer-events: none;
}
.shs-left { display: flex; align-items: center; gap: 18px; position: relative; z-index: 1; }
.shs-icon {
    width: 56px; height: 56px; border-radius: 14px;
    background: rgba(26, 115, 232, 0.2);
    border: 1px solid rgba(26, 115, 232, 0.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: #60a5fa; flex-shrink: 0;
}
.shs-title { font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 4px; }
.shs-sub { font-size: 13.5px; color: #94a3b8; max-width: 650px; line-height: 1.5; margin: 0; }
.shs-actions { display: flex; gap: 10px; align-items: center; position: relative; z-index: 1; flex-wrap: wrap; }

/* Stats Bar */
.seo-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.seo-stat-card {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: var(--sh-card);
    transition: transform .2s, box-shadow .2s;
}
.seo-stat-card:hover { transform: translateY(-2px); box-shadow: var(--sh-hover); }
.ssc-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0;
}
.ssc-icon.blue { background: #ebf3fe; color: #1a73e8; }
.ssc-icon.green { background: #ecfdf5; color: #10b981; }
.ssc-icon.purple { background: #f5f3ff; color: #8b5cf6; }
.ssc-icon.amber { background: #fffbeb; color: #f59e0b; }
.ssc-num { font-size: 20px; font-weight: 800; color: var(--ink); line-height: 1.2; }
.ssc-lbl { font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: .03em; }

/* Tab Bar */
.seo-tab-nav {
    display: flex;
    gap: 8px;
    background: #f1f5f9;
    padding: 6px;
    border-radius: 14px;
    margin-bottom: 24px;
    overflow-x: auto;
    border: 1px solid var(--line);
}
.seo-tab-btn {
    flex: 1;
    min-width: 170px;
    padding: 11px 18px;
    font-size: 13.5px;
    font-weight: 700;
    border: none;
    background: transparent;
    border-radius: 10px;
    color: var(--muted);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all .2s ease;
    white-space: nowrap;
    text-decoration: none;
}
.seo-tab-btn:hover { color: var(--ink); background: rgba(255, 255, 255, 0.6); }
.seo-tab-btn.is-active {
    background: #fff;
    color: var(--brand);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
}

/* Page Selector Strip */
.page-picker-strip {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 12px;
    margin-bottom: 20px;
}
.page-chip {
    padding: 8px 16px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    border: 1.5px solid var(--line);
    background: #fff;
    color: var(--body);
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none;
    transition: all .2s;
    white-space: nowrap;
}
.page-chip:hover { border-color: var(--brand); color: var(--brand); }
.page-chip.is-active {
    background: var(--brand);
    color: #fff;
    border-color: var(--brand);
    box-shadow: 0 4px 12px rgba(26, 115, 232, 0.25);
}

/* Two-column layout for editor */
.seo-layout-grid {
    display: grid;
    grid-template-columns: 1fr 390px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 1100px) {
    .seo-layout-grid { grid-template-columns: 1fr; }
}

/* Live Google SERP Card Preview */
.serp-preview-box {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--sh-card);
    position: sticky;
    top: 90px;
}
.serp-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--line-soft);
}
.serp-badge {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #1a73e8;
    background: #ebf3fe;
    padding: 4px 8px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.serp-device-switch {
    display: flex;
    background: #f1f5f9;
    padding: 2px;
    border-radius: 6px;
}
.sds-btn {
    border: none;
    background: transparent;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 700;
    color: var(--muted);
    border-radius: 4px;
    cursor: pointer;
}
.sds-btn.is-active { background: #fff; color: var(--ink); box-shadow: 0 1px 3px rgba(0,0,0,0.08); }

.google-result-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 14px 16px;
    border: 1px solid #dadce0;
    font-family: Arial, Roboto, sans-serif;
}
.gr-site-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
}
.gr-favicon {
    width: 26px; height: 26px; border-radius: 50%;
    background: #f8fafc; border: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.gr-favicon img { width: 16px; height: 16px; object-fit: contain; }
.gr-site-info { line-height: 1.25; }
.gr-site-name { font-size: 13px; color: #202124; font-weight: 600; }
.gr-site-url { font-size: 11px; color: #5f6368; word-break: break-all; }
.gr-title {
    font-size: 18px;
    line-height: 1.35;
    color: #1a0dab;
    cursor: pointer;
    margin-bottom: 5px;
    font-weight: 400;
    word-break: break-word;
}
.gr-title:hover { text-decoration: underline; }
.gr-desc {
    font-size: 13px;
    line-height: 1.5;
    color: #4d5156;
    margin: 0;
    word-break: break-word;
}

/* Character Count Indicators */
.field-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.char-pill {
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 6px;
    background: #f1f5f9;
    color: var(--muted);
}
.char-pill.good { background: #e6f4ea; color: #137333; }
.char-pill.warning { background: #fef7e0; color: #b06000; }
.char-pill.bad { background: #fce8e6; color: #c5221f; }

/* Keyword pills container */
.kw-pill-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}
.kw-tag {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    padding: 3px 9px;
    border-radius: 12px;
    font-size: 11.5px;
    font-weight: 600;
    color: #334155;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.kw-tag i { color: #1a73e8; font-size: 10px; }

/* Modal for quick editing */
.seo-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s;
}
.seo-modal-overlay.is-open {
    opacity: 1;
    pointer-events: auto;
}
.seo-modal-dialog {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 720px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    border: 1px solid var(--line);
    padding: 28px;
    transform: translateY(20px);
    transition: transform .2s;
}
.seo-modal-overlay.is-open .seo-modal-dialog {
    transform: translateY(0);
}
.sm-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--line);
}
.sm-title { font-size: 18px; font-weight: 800; color: var(--ink); }
.sm-close {
    border: none; background: #f1f5f9; width: 32px; height: 32px;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 14px; color: var(--muted); cursor: pointer;
}
.sm-close:hover { background: #e2e8f0; color: var(--ink); }
</style>

<!-- ========================================================
     TOP HERO STRIP
     ======================================================== -->
<div class="seo-header-strip">
  <div class="shs-left">
    <div class="shs-icon">
      <i class="fas fa-magnifying-glass-chart"></i>
    </div>
    <div>
      <h2 class="shs-title">SEO & Content Optimization Hub</h2>
      <p class="shs-sub">Full control over Meta Titles, Descriptions, Keywords, H1 Headings, and Content for all public pages and blog posts.</p>
    </div>
  </div>
  <div class="shs-actions">
    <a href="<?= e(url('/sitemap.xml')) ?>" target="_blank" rel="noopener" class="btn btn-ghost" style="color:#e2e8f0;border-color:rgba(255,255,255,0.2);">
      <i class="fas fa-sitemap"></i> Sitemap
    </a>
    <a href="<?= e(url('/robots.txt')) ?>" target="_blank" rel="noopener" class="btn btn-ghost" style="color:#e2e8f0;border-color:rgba(255,255,255,0.2);">
      <i class="fas fa-robot"></i> Robots.txt
    </a>
    <a href="<?= e(url('/')) ?>" target="_blank" rel="noopener" class="btn btn-primary">
      <i class="fas fa-arrow-up-right-from-square"></i> Visit Public Site
    </a>
  </div>
</div>

<!-- ========================================================
     QUICK STATS STRIP
     ======================================================== -->
<div class="seo-stats-grid">
  <div class="seo-stat-card">
    <div class="ssc-icon blue"><i class="fas fa-file-lines"></i></div>
    <div>
      <div class="ssc-num"><?= $tot_pages_count ?> Pages</div>
      <div class="ssc-lbl">Core Pages Managed</div>
    </div>
  </div>
  <div class="seo-stat-card">
    <div class="ssc-icon purple"><i class="fas fa-feather-pointed"></i></div>
    <div>
      <div class="ssc-num"><?= $tot_published_blogs ?> / <?= $tot_blogs_count ?> Articles</div>
      <div class="ssc-lbl">Blog Articles Published</div>
    </div>
  </div>
  <div class="seo-stat-card">
    <div class="ssc-icon green"><i class="fas fa-shield-halved"></i></div>
    <div>
      <div class="ssc-num">100% Synced</div>
      <div class="ssc-lbl">Database Overrides Active</div>
    </div>
  </div>
  <div class="seo-stat-card">
    <div class="ssc-icon amber"><i class="fas fa-chart-line"></i></div>
    <div>
      <div class="ssc-num">NEP 2020 / AI</div>
      <div class="ssc-lbl">Target Search Intent</div>
    </div>
  </div>
</div>

<!-- ========================================================
     TAB NAVIGATION
     ======================================================== -->
<div class="seo-tab-nav">
  <a href="seo.php?tab=pages<?= $selected_key ? '&key=' . urlencode($selected_key) : '' ?>" class="seo-tab-btn <?= $active_tab === 'pages' ? 'is-active' : '' ?>">
    <i class="fas fa-file-code"></i> 1. Website Pages SEO (Meta, H1, Content)
  </a>
  <a href="seo.php?tab=blogs" class="seo-tab-btn <?= $active_tab === 'blogs' ? 'is-active' : '' ?>">
    <i class="fas fa-newspaper"></i> 2. Blog Posting SEO & Articles
  </a>
  <a href="seo.php?tab=simulator<?= $selected_key ? '&key=' . urlencode($selected_key) : '' ?>" class="seo-tab-btn <?= $active_tab === 'simulator' ? 'is-active' : '' ?>">
    <i class="fas fa-mobile-screen-button"></i> 3. Google SERP Simulator
  </a>
  <a href="seo.php?tab=audit" class="seo-tab-btn <?= $active_tab === 'audit' ? 'is-active' : '' ?>">
    <i class="fas fa-circle-check"></i> 4. Technical SEO & Schema Checklist
  </a>
</div>

<?php if ($active_tab === 'pages'): ?>
  <!-- ========================================================
       TAB 1: CORE PAGES SEO & CONTENT
       ======================================================== -->
  
  <!-- Page Selector Chips -->
  <div class="page-picker-strip">
    <?php foreach ($pages as $p): ?>
      <a href="seo.php?tab=pages&key=<?= urlencode($p['page_key']) ?>" 
         class="page-chip <?= $p['page_key'] === $selected_key ? 'is-active' : '' ?>">
        <i class="fas fa-link" style="font-size:11px;"></i>
        <span><?= e($p['page_name']) ?></span>
        <code style="font-size:11px;opacity:0.8;"><?= e($p['page_url']) ?></code>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if ($current_page): ?>
    <div class="seo-layout-grid">
      
      <!-- Left Column: Edit Form -->
      <div class="card">
        <div class="card-head">
          <div>
            <h2 style="font-size:18px;display:flex;align-items:center;gap:8px;">
              <i class="fas fa-pen-to-square text-blue"></i>
              Editing: <?= e($current_page['page_name']) ?>
            </h2>
            <p style="font-size:12.5px;color:var(--muted);margin-top:2px;">
              Live Page URL: <a href="<?= e(url($current_page['page_url'] === '/' ? '' : ltrim($current_page['page_url'], '/'))) ?>" target="_blank" rel="noopener" style="color:var(--brand);text-decoration:underline;">
                <?= e(url($current_page['page_url'] === '/' ? '' : ltrim($current_page['page_url'], '/'))) ?> <i class="fas fa-arrow-up-right-from-square" style="font-size:10px;"></i>
              </a>
            </p>
          </div>
          <span class="spacer"></span>
          <span class="badge b-blue"><i class="fas fa-clock"></i> Updated: <?= date('d M Y, h:i A', strtotime($current_page['updated_at'])) ?></span>
        </div>

        <div class="card-body">
          <form method="POST" id="pageSeoForm">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save_page_seo">
            <input type="hidden" name="page_key" value="<?= e($current_page['page_key']) ?>">
            <input type="hidden" name="page_name" value="<?= e($current_page['page_name']) ?>">
            <input type="hidden" name="page_url" value="<?= e($current_page['page_url']) ?>">

            <div class="form-grid">
              
              <!-- 1. H1 HEADING -->
              <div class="fg full">
                <div class="field-header-row">
                  <label for="f_h1"><strong><i class="fas fa-heading text-blue"></i> Primary H1 Heading</strong> <span class="req">*</span></label>
                  <span class="char-pill" id="h1_counter">0 chars</span>
                </div>
                <input type="text" id="f_h1" name="h1_heading" required 
                       value="<?= e($current_page['h1_heading']) ?>" 
                       placeholder="e.g. AI Education for a Future-Ready Bharat">
                <span class="field-hint">The main headline displayed on the hero section of this page. Essential for top Google search engine ranking.</span>
              </div>

              <!-- 2. CONTENT / HERO LEAD -->
              <div class="fg full">
                <div class="field-header-row">
                  <label for="f_content"><strong><i class="fas fa-align-left text-blue"></i> Content / Hero Lead Description</strong></label>
                  <span class="char-pill" id="content_counter">0 chars</span>
                </div>
                <textarea id="f_content" name="content" rows="4" style="resize:vertical;"
                          placeholder="e.g. Practical, hands-on AI education for school students, teachers and working professionals across India..."><?= e($current_page['content']) ?></textarea>
                <span class="field-hint">The primary body/intro lead text shown below the H1 heading. Helps search engines and visitors understand page context immediately.</span>
              </div>

              <!-- 3. META TITLE -->
              <div class="fg full">
                <div class="field-header-row">
                  <label for="f_meta_title"><strong><i class="fas fa-tag text-purple"></i> Meta Title (&lt;title&gt;)</strong> <span class="req">*</span></label>
                  <span class="char-pill" id="title_counter">0 / 60 chars</span>
                </div>
                <input type="text" id="f_meta_title" name="meta_title" required 
                       value="<?= e($current_page['meta_title']) ?>" 
                       placeholder="e.g. AI Education Company India | AI Courses & Certification | SSD Prayas">
                <span class="field-hint">Recommended length: <strong>50 - 60 characters</strong>. Appears as the blue clickable title in Google search results.</span>
              </div>

              <!-- 4. META DESCRIPTION -->
              <div class="fg full">
                <div class="field-header-row">
                  <label for="f_meta_desc"><strong><i class="fas fa-paragraph text-purple"></i> Meta Description</strong> <span class="req">*</span></label>
                  <span class="char-pill" id="desc_counter">0 / 160 chars</span>
                </div>
                <textarea id="f_meta_desc" name="meta_description" rows="3" required style="resize:vertical;"
                          placeholder="e.g. SSD Prayas — India's AI education company. Online AI courses, certification & beginner programmes for students, educators & professionals."><?= e($current_page['meta_description']) ?></textarea>
                <span class="field-hint">Recommended length: <strong>140 - 160 characters</strong>. The snippet displayed under the title in Google search results.</span>
              </div>

              <!-- 5. META KEYWORDS -->
              <div class="fg full">
                <div class="field-header-row">
                  <label for="f_meta_keywords"><strong><i class="fas fa-key text-amber"></i> Meta Keywords (Comma Separated)</strong></label>
                  <span class="char-pill" id="kw_counter">0 keywords</span>
                </div>
                <input type="text" id="f_meta_keywords" name="meta_keywords" 
                       value="<?= e($current_page['meta_keywords']) ?>" 
                       placeholder="ai education company, best ai learning platform india, nep 2020 ai, ai for schools">
                <div class="kw-pill-container" id="kw_preview"></div>
                <span class="field-hint">Separate keywords with commas. Used for internal search matching, indexing cues, and keyword coverage.</span>
              </div>

              <!-- 6. CANONICAL URL -->
              <div class="fg full">
                <label for="f_canonical"><strong><i class="fas fa-link text-teal"></i> Canonical URL</strong></label>
                <input type="url" id="f_canonical" name="canonical_url" 
                       value="<?= e($current_page['canonical_url'] ?: url($current_page['page_url'] === '/' ? '' : ltrim($current_page['page_url'], '/'))) ?>" 
                       placeholder="https://ssdprayas.com/...">
                <span class="field-hint">Prevents duplicate content issues and consolidates ranking signals for Google.</span>
              </div>

              <!-- Collapsible Advanced Social / Schema -->
              <details style="margin-top:10px;border:1px solid var(--line);border-radius:12px;padding:12px 16px;background:#fafbfc;" class="fg full">
                <summary style="font-weight:700;color:var(--ink);cursor:pointer;display:flex;align-items:center;gap:8px;">
                  <i class="fas fa-share-nodes text-blue"></i> Advanced: Social OpenGraph & Custom Schema
                </summary>
                <div style="margin-top:16px;display:grid;grid-template-columns:1fr;gap:14px;">
                  <div class="fg full">
                    <label>Open Graph Title (Facebook / LinkedIn)</label>
                    <input type="text" name="og_title" value="<?= e($current_page['og_title']) ?>" placeholder="Leave blank to use Meta Title">
                  </div>
                  <div class="fg full">
                    <label>Open Graph Description</label>
                    <textarea name="og_description" rows="2" placeholder="Leave blank to use Meta Description"><?= e($current_page['og_description']) ?></textarea>
                  </div>
                  <div class="fg full">
                    <label>Custom Schema LD+JSON (Optional)</label>
                    <textarea name="schema_json" rows="4" style="font-family:monospace;font-size:12px;" placeholder='<script type="application/ld+json">{ ... }</script>'><?= e($current_page['schema_json']) ?></textarea>
                  </div>
                </div>
              </details>

            </div>

            <div style="margin-top:24px;display:flex;gap:12px;align-items:center;">
              <button type="submit" class="btn btn-primary" style="padding:12px 28px;font-size:15px;">
                <i class="fas fa-floppy-disk"></i> Save Changes to Live Site
              </button>
              <a href="seo.php?tab=pages&key=<?= urlencode($current_page['page_key']) ?>" class="btn btn-ghost">Reset Changes</a>
            </div>
          </form>
        </div>
      </div>

      <!-- Right Column: Live Google SERP Card Preview -->
      <div class="serp-preview-box">
        <div class="serp-header">
          <span class="serp-badge"><i class="fab fa-google"></i> Live Google SERP Preview</span>
          <div class="serp-device-switch">
            <button type="button" class="sds-btn is-active" id="btnDesk"><i class="fas fa-desktop"></i></button>
            <button type="button" class="sds-btn" id="btnMob"><i class="fas fa-mobile-screen"></i></button>
          </div>
        </div>

        <div class="google-result-card" id="serpCard">
          <div class="gr-site-row">
            <div class="gr-favicon">
              <img src="<?= e(url('favicon.png')) ?>" alt="SSD Prayas">
            </div>
            <div class="gr-site-info">
              <div class="gr-site-name"><?= e(SITE_NAME) ?></div>
              <div class="gr-site-url" id="pvUrl"><?= e(SITE_URL . $current_page['page_url']) ?></div>
            </div>
          </div>
          <div class="gr-title" id="pvTitle"><?= e($current_page['meta_title'] ?: 'AI Education Company India – SSD Prayas') ?></div>
          <p class="gr-desc" id="pvDesc"><?= e($current_page['meta_description'] ?: 'SSD Prayas delivers NEP 2020-aligned AI skilling for schools, educators and working professionals across India.') ?></p>
        </div>

        <div style="margin-top:18px;padding-top:16px;border-top:1px solid var(--line-soft);font-size:12.5px;color:var(--muted);line-height:1.5;">
          <strong style="color:var(--ink);display:block;margin-bottom:6px;"><i class="fas fa-circle-info text-blue"></i> SEO Best Practice Checklist:</strong>
          <ul style="padding-left:18px;list-style:disc;display:grid;gap:4px;">
            <li>Keep Meta Title between <strong>50 - 60 chars</strong>.</li>
            <li>Keep Meta Description between <strong>140 - 160 chars</strong>.</li>
            <li>Place your target primary keyword in both <strong>H1 Heading</strong> and <strong>Meta Title</strong>.</li>
            <li>Changes take effect immediately on public pages without redeploying.</li>
          </ul>
        </div>
      </div>

    </div>
  <?php endif; ?>

<?php elseif ($active_tab === 'blogs'): ?>
  <!-- ========================================================
       TAB 2: BLOG POSTING SEO & ARTICLES
       ======================================================== -->

  <div class="card" style="margin-bottom:20px;">
    <div class="card-head" style="flex-wrap:wrap;gap:12px;">
      <div>
        <h2 style="font-size:18px;"><i class="fas fa-feather-pointed text-purple"></i> Blog Posting SEO Management</h2>
        <p style="font-size:12.5px;color:var(--muted);margin-top:2px;">Optimize Meta Titles, Descriptions, Keywords, H1 Headings, and Content for every blog article.</p>
      </div>
      <span class="spacer"></span>
      <div style="display:flex;gap:10px;align-items:center;">
        <button type="button" class="btn btn-secondary" id="btnOpenNewBlog">
          <i class="fas fa-plus"></i> Rapid SEO Blog Post
        </button>
        <a href="blog-form.php" class="btn btn-primary">
          <i class="fas fa-pen-nib"></i> Full Visual Blog Editor
        </a>
      </div>
    </div>

    <!-- Filter Strip -->
    <div style="padding:16px 20px;border-bottom:1px solid var(--line);background:#fafbfc;">
      <form method="GET" style="display:flex;gap:10px;align-items:center;margin:0;">
        <input type="hidden" name="tab" value="blogs">
        <div style="flex:1;position:relative;">
          <input type="text" name="q_blog" value="<?= e($q_blog) ?>" placeholder="Search blog articles by Title, Keywords, or Tag..." 
                 style="width:100%;padding:9px 14px;border:1px solid var(--line);border-radius:10px;font-size:13.5px;">
        </div>
        <button type="submit" class="btn btn-secondary"><i class="fas fa-magnifying-glass"></i> Filter</button>
        <?php if ($q_blog !== ''): ?>
          <a href="seo.php?tab=blogs" class="btn btn-ghost"><i class="fas fa-rotate-left"></i> Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="card-body" style="padding:0;">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:50px;">#</th>
              <th>Post Title / H1 Heading</th>
              <th>Meta Title & Description</th>
              <th>Keywords</th>
              <th>Status</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($blogs)): ?>
              <tr>
                <td colspan="6" style="text-align:center;padding:40px;color:var(--muted);">
                  <i class="fas fa-newspaper" style="font-size:32px;margin-bottom:10px;display:block;opacity:0.3;"></i>
                  No blog articles found. Click "Rapid SEO Blog Post" or write a new one in the visual editor.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($blogs as $i => $b): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td style="max-width:280px;">
                    <div style="font-weight:700;color:var(--ink);font-size:14px;margin-bottom:4px;line-height:1.35;">
                      <?= e($b['title']) ?>
                    </div>
                    <div style="font-size:11.5px;color:var(--muted);display:flex;align-items:center;gap:6px;">
                      <span class="badge b-blue" style="font-size:10.5px;padding:2px 6px;"><?= e($b['tag'] ?: 'Article') ?></span>
                      <code>/blog/<?= e($b['slug']) ?></code>
                    </div>
                  </td>
                  <td style="max-width:320px;">
                    <div style="font-size:13px;font-weight:600;color:var(--brand);margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                      <?= e($b['meta_title'] ?: $b['title']) ?>
                    </div>
                    <div style="font-size:12px;color:var(--muted);line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                      <?= e($b['meta_description'] ?: $b['excerpt']) ?>
                    </div>
                  </td>
                  <td style="max-width:180px;">
                    <?php if (!empty($b['meta_keywords'])): ?>
                      <div style="font-size:11px;color:var(--body);line-height:1.4;">
                        <?php 
                        $kws = array_slice(array_map('trim', explode(',', $b['meta_keywords'])), 0, 3);
                        foreach ($kws as $kw): ?>
                          <span style="background:#f1f5f9;border-radius:4px;padding:2px 5px;display:inline-block;margin:1px;font-size:10.5px;">
                            <?= e($kw) ?>
                          </span>
                        <?php endforeach; ?>
                      </div>
                    <?php else: ?>
                      <span style="font-size:11px;color:#94a3b8;font-style:italic;">No keywords set</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($b['status'] === 'published'): ?>
                      <span class="badge b-green"><i class="fas fa-circle-check"></i> Published</span>
                    <?php else: ?>
                      <span class="badge b-amber"><i class="fas fa-clock"></i> Draft</span>
                    <?php endif; ?>
                  </td>
                  <td style="text-align:right;white-space:nowrap;">
                    <button type="button" class="btn btn-secondary btn-sm btn-quick-edit-blog"
                            data-id="<?= (int)$b['id'] ?>"
                            data-title="<?= htmlspecialchars($b['title'], ENT_QUOTES) ?>"
                            data-meta-title="<?= htmlspecialchars($b['meta_title'] ?: $b['title'], ENT_QUOTES) ?>"
                            data-meta-desc="<?= htmlspecialchars($b['meta_description'] ?: $b['excerpt'], ENT_QUOTES) ?>"
                            data-keywords="<?= htmlspecialchars($b['meta_keywords'] ?? '', ENT_QUOTES) ?>"
                            data-excerpt="<?= htmlspecialchars($b['excerpt'] ?? '', ENT_QUOTES) ?>"
                            data-status="<?= e($b['status']) ?>"
                            title="Quick Edit SEO Fields">
                      <i class="fas fa-pen-to-square"></i> Edit SEO
                    </button>
                    <a href="blog-form.php?id=<?= (int)$b['id'] ?>" class="btn btn-ghost btn-sm" title="Full Visual Editor">
                      <i class="fas fa-sliders"></i> Full Editor
                    </a>
                    <a href="<?= e(url('blog/' . $b['slug'])) ?>" target="_blank" rel="noopener" class="icon-btn ib-view" title="View Public Post">
                      <i class="fas fa-arrow-up-right-from-square"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Quick Edit Blog SEO Modal -->
  <div class="seo-modal-overlay" id="editBlogModal">
    <div class="seo-modal-dialog">
      <div class="sm-head">
        <div class="sm-title"><i class="fas fa-pen-to-square text-blue"></i> Quick Edit Blog SEO & Content</div>
        <button type="button" class="sm-close" onclick="closeEditBlogModal()">&times;</button>
      </div>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save_blog_seo">
        <input type="hidden" name="blog_id" id="eb_id">

        <div class="form-grid">
          <div class="fg full">
            <label for="eb_title"><strong>Post Title / H1 Heading</strong> <span class="req">*</span></label>
            <input type="text" id="eb_title" name="title" required placeholder="Article title / H1">
          </div>
          <div class="fg full">
            <label for="eb_meta_title"><strong>Meta Title (&lt;title&gt;)</strong></label>
            <input type="text" id="eb_meta_title" name="meta_title" placeholder="Google search title">
          </div>
          <div class="fg full">
            <label for="eb_meta_desc"><strong>Meta Description</strong></label>
            <textarea id="eb_meta_desc" name="meta_description" rows="3" placeholder="Search snippet description"></textarea>
          </div>
          <div class="fg full">
            <label for="eb_keywords"><strong>Meta Keywords</strong></label>
            <input type="text" id="eb_keywords" name="meta_keywords" placeholder="keyword 1, keyword 2, keyword 3">
          </div>
          <div class="fg full">
            <label for="eb_excerpt"><strong>Excerpt / Summary</strong></label>
            <textarea id="eb_excerpt" name="excerpt" rows="2" placeholder="Brief summary of article"></textarea>
          </div>
          <div class="fg half">
            <label for="eb_status"><strong>Status</strong></label>
            <select id="eb_status" name="status">
              <option value="published">Published</option>
              <option value="draft">Draft</option>
            </select>
          </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
          <button type="button" class="btn btn-ghost" onclick="closeEditBlogModal()">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Update Blog SEO</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Create New SEO Blog Modal -->
  <div class="seo-modal-overlay" id="newBlogModal">
    <div class="seo-modal-dialog">
      <div class="sm-head">
        <div class="sm-title"><i class="fas fa-feather-pointed text-purple"></i> Rapid SEO Blog Post</div>
        <button type="button" class="sm-close" onclick="closeNewBlogModal()">&times;</button>
      </div>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create_seo_blog">

        <div class="form-grid">
          <div class="fg full">
            <label for="nb_title"><strong>Post Title / H1 Heading</strong> <span class="req">*</span></label>
            <input type="text" id="nb_title" name="title" required placeholder="Impactful headline for article">
          </div>
          <div class="fg half">
            <label for="nb_slug"><strong>URL Slug</strong></label>
            <input type="text" id="nb_slug" name="slug" placeholder="e.g. artificial-intelligence-for-schools">
          </div>
          <div class="fg half">
            <label for="nb_tag"><strong>Category / Tag</strong></label>
            <input type="text" id="nb_tag" name="tag" value="AI Education" placeholder="e.g. AI Education, NEP 2020">
          </div>
          <div class="fg full">
            <label for="nb_meta_title"><strong>Meta Title</strong></label>
            <input type="text" id="nb_meta_title" name="meta_title" placeholder="Clickable headline in Google search">
          </div>
          <div class="fg full">
            <label for="nb_meta_desc"><strong>Meta Description</strong></label>
            <textarea id="nb_meta_desc" name="meta_description" rows="2" placeholder="140-160 chars summary for Google snippet"></textarea>
          </div>
          <div class="fg full">
            <label for="nb_keywords"><strong>Meta Keywords</strong></label>
            <input type="text" id="nb_keywords" name="meta_keywords" placeholder="ai education, school coding, nep 2020">
          </div>
          <div class="fg full">
            <label for="nb_excerpt"><strong>Excerpt / Lead</strong></label>
            <textarea id="nb_excerpt" name="excerpt" rows="2" placeholder="Short introduction for article"></textarea>
          </div>
          <div class="fg full">
            <label for="nb_content"><strong>Article Body Content</strong> <span class="req">*</span></label>
            <textarea id="nb_content" name="content" rows="6" required placeholder="Write or paste your article paragraphs here (HTML supported)..."></textarea>
          </div>
          <div class="fg half">
            <label for="nb_status"><strong>Status</strong></label>
            <select id="nb_status" name="status">
              <option value="published">Publish Immediately</option>
              <option value="draft">Save as Draft</option>
            </select>
          </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
          <button type="button" class="btn btn-ghost" onclick="closeNewBlogModal()">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-rocket"></i> Publish SEO Blog</button>
        </div>
      </form>
    </div>
  </div>

<?php elseif ($active_tab === 'simulator'): ?>
  <!-- ========================================================
       TAB 3: GOOGLE SERP SIMULATOR
       ======================================================== -->
  <div class="card" style="max-width:850px;margin:0 auto;">
    <div class="card-head">
      <h2><i class="fab fa-google text-blue"></i> Google SERP Simulator & Snippet Previewer</h2>
      <span class="spacer"></span>
      <span class="badge b-blue">Desktop & Mobile Tested</span>
    </div>
    <div class="card-body">
      <p style="color:var(--muted);font-size:13.5px;margin-bottom:20px;">
        Test how your Title, URL, and Description will appear to real users searching on Google. Adjust length to prevent search engines from truncating with ellipsis ("...").
      </p>

      <div class="form-grid" style="margin-bottom:24px;">
        <div class="fg full">
          <label for="sim_title">Test Page Title</label>
          <input type="text" id="sim_title" value="<?= e($current_page['meta_title'] ?? 'AI Education Company India – AI Courses & Certification – SSD Prayas') ?>">
        </div>
        <div class="fg full">
          <label for="sim_url">Target URL Slug</label>
          <input type="text" id="sim_url" value="<?= e(SITE_URL . ($current_page['page_url'] ?? '/')) ?>">
        </div>
        <div class="fg full">
          <label for="sim_desc">Test Meta Description</label>
          <textarea id="sim_desc" rows="3"><?= e($current_page['meta_description'] ?? 'SSD Prayas — India\'s AI education company. Online AI courses, certification & beginner programmes for students, educators & professionals.') ?></textarea>
        </div>
      </div>

      <div class="google-result-card" style="margin-top:20px;border-left:4px solid #1a73e8;padding:20px;">
        <div class="gr-site-row">
          <div class="gr-favicon">
            <img src="<?= e(url('favicon.png')) ?>" alt="SSD Prayas">
          </div>
          <div class="gr-site-info">
            <div class="gr-site-name"><?= e(SITE_NAME) ?></div>
            <div class="gr-site-url" id="sim_pv_url"><?= e(SITE_URL . ($current_page['page_url'] ?? '/')) ?></div>
          </div>
        </div>
        <div class="gr-title" id="sim_pv_title" style="font-size:20px;font-weight:500;">
          <?= e($current_page['meta_title'] ?? 'AI Education Company India – AI Courses & Certification – SSD Prayas') ?>
        </div>
        <p class="gr-desc" id="sim_pv_desc" style="font-size:14px;line-height:1.55;">
          <?= e($current_page['meta_description'] ?? 'SSD Prayas — India\'s AI education company. Online AI courses, certification & beginner programmes for students, educators & professionals.') ?>
        </p>
      </div>
    </div>
  </div>

<?php elseif ($active_tab === 'audit'): ?>
  <!-- ========================================================
       TAB 4: TECHNICAL SEO CHECKLIST & SCHEMA
       ======================================================== -->
  <div class="card">
    <div class="card-head">
      <h2><i class="fas fa-list-check text-green"></i> Technical & On-Page SEO Health Matrix</h2>
      <span class="spacer"></span>
      <span class="badge b-green"><i class="fas fa-check-double"></i> Standards Compliant</span>
    </div>
    <div class="card-body">
      <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;">
        
        <div style="background:#f8fafc;border:1px solid var(--line);border-radius:14px;padding:20px;">
          <h3 style="font-size:16px;margin-bottom:10px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-sitemap text-blue"></i> XML Sitemap & Indexing
          </h3>
          <p style="font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:14px;">
            Automatically lists all public landing pages, programmes, and published blog posts with real-time lastmod timestamps.
          </p>
          <a href="<?= e(url('/sitemap.xml')) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
            <i class="fas fa-external-link"></i> Open sitemap.xml
          </a>
        </div>

        <div style="background:#f8fafc;border:1px solid var(--line);border-radius:14px;padding:20px;">
          <h3 style="font-size:16px;margin-bottom:10px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-robot text-purple"></i> Robots.txt Directives
          </h3>
          <p style="font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:14px;">
            Instructs Googlebot and other web crawlers to index all public assets while safeguarding admin files.
          </p>
          <a href="<?= e(url('/robots.txt')) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
            <i class="fas fa-external-link"></i> Open robots.txt
          </a>
        </div>

        <div style="background:#f8fafc;border:1px solid var(--line);border-radius:14px;padding:20px;">
          <h3 style="font-size:16px;margin-bottom:10px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-diagram-project text-teal"></i> Structured Data Schemas
          </h3>
          <p style="font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:14px;">
            Configured with EducationalOrganization, FAQPage, and BlogPosting schemas for rich Google search snippets.
          </p>
          <span class="badge b-green"><i class="fas fa-circle-check"></i> Active in Head Section</span>
        </div>

        <div style="background:#f8fafc;border:1px solid var(--line);border-radius:14px;padding:20px;">
          <h3 style="font-size:16px;margin-bottom:10px;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-arrow-turn-down text-amber"></i> Canonical URLs & Redirects
          </h3>
          <p style="font-size:13px;color:var(--muted);line-height:1.5;margin-bottom:14px;">
            Canonical domain enforced to prevent duplicate indexing across variations of hostnames or protocols.
          </p>
          <span class="badge b-blue"><i class="fas fa-link"></i> Canonical Tags Active</span>
        </div>

      </div>
    </div>
  </div>
<?php endif; ?>

<script>
// ==========================================
// REAL-TIME CHARACTER COUNTER & SERP PREVIEW
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const fH1 = document.getElementById('f_h1');
    const fTitle = document.getElementById('f_meta_title');
    const fDesc = document.getElementById('f_meta_desc');
    const fContent = document.getElementById('f_content');
    const fKeywords = document.getElementById('f_meta_keywords');

    const h1Counter = document.getElementById('h1_counter');
    const titleCounter = document.getElementById('title_counter');
    const descCounter = document.getElementById('desc_counter');
    const contentCounter = document.getElementById('content_counter');
    const kwCounter = document.getElementById('kw_counter');
    const kwPreview = document.getElementById('kw_preview');

    const pvTitle = document.getElementById('pvTitle');
    const pvDesc = document.getElementById('pvDesc');

    function updateCounters() {
        if (fH1 && h1Counter) {
            h1Counter.textContent = fH1.value.length + ' chars';
        }

        if (fTitle && titleCounter && pvTitle) {
            const len = fTitle.value.length;
            titleCounter.textContent = len + ' / 60 chars';
            titleCounter.className = 'char-pill ' + (len >= 40 && len <= 60 ? 'good' : (len > 60 ? 'bad' : 'warning'));
            pvTitle.textContent = fTitle.value || 'Untitled Page – SSD Prayas';
        }

        if (fDesc && descCounter && pvDesc) {
            const len = fDesc.value.length;
            descCounter.textContent = len + ' / 160 chars';
            descCounter.className = 'char-pill ' + (len >= 130 && len <= 160 ? 'good' : (len > 160 ? 'bad' : 'warning'));
            pvDesc.textContent = fDesc.value || 'No meta description provided yet.';
        }

        if (fContent && contentCounter) {
            contentCounter.textContent = fContent.value.length + ' chars';
        }

        if (fKeywords && kwCounter && kwPreview) {
            const raw = fKeywords.value.trim();
            const list = raw ? raw.split(',').map(s => s.trim()).filter(Boolean) : [];
            kwCounter.textContent = list.length + ' keywords';
            kwPreview.innerHTML = list.map(kw => '<span class="kw-tag"><i class="fas fa-tag"></i> ' + escapeHtml(kw) + '</span>').join('');
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    if (fH1) fH1.addEventListener('input', updateCounters);
    if (fTitle) fTitle.addEventListener('input', updateCounters);
    if (fDesc) fDesc.addEventListener('input', updateCounters);
    if (fContent) fContent.addEventListener('input', updateCounters);
    if (fKeywords) fKeywords.addEventListener('input', updateCounters);

    updateCounters();

    // Device switcher on SERP preview
    const btnDesk = document.getElementById('btnDesk');
    const btnMob = document.getElementById('btnMob');
    const serpCard = document.getElementById('serpCard');

    if (btnDesk && btnMob && serpCard) {
        btnDesk.addEventListener('click', function() {
            btnDesk.classList.add('is-active');
            btnMob.classList.remove('is-active');
            serpCard.style.maxWidth = '100%';
        });
        btnMob.addEventListener('click', function() {
            btnMob.classList.add('is-active');
            btnDesk.classList.remove('is-active');
            serpCard.style.maxWidth = '360px';
        });
    }

    // Simulator Tab Listeners
    const simTitle = document.getElementById('sim_title');
    const simUrl = document.getElementById('sim_url');
    const simDesc = document.getElementById('sim_desc');

    const simPvTitle = document.getElementById('sim_pv_title');
    const simPvUrl = document.getElementById('sim_pv_url');
    const simPvDesc = document.getElementById('sim_pv_desc');

    if (simTitle && simPvTitle) {
        simTitle.addEventListener('input', () => simPvTitle.textContent = simTitle.value || 'Untitled');
    }
    if (simUrl && simPvUrl) {
        simUrl.addEventListener('input', () => simPvUrl.textContent = simUrl.value || 'https://ssdprayas.com/');
    }
    if (simDesc && simPvDesc) {
        simDesc.addEventListener('input', () => simPvDesc.textContent = simDesc.value || 'Snippet preview...');
    }

    // Modal Handlers for Blog SEO Quick Edit
    document.querySelectorAll('.btn-quick-edit-blog').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('eb_id').value = this.dataset.id;
            document.getElementById('eb_title').value = this.dataset.title;
            document.getElementById('eb_meta_title').value = this.dataset.metaTitle;
            document.getElementById('eb_meta_desc').value = this.dataset.metaDesc;
            document.getElementById('eb_keywords').value = this.dataset.keywords;
            document.getElementById('eb_excerpt').value = this.dataset.excerpt;
            document.getElementById('eb_status').value = this.dataset.status;

            document.getElementById('editBlogModal').classList.add('is-open');
        });
    });

    const btnOpenNewBlog = document.getElementById('btnOpenNewBlog');
    if (btnOpenNewBlog) {
        btnOpenNewBlog.addEventListener('click', function() {
            document.getElementById('newBlogModal').classList.add('is-open');
        });
    }
});

function closeEditBlogModal() {
    document.getElementById('editBlogModal').classList.remove('is-open');
}

function closeNewBlogModal() {
    document.getElementById('newBlogModal').classList.remove('is-open');
}
</script>

<?php include __DIR__ . '/_layout_end.php'; ?>
