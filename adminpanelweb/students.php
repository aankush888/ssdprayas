<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

$action   = $_GET['action'] ?? 'list';
$id       = (int)($_GET['id'] ?? 0);
$states   = rows($pdo, "SELECT id, name FROM states WHERE is_active = 1 ORDER BY sort_order, name");
$partners = rows($pdo, "SELECT id, name FROM partners ORDER BY name");
$batches  = rows($pdo, "SELECT id, batch_code, title FROM batches WHERE audience = 'student' ORDER BY created_at DESC");

$CLASSES  = ['3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
$MODES    = ['online', 'offline', 'hybrid'];
$STATUSES = ['enrolled', 'completed', 'dropped'];

/* ---------- Save ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name'      => trim($_POST['full_name'] ?? ''),
        'class_level'    => enum_or($_POST['class_level'] ?? '', $CLASSES, ''),
        'school_name'    => trim($_POST['school_name'] ?? ''),
        'partner_id'     => nullable_int($_POST['partner_id'] ?? ''),
        'batch_id'       => nullable_int($_POST['batch_id'] ?? ''),
        'state_id'       => nullable_int($_POST['state_id'] ?? ''),
        'district'       => trim($_POST['district'] ?? ''),
        'city'           => trim($_POST['city'] ?? ''),
        'guardian_name'  => trim($_POST['guardian_name'] ?? ''),
        'guardian_phone' => trim($_POST['guardian_phone'] ?? ''),
        'email'          => trim($_POST['email'] ?? ''),
        'mode'           => enum_or($_POST['mode'] ?? '', $MODES, 'offline'),
        'is_certified'   => isset($_POST['is_certified']) ? 1 : 0,
        'certificate_id' => trim($_POST['certificate_id'] ?? ''),
        'status'         => enum_or($_POST['status'] ?? '', $STATUSES, 'enrolled'),
    ];

    if ($data['full_name'] === '') {
        flash_set('error', 'Student name is required.');
    } else {
        try {
            $edit_id = (int)($_POST['id'] ?? 0);
            if ($edit_id) {
                $sql = "UPDATE students SET " . implode(', ', array_map(fn($k) => "$k = ?", array_keys($data))) . " WHERE id = ?";
                $pdo->prepare($sql)->execute([...array_values($data), $edit_id]);
                flash_set('success', 'Student updated successfully.');
            } else {
                $cols  = implode(', ', array_keys($data));
                $marks = implode(', ', array_fill(0, count($data), '?'));
                $pdo->prepare("INSERT INTO students ($cols) VALUES ($marks)")->execute(array_values($data));
                flash_set('success', 'Student added successfully.');
            }
        } catch (PDOException $e) {
            error_log('Student save failed: ' . $e->getMessage());
            flash_set('error', 'Could not save the student. Please try again.');
        }
    }
    header('Location: students.php');
    exit;
}

/* ---------- Delete ---------- */
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);
        flash_set('success', 'Student deleted.');
    } catch (PDOException $e) {
        error_log('Student delete failed: ' . $e->getMessage());
        flash_set('error', 'Could not delete this student.');
    }
    header('Location: students.php');
    exit;
}

