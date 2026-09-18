<?php
require_once __DIR__ . '/includes/functions.php';

$page          = 'ai-for-school';
$page_title    = 'AI for Schools in India – AI Training – SSD Prayas';
$page_desc     = 'SSD Prayas delivers AI education for schools across India — training educators, teaching AI in schools with a NEP 2020 curriculum and certification.';
$page_keywords = 'ai for schools in india, ai training for schools, ai education for schools, teaching ai in schools, ai teaching school, nep 2020 ai curriculum, ai for school programme, school ai certification';
$page_robots   = 'index, follow';
$page_canonical= url('ai-for-school');
$page_image    = asset('img/ai-school-hero.jpg');

$stats = impact_stats($pdo);

// FAQ Schema & Breadcrumb Schema
$faq_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'Do we need to buy any new equipment?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'No. Our AI training for schools is built to run on whatever computers your school already has. Basic internet is enough, and several activities work offline too.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'Which classes can join?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Class 3 to 12. Most schools start with a few grades and expand once the first batch wraps up — the curriculum is graded, so every class gets age-appropriate content.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'What happens to our teachers?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'They go through our L1 and L2 certification batches. The real goal of this AI education for schools model is that your staff can keep teaching AI independently after the first year, without leaning on us.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'Is the programme offline or online?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Mostly offline, delivered on your campus. Online and hybrid formats are available for schools where travel is difficult or for more remote districts.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'What certification do students receive?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Students who clear the assessment get a completion certificate for their class level, issued only after the batch is verified through our e-check process.',
            ],
        ],
    ],
];

$breadcrumb_schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Home',
            'item' => url('/'),
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'AI for School Programme',
            'item' => url('ai-for-school'),
        ],
    ],
];

