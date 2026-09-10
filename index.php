<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'home';
$page_title = SITE_NAME . ' | AI Skilling for Students, Educators & Professionals';
$page_desc  = 'SSD Prayas delivers NEP 2020-aligned AI skilling across India — Class 3 to 12 students, school educators and working professionals. Online and offline, with government and school partnerships.';

/* ---------- Page data ---------- */
$stats  = impact_stats($pdo);
$posts  = rows($pdo, "SELECT title, slug, tag, excerpt, image, created_at
                      FROM blogs WHERE status = 'published'
                      ORDER BY created_at DESC LIMIT 3");

// Committed figures — live DB counts take over as the databases fill up.
$show_partners  = display_stat($stats['partners'],  250);
$show_educators = display_stat($stats['educators'], 800);
$show_students  = display_stat($stats['students'],  25000);
$show_states    = display_stat($stats['states'],    6);

include __DIR__ . '/includes/header.php';
?>

<main class="page-home">

<!-- ============ HERO SECTION ============ -->
<section class="home-hero" id="home">
  <div class="container">
    <div class="home-hero-content">
      
      <div class="google-partner-pill">
        <img src="<?= e(asset('img/google.png')) ?>" alt="Google" class="google-logo-sm">
        <span>Google for Education Partner</span>
      </div>

      <h1>AI Skilling for a<br><span class="text-blue">Future-Ready</span> Bharat</h1>

      <p class="home-hero-lead">
        Practical, hands-on AI education for school students, teachers and working professionals across India.
      </p>

      <div class="home-hero-actions">
        <a href="<?= e(url('contact')) ?>" class="btn btn-primary btn-pill">Start Your AI Journey <i class="fas fa-arrow-right"></i></a>
        <a href="<?= e(whatsapp_link('Hello SSD Prayas, I would like to book a demo.')) ?>"
           class="btn btn-white-pill" target="_blank" rel="noopener">
          <i class="far fa-circle-play text-blue"></i> Book a Demo
        </a>
      </div>

      <div class="home-hero-highlights">
        <div class="hh-item">
          <div class="hh-icon"><i class="fas fa-microchip"></i></div>
          <span>Industry Relevant AI Curriculum</span>
        </div>
        <div class="hh-item">
          <div class="hh-icon"><i class="fas fa-hand-sparkles"></i></div>
          <span>Practical, Hands-on Learning</span>
        </div>
        <div class="hh-item">
          <div class="hh-icon"><i class="fas fa-map-location-dot"></i></div>
          <span>Across India Impact</span>
        </div>
      </div>

    </div>
  </div>

  <div class="home-hero-wave-corner" aria-hidden="true">
    <svg viewBox="0 0 360 90" preserveAspectRatio="none">
      <path d="M0,90 C90,45 180,85 270,30 C310,5 340,0 360,0 L360,90 Z" fill="url(#homeHeroWaveGrad)"></path>
      <defs>
        <linearGradient id="homeHeroWaveGrad" x1="0" y1="0" x2="360" y2="90" gradientUnits="userSpaceOnUse">
          <stop stop-color="#1a73e8"/>
          <stop offset="1" stop-color="#0b57d0"/>
        </linearGradient>
      </defs>
    </svg>
  </div>
</section>

<!-- ============ TRACK CARDS (Continuous Left-to-Right Slider) ============ -->
<section class="section section-tracks">
  <div class="tracks-slider-wrapper" aria-label="SSD Prayas Tracks continuous slider">
    <div class="tracks-slider-track">
      <?php for ($set = 0; $set < 4; $set++): ?>
      <div class="tracks-slider-set" <?= $set > 0 ? 'aria-hidden="true"' : '' ?>>

        <!-- Card 1: Students -->
        <article class="home-track-card">
          <div class="htrack-media">
            <img src="<?= e(asset('img/card-students.jpg')) ?>" alt="Students in a school computer lab learning with AI" loading="lazy">
            <span class="htrack-tag is-blue">FOR STUDENTS</span>
          </div>
          <div class="htrack-info">
            <h3>AI for Students</h3>
            <p>A grade-wise curriculum that grows with the child &mdash; starting with digital literacy in the early years and moving into real AI projects and career-readiness by senior school. Our online AI classes for students are built so a Class 3 student and a Class 12 student are never learning the same thing in a different font &mdash; each stage has its own depth, tools, and outcomes.</p>
            <a href="<?= e(url('programmes')) ?>" class="htrack-link link-blue" <?= $set > 0 ? 'tabindex="-1"' : '' ?>>Explore Programme <i class="fas fa-arrow-right"></i></a>
          </div>
        </article>

        <!-- Card 2: Educators -->
        <article class="home-track-card">
          <div class="htrack-media">
            <img src="<?= e(asset('img/card-educators.jpg')) ?>" alt="Teacher guiding a student at a computer" loading="lazy">
            <span class="htrack-tag is-green">FOR EDUCATORS</span>
          </div>
          <div class="htrack-info">
            <h3>AI for Educators</h3>
            <p>We train and certify your own teachers, so AI teaching in schools continues long after our team has left the building. This isn't a one-day workshop &mdash; it's structured L1 and L2 training designed to make your existing staff confident, independent, and genuinely good at running AI classrooms on their own.</p>
            <a href="<?= e(url('programmes')) ?>" class="htrack-link link-green" <?= $set > 0 ? 'tabindex="-1"' : '' ?>>Explore Training <i class="fas fa-arrow-right"></i></a>
          </div>
        </article>

        <!-- Card 3: Professionals -->
        <article class="home-track-card">
          <div class="htrack-media">
            <img src="<?= e(asset('img/card-professionals.jpg')) ?>" alt="Working professional upskilling on a laptop" loading="lazy">
            <span class="htrack-tag is-orange">FOR PROFESSIONALS</span>
          </div>
          <div class="htrack-info">
            <h3>AI for Professionals</h3>
            <p>Applied AI skills for working professionals &mdash; real tools, real workflows, and portfolio projects that actually hold up in an interview. Every course ends in an industry-recognised AI certification, so what you learn on screen translates into something you can point to on a resume.</p>
            <a href="<?= e(url('programmes')) ?>" class="htrack-link link-orange" <?= $set > 0 ? 'tabindex="-1"' : '' ?>>Explore Courses <i class="fas fa-arrow-right"></i></a>
          </div>
        </article>

        <!-- Card 4: Everyone -->
        <article class="home-track-card">
          <div class="htrack-media">
            <img src="<?= e(asset('img/card-everyone.jpg')) ?>" alt="Lifelong learners and everyday citizens mastering AI skills" loading="lazy">
            <span class="htrack-tag is-purple">FOR EVERYONE</span>
          </div>
          <div class="htrack-info">
            <h3>AI for Everyone</h3>
            <p>Practical AI literacy for everyday citizens, homemakers, parents, and curious minds &mdash; demystifying generative AI tools, voice assistants, and smart workflows for daily productivity and creative problem-solving with zero coding or technical background required.</p>
            <a href="<?= e(url('programmes')) ?>" class="htrack-link link-purple" <?= $set > 0 ? 'tabindex="-1"' : '' ?>>Explore Courses <i class="fas fa-arrow-right"></i></a>
          </div>
        </article>

      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- ============ STATS BAR ============ -->
<section class="section-tight" style="padding: 10px 0 50px;">
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

<!-- ============ WHERE SSD PRAYAS WORKS ============ -->
<section class="section section-soft" id="explore">
  <div class="container">
    <div class="home-works-grid">
      
      <!-- Left Intro Box -->
      <div class="works-intro-col reveal">
        <span class="eyebrow is-blue"><i class="fas fa-compass"></i> Explore</span>
        <h2>Where SSD Prayas <span class="text-blue">Works</span></h2>
        <p>We deliver AI education at scale across schools, individuals and government departments in multiple states.</p>
        <a href="<?= e(url('programmes')) ?>" class="btn btn-outline-pill">View All Programmes <i class="fas fa-arrow-right"></i></a>
      </div>

      <!-- Middle Card: AI for School -->
      <article class="works-card works-school reveal">
        <div class="works-card-bg" style="background-image: url('<?= e(asset('img/school-hero.jpg')) ?>');"></div>
        <div class="works-card-overlay is-blue-overlay"></div>
        <div class="works-card-content">
          <span class="eyebrow is-trans-blue">AI FOR SCHOOL PROGRAMME</span>
          <h3>AI for School Programme</h3>
          <p>We train your teachers, deliver the curriculum inside your existing computer lab, and certify both educators and students by the end of the cycle. It's one of the more complete models for AI training in schools available in India today &mdash; no new infrastructure required, no outside dependency once your team is certified.</p>
          <a href="<?= e(url('ai-for-school')) ?>" class="works-card-btn">
            <span class="wc-btn-icon"><i class="fas fa-laptop-code"></i></span>
            <span>Explore it works <i class="fas fa-arrow-right"></i></span>
          </a>
        </div>
      </article>

      <!-- Right Card: Government Projects -->
      <article class="works-card works-govt reveal">
        <div class="works-card-bg" style="background-image: url('<?= e(asset('img/govt-hero.jpg')) ?>');"></div>
        <div class="works-card-overlay is-green-overlay"></div>
        <div class="works-card-content">
          <span class="eyebrow is-trans-green">GOVERNMENT PROJECTS</span>
          <h3>Statewide AI Skilling</h3>
          <p>Multi-district projects built for scale &mdash; from partner onboarding through L1 and L2 educator batches, right down to full certification tracking across every school involved. This is AI education in India as a policy-level rollout, not a pilot programme that quietly ends after one semester.</p>
          <a href="<?= e(url('government')) ?>" class="works-card-btn">
            <span class="wc-btn-icon"><i class="fas fa-landmark"></i></span>
            <span>See our approach <i class="fas fa-arrow-right"></i></span>
          </a>
        </div>
      </article>

    </div>
  </div>
</section>

<!-- ============ HOW AN SSD PRAYAS PROJECT RUNS ============ -->
<section class="section section-home-process">
  <div class="container">
    <div class="section-head is-center reveal">
      <h2>How an SSD Prayas Project <span class="text-blue">Runs</span></h2>
    </div>

    <div class="home-process-card reveal">
      <div class="home-milestones-track">

        <div class="milestone-item">
          <div class="m-badge m-blue">01</div>
          <h4>Partner Onboarding</h4>
          <p>Every school, institute, or government department is onboarded and recorded in our partner database, with district, scale, and scope mapped out from day one.</p>
        </div>

        <div class="milestone-item">
          <div class="m-badge m-green">02</div>
          <h4>Curriculum Mapping</h4>
          <p>The NEP 2020-aligned curriculum is matched to the learner group &mdash; grade-wise for students, level-wise for educators &mdash; so nobody starts a module they're not ready for.</p>
        </div>

        <div class="milestone-item">
          <div class="m-badge m-yellow">03</div>
          <h4>Educator Training (L1/L2)</h4>
          <p>Educators are grouped into L1 and L2 batches and trained on AI tools, teaching methods, and how to actually run an AI classroom day to day.</p>
        </div>

        <div class="milestone-item">
          <div class="m-badge m-purple">04</div>
          <h4>Classroom Delivery</h4>
          <p>Trained faculty deliver hands-on, project-based sessions &mdash; offline in labs, online, or a mix of both, depending on what the school can support.</p>
        </div>

        <div class="milestone-item">
          <div class="m-badge m-cyan">05</div>
          <h4>Assessment &amp; E-Check</h4>
          <p>Every batch goes through assessment and a verification check before any certificate is released &mdash; no rubber-stamping.</p>
        </div>

        <div class="milestone-item">
          <div class="m-badge m-emerald">06</div>
          <h4>Certification</h4>
          <p>Students and educators receive recognised certificates, and the batch is marked work-certified in our records.</p>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- ============ TESTIMONIALS BANNER ============ -->
<section class="section-tight" style="padding-bottom: 35px;">
  <div class="container">
    <div class="home-testimonial-banner reveal">
      <!-- Left: 3D Robot & Student illustration -->
      <div class="tb-visual">
        <img src="<?= e(asset('img/robot-testimonial.png')) ?>" alt="AI Learning Together" class="tb-robot-img">
      </div>

      <!-- Right: 3 Testimonial Cards -->
      <div class="tb-reviews-grid">
        <div class="tb-review-card">
          <div class="tb-stars">★★★★★</div>
          <p class="tb-quote">"This programme opened doors I never thought possible at my age. I now build AI projects and feel confident about my future."</p>
          <div class="tb-author">
            <div class="tb-avatar-circle">RP</div>
            <div class="tb-meta">
              <strong>Rahul Patel</strong>
              <span>Class 10 Student, Madhya Pradesh</span>
            </div>
          </div>
        </div>

        <div class="tb-review-card">
          <div class="tb-stars">★★★★★</div>
          <p class="tb-quote">"The L1 &amp; L2 training was practical, well paced and full of activities. I now integrate AI in my classroom with ease."</p>
          <div class="tb-author">
            <div class="tb-avatar-circle is-green">SK</div>
            <div class="tb-meta">
              <strong>Sunita Kumari</strong>
              <span>Senior Educator, Rajasthan</span>
            </div>
          </div>
        </div>

        <div class="tb-review-card">
          <div class="tb-stars">★★★★★</div>
          <p class="tb-quote">"The AI curriculum and training are industry-relevant and making a real impact in our schools."</p>
          <div class="tb-author">
            <div class="tb-avatar-circle is-purple">AV</div>
            <div class="tb-meta">
              <strong>Amit Verma</strong>
              <span>School Principal, Uttar Pradesh</span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="tb-dots" aria-hidden="true">
        <span class="tb-dot is-active"></span>
        <span class="tb-dot"></span>
        <span class="tb-dot"></span>
      </div>
    </div>
  </div>
</section>

<!-- ============ CTA BANNER ============ -->
<section class="section-tight" style="padding-bottom: 60px;">
  <div class="container">
    <div class="home-cta-box reveal">
      <div class="home-cta-content">
        <h2>Ready to bring AI to your students?</h2>
        <p>Whether you're running a single school, a district-wide programme, or a state-level project — we're here to help you every step of the way.</p>
      </div>
      <div class="home-cta-actions">
        <a href="<?= e(url('contact')) ?>" class="btn btn-yellow-pill">Start Your AI Journey <i class="fas fa-arrow-right"></i></a>
        <a href="tel:+<?= e(CONTACT_PHONE_RAW) ?>" class="btn btn-ghost-pill"><i class="fas fa-phone"></i> Call Now</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ LATEST INSIGHTS / BLOGS ============ -->
<?php if ($posts): ?>
<section class="section section-soft" id="insights" style="padding-bottom: 90px;">
  <div class="container">
    <div class="home-insights-header reveal">
      <span class="eyebrow is-blue"><i class="fas fa-newspaper"></i> Latest Insights</span>
      <a href="<?= e(url('blogs')) ?>" class="btn btn-outline-pill">View All Articles <i class="fas fa-arrow-right"></i></a>
    </div>

    <div class="grid grid-3 home-blog-grid">
      <?php foreach ($posts as $post): ?>
        <article class="home-blog-card reveal">
          <a href="<?= e(url('blog/' . $post['slug'])) ?>" class="hbc-thumb">
            <img src="<?= e(url_v($post['image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
          </a>
          <div class="hbc-body">
            <span class="hbc-tag"><?= e($post['tag']) ?></span>
            <h3><a href="<?= e(url('blog/' . $post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
            <div class="hbc-footer">
              <span class="hbc-date"><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
              <a href="<?= e(url('blog/' . $post['slug'])) ?>" class="hbc-link">Read <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
