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
    <div class="card">
      <div class="card-head">
        <h2><?= e($admin_title) ?></h2>
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
?>

<div class="card">
  <div class="card-head">
    <h2>Student Database <span class="badge b-grey"><?= number_format($tot) ?></span></h2>
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
      <button class="btn btn-ghost btn-sm"><i class="fas fa-magnifying-glass"></i></button>
    </form>
    <a href="students.php?action=new" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Student</a>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-user-graduate"></i>
      <h3>No students found</h3>
      <p>Add students as they enrol in programme batches.</p>
      <a href="students.php?action=new" class="btn btn-primary"><i class="fas fa-plus"></i> Add Student</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>Student</th><th>Class</th><th>School</th><th>Location</th><th>Batch</th><th>Certificate</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr>
              <td>
                <div class="cell-main"><?= e($r['full_name']) ?></div>
                <div class="cell-sub"><?= e($r['guardian_phone'] ?: $r['email'] ?: '—') ?></div>
              </td>
              <td><?= $r['class_level'] ? '<span class="badge b-blue">Class ' . e($r['class_level']) . '</span>' : '—' ?></td>
              <td class="cell-sub"><?= e($r['school_name'] ?: '—') ?></td>
              <td>
                <div><?= e($r['state_name'] ?: '—') ?></div>
                <div class="cell-sub"><?= e($r['district'] ?: $r['city'] ?: '') ?></div>
              </td>
              <td class="cell-sub"><?= e($r['batch_code'] ?: '—') ?></td>
              <td>
                <?php if ($r['is_certified']): ?>
                  <span class="badge b-green">Issued</span>
                  <div class="cell-sub"><?= e($r['certificate_id']) ?></div>
                <?php else: ?>
                  <span class="badge b-grey">Pending</span>
                <?php endif; ?>
              </td>
              <td><span class="badge <?= $r['status'] === 'completed' ? 'b-green' : ($r['status'] === 'enrolled' ? 'b-blue' : 'b-red') ?>"><?= e(ucfirst($r['status'])) ?></span></td>
              <td>
                <div class="row-actions">
                  <a href="students.php?action=edit&id=<?= (int)$r['id'] ?>" class="icon-btn ib-edit" title="Edit"><i class="fas fa-pen"></i></a>
                  <a href="students.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete student &quot;<?= e($r['full_name']) ?>&quot;?"><i class="fas fa-trash"></i></a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($pgs > 1): ?>
      <div class="pager">
        <?php for ($i = 1; $i <= $pgs; $i++): ?>
          <a href="?page=<?= $i ?><?= $q !== '' ? '&q=' . urlencode($q) : '' ?>"
             class="btn btn-sm <?= $i === $pg ? 'btn-primary' : 'btn-ghost' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/_layout_end.php'; ?>
