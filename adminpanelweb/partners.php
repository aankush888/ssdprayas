<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);
$states = rows($pdo, "SELECT id, name FROM states WHERE is_active = 1 ORDER BY sort_order, name");

$TYPES    = ['school', 'government', 'institute', 'corporate', 'ngo', 'other'];
$STATUSES = ['active', 'pending', 'inactive'];

/* ---------- Save ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'           => trim($_POST['name'] ?? ''),
        'partner_type'   => enum_or($_POST['partner_type'] ?? '', $TYPES, 'school'),
        'state_id'       => nullable_int($_POST['state_id'] ?? ''),
        'district'       => trim($_POST['district'] ?? ''),
        'city'           => trim($_POST['city'] ?? ''),
        'contact_person' => trim($_POST['contact_person'] ?? ''),
        'phone'          => trim($_POST['phone'] ?? ''),
        'email'          => trim($_POST['email'] ?? ''),
        'address'        => trim($_POST['address'] ?? ''),
        'schools_count'  => (int)($_POST['schools_count'] ?? 0),
        'students_count' => (int)($_POST['students_count'] ?? 0),
        'mou_signed'     => isset($_POST['mou_signed']) ? 1 : 0,
        'mou_date'       => ($_POST['mou_date'] ?? '') !== '' ? $_POST['mou_date'] : null,
        'status'         => enum_or($_POST['status'] ?? '', $STATUSES, 'active'),
        'notes'          => trim($_POST['notes'] ?? ''),
    ];

    if ($data['name'] === '') {
        flash_set('error', 'Partner name is required.');
    } else {
        try {
            $edit_id = (int)($_POST['id'] ?? 0);
            if ($edit_id) {
                $sql = "UPDATE partners SET " . implode(', ', array_map(fn($k) => "$k = ?", array_keys($data))) . " WHERE id = ?";
                $pdo->prepare($sql)->execute([...array_values($data), $edit_id]);
                flash_set('success', 'Partner updated successfully.');
            } else {
                $cols = implode(', ', array_keys($data));
                $marks = implode(', ', array_fill(0, count($data), '?'));
                $pdo->prepare("INSERT INTO partners ($cols) VALUES ($marks)")->execute(array_values($data));
                flash_set('success', 'Partner added successfully.');
            }
        } catch (PDOException $e) {
            error_log('Partner save failed: ' . $e->getMessage());
            flash_set('error', 'Could not save the partner. Please try again.');
        }
    }
    header('Location: partners.php');
    exit;
}

/* ---------- Delete ---------- */
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM partners WHERE id = ?")->execute([$id]);
        flash_set('success', 'Partner deleted.');
    } catch (PDOException $e) {
        error_log('Partner delete failed: ' . $e->getMessage());
        flash_set('error', 'Could not delete — this partner is still linked to batches or records.');
    }
    header('Location: partners.php');
    exit;
}

/* ---------- Form ---------- */
if ($action === 'new' || $action === 'edit') {
    $row = [
        'id' => 0, 'name' => '', 'partner_type' => 'school', 'state_id' => '', 'district' => '',
        'city' => '', 'contact_person' => '', 'phone' => '', 'email' => '', 'address' => '',
        'schools_count' => 0, 'students_count' => 0, 'mou_signed' => 0, 'mou_date' => '',
        'status' => 'active', 'notes' => '',
    ];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM partners WHERE id = ?");
        $stmt->execute([$id]);
        $found = $stmt->fetch();
        if (!$found) { flash_set('error', 'Partner not found.'); header('Location: partners.php'); exit; }
        $row = $found;
    }

    $admin_title  = $action === 'edit' ? 'Edit Partner' : 'Add Partner';
    $admin_active = 'partners';
    $admin_sub    = 'Schools, institutes and government departments partnered with SSD Prayas.';
    include __DIR__ . '/_layout.php';
    ?>
    <div class="card">
      <div class="card-head">
        <h2><?= e($admin_title) ?></h2>
        <span class="spacer"></span>
        <a href="partners.php" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
      <div class="card-body">
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <div class="form-grid">

            <div class="fg full">
              <label for="p-name">Partner Name <span class="req">*</span></label>
              <input type="text" id="p-name" name="name" required value="<?= e($row['name']) ?>" placeholder="e.g. Govt. Higher Secondary School, Kolar">
            </div>

            <div class="fg">
              <label for="p-type">Partner Type</label>
              <select id="p-type" name="partner_type">
                <?php foreach ($TYPES as $t): ?>
                  <option value="<?= e($t) ?>" <?= $row['partner_type'] === $t ? 'selected' : '' ?>><?= e(ucfirst($t)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="fg">
              <label for="p-state">State</label>
              <select id="p-state" name="state_id">
                <option value="">— Select state —</option>
                <?php foreach ($states as $s): ?>
                  <option value="<?= (int)$s['id'] ?>" <?= (string)$row['state_id'] === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="fg">
              <label for="p-district">District</label>
              <input type="text" id="p-district" name="district" value="<?= e($row['district']) ?>">
            </div>

            <div class="fg">
              <label for="p-city">City</label>
              <input type="text" id="p-city" name="city" value="<?= e($row['city']) ?>">
            </div>

            <div class="fg">
              <label for="p-person">Contact Person</label>
              <input type="text" id="p-person" name="contact_person" value="<?= e($row['contact_person']) ?>">
            </div>

            <div class="fg">
              <label for="p-phone">Phone</label>
              <input type="text" id="p-phone" name="phone" value="<?= e($row['phone']) ?>">
            </div>

            <div class="fg">
              <label for="p-email">Email</label>
              <input type="email" id="p-email" name="email" value="<?= e($row['email']) ?>">
            </div>

            <div class="fg">
              <label for="p-status">Status</label>
              <select id="p-status" name="status">
                <?php foreach ($STATUSES as $s): ?>
                  <option value="<?= e($s) ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="fg">
              <label for="p-schools">Schools Covered</label>
              <input type="number" id="p-schools" name="schools_count" min="0" value="<?= (int)$row['schools_count'] ?>">
            </div>

            <div class="fg">
              <label for="p-students">Students Covered</label>
              <input type="number" id="p-students" name="students_count" min="0" value="<?= (int)$row['students_count'] ?>">
            </div>

            <div class="fg">
              <label for="p-moudate">MoU Date</label>
              <input type="date" id="p-moudate" name="mou_date" value="<?= e($row['mou_date']) ?>">
            </div>

            <div class="fg fg-check">
              <input type="checkbox" id="p-mou" name="mou_signed" value="1" <?= $row['mou_signed'] ? 'checked' : '' ?>>
              <label for="p-mou">MoU signed</label>
            </div>

            <div class="fg full">
              <label for="p-address">Address</label>
              <textarea id="p-address" name="address" style="min-height:80px"><?= e($row['address']) ?></textarea>
            </div>

            <div class="fg full">
              <label for="p-notes">Internal Notes</label>
              <textarea id="p-notes" name="notes" style="min-height:80px"><?= e($row['notes']) ?></textarea>
            </div>

          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Partner</button>
            <a href="partners.php" class="btn btn-ghost">Cancel</a>
          </div>
        </form>
      </div>
    </div>
    <?php
    include __DIR__ . '/_layout_end.php';
    exit;
}

