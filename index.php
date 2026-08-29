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

<main>

<!-- ============ HERO ============ -->
<section class="hero" id="home">
  <div class="container">

    <div class="hero-head">
      <span class="hero-badge">
        <i class="fas fa-award"></i> Google for Education Partner
      </span>

      <h1>AI Skilling for a <span class="grad-text">Future-Ready</span> Bharat</h1>

      <p class="hero-lead">
        Practical, hands-on AI education for school students, their teachers and working
        professionals — NEP 2020-aligned, delivered online and offline across India.
      </p>

      <div class="hero-actions">
        <a href="<?= e(url('contact')) ?>" class="btn btn-primary">Start Your AI Journey <i class="fas fa-arrow-right"></i></a>
        <a href="<?= e(whatsapp_link('Hello SSD Prayas, I would like to book a demo.')) ?>"
           class="btn btn-ghost" target="_blank" rel="noopener">
          <i class="fab fa-whatsapp" style="color:#25D366"></i> Book a Demo
        </a>
      </div>
    </div>

    <div class="hero-tracks">

      <article class="htrack">
        <div class="htrack-img">
          <img src="<?= e(asset('img/card-students.jpg')) ?>" alt="Students in a school computer lab learning with AI" fetchpriority="high">
          <span class="htrack-chip"><i class="fas fa-graduation-cap"></i> Class 3–12</span>
        </div>
        <div class="htrack-body">
          <h3>AI for Students</h3>
          <p>A grade-wise curriculum that grows with the child — from digital literacy to
             AI projects and career pathways.</p>
          <a href="<?= e(url('programmes')) ?>" class="link-arrow">See the programme <span>→</span></a>
        </div>
      </article>

      <article class="htrack t-green">
        <div class="htrack-img">
          <img src="<?= e(asset('img/card-educators.jpg')) ?>" alt="Teacher guiding a student at a computer" loading="lazy">
          <span class="htrack-chip"><i class="fas fa-chalkboard-user"></i> L1 &amp; L2</span>
        </div>
        <div class="htrack-body">
          <h3>AI for Educators</h3>
          <p>We train and certify your own teachers, so AI teaching continues long after
             our team leaves the campus.</p>
          <a href="<?= e(url('programmes')) ?>" class="link-arrow">See the training <span>→</span></a>
        </div>
      </article>

      <article class="htrack t-yellow">
        <div class="htrack-img">
          <img src="<?= e(asset('img/card-professionals.jpg')) ?>" alt="Working professional upskilling on a laptop" loading="lazy">
          <span class="htrack-chip"><i class="fas fa-briefcase"></i> Upskilling</span>
        </div>
        <div class="htrack-body">
          <h3>AI for Professionals</h3>
          <p>Applied AI skills for working professionals — real tools, real workflows,
             portfolio projects that hold up.</p>
          <a href="<?= e(url('programmes')) ?>" class="link-arrow">See the courses <span>→</span></a>
        </div>
      </article>

    </div>

    <div class="hero-facts">
      <div>
        <strong><?= number_format($show_partners) ?>+</strong>
        <span>Partner institutions</span>
      </div>
      <div>
        <strong><?= number_format($show_states) ?>+</strong>
        <span>States covered</span>
      </div>
      <div>
        <strong><?= number_format($show_educators) ?>+</strong>
        <span>Educators trained</span>
      </div>
      <img src="<?= e(asset('google-partner-badge-horizontal.png')) ?>"
           alt="Google for Education Professional Development Partner">
    </div>

  </div>
</section>

