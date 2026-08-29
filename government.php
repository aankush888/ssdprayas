<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'government';
$page_title = 'Government Projects | ' . SITE_NAME;
$page_desc  = 'SSD Prayas executes large-scale government AI skilling projects across multiple Indian states, with partner onboarding, L1/L2 educator batches, student enrolment and certification tracked in one monitored system.';

$stats  = impact_stats($pdo);
$states = rows($pdo, "SELECT id, name FROM states WHERE is_active = 1 ORDER BY sort_order, name");

include __DIR__ . '/includes/header.php';
?>

<main>

<section class="page-hero has-photo bg-govt">
  <div class="container">
    <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; Government Projects</p>
    <span class="eyebrow is-red"><i class="fas fa-landmark"></i> Government Projects</span>
    <h1>Large-Scale AI Skilling, <br><span class="grad-text">Across Multiple States</span></h1>
    <p>SSD Prayas delivers government and institutional AI skilling at state scale — managing
       partner onboarding, educator training batches, student enrolment and certification
       tracking through a single monitored system.</p>
    <div class="hero-actions">
      <a href="<?= e(url('contact')) ?>" class="btn btn-primary">Discuss a Project <i class="fas fa-arrow-right"></i></a>
      <a href="tel:+<?= e(CONTACT_PHONE_RAW) ?>" class="btn btn-ghost"><i class="fas fa-phone"></i> <?= e(CONTACT_PHONE) ?></a>
    </div>
  </div>
</section>

<!-- Why us for scale -->
<section class="section">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow"><i class="fas fa-layer-group"></i> Built for Scale</span>
      <h2>Why Departments <span class="text-brand">Work With Us</span></h2>
      <p>A district pilot and a statewide roll-out run on the same system — nothing is
         rebuilt when the numbers grow.</p>
    </div>

    <div class="grid grid-4">
      <article class="card reveal">
        <div class="card-icon i-blue"><i class="fas fa-database"></i></div>
        <h3>Everything Tracked</h3>
        <p>Partners, educators, students and batches are recorded state-wise, so progress is
           auditable at any point.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-green"><i class="fas fa-users-gear"></i></div>
        <h3>Train the Trainer</h3>
        <p>We certify government school teachers through L1 and L2 batches, so capacity stays
           inside the system.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-yellow"><i class="fas fa-shield-halved"></i></div>
        <h3>E-Check Verification</h3>
        <p>No batch is certified until it clears our e-check verification — a documented
           quality gate for every cohort.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-red"><i class="fas fa-building-columns"></i></div>
        <h3>Existing Infrastructure</h3>
        <p>Programmes run in the school's existing computer labs. No procurement cycle, no
           new hardware budget.</p>
      </article>
    </div>
  </div>
</section>

<!-- States + metrics -->
<section class="section section-soft">
  <div class="container">
    <div class="gov-panel reveal">
      <div class="gov-split">
        <div>
          <span class="eyebrow"><i class="fas fa-map-location-dot"></i> Our Reach</span>
          <h2>States &amp; Union Territories <br>We Work In</h2>
          <p style="margin:18px 0 8px">Each state's partners, educators, students and batches
             are maintained separately, so every outcome stays traceable to its district.</p>
          <div class="state-chips">
            <?php foreach ($states as $st): ?>
              <span class="state-chip"><i class="fas fa-location-dot"></i> <?= e($st['name']) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="gov-metrics">
          <div class="gov-metric">
            <strong><?= number_format(display_stat($stats['states'], 6)) ?>+</strong>
            <span>States &amp; UTs engaged</span>
          </div>
          <div class="gov-metric">
            <strong><?= number_format(display_stat($stats['partners'], 250)) ?>+</strong>
            <span>Institutions onboarded</span>
          </div>
          <div class="gov-metric">
            <strong>L1 · L2</strong>
            <span>Structured training levels</span>
          </div>
          <div class="gov-metric">
            <strong>100%</strong>
            <span>Batch-wise tracking</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Delivery model -->
<section class="section">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow is-green"><i class="fas fa-diagram-project"></i> Delivery Model</span>
      <h2>How a State Project <span class="text-brand">Runs</span></h2>
    </div>

    <div class="steps">
      <article class="step reveal">
        <span class="step-no">01</span>
        <h3>Scoping &amp; MoU</h3>
        <p>Districts, schools and target numbers are agreed with the department and recorded
           against the project.</p>
      </article>
      <article class="step reveal">
        <span class="step-no">02</span>
        <h3>Partner Onboarding</h3>
        <p>Each participating school or institute is onboarded into the partner database with
           district and scale.</p>
      </article>
      <article class="step reveal">
        <span class="step-no">03</span>
        <h3>Educator Batches</h3>
        <p>Government teachers are grouped into L1 and L2 batches and trained in a phased
           district-by-district calendar.</p>
      </article>
      <article class="step reveal">
        <span class="step-no">04</span>
        <h3>Student Roll-out</h3>
        <p>Trained educators, supported by our faculty, deliver the graded AI curriculum in
           their own classrooms.</p>
      </article>
      <article class="step reveal">
        <span class="step-no">05</span>
        <h3>E-Check &amp; Audit</h3>
        <p>Each batch is verified, with attendance, assessment and outcome records available
           to the department.</p>
      </article>
      <article class="step reveal">
        <span class="step-no">06</span>
        <h3>Certification &amp; Report</h3>
        <p>Certificates are issued and a consolidated report is shared covering coverage,
           completion and certification.</p>
      </article>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section-tight" style="padding-bottom:90px">
  <div class="container">
    <div class="cta-band reveal">
      <div>
        <h2>Planning a district or state programme?</h2>
        <p>Share the scope — districts, schools and target numbers — and we will come back
           with a phased delivery plan and timeline.</p>
      </div>
      <div class="cta-actions">
        <a href="<?= e(url('contact')) ?>" class="btn btn-yellow">Discuss a Project <i class="fas fa-arrow-right"></i></a>
        <a href="mailto:<?= e(SITE_EMAIL) ?>" class="btn btn-light"><i class="fas fa-envelope"></i> Email Us</a>
      </div>
    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
