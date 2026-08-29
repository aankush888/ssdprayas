<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'ai-for-school';
$page_title = 'AI for School Programme | ' . SITE_NAME;
$page_desc  = 'Our flagship school engagement: SSD Prayas trains your educators, delivers a NEP 2020-aligned AI curriculum in your existing computer lab, and certifies both teachers and students.';

$stats = impact_stats($pdo);

include __DIR__ . '/includes/header.php';
?>

<main>

<section class="page-hero has-photo bg-school">
  <div class="container">
    <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; AI for School Programme</p>
    <span class="eyebrow is-green"><i class="fas fa-school"></i> Flagship Programme</span>
    <h1>The <span class="grad-text">AI for School</span> Programme</h1>
    <p>We do not drop a syllabus and leave. We partner with your school, train your own
       teachers, run the classes inside your existing computer lab, and certify both the
       educators and the students — a self-sustaining AI ecosystem on your campus.</p>
    <div class="hero-actions">
      <a href="<?= e(url('contact')) ?>" class="btn btn-primary">Partner Your School <i class="fas fa-arrow-right"></i></a>
      <a href="<?= e(whatsapp_link('Hello SSD Prayas, I want to know about the AI for School programme.')) ?>"
         class="btn btn-ghost" target="_blank" rel="noopener">
        <i class="fab fa-whatsapp" style="color:#25D366"></i> Book a Demo
      </a>
    </div>
  </div>
</section>

<!-- What the school gets -->
<section class="section">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow"><i class="fas fa-box-open"></i> What's Included</span>
      <h2>What Your School <span class="text-brand">Gets</span></h2>
      <p>One programme, six moving parts — all handled by us.</p>
    </div>

    <div class="grid grid-3">
      <article class="card reveal">
        <div class="card-icon i-blue"><i class="fas fa-book-open"></i></div>
        <h3>Grade-wise Curriculum</h3>
        <p>A complete Class 3 to 12 AI syllabus, NEP 2020-aligned and mapped to what each
           age group can actually absorb.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-green"><i class="fas fa-chalkboard-user"></i></div>
        <h3>Educator Training</h3>
        <p>Your teachers go through L1 and L2 training on campus, with lesson plans and
           classroom-ready material.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-yellow"><i class="fas fa-laptop-code"></i></div>
        <h3>Hands-on Lab Sessions</h3>
        <p>Trained faculty deliver practical, project-based classes in your existing
           computer lab — no new hardware needed.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-red"><i class="fas fa-lightbulb"></i></div>
        <h3>Projects &amp; Exhibitions</h3>
        <p>Students build real AI projects and present them at school exhibitions, building
           portfolios and confidence.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-blue"><i class="fas fa-clipboard-check"></i></div>
        <h3>Assessment &amp; E-Check</h3>
        <p>Every batch is assessed and verified through our e-check process before any
           certificate is released.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-green"><i class="fas fa-certificate"></i></div>
        <h3>Certification</h3>
        <p>Students and teachers receive recognised certificates, and the batch is recorded
           as work-certified in our system.</p>
      </article>
    </div>
  </div>
</section>

