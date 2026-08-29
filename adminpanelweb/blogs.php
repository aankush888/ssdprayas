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

$admin_title  = 'Blogs';
$admin_active = 'blogs';
$admin_sub    = 'Articles published on the SSD Prayas website.';
include __DIR__ . '/_layout.php';
?>

<div class="card">
  <div class="card-head">
    <h2>Blog Posts <span class="badge b-grey"><?= count($list) ?></span></h2>
    <span class="spacer"></span>
    <form method="GET" class="filters">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search title, tag…">
      <select name="status">
        <option value="">All</option>
        <option value="published" <?= $f_stat === 'published' ? 'selected' : '' ?>>Published</option>
        <option value="draft"     <?= $f_stat === 'draft' ? 'selected' : '' ?>>Draft</option>
      </select>
      <button class="btn btn-ghost btn-sm"><i class="fas fa-magnifying-glass"></i></button>
    </form>
    <a href="blog-form.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Write Post</a>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-pen-nib"></i>
      <h3>No posts yet</h3>
      <p>Write your first article — the AI writer can draft it for you.</p>
      <a href="blog-form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Write Post</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>Post</th><th>Tag</th><th>Views</th><th>Status</th><th>Date</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr>
              <td>
                <div class="cell-flex">
                  <img src="<?= e(url($r['image'])) ?>" class="thumb" alt="">
                  <div>
                    <div class="cell-main"><?= e($r['title']) ?></div>
                    <div class="cell-sub">/blog/<?= e($r['slug']) ?></div>
                  </div>
                </div>
              </td>
              <td><span class="badge b-blue"><?= e($r['tag']) ?></span></td>
              <td class="cell-sub"><?= number_format($r['views']) ?></td>
              <td><span class="badge <?= $r['status'] === 'published' ? 'b-green' : 'b-yellow' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
              <td class="cell-sub"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
              <td>
                <div class="row-actions">
                  <a href="<?= e(url('blog/' . $r['slug'])) ?>" target="_blank" rel="noopener" class="icon-btn ib-view" title="View"><i class="fas fa-eye"></i></a>
                  <a href="blog-form.php?id=<?= (int)$r['id'] ?>" class="icon-btn ib-edit" title="Edit"><i class="fas fa-pen"></i></a>
                  <a href="blogs.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete &quot;<?= e($r['title']) ?>&quot;?"><i class="fas fa-trash"></i></a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/_layout_end.php'; ?>