$custom_schema = '<script type="application/ld+json">' . json_encode($faq_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n"
               . '<script type="application/ld+json">' . json_encode($breadcrumb_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';

include __DIR__ . '/includes/header.php';
?>

<main class="page-school">

<!-- ============ HERO SECTION ============ -->
<section class="school-hero">
  <div class="container">
    <div class="school-hero-content">
      <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; AI for School Programme</p>
      <span class="eyebrow is-green"><i class="fas fa-school"></i> Flagship Programme</span>
      <h1>AI for <span class="text-blue">Schools</span> in <span class="text-green">India</span></h1>
      <p class="hero-lead">We don't hand over a syllabus and walk away. Our AI training for schools model puts your own teachers through certified training, runs classes inside the computer lab you already have, and leaves your campus capable of teaching AI on its own — long after our team has moved to the next school.</p>
      <div class="hero-actions" style="justify-content: flex-start;">
        <a href="<?= e(url('contact')) ?>" class="btn btn-primary btn-pill">Partner Your School <i class="fas fa-arrow-right"></i></a>
        <a href="<?= e(whatsapp_link('Hello SSD Prayas, I want to know about the AI for School programme.')) ?>"
           class="btn btn-white-pill" target="_blank" rel="noopener">
          <i class="fab fa-whatsapp" style="color:#25D366"></i> Book a Demo
        </a>
      </div>
    </div>
  </div>
  <div class="school-hero-wave-corner" aria-hidden="true">
    <svg viewBox="0 0 360 90" preserveAspectRatio="none">
      <path d="M0,90 C90,45 180,85 270,30 C310,5 340,0 360,0 L360,90 Z" fill="url(#heroWaveGrad)"></path>
      <defs>
        <linearGradient id="heroWaveGrad" x1="0" y1="0" x2="360" y2="90" gradientUnits="userSpaceOnUse">
          <stop stop-color="#1a73e8"/>
          <stop offset="1" stop-color="#0b57d0"/>
        </linearGradient>
      </defs>
    </svg>
  </div>
</section>

<!-- ============ WHAT YOUR SCHOOL GETS ============ -->
<section class="section section-school-gets">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow is-blue"><i class="fas fa-box-open"></i> What's Included</span>
      <h2>What Your <span class="text-blue">School Gets</span></h2>
      <p>One programme, six moving parts — all handled by us.</p>
    </div>

    <!-- Dual Continuous Sliding Marquees: Row 1 Scrolls Left, Row 2 Scrolls Right -->
    <div class="school-marquee-container">
      <!-- Row 1: Continuously scrolls to the Left -->
      <div class="school-marquee-wrapper is-scroll-left" aria-label="School programme features row 1">
        <div class="school-marquee-track track-scroll-left">
          <?php for ($i = 0; $i < 4; $i++): ?>
          <div class="school-marquee-set" <?= $i > 0 ? 'aria-hidden="true"' : '' ?>>
            <article class="school-card">
              <div class="s-card-icon i-blue"><i class="fas fa-book-open"></i></div>
              <div class="s-card-body">
                <h3>Grade-wise Curriculum</h3>
                <p>A complete Class 3 to 12 AI syllabus, NEP 2020-aligned and paced to what each age group can genuinely take in — not a one-size-fits-all deck.</p>
              </div>
            </article>

            <article class="school-card">
              <div class="s-card-icon i-green"><i class="fas fa-chalkboard-user"></i></div>
              <div class="s-card-body">
                <h3>Educator Training</h3>
                <p>This is where AI education for schools actually sticks. Your teachers go through structured L1 and L2 training on campus, walking away with lesson plans and classroom-ready material, not just a certificate.</p>
              </div>
            </article>

            <article class="school-card">
              <div class="s-card-icon i-yellow"><i class="fas fa-flask"></i></div>
              <div class="s-card-body">
                <h3>Hands-on Lab Sessions</h3>
                <p>Teaching AI in schools works best as a practical subject, not a theory class. Our trained faculty run project-based sessions in your existing computer lab — no new hardware, no special setup.</p>
              </div>
            </article>
          </div>
          <?php endfor; ?>
        </div>
      </div>

      <!-- Row 2: Continuously scrolls to the Right -->
      <div class="school-marquee-wrapper is-scroll-right" aria-label="School programme features row 2">
        <div class="school-marquee-track track-scroll-right">
          <?php for ($i = 0; $i < 4; $i++): ?>
          <div class="school-marquee-set" <?= $i > 0 ? 'aria-hidden="true"' : '' ?>>
            <article class="school-card">
              <div class="s-card-icon i-red"><i class="fas fa-chess-knight"></i></div>
              <div class="s-card-body">
                <h3>Projects &amp; Exhibitions</h3>
                <p>Students build real AI projects and showcase them at school exhibitions, turning classroom learning into a portfolio piece they can talk about with confidence.</p>
              </div>
            </article>

            <article class="school-card">
              <div class="s-card-icon i-blue"><i class="fas fa-clipboard-check"></i></div>
              <div class="s-card-body">
                <h3>Assessment &amp; E-Check</h3>
                <p>Every batch is assessed and passed through our e-check verification before a single certificate goes out — so certification actually means something.</p>
              </div>
            </article>

            <article class="school-card">
              <div class="s-card-icon i-green"><i class="fas fa-award"></i></div>
              <div class="s-card-body">
                <h3>Certification</h3>
                <p>Students and teachers both receive recognised certificates, and the batch is logged as work-certified in our system for future reference.</p>
              </div>
            </article>
          </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ HOW IT RUNS + REQUIREMENTS + STATS ============ -->
<section class="section section-school-process">
  <div class="container">
    <!-- Upper Split -->
    <div class="school-process-split">
      <!-- Left: Timeline -->
      <div class="school-process-left reveal">
        <span class="eyebrow is-red"><i class="fas fa-diagram-project"></i> On Campus</span>
        <h2>How the <span class="text-blue">Programme Runs</span></h2>
        <p class="process-sub">From first conversation to certificates in hand, a typical AI teaching school engagement moves through five stages.</p>

        <div class="school-timeline">
          <div class="st-item">
            <div class="st-badge">01</div>
            <div class="st-content">
              <h3>01 — School Onboarding</h3>
              <p>We visit, walk through your infrastructure and class strengths, and agree on scope and a realistic calendar together.</p>
            </div>
          </div>

          <div class="st-item">
            <div class="st-badge">02</div>
            <div class="st-content">
              <h3>02 — Curriculum Mapping</h3>
              <p>The grade-wise syllabus gets mapped against your timetable and board requirements, so it fits into the year you're already running.</p>
            </div>
          </div>

          <div class="st-item">
            <div class="st-badge">03</div>
            <div class="st-content">
              <h3>03 — Educator Training</h3>
              <p>Teachers are grouped into L1 and L2 batches and trained on AI tools, teaching methods, and how to actually run a session — not just the theory behind one.</p>
            </div>
          </div>

          <div class="st-item">
            <div class="st-badge">04</div>
            <div class="st-content">
              <h3>04 — Classroom Delivery</h3>
              <p>Sessions run in your computer lab across the term — hands-on and project-based, with our faculty working alongside your teachers rather than instead of them.</p>
            </div>
          </div>

          <div class="st-item">
            <div class="st-badge">05</div>
            <div class="st-content">
              <h3>05 — Assessment &amp; Certification</h3>
              <p>Students are assessed, the batch clears e-check, and AI certificates go out to both students and the educators we trained.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Requirements Cards -->
      <div class="school-process-right reveal">
        <div class="school-req-box">
          <h3><i class="fas fa-circle-check" style="color:var(--green)"></i> What We Need From the School</h3>
          <ul class="school-req-list">
            <li><i class="fas fa-circle-check"></i> An existing computer lab with working machines</li>
            <li><i class="fas fa-circle-check"></i> Basic internet connectivity — nothing elaborate</li>
            <li><i class="fas fa-circle-check"></i> Timetable slots for the AI period</li>
            <li><i class="fas fa-circle-check"></i> Two or more teachers willing to be trained</li>
            <li><i class="fas fa-circle-check"></i> A coordinator we can work with directly</li>
          </ul>
        </div>

        <div class="school-req-box is-cross-box">
          <h3 class="is-red"><i class="fas fa-circle-xmark" style="color:var(--red)"></i> What You Don't Need</h3>
          <ul class="school-req-list is-cross">
            <li><i class="fas fa-circle-xmark"></i> New hardware or costly AI kits</li>
            <li><i class="fas fa-circle-xmark"></i> Existing computer-science expertise on staff</li>
            <li><i class="fas fa-circle-xmark"></i> A separate lab or dedicated room</li>
            <li><i class="fas fa-circle-xmark"></i> Paid software licences</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Lower Royal Blue Stats Bar -->
    <div class="school-stats-band reveal">
      <div class="st-stat">
        <div class="st-icon s-blue"><i class="fas fa-landmark"></i></div>
        <div class="st-info">
          <strong data-count="<?= (int)display_stat($stats['partners'], 250) ?>" data-suffix="+">250+</strong>
          <span>Partner Schools</span>
        </div>
      </div>

      <div class="st-stat">
        <div class="st-icon s-green"><i class="fas fa-graduation-cap"></i></div>
        <div class="st-info">
          <strong data-count="<?= (int)display_stat($stats['educators'], 800) ?>" data-suffix="+">800+</strong>
          <span>Educators Trained</span>
        </div>
      </div>

      <div class="st-stat">
        <div class="st-icon s-cyan"><i class="fas fa-users"></i></div>
        <div class="st-info">
          <strong data-count="<?= (int)display_stat($stats['students'], 25000) ?>" data-suffix="+">25,000+</strong>
          <span>Students Reached</span>
        </div>
      </div>

      <div class="st-stat">
        <div class="st-icon s-teal"><i class="fas fa-location-dot"></i></div>
        <div class="st-info">
          <strong data-count="<?= (int)display_stat($stats['states'], 6) ?>" data-suffix="+">6+</strong>
          <span>States Covered</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ COMMON QUESTIONS ============ -->
<section class="section section-school-faq">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow is-blue"><i class="fas fa-circle-question"></i> Questions</span>
      <h2>Common <span class="text-blue">Questions</span></h2>
    </div>

    <div class="school-faq-wrap reveal">
      <details class="school-faq-item">
        <summary>
          <span>Do we need to buy any new equipment?</span>
          <i class="fas fa-plus"></i>
        </summary>
        <div class="school-faq-body">
          <p>No. Our AI training for schools is built to run on whatever computers your school already has. Basic internet is enough, and several activities work offline too.</p>
        </div>
      </details>

      <details class="school-faq-item">
        <summary>
          <span>Which classes can join?</span>
          <i class="fas fa-plus"></i>
        </summary>
        <div class="school-faq-body">
          <p>Class 3 to 12. Most schools start with a few grades and expand once the first batch wraps up — the curriculum is graded, so every class gets age-appropriate content.</p>
        </div>
      </details>

      <details class="school-faq-item">
        <summary>
          <span>What happens to our teachers?</span>
          <i class="fas fa-plus"></i>
        </summary>
        <div class="school-faq-body">
          <p>They go through our L1 and L2 certification batches. The real goal of this AI education for schools model is that your staff can keep teaching AI independently after the first year, without leaning on us.</p>
        </div>
      </details>

      <details class="school-faq-item">
        <summary>
          <span>Is the programme offline or online?</span>
          <i class="fas fa-plus"></i>
        </summary>
        <div class="school-faq-body">
          <p>Mostly offline, delivered on your campus. Online and hybrid formats are available for schools where travel is difficult or for more remote districts.</p>
        </div>
      </details>

      <details class="school-faq-item">
        <summary>
          <span>What certification do students receive?</span>
          <i class="fas fa-plus"></i>
        </summary>
        <div class="school-faq-body">
          <p>Students who clear the assessment get a completion certificate for their class level, issued only after the batch is verified through our e-check process.</p>
        </div>
      </details>
    </div>
  </div>
</section>

<!-- ============ CTA BANNER ============ -->
<section class="section-tight" style="padding-bottom:90px">
  <div class="container">
    <div class="school-cta-box reveal">
      <div class="school-cta-graphic">
        <img src="<?= e(asset('img/cta-school.png')) ?>" alt="Bring AI Teaching to Your School Campus — SSD Prayas" class="cta-school-img">
      </div>
      <div class="school-cta-content">
        <h2>Bring AI Teaching to Your School Campus</h2>
        <p>Tell us about your school, and we'll put together a roll-out plan along with a demo session for your management team.</p>
      </div>
      <div class="school-cta-actions">
        <a href="<?= e(url('contact')) ?>" class="btn btn-yellow-pill">Partner Your School <i class="fas fa-arrow-right"></i></a>
        <a href="tel:+<?= e(CONTACT_PHONE_RAW) ?>" class="btn btn-ghost-pill"><i class="fas fa-phone"></i> Call Now</a>
      </div>
    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
