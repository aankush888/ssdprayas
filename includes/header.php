<?php
require_once __DIR__ . '/functions.php';

// Each page sets these before including this file.
$page       = $page       ?? 'home';
$page_title = $page_title ?? SITE_NAME . ' | ' . SITE_TAGLINE;
$page_desc  = $page_desc  ?? 'SSD Prayas delivers NEP 2020-aligned AI skilling for school students (Class 3-12), educators and working professionals — online and offline, across India.';
$page_image  = $page_image  ?? asset('img/og-default.png');
$page_robots   = $page_robots ?? 'index, follow';
$page_keywords = $page_keywords ?? 'SSD Prayas, AI education India, AI course for school students, AI training for educators, AI skilling for professionals, NEP 2020 AI, government AI skilling project';

// Anchors live on the homepage, so off-home pages need an absolute link.
$home = ($page === 'home') ? '' : url('/');
$canonical_url = $page_canonical ?? url($page === 'home' ? '/' : $page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="google-site-verification" content="CJXHhq68rWJ2jj791RqN6AUrDULSDZQRE0TDRzbtILs">

<title><?= e($page_title) ?></title>
<meta name="description" content="<?= e($page_desc) ?>">
<meta name="robots" content="<?= e($page_robots) ?>">
<meta name="author" content="<?= e(SITE_NAME) ?>">
<meta name="keywords" content="<?= e($page_keywords) ?>">
<link rel="canonical" href="<?= e($canonical_url) ?>">

<link rel="icon" type="image/png" href="<?= e(url('favicon.png')) ?>">
<link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">

<meta property="og:type" content="<?= ($page === 'blog') ? 'article' : 'website' ?>">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:title" content="<?= e($page_title) ?>">
<meta property="og:description" content="<?= e($page_desc) ?>">
<meta property="og:url" content="<?= e($canonical_url) ?>">
<meta property="og:image" content="<?= e($page_image) ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:type" content="image/png">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($page_title) ?>">
<meta name="twitter:description" content="<?= e($page_desc) ?>">
<meta name="twitter:image" content="<?= e($page_image) ?>">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= e(asset_v('css/main.css')) ?>">

<!-- Enhanced Organization Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "EducationalOrganization",
  "name": "SSD Prayas",
  "url": "https://ssdprayas.com/",
  "logo": "https://ssdprayas.com/assets/ssdprayaslogo-1.png",
  "description": "India's AI education company delivering NEP 2020-aligned AI skilling for schools, educators, and professionals.",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "2nd Floor, 24, 7 Annexe, Danish Kunj, Kolar Rd",
    "addressLocality": "Bhopal",
    "addressRegion": "Madhya Pradesh",
    "postalCode": "462039",
    "addressCountry": "IN"
  },
  "contactPoint": {
    "@type": "ContactPoint",
    "telephone": "+91-9810450465",
    "contactType": "Customer Support",
    "areaServed": "IN",
    "availableLanguage": ["English", "Hindi"]
  },
  "sameAs": [
    "https://www.facebook.com/ssdprayas",
    "https://www.instagram.com/ssdprayas/",
    "https://www.linkedin.com/company/aiforschoolsindia/"
  ]
}
</script>

<?php if ($page === 'home'): ?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "SSD Prayas",
  "alternateName": ["SSDPrayas", "SSD Prayas AI", "AI for Schools India"],
  "url": "https://ssdprayas.com/"
}
</script>
<!-- FAQ Schema Code Placement in Head Section -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [{
    "@type": "Question",
    "name": "What makes SSD Prayas the best AI learning platform in India for schools?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Most platforms hand a school a login and call it done. We don't. SSD Prayas trains your own teachers, delivers the curriculum inside your existing classroom setup, and certifies both students and staff \u2014 so the learning doesn't disappear the moment a subscription ends. That combination of hands-on delivery, teacher independence, and NEP 2020 alignment is what schools tell us sets us apart from platforms that are really just video libraries with a login screen."
    }
  },{
    "@type": "Question",
    "name": "Is there an AI course for beginners in India, or do students need coding experience first?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "No coding background is needed to start. Our beginner-level modules are built for students who've never written a line of code \u2014 the early classes focus on how AI actually works in plain language, with simple hands-on tools, before anything resembling \"programming\" shows up. Coding gets introduced gradually as students move into higher grades or more advanced tracks, not on day one."
    }
  },{
    "@type": "Question",
    "name": "Do you offer online AI classes for students, or is everything in-person?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Both, depending on what a school needs. Some partner schools run sessions fully offline in their existing computer labs; others prefer online delivery, and a fair number end up doing a mix of the two. The curriculum itself doesn't change based on format \u2014 what changes is how it's delivered, and we build that around the school's infrastructure rather than forcing one model on everyone."
    }
  },{
    "@type": "Question",
    "name": "What does the AI certification actually cover, and is it recognised outside SSD Prayas?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Certification is tied to completed, assessed work \u2014 not attendance. Students and educators go through an assessment and verification check before any certificate is issued, so it reflects what was actually learned, not just a completed calendar. For working professionals in particular, the certificate is designed to be something you can genuinely point to in a resume or interview, not a participation trophy."
    }
  },{
    "@type": "Question",
    "name": "Can working professionals join, or is this only for schools and students?",
    "acceptedAnswer": {
      "@type": "Answer",
      "text": "Professionals are one of our three main tracks, alongside students and educators. The professional track is applied rather than academic \u2014 real tools, real workflows, project-based learning \u2014 aimed at people upskilling for their current job or pivoting into AI-adjacent roles, not at people looking for a theory-heavy refresher course."
    }
  }]
}
</script>
<?php endif; ?>
<?php if (!empty($custom_schema)): ?><?= $custom_schema ?><?php endif; ?>
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
