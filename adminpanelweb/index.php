<?php
require_once __DIR__ . '/_auth.php';
require_admin();

$admin_title  = 'Dashboard';
$admin_active = 'dashboard';
$admin_sub    = 'Live overview of the SSD Prayas programmes, cohorts, and metrics.';

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

<!-- Ultra-Modern Dashboard Hero Banner -->
<div class="hero-welcome-card">
  <div class="hw-glow-circle-1"></div>
  <div class="hw-glow-circle-2"></div>
  
  <div class="hw-main-content">
    <div class="hw-pill">
      <span class="hw-pill-dot"></span>
      <span>AI SKILLING MISSION CONTROL</span>
    </div>
    <h2>Welcome back, <?= e($me['name']) ?> 👋</h2>
    <p class="hw-desc">
      Monitor your active partner schools, certified educators, AI batch roll-outs, and student participation across all active states in real-time.
    </p>
    <div class="hw-date-strip">
      <span class="hds-item"><i class="far fa-calendar-check"></i> <?= date('l, d F Y') ?></span>
      <span class="hds-item"><i class="fas fa-signal"></i> Network Status: <strong>100% Operational</strong></span>
    </div>
  </div>

  <div class="hw-actions">
    <a href="partners.php?action=new" class="btn btn-hw-primary">
      <i class="fas fa-plus"></i>
      <span>Add Partner</span>
    </a>
    <a href="batches.php?action=new" class="btn btn-hw-glass">
      <i class="fas fa-layer-group"></i>
      <span>New Batch</span>
    </a>
    <a href="blog-form.php" class="btn btn-hw-glass">
      <i class="fas fa-feather-pointed"></i>
      <span>Write Blog</span>
    </a>
  </div>
</div>

<!-- High-Priority Inbound Alert Notice -->
<?php if ($c['new_enq'] || $c['new_apps']): ?>
  <div class="smart-alert-banner">
    <div class="sab-icon-wrap">
      <span class="sab-ring"></span>
      <i class="fas fa-bell"></i>
    </div>
    <div class="sab-info">
      <h4>Pending Items Awaiting Review</h4>
      <p>
        You have
        <?php if ($c['new_enq']): ?>
          <a href="enquiries.php?status=new" class="sab-link"><?= $c['new_enq'] ?> new enquir<?= $c['new_enq'] === 1 ? 'y' : 'ies' ?></a>
        <?php endif; ?>
        <?= ($c['new_enq'] && $c['new_apps']) ? ' and ' : '' ?>
        <?php if ($c['new_apps']): ?>
          <a href="applications.php?status=new" class="sab-link"><?= $c['new_apps'] ?> new candidate application<?= $c['new_apps'] === 1 ? '' : 's' ?></a>
        <?php endif; ?>
        ready for your response.
      </p>
    </div>
    <div class="sab-actions">
      <?php if ($c['new_enq']): ?>
        <a href="enquiries.php?status=new" class="btn btn-sm btn-white-pill">Review Enquiries &rarr;</a>
      <?php endif; ?>
      <?php if ($c['new_apps']): ?>
        <a href="applications.php?status=new" class="btn btn-sm btn-white-pill">Review Candidates &rarr;</a>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<!-- 6 Balanced Metric KPI Cards -->