<!-- ============ MARQUEE STRIP ============ -->
<div class="strip" aria-hidden="true">
  <div class="strip-track">
    <?php for ($i = 0; $i < 2; $i++): ?>
      <span><i class="fas fa-circle"></i> NEP 2020 Aligned Curriculum</span>
      <span><i class="fas fa-circle"></i> Google for Education Partner</span>
      <span><i class="fas fa-circle"></i> Government Large-Scale Projects</span>
      <span><i class="fas fa-circle"></i> Class 3 to 12 Students</span>
      <span><i class="fas fa-circle"></i> L1 &amp; L2 Educator Training</span>
      <span><i class="fas fa-circle"></i> Online &amp; Offline Delivery</span>
      <span><i class="fas fa-circle"></i> Work-Certified Batches</span>
    <?php endfor; ?>
  </div>
</div>

<!-- ============ STAT BAND ============ -->
<section class="section-tight">
  <div class="container">
    <div class="stat-band reveal">
      <div>
        <strong data-count="<?= (int)$show_partners ?>" data-suffix="+">0</strong>
        <span>Partner Institutions</span>
      </div>
      <div>
        <strong data-count="<?= (int)$show_educators ?>" data-suffix="+">0</strong>
        <span>Educators Trained</span>
      </div>
      <div>
        <strong data-count="<?= (int)$show_students ?>" data-suffix="+">0</strong>
        <span>Students Impacted</span>
      </div>
      <div>
        <strong data-count="<?= (int)$show_states ?>" data-suffix="+">0</strong>
        <span>States Covered</span>
      </div>
    </div>
  </div>
</section>

<!-- ============ EXPLORE — links to the full pages ============ -->
<section class="section section-soft" id="explore">
  <div class="container">

    <div class="section-head is-center reveal">
      <span class="eyebrow"><i class="fas fa-compass"></i> Explore</span>
      <h2>Where SSD Prayas <span class="text-brand">Works</span></h2>
      <p>Two ways we deliver AI education at scale — inside individual schools, and across
         entire states with government departments.</p>
    </div>

    <div class="teaser">
      <a class="teaser-card is-school reveal" href="<?= e(url('ai-for-school')) ?>">
        <span class="eyebrow"><i class="fas fa-school"></i> Flagship Programme</span>
        <h3>AI for School Programme</h3>
        <p>We train your teachers, deliver the curriculum in your existing computer lab,
           and certify both educators and students.</p>
        <span class="link-arrow">See how it works <span>→</span></span>
      </a>

      <a class="teaser-card is-govt reveal" href="<?= e(url('government')) ?>">
        <span class="eyebrow"><i class="fas fa-landmark"></i> Government Projects</span>
        <h3>Statewide AI Skilling</h3>
        <p>Multi-district projects with partner onboarding, L1/L2 educator batches and
           full certification tracking.</p>
        <span class="link-arrow">See our approach <span>→</span></span>
      </a>
    </div>

    <div style="text-align:center;margin-top:38px">
      <a href="<?= e(url('programmes')) ?>" class="btn btn-ghost">
        View All Programmes <i class="fas fa-arrow-right"></i>
      </a>
    </div>

  </div>
</section>

<!-- ============ HOW WE WORK ============ -->
<section class="section section-soft" id="process">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow is-red"><i class="fas fa-diagram-project"></i> Our Process</span>
      <h2>How an SSD Prayas <span class="text-brand">Project Runs</span></h2>
      <p>A repeatable six-stage system — the same whether it is one school or an entire state.</p>
    </div>

    <div class="steps">
      <article class="step reveal">
        <span class="step-no">01</span>
        <h3>Partner Onboarding</h3>
        <p>School, institute or government department is onboarded and recorded in our partner
           database with district, scale and scope.</p>
      </article>
      <article class="step reveal">
        <span class="step-no">02</span>
        <h3>Curriculum Mapping</h3>
        <p>NEP 2020-aligned curriculum is mapped to the learner group — grade-wise for students,
           level-wise for educators.</p>
      </article>
      <article class="step reveal">
        <span class="step-no">03</span>
        <h3>Educator Training (L1/L2)</h3>
        <p>Educators are grouped into L1 and L2 batches and trained on AI tools, pedagogy and
           classroom delivery.</p>
      </article>
      <article class="step reveal">
        <span class="step-no">04</span>
        <h3>Classroom Delivery</h3>
        <p>Trained faculty deliver hands-on, project-based sessions — offline in labs, online,
           or hybrid.</p>
      </article>
      <article class="step reveal">
        <span class="step-no">05</span>
        <h3>Assessment &amp; E-Check</h3>
        <p>Every batch goes through assessment and an e-check verification before certification
           is released.</p>
      </article>
      <article class="step reveal">
        <span class="step-no">06</span>
        <h3>Certification</h3>
        <p>Students and educators receive recognised certificates, and the batch is marked
           work-certified in our records.</p>
      </article>
    </div>
  </div>
