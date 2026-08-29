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
    <div class="card">
      <div class="card-head">
        <h2><?= e($admin_title) ?></h2>
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

$echeck_badge = ['pending' => 'b-grey', 'in_review' => 'b-yellow', 'verified' => 'b-green', 'rejected' => 'b-red'];
$status_badge = ['planned' => 'b-grey', 'running' => 'b-blue', 'completed' => 'b-green', 'cancelled' => 'b-red'];
?>

<div class="card">
  <div class="card-head">
    <h2>Training Batches <span class="badge b-grey"><?= count($list) ?></span></h2>
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
      <button class="btn btn-ghost btn-sm"><i class="fas fa-magnifying-glass"></i></button>
    </form>
    <a href="batches.php?action=new" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Create Batch</a>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-layer-group"></i>
      <h3>No batches yet</h3>
      <p>Create a batch to start tracking training and certification.</p>
      <a href="batches.php?action=new" class="btn btn-primary"><i class="fas fa-plus"></i> Create Batch</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>Batch</th><th>Audience</th><th>Location</th><th>Enrolled</th><th>Dates</th><th>E-Check</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr>
              <td>
                <div class="cell-main"><?= e($r['batch_code']) ?></div>
                <div class="cell-sub"><?= e($r['title'] ?: '—') ?></div>
              </td>
              <td>
                <span class="badge b-blue"><?= e(ucfirst($r['audience'])) ?></span>
                <span class="badge b-grey"><?= e($r['level']) ?></span>
                <div class="cell-sub" style="margin-top:4px"><?= e(ucfirst($r['mode'])) ?></div>
              </td>
              <td>
                <div><?= e($r['state_name'] ?: '—') ?></div>
                <div class="cell-sub"><?= e($r['partner_name'] ?: '') ?></div>
              </td>
              <td>
                <div class="cell-sub"><?= (int)$r['educator_count'] ?> educators</div>
                <div class="cell-sub"><?= (int)$r['student_count'] ?> students</div>
                <?php if ($r['total_seats']): ?><div class="cell-sub">of <?= (int)$r['total_seats'] ?> seats</div><?php endif; ?>
              </td>
              <td class="cell-sub">
                <?= $r['start_date'] ? date('d M Y', strtotime($r['start_date'])) : '—' ?><br>
                <?= $r['end_date'] ? 'to ' . date('d M Y', strtotime($r['end_date'])) : '' ?>
              </td>
              <td>
                <span class="badge <?= $echeck_badge[$r['echeck_status']] ?? 'b-grey' ?>"><?= e(ucfirst(str_replace('_', ' ', $r['echeck_status']))) ?></span>
                <?php if ($r['is_work_certified']): ?><div style="margin-top:4px"><span class="badge b-yellow">Work certified</span></div><?php endif; ?>
              </td>
              <td><span class="badge <?= $status_badge[$r['status']] ?? 'b-grey' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
              <td>
                <div class="row-actions">
                  <a href="batches.php?action=edit&id=<?= (int)$r['id'] ?>" class="icon-btn ib-edit" title="Edit"><i class="fas fa-pen"></i></a>
                  <a href="batches.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete batch <?= e($r['batch_code']) ?>?"><i class="fas fa-trash"></i></a>
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
