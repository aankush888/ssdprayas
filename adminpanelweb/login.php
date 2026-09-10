<?php
require_once __DIR__ . '/_auth.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$LOCK_AFTER = 5;
$LOCK_FOR   = 300; // seconds

$_SESSION['login_fails'] = $_SESSION['login_fails'] ?? 0;
$_SESSION['login_until'] = $_SESSION['login_until'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (time() < $_SESSION['login_until']) {
        $wait  = (int)ceil(($_SESSION['login_until'] - time()) / 60);
        $error = "Too many failed attempts. Try again in {$wait} minute(s).";
    } elseif (!csrf_check()) {
        $error = 'Session expired. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE (username = ? OR email = ?) AND is_active = 1 LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']    = $user['id'];
            $_SESSION['admin_name']  = $user['full_name'] ?: $user['username'];
            $_SESSION['admin_role']  = $user['role'];
            $_SESSION['login_fails'] = 0;
            $_SESSION['login_until'] = 0;

            try {
                $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);
            } catch (PDOException $e) {
                error_log('last_login update failed: ' . $e->getMessage());
            }

            header('Location: index.php');
            exit;
        }

        $_SESSION['login_fails']++;
        if ($_SESSION['login_fails'] >= $LOCK_AFTER) {
            $_SESSION['login_until'] = time() + $LOCK_FOR;
            $_SESSION['login_fails'] = 0;
            $error = 'Too many failed attempts. Locked for 5 minutes.';
        } else {
            $left  = $LOCK_AFTER - $_SESSION['login_fails'];
            $error = "Invalid username or password. {$left} attempt(s) left.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | SSD Prayas</title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="icon" type="image/png" href="<?= e(url('favicon.png')) ?>">
<link rel="stylesheet" href="admin.css?v=<?= filemtime(__DIR__ . '/admin.css') ?>">
</head>
<body class="login-page">
  <div class="login-box">
    <img class="login-logo" src="<?= e(asset(SITE_LOGO)) ?>" alt="<?= e(SITE_NAME) ?>">
    <h2>Welcome back</h2>
    <p>Sign in to the SSD Prayas admin panel.</p>

    <?php if ($error): ?>
      <div class="note note-error" style="text-align:left"><i class="fas fa-circle-exclamation"></i><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form method="POST">
      <?= csrf_field() ?>
      <div class="fg">
        <label for="u">Username or Email</label>
        <input type="text" id="u" name="username" required autofocus autocomplete="username" placeholder="Enter username or email">
      </div>
      <div class="fg">
        <label for="p">Password</label>
        <input type="password" id="p" name="password" required autocomplete="current-password" placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="margin-top:8px">Sign In</button>
    </form>

    <p style="margin-top:24px;font-size:13px;color:var(--muted)">
      <a href="<?= e(url('/')) ?>" style="color:var(--brand);font-weight:600">← Back to website</a>
    </p>
  </div>
</body>
</html>
