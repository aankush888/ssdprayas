<?php
require_once __DIR__ . '/_auth.php';
require_admin();

$admin_title  = 'Dashboard';
$admin_active = 'dashboard';
$admin_sub    = 'Live overview of the SSD Prayas programme.';

$c = [
    'partners'    => (int)scalar($pdo, "SELECT COUNT(*) FROM partners"),
    'educators'   => (int)scalar($pdo, "SELECT COUNT(*) FROM educators"),
    'students'    => (int)scalar($pdo, "SELECT COUNT(*) FROM students"),
    'batches'     => (int)scalar($pdo, "SELECT COUNT(*) FROM batches"),
    'certified'   => (int)scalar($pdo, "SELECT COUNT(*) FROM educators WHERE gemini_certified = 1"),
    'running'     => (int)scalar($pdo, "SELECT COUNT(*) FROM batches WHERE status = 'running'"),
    'new_enq'     => (int)scalar($pdo, "SELECT COUNT(*) FROM enquiries WHERE status = 'new'"),
    'new_apps'    => (int)scalar($pdo, "SELECT COUNT(*) FROM job_applications WHERE status = 'new'"),
    'blogs'       => (int)scalar($pdo, "SELECT COUNT(*) FROM blogs"),
    'states'      => (int)scalar($pdo, "SELECT COUNT(DISTINCT state_id) FROM partners WHERE state_id IS NOT NULL"),
];

