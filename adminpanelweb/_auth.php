<?php
require_once __DIR__ . '/../includes/functions.php';

/** Redirect to login unless a valid admin session exists. */
function require_admin() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function admin_user() {
    return [
        'id'   => $_SESSION['admin_id']   ?? 0,
        'name' => $_SESSION['admin_name'] ?? 'Admin',
        'role' => $_SESSION['admin_role'] ?? 'admin',
    ];
}

/** Flash message helpers — survive the post/redirect/get cycle. */
function flash_set($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function flash_get() {
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

/** Reject any POST without a valid CSRF token. */
function require_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_check()) {
        flash_set('error', 'Session expired. Please try again.');
        header('Location: ' . basename($_SERVER['PHP_SELF']));
        exit;
    }
}

/** Nullable integer from a form field. */
function nullable_int($value) {
    return ($value === '' || $value === null) ? null : (int)$value;
}

/** Keep a submitted value within an allow-list. */
function enum_or($value, array $allowed, $fallback) {
    return in_array($value, $allowed, true) ? $value : $fallback;
}
