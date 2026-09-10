<?php
require_once __DIR__ . '/db.php';

// Must run before any output — csrf_token() needs a live session.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escape for HTML output. */
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Build a URL-safe slug. */
function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

/** Absolute site URL for a path. */
function url($path = '') {
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

/** Asset path relative to site root. */
function asset($path) {
    return url('assets/' . ltrim($path, '/'));
}

/**
 * Asset URL with a cache-busting stamp from the file's mtime.
 * Without this, browsers keep a stale CSS/JS for as long as the Expires header says.
 */
function asset_v($path) {
    $path = ltrim($path, '/');
    $file = __DIR__ . '/../assets/' . $path;
    $url  = asset($path);
    return is_file($file) ? $url . '?v=' . filemtime($file) : $url;
}

/** URL with automatic cache-busting stamp from file mtime */
function url_v($path) {
    if (!$path) return '';
    $path_clean = ltrim($path, '/');
    $file = __DIR__ . '/../' . $path_clean;
    $u = url($path_clean);
    return is_file($file) ? $u . '?v=' . filemtime($file) : $u;
}

/**
 * Resolves a blog post's featured image.
 * If the image is the legacy generic placeholder ('student-laptop.png' or empty),
 * returns the dedicated high-res asset matching the blog's slug.
 */
function blog_image($post) {
    if (is_string($post)) {
        $img  = $post;
        $slug = '';
    } else {
        $img  = $post['image'] ?? '';
        $slug = $post['slug'] ?? '';
    }

    $slug_map = [
        'why-ai-in-cbse-schools-is-no-longer-optional-and-why-it-should-start-early' => 'assets/img/blog-1.jpg',
        'ai-in-schools-the-future-of-smart-education'                                => 'assets/img/blog-2.jpg',
        'nep-2020-and-the-ai-revolution'                                            => 'assets/img/blog-3.jpg',
        'top-5-ai-skills'                                                           => 'assets/img/blog-4.jpg',
        'how-educators-can-bring-ai-to-their-classrooms'                             => 'assets/img/blog-5.jpg',
    ];

    if (empty($img) || strpos($img, 'student-laptop.png') !== false) {
        if ($slug && isset($slug_map[$slug])) {
            return $slug_map[$slug];
        }
        return 'assets/img/blog-1.jpg';
    }

    return $img;
}

/** Blog image URL with cache-busting */
function blog_image_url($post) {
    return url_v(blog_image($post));
}

/** WhatsApp click-to-chat link. */
function whatsapp_link($text = '') {
    return 'https://wa.me/' . CONTACT_PHONE_RAW . ($text ? '?text=' . rawurlencode($text) : '');
}

/** Single scalar from a query — returns $default on any failure. */
function scalar(PDO $pdo, $sql, $params = [], $default = 0) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        return $value === false ? $default : $value;
    } catch (PDOException $e) {
        error_log('scalar() failed: ' . $e->getMessage());
        return $default;
    }
}

/** All rows for a query — returns [] on any failure. */
function rows(PDO $pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('rows() failed: ' . $e->getMessage());
        return [];
    }
}

/** Homepage impact numbers, pulled live from the database. */
function impact_stats(PDO $pdo) {
    return [
        'partners'  => (int)scalar($pdo, "SELECT COUNT(*) FROM partners WHERE status = 'active'"),
        'educators' => (int)scalar($pdo, "SELECT COUNT(*) FROM educators WHERE status <> 'inactive'"),
        'students'  => (int)scalar($pdo, "SELECT COUNT(*) FROM students"),
        'states'    => (int)scalar($pdo, "SELECT COUNT(DISTINCT state_id) FROM partners WHERE state_id IS NOT NULL"),
        'batches'   => (int)scalar($pdo, "SELECT COUNT(*) FROM batches"),
        'certified' => (int)scalar($pdo, "SELECT COUNT(*) FROM educators WHERE gemini_certified = 1"),
    ];
}

/**
 * Numbers shown on the public site.
 * Live DB count is used once it crosses the committed figure, so the site never
 * looks emptier than reality while the databases are still being filled.
 */
function display_stat($live, $floor) {
    return $live > $floor ? $live : $floor;
}

/** Store an uploaded file; returns the web-relative path or null. */
function handle_upload($field, $target_dir, $allowed_ext, $prefix = '') {
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    if ($_FILES[$field]['size'] > MAX_UPLOAD_MB * 1024 * 1024) {
        return null;
    }
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext, true)) {
        return null;
    }
    if (!is_dir($target_dir) && !mkdir($target_dir, 0755, true)) {
        return null;
    }
    $name = $prefix . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $target_dir . '/' . $name)) {
        return null;
    }
    // Web-relative path from the site root
    $folder = basename($target_dir);
    return 'assets/uploads/' . $folder . '/' . $name;
}

/** CSRF token for this session. */
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check() {
    return !empty($_POST['csrf'])
        && !empty($_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}