/* ---------- Form ---------- */
if ($action === 'new' || $action === 'edit') {
    $row = [
        'id' => 0, 'full_name' => '', 'class_level' => '', 'school_name' => '', 'partner_id' => '',
        'batch_id' => '', 'state_id' => '', 'district' => '', 'city' => '', 'guardian_name' => '',
        'guardian_phone' => '', 'email' => '', 'mode' => 'offline', 'is_certified' => 0,
        'certificate_id' => '', 'status' => 'enrolled',
    ];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        $found = $stmt->fetch();
        if (!$found) { flash_set('error', 'Student not found.'); header('Location: students.php'); exit; }
        $row = $found;
    }

    $admin_title  = $action === 'edit' ? 'Edit Student' : 'Add Student';
    $admin_active = 'students';
    $admin_sub    = 'Student enrolment records, Class 3 to 12.';
    include __DIR__ . '/_layout.php';
    ?>
    <!-- Form Hero Header -->
    <div class="mod-hero-strip">
      <div class="mod-hero-left">
        <div class="mod-hero-icon mhi-purple">
          <i class="fas fa-user-graduate"></i>
        </div>
        <div>
          <h2 class="mod-hero-title"><?= e($admin_title) ?></h2>
          <p class="mod-hero-sub">Register student enrollment data, school details, guardian contacts, and certificates.</p>
        </div>
      </div>
      <div class="mod-hero-actions">
        <a href="students.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Directory</a>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-address-card text-purple" style="font-size:16px"></i> Student Enrolment Form</h2>
        <span class="spacer"></span>
        <a href="students.php" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
      <div class="card-body">
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <div class="form-grid">

            <div class="fg">
              <label for="s-name">Student Name <span class="req">*</span></label>
              <input type="text" id="s-name" name="full_name" required value="<?= e($row['full_name']) ?>">
            </div>
            <div class="fg">
              <label for="s-class">Class</label>
              <select id="s-class" name="class_level">
                <option value="">— Select class —</option>
                <?php foreach ($CLASSES as $c): ?>
                  <option value="<?= e($c) ?>" <?= (string)$row['class_level'] === $c ? 'selected' : '' ?>>Class <?= e($c) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="s-school">School Name</label>
              <input type="text" id="s-school" name="school_name" value="<?= e($row['school_name']) ?>">
            </div>
            <div class="fg">
              <label for="s-partner">Partner Institution</label>
              <select id="s-partner" name="partner_id">
                <option value="">— None —</option>
                <?php foreach ($partners as $p): ?>
                  <option value="<?= (int)$p['id'] ?>" <?= (string)$row['partner_id'] === (string)$p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="s-batch">Batch</label>
              <select id="s-batch" name="batch_id">
                <option value="">— None —</option>
                <?php foreach ($batches as $b): ?>
                  <option value="<?= (int)$b['id'] ?>" <?= (string)$row['batch_id'] === (string)$b['id'] ? 'selected' : '' ?>>
                    <?= e($b['batch_code']) ?><?= $b['title'] ? ' — ' . e($b['title']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="s-state">State</label>
              <select id="s-state" name="state_id">
                <option value="">— Select state —</option>
                <?php foreach ($states as $s): ?>
                  <option value="<?= (int)$s['id'] ?>" <?= (string)$row['state_id'] === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="s-district">District</label>
              <input type="text" id="s-district" name="district" value="<?= e($row['district']) ?>">
            </div>
            <div class="fg">
              <label for="s-city">City</label>
              <input type="text" id="s-city" name="city" value="<?= e($row['city']) ?>">
            </div>
            <div class="fg">
              <label for="s-guardian">Guardian Name</label>
              <input type="text" id="s-guardian" name="guardian_name" value="<?= e($row['guardian_name']) ?>">
            </div>
            <div class="fg">
              <label for="s-gphone">Guardian Phone</label>
              <input type="text" id="s-gphone" name="guardian_phone" value="<?= e($row['guardian_phone']) ?>">
            </div>
            <div class="fg">
              <label for="s-email">Email</label>
              <input type="email" id="s-email" name="email" value="<?= e($row['email']) ?>">
            </div>
            <div class="fg">
              <label for="s-mode">Mode</label>
              <select id="s-mode" name="mode">
                <?php foreach ($MODES as $m): ?>
                  <option value="<?= e($m) ?>" <?= $row['mode'] === $m ? 'selected' : '' ?>><?= e(ucfirst($m)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="s-certid">Certificate ID</label>
              <input type="text" id="s-certid" name="certificate_id" value="<?= e($row['certificate_id']) ?>">
            </div>
            <div class="fg fg-check">
              <input type="checkbox" id="s-cert" name="is_certified" value="1" <?= $row['is_certified'] ? 'checked' : '' ?>>
              <label for="s-cert">Certificate issued</label>
            </div>
            <div class="fg">
              <label for="s-status">Status</label>
              <select id="s-status" name="status">
                <?php foreach ($STATUSES as $s): ?>
                  <option value="<?= e($s) ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Student</button>
            <a href="students.php" class="btn btn-ghost">Cancel</a>
          </div>
        </form>
      </div>
    </div>
    <?php
    include __DIR__ . '/_layout_end.php';
    exit;
}

/* ---------- List ---------- */
$q       = trim($_GET['q'] ?? '');
$f_state = $_GET['state'] ?? '';
$f_class = $_GET['class'] ?? '';
$f_stat  = $_GET['status'] ?? '';

$where = ['1=1'];
$args  = [];
if ($q !== '')       { $where[] = "(st.full_name LIKE ? OR st.school_name LIKE ? OR st.guardian_phone LIKE ?)";
                       array_push($args, "%$q%", "%$q%", "%$q%"); }
if ($f_state !== '') { $where[] = "st.state_id = ?";    $args[] = (int)$f_state; }
if ($f_class !== '') { $where[] = "st.class_level = ?"; $args[] = $f_class; }
if ($f_stat !== '')  { $where[] = "st.status = ?";      $args[] = $f_stat; }
$w = implode(' AND ', $where);

$per  = 50;
$pg   = max(1, (int)($_GET['page'] ?? 1));
$off  = ($pg - 1) * $per;
$tot  = (int)scalar($pdo, "SELECT COUNT(*) FROM students st WHERE $w", $args);
$pgs  = max(1, (int)ceil($tot / $per));

$list = rows($pdo, "SELECT st.*, s.name AS state_name, b.batch_code
                    FROM students st
                    LEFT JOIN states  s ON s.id = st.state_id
                    LEFT JOIN batches b ON b.id = st.batch_id
                    WHERE $w ORDER BY st.created_at DESC LIMIT $per OFFSET $off", $args);

$admin_title  = 'Students';
$admin_active = 'students';
$admin_sub    = 'Student enrolment records, Class 3 to 12.';
include __DIR__ . '/_layout.php';

$tot_enrolled  = $tot;
$tot_certified = count(array_filter($list, fn($s) => !empty($s['cert_issued'])));
$tot_active    = count(array_filter($list, fn($s) => $s['status'] === 'enrolled' || $s['status'] === 'active'));
$tot_batches   = count(array_unique(array_filter(array_column($list, 'batch_id'))));
?>

<!-- Module Hero Header -->
<div class="mod-hero-strip">
  <div class="mod-hero-left">
    <div class="mod-hero-icon mhi-purple">
      <i class="fas fa-user-graduate"></i>
    </div>
    <div>
      <h2 class="mod-hero-title">
        Student Learners Database
        <span class="mod-hero-badge"><?= number_format($tot) ?> Enrolled</span>
      </h2>
      <p class="mod-hero-sub">Track school student enrolments, training completion, and issued AI certificates.</p>
    </div>
  </div>
  <div class="mod-hero-actions">
    <a href="students.php?action=new" class="btn btn-primary"><i class="fas fa-plus"></i> Add Student</a>
  </div>
</div>

<!-- Quick Stats Strip -->
<div class="mod-stats-grid">
  <div class="mod-stat-card">
    <div class="msc-icon msc-purple"><i class="fas fa-user-graduate"></i></div>
    <div class="msc-info">
      <span class="msc-label">Total Enrolled</span>
      <div class="msc-value"><?= number_format($tot_enrolled) ?></div>
      <div class="msc-sub"><i class="fas fa-users text-purple"></i> Class 3 to 12</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-blue"><i class="fas fa-circle-check"></i></div>
    <div class="msc-info">
      <span class="msc-label">Active Learners</span>
      <div class="msc-value"><?= number_format($tot_active) ?></div>
      <div class="msc-sub"><i class="fas fa-book-open-reader text-brand"></i> In Programme</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-green"><i class="fas fa-certificate"></i></div>
    <div class="msc-info">
      <span class="msc-label">Certified</span>
      <div class="msc-value"><?= number_format($tot_certified) ?></div>
      <div class="msc-sub"><i class="fas fa-award text-green"></i> Certificate Issued</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-amber"><i class="fas fa-layer-group"></i></div>
    <div class="msc-info">
      <span class="msc-label">School Batches</span>
      <div class="msc-value"><?= number_format($tot_batches) ?></div>
      <div class="msc-sub"><i class="fas fa-school text-amber"></i> Active Groups</div>
    </div>
  </div>
</div>

<div class="card">
  <!-- Quick Tabs -->
  <div class="mod-quick-tabs">
    <a href="students.php" class="mqt-item <?= ($f_stat === '' && $q === '') ? 'is-active' : '' ?>">
      <i class="fas fa-user-graduate"></i> All Students <span class="mqt-count"><?= $tot ?></span>
    </a>
    <a href="students.php?status=completed" class="mqt-item <?= $f_stat === 'completed' ? 'is-active' : '' ?>">
      <i class="fas fa-award"></i> Completed & Certified <span class="mqt-count"><?= count(array_filter($list, fn($s) => $s['status'] === 'completed')) ?></span>
    </a>
    <a href="students.php?status=enrolled" class="mqt-item <?= $f_stat === 'enrolled' ? 'is-active' : '' ?>">
      <i class="fas fa-clock"></i> Currently Enrolled <span class="mqt-count"><?= count(array_filter($list, fn($s) => $s['status'] === 'enrolled')) ?></span>
    </a>
  </div>

  <div class="card-head">
    <h2>
      <i class="fas fa-user-graduate text-purple" style="font-size:16px"></i>
      Enrolment Directory
    </h2>
    <span class="spacer"></span>
    <form method="GET" class="filters">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, school…">
      <select name="state">
        <option value="">All states</option>
        <?php foreach ($states as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= (string)$f_state === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="class">
        <option value="">All classes</option>
        <?php foreach ($CLASSES as $c): ?>
          <option value="<?= e($c) ?>" <?= $f_class === $c ? 'selected' : '' ?>>Class <?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="status">
        <option value="">All status</option>
        <?php foreach ($STATUSES as $s): ?>
          <option value="<?= e($s) ?>" <?= $f_stat === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary btn-sm"><i class="fas fa-magnifying-glass"></i> Filter</button>
      <?php if ($q || $f_state || $f_class || $f_stat): ?>
        <a href="students.php" class="btn btn-ghost btn-sm" title="Reset Filters"><i class="fas fa-rotate-left"></i></a>
      <?php endif; ?>
    </form>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-user-graduate"></i>
      <h3>No students found</h3>
      <p>Add students as they enrol in programme batches.</p>
      <a href="students.php?action=new" class="btn btn-primary" style="margin-top:14px"><i class="fas fa-plus"></i> Add Student</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th><i class="fas fa-user-graduate" style="margin-right:6px"></i>Student</th>
            <th><i class="fas fa-graduation-cap" style="margin-right:6px"></i>Class</th>
            <th><i class="fas fa-school" style="margin-right:6px"></i>School</th>
            <th><i class="fas fa-location-dot" style="margin-right:6px"></i>Location</th>
            <th><i class="fas fa-layer-group" style="margin-right:6px"></i>Batch</th>
            <th><i class="fas fa-certificate" style="margin-right:6px"></i>Certificate</th>
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
                  <div class="entity-avatar ea-purple">
                    <?= e($initials) ?>
                  </div>
                  <div>
                    <div class="cell-main"><?= e($r['full_name']) ?></div>
                    <div class="cell-sub">
                      <?php if ($r['guardian_phone']): ?>
                        <i class="fas fa-phone" style="font-size:11px"></i> <?= e($r['guardian_phone']) ?>
                      <?php else: ?>
                        <i class="fas fa-id-badge" style="font-size:11px"></i> ID #<?= (int)$r['id'] ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </td>
              <td><?= $r['class_level'] ? '<span class="badge b-blue"><span class="badge-dot"></span>Class ' . e($r['class_level']) . '</span>' : '—' ?></td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= e($r['school_name'] ?: '—') ?></div>
              </td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= e($r['state_name'] ?: '—') ?></div>
                <div class="cell-sub"><?= e($r['district'] ?: $r['city'] ?: 'Central') ?></div>
              </td>
              <td class="cell-sub" style="font-weight:600"><?= e($r['batch_code'] ?: '—') ?></td>
              <td>
                <?php if ($r['is_certified']): ?>
                  <span class="badge b-green"><span class="badge-dot"></span>Issued</span>
                  <div class="cell-sub" style="font-family:monospace;font-size:11px"><?= e($r['certificate_id']) ?></div>
                <?php else: ?>
                  <span class="badge b-grey">Pending</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $r['status'] === 'completed' ? 'b-green' : ($r['status'] === 'enrolled' ? 'b-blue' : 'b-red') ?>">
                  <span class="badge-dot"></span><?= e(ucfirst($r['status'])) ?>
                </span>
              </td>
              <td style="text-align:right">
                <div class="row-actions" style="justify-content:flex-end">
                  <a href="students.php?action=edit&id=<?= (int)$r['id'] ?>" class="icon-btn ib-edit" title="Edit Student"><i class="fas fa-pen"></i></a>
                  <a href="students.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete student &quot;<?= e($r['full_name']) ?>&quot;?"><i class="fas fa-trash"></i></a>
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
        <span>Showing <strong><?= count($list) ?></strong> of <strong><?= number_format($tot) ?></strong> students</span>
        <span class="tfb-sync"><span class="tfb-sync-dot"></span> Records Live</span>
      </div>
      <div class="tfb-pager">
        <?php if ($pg > 1): ?>
          <a href="?page=<?= $pg - 1 ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="tfb-btn"><i class="fas fa-chevron-left"></i> Previous</a>
        <?php else: ?>
          <button class="tfb-btn disabled"><i class="fas fa-chevron-left"></i> Previous</button>
        <?php endif; ?>
        <span style="font-size:12px;font-weight:700;padding:0 8px;color:var(--ink)"><?= $pg ?> of <?= $pgs ?></span>
        <?php if ($pg < $pgs): ?>
          <a href="?page=<?= $pg + 1 ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>" class="tfb-btn">Next <i class="fas fa-chevron-right"></i></a>
        <?php else: ?>
          <button class="tfb-btn disabled">Next <i class="fas fa-chevron-right"></i></button>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Secondary Insights Grid -->
<div class="mod-bottom-grid">
  <div class="mbg-card">
    <div class="mbg-header">
      <div class="mbg-title"><i class="fas fa-chart-simple text-purple"></i> Learning & Certification Progress</div>
      <span class="mbg-tag">Metrics</span>
    </div>
    <div class="mbg-metric-row">
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Certification Completion Rate</span>
          <span class="mmr-val"><?= $tot > 0 ? round(($tot_certified / $tot) * 100) : 0 ?>% Complete</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-green" style="width: <?= $tot > 0 ? round(($tot_certified / $tot) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Active Learning Enrolment</span>
          <span class="mmr-val"><?= $tot > 0 ? round(($tot_active / $tot) * 100) : 0 ?>% In Progress</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-purple" style="width: <?= $tot > 0 ? round(($tot_active / $tot) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Target Cohort Benchmark</span>
          <span class="mmr-val"><?= number_format($tot) ?> / 500 Students</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill" style="width: <?= min(100, round(($tot / 500) * 100)) ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="mbg-card">
    <div class="mbg-header">
      <div class="mbg-title"><i class="fas fa-bolt text-amber"></i> Student Operations</div>
      <span class="mbg-tag">Shortcuts</span>
    </div>
    <div class="quick-links-grid">
      <a href="students.php?action=new" class="qlg-item">
        <div class="qlg-icon qi-purple"><i class="fas fa-user-plus"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Enrol Student</span>
          <span class="qlg-desc">New learner profile</span>
        </div>
      </a>
      <a href="batches.php" class="qlg-item">
        <div class="qlg-icon qi-blue"><i class="fas fa-layer-group"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Training Batches</span>
          <span class="qlg-desc">Assign cohorts</span>
        </div>
      </a>
      <a href="educators.php" class="qlg-item">
        <div class="qlg-icon qi-green"><i class="fas fa-chalkboard-user"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Assigned Faculty</span>
          <span class="qlg-desc">Batch trainers</span>
        </div>
      </a>
      <a href="partners.php" class="qlg-item">
        <div class="qlg-icon qi-amber"><i class="fas fa-school"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Partner Schools</span>
          <span class="qlg-desc">Host institutions</span>
        </div>
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/_layout_end.php'; ?>
