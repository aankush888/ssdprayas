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

$admin_title  = 'Enquiries';
$admin_active = 'enquiries';
$admin_sub    = 'Contact form submissions from the website.';
include __DIR__ . '/_layout.php';

$badge = ['new' => 'b-blue', 'contacted' => 'b-yellow', 'converted' => 'b-green', 'closed' => 'b-grey'];
?>

<div class="card">
  <div class="card-head">
    <h2>Website Enquiries <span class="badge b-grey"><?= count($list) ?></span></h2>
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
      <button class="btn btn-ghost btn-sm"><i class="fas fa-magnifying-glass"></i></button>
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
          <tr><th>Contact</th><th>Type</th><th>Organisation</th><th>State</th><th>Message</th><th>Received</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($list as $r): ?>
            <tr>
              <td>
                <div class="cell-main"><?= e($r['name']) ?></div>
                <div class="cell-sub">
                  <a href="tel:<?= e($r['phone']) ?>"><?= e($r['phone']) ?></a>
                  <?= $r['email'] ? ' · <a href="mailto:' . e($r['email']) . '">' . e($r['email']) . '</a>' : '' ?>
                </div>
              </td>
              <td><span class="badge b-blue"><?= e(ucfirst($r['audience'])) ?></span></td>
              <td class="cell-sub"><?= e($r['organisation'] ?: '—') ?></td>
              <td class="cell-sub"><?= e($r['state_name'] ?: '—') ?></td>
              <td class="cell-sub" style="max-width:280px"><?= e(mb_strimwidth((string)$r['message'], 0, 90, '…')) ?: '—' ?></td>
              <td class="cell-sub"><?= date('d M Y', strtotime($r['created_at'])) ?><br><?= date('g:i A', strtotime($r['created_at'])) ?></td>
              <td>
                <form method="POST" style="margin:0">
                  <?= csrf_field() ?>
                  <input type="hidden" name="do" value="status">
                  <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                  <select name="status" onchange="this.form.submit()"
                          style="padding:5px 9px;border-radius:8px;border:1.5px solid var(--line);font-size:12.5px;font-weight:700">
                    <?php foreach ($STATUSES as $s): ?>
                      <option value="<?= e($s) ?>" <?= $r['status'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td>
                <div class="row-actions">
                  <a href="https://wa.me/91<?= e(preg_replace('/\D/', '', $r['phone'])) ?>" target="_blank" rel="noopener"
                     class="icon-btn ib-view" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                  <a href="enquiries.php?action=delete&id=<?= (int)$r['id'] ?>" class="icon-btn ib-del" title="Delete"
                     data-confirm="Delete this enquiry?"><i class="fas fa-trash"></i></a>
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
