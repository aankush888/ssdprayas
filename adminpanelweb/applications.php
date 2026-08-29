<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

$STATUSES = ['new', 'shortlisted', 'interview', 'hired', 'rejected'];
$states   = rows($pdo, "SELECT id, name FROM states WHERE is_active = 1 ORDER BY sort_order, name");

/* ---------- Status change ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'status') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = enum_or($_POST['status'] ?? '', $STATUSES, 'new');
    try {
        $pdo->prepare("UPDATE job_applications SET status = ? WHERE id = ?")->execute([$status, $id]);
        flash_set('success', 'Application status updated.');
    } catch (PDOException $e) {
        error_log('Application status update failed: ' . $e->getMessage());
        flash_set('error', 'Could not update the status.');
    }
    header('Location: applications.php' . (!empty($_POST['back']) ? '?id=' . $id : ''));
    exit;
}

/* ---------- Delete ---------- */
if (($_GET['action'] ?? '') === 'delete' && ($id = (int)($_GET['id'] ?? 0))) {
    try {
        $stmt = $pdo->prepare("SELECT resume_path FROM job_applications WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetchColumn();

        $pdo->prepare("DELETE FROM job_applications WHERE id = ?")->execute([$id]);

        // Remove the stored resume too, but only from inside the resumes folder.
        if ($old) {
            $base = realpath(UPLOAD_RESUMES);
            $file = realpath(__DIR__ . '/../' . $old);
            if ($base && $file && strncmp($file, $base, strlen($base)) === 0 && is_file($file)) {
                unlink($file);
            }
        }
        flash_set('success', 'Application deleted.');
    } catch (PDOException $e) {
        error_log('Application delete failed: ' . $e->getMessage());
        flash_set('error', 'Could not delete this application.');
    }
    header('Location: applications.php');
    exit;
}

/* ---------- Single view ---------- */
$view_id = (int)($_GET['id'] ?? 0);
if ($view_id) {
    $stmt = $pdo->prepare("SELECT a.*, s.name AS state_name
                           FROM job_applications a LEFT JOIN states s ON s.id = a.state_id
                           WHERE a.id = ?");
    $stmt->execute([$view_id]);
    $a = $stmt->fetch();
    if (!$a) { flash_set('error', 'Application not found.'); header('Location: applications.php'); exit; }

    $admin_title  = 'Application — ' . $a['full_name'];
    $admin_active = 'applications';
    $admin_sub    = 'Educator application received from the careers page.';
    include __DIR__ . '/_layout.php';
    ?>
    <div class="card">
      <div class="card-head">
        <h2><?= e($a['full_name']) ?></h2>
        <span class="badge b-blue"><?= e($a['position']) ?></span>
        <span class="spacer"></span>
        <?php if ($a['resume_path']): ?>
          <a href="resume.php?id=<?= (int)$a['id'] ?>" target="_blank" rel="noopener" class="btn btn-primary btn-sm">
            <i class="fas fa-file-arrow-down"></i> Open Resume
          </a>
        <?php endif; ?>
        <a href="applications.php" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
      <div class="card-body">
        <dl class="detail-list">
          <div><dt>Phone</dt><dd><a href="tel:<?= e($a['phone']) ?>"><?= e($a['phone']) ?></a></dd></div>
          <div><dt>Email</dt><dd><a href="mailto:<?= e($a['email']) ?>"><?= e($a['email']) ?></a></dd></div>
          <div><dt>State</dt><dd><?= e($a['state_name'] ?: '—') ?></dd></div>
          <div><dt>City / District</dt><dd><?= e($a['city'] ?: '—') ?></dd></div>
          <div><dt>Qualification</dt><dd><?= e($a['qualification'] ?: '—') ?></dd></div>
          <div><dt>Experience</dt><dd><?= (int)$a['experience_years'] ?> years</dd></div>
          <div><dt>Subject Expertise</dt><dd><?= e($a['subject_expertise'] ?: '—') ?></dd></div>
          <div><dt>Preferred Mode</dt><dd><?= e(ucfirst($a['work_mode'])) ?></dd></div>
          <div><dt>Applied On</dt><dd><?= date('d M Y, g:i A', strtotime($a['created_at'])) ?></dd></div>
        </dl>

        <?php if ($a['cover_note']): ?>
          <h3 style="margin:28px 0 10px;font-size:15px">Why they want to teach AI</h3>
          <p style="background:var(--bg);padding:18px;border-radius:12px"><?= nl2br(e($a['cover_note'])) ?></p>
        <?php endif; ?>

        <form method="POST" style="margin-top:28px;display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
          <?= csrf_field() ?>
          <input type="hidden" name="do" value="status">
          <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
          <input type="hidden" name="back" value="1">
          <div class="fg" style="margin:0">
            <label for="st">Hiring Status</label>
            <select id="st" name="status">
              <?php foreach ($STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= $a['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-primary"><i class="fas fa-check"></i> Update Status</button>
        </form>
      </div>
    </div>
    <?php
    include __DIR__ . '/_layout_end.php';
    exit;
}

/* ---------- List ---------- */
$q      = trim($_GET['q'] ?? '');
$f_stat = $_GET['status'] ?? '';
$f_mode = $_GET['mode'] ?? '';

$where = ['1=1'];
$args  = [];
if ($q !== '')      { $where[] = "(a.full_name LIKE ? OR a.email LIKE ? OR a.phone LIKE ?)";
                      array_push($args, "%$q%", "%$q%", "%$q%"); }
if ($f_stat !== '') { $where[] = "a.status = ?";    $args[] = $f_stat; }
if ($f_mode !== '') { $where[] = "a.work_mode = ?"; $args[] = $f_mode; }
$w = implode(' AND ', $where);

$list = rows($pdo, "SELECT a.*, s.name AS state_name
                    FROM job_applications a LEFT JOIN states s ON s.id = a.state_id
                    WHERE $w ORDER BY a.created_at DESC", $args);

$admin_title  = 'Applications';
$admin_active = 'applications';
$admin_sub    = 'Educator applications submitted through the careers page.';
include __DIR__ . '/_layout.php';

$badge = ['new' => 'b-blue', 'shortlisted' => 'b-yellow', 'interview' => 'b-yellow', 'hired' => 'b-green', 'rejected' => 'b-red'];
?>

<div class="card">
  <div class="card-head">
    <h2>Educator Applications <span class="badge b-grey"><?= count($list) ?></span></h2>
    <span class="spacer"></span>
    <form method="GET" class="filters">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, email…">
      <select name="status">
        <option value="">All status</option>
        <?php foreach ($STATUSES as $s): ?>
          <option value="<?= e($s) ?>" <?= $f_stat === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="mode">
        <option value="">All modes</option>
        <?php foreach (['hybrid', 'offline', 'online'] as $m): ?>
          <option value="<?= e($m) ?>" <?= $f_mode === $m ? 'selected' : '' ?>><?= e(ucfirst($m)) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-ghost btn-sm"><i class="fas fa-magnifying-glass"></i></button>
    </form>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-file-lines"></i>
      <h3>No applications yet</h3>
      <p>Applications from the careers page will land here.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>Applicant</th><th>Position</th><th>Experience</th><th>Location</th><th>Mode</th><th>Applied</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr>
              <td>
                <div class="cell-main"><?= e($r['full_name']) ?></div>
                <div class="cell-sub"><?= e($r['phone']) ?> · <?= e($r['email']) ?></div>
              </td>
              <td><span class="badge b-blue"><?= e($r['position']) ?></span></td>
              <td>
                <div><?= (int)$r['experience_years'] ?> yrs</div>
                <div class="cell-sub"><?= e($r['qualification'] ?: '') ?></div>
              </td>
              <td>
                <div><?= e($r['state_name'] ?: '—') ?></div>
                <div class="cell-sub"><?= e($r['city'] ?: '') ?></div>
              </td>
              <td><span class="badge b-grey"><?= e(ucfirst($r['work_mode'])) ?></span></td>
              <td class="cell-sub"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
              <td><span class="badge <?= $badge[$r['status']] ?? 'b-grey' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
              <td>
                <div class="row-actions">
                  <a href="applications.php?id=<?= (int)$r['id'] ?>" class="icon-btn ib-view" title="View"><i class="fas fa-eye"></i></a>
                  <?php if ($r['resume_path']): ?>
                    <a href="resume.php?id=<?= (int)$r['id'] ?>" target="_blank" rel="noopener" class="icon-btn ib-edit" title="Resume"><i class="fas fa-file-pdf"></i></a>
                  <?php endif; ?>
                  <a href="applications.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete this application and its resume?"><i class="fas fa-trash"></i></a>
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