/* ---------- List ---------- */
$q        = trim($_GET['q'] ?? '');
$f_state  = $_GET['state'] ?? '';
$f_status = $_GET['status'] ?? '';

$where = ['1=1'];
$args  = [];
if ($q !== '')        { $where[] = "(p.name LIKE ? OR p.city LIKE ? OR p.district LIKE ? OR p.contact_person LIKE ?)";
                        array_push($args, "%$q%", "%$q%", "%$q%", "%$q%"); }
if ($f_state !== '')  { $where[] = "p.state_id = ?";  $args[] = (int)$f_state; }
if ($f_status !== '') { $where[] = "p.status = ?";    $args[] = $f_status; }
$w = implode(' AND ', $where);

$list = rows($pdo, "SELECT p.*, s.name AS state_name
                    FROM partners p LEFT JOIN states s ON s.id = p.state_id
                    WHERE $w ORDER BY p.created_at DESC", $args);

$admin_title  = 'Partners';
$admin_active = 'partners';
$admin_sub    = 'Schools, institutes and government departments partnered with SSD Prayas.';
include __DIR__ . '/_layout.php';
?>

<div class="card">
  <div class="card-head">
    <h2>Partner Database <span class="badge b-grey"><?= count($list) ?></span></h2>
    <span class="spacer"></span>
    <form method="GET" class="filters">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, city…">
      <select name="state">
        <option value="">All states</option>
        <?php foreach ($states as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= (string)$f_state === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
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
    <a href="partners.php?action=new" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Partner</a>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-handshake"></i>
      <h3>No partners found</h3>
      <p>Add your first partner school, institute or department.</p>
      <a href="partners.php?action=new" class="btn btn-primary"><i class="fas fa-plus"></i> Add Partner</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>Partner</th><th>Type</th><th>Location</th><th>Coverage</th><th>MoU</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr>
              <td>
                <div class="cell-main"><?= e($r['name']) ?></div>
                <div class="cell-sub"><?= e($r['contact_person'] ?: '—') ?><?= $r['phone'] ? ' · ' . e($r['phone']) : '' ?></div>
              </td>
              <td><span class="badge b-blue"><?= e(ucfirst($r['partner_type'])) ?></span></td>
              <td>
                <div><?= e($r['state_name'] ?: '—') ?></div>
                <div class="cell-sub"><?= e($r['district'] ?: $r['city'] ?: '') ?></div>
              </td>
              <td>
                <div class="cell-sub"><?= number_format($r['schools_count']) ?> schools</div>
                <div class="cell-sub"><?= number_format($r['students_count']) ?> students</div>
              </td>
              <td>
                <?php if ($r['mou_signed']): ?>
                  <span class="badge b-green">Signed</span>
                <?php else: ?>
                  <span class="badge b-grey">Pending</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $r['status'] === 'active' ? 'b-green' : ($r['status'] === 'pending' ? 'b-yellow' : 'b-grey') ?>">
                  <?= e(ucfirst($r['status'])) ?>
                </span>
              </td>
              <td>
                <div class="row-actions">
                  <a href="partners.php?action=edit&id=<?= (int)$r['id'] ?>" class="icon-btn ib-edit" title="Edit"><i class="fas fa-pen"></i></a>
                  <a href="partners.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete partner &quot;<?= e($r['name']) ?>&quot;? This cannot be undone."><i class="fas fa-trash"></i></a>
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
