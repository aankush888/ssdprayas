<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

$action   = $_GET['action'] ?? 'list';
$id       = (int)($_GET['id'] ?? 0);
$states   = rows($pdo, "SELECT id, name FROM states WHERE is_active = 1 ORDER BY sort_order, name");
$partners = rows($pdo, "SELECT id, name FROM partners ORDER BY name");

$AUDIENCE = ['student', 'educator', 'professional'];
$LEVELS   = ['L1', 'L2', 'NA'];
$MODES    = ['online', 'offline', 'hybrid'];
$ECHECK   = ['pending', 'in_review', 'verified', 'rejected'];
$STATUSES = ['planned', 'running', 'completed', 'cancelled'];

/* ---------- Save ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'batch_code'        => trim($_POST['batch_code'] ?? ''),
        'title'             => trim($_POST['title'] ?? ''),
        'audience'          => enum_or($_POST['audience'] ?? '', $AUDIENCE, 'educator'),
        'level'             => enum_or($_POST['level'] ?? '', $LEVELS, 'L1'),
        'mode'              => enum_or($_POST['mode'] ?? '', $MODES, 'offline'),
        'state_id'          => nullable_int($_POST['state_id'] ?? ''),
        'partner_id'        => nullable_int($_POST['partner_id'] ?? ''),
        'start_date'        => ($_POST['start_date'] ?? '') !== '' ? $_POST['start_date'] : null,
        'end_date'          => ($_POST['end_date'] ?? '') !== '' ? $_POST['end_date'] : null,
        'total_seats'       => (int)($_POST['total_seats'] ?? 0),
        'echeck_status'     => enum_or($_POST['echeck_status'] ?? '', $ECHECK, 'pending'),
        'echeck_date'       => ($_POST['echeck_date'] ?? '') !== '' ? $_POST['echeck_date'] : null,
        'is_work_certified' => isset($_POST['is_work_certified']) ? 1 : 0,
        'certified_on'      => ($_POST['certified_on'] ?? '') !== '' ? $_POST['certified_on'] : null,
        'status'            => enum_or($_POST['status'] ?? '', $STATUSES, 'planned'),
        'remarks'           => trim($_POST['remarks'] ?? ''),
    ];

    if ($data['batch_code'] === '') {
        flash_set('error', 'Batch code is required.');
    } else {
        try {
            $edit_id = (int)($_POST['id'] ?? 0);
            if ($edit_id) {
                $sql = "UPDATE batches SET " . implode(', ', array_map(fn($k) => "$k = ?", array_keys($data))) . " WHERE id = ?";
                $pdo->prepare($sql)->execute([...array_values($data), $edit_id]);
                flash_set('success', 'Batch updated successfully.');
            } else {
                $cols  = implode(', ', array_keys($data));
                $marks = implode(', ', array_fill(0, count($data), '?'));
                $pdo->prepare("INSERT INTO batches ($cols) VALUES ($marks)")->execute(array_values($data));
                flash_set('success', 'Batch created successfully.');
            }
        } catch (PDOException $e) {
            error_log('Batch save failed: ' . $e->getMessage());
            $msg = str_contains($e->getMessage(), 'Duplicate')
                 ? 'That batch code already exists. Use a different code.'
                 : 'Could not save the batch. Please try again.';
            flash_set('error', $msg);
        }
    }
    header('Location: batches.php');
    exit;
}

/* ---------- Delete ---------- */
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM batches WHERE id = ?")->execute([$id]);
        flash_set('success', 'Batch deleted.');
    } catch (PDOException $e) {
        error_log('Batch delete failed: ' . $e->getMessage());
        flash_set('error', 'Could not delete this batch.');
    }
    header('Location: batches.php');
    exit;
}

