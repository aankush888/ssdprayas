<?php
/**
 * SSD Prayas — Auto Blog Migration Runner for Live Server
 * Usage: Open in browser -> https://ssdprayas.com/sync_blogs.php?key=ssd2026
 */
require_once __DIR__ . '/includes/functions.php';

$key = $_GET['key'] ?? '';
if ($key !== 'ssd2026') {
    http_response_code(403);
    die("<h3 style='color:red;font-family:sans-serif;'>Access Denied. Invalid key.</h3>");
}

$sqlFile = __DIR__ . '/add_5_new_blogs.sql';
if (!file_exists($sqlFile)) {
    die("<h3 style='color:red;font-family:sans-serif;'>add_5_new_blogs.sql file not found.</h3>");
}

$sql = file_get_contents($sqlFile);

try {
    $pdo->exec($sql);
    echo "<div style='font-family:sans-serif;max-width:600px;margin:50px auto;padding:30px;border-radius:12px;background:#f0fdf4;border:1px solid #86efac;text-align:center;'>";
    echo "<h2 style='color:#166534;'> Migration Successful!</h2>";
    echo "<p style='color:#15803d;font-size:16px;'>Sabhi 5 naye blogs Hostinger Database mein successfully add ho chuke hain.</p>";
    echo "<p><a href='" . url('blogs') . "' style='display:inline-block;padding:12px 24px;background:#1A73E8;color:#fff;text-decoration:none;border-radius:8px;font-weight:600;'>View Blogs Page &rarr;</a></p>";
    echo "</div>";
} catch (PDOException $e) {
    echo "<div style='font-family:sans-serif;max-width:600px;margin:50px auto;padding:30px;border-radius:12px;background:#fef2f2;border:1px solid #fca5a5;'>";
    echo "<h2 style='color:#991b1b;'>Database Error</h2>";
    echo "<p style='color:#b91c1c;'>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
