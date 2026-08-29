<?php
require_once __DIR__ . '/_auth.php';
require_admin();
require_csrf();

$me = admin_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['do'] ?? '') === 'password') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
    $stmt->execute([$me['id']]);
    $hash = $stmt->fetchColumn();

    if (!$hash || !password_verify($current, $hash)) {
        flash_set('error', 'Your current password is incorrect.');
    } elseif (strlen($new) < 8) {
        flash_set('error', 'New password must be at least 8 characters.');
    } elseif ($new !== $confirm) {
        flash_set('error', 'New password and confirmation do not match.');
    } else {
        try {
            $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?")
                ->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]);
            flash_set('success', 'Password changed successfully.');
        } catch (PDOException $e) {
            error_log('Password change failed: ' . $e->getMessage());
            flash_set('error', 'Could not change the password.');
        }
    }
    header('Location: settings.php');
    exit;
}

$account = null;
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$me['id']]);
$account = $stmt->fetch();

$admin_title  = 'Settings';
$admin_active = 'settings';
$admin_sub    = 'Your account and site configuration.';
include __DIR__ . '/_layout.php';
?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px" class="set-split">

  <div class="card">
    <div class="card-head"><h2>Change Password</h2></div>
    <div class="card-body">
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="password">
        <div class="fg">
          <label for="cp">Current Password</label>
          <input type="password" id="cp" name="current_password" required autocomplete="current-password">
        </div>
        <div class="fg">
          <label for="np">New Password</label>
          <input type="password" id="np" name="new_password" required minlength="8" autocomplete="new-password">
          <p class="hint">Minimum 8 characters. Use letters, numbers and a symbol.</p>
        </div>
        <div class="fg">
          <label for="xp">Confirm New Password</label>
          <input type="password" id="xp" name="confirm_password" required minlength="8" autocomplete="new-password">
        </div>
        <button class="btn btn-primary"><i class="fas fa-key"></i> Update Password</button>
      </form>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-head"><h2>Your Account</h2></div>
      <div class="card-body">
        <dl class="detail-list" style="grid-template-columns:1fr">
          <div><dt>Username</dt><dd><?= e($account['username'] ?? '—') ?></dd></div>
          <div><dt>Name</dt><dd><?= e($account['full_name'] ?? '—') ?></dd></div>
          <div><dt>Role</dt><dd><?= e(ucfirst($account['role'] ?? '')) ?></dd></div>
          <div><dt>Last Login</dt><dd><?= $account['last_login'] ? date('d M Y, g:i A', strtotime($account['last_login'])) : 'First session' ?></dd></div>
        </dl>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><h2>Site Configuration</h2></div>
      <div class="card-body">
        <p style="font-size:14px;color:var(--muted);margin-bottom:16px">
          Phone number, address, email, social links and the Gemini API key all live in one file:
          <code style="background:var(--bg);padding:3px 8px;border-radius:6px;font-size:13px">config.php</code>
          in the site root. Edit that file to change them site-wide.
        </p>
        <dl class="detail-list" style="grid-template-columns:1fr">
          <div><dt>Site Name</dt><dd><?= e(SITE_NAME) ?></dd></div>
          <div><dt>Contact Phone</dt><dd><?= e(CONTACT_PHONE) ?></dd></div>
          <div><dt>Contact Email</dt><dd><?= e(SITE_EMAIL) ?></dd></div>
          <div><dt>Gemini AI Writer</dt>
            <dd><?= GEMINI_API_KEY !== '' ? '<span class="badge b-green">Configured</span>' : '<span class="badge b-yellow">API key not set</span>' ?></dd></div>
        </dl>
      </div>
    </div>
  </div>

</div>

<style>@media(max-width:1000px){.set-split{grid-template-columns:1fr !important}}</style>

<?php include __DIR__ . '/_layout_end.php'; ?>
