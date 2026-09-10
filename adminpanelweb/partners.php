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
    <!-- Form Hero Header -->
    <div class="mod-hero-strip">
      <div class="mod-hero-left">
        <div class="mod-hero-icon mhi-blue">
          <i class="fas fa-school"></i>
        </div>
        <div>
          <h2 class="mod-hero-title"><?= e($admin_title) ?></h2>
          <p class="mod-hero-sub">Register and manage school details, district coverage, and official MoU agreements.</p>
        </div>
      </div>
      <div class="mod-hero-actions">
        <a href="partners.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Directory</a>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-file-pen text-brand" style="font-size:16px"></i> Institution Information Form</h2>
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

$total_partners = count($list);
$govt_partners  = count(array_filter($list, fn($p) => $p['partner_type'] === 'government'));
$total_students = array_sum(array_column($list, 'students_count'));
$mou_signed     = count(array_filter($list, fn($p) => !empty($p['mou_signed'])));
?>

<!-- Module Hero Header -->
<div class="mod-hero-strip">
  <div class="mod-hero-left">
    <div class="mod-hero-icon mhi-blue">
      <i class="fas fa-handshake"></i>
    </div>
    <div>
      <h2 class="mod-hero-title">
        Partner Network & Institutions
        <span class="mod-hero-badge"><?= $total_partners ?> Registered</span>
      </h2>
      <p class="mod-hero-sub">Manage affiliated schools, colleges, state government departments, and signed MoUs.</p>
    </div>
  </div>
  <div class="mod-hero-actions">
    <a href="partners.php?action=new" class="btn btn-primary"><i class="fas fa-plus"></i> Add Partner</a>
  </div>
</div>

<!-- Quick Stats Strip -->
<div class="mod-stats-grid">
  <div class="mod-stat-card">
    <div class="msc-icon msc-blue"><i class="fas fa-handshake"></i></div>
    <div class="msc-info">
      <span class="msc-label">Total Partners</span>
      <div class="msc-value"><?= number_format($total_partners) ?></div>
      <div class="msc-sub"><i class="fas fa-school text-brand"></i> Active Institutions</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-green"><i class="fas fa-building-columns"></i></div>
    <div class="msc-info">
      <span class="msc-label">Govt Partners</span>
      <div class="msc-value"><?= number_format($govt_partners) ?></div>
      <div class="msc-sub"><i class="fas fa-landmark text-green"></i> State Schools</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-purple"><i class="fas fa-user-graduate"></i></div>
    <div class="msc-info">
      <span class="msc-label">Student Reach</span>
      <div class="msc-value"><?= number_format($total_students) ?></div>
      <div class="msc-sub"><i class="fas fa-users text-purple"></i> Covered Learners</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-amber"><i class="fas fa-file-contract"></i></div>
    <div class="msc-info">
      <span class="msc-label">MoUs Signed</span>
      <div class="msc-value"><?= number_format($mou_signed) ?></div>
      <div class="msc-sub"><i class="fas fa-check-circle text-amber"></i> Official Agreements</div>
    </div>
  </div>
</div>

