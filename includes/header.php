<?php
require_once __DIR__ . '/functions.php';

// Each page sets these before including this file.
$page       = $page       ?? 'home';
$page_title = $page_title ?? SITE_NAME . ' | ' . SITE_TAGLINE;
$page_desc  = $page_desc  ?? 'SSD Prayas delivers NEP 2020-aligned AI skilling for school students (Class 3-12), educators and working professionals — online and offline, across India.';
$page_image = $page_image ?? asset('img/og-default.png');

// Anchors live on the homepage, so off-home pages need an absolute link.
$home = ($page === 'home') ? '' : url('/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e($page_desc) ?>">
<meta name="robots" content="index, follow">
<meta name="author" content="<?= e(SITE_NAME) ?>">
<!-- TODO: keywords Jai bhaiya se aane hain -->
<meta name="keywords" content="SSD Prayas, AI education India, AI course for school students, AI training for educators, AI skilling for professionals, NEP 2020 AI, government AI skilling project">
<link rel="canonical" href="<?= e(url($page === 'home' ? '/' : $page)) ?>">

<link rel="icon" type="image/png" href="<?= e(url('favicon.png')) ?>">
<link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">

<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:title" content="<?= e($page_title) ?>">
<meta property="og:description" content="<?= e($page_desc) ?>">
<meta property="og:url" content="<?= e(url($page === 'home' ? '/' : $page)) ?>">
<meta property="og:image" content="<?= e($page_image) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($page_title) ?>">
<meta name="twitter:description" content="<?= e($page_desc) ?>">
<meta name="twitter:image" content="<?= e($page_image) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= e(asset_v('css/main.css')) ?>">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": "<?= e(SITE_NAME) ?>",
  "url": "<?= e(url('/')) ?>",
  "description": "<?= e($page_desc) ?>",
  "telephone": "<?= e(CONTACT_PHONE) ?>",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "The DM Tower, Danish Kunj, Kolar Road",
    "addressLocality": "Bhopal",
    "addressRegion": "Madhya Pradesh",
    "postalCode": "462039",
    "addressCountry": "IN"
  },
  "areaServed": "IN",
  "sameAs": [
    "<?= e(SOCIAL_FACEBOOK) ?>",
    "<?= e(SOCIAL_INSTAGRAM) ?>",
    "<?= e(SOCIAL_LINKEDIN) ?>"
  ]
}
</script>
</head>
<body>

<header class="site-header" id="siteHeader">
  <div class="container nav-wrap">

    <a href="<?= e(url('/')) ?>" class="brand" aria-label="<?= e(SITE_NAME) ?> home">
      <img class="brand-logo" src="<?= e(asset(SITE_LOGO)) ?>"
           alt="<?= e(SITE_NAME) ?> — <?= e(SITE_TAGLINE) ?>" width="602" height="134">
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Menu" aria-expanded="false" aria-controls="navMenu">
      <i class="fas fa-bars"></i>
    </button>

    <nav class="nav-menu" id="navMenu">
      <a href="<?= e(url('/')) ?>" class="<?= $page === 'home' ? 'active' : '' ?>">Home</a>
      <a href="<?= e(url('programmes')) ?>" class="<?= $page === 'programmes' ? 'active' : '' ?>">Programmes</a>
      <a href="<?= e(url('ai-for-school')) ?>" class="<?= $page === 'ai-for-school' ? 'active' : '' ?>">AI for School</a>
      <a href="<?= e(url('government')) ?>" class="<?= $page === 'government' ? 'active' : '' ?>">Government</a>
      <a href="<?= e(url('about')) ?>" class="<?= $page === 'about' ? 'active' : '' ?>">About</a>
      <a href="<?= e(url('careers')) ?>" class="<?= $page === 'careers' ? 'active' : '' ?>">Careers</a>
      <a href="<?= e(url('blogs')) ?>" class="<?= in_array($page, ['blogs', 'blog'], true) ? 'active' : '' ?>">Blogs</a>
      <a href="<?= e(url('contact')) ?>" class="<?= $page === 'contact' ? 'active' : '' ?>">Contact</a>
    </nav>

    <div class="nav-actions">
      <a href="<?= e(whatsapp_link('Hello SSD Prayas, I would like to know more about your AI programmes.')) ?>"
         class="btn btn-ghost btn-sm btn-wa" target="_blank" rel="noopener" aria-label="WhatsApp">
        <i class="fab fa-whatsapp" style="color:#25D366;font-size:17px"></i>
      </a>
      <a href="<?= e(url('contact')) ?>" class="btn btn-primary btn-sm">Start Your AI Journey</a>
    </div>

  </div>
</header>