$by_state = rows($pdo, "
    SELECT s.name,
           (SELECT COUNT(*) FROM partners  p WHERE p.state_id  = s.id) AS partners,
           (SELECT COUNT(*) FROM educators e WHERE e.state_id  = s.id) AS educators,
           (SELECT COUNT(*) FROM students  st WHERE st.state_id = s.id) AS students,
           (SELECT COUNT(*) FROM batches   b WHERE b.state_id  = s.id) AS batches
    FROM states s
    WHERE s.is_active = 1
    ORDER BY partners DESC, s.sort_order");

$recent_enq  = rows($pdo, "SELECT * FROM enquiries ORDER BY created_at DESC LIMIT 5");
$recent_apps = rows($pdo, "SELECT * FROM job_applications ORDER BY created_at DESC LIMIT 5");

include __DIR__ . '/_layout.php';
?>

<?php if ($c['new_enq'] || $c['new_apps']): ?>
  <div class="note note-info">
    <i class="fas fa-bell"></i>
    <span>
      You have
      <?php if ($c['new_enq']): ?><a href="enquiries.php?status=new" style="color:inherit;text-decoration:underline"><?= $c['new_enq'] ?> new enquir<?= $c['new_enq'] === 1 ? 'y' : 'ies' ?></a><?php endif; ?>
      <?= $c['new_enq'] && $c['new_apps'] ? ' and ' : '' ?>
      <?php if ($c['new_apps']): ?><a href="applications.php?status=new" style="color:inherit;text-decoration:underline"><?= $c['new_apps'] ?> new application<?= $c['new_apps'] === 1 ? '' : 's' ?></a><?php endif; ?>
      waiting for review.
    </span>
  </div>
<?php endif; ?>

<div class="stats">
  <div class="stat">
    <div class="stat-ico ico-blue"><i class="fas fa-handshake"></i></div>
    <h3>Partners</h3>
    <div class="val"><?= number_format($c['partners']) ?></div>
    <div class="sub">Across <?= $c['states'] ?> state<?= $c['states'] === 1 ? '' : 's' ?></div>
  </div>
  <div class="stat">
    <div class="stat-ico ico-green"><i class="fas fa-chalkboard-user"></i></div>
    <h3>Educators</h3>
    <div class="val"><?= number_format($c['educators']) ?></div>
    <div class="sub"><?= $c['certified'] ?> Gemini certified</div>
  </div>
  <div class="stat">
    <div class="stat-ico ico-yellow"><i class="fas fa-user-graduate"></i></div>
    <h3>Students</h3>
    <div class="val"><?= number_format($c['students']) ?></div>
    <div class="sub">Enrolled in programmes</div>
  </div>
  <div class="stat">
    <div class="stat-ico ico-red"><i class="fas fa-layer-group"></i></div>
    <h3>Batches</h3>
    <div class="val"><?= number_format($c['batches']) ?></div>
    <div class="sub"><?= $c['running'] ?> currently running</div>
  </div>
  <div class="stat">
    <div class="stat-ico ico-blue"><i class="fas fa-inbox"></i></div>
    <h3>New Enquiries</h3>
    <div class="val"><?= number_format($c['new_enq']) ?></div>
    <div class="sub">From the website form</div>
  </div>
  <div class="stat">
    <div class="stat-ico ico-green"><i class="fas fa-file-lines"></i></div>
    <h3>New Applications</h3>
    <div class="val"><?= number_format($c['new_apps']) ?></div>
    <div class="sub">Educator applications</div>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <h2>State-wise Coverage</h2>
    <span class="spacer"></span>
    <a href="partners.php?action=new" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Partner</a>
  </div>
  <div class="table-wrap">
    <table class="data">
      <thead>
        <tr><th>State</th><th>Partners</th><th>Educators</th><th>Students</th><th>Batches</th></tr>
      </thead>
      <tbody>
        <?php foreach ($by_state as $row): ?>
          <tr>
            <td class="cell-main"><i class="fas fa-location-dot" style="color:var(--muted);margin-right:8px"></i><?= e($row['name']) ?></td>
            <td><?= number_format($row['partners']) ?></td>
            <td><?= number_format($row['educators']) ?></td>
            <td><?= number_format($row['students']) ?></td>
            <td><?= number_format($row['batches']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px" class="dash-split">

  <div class="card">
    <div class="card-head">
      <h2>Latest Enquiries</h2>
      <span class="spacer"></span>
      <a href="enquiries.php" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <?php if (!$recent_enq): ?>
      <div class="empty-state"><i class="fas fa-inbox"></i><h3>No enquiries yet</h3><p>Website form submissions will appear here.</p></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data" style="min-width:0">
          <tbody>
            <?php foreach ($recent_enq as $q): ?>
              <tr>
                <td>
                  <div class="cell-main"><?= e($q['name']) ?></div>
                  <div class="cell-sub"><?= e($q['organisation'] ?: ucfirst($q['audience'])) ?> &middot; <?= e($q['phone']) ?></div>
                </td>
                <td style="text-align:right">
                  <span class="badge <?= $q['status'] === 'new' ? 'b-blue' : 'b-grey' ?>"><?= e(ucfirst($q['status'])) ?></span>
                  <div class="cell-sub" style="margin-top:4px"><?= date('d M', strtotime($q['created_at'])) ?></div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-head">
      <h2>Latest Applications</h2>
      <span class="spacer"></span>
      <a href="applications.php" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <?php if (!$recent_apps): ?>
      <div class="empty-state"><i class="fas fa-file-lines"></i><h3>No applications yet</h3><p>Educator applications will appear here.</p></div>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data" style="min-width:0">
          <tbody>
            <?php foreach ($recent_apps as $a): ?>
              <tr>
                <td>
                  <div class="cell-main"><?= e($a['full_name']) ?></div>
                  <div class="cell-sub"><?= e($a['position']) ?> &middot; <?= (int)$a['experience_years'] ?> yr exp</div>
                </td>
                <td style="text-align:right">
                  <span class="badge <?= $a['status'] === 'new' ? 'b-blue' : 'b-grey' ?>"><?= e(ucfirst($a['status'])) ?></span>
                  <div class="cell-sub" style="margin-top:4px"><?= date('d M', strtotime($a['created_at'])) ?></div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<style>@media(max-width:1000px){.dash-split{grid-template-columns:1fr !important}}</style>

<?php include __DIR__ . '/_layout_end.php'; ?>