<div class="metrics-grid">

  <!-- 1. Partners -->
  <div class="metric-card mc-blue">
    <div class="mc-head">
      <div class="mc-icon"><i class="fas fa-handshake"></i></div>
      <span class="mc-pill">Partners</span>
    </div>
    <div class="mc-body">
      <span class="mc-label">Partner Schools</span>
      <div class="mc-num"><?= number_format($c['partners']) ?></div>
      <div class="mc-sub"><i class="fas fa-location-dot"></i> Across <?= $c['states'] ?> state<?= $c['states'] === 1 ? '' : 's' ?></div>
    </div>
    <a href="partners.php" class="mc-foot">
      <span>View directory</span>
      <i class="fas fa-arrow-right"></i>
    </a>
  </div>

  <!-- 2. Educators -->
  <div class="metric-card mc-emerald">
    <div class="mc-head">
      <div class="mc-icon"><i class="fas fa-chalkboard-user"></i></div>
      <span class="mc-pill"><?= $c['certified'] ?> Certified</span>
    </div>
    <div class="mc-body">
      <span class="mc-label">AI Educators</span>
      <div class="mc-num"><?= number_format($c['educators']) ?></div>
      <div class="mc-sub"><i class="fas fa-award"></i> Gemini AI Certified</div>
    </div>
    <a href="educators.php" class="mc-foot">
      <span>View faculty</span>
      <i class="fas fa-arrow-right"></i>
    </a>
  </div>

  <!-- 3. Students -->
  <div class="metric-card mc-purple">
    <div class="mc-head">
      <div class="mc-icon"><i class="fas fa-user-graduate"></i></div>
      <span class="mc-pill">Class 3–12</span>
    </div>
    <div class="mc-body">
      <span class="mc-label">Enrolled Students</span>
      <div class="mc-num"><?= number_format($c['students']) ?></div>
      <div class="mc-sub"><i class="fas fa-laptop-code"></i> Active Learners</div>
    </div>
    <a href="students.php" class="mc-foot">
      <span>View enrollment</span>
      <i class="fas fa-arrow-right"></i>
    </a>
  </div>

  <!-- 4. Batches -->
  <div class="metric-card mc-amber">
    <div class="mc-head">
      <div class="mc-icon"><i class="fas fa-layer-group"></i></div>
      <span class="mc-pill"><?= $c['running'] ?> Running</span>
    </div>
    <div class="mc-body">
      <span class="mc-label">Training Batches</span>
      <div class="mc-num"><?= number_format($c['batches']) ?></div>
      <div class="mc-sub"><i class="fas fa-spinner fa-spin-pulse"></i> <?= $c['running'] ?> Live Batches</div>
    </div>
    <a href="batches.php" class="mc-foot">
      <span>Manage batches</span>
      <i class="fas fa-arrow-right"></i>
    </a>
  </div>

  <!-- 5. Enquiries -->
  <div class="metric-card mc-teal">
    <div class="mc-head">
      <div class="mc-icon"><i class="fas fa-inbox"></i></div>
      <span class="mc-pill <?= $c['new_enq'] ? 'pill-alert' : '' ?>"><?= $c['new_enq'] ? $c['new_enq'] . ' New' : 'Inbox' ?></span>
    </div>
    <div class="mc-body">
      <span class="mc-label">Website Enquiries</span>
      <div class="mc-num"><?= number_format($c['new_enq']) ?></div>
      <div class="mc-sub"><i class="fas fa-comment-dots"></i> Inbound school leads</div>
    </div>
    <a href="enquiries.php" class="mc-foot">
      <span>Open inbox</span>
      <i class="fas fa-arrow-right"></i>
    </a>
  </div>

  <!-- 6. Applications -->
  <div class="metric-card mc-rose">
    <div class="mc-head">
      <div class="mc-icon"><i class="fas fa-file-signature"></i></div>
      <span class="mc-pill <?= $c['new_apps'] ? 'pill-alert' : '' ?>"><?= $c['new_apps'] ? $c['new_apps'] . ' New' : 'Talent' ?></span>
    </div>
    <div class="mc-body">
      <span class="mc-label">Job Applications</span>
      <div class="mc-num"><?= number_format($c['new_apps']) ?></div>
      <div class="mc-sub"><i class="fas fa-user-plus"></i> Candidate resumes</div>
    </div>
    <a href="applications.php" class="mc-foot">
      <span>Review resumes</span>
      <i class="fas fa-arrow-right"></i>
    </a>
  </div>

</div>

<!-- State-wise Coverage Table with Live Search Filter -->
<div class="card premium-card">
  <div class="card-head ch-flex">
    <div class="ch-left">
      <div class="ch-avatar-icon"><i class="fas fa-map-location-dot"></i></div>
      <div>
        <h2>State-wise Programme Coverage</h2>
        <p class="ch-sub">Geographical distribution of institutions, educators, students and active batches.</p>
      </div>
    </div>
    <div class="ch-right-controls">
      <div class="table-search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="stateFilterInput" placeholder="Filter states..." onkeyup="filterStatesTable()">
      </div>
      <a href="partners.php?action=new" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Partner</a>
    </div>
  </div>

  <div class="table-wrap">
    <table class="data modern-data-table" id="stateCoverageTable">
      <thead>
        <tr>
          <th>State Region</th>
          <th>Partners</th>
          <th>Educators</th>
          <th>Students</th>
          <th>Batches</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($by_state as $row): 
          $has_activity = ($row['partners'] > 0 || $row['educators'] > 0 || $row['students'] > 0 || $row['batches'] > 0);
        ?>
          <tr class="<?= $has_activity ? 'row-has-activity' : '' ?>">
            <td class="cell-main">
              <div class="state-row-cell">
                <span class="state-indicator <?= $has_activity ? 'is-active-pin' : '' ?>"><i class="fas fa-location-dot"></i></span>
                <span class="state-name-text"><?= e($row['name']) ?></span>
              </div>
            </td>
            <td>
              <span class="count-tag <?= $row['partners'] > 0 ? 'ct-blue' : 'ct-zero' ?>">
                <?= number_format($row['partners']) ?>
              </span>
            </td>
            <td>
              <span class="count-tag <?= $row['educators'] > 0 ? 'ct-green' : 'ct-zero' ?>">
                <?= number_format($row['educators']) ?>
              </span>
            </td>
            <td>
              <span class="count-tag <?= $row['students'] > 0 ? 'ct-purple' : 'ct-zero' ?>">
                <?= number_format($row['students']) ?>
              </span>
            </td>
            <td>
              <span class="count-tag <?= $row['batches'] > 0 ? 'ct-amber' : 'ct-zero' ?>">
                <?= number_format($row['batches']) ?>
              </span>
            </td>
            <td>
              <?php if ($has_activity): ?>
                <span class="badge b-green"><i class="fas fa-circle-check"></i> Active</span>
              <?php else: ?>
                <span class="badge b-grey">Planned</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Split Activity Cards: Latest Enquiries & Latest Applications -->
