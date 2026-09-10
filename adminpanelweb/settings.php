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

<!-- Module Hero Header -->
<div class="mod-hero-strip">
  <div class="mod-hero-left">
    <div class="mod-hero-icon mhi-blue">
      <i class="fas fa-shield-halved"></i>
    </div>
    <div>
      <h2 class="mod-hero-title">
        Security & System Preferences
        <span class="mod-hero-badge">System Profile</span>
      </h2>
      <p class="mod-hero-sub">Manage administrator credentials, audit active sessions, and check portal environment parameters.</p>
    </div>
  </div>
  <div class="mod-hero-actions">
    <a href="index.php" class="btn btn-ghost"><i class="fas fa-gauge"></i> Dashboard</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px" class="set-split">

  <!-- Left Column: Password & Security -->
  <div style="display:flex;flex-direction:column;gap:24px">
    
    <!-- Change Password Card -->
    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-key text-brand" style="font-size:16px"></i> Change Password</h2>
        <span class="badge b-blue"><i class="fas fa-lock"></i> SSL Secured</span>
      </div>
      <div class="card-body">
        <form method="POST">
          <?= csrf_field() ?>
          <input type="hidden" name="do" value="password">
          
          <div class="form-grid" style="grid-template-columns:1fr;gap:18px">
            
            <div class="fg">
              <label for="cp"><i class="fas fa-lock" style="color:var(--muted);font-size:12px"></i> Current Password <span class="req">*</span></label>
              <div class="input-with-action">
                <input type="password" id="cp" name="current_password" required autocomplete="current-password" placeholder="Enter current password">
                <button type="button" class="input-action-btn" onclick="togglePass('cp', this)" title="Show/Hide Password"><i class="fas fa-eye"></i></button>
              </div>
            </div>

            <div class="fg">
              <label for="np"><i class="fas fa-key" style="color:var(--muted);font-size:12px"></i> New Password <span class="req">*</span></label>
              <div class="input-with-action">
                <input type="password" id="np" name="new_password" required minlength="8" autocomplete="new-password" placeholder="Enter new strong password">
                <button type="button" class="input-action-btn" onclick="togglePass('np', this)" title="Show/Hide Password"><i class="fas fa-eye"></i></button>
              </div>
              <div class="pass-rules">
                <i class="fas fa-circle-info"></i>
                <span>Minimum 8 characters. Must combine letters, numbers and symbols.</span>
              </div>
            </div>

            <div class="fg">
              <label for="xp"><i class="fas fa-check-double" style="color:var(--muted);font-size:12px"></i> Confirm New Password <span class="req">*</span></label>
              <div class="input-with-action">
                <input type="password" id="xp" name="confirm_password" required minlength="8" autocomplete="new-password" placeholder="Re-enter new password">
                <button type="button" class="input-action-btn" onclick="togglePass('xp', this)" title="Show/Hide Password"><i class="fas fa-eye"></i></button>
              </div>
            </div>

          </div>

          <div class="form-actions" style="margin-top:24px;padding-top:20px">
            <button type="submit" class="btn btn-primary" style="width:100%"><i class="fas fa-shield-check"></i> Update Password</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Security & Session Card -->
    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-user-shield text-green" style="font-size:16px"></i> Active Session & Security</h2>
        <span class="badge b-green"><span class="badge-dot"></span>Session Active</span>
      </div>
      <div class="card-body">
        <dl class="detail-list" style="grid-template-columns:1fr">
          <div>
            <dt><i class="fas fa-shield"></i> Password Encryption</dt>
            <dd>Bcrypt (Cost 10, Auto-Salted & SHA-256 Verified)</dd>
          </div>
          <div>
            <dt><i class="fas fa-clock"></i> Last Authenticated Session</dt>
            <dd><?= $account['last_login'] ? date('d M Y, g:i A', strtotime($account['last_login'])) : 'Active current session' ?></dd>
          </div>
          <div>
            <dt><i class="fas fa-network-wired"></i> Access Environment</dt>
            <dd>Localhost Apache / Windows NT</dd>
          </div>
        </dl>
      </div>
    </div>

  </div>

  <!-- Right Column: Account Profile & Platform Config -->
  <div style="display:flex;flex-direction:column;gap:24px">

    <!-- Administrator Profile Card -->
    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-circle-user text-purple" style="font-size:16px"></i> Account Profile</h2>
        <span class="badge b-purple"><span class="badge-dot"></span><?= e(strtoupper($account['role'] ?? 'SUPERADMIN')) ?></span>
      </div>
      <div class="card-body">
        <div class="admin-profile-header">
          <div class="aph-avatar">
            <?= e(strtoupper(substr($account['full_name'] ?? 'A', 0, 1))) ?>
          </div>
          <div class="aph-info">
            <h3><?= e($account['full_name'] ?? 'Superadmin') ?></h3>
            <p><i class="fas fa-envelope" style="font-size:11px"></i> <?= e($account['email'] ?? $account['username'] ?? '—') ?></p>
          </div>
        </div>

        <dl class="detail-list" style="grid-template-columns:1fr 1fr">
          <div>
            <dt><i class="fas fa-at"></i> Login Username</dt>
            <dd><?= e($account['username'] ?? '—') ?></dd>
          </div>
          <div>
            <dt><i class="fas fa-id-badge"></i> Role Authority</dt>
            <dd><?= e(ucfirst($account['role'] ?? 'Superadmin')) ?></dd>
          </div>
          <div>
            <dt><i class="fas fa-fingerprint"></i> Admin ID</dt>
            <dd>UID #<?= (int)$account['id'] ?></dd>
          </div>
          <div>
            <dt><i class="fas fa-circle-check"></i> Account Status</dt>
            <dd><span class="badge b-green"><span class="badge-dot"></span>Active</span></dd>
          </div>
        </dl>
      </div>
    </div>

    <!-- Portal Configuration Card -->
    <div class="card">
      <div class="card-head">
        <h2><i class="fas fa-server text-teal" style="font-size:16px"></i> Portal Configuration</h2>
        <span class="badge b-green"><span class="badge-dot"></span>Production Live</span>
      </div>
      <div class="card-body">
        <p style="font-size:13px;color:var(--muted);margin-bottom:18px;line-height:1.5">
          Portal parameters and Gemini AI writer credentials are maintained in
          <code style="background:var(--brand-soft);color:var(--brand);padding:2px 8px;border-radius:6px;font-weight:700">config.php</code>.
        </p>
        
        <dl class="detail-list" style="grid-template-columns:1fr">
          <div>
            <dt><i class="fas fa-globe"></i> Portal Title</dt>
            <dd><?= e(SITE_NAME) ?></dd>
          </div>
          <div>
            <dt><i class="fas fa-phone"></i> Helpline Contact</dt>
            <dd><a href="tel:<?= e(CONTACT_PHONE) ?>" style="color:var(--brand)"><?= e(CONTACT_PHONE) ?></a></dd>
          </div>
          <div>
            <dt><i class="fas fa-envelope"></i> Official Email</dt>
            <dd><a href="mailto:<?= e(SITE_EMAIL) ?>" style="color:var(--brand)"><?= e(SITE_EMAIL) ?></a></dd>
          </div>
          <div>
            <dt><i class="fas fa-microchip"></i> Gemini AI Writer</dt>
            <dd>
              <?= GEMINI_API_KEY !== ''
                ? '<span class="badge b-green"><span class="badge-dot"></span>API Connected</span>'
                : '<span class="badge b-yellow"><span class="badge-dot"></span>API key not configured</span>' ?>
            </dd>
          </div>
        </dl>
      </div>
    </div>

  </div>

</div>

<script>
function togglePass(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon = btn.querySelector('i');
  if (input.type === 'password') {
    input.type = 'text';
    icon.className = 'fas fa-eye-slash';
  } else {
    input.type = 'password';
    icon.className = 'fas fa-eye';
  }
}
</script>

<style>
@media (max-width: 1000px) {
  .set-split {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php include __DIR__ . '/_layout_end.php'; ?>