</section>

<!-- ============ TESTIMONIAL + CTA ============ -->
<section class="section">
  <div class="container">
    <div class="grid grid-2" style="align-items:stretch">

      <div class="quote-card reveal">
        <div class="quote-stars">★★★★★</div>
        <blockquote>
          "This programme taught me to <em>create</em> AI tools, not just use them. The portfolio I
          built got me recognition at school, and the certification opened doors I never thought
          possible at my age."
        </blockquote>
        <div class="quote-author">
          <span class="quote-avatar">RP</span>
          <div>
            <b>Rahul Patel</b>
            <span>Class 10 Student, Madhya Pradesh</span>
          </div>
        </div>
      </div>

      <div class="quote-card reveal">
        <div class="quote-stars">★★★★★</div>
        <blockquote>
          "As a teacher I was nervous about AI. The L1 and L2 training was practical and paced for
          us — now I run AI activities in my own classroom without any outside help."
        </blockquote>
        <div class="quote-author">
          <span class="quote-avatar" style="background:var(--green-soft);color:#1c7a3c">SK</span>
          <div>
            <b>Sunita Kumari</b>
            <span>Senior Educator, Jharkhand</span>
          </div>
        </div>
      </div>

    </div>

    <div class="cta-band reveal" style="margin-top:34px">
      <div>
        <h2>Ready to bring AI to your students?</h2>
        <p>Whether you run a single school, a district programme or a state-level project —
           our team will design the right roll-out with you.</p>
      </div>
      <div class="cta-actions">
        <a href="<?= e(url('contact')) ?>" class="btn btn-yellow">Start Your AI Journey <i class="fas fa-arrow-right"></i></a>
        <a href="tel:+<?= e(CONTACT_PHONE_RAW) ?>" class="btn btn-light"><i class="fas fa-phone"></i> Call Now</a>
      </div>
    </div>
  </div>
</section>

<!-- ============ BLOGS ============ -->
<?php if ($posts): ?>
<section class="section section-soft" id="insights">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow"><i class="fas fa-newspaper"></i> Latest Insights</span>
      <h2>From the <span class="text-brand">SSD Prayas Blog</span></h2>
    </div>

    <div class="grid grid-3">
      <?php foreach ($posts as $post): ?>
        <article class="post-card reveal">
          <a href="<?= e(url('blog/' . $post['slug'])) ?>" class="post-thumb">
            <img src="<?= e(url($post['image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
          </a>
          <div class="post-body">
            <span class="post-tag"><?= e($post['tag']) ?></span>
            <h3><a href="<?= e(url('blog/' . $post['slug'])) ?>"><?= e($post['title']) ?></a></h3>
            <p><?= e(mb_strimwidth($post['excerpt'], 0, 120, '…')) ?></p>
            <div class="post-meta">
              <span><?= date('M d, Y', strtotime($post['created_at'])) ?></span>
              <a href="<?= e(url('blog/' . $post['slug'])) ?>" class="link-arrow">Read <span>→</span></a>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <div style="text-align:center;margin-top:40px">
      <a href="<?= e(url('blogs')) ?>" class="btn btn-ghost">View All Articles <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
</section>
<?php endif; ?>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
