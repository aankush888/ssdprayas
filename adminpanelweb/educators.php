<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

$action   = $_GET['action'] ?? 'list';
$id       = (int)($_GET['id'] ?? 0);
$states   = rows($pdo, "SELECT id, name FROM states WHERE is_active = 1 ORDER BY sort_order, name");
$partners = rows($pdo, "SELECT id, name FROM partners ORDER BY name");
$batches  = rows($pdo, "SELECT id, batch_code, title FROM batches WHERE audience = 'educator' ORDER BY created_at DESC");

$LEVELS   = ['L1', 'L2', 'NA'];
$MODES    = ['online', 'offline', 'hybrid'];
$STATUSES = ['active', 'training', 'inactive'];
$GENDERS  = ['male', 'female', 'other'];

/* ---------- Save ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name'         => trim($_POST['full_name'] ?? ''),
        'email'             => trim($_POST['email'] ?? ''),
        'phone'             => trim($_POST['phone'] ?? ''),
        'gender'            => in_array($_POST['gender'] ?? '', $GENDERS, true) ? $_POST['gender'] : null,
        'state_id'          => nullable_int($_POST['state_id'] ?? ''),
        'district'          => trim($_POST['district'] ?? ''),
        'city'              => trim($_POST['city'] ?? ''),
        'qualification'     => trim($_POST['qualification'] ?? ''),
        'subject_expertise' => trim($_POST['subject_expertise'] ?? ''),
        'experience_years'  => (int)($_POST['experience_years'] ?? 0),
        'partner_id'        => nullable_int($_POST['partner_id'] ?? ''),
        'batch_id'          => nullable_int($_POST['batch_id'] ?? ''),
        'level'             => enum_or($_POST['level'] ?? '', $LEVELS, 'L1'),
        'gemini_certified'  => isset($_POST['gemini_certified']) ? 1 : 0,
        'gemini_cert_id'    => trim($_POST['gemini_cert_id'] ?? ''),
        'gemini_cert_date'  => ($_POST['gemini_cert_date'] ?? '') !== '' ? $_POST['gemini_cert_date'] : null,
        'is_work_certified' => isset($_POST['is_work_certified']) ? 1 : 0,
        'mode'              => enum_or($_POST['mode'] ?? '', $MODES, 'offline'),
        'status'            => enum_or($_POST['status'] ?? '', $STATUSES, 'training'),
        'notes'             => trim($_POST['notes'] ?? ''),
    ];

    if ($data['full_name'] === '') {
        flash_set('error', 'Educator name is required.');
    } else {
        try {
            $edit_id = (int)($_POST['id'] ?? 0);
            if ($edit_id) {
                $sql = "UPDATE educators SET " . implode(', ', array_map(fn($k) => "$k = ?", array_keys($data))) . " WHERE id = ?";
                $pdo->prepare($sql)->execute([...array_values($data), $edit_id]);
                flash_set('success', 'Educator updated successfully.');
            } else {
                $cols  = implode(', ', array_keys($data));
                $marks = implode(', ', array_fill(0, count($data), '?'));
                $pdo->prepare("INSERT INTO educators ($cols) VALUES ($marks)")->execute(array_values($data));
                flash_set('success', 'Educator added successfully.');
            }
        } catch (PDOException $e) {
            error_log('Educator save failed: ' . $e->getMessage());
            flash_set('error', 'Could not save the educator. Please try again.');
        }
    }
    header('Location: educators.php');
    exit;
}

/* ---------- Delete ---------- */
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM educators WHERE id = ?")->execute([$id]);
        flash_set('success', 'Educator deleted.');
    } catch (PDOException $e) {
        error_log('Educator delete failed: ' . $e->getMessage());
        flash_set('error', 'Could not delete this educator.');
    }
    header('Location: educators.php');
    exit;
}

