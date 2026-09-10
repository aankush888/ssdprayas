<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

$STATUSES = ['new', 'contacted', 'converted', 'closed'];
$AUDIENCE = ['student', 'educator', 'professional', 'school', 'government', 'partner', 'other'];

/* ---------- Status change ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'status') {
    $id     = (int)($_POST['id'] ?? 0);
    $status = enum_or($_POST['status'] ?? '', $STATUSES, 'new');
    try {
        $pdo->prepare("UPDATE enquiries SET status = ? WHERE id = ?")->execute([$status, $id]);
        flash_set('success', 'Enquiry status updated.');
    } catch (PDOException $e) {
        error_log('Enquiry status update failed: ' . $e->getMessage());
        flash_set('error', 'Could not update the status.');
    }
    header('Location: enquiries.php');
    exit;
}

/* ---------- Delete ---------- */
if (($_GET['action'] ?? '') === 'delete' && ($id = (int)($_GET['id'] ?? 0))) {
    try {
        $pdo->prepare("DELETE FROM enquiries WHERE id = ?")->execute([$id]);
        flash_set('success', 'Enquiry deleted.');
    } catch (PDOException $e) {
        error_log('Enquiry delete failed: ' . $e->getMessage());
        flash_set('error', 'Could not delete this enquiry.');
    }
    header('Location: enquiries.php');
    exit;
}

/* ---------- List ---------- */
$q      = trim($_GET['q'] ?? '');
$f_stat = $_GET['status'] ?? '';
$f_aud  = $_GET['audience'] ?? '';

$where = ['1=1'];
$args  = [];
if ($q !== '')      { $where[] = "(q.name LIKE ? OR q.phone LIKE ? OR q.email LIKE ? OR q.organisation LIKE ?)";
                      array_push($args, "%$q%", "%$q%", "%$q%", "%$q%"); }
if ($f_stat !== '') { $where[] = "q.status = ?";   $args[] = $f_stat; }
if ($f_aud !== '')  { $where[] = "q.audience = ?"; $args[] = $f_aud; }
$w = implode(' AND ', $where);