<div class="dash-activity-grid">

  <!-- Latest Enquiries -->
  <div class="card activity-card">
    <div class="card-head ch-flex">
      <div class="ch-left">
        <div class="ch-avatar-icon icon-teal"><i class="fas fa-inbox"></i></div>
        <div>
          <h2>Latest Enquiries</h2>
          <p class="ch-sub">Form submissions from institutions</p>
        </div>
      </div>
      <a href="enquiries.php" class="btn btn-ghost btn-sm">View All <i class="fas fa-arrow-right"></i></a>
    </div>

    <?php if (!$recent_enq): ?>
      <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>No enquiries yet</h3>
        <p>Inbound messages from the website form will appear here.</p>
      </div>
    <?php else: ?>
      <div class="activity-feed">
        <?php foreach ($recent_enq as $q): 
          $initials = strtoupper(substr($q['name'], 0, 2));
        ?>
          <div class="activity-row">
            <div class="row-avatar ra-teal"><?= e($initials) ?></div>
            <div class="row-content">
              <div class="row-top">
                <span class="row-name"><?= e($q['name']) ?></span>
                <span class="badge <?= $q['status'] === 'new' ? 'b-blue' : 'b-grey' ?>">
                  <?= $q['status'] === 'new' ? '<span class="status-pulse-dot"></span>' : '' ?>
                  <?= e(ucfirst($q['status'])) ?>
                </span>
              </div>
              <div class="row-chips">
                <span class="meta-chip"><i class="fas fa-building"></i> <?= e($q['organisation'] ?: ucfirst($q['audience'])) ?></span>
                <span class="meta-chip"><i class="fas fa-phone"></i> <?= e($q['phone']) ?></span>
              </div>
            </div>
            <div class="row-timestamp">
              <i class="far fa-clock"></i> <?= date('d M', strtotime($q['created_at'])) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Latest Applications -->
  <div class="card activity-card">
    <div class="card-head ch-flex">
      <div class="ch-left">
        <div class="ch-avatar-icon icon-rose"><i class="fas fa-file-lines"></i></div>
        <div>
          <h2>Latest Applications</h2>
          <p class="ch-sub">Educator resumes &amp; candidate profiles</p>
        </div>
      </div>
      <a href="applications.php" class="btn btn-ghost btn-sm">View All <i class="fas fa-arrow-right"></i></a>
    </div>

    <?php if (!$recent_apps): ?>
      <div class="empty-state">
        <i class="fas fa-file-lines"></i>
        <h3>No applications yet</h3>
        <p>Educator applications and resumes will appear here.</p>
      </div>
    <?php else: ?>
      <div class="activity-feed">
        <?php foreach ($recent_apps as $a): 
          $initials = strtoupper(substr($a['full_name'], 0, 2));
        ?>
          <div class="activity-row">
            <div class="row-avatar ra-rose"><?= e($initials) ?></div>
            <div class="row-content">
              <div class="row-top">
                <span class="row-name"><?= e($a['full_name']) ?></span>
                <span class="badge <?= $a['status'] === 'new' ? 'b-rose' : 'b-grey' ?>">
                  <?= $a['status'] === 'new' ? '<span class="status-pulse-dot dot-rose"></span>' : '' ?>
                  <?= e(ucfirst($a['status'])) ?>
                </span>
              </div>
              <div class="row-chips">
                <span class="meta-chip"><i class="fas fa-briefcase"></i> <?= e($a['position']) ?></span>
                <span class="meta-chip"><i class="fas fa-business-time"></i> <?= (int)$a['experience_years'] ?> yr exp</span>
              </div>
            </div>
            <div class="row-timestamp">
              <i class="far fa-clock"></i> <?= date('d M', strtotime($a['created_at'])) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- State Table Instant Filter Script -->
<script>
function filterStatesTable() {
  const input = document.getElementById("stateFilterInput");
  const filter = input.value.toLowerCase();
  const table = document.getElementById("stateCoverageTable");
  const tr = table.getElementsByTagName("tr");

  for (let i = 1; i < tr.length; i++) {
    const td = tr[i].getElementsByTagName("td")[0];
    if (td) {
      const txtValue = td.textContent || td.innerText;
      if (txtValue.toLowerCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }
  }
}
</script>

<?php include __DIR__ . '/_layout_end.php'; ?>
