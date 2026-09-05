<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'ai-for-school';
$page_title = 'AI for School Programme | ' . SITE_NAME;
$page_desc  = 'Our flagship school engagement: SSD Prayas trains your educators, delivers a NEP 2020-aligned AI curriculum in your existing computer lab, and certifies both teachers and students.';

$stats = impact_stats($pdo);

include __DIR__ . '/includes/header.php';
?>

<main class="page-school">

<!-- ============ HERO SECTION ============ -->
<section class="school-hero">
  <div class="container">
    <div class="school-hero-content">
      <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; AI for School Programme</p>
      <span class="eyebrow is-green"><i class="fas fa-school"></i> Flagship Programme</span>
      <h1>The <span class="text-blue">AI for</span><br><span class="text-green">School</span> Programme</h1>
      <p class="hero-lead">We do not drop a syllabus and leave. We partner with your school, train your own
         teachers, run the classes inside your existing computer lab, and certify both the
         educators and the students — a self-sustaining AI ecosystem on your campus.</p>
      <div class="hero-actions">
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

    <div class="grid grid-3 school-features-grid">
      <article class="school-card reveal">
        <div class="s-card-icon i-blue"><i class="fas fa-book-open"></i></div>
        <div class="s-card-body">
          <h3>Grade-wise Curriculum</h3>
          <p>A complete Class 3 to 12 AI syllabus, NEP 2020-aligned and mapped to what each age group can actually absorb.</p>
        </div>
      </article>

      <article class="school-card reveal">
        <div class="s-card-icon i-green"><i class="fas fa-chalkboard-user"></i></div>
        <div class="s-card-body">
          <h3>Educator Training</h3>
          <p>Your teachers go through L1 and L2 training on campus, with lesson plans and classroom-ready material.</p>
        </div>
      </article>

      <article class="school-card reveal">
        <div class="s-card-icon i-yellow"><i class="fas fa-flask"></i></div>
        <div class="s-card-body">
          <h3>Hands-on Lab Sessions</h3>
          <p>Trained faculty deliver practical, project-based classes in your existing computer lab — no new hardware needed.</p>
        </div>
      </article>

      <article class="school-card reveal">
        <div class="s-card-icon i-red"><i class="fas fa-chess-knight"></i></div>
        <div class="s-card-body">
          <h3>Projects &amp; Exhibitions</h3>
          <p>Students build real AI projects and present them at school exhibitions, building portfolios and confidence.</p>
        </div>
      </article>

      <article class="school-card reveal">
        <div class="s-card-icon i-blue"><i class="fas fa-clipboard-check"></i></div>
        <div class="s-card-body">
          <h3>Assessment &amp; E-Check</h3>
          <p>Every batch is assessed and verified through our e-check process before any certificate is released.</p>
        </div>
      </article>

      <article class="school-card reveal">
        <div class="s-card-icon i-green"><i class="fas fa-award"></i></div>
        <div class="s-card-body">
          <h3>Certification</h3>
          <p>Students and teachers receive recognised certificates, and the batch is recorded as work-certified in our system.</p>
        </div>
      </article>
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
        <p class="process-sub">From first conversation to certificates in students' hands, a typical school engagement moves through five stages.</p>

        <div class="school-timeline">
          <div class="st-item">
            <div class="st-badge">01</div>
            <div class="st-content">
              <h3>School onboarding</h3>
              <p>We visit, understand your infrastructure and classes, and agree the scope and calendar.</p>
            </div>
          </div>

          <div class="st-item">
            <div class="st-badge">02</div>
            <div class="st-content">
              <h3>Curriculum mapping</h3>
              <p>The grade-wise syllabus is mapped to your timetable and board requirements.</p>
            </div>
          </div>

          <div class="st-item">
            <div class="st-badge">03</div>
            <div class="st-content">
              <h3>Educator training</h3>
              <p>Your teachers are grouped into L1 and L2 batches and trained on AI tools, pedagogy and classroom delivery.</p>
            </div>
          </div>

          <div class="st-item">
            <div class="st-badge">04</div>
            <div class="st-content">
              <h3>Classroom delivery</h3>
              <p>Sessions run in your computer lab through the term — hands-on, project-based, with our faculty alongside you.</p>
            </div>
          </div>

          <div class="st-item">
            <div class="st-badge">05</div>
            <div class="st-content">
              <h3>Assessment &amp; certification</h3>
              <p>Students are assessed, the batch clears e-check, and certificates are issued to students and trained educators.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: Requirements Cards -->
      <div class="school-process-right reveal">
        <div class="school-req-box">
          <h3><i class="fas fa-circle-check" style="color:var(--green)"></i> What we need from the school</h3>
          <ul class="school-req-list">
            <li><i class="fas fa-circle-check"></i> An existing computer lab with working machines</li>
            <li><i class="fas fa-circle-check"></i> Internet connectivity (basic is enough)</li>
            <li><i class="fas fa-circle-check"></i> Timetable slots for the AI period</li>
            <li><i class="fas fa-circle-check"></i> Two or more teachers willing to be trained</li>
            <li><i class="fas fa-circle-check"></i> A coordinator we can work with</li>
          </ul>
        </div>

        <div class="school-req-box is-cross-box">
          <h3 class="is-red"><i class="fas fa-circle-xmark" style="color:var(--red)"></i> What you don't need</h3>
          <ul class="school-req-list is-cross">
            <li><i class="fas fa-circle-xmark"></i> New hardware or expensive AI kits</li>
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
          <p>No. The programme is designed to run on the computers your school already has. Basic internet is enough; several activities also work offline.</p>
        </div>
      </details>

      <details class="school-faq-item">
        <summary>
          <span>Which classes can join?</span>
          <i class="fas fa-plus"></i>
        </summary>
        <div class="school-faq-body">
          <p>Class 3 to 12. Schools usually start with a few grades and expand once the first batch completes. The curriculum is graded, so each class gets age-appropriate content.</p>
        </div>
      </details>

      <details class="school-faq-item">
        <summary>
          <span>What happens to our teachers?</span>
          <i class="fas fa-plus"></i>
        </summary>
        <div class="school-faq-body">
          <p>They are trained and certified through our L1 and L2 batches. The goal is that your school can keep teaching AI independently after the first year.</p>
        </div>
      </details>

      <details class="school-faq-item">
        <summary>
          <span>Is the programme offline or online?</span>
          <i class="fas fa-plus"></i>
        </summary>
        <div class="school-faq-body">
          <p>Primarily offline, on your campus. Online and hybrid delivery are available where travel is difficult or for remote districts.</p>
        </div>
      </details>

      <details class="school-faq-item">
        <summary>
          <span>What certification do students receive?</span>
          <i class="fas fa-plus"></i>
        </summary>
        <div class="school-faq-body">
          <p>Students who clear the assessment receive a completion certificate for their class level. Batches are verified through our e-check process before certificates are issued.</p>
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
        <img src="<?= e(asset('img/cta-school.png')) ?>" alt="School Campus" class="cta-school-img">
      </div>
      <div class="school-cta-content">
        <h2>Bring AI to your campus</h2>
        <p>Tell us about your school and we will put together a roll-out plan and a demo session for your management.</p>
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