$list = rows($pdo, "SELECT q.*, s.name AS state_name
                    FROM enquiries q LEFT JOIN states s ON s.id = q.state_id
                    WHERE $w ORDER BY q.created_at DESC", $args);

$tot_enquiries = count($list);
$tot_new_enq   = count(array_filter($list, fn($q) => $q['status'] === 'new'));
$tot_contacted = count(array_filter($list, fn($q) => in_array($q['status'], ['contacted', 'converted'])));
$tot_schools   = count(array_filter($list, fn($q) => in_array($q['audience'], ['school', 'government'])));

$admin_title  = 'Enquiries';
$admin_active = 'enquiries';
$admin_sub    = 'Contact form submissions and partner inquiries from the website.';
include __DIR__ . '/_layout.php';

$badge = ['new' => 'b-blue', 'contacted' => 'b-yellow', 'converted' => 'b-green', 'closed' => 'b-grey'];
?>

<!-- Module Hero Header -->
<div class="mod-hero-strip">
  <div class="mod-hero-left">
    <div class="mod-hero-icon mhi-teal">
      <i class="fas fa-inbox"></i>
    </div>
    <div>
      <h2 class="mod-hero-title">
        Public Inquiries & Leads
        <span class="mod-hero-badge"><?= $tot_enquiries ?> Inquiries</span>
      </h2>
      <p class="mod-hero-sub">Respond to prospective schools, parents, corporate CSR teams, and government officials.</p>
    </div>
  </div>
  <div class="mod-hero-actions">
    <a href="https://wa.me/" target="_blank" class="btn btn-ghost"><i class="fab fa-whatsapp text-green"></i> Open WhatsApp Web</a>
  </div>
</div>

<!-- Quick Stats Strip -->
<div class="mod-stats-grid">
  <div class="mod-stat-card">
    <div class="msc-icon msc-teal"><i class="fas fa-inbox"></i></div>
    <div class="msc-info">
      <span class="msc-label">Total Inquiries</span>
      <div class="msc-value"><?= number_format($tot_enquiries) ?></div>
      <div class="msc-sub"><i class="fas fa-envelope text-teal"></i> Web Submissions</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-blue"><i class="fas fa-bell"></i></div>
    <div class="msc-info">
      <span class="msc-label">New / Pending</span>
      <div class="msc-value"><?= number_format($tot_new_enq) ?></div>
      <div class="msc-sub"><i class="fas fa-circle-exclamation text-brand"></i> Needs Response</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-green"><i class="fas fa-headset"></i></div>
    <div class="msc-info">
      <span class="msc-label">Contacted / Closed</span>
      <div class="msc-value"><?= number_format($tot_contacted) ?></div>
      <div class="msc-sub"><i class="fas fa-check-circle text-green"></i> In Pipeline</div>
    </div>
  </div>
  <div class="mod-stat-card">
    <div class="msc-icon msc-amber"><i class="fas fa-school"></i></div>
    <div class="msc-info">
      <span class="msc-label">Institutional Leads</span>
      <div class="msc-value"><?= number_format($tot_schools) ?></div>
      <div class="msc-sub"><i class="fas fa-building-columns text-amber"></i> Schools & Govt</div>
    </div>
  </div>
</div>

<div class="card">
  <!-- Quick Tabs -->
  <div class="mod-quick-tabs">
    <a href="enquiries.php" class="mqt-item <?= ($f_stat === '' && $q === '') ? 'is-active' : '' ?>">
      <i class="fas fa-inbox"></i> All Leads <span class="mqt-count"><?= $tot_enquiries ?></span>
    </a>
    <a href="enquiries.php?status=new" class="mqt-item <?= $f_stat === 'new' ? 'is-active' : '' ?>">
      <i class="fas fa-envelope"></i> New Unread <span class="mqt-count"><?= $tot_new_enq ?></span>
    </a>
    <a href="enquiries.php?status=contacted" class="mqt-item <?= $f_stat === 'contacted' ? 'is-active' : '' ?>">
      <i class="fas fa-phone"></i> In Discussion <span class="mqt-count"><?= count(array_filter($list, fn($q) => $q['status'] === 'contacted')) ?></span>
    </a>
  </div>

  <div class="card-head">
    <h2>
      <i class="fas fa-envelope-open-text text-teal" style="font-size:16px"></i>
      Inquiry Inbox
    </h2>
    <span class="spacer"></span>
    <form method="GET" class="filters">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, phone…">
      <select name="audience">
        <option value="">All types</option>
        <?php foreach ($AUDIENCE as $a): ?>
          <option value="<?= e($a) ?>" <?= $f_aud === $a ? 'selected' : '' ?>><?= e(ucfirst($a)) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="status">
        <option value="">All status</option>
        <?php foreach ($STATUSES as $s): ?>
          <option value="<?= e($s) ?>" <?= $f_stat === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-primary btn-sm"><i class="fas fa-magnifying-glass"></i> Filter</button>
      <?php if ($q || $f_stat || $f_aud): ?>
        <a href="enquiries.php" class="btn btn-ghost btn-sm" title="Reset Filters"><i class="fas fa-rotate-left"></i></a>
      <?php endif; ?>
    </form>
  </div>

  <?php if (!$list): ?>
    <div class="empty-state">
      <i class="fas fa-inbox"></i>
      <h3>No enquiries yet</h3>
      <p>Every contact form submission on the website appears here.</p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr>
            <th><i class="fas fa-user" style="margin-right:6px"></i>Sender / Contact</th>
            <th><i class="fas fa-tag" style="margin-right:6px"></i>Audience</th>
            <th><i class="fas fa-building" style="margin-right:6px"></i>Organisation</th>
            <th><i class="fas fa-location-dot" style="margin-right:6px"></i>State</th>
            <th><i class="fas fa-comment-dots" style="margin-right:6px"></i>Message</th>
            <th><i class="fas fa-clock" style="margin-right:6px"></i>Received</th>
            <th><i class="fas fa-arrows-rotate" style="margin-right:6px"></i>Status</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): 
            $initials = strtoupper(substr($r['name'], 0, 2));
          ?>
            <tr>
              <td>
                <div class="entity-cell">
                  <div class="entity-avatar ea-teal">
                    <?= e($initials) ?>
                  </div>
                  <div>
                    <div class="cell-main"><?= e($r['name']) ?></div>
                    <div class="cell-sub">
                      <a href="tel:<?= e($r['phone']) ?>" style="font-weight:700;color:var(--brand)"><i class="fas fa-phone" style="font-size:11px"></i> <?= e($r['phone']) ?></a>
                      <?php if ($r['email']): ?>
                        · <a href="mailto:<?= e($r['email']) ?>"><i class="fas fa-envelope" style="font-size:11px"></i> <?= e($r['email']) ?></a>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </td>
              <td><span class="badge b-blue"><?= e(ucfirst($r['audience'])) ?></span></td>
              <td class="cell-sub" style="font-weight:700;color:var(--ink)"><?= e($r['organisation'] ?: 'Individual') ?></td>
              <td class="cell-sub"><?= e($r['state_name'] ?: '—') ?></td>
              <td class="cell-sub" style="max-width:280px;line-height:1.4">
                <?= e(mb_strimwidth((string)$r['message'], 0, 95, '…')) ?: 'No message attached' ?>
              </td>
              <td class="cell-sub">
                <strong><?= date('d M Y', strtotime($r['created_at'])) ?></strong><br>
                <?= date('g:i A', strtotime($r['created_at'])) ?>
              </td>
              <td>
                <form method="POST" style="margin:0">
                  <?= csrf_field() ?>
                  <input type="hidden" name="do" value="status">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <select name="status" onchange="this.form.submit()"
                          style="padding:6px 12px;border-radius:8px;border:1.5px solid var(--line);font-size:12px;font-weight:700;background:#f8fafc;cursor:pointer">
                    <?php foreach ($STATUSES as $s): ?>
                      <option value="<?= e($s) ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td style="text-align:right">
                <div class="row-actions" style="justify-content:flex-end">
                  <a href="https://wa.me/91<?= e(preg_replace('/\D/', '', $r['phone'])) ?>" target="_blank" rel="noopener"
                     class="icon-btn ib-view" title="Reply via WhatsApp"><i class="fab fa-whatsapp"></i></a>
                  <a href="enquiries.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete this enquiry?"><i class="fas fa-trash"></i></a>
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
        <span>Showing <strong><?= count($list) ?></strong> of <strong><?= $tot_enquiries ?></strong> leads</span>
        <span class="tfb-sync"><span class="tfb-sync-dot"></span> Inbox Connected</span>
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
      <div class="mbg-title"><i class="fas fa-chart-simple text-teal"></i> Lead Response Analytics</div>
      <span class="mbg-tag">Performance</span>
    </div>
    <div class="mbg-metric-row">
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>Response Resolution Rate</span>
          <span class="mmr-val"><?= $tot_enquiries > 0 ? round((($tot_enquiries - $tot_new_enq) / $tot_enquiries) * 100) : 100 ?>% Handled</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-green" style="width: <?= $tot_enquiries > 0 ? round((($tot_enquiries - $tot_new_enq) / $tot_enquiries) * 100) : 100 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>School Partnership Opportunities</span>
          <span class="mmr-val"><?= $tot_enquiries > 0 ? round(($tot_schools / $tot_enquiries) * 100) : 0 ?>% Institutional</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-amber" style="width: <?= $tot_enquiries > 0 ? round(($tot_schools / $tot_enquiries) * 100) : 0 ?>%"></div>
        </div>
      </div>
      <div class="mmr-item">
        <div class="mmr-label-bar">
          <span>High Priority Unanswered</span>
          <span class="mmr-val"><?= $tot_new_enq ?> Inquiries Pending</span>
        </div>
        <div class="mmr-bar-track">
          <div class="mmr-bar-fill fill-purple" style="width: <?= $tot_enquiries > 0 ? round(($tot_new_enq / $tot_enquiries) * 100) : 0 ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="mbg-card">
    <div class="mbg-header">
      <div class="mbg-title"><i class="fas fa-bolt text-amber"></i> Fast Lead Operations</div>
      <span class="mbg-tag">Shortcuts</span>
    </div>
    <div class="quick-links-grid">
      <a href="partners.php?action=new" class="qlg-item">
        <div class="qlg-icon qi-blue"><i class="fas fa-handshake"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Convert to Partner</span>
          <span class="qlg-desc">Register institution</span>
        </div>
      </a>
      <a href="educators.php?action=new" class="qlg-item">
        <div class="qlg-icon qi-green"><i class="fas fa-chalkboard-user"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Onboard Faculty</span>
          <span class="qlg-desc">Register instructor</span>
        </div>
      </a>
      <a href="batches.php" class="qlg-item">
        <div class="qlg-icon qi-purple"><i class="fas fa-layer-group"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Batch Program</span>
          <span class="qlg-desc">View schedule</span>
        </div>
      </a>
      <a href="<?= e(url('/contact')) ?>" target="_blank" class="qlg-item">
        <div class="qlg-icon qi-amber"><i class="fas fa-arrow-up-right-from-square"></i></div>
        <div class="qlg-text">
          <span class="qlg-name">Contact Portal</span>
          <span class="qlg-desc">Public contact page</span>
        </div>
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/_layout_end.php'; ?>
