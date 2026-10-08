<?php
require_once __DIR__ . '/db.php';

// Must run before any output — csrf_token() needs a live session.
// Avoid starting sessions on static/XML requests (e.g. sitemap.xml)
if (!defined('NO_SESSION') && session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
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
        'how-do-i-learn-ai-as-a-student-in-india'                                    => 'assets/img/blog-6.jpg',
        'how-school-students-in-india-can-learn-artificial-intelligence'             => 'assets/img/blog-7.jpg',
        'artificial-intelligence-for-kids-india'                                     => 'assets/img/blog-7.jpg',
        'chatgpt-for-school-homework-guide-for-students'                             => 'assets/img/blog-8.jpg',
        'how-to-use-ai-for-studies-students-guide'                                   => 'assets/img/blog-9.jpg',
        'is-ai-compulsory-in-cbse-2026-rules-explained'                              => 'assets/img/blog-10.jpg',
        'best-ai-course-after-12th-in-india-2026-guide'                              => 'assets/img/blog-11.jpg',
        'cbse-class-9-ai-syllabus-2026-27-breakdown'                                 => 'assets/img/blog-12.jpg',
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

/** Homepage impact numbers, pulled live from the database (consolidated query with cache). */
function impact_stats(PDO $pdo) {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    try {
        $row = $pdo->query("SELECT 
            (SELECT COUNT(*) FROM partners WHERE status = 'active') AS partners,
            (SELECT COUNT(*) FROM educators WHERE status <> 'inactive') AS educators,
            (SELECT COUNT(*) FROM students) AS students,
            (SELECT COUNT(DISTINCT state_id) FROM partners WHERE state_id IS NOT NULL) AS states,
            (SELECT COUNT(*) FROM batches) AS batches,
            (SELECT COUNT(*) FROM educators WHERE gemini_certified = 1) AS certified")->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $cached = [
                'partners'  => (int)$row['partners'],
                'educators' => (int)$row['educators'],
                'students'  => (int)$row['students'],
                'states'    => (int)$row['states'],
                'batches'   => (int)$row['batches'],
                'certified' => (int)$row['certified'],
            ];
            return $cached;
        }
    } catch (PDOException $e) {
        error_log('impact_stats() failed: ' . $e->getMessage());
    }
    return [
        'partners'  => 250,
        'educators' => 800,
        'students'  => 25000,
        'states'    => 6,
        'batches'   => 50,
        'certified' => 450,
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

/**
 * Automatically sync legacy blog image paths in the database if any old student-laptop.png paths exist.
 */
function sync_legacy_blog_images(PDO $pdo) {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $has_old = (int)scalar($pdo, "SELECT COUNT(*) FROM blogs WHERE image LIKE '%student-laptop.png%'");
        if ($has_old > 0) {
            $pdo->exec("UPDATE blogs SET image = 'assets/img/blog-1.jpg' WHERE slug = 'why-ai-in-cbse-schools-is-no-longer-optional-and-why-it-should-start-early' AND image LIKE '%student-laptop.png%'");
            $pdo->exec("UPDATE blogs SET image = 'assets/img/blog-2.jpg' WHERE slug = 'ai-in-schools-the-future-of-smart-education' AND image LIKE '%student-laptop.png%'");
            $pdo->exec("UPDATE blogs SET image = 'assets/img/blog-3.jpg' WHERE slug = 'nep-2020-and-the-ai-revolution' AND image LIKE '%student-laptop.png%'");
            $pdo->exec("UPDATE blogs SET image = 'assets/img/blog-4.jpg' WHERE slug = 'top-5-ai-skills' AND image LIKE '%student-laptop.png%'");
            $pdo->exec("UPDATE blogs SET image = 'assets/img/blog-5.jpg' WHERE slug = 'how-educators-can-bring-ai-to-their-classrooms' AND image LIKE '%student-laptop.png%'");
        }
    } catch (PDOException $e) {
        // fail silently
    }
}

/**
 * Automatically sync blog SEO & OpenGraph columns in database if not present.
 */
function sync_blog_schema_columns(PDO $pdo) {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM blogs LIKE 'image_alt'");
        if (!$stmt->fetch()) {
            $pdo->exec("
                ALTER TABLE blogs
                  ADD COLUMN image_alt varchar(255) DEFAULT NULL AFTER image,
                  ADD COLUMN meta_keywords text DEFAULT NULL AFTER meta_description,
                  ADD COLUMN schema_article longtext DEFAULT NULL AFTER meta_keywords,
                  ADD COLUMN schema_faq longtext DEFAULT NULL AFTER schema_article,
                  ADD COLUMN og_title varchar(255) DEFAULT NULL AFTER schema_faq,
                  ADD COLUMN og_description text DEFAULT NULL AFTER og_title,
                  ADD COLUMN og_image varchar(255) DEFAULT NULL AFTER og_description,
                  ADD COLUMN twitter_title varchar(255) DEFAULT NULL AFTER og_image,
                  ADD COLUMN twitter_description text DEFAULT NULL AFTER twitter_title,
                  ADD COLUMN twitter_image varchar(255) DEFAULT NULL AFTER twitter_description
            ");
        }
    } catch (PDOException $e) {
        // fail silently
    }
}

/**
 * Automatically sync pages_seo table in database if not present.
 */
function sync_pages_seo_table(PDO $pdo) {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `pages_seo` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `page_key` varchar(64) NOT NULL,
              `page_name` varchar(100) NOT NULL,
              `page_url` varchar(255) NOT NULL,
              `meta_title` varchar(255) NOT NULL DEFAULT '',
              `meta_description` text DEFAULT NULL,
              `meta_keywords` text DEFAULT NULL,
              `h1_heading` varchar(255) NOT NULL DEFAULT '',
              `content` longtext DEFAULT NULL,
              `canonical_url` varchar(255) DEFAULT '',
              `og_title` varchar(255) DEFAULT '',
              `og_description` text DEFAULT NULL,
              `og_image` varchar(255) DEFAULT '',
              `schema_json` longtext DEFAULT NULL,
              `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `uniq_page_key` (`page_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");

        $cnt = (int)$pdo->query("SELECT COUNT(*) FROM `pages_seo`")->fetchColumn();
        if ($cnt === 0) {
            $pdo->exec("
                INSERT IGNORE INTO `pages_seo` (`page_key`, `page_name`, `page_url`, `meta_title`, `meta_description`, `meta_keywords`, `h1_heading`, `content`, `canonical_url`) VALUES
                ('home', 'Home Page', '/', 'AI Education Company India – AI Courses & Certification – SSD Prayas', 'SSD Prayas — India\'s AI education company. Online AI courses, certification & beginner programmes for students, educators & professionals.', 'ai education company, best ai learning platform india, ai for education, ai education india, ai certification, online ai classes for students, ai course for beginners india, ssd prayas, nep 2020 ai education, ai skilling india', 'AI Education for a Future-Ready Bharat', 'Practical, hands-on AI education for school students, teachers and working professionals across India.', 'https://ssdprayas.com/'),
                ('ai-for-school', 'AI for School', '/ai-for-school', 'AI for Schools in India – AI Training – SSD Prayas', 'SSD Prayas delivers AI education for schools across India — training educators, teaching AI in schools with a NEP 2020 curriculum and certification.', 'ai for schools in india, ai training for schools, ai education for schools, teaching ai in schools, ai teaching school, nep 2020 ai curriculum, ai for school programme, school ai certification', 'AI for Schools in India', 'We don\'t hand over a syllabus and walk away. Our AI training for schools model puts your own teachers through certified training, runs classes inside the computer lab you already have, and leaves your campus capable of teaching AI on its own — long after our team has moved to the next school.', 'https://ssdprayas.com/ai-for-school'),
                ('programmes', 'Programmes', '/programmes', 'AI Programmes for Students, Educators & Professionals | SSD Prayas', 'Grade-wise AI curriculum for Class 3–12, L1/L2 educator training, and applied AI upskilling for working professionals — online or offline.', 'ai programmes, ai curriculum school, teacher ai training, nep 2020 ai courses, professional ai skilling', 'The SSD Prayas Learning Journey', 'AI is not one course taught once. Our curriculum grows with the learner — from a Class 3 child meeting a computer, to a Class 12 student building real AI projects, to a teacher who can carry the whole programme forward.', 'https://ssdprayas.com/programmes'),
                ('government', 'Government Projects', '/government', 'Government AI Skilling Projects Across India | SSD Prayas', 'State-scale AI skilling for government departments — partner onboarding, L1/L2 educator batches, student enrolment and certification tracking.', 'government ai skilling, state scale ai education, institutional ai training india, government school ai project', 'Large-Scale AI Skilling, Across Multiple States', 'SSD Prayas delivers government and institutional AI skilling at state scale — managing partner onboarding, educator training batches, student enrolment and certification tracking through a single monitored system.', 'https://ssdprayas.com/government'),
                ('about', 'About Us', '/about', 'About SSD Prayas – Our AI Education Mission', 'SSD Prayas brings practical AI education to students, educators and professionals across India — online, offline and at government scale.', 'about ssd prayas, ai education mission, future ready bharat, practical ai skills', 'Building India\'s AI-Ready Generation', 'SSD Prayas exists for one reason — to make sure practical AI skills reach every classroom, every teacher and every working professional, not just the ones in metro cities.', 'https://ssdprayas.com/about'),
                ('careers', 'Careers', '/careers', 'Careers at SSD Prayas – Hiring AI Educators', 'Join SSD Prayas as an AI Educator. Hybrid roles across Indian states, training students and teachers in practical AI. Apply with your resume.', 'ai educator jobs, ai teaching careers india, trainer jobs ai, edtech educator hiring', 'Teach the Skill That Changes Careers', 'SSD Prayas hires educators only. If you can hold a classroom and you are willing to learn AI properly, we will train you, certify you and put you in front of students who need you.', 'https://ssdprayas.com/careers'),
                ('contact', 'Contact Us', '/contact', 'Contact SSD Prayas – Start Your AI Journey', 'Talk to SSD Prayas about bringing AI education to your school or organisation. Call, WhatsApp or send an enquiry — we respond within 24 hours.', 'contact ssd prayas, book ai demo, school ai partnership, ai training inquiry', 'Let\'s Plan Your AI Roll-out', 'Whether you want to introduce AI across a school network, certify your teachers or discuss a government-scale rollout — talk to our team.', 'https://ssdprayas.com/contact'),
                ('blogs', 'Blogs Listing', '/blogs', 'SSD Prayas Blog – AI Education Insights & Stories', 'Stories and insights on AI education, NEP 2020 and educator training — what\'s really working inside Indian classrooms.', 'ai education blog, nep 2020 ai articles, classroom ai insights, ai for school stories', 'The SSD Prayas Blog', 'Stories, ideas and insights on AI education, NEP 2020, educator training and what\'s really working inside Indian classrooms.', 'https://ssdprayas.com/blogs')
            ");
        }
    } catch (PDOException $e) {
        // fail silently
    }
}

/**
 * Fetch SEO settings for a given page key.
 */
function get_page_seo(PDO $pdo, string $page_key): ?array {
    static $cache = [];
    if (isset($cache[$page_key])) {
        return $cache[$page_key];
    }
    try {
        $stmt = $pdo->prepare("SELECT * FROM pages_seo WHERE page_key = ? LIMIT 1");
        $stmt->execute([$page_key]);
        $row = $stmt->fetch();
        $cache[$page_key] = $row ?: null;
        return $cache[$page_key];
    } catch (PDOException $e) {
        return null;
    }
}

// Note: Schema sync functions (sync_pages_seo_table, sync_blog_schema_columns)
// are migration helpers and should only be invoked from admin setup, never on every visitor page load.

