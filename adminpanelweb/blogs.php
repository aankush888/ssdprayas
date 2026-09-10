<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

if (($_GET['action'] ?? '') === 'delete' && ($id = (int)($_GET['id'] ?? 0))) {
    try {
        $pdo->prepare("DELETE FROM blogs WHERE id = ?")->execute([$id]);
        flash_set('success', 'Blog post deleted.');
    } catch (PDOException $e) {
        error_log('Blog delete failed: ' . $e->getMessage());
        flash_set('error', 'Could not delete this post.');
    }
    header('Location: blogs.php');
    exit;
}

$q      = trim($_GET['q'] ?? '');
$f_stat = $_GET['status'] ?? '';

$where = ['1=1'];
$args  = [];
if ($q !== '')      { $where[] = "(title LIKE ? OR tag LIKE ?)"; array_push($args, "%$q%", "%$q%"); }
if ($f_stat !== '') { $where[] = "status = ?"; $args[] = $f_stat; }
$w = implode(' AND ', $where);

$list = rows($pdo, "SELECT * FROM blogs WHERE $w ORDER BY created_at DESC", $args);

$tot_posts     = count($list);
$tot_published = count(array_filter($list, fn($b) => $b['status'] === 'published'));
$tot_drafts    = count(array_filter($list, fn($b) => $b['status'] === 'draft'));
$tot_views     = array_sum(array_column($list, 'views'));

$admin_title  = 'Blogs';
$admin_active = 'blogs';
$admin_sub    = 'Articles published on the SSD Prayas website.';
include __DIR__ . '/_layout.php';
?>

<!-- Module Hero Header -->
<div class="mod-hero-strip">
  <div class="mod-hero-left">
    <div class="mod-hero-icon mhi-purple">
      <i class="fas fa-newspaper"></i>
    </div>
    <div>
      <h2 class="mod-hero-title">
        News & Editorial Articles
        <span class="mod-hero-badge"><?= $tot_posts ?> Articles</span>
      </h2>
      <p class="mod-hero-sub">Publish educational stories, AI curriculum announcements, and event updates.</p>
    </div>
  </div>
  <div class="mod-hero-actions">
    <a href="blog-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Write Post</a>
  </div>
</div>

<!-- Quick Stats Strip -->
<div class="mod-stats-grid">
  <div class="mod-stat-card">
    <div class="msc-icon msc-purple"><i class="fas fa-newspaper"></i></div>
    <div class="msc-info">
      <span class="msc-label">Total Articles</span>
      <div class="msc-value"><?= number_format($tot_posts) ?></div>
      <div class="msc-sub"><i class="fas fa-pen-nib text-purple"></i> All Stories</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-green"><i class="fas fa-globe"></i></div>
    <div class="msc-info">
      <span class="msc-label">Live Published</span>
      <div class="msc-value"><?= number_format($tot_published) ?></div>
      <div class="msc-sub"><i class="fas fa-circle-check text-green"></i> Visible on Portal</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-amber"><i class="fas fa-file-pen"></i></div>
    <div class="msc-info">
      <span class="msc-label">Drafts</span>
      <div class="msc-value"><?= number_format($tot_drafts) ?></div>
      <div class="msc-sub"><i class="fas fa-clock text-amber"></i> Work in Progress</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-blue"><i class="fas fa-eye"></i></div>
    <div class="msc-info">
      <span class="msc-label">Reader Views</span>
      <div class="msc-value"><?= number_format($tot_views) ?></div>
      <div class="msc-sub"><i class="fas fa-chart-line text-brand"></i> Total Engagement</div>
    </div>
  </div>
</div>