<!-- How it runs -->
<section class="section section-soft">
  <div class="container split" style="align-items:start">
    <div class="reveal">
      <span class="eyebrow is-red"><i class="fas fa-timeline"></i> On Campus</span>
      <h2>How the Programme <span class="text-brand">Runs</span></h2>
      <p style="margin:18px 0 26px">From first conversation to certificates in students' hands,
         a typical school engagement moves through five stages.</p>

      <div class="timeline">
        <div class="timeline-item">
          <h3>1. School onboarding</h3>
          <p>We visit, understand your infrastructure and classes, and agree the scope and
             calendar. The school is recorded in our partner database.</p>
        </div>
        <div class="timeline-item">
          <h3>2. Curriculum mapping</h3>
          <p>The grade-wise syllabus is mapped to your timetable and board requirements.</p>
        </div>
        <div class="timeline-item">
          <h3>3. Educator training</h3>
          <p>Your teachers are grouped into L1 and L2 batches and trained on AI tools,
             pedagogy and classroom delivery.</p>
        </div>
        <div class="timeline-item">
          <h3>4. Classroom delivery</h3>
          <p>Sessions run in your computer lab through the term — hands-on, project-based,
             with our faculty alongside yours.</p>
        </div>
        <div class="timeline-item">
          <h3>5. Assessment &amp; certification</h3>
          <p>Students are assessed, the batch clears e-check, and certificates are issued
             to students and trained educators.</p>
        </div>
      </div>
    </div>

    <div class="reveal">
      <div class="req-panel" style="margin-bottom:22px">
        <h3><i class="fas fa-circle-check" style="color:var(--green);margin-right:8px"></i>What we need from the school</h3>
        <ul class="tick-list">
          <li>An existing computer lab with working machines</li>
          <li>Internet connectivity (basic is enough)</li>
          <li>Timetable slots for the AI period</li>
          <li>Two or more teachers willing to be trained</li>
          <li>A coordinator we can work with</li>
        </ul>
      </div>

      <div class="req-panel">
        <h3><i class="fas fa-xmark" style="color:var(--red);margin-right:8px"></i>What you don't need</h3>
        <ul class="cross-list">
          <li>New hardware or expensive AI kits</li>
          <li>Existing computer-science expertise on staff</li>
          <li>A separate lab or dedicated room</li>
          <li>Paid software licences</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- Proof -->
<section class="section">
  <div class="container">
    <div class="stat-band reveal">
      <div>
        <strong data-count="<?= (int)display_stat($stats['partners'], 250) ?>" data-suffix="+">0</strong>
        <span>Partner Schools</span>
      </div>
      <div>
        <strong data-count="<?= (int)display_stat($stats['educators'], 800) ?>" data-suffix="+">0</strong>
        <span>Educators Trained</span>
      </div>
      <div>
        <strong data-count="<?= (int)display_stat($stats['students'], 25000) ?>" data-suffix="+">0</strong>
        <span>Students Reached</span>
      </div>
      <div>
        <strong data-count="<?= (int)display_stat($stats['states'], 6) ?>" data-suffix="+">0</strong>
        <span>States Covered</span>
      </div>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="section section-soft">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow"><i class="fas fa-circle-question"></i> Questions</span>
      <h2>Common <span class="text-brand">Questions</span></h2>
    </div>

    <div class="faq reveal">
      <details>
        <summary>Do we need to buy any new equipment?</summary>
        <p>No. The programme is designed to run on the computers your school already has.
           Basic internet is enough; several activities also work offline.</p>
      </details>
      <details>
        <summary>Which classes can join?</summary>
        <p>Class 3 to 12. Schools usually start with a few grades and expand once the first
           batch completes. The curriculum is graded, so each class gets age-appropriate content.</p>
      </details>
      <details>
        <summary>What happens to our teachers?</summary>
        <p>They are trained and certified through our L1 and L2 batches. The goal is that your
           school can keep teaching AI independently after the first year.</p>
      </details>
      <details>
        <summary>Is the programme offline or online?</summary>
        <p>Primarily offline, on your campus. Online and hybrid delivery are available where
           travel is difficult or for remote districts.</p>
      </details>
      <details>
        <summary>What certification do students receive?</summary>
        <p>Students who clear the assessment receive a completion certificate for their class
           level. Batches are verified through our e-check process before certificates are issued.</p>
      </details>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section-tight" style="padding-bottom:90px">
  <div class="container">
    <div class="cta-band reveal">
      <div>
        <h2>Bring AI to your campus</h2>
        <p>Tell us about your school and we will put together a roll-out plan and a demo
           session for your management.</p>
      </div>
      <div class="cta-actions">
        <a href="<?= e(url('contact')) ?>" class="btn btn-yellow">Partner Your School <i class="fas fa-arrow-right"></i></a>
        <a href="tel:+<?= e(CONTACT_PHONE_RAW) ?>" class="btn btn-light"><i class="fas fa-phone"></i> Call Now</a>
      </div>
    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