<div class="card">
  <!-- Quick Tabs -->
  <div class="mod-quick-tabs">
    <a href="partners.php" class="mqt-item <?= ($f_status === '' && $q === '') ? 'is-active' : '' ?>">
      <i class="fas fa-list-check"></i> All Partners <span class="mqt-count"><?= $total_partners ?></span>
    </a>
    <a href="partners.php?status=active" class="mqt-item <?= $f_status === 'active' ? 'is-active' : '' ?>">
      <i class="fas fa-circle-check"></i> Active <span class="mqt-count"><?= count(array_filter($list, fn($p) => $p['status'] === 'active')) ?></span>
    </a>
    <a href="partners.php?status=pending" class="mqt-item <?= $f_status === 'pending' ? 'is-active' : '' ?>">
      <i class="fas fa-clock"></i> Pending <span class="mqt-count"><?= count(array_filter($list, fn($p) => $p['status'] === 'pending')) ?></span>
    </a>
  </div>

  <div class="card-head">
    <h2>
      <i class="fas fa-database text-brand" style="font-size:16px"></i>
      Institution Directory
    </h2>
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
      <button class="btn btn-primary btn-sm"><i class="fas fa-magnifying-glass"></i> Filter</button>
      <?php if ($q || $f_state || $f_status): ?>
        <a href="partners.php" class="btn btn-ghost btn-sm" title="Reset Filters"><i class="fas fa-rotate-left"></i></a>
      <?php endif; ?>
    </form>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-handshake"></i>
      <h3>No partners found</h3>
      <p>Add your first partner school, institute or government department.</p>
      <a href="partners.php?action=new" class="btn btn-primary" style="margin-top:14px"><i class="fas fa-plus"></i> Add Partner</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th><i class="fas fa-school" style="margin-right:6px"></i>Partner Institution</th>
            <th><i class="fas fa-tag" style="margin-right:6px"></i>Type</th>
            <th><i class="fas fa-location-dot" style="margin-right:6px"></i>Location</th>
            <th><i class="fas fa-chart-pie" style="margin-right:6px"></i>Coverage</th>
            <th><i class="fas fa-file-signature" style="margin-right:6px"></i>MoU Status</th>
            <th><i class="fas fa-shield-halved" style="margin-right:6px"></i>Status</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr>
              <td>
                <div class="entity-cell">
                  <div class="entity-avatar ea-blue">
                    <i class="fas fa-school"></i>
                  </div>
                  <div>
                    <div class="cell-main"><?= e($r['name']) ?></div>
                    <div class="cell-sub">
                      <?php if ($r['contact_person']): ?>
                        <i class="fas fa-user" style="font-size:11px"></i> <?= e($r['contact_person']) ?>
                      <?php endif; ?>
                      <?php if ($r['phone']): ?>
                        · <i class="fas fa-phone" style="font-size:11px"></i> <?= e($r['phone']) ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </td>
              <td><span class="badge b-blue"><?= e(ucfirst($r['partner_type'])) ?></span></td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= e($r['state_name'] ?: '—') ?></div>
                <div class="cell-sub"><?= e($r['district'] ?: $r['city'] ?: 'Regional') ?></div>
              </td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= number_format($r['schools_count']) ?> school<?= $r['schools_count'] > 1 ? 's' : '' ?></div>
                <div class="cell-sub"><?= number_format($r['students_count']) ?> students enrolled</div>
              </td>
              <td>
                <?php if ($r['mou_signed']): ?>
                  <span class="badge b-green"><span class="badge-dot"></span>Signed</span>
                <?php else: ?>
                  <span class="badge b-yellow"><span class="badge-dot"></span>In Discussion</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $r['status'] === 'active' ? 'b-green' : ($r['status'] === 'pending' ? 'b-yellow' : 'b-grey') ?>">
                  <span class="badge-dot"></span><?= e(ucfirst($r['status'])) ?>
                </span>
              </td>
              <td style="text-align:right">
                <div class="row-actions" style="justify-content:flex-end">
                  <a href="partners.php?action=edit&id=<?= (int)$r['id'] ?>" class="icon-btn ib-edit" title="Edit Partner"><i class="fas fa-pen"></i></a>
                  <a href="partners.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete partner &quot;<?= e($r['name']) ?>&quot;? This cannot be undone."><i class="fas fa-trash"></i></a>
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
        <span>Showing <strong><?= count($list) ?></strong> of <strong><?= $total_partners ?></strong> institutions</span>
        <span class="tfb-sync"><span class="tfb-sync-dot"></span> Network Active</span>
      </div>
      <div class="tfb-pager">
        <button class="tfb-btn disabled"><i class="fas fa-chevron-left"></i> Previous</button>
        <span style="font-size:12px;font-weight:700;padding:0 8px;color:var(--ink)">1 of 1</span>
        <button class="tfb-btn disabled">Next <i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  <?php endif; ?>
</div>

<!-- Secondary Insights Grid to fill viewport with valuable data -->
<div class="mod-bottom-grid">
  <div class="mbg-card">
    <div class="mbg-header">
      <div class="mbg-title"><i class="fas fa-chart-simple text-brand"></i> Partnership Distribution</div>
      <span class="mbg-tag">Live Metrics</span>
    </div>
    <div class="mbg-metric-row">
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>MoU Formalization</span>
          <span class="mmr-val"><?= $total_partners > 0 ? round(($mou_signed / $total_partners) * 100) : 0 ?>% Complete</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-green" style="width: <?= $total_partners > 0 ? round(($mou_signed / $total_partners) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Government Sector Share</span>
          <span class="mmr-val"><?= $total_partners > 0 ? round(($govt_partners / $total_partners) * 100) : 0 ?>% Govt Schools</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-purple" style="width: <?= $total_partners > 0 ? round(($govt_partners / $total_partners) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Student Coverage Target</span>
          <span class="mmr-val"><?= number_format($total_students) ?> / 1,000 Target</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill" style="width: <?= min(100, round(($total_students / 1000) * 100)) ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="mbg-card">
    <div class="mbg-header">
      <div class="mbg-title"><i class="fas fa-bolt text-amber"></i> Quick Partner Operations</div>
      <span class="mbg-tag">Shortcuts</span>
    </div>
    <div class="quick-links-grid">
      <a href="partners.php?action=new" class="qlg-item">
        <div class="qlg-icon qi-blue"><i class="fas fa-plus"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Add Partner</span>
          <span class="qlg-desc">Register new school</span>
        </div>
      </a>
      <a href="batches.php?action=new" class="qlg-item">
        <div class="qlg-icon qi-green"><i class="fas fa-calendar-plus"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Assign Cohort</span>
          <span class="qlg-desc">Create training batch</span>
        </div>
      </a>
      <a href="students.php" class="qlg-item">
        <div class="qlg-icon qi-purple"><i class="fas fa-user-graduate"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">View Students</span>
          <span class="qlg-desc">Enrolled learners</span>
        </div>
      </a>
      <a href="educators.php" class="qlg-item">
        <div class="qlg-icon qi-amber"><i class="fas fa-chalkboard-user"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Faculty List</span>
          <span class="qlg-desc">Assigned AI mentors</span>
        </div>
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/_layout_end.php'; ?>
