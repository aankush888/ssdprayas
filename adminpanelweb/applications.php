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

$tot_apps        = count($list);
$tot_new         = count(array_filter($list, fn($a) => $a['status'] === 'new'));
$tot_shortlisted = count(array_filter($list, fn($a) => in_array($a['status'], ['shortlisted', 'interview', 'hired'])));
$tot_experienced = count(array_filter($list, fn($a) => (int)$a['experience_years'] >= 3));

$admin_title  = 'Applications';
$admin_active = 'applications';
$admin_sub    = 'Educator applications submitted through the careers page.';
include __DIR__ . '/_layout.php';

$badge = ['new' => 'b-blue', 'shortlisted' => 'b-yellow', 'interview' => 'b-yellow', 'hired' => 'b-green', 'rejected' => 'b-red'];
?>

<!-- Module Hero Header -->
<div class="mod-hero-strip">
  <div class="mod-hero-left">
    <div class="mod-hero-icon mhi-blue">
      <i class="fas fa-id-card"></i>
    </div>
    <div>
      <h2 class="mod-hero-title">
        Educator Job Applications
        <span class="mod-hero-badge"><?= $tot_apps ?> Applicants</span>
      </h2>
      <p class="mod-hero-sub">Review incoming resumes, candidate qualifications, and hiring status for AI instructors.</p>
    </div>
  </div>
  <div class="mod-hero-actions">
    <a href="educators.php" class="btn btn-ghost"><i class="fas fa-chalkboard-user"></i> View Faculty</a>
  </div>
</div>

<!-- Quick Stats Strip -->
<div class="mod-stats-grid">
  <div class="mod-stat-card">
    <div class="msc-icon msc-blue"><i class="fas fa-id-card"></i></div>
    <div class="msc-info">
      <span class="msc-label">Total Applications</span>
      <div class="msc-value"><?= number_format($tot_apps) ?></div>
      <div class="msc-sub"><i class="fas fa-users text-brand"></i> All Candidates</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-green"><i class="fas fa-bell"></i></div>
    <div class="msc-info">
      <span class="msc-label">New / Pending</span>
      <div class="msc-value"><?= number_format($tot_new) ?></div>
      <div class="msc-sub"><i class="fas fa-envelope-open text-green"></i> Awaiting Review</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-amber"><i class="fas fa-user-check"></i></div>
    <div class="msc-info">
      <span class="msc-label">Shortlisted & Hired</span>
      <div class="msc-value"><?= number_format($tot_shortlisted) ?></div>
      <div class="msc-sub"><i class="fas fa-check-double text-amber"></i> In Pipeline</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-purple"><i class="fas fa-briefcase"></i></div>
    <div class="msc-info">
      <span class="msc-label">Experienced (3+ Yrs)</span>
      <div class="msc-value"><?= number_format($tot_experienced) ?></div>
      <div class="msc-sub"><i class="fas fa-star text-purple"></i> Senior Talent</div>
    </div>
  </div>
</div>

