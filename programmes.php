<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'programmes';
$page_title = 'Programmes | ' . SITE_NAME;
$page_desc  = 'SSD Prayas AI programmes: a grade-wise curriculum for Class 3 to 12, L1 and L2 educator training, and applied AI upskilling for working professionals.';

$stats = impact_stats($pdo);

$show_partners  = display_stat($stats['partners'],  250);
$show_educators = display_stat($stats['educators'], 800);
$show_students  = display_stat($stats['students'],  25000);
$show_states    = display_stat($stats['states'],    6);

include __DIR__ . '/includes/header.php';
?>

<main class="page-programmes">

<!-- ============ HERO SECTION ============ -->
<section class="prog-hero" id="hero">
  <div class="container">
    <div class="prog-hero-content">
      <span class="eyebrow is-blue"><i class="fas fa-graduation-cap"></i> Our Programmes</span>
      <h1>The SSD Prayas<br><span class="text-blue">Learning</span> <span class="text-teal">Journey</span></h1>
      <p class="prog-hero-lead">
        AI is not one course taught once. Our curriculum grows with the learner — from a Class 3 child meeting a computer, to a Class 12 student building real AI projects, to a teacher who can carry the whole programme forward.
      </p>
      
      <div class="prog-hero-actions">
        <a href="<?= e(url('contact')) ?>" class="btn btn-primary btn-pill">Enroll Your School <i class="fas fa-arrow-right"></i></a>
        <a href="<?= e(url('ai-for-school')) ?>" class="btn btn-white-pill">
          <i class="far fa-circle-play text-blue"></i> Watch Video
        </a>
      </div>

      <div class="prog-hero-highlights">
        <div class="ph-item">
          <div class="ph-icon"><i class="fas fa-microchip"></i></div>
          <span>Industry Relevant AI Curriculum</span>
        </div>
        <div class="ph-item">
          <div class="ph-icon"><i class="fas fa-user-gear"></i></div>
          <span>Practical, Hands-on Learning</span>
        </div>
        <div class="ph-item">
          <div class="ph-icon"><i class="fas fa-map-location-dot"></i></div>
          <span>Across India Impact</span>
        </div>
        <div class="ph-item">
          <div class="ph-icon"><i class="fas fa-shield-halved"></i></div>
          <span>Trusted by Schools &amp; Educators</span>
        </div>
      </div>
    </div>
  </div>

  <div class="prog-hero-contour" aria-hidden="true">
    <svg viewBox="0 0 600 600" fill="none" preserveAspectRatio="none">
      <path d="M340,0 C420,180 320,380 480,600" stroke="#3b82f6" stroke-width="2" stroke-opacity="0.25" />
      <path d="M360,0 C440,180 340,380 500,600" stroke="#60a5fa" stroke-width="1.5" stroke-opacity="0.15" />
    </svg>
  </div>
</section>

<!-- ============ GRADE BY GRADE (DARK NAVY CARD) ============ -->
<section class="section section-prog-grades">
  <div class="container">
    <div class="prog-grades-card reveal">
      
      <!-- Left Heading -->
      <div class="pgc-left">
        <span class="eyebrow is-trans-blue"><i class="fas fa-graduation-cap"></i> FOR STUDENTS</span>
        <h2>Class 3 to 12,<br>Grade by <span class="text-green-light">Grade</span></h2>
        <p>Each stage builds on the last. No child is thrown into machine learning on day one.</p>
      </div>

      <!-- Right 4 Stages -->
      <div class="pgc-stages">
        <div class="pgc-stage-item">
          <div class="pgc-node node-blue"><i class="fas fa-user"></i></div>
          <span class="pgc-class">CLASS 3 – 5</span>
          <h3>Digital Foundations</h3>
          <p>Computer confidence, safe internet habits and first look at artificial intelligence.</p>
        </div>

        <div class="pgc-stage-item">
          <div class="pgc-node node-green"><i class="fas fa-puzzle-piece"></i></div>
          <span class="pgc-class">CLASS 6 – 8</span>
          <h3>AI Fundamentals</h3>
          <p>Computational thinking, logic, coding and the core ideas behind machine learning.</p>
        </div>

        <div class="pgc-stage-item">
          <div class="pgc-node node-orange"><i class="fas fa-network-wired"></i></div>
          <span class="pgc-class">CLASS 9 – 10</span>
          <h3>Applied ML</h3>
          <p>Real datasets, real AI tools and real problems. Students build projects with purpose.</p>
        </div>

        <div class="pgc-stage-item">
          <div class="pgc-node node-red"><i class="fas fa-rocket"></i></div>
          <span class="pgc-class">CLASS 11 – 12</span>
          <h3>Specialisation &amp; Careers</h3>
          <p>Advanced AI, capstone projects, certification and clear guidance on careers that follow.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============ STATS BAR ============ -->