<div class="card">
  <!-- Quick Tabs -->
  <div class="mod-quick-tabs">
    <a href="blogs.php" class="mqt-item <?= ($f_stat === '' && $q === '') ? 'is-active' : '' ?>">
      <i class="fas fa-newspaper"></i> All Posts <span class="mqt-count"><?= $tot_posts ?></span>
    </a>
    <a href="blogs.php?status=published" class="mqt-item <?= $f_stat === 'published' ? 'is-active' : '' ?>">
      <i class="fas fa-circle-check"></i> Published <span class="mqt-count"><?= $tot_published ?></span>
    </a>
    <a href="blogs.php?status=draft" class="mqt-item <?= $f_stat === 'draft' ? 'is-active' : '' ?>">
      <i class="fas fa-clock"></i> Drafts <span class="mqt-count"><?= $tot_drafts ?></span>
    </a>
  </div>

  <div class="card-head">
    <h2>
      <i class="fas fa-pen-fancy text-purple" style="font-size:16px"></i>
      Article Catalogue
    </h2>
    <span class="spacer"></span>
    <form method="GET" class="filters">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search title, tag…">
      <select name="status">
        <option value="">All</option>
        <option value="published" <?= $f_stat === 'published' ? 'selected' : '' ?>>Published</option>
        <option value="draft"     <?= $f_stat === 'draft' ? 'selected' : '' ?>>Draft</option>
      </select>
      <button class="btn btn-primary btn-sm"><i class="fas fa-magnifying-glass"></i> Filter</button>
      <?php if ($q || $f_stat): ?>
        <a href="blogs.php" class="btn btn-ghost btn-sm" title="Reset Filters"><i class="fas fa-rotate-left"></i></a>
      <?php endif; ?>
    </form>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-pen-nib"></i>
      <h3>No posts yet</h3>
      <p>Write your first article — share achievements, tutorials, and milestones.</p>
      <a href="blog-form.php" class="btn btn-primary" style="margin-top:14px"><i class="fas fa-plus"></i> Write Post</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th><i class="fas fa-book-open" style="margin-right:6px"></i>Post Title & URL</th>
            <th><i class="fas fa-tag" style="margin-right:6px"></i>Tag</th>
            <th><i class="fas fa-eye" style="margin-right:6px"></i>Views</th>
            <th><i class="fas fa-shield-halved" style="margin-right:6px"></i>Status</th>
            <th><i class="fas fa-calendar" style="margin-right:6px"></i>Date Published</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr>
              <td>
                <div class="entity-cell">
                  <div class="entity-avatar ea-purple">
                    <i class="fas fa-newspaper"></i>
                  </div>
                  <div>
                    <div class="cell-main"><?= e($r['title']) ?></div>
                    <div class="cell-sub">/blog/<?= e($r['slug']) ?></div>
                  </div>
                </div>
              </td>
              <td><span class="badge b-blue"><?= e($r['tag'] ?: 'Article') ?></span></td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= number_format($r['views']) ?></div>
                <div class="cell-sub">reads</div>
              </td>
              <td>
                <span class="badge <?= $r['status'] === 'published' ? 'b-green' : 'b-yellow' ?>">
                  <span class="badge-dot"></span><?= e(ucfirst($r['status'])) ?>
                </span>
              </td>
              <td class="cell-sub">
                <strong><?= date('d M Y', strtotime($r['created_at'])) ?></strong><br>
                <?= date('g:i A', strtotime($r['created_at'])) ?>
              </td>
              <td style="text-align:right">
                <div class="row-actions" style="justify-content:flex-end">
                  <a href="<?= e(url('blog/' . $r['slug'])) ?>" target="_blank" rel="noopener" class="icon-btn ib-view" title="Read on Public Site"><i class="fas fa-arrow-up-right-from-square"></i></a>
                  <a href="blog-form.php?id=<?= (int)$r['id'] ?>" class="icon-btn ib-edit" title="Edit Article"><i class="fas fa-pen"></i></a>
                  <a href="blogs.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete &quot;<?= e($r['title']) ?>&quot;?"><i class="fas fa-trash"></i></a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Table Footer Bar -->
    <div class="table-footer-bar">
      <div class="tfb-info">
        <span>Showing <strong><?= count($list) ?></strong> of <strong><?= $tot_posts ?></strong> articles</span>
        <span class="tfb-sync"><span class="tfb-sync-dot"></span> CMS Live</span>
      </div>
      <div class="tfb-pager">
        <button class="tfb-btn disabled"><i class="fas fa-chevron-left"></i> Previous</button>
        <span style="font-size:12px;font-weight:700;padding:0 8px;color:var(--ink)">1 of 1</span>
        <button class="tfb-btn disabled">Next <i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Secondary Insights Grid -->
<div class="mod-bottom-grid">
  <div class="mbg-card">
    <div class="mbg-header">
      <div class="mbg-title"><i class="fas fa-chart-simple text-purple"></i> Content Publication Ratio</div>
      <span class="mbg-tag">Insights</span>
    </div>
    <div class="mbg-metric-row">
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Publish Rate</span>
          <span class="mmr-val"><?= $tot_posts > 0 ? round(($tot_published / $tot_posts) * 100) : 0 ?>% Published</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-green" style="width: <?= $tot_posts > 0 ? round(($tot_published / $tot_posts) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Draft Articles Pending</span>
          <span class="mmr-val"><?= $tot_drafts ?> Drafts</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-amber" style="width: <?= $tot_posts > 0 ? round(($tot_drafts / $tot_posts) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Average Readers / Story</span>
          <span class="mmr-val"><?= $tot_posts > 0 ? number_format(round($tot_views / $tot_posts)) : 0 ?> Views</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-purple" style="width: 80%"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="mbg-card">
    <div class="mbg-header">
      <div class="mbg-title"><i class="fas fa-bolt text-amber"></i> Quick Editorial Actions</div>
      <span class="mbg-tag">Shortcuts</span>
    </div>
    <div class="quick-links-grid">
      <a href="blog-form.php" class="qlg-item">
        <div class="qlg-icon qi-purple"><i class="fas fa-pen-nib"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">New Article</span>
          <span class="qlg-desc">Compose story</span>
        </div>
      </a>
      <a href="<?= e(url('/blog')) ?>" target="_blank" class="qlg-item">
        <div class="qlg-icon qi-blue"><i class="fas fa-globe"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Blog Feed</span>
          <span class="qlg-desc">Public reader view</span>
        </div>
      </a>
      <a href="enquiries.php" class="qlg-item">
        <div class="qlg-icon qi-green"><i class="fas fa-inbox"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Enquiries</span>
          <span class="qlg-desc">Reader feedback</span>
        </div>
      </a>
      <a href="settings.php" class="qlg-item">
        <div class="qlg-icon qi-amber"><i class="fas fa-gear"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Site Settings</span>
          <span class="qlg-desc">Admin configs</span>
        </div>
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/_layout_end.php'; ?>