/* ---------- Form ---------- */
if ($action === 'new' || $action === 'edit') {
    $row = [
        'id' => 0, 'batch_code' => '', 'title' => '', 'audience' => 'educator', 'level' => 'L1',
        'mode' => 'offline', 'state_id' => '', 'partner_id' => '', 'start_date' => '', 'end_date' => '',
        'total_seats' => 0, 'echeck_status' => 'pending', 'echeck_date' => '', 'is_work_certified' => 0,
        'certified_on' => '', 'status' => 'planned', 'remarks' => '',
    ];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM batches WHERE id = ?");
        $stmt->execute([$id]);
        $found = $stmt->fetch();
        if (!$found) { flash_set('error', 'Batch not found.'); header('Location: batches.php'); exit; }
        $row = $found;
    } else {
        $row['batch_code'] = 'SSD-' . date('Ym') . '-' . str_pad((string)random_int(1, 999), 3, '0', STR_PAD_LEFT);
    }

    $admin_title  = $action === 'edit' ? 'Edit Batch' : 'Create Batch';
    $admin_active = 'batches';
    $admin_sub    = 'L1 / L2 training batches with e-check verification and certification tracking.';
    include __DIR__ . '/_layout.php';
    ?>
    <!-- Form Hero Header -->
    <div class="mod-hero-strip">
      <div class="mod-hero-left">
        <div class="mod-hero-icon mhi-amber">
          <i class="fas fa-layer-group"></i>
        </div>
        <div>
          <h2 class="mod-hero-title"><?= e($admin_title) ?></h2>
          <p class="mod-hero-sub">Schedule training cohorts, manage seats, assign partner schools, and track e-checks.</p>
        </div>
      </div>
      <div class="mod-hero-actions">
        <a href="batches.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Batches</a>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-calendar-check text-amber" style="font-size:16px"></i> Training Cohort Details</h2>
        <span class="spacer"></span>
        <a href="batches.php" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
      <div class="card-body">
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <div class="form-grid">

            <div class="fg">
              <label for="b-code">Batch Code <span class="req">*</span></label>
              <input type="text" id="b-code" name="batch_code" required value="<?= e($row['batch_code']) ?>">
              <p class="hint">Must be unique across all batches.</p>
            </div>
            <div class="fg">
              <label for="b-title">Batch Title</label>
              <input type="text" id="b-title" name="title" value="<?= e($row['title']) ?>" placeholder="e.g. Bhopal Educator Training — Phase 1">
            </div>
            <div class="fg">
              <label for="b-audience">Audience</label>
              <select id="b-audience" name="audience">
                <?php foreach ($AUDIENCE as $a): ?>
                  <option value="<?= e($a) ?>" <?= $row['audience'] === $a ? 'selected' : '' ?>><?= e(ucfirst($a)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="b-level">Level</label>
              <select id="b-level" name="level">
                <?php foreach ($LEVELS as $l): ?>
                  <option value="<?= e($l) ?>" <?= $row['level'] === $l ? 'selected' : '' ?>><?= $l === 'NA' ? 'Not applicable' : $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="b-mode">Mode</label>
              <select id="b-mode" name="mode">
                <?php foreach ($MODES as $m): ?>
                  <option value="<?= e($m) ?>" <?= $row['mode'] === $m ? 'selected' : '' ?>><?= e(ucfirst($m)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="b-state">State</label>
              <select id="b-state" name="state_id">
                <option value="">— Select state —</option>
                <?php foreach ($states as $s): ?>
                  <option value="<?= (int)$s['id'] ?>" <?= (string)$row['state_id'] === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="b-partner">Partner Institution</label>
              <select id="b-partner" name="partner_id">
                <option value="">— None —</option>
                <?php foreach ($partners as $p): ?>
                  <option value="<?= (int)$p['id'] ?>" <?= (string)$row['partner_id'] === (string)$p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="b-seats">Total Seats</label>
              <input type="number" id="b-seats" name="total_seats" min="0" value="<?= (int)$row['total_seats'] ?>">
            </div>
            <div class="fg">
              <label for="b-start">Start Date</label>
              <input type="date" id="b-start" name="start_date" value="<?= e($row['start_date']) ?>">
            </div>
            <div class="fg">
              <label for="b-end">End Date</label>
              <input type="date" id="b-end" name="end_date" value="<?= e($row['end_date']) ?>">
            </div>
            <div class="fg">
              <label for="b-echeck">E-Check Status</label>
              <select id="b-echeck" name="echeck_status">
                <?php foreach ($ECHECK as $c): ?>
                  <option value="<?= e($c) ?>" <?= $row['echeck_status'] === $c ? 'selected' : '' ?>><?= e(ucfirst(str_replace('_', ' ', $c))) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="b-echeckdate">E-Check Date</label>
              <input type="date" id="b-echeckdate" name="echeck_date" value="<?= e($row['echeck_date']) ?>">
            </div>
            <div class="fg">
              <label for="b-certon">Certified On</label>
              <input type="date" id="b-certon" name="certified_on" value="<?= e($row['certified_on']) ?>">
            </div>
            <div class="fg fg-check">
              <input type="checkbox" id="b-work" name="is_work_certified" value="1" <?= $row['is_work_certified'] ? 'checked' : '' ?>>
              <label for="b-work">Work-certified trained batch</label>
            </div>
            <div class="fg">
              <label for="b-status">Batch Status</label>
              <select id="b-status" name="status">
                <?php foreach ($STATUSES as $s): ?>
                  <option value="<?= e($s) ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg full">
              <label for="b-remarks">Remarks</label>
              <textarea id="b-remarks" name="remarks" style="min-height:80px"><?= e($row['remarks']) ?></textarea>
            </div>

          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Batch</button>
            <a href="batches.php" class="btn btn-ghost">Cancel</a>
          </div>
        </form>
      </div>
    </div>
    <?php
    include __DIR__ . '/_layout_end.php';
    exit;
}

/* ---------- List ---------- */
$q          = trim($_GET['q'] ?? '');
$f_state    = $_GET['state'] ?? '';
$f_audience = $_GET['audience'] ?? '';
$f_status   = $_GET['status'] ?? '';

$where = ['1=1'];
$args  = [];
if ($q !== '')          { $where[] = "(b.batch_code LIKE ? OR b.title LIKE ?)"; array_push($args, "%$q%", "%$q%"); }
if ($f_state !== '')    { $where[] = "b.state_id = ?";  $args[] = (int)$f_state; }
if ($f_audience !== '') { $where[] = "b.audience = ?";  $args[] = $f_audience; }
if ($f_status !== '')   { $where[] = "b.status = ?";    $args[] = $f_status; }
$w = implode(' AND ', $where);

$list = rows($pdo, "SELECT b.*, s.name AS state_name, p.name AS partner_name,
                           (SELECT COUNT(*) FROM educators e WHERE e.batch_id = b.id) AS educator_count,
                           (SELECT COUNT(*) FROM students  st WHERE st.batch_id = b.id) AS student_count
                    FROM batches b
                    LEFT JOIN states   s ON s.id = b.state_id
                    LEFT JOIN partners p ON p.id = b.partner_id
                    WHERE $w ORDER BY b.created_at DESC", $args);

$admin_title  = 'Batches';
$admin_active = 'batches';
$admin_sub    = 'L1 / L2 training batches with e-check verification and certification tracking.';
include __DIR__ . '/_layout.php';

$tot_batches   = count($list);
$tot_running   = count(array_filter($list, fn($b) => $b['status'] === 'running'));
$tot_verified  = count(array_filter($list, fn($b) => ($b['echeck_status'] ?? '') === 'verified'));
$tot_seats     = array_sum(array_column($list, 'total_seats'));

$echeck_badge = ['pending' => 'b-grey', 'in_review' => 'b-yellow', 'verified' => 'b-green', 'rejected' => 'b-red'];
$status_badge = ['planned' => 'b-grey', 'running' => 'b-blue', 'completed' => 'b-green', 'cancelled' => 'b-red'];
?>

<!-- Module Hero Header -->
<div class="mod-hero-strip">
  <div class="mod-hero-left">
    <div class="mod-hero-icon mhi-amber">
      <i class="fas fa-layer-group"></i>
    </div>
    <div>
      <h2 class="mod-hero-title">
        Training Batches & Cohorts
        <span class="mod-hero-badge"><?= $tot_batches ?> Total</span>
      </h2>
      <p class="mod-hero-sub">Manage educator and student cohorts, e-check compliance, and official certification dates.</p>
    </div>
  </div>
  <div class="mod-hero-actions">
    <a href="batches.php?action=new" class="btn btn-primary"><i class="fas fa-plus"></i> Create Batch</a>
  </div>
</div>

<!-- Quick Stats Strip -->
<div class="mod-stats-grid">
  <div class="mod-stat-card">
    <div class="msc-icon msc-amber"><i class="fas fa-layer-group"></i></div>
    <div class="msc-info">
      <span class="msc-label">Total Batches</span>
      <div class="msc-value"><?= number_format($tot_batches) ?></div>
      <div class="msc-sub"><i class="fas fa-folder-open text-amber"></i> All Cohorts</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-blue"><i class="fas fa-spinner fa-spin-pulse"></i></div>
    <div class="msc-info">
      <span class="msc-label">Running Batches</span>
      <div class="msc-value"><?= number_format($tot_running) ?></div>
      <div class="msc-sub"><i class="fas fa-circle-play text-brand"></i> Live Training</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-green"><i class="fas fa-clipboard-check"></i></div>
    <div class="msc-info">
      <span class="msc-label">Verified E-Checks</span>
      <div class="msc-value"><?= number_format($tot_verified) ?></div>
      <div class="msc-sub"><i class="fas fa-award text-green"></i> Quality Passed</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-purple"><i class="fas fa-chair"></i></div>
    <div class="msc-info">
      <span class="msc-label">Total Seats</span>
      <div class="msc-value"><?= number_format($tot_seats) ?></div>
      <div class="msc-sub"><i class="fas fa-users-line text-purple"></i> Total Capacity</div>
    </div>
  </div>
</div>

<div class="card">
  <!-- Quick Tabs -->
  <div class="mod-quick-tabs">
    <a href="batches.php" class="mqt-item <?= ($f_status === '' && $q === '') ? 'is-active' : '' ?>">
      <i class="fas fa-layer-group"></i> All Cohorts <span class="mqt-count"><?= $tot_batches ?></span>
    </a>
    <a href="batches.php?status=running" class="mqt-item <?= $f_status === 'running' ? 'is-active' : '' ?>">
      <i class="fas fa-play"></i> Running <span class="mqt-count"><?= count(array_filter($list, fn($b) => $b['status'] === 'running')) ?></span>
    </a>
    <a href="batches.php?status=completed" class="mqt-item <?= $f_status === 'completed' ? 'is-active' : '' ?>">
      <i class="fas fa-check-double"></i> Completed <span class="mqt-count"><?= count(array_filter($list, fn($b) => $b['status'] === 'completed')) ?></span>
    </a>
  </div>

  <div class="card-head">
    <h2>
      <i class="fas fa-layer-group text-amber" style="font-size:16px"></i>
      Cohort Schedule & Management
    </h2>
    <span class="spacer"></span>
    <form method="GET" class="filters">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search code, title…">
      <select name="state">
        <option value="">All states</option>
        <?php foreach ($states as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= (string)$f_state === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="audience">
        <option value="">All audiences</option>
        <?php foreach ($AUDIENCE as $a): ?>
          <option value="<?= e($a) ?>" <?= $f_audience === $a ? 'selected' : '' ?>><?= e(ucfirst($a)) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="status">
        <option value="">All status</option>
        <?php foreach ($STATUSES as $s): ?>
          <option value="<?= e($s) ?>" <?= $f_status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary btn-sm"><i class="fas fa-magnifying-glass"></i> Filter</button>
      <?php if ($q || $f_state || $f_audience || $f_status): ?>
        <a href="batches.php" class="btn btn-ghost btn-sm" title="Reset Filters"><i class="fas fa-rotate-left"></i></a>
      <?php endif; ?>
    </form>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-layer-group"></i>
      <h3>No batches yet</h3>
      <p>Create a batch to start tracking training and certification.</p>
      <a href="batches.php?action=new" class="btn btn-primary" style="margin-top:14px"><i class="fas fa-plus"></i> Create Batch</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th><i class="fas fa-barcode" style="margin-right:6px"></i>Batch Code & Title</th>
            <th><i class="fas fa-users" style="margin-right:6px"></i>Audience</th>
            <th><i class="fas fa-location-dot" style="margin-right:6px"></i>Location & School</th>
            <th><i class="fas fa-chair" style="margin-right:6px"></i>Enrolled</th>
            <th><i class="fas fa-calendar" style="margin-right:6px"></i>Dates</th>
            <th><i class="fas fa-shield-check" style="margin-right:6px"></i>E-Check</th>
            <th><i class="fas fa-traffic-light" style="margin-right:6px"></i>Status</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr>
              <td>
                <div class="entity-cell">
                  <div class="entity-avatar ea-amber">
                    <i class="fas fa-layer-group"></i>
                  </div>
                  <div>
                    <div class="cell-main"><?= e($r['batch_code']) ?></div>
                    <div class="cell-sub"><?= e($r['title'] ?: '—') ?></div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge b-blue"><?= e(ucfirst($r['audience'])) ?></span>
                <span class="badge b-grey"><?= e($r['level']) ?></span>
                <div class="cell-sub" style="margin-top:4px"><i class="fas fa-laptop" style="font-size:11px"></i> <?= e(ucfirst($r['mode'])) ?></div>
              </td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= e($r['state_name'] ?: '—') ?></div>
                <div class="cell-sub"><?= e($r['partner_name'] ?: 'Independent') ?></div>
              </td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= (int)$r['educator_count'] ?> educators</div>
                <div class="cell-sub"><?= (int)$r['student_count'] ?> students</div>
                <?php if ($r['total_seats']): ?><div class="cell-sub">Capacity: <?= (int)$r['total_seats'] ?></div><?php endif; ?>
              </td>
              <td class="cell-sub">
                <strong><?= $r['start_date'] ? date('d M Y', strtotime($r['start_date'])) : '—' ?></strong><br>
                <?= $r['end_date'] ? 'to ' . date('d M Y', strtotime($r['end_date'])) : '' ?>
              </td>
              <td>
                <span class="badge <?= $echeck_badge[$r['echeck_status']] ?? 'b-grey' ?>">
                  <span class="badge-dot"></span><?= e(ucfirst(str_replace('_', ' ', $r['echeck_status']))) ?>
                </span>
                <?php if ($r['is_work_certified']): ?><div style="margin-top:4px"><span class="badge b-yellow"><span class="badge-dot"></span>Work certified</span></div><?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $status_badge[$r['status']] ?? 'b-grey' ?>">
                  <span class="badge-dot"></span><?= e(ucfirst($r['status'])) ?>
                </span>
              </td>
              <td style="text-align:right">
                <div class="row-actions" style="justify-content:flex-end">
                  <a href="batches.php?action=edit&id=<?= (int)$r['id'] ?>" class="icon-btn ib-edit" title="Edit Batch"><i class="fas fa-pen"></i></a>
                  <a href="batches.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete batch <?= e($r['batch_code']) ?>?"><i class="fas fa-trash"></i></a>
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
        <span>Showing <strong><?= count($list) ?></strong> of <strong><?= $tot_batches ?></strong> cohorts</span>
        <span class="tfb-sync"><span class="tfb-sync-dot"></span> Schedule Synchronized</span>
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
      <div class="mbg-title"><i class="fas fa-chart-simple text-amber"></i> Cohort Quality & Completion</div>
      <span class="mbg-tag">Performance</span>
    </div>
    <div class="mbg-metric-row">
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>E-Check Verification Rate</span>
          <span class="mmr-val"><?= $tot_batches > 0 ? round(($tot_verified / $tot_batches) * 100) : 0 ?>% Quality Cleared</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-green" style="width: <?= $tot_batches > 0 ? round(($tot_verified / $tot_batches) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Active Batch Delivery</span>
          <span class="mmr-val"><?= $tot_batches > 0 ? round(($tot_running / $tot_batches) * 100) : 0 ?>% Running Now</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-amber" style="width: <?= $tot_batches > 0 ? round(($tot_running / $tot_batches) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Seat Utilization</span>
          <span class="mmr-val"><?= number_format($tot_seats) ?> Available Seats</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-purple" style="width: 75%"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="mbg-card">
    <div class="mbg-header">
      <div class="mbg-title"><i class="fas fa-bolt text-amber"></i> Batch Management Actions</div>
      <span class="mbg-tag">Shortcuts</span>
    </div>
    <div class="quick-links-grid">
      <a href="batches.php?action=new" class="qlg-item">
        <div class="qlg-icon qi-amber"><i class="fas fa-calendar-plus"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Create Batch</span>
          <span class="qlg-desc">Launch new cohort</span>
        </div>
      </a>
      <a href="educators.php" class="qlg-item">
        <div class="qlg-icon qi-green"><i class="fas fa-chalkboard-user"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Assign Mentors</span>
          <span class="qlg-desc">Link faculty</span>
        </div>
      </a>
      <a href="students.php" class="qlg-item">
        <div class="qlg-icon qi-purple"><i class="fas fa-user-graduate"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Enrol Learners</span>
          <span class="qlg-desc">Add students</span>
        </div>
      </a>
      <a href="partners.php" class="qlg-item">
        <div class="qlg-icon qi-blue"><i class="fas fa-school"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Partner Center</span>
          <span class="qlg-desc">School venue</span>
        </div>
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/_layout_end.php'; ?>