/* ---------- Form ---------- */
if ($action === 'new' || $action === 'edit') {
    $row = [
        'id' => 0, 'full_name' => '', 'email' => '', 'phone' => '', 'gender' => '', 'state_id' => '',
        'district' => '', 'city' => '', 'qualification' => '', 'subject_expertise' => '',
        'experience_years' => 0, 'partner_id' => '', 'batch_id' => '', 'level' => 'L1',
        'gemini_certified' => 0, 'gemini_cert_id' => '', 'gemini_cert_date' => '',
        'is_work_certified' => 0, 'mode' => 'offline', 'status' => 'training', 'notes' => '',
    ];
    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM educators WHERE id = ?");
        $stmt->execute([$id]);
        $found = $stmt->fetch();
        if (!$found) { flash_set('error', 'Educator not found.'); header('Location: educators.php'); exit; }
        $row = $found;
    }

    $admin_title  = $action === 'edit' ? 'Edit Educator' : 'Add Educator';
    $admin_active = 'educators';
    $admin_sub    = 'Gemini-certified educator database with L1 / L2 training levels.';
    include __DIR__ . '/_layout.php';
    ?>
    <!-- Form Hero Header -->
    <div class="mod-hero-strip">
      <div class="mod-hero-left">
        <div class="mod-hero-icon mhi-green">
          <i class="fas fa-chalkboard-user"></i>
        </div>
        <div>
          <h2 class="mod-hero-title"><?= e($admin_title) ?></h2>
          <p class="mod-hero-sub">Register AI mentor details, Gemini certification credentials, and batch assignments.</p>
        </div>
      </div>
      <div class="mod-hero-actions">
        <a href="educators.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back to Faculty Roster</a>
      </div>
    </div>

    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-id-card-clip text-green" style="font-size:16px"></i> Educator Profile Form</h2>
        <span class="spacer"></span>
        <a href="educators.php" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
      <div class="card-body">
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <div class="form-grid">

            <div class="fg">
              <label for="e-name">Full Name <span class="req">*</span></label>
              <input type="text" id="e-name" name="full_name" required value="<?= e($row['full_name']) ?>">
            </div>
            <div class="fg">
              <label for="e-phone">Phone</label>
              <input type="text" id="e-phone" name="phone" value="<?= e($row['phone']) ?>">
            </div>
            <div class="fg">
              <label for="e-email">Email</label>
              <input type="email" id="e-email" name="email" value="<?= e($row['email']) ?>">
            </div>
            <div class="fg">
              <label for="e-gender">Gender</label>
              <select id="e-gender" name="gender">
                <option value="">— Select —</option>
                <?php foreach ($GENDERS as $g): ?>
                  <option value="<?= e($g) ?>" <?= $row['gender'] === $g ? 'selected' : '' ?>><?= e(ucfirst($g)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="e-state">State</label>
              <select id="e-state" name="state_id">
                <option value="">— Select state —</option>
                <?php foreach ($states as $s): ?>
                  <option value="<?= (int)$s['id'] ?>" <?= (string)$row['state_id'] === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="e-district">District</label>
              <input type="text" id="e-district" name="district" value="<?= e($row['district']) ?>">
            </div>
            <div class="fg">
              <label for="e-city">City</label>
              <input type="text" id="e-city" name="city" value="<?= e($row['city']) ?>">
            </div>
            <div class="fg">
              <label for="e-qual">Qualification</label>
              <input type="text" id="e-qual" name="qualification" value="<?= e($row['qualification']) ?>" placeholder="e.g. B.Ed, M.Sc">
            </div>
            <div class="fg">
              <label for="e-subject">Subject Expertise</label>
              <input type="text" id="e-subject" name="subject_expertise" value="<?= e($row['subject_expertise']) ?>">
            </div>
            <div class="fg">
              <label for="e-exp">Experience (years)</label>
              <input type="number" id="e-exp" name="experience_years" min="0" max="60" value="<?= (int)$row['experience_years'] ?>">
            </div>
            <div class="fg">
              <label for="e-partner">Partner Institution</label>
              <select id="e-partner" name="partner_id">
                <option value="">— None —</option>
                <?php foreach ($partners as $p): ?>
                  <option value="<?= (int)$p['id'] ?>" <?= (string)$row['partner_id'] === (string)$p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="e-batch">Training Batch</label>
              <select id="e-batch" name="batch_id">
                <option value="">— None —</option>
                <?php foreach ($batches as $b): ?>
                  <option value="<?= (int)$b['id'] ?>" <?= (string)$row['batch_id'] === (string)$b['id'] ? 'selected' : '' ?>>
                    <?= e($b['batch_code']) ?><?= $b['title'] ? ' — ' . e($b['title']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="e-level">Training Level</label>
              <select id="e-level" name="level">
                <?php foreach ($LEVELS as $l): ?>
                  <option value="<?= e($l) ?>" <?= $row['level'] === $l ? 'selected' : '' ?>><?= $l === 'NA' ? 'Not applicable' : $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="e-mode">Mode</label>
              <select id="e-mode" name="mode">
                <?php foreach ($MODES as $m): ?>
                  <option value="<?= e($m) ?>" <?= $row['mode'] === $m ? 'selected' : '' ?>><?= e(ucfirst($m)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg">
              <label for="e-certid">Gemini Certificate ID</label>
              <input type="text" id="e-certid" name="gemini_cert_id" value="<?= e($row['gemini_cert_id']) ?>">
            </div>
            <div class="fg">
              <label for="e-certdate">Gemini Certification Date</label>
              <input type="date" id="e-certdate" name="gemini_cert_date" value="<?= e($row['gemini_cert_date']) ?>">
            </div>
            <div class="fg fg-check">
              <input type="checkbox" id="e-gemini" name="gemini_certified" value="1" <?= $row['gemini_certified'] ? 'checked' : '' ?>>
              <label for="e-gemini">Gemini certified educator</label>
            </div>
            <div class="fg fg-check">
              <input type="checkbox" id="e-work" name="is_work_certified" value="1" <?= $row['is_work_certified'] ? 'checked' : '' ?>>
              <label for="e-work">Work-certified (trained batch)</label>
            </div>
            <div class="fg">
              <label for="e-status">Status</label>
              <select id="e-status" name="status">
                <?php foreach ($STATUSES as $s): ?>
                  <option value="<?= e($s) ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fg full">
              <label for="e-notes">Notes</label>
              <textarea id="e-notes" name="notes" style="min-height:80px"><?= e($row['notes']) ?></textarea>
            </div>

          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-floppy-disk"></i> Save Educator</button>
            <a href="educators.php" class="btn btn-ghost">Cancel</a>
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
$f_level  = $_GET['level'] ?? '';
$f_cert   = $_GET['cert'] ?? '';

$where = ['1=1'];
$args  = [];
if ($q !== '')       { $where[] = "(e.full_name LIKE ? OR e.email LIKE ? OR e.phone LIKE ? OR e.city LIKE ?)";
                       array_push($args, "%$q%", "%$q%", "%$q%", "%$q%"); }
if ($f_state !== '') { $where[] = "e.state_id = ?"; $args[] = (int)$f_state; }
if ($f_level !== '') { $where[] = "e.level = ?";    $args[] = $f_level; }
if ($f_cert === '1') { $where[] = "e.gemini_certified = 1"; }
if ($f_cert === '0') { $where[] = "e.gemini_certified = 0"; }
$w = implode(' AND ', $where);

$list = rows($pdo, "SELECT e.*, s.name AS state_name, p.name AS partner_name, b.batch_code
                    FROM educators e
                    LEFT JOIN states   s ON s.id = e.state_id
                    LEFT JOIN partners p ON p.id = e.partner_id
                    LEFT JOIN batches  b ON b.id = e.batch_id
                    WHERE $w ORDER BY e.created_at DESC", $args);

$total_faculty = count($list);
$total_active  = count(array_filter($list, fn($e) => $e['status'] === 'active'));
$total_levels  = count(array_filter($list, fn($e) => in_array($e['level'], ['L1', 'L2'])));
$total_cert    = count(array_filter($list, fn($e) => !empty($e['gemini_certified'])));

$admin_title  = 'Educators';
$admin_active = 'educators';
$admin_sub    = 'Gemini-certified educator database with L1 / L2 training levels.';
include __DIR__ . '/_layout.php';
?>

<!-- Module Hero Header -->
<div class="mod-hero-strip">
  <div class="mod-hero-left">
    <div class="mod-hero-icon mhi-green">
      <i class="fas fa-chalkboard-user"></i>
    </div>
    <div>
      <h2 class="mod-hero-title">
        Educators & Master Trainers
        <span class="mod-hero-badge"><?= $total_faculty ?> Registered</span>
      </h2>
      <p class="mod-hero-sub">AI educators, certified Google Gemini instructors, and school batch mentors.</p>
    </div>
  </div>
  <div class="mod-hero-actions">
    <a href="educators.php?action=new" class="btn btn-primary"><i class="fas fa-plus"></i> Add Educator</a>
  </div>
</div>

<!-- Quick Stats Strip -->
<div class="mod-stats-grid">
  <div class="mod-stat-card">
    <div class="msc-icon msc-green"><i class="fas fa-chalkboard-user"></i></div>
    <div class="msc-info">
      <span class="msc-label">Total Faculty</span>
      <div class="msc-value"><?= number_format($total_faculty) ?></div>
      <div class="msc-sub"><i class="fas fa-users text-green"></i> AI Educators</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-blue"><i class="fas fa-award"></i></div>
    <div class="msc-info">
      <span class="msc-label">Gemini Certified</span>
      <div class="msc-value"><?= number_format($total_cert) ?></div>
      <div class="msc-sub"><i class="fas fa-certificate text-brand"></i> Verified by Google</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-purple"><i class="fas fa-layer-group"></i></div>
    <div class="msc-info">
      <span class="msc-label">L1 / L2 Qualified</span>
      <div class="msc-value"><?= number_format($total_levels) ?></div>
      <div class="msc-sub"><i class="fas fa-graduation-cap text-purple"></i> Advanced Trainers</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-amber"><i class="fas fa-signal"></i></div>
    <div class="msc-info">
      <span class="msc-label">Active Trainers</span>
      <div class="msc-value"><?= number_format($total_active) ?></div>
      <div class="msc-sub"><i class="fas fa-circle-check text-amber"></i> On Duty</div>
    </div>
  </div>
</div>

<div class="card">
  <!-- Quick Tabs -->
  <div class="mod-quick-tabs">
    <a href="educators.php" class="mqt-item <?= ($f_cert === '' && $f_level === '' && $q === '') ? 'is-active' : '' ?>">
      <i class="fas fa-users"></i> All Faculty <span class="mqt-count"><?= $total_faculty ?></span>
    </a>
    <a href="educators.php?cert=1" class="mqt-item <?= $f_cert === '1' ? 'is-active' : '' ?>">
      <i class="fas fa-award"></i> Gemini Certified <span class="mqt-count"><?= $total_cert ?></span>
    </a>
    <a href="educators.php?level=L2" class="mqt-item <?= $f_level === 'L2' ? 'is-active' : '' ?>">
      <i class="fas fa-star"></i> L2 Senior Mentors <span class="mqt-count"><?= count(array_filter($list, fn($e) => $e['level'] === 'L2')) ?></span>
    </a>
  </div>

  <div class="card-head">
    <h2>
      <i class="fas fa-chalkboard-user text-green" style="font-size:16px"></i>
      Faculty Roster
    </h2>
    <span class="spacer"></span>
    <form method="GET" class="filters">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, phone…">
      <select name="state">
        <option value="">All states</option>
        <?php foreach ($states as $s): ?>
          <option value="<?= (int)$s['id'] ?>" <?= (string)$f_state === (string)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="level">
        <option value="">All levels</option>
        <?php foreach ($LEVELS as $l): ?>
          <option value="<?= e($l) ?>" <?= $f_level === $l ? 'selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="cert">
        <option value="">Certification</option>
        <option value="1" <?= $f_cert === '1' ? 'selected' : '' ?>>Gemini certified</option>
        <option value="0" <?= $f_cert === '0' ? 'selected' : '' ?>>Not certified</option>
      </select>
      <button class="btn btn-primary btn-sm"><i class="fas fa-magnifying-glass"></i> Filter</button>
      <?php if ($q || $f_state || $f_level || $f_cert !== ''): ?>
        <a href="educators.php" class="btn btn-ghost btn-sm" title="Reset Filters"><i class="fas fa-rotate-left"></i></a>
      <?php endif; ?>
    </form>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-chalkboard-user"></i>
      <h3>No educators found</h3>
      <p>Add educators as they join training batches.</p>
      <a href="educators.php?action=new" class="btn btn-primary" style="margin-top:14px"><i class="fas fa-plus"></i> Add Educator</a>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th><i class="fas fa-user-tie" style="margin-right:6px"></i>Educator</th>
            <th><i class="fas fa-location-dot" style="margin-right:6px"></i>Location</th>
            <th><i class="fas fa-school" style="margin-right:6px"></i>Partner / Batch</th>
            <th><i class="fas fa-layer-group" style="margin-right:6px"></i>Level</th>
            <th><i class="fas fa-certificate" style="margin-right:6px"></i>Certification</th>
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
                  <div class="entity-avatar ea-green">
                    <?= e($initials) ?>
                  </div>
                  <div>
                    <div class="cell-main"><?= e($r['full_name']) ?></div>
                    <div class="cell-sub">
                      <i class="fas fa-phone" style="font-size:11px"></i> <?= e($r['phone'] ?: 'No Phone') ?>
                      <?php if ($r['experience_years']): ?>
                        · <i class="fas fa-briefcase" style="font-size:11px"></i> <?= (int)$r['experience_years'] ?> yrs exp
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= e($r['state_name'] ?: '—') ?></div>
                <div class="cell-sub"><?= e($r['district'] ?: $r['city'] ?: 'Central') ?></div>
              </td>
              <td>
                <div style="font-weight:700;color:var(--ink)"><?= e($r['partner_name'] ?: 'Independent') ?></div>
                <div class="cell-sub"><?= e($r['batch_code'] ?: 'Unassigned') ?></div>
              </td>
              <td>
                <span class="badge <?= $r['level'] === 'L2' ? 'b-purple' : ($r['level'] === 'L1' ? 'b-blue' : 'b-grey') ?>">
                  <span class="badge-dot"></span><?= e($r['level']) ?>
                </span>
              </td>
              <td>
                <?php if ($r['gemini_certified']): ?>
                  <span class="badge b-green"><span class="badge-dot"></span>Gemini</span>
                <?php endif; ?>
                <?php if ($r['is_work_certified']): ?>
                  <span class="badge b-yellow"><span class="badge-dot"></span>Work</span>
                <?php endif; ?>
                <?php if (!$r['gemini_certified'] && !$r['is_work_certified']): ?>
                  <span class="badge b-grey">Pending</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $r['status'] === 'active' ? 'b-green' : ($r['status'] === 'training' ? 'b-yellow' : 'b-grey') ?>">
                  <span class="badge-dot"></span><?= e(ucfirst($r['status'])) ?>
                </span>
              </td>
              <td style="text-align:right">
                <div class="row-actions" style="justify-content:flex-end">
                  <a href="educators.php?action=edit&id=<?= (int)$r['id'] ?>" class="icon-btn ib-edit" title="Edit Educator"><i class="fas fa-pen"></i></a>
                  <a href="educators.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete educator &quot;<?= e($r['full_name']) ?>&quot;?"><i class="fas fa-trash"></i></a>
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
        <span>Showing <strong><?= count($list) ?></strong> of <strong><?= $total_faculty ?></strong> instructors</span>
        <span class="tfb-sync"><span class="tfb-sync-dot"></span> Faculty Active</span>
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
      <div class="mbg-title"><i class="fas fa-chart-simple text-green"></i> Faculty Qualifications</div>
      <span class="mbg-tag">Overview</span>
    </div>
    <div class="mbg-metric-row">
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Gemini Certification Rate</span>
          <span class="mmr-val"><?= $total_faculty > 0 ? round(($total_cert / $total_faculty) * 100) : 0 ?>% Verified</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-green" style="width: <?= $total_faculty > 0 ? round(($total_cert / $total_faculty) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>L1 / L2 Progression</span>
          <span class="mmr-val"><?= $total_faculty > 0 ? round(($total_levels / $total_faculty) * 100) : 0 ?>% Advanced</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-purple" style="width: <?= $total_faculty > 0 ? round(($total_levels / $total_faculty) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Active Deployment</span>
          <span class="mmr-val"><?= $total_faculty > 0 ? round(($total_active / $total_faculty) * 100) : 0 ?>% On Duty</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-amber" style="width: <?= $total_faculty > 0 ? round(($total_active / $total_faculty) * 100) : 0 ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="mbg-card">
    <div class="mbg-header">
      <div class="mbg-title"><i class="fas fa-bolt text-amber"></i> Faculty Operations</div>
      <span class="mbg-tag">Shortcuts</span>
    </div>
    <div class="quick-links-grid">
      <a href="educators.php?action=new" class="qlg-item">
        <div class="qlg-icon qi-green"><i class="fas fa-user-plus"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Register Trainer</span>
          <span class="qlg-desc">Add new AI educator</span>
        </div>
      </a>
      <a href="batches.php" class="qlg-item">
        <div class="qlg-icon qi-blue"><i class="fas fa-layer-group"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Assign Cohort</span>
          <span class="qlg-desc">Link to batch</span>
        </div>
      </a>
      <a href="applications.php" class="qlg-item">
        <div class="qlg-icon qi-purple"><i class="fas fa-id-card"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Job Applications</span>
          <span class="qlg-desc">Review educator leads</span>
        </div>
      </a>
      <a href="partners.php" class="qlg-item">
        <div class="qlg-icon qi-amber"><i class="fas fa-school"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Partner Schools</span>
          <span class="qlg-desc">Affiliated campuses</span>
        </div>
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/_layout_end.php'; ?>