<section class="section-tight" style="padding: 10px 0 46px;">
  <div class="container">
    <div class="home-stats-card reveal">
      <div class="hs-stat">
        <div class="hs-icon hs-blue"><i class="fas fa-landmark"></i></div>
        <div class="hs-info">
          <strong data-count="<?= (int)$show_partners ?>" data-suffix="+">250+</strong>
          <span>Partner Institutions</span>
        </div>
      </div>

      <div class="hs-stat">
        <div class="hs-icon hs-green"><i class="fas fa-graduation-cap"></i></div>
        <div class="hs-info">
          <strong data-count="<?= (int)$show_educators ?>" data-suffix="+">800+</strong>
          <span>Educators Trained</span>
        </div>
      </div>

      <div class="hs-stat">
        <div class="hs-icon hs-yellow"><i class="fas fa-users"></i></div>
        <div class="hs-info">
          <strong data-count="<?= (int)$show_students ?>" data-suffix="+">25,000+</strong>
          <span>Students Impacted</span>
        </div>
      </div>

      <div class="hs-stat">
        <div class="hs-icon hs-purple"><i class="fas fa-globe"></i></div>
        <div class="hs-info">
          <strong data-count="<?= (int)$show_states ?>" data-suffix="+">6+</strong>
          <span>States Covered</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ 3 HORIZONTAL TRACK CARDS ============ -->
<section class="section section-prog-tracks" style="padding-top: 0; padding-bottom: 70px;">
  <div class="container">
    <div class="grid grid-3 prog-tracks-grid">

      <!-- Card 1: Students -->
      <article class="prog-h-card reveal">
        <div class="phc-content">
          <span class="phc-badge is-blue">FOR STUDENTS</span>
          <h3>AI for Students</h3>
          <p>Future-ready AI skills, hands-on projects and real-world exposure to build tomorrow's leaders.</p>
          <a href="<?= e(url('contact')) ?>" class="phc-link link-blue">Explore Programme <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="phc-visual bg-blue-arch">
          <img src="<?= e(asset('img/prog-student-portrait.png')) ?>" alt="AI for Students" loading="lazy">
        </div>
      </article>

      <!-- Card 2: Educators -->
      <article class="prog-h-card reveal">
        <div class="phc-content">
          <span class="phc-badge is-green">FOR EDUCATORS</span>
          <h3>AI for Educators</h3>
          <p>Empowering teachers with the training and tools to bring AI into classrooms.</p>
          <a href="<?= e(url('contact')) ?>" class="phc-link link-green">Explore Training <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="phc-visual bg-green-arch">
          <img src="<?= e(asset('img/prog-educator-portrait.png')) ?>" alt="AI for Educators" loading="lazy">
        </div>
      </article>

      <!-- Card 3: Professionals -->
      <article class="prog-h-card reveal">
        <div class="phc-content">
          <span class="phc-badge is-orange">FOR PROFESSIONALS</span>
          <h3>AI for Professionals</h3>
          <p>Upskill with industry relevant AI tools and practical workflow to stay ahead.</p>
          <a href="<?= e(url('contact')) ?>" class="phc-link link-orange">Explore Courses <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="phc-visual bg-orange-arch">
          <img src="<?= e(asset('img/prog-prof-portrait.png')) ?>" alt="AI for Professionals" loading="lazy">
        </div>
      </article>

    </div>
  </div>
</section>

<!-- ============ DELIVERY MODE ============ -->
<section class="section section-soft" id="mode">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow is-blue"><i class="fas fa-tower-broadcast"></i> Delivery Mode</span>
      <h2>Learn <span class="text-blue">Online</span>, <span class="text-green">Offline</span> or Both</h2>
      <p>Geography should never decide who gets AI education. The same curriculum is delivered in whichever format works for your school, district or team.</p>
    </div>

    <div class="grid grid-3 mode-grid">
      <div class="mode-card reveal">
        <div class="mode-icon is-blue"><i class="fas fa-chalkboard-user"></i></div>
        <h3>Offline</h3>
        <p>Trained faculty travel to your campus and run hands-on sessions in your existing computer lab — the format most schools and government projects prefer.</p>
      </div>

      <div class="mode-card reveal">
        <div class="mode-icon is-green"><i class="fas fa-video"></i></div>
        <h3>Online</h3>
        <p>Live instructor-led virtual classrooms with recordings, digital worksheets and remote mentor support — ideal for professionals and remote districts.</p>
      </div>

      <div class="mode-card reveal">
        <div class="mode-icon is-orange"><i class="fas fa-shuffle"></i></div>
        <h3>Hybrid</h3>
        <p>Offline practical labs combined with online theory and mentoring — the model we use for large multi-district educator training.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============ CTA BANNER ============ -->
<section class="section-tight" style="padding-bottom: 90px;">
  <div class="container">
    <div class="home-cta-box reveal">
      <div class="home-cta-content">
        <h2>Not sure which programme fits?</h2>
        <p>Tell us the classes, the batch size and your timeline. We will map the right curriculum and delivery mode for you.</p>
      </div>
      <div class="home-cta-actions">
        <a href="<?= e(url('contact')) ?>" class="btn btn-yellow-pill">Talk to Our Team <i class="fas fa-arrow-right"></i></a>
        <a href="tel:+<?= e(CONTACT_PHONE_RAW) ?>" class="btn btn-ghost-pill"><i class="fas fa-phone"></i> Call Now</a>
      </div>
    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