<div class="card">
  <!-- Quick Tabs -->
  <div class="mod-quick-tabs">
    <a href="applications.php" class="mqt-item <?= ($f_stat === '' && $q === '') ? 'is-active' : '' ?>">
      <i class="fas fa-inbox"></i> All Applications <span class="mqt-count"><?= $tot_apps ?></span>
    </a>
    <a href="applications.php?status=new" class="mqt-item <?= $f_stat === 'new' ? 'is-active' : '' ?>">
      <i class="fas fa-sparkles"></i> New Unread <span class="mqt-count"><?= $tot_new ?></span>
    </a>
    <a href="applications.php?status=shortlisted" class="mqt-item <?= $f_stat === 'shortlisted' ? 'is-active' : '' ?>">
      <i class="fas fa-star"></i> Shortlisted <span class="mqt-count"><?= count(array_filter($list, fn($a) => $a['status'] === 'shortlisted')) ?></span>
    </a>
  </div>

  <div class="card-head">
    <h2>
      <i class="fas fa-id-card text-brand" style="font-size:16px"></i>
      Applicant Pool
    </h2>
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
      <button class="btn btn-primary btn-sm"><i class="fas fa-magnifying-glass"></i> Filter</button>
      <?php if ($q || $f_stat || $f_mode): ?>
        <a href="applications.php" class="btn btn-ghost btn-sm" title="Reset Filters"><i class="fas fa-rotate-left"></i></a>
      <?php endif; ?>
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
          <tr>
            <th><i class="fas fa-user" style="margin-right:6px"></i>Applicant</th>
            <th><i class="fas fa-briefcase" style="margin-right:6px"></i>Position</th>
            <th><i class="fas fa-graduation-cap" style="margin-right:6px"></i>Experience & Qual</th>
            <th><i class="fas fa-location-dot" style="margin-right:6px"></i>Location</th>
            <th><i class="fas fa-laptop" style="margin-right:6px"></i>Work Mode</th>
            <th><i class="fas fa-calendar" style="margin-right:6px"></i>Applied On</th>
            <th><i class="fas fa-shield-halved" style="margin-right:6px"></i>Status</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): 
            $initials = strtoupper(substr($r['full_name'], 0, 2));
          ?>
            <tr>
              <td>
                <div class="entity-cell">
                  <div class="entity-avatar ea-blue">
                    <?= e($initials) ?>
                  </div>
                  <div>
                    <div class="cell-main"><?= e($r['full_name']) ?></div>
                    <div class="cell-sub">
                      <i class="fas fa-phone" style="font-size:11px"></i> <?= e($r['phone']) ?> · <i class="fas fa-envelope" style="font-size:11px"></i> <?= e($r['email']) ?>
                    </div>
                  </div>
                </div>
              </td>
              <td><span class="badge b-blue"><?= e($r['position']) ?></span></td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= (int)$r['experience_years'] ?> years exp</div>
                <div class="cell-sub"><?= e($r['qualification'] ?: '—') ?></div>
              </td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= e($r['state_name'] ?: '—') ?></div>
                <div class="cell-sub"><?= e($r['city'] ?: 'Regional') ?></div>
              </td>
              <td><span class="badge b-grey"><?= e(ucfirst($r['work_mode'])) ?></span></td>
              <td class="cell-sub">
                <strong><?= date('d M Y', strtotime($r['created_at'])) ?></strong><br>
                <?= date('g:i A', strtotime($r['created_at'])) ?>
              </td>
              <td>
                <span class="badge <?= $badge[$r['status']] ?? 'b-grey' ?>">
                  <span class="badge-dot"></span><?= e(ucfirst($r['status'])) ?>
                </span>
              </td>
              <td style="text-align:right">
                <div class="row-actions" style="justify-content:flex-end">
                  <a href="applications.php?id=<?= (int)$r['id'] ?>" class="icon-btn ib-view" title="Review Application"><i class="fas fa-eye"></i></a>
                  <?php if ($r['resume_path']): ?>
                    <a href="resume.php?id=<?= (int)$r['id'] ?>" target="_blank" rel="noopener" class="icon-btn ib-edit" title="Download Resume PDF"><i class="fas fa-file-pdf"></i></a>
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

    <!-- Table Footer Bar -->
    <div class="table-footer-bar">
      <div class="tfb-info">
        <span>Showing <strong><?= count($list) ?></strong> of <strong><?= $tot_apps ?></strong> applicants</span>
        <span class="tfb-sync"><span class="tfb-sync-dot"></span> Hiring Active</span>
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
      <div class="mbg-title"><i class="fas fa-chart-simple text-brand"></i> Talent Pipeline Insights</div>
      <span class="mbg-tag">Overview</span>
    </div>
    <div class="mbg-metric-row">
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Review Completion</span>
          <span class="mmr-val"><?= $tot_apps > 0 ? round((($tot_apps - $tot_new) / $tot_apps) * 100) : 100 ?>% Reviewed</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-green" style="width: <?= $tot_apps > 0 ? round((($tot_apps - $tot_new) / $tot_apps) * 100) : 100 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Shortlist Conversion Rate</span>
          <span class="mmr-val"><?= $tot_apps > 0 ? round(($tot_shortlisted / $tot_apps) * 100) : 0 ?>% Selected</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-amber" style="width: <?= $tot_apps > 0 ? round(($tot_shortlisted / $tot_apps) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Experienced Profiles (3+ yrs)</span>
          <span class="mmr-val"><?= $tot_apps > 0 ? round(($tot_experienced / $tot_apps) * 100) : 0 ?>% Senior</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-purple" style="width: <?= $tot_apps > 0 ? round(($tot_experienced / $tot_apps) * 100) : 0 ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="mbg-card">
    <div class="mbg-header">
      <div class="mbg-title"><i class="fas fa-bolt text-amber"></i> Quick Talent Actions</div>
      <span class="mbg-tag">Shortcuts</span>
    </div>
    <div class="quick-links-grid">
      <a href="educators.php" class="qlg-item">
        <div class="qlg-icon qi-green"><i class="fas fa-chalkboard-user"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Faculty List</span>
          <span class="qlg-desc">Active instructors</span>
        </div>
      </a>
      <a href="batches.php" class="qlg-item">
        <div class="qlg-icon qi-blue"><i class="fas fa-layer-group"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Training Batches</span>
          <span class="qlg-desc">Assign hired staff</span>
        </div>
      </a>
      <a href="enquiries.php" class="qlg-item">
        <div class="qlg-icon qi-purple"><i class="fas fa-inbox"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Public Enquiries</span>
          <span class="qlg-desc">Contact submissions</span>
        </div>
      </a>
      <a href="<?= e(url('/careers')) ?>" target="_blank" class="qlg-item">
        <div class="qlg-icon qi-amber"><i class="fas fa-arrow-up-right-from-square"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Careers Page</span>
          <span class="qlg-desc">Live public listing</span>
        </div>
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/_layout_end.php'; ?>
