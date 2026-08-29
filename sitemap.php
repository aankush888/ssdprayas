<?php
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/xml; charset=utf-8');

$static = [
    ['/', '1.00', 'weekly'],
    ['about', '0.80', 'monthly'],
    ['careers', '0.80', 'weekly'],
    ['blogs', '0.80', 'weekly'],
];
$posts = rows($pdo, "SELECT slug, updated_at, created_at FROM blogs WHERE status = 'published' ORDER BY created_at DESC");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($static as [$path, $priority, $freq]): ?>
  <url>
    <loc><?= e(url($path)) ?></loc>
    <changefreq><?= $freq ?></changefreq>
    <priority><?= $priority ?></priority>
  </url>
<?php endforeach; ?>
<?php foreach ($posts as $p): ?>
  <url>
    <loc><?= e(url('blog/' . $p['slug'])) ?></loc>
    <lastmod><?= date('c', strtotime($p['updated_at'] ?: $p['created_at'])) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.64</priority>
  </url>
<?php endforeach; ?>
</urlset>
