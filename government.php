<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'government';
$page_title = 'Government Projects | ' . SITE_NAME;
$page_desc  = 'SSD Prayas delivers government and institutional AI skilling at state scale — managing partner onboarding, educator training batches, student enrolment and certification tracking through a single monitored system.';

$stats  = impact_stats($pdo);
$states = rows($pdo, "SELECT id, name FROM states WHERE is_active = 1 ORDER BY sort_order, name");

include __DIR__ . '/includes/header.php';
?>

<main class="page-govt">

<!-- Hero Section: Full bleed edge-to-edge background with high-res photo -->
<section class="govt-hero">
  <div class="container govt-hero-container">
    <div class="govt-hero-content">
      <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; Government Projects</p>
      
      <div class="govt-pill-badge is-red">
        <i class="fas fa-landmark"></i> GOVERNMENT PROJECTS
      </div>
      
      <h1 class="govt-hero-title">
        Large-Scale <br>
        AI Skilling, <br>
        <span class="govt-title-highlight"><span class="text-blue">Across Multiple</span> <span class="text-green">States</span></span>
      </h1>
      
      <p class="govt-hero-desc">
        SSD Prayas delivers government and institutional AI skilling at state scale —<br class="hero-desc-br">
        managing partner onboarding, educator training batches, student enrolment<br class="hero-desc-br">
        and certification tracking through a single monitored system.
      </p>
      
      <div class="govt-hero-actions">
        <a href="<?= e(url('contact')) ?>" class="btn-blue-pill">Discuss a Project <i class="fas fa-arrow-right"></i></a>
        <a href="tel:+<?= e(CONTACT_PHONE_RAW) ?>" class="btn-white-phone-pill"><i class="fas fa-phone"></i> <?= e(CONTACT_PHONE) ?></a>
      </div>
      
      <div class="govt-trust-row">
        <div class="govt-trust-item">
          <span class="gti-icon"><i class="fas fa-server"></i></span>
          <span>Scalable &amp; Secure</span>
        </div>
        <div class="govt-trust-item">
          <span class="gti-icon"><i class="fas fa-chart-line"></i></span>
          <span>Measurable Impact</span>
        </div>
        <div class="govt-trust-item">
          <span class="gti-icon"><i class="fas fa-shield-halved"></i></span>
          <span>Government Aligned</span>
        </div>
      </div>
    </div>

    <!-- Floating glass badge over hero photo -->
    <aside class="govt-bharat-badge" aria-label="Skilling Stronger States Brighter Bharat">
      <div class="gbb-line gbb-dim">Skilling</div>
      <div class="gbb-line gbb-dim">Stronger</div>
      <div class="gbb-line gbb-bold">
        <span>States</span>
        <svg class="in-flag-svg" viewBox="0 0 24 16" width="19" height="13" aria-label="India Flag" role="img">
          <rect width="24" height="5.33" fill="#FF9933"/>
          <rect y="5.33" width="24" height="5.34" fill="#FFFFFF"/>
          <rect y="10.67" width="24" height="5.33" fill="#138808"/>
          <circle cx="12" cy="8" r="2" fill="none" stroke="#000080" stroke-width="0.5"/>
          <circle cx="12" cy="8" r="0.4" fill="#000080"/>
        </svg>
      </div>
      <div class="gbb-line gbb-dim">Brighter</div>
      <div class="gbb-line gbb-bold">Bharat</div>
      <div class="gbb-stripes">
        <span class="stripe-saffron"></span>
        <span class="stripe-green"></span>
      </div>
    </aside>
  </div>
</section>

<!-- Section 2: Why Departments Work With Us -->
<section class="section-govt-why">
  <div class="container">
    <div class="section-head is-center reveal">
      <div class="pill-badge pill-blue"><i class="fas fa-layer-group"></i> BUILT FOR SCALE</div>
      <h2>Why Departments <span class="text-blue">Work With Us</span></h2>
      <p class="sec-subtitle">A district pilot and a statewide roll-out run on the same system — nothing is rebuilt when the numbers grow.</p>
    </div>

    <div class="govt-why-grid">
      <article class="govt-why-card reveal">
        <div class="gwc-icon gwc-blue"><i class="fas fa-chart-simple"></i></div>
        <h3>Everything Tracked</h3>
        <p>Partners, educators, students and batches are recorded state-wise, so progress is auditable at any point.</p>
      </article>

      <article class="govt-why-card reveal">
        <div class="gwc-icon gwc-green"><i class="fas fa-users-gear"></i></div>
        <h3>Train the Trainer</h3>
        <p>We certify government school teachers through L1 and L2 batches, so capacity stays inside the system.</p>
      </article>

      <article class="govt-why-card reveal">
        <div class="gwc-icon gwc-amber"><i class="fas fa-shield-halved"></i></div>
        <h3>E-Check Verification</h3>
        <p>No batch is certified until it clears our e-check verification — a documented quality gate for every cohort.</p>
      </article>

      <article class="govt-why-card reveal">
        <div class="gwc-icon gwc-red"><i class="fas fa-building-columns"></i></div>
        <h3>Existing Infrastructure</h3>
        <p>Programmes run in the school's existing computer labs. No procurement cycle, no new hardware budget.</p>
      </article>
    </div>
  </div>
</section>

<!-- Section 3: States & Union Territories We Work In -->
<section class="section-govt-states">
  <div class="container">
    <div class="govt-reach-panel reveal">
      <div class="govt-reach-top">
        
        <!-- Left column: Headings and State Chips -->
        <div class="grp-left">
          <div class="grp-eyebrow"><i class="fas fa-map-location-dot"></i> OUR REACH</div>
          <h2 class="grp-title">States &amp; Union Territories <br>We Work In</h2>
          <p class="grp-desc">Each state's partners, educators, students and batches are maintained separately, so every outcome stays traceable to its district.</p>
          
          <div class="grp-chips">
            <?php foreach ($states as $st): ?>
              <span class="grp-chip"><i class="fas fa-location-dot"></i> <?= e($st['name']) ?></span>
            <?php endforeach; ?>
            <span class="grp-chip grp-chip-more">+ More States</span>
          </div>
        </div>

        <!-- Center column: Illuminated India Map -->
        <div class="grp-center">
          <div class="grp-map-frame">
            <img src="<?= e(asset_v('img/govt-india-nodes.jpg')) ?>" alt="SSD Prayas Pan-India Network Map" class="grp-map-img">
            <div class="grp-map-overlay"></div>
          </div>
        </div>

        <!-- Right column: 5 Stacked Dark Metric Cards -->
        <div class="grp-right">
          <div class="grp-stat-card">
            <div class="gsc-icon gsc-blue"><i class="fas fa-users"></i></div>
            <div class="gsc-info">
              <strong class="gsc-val"><?= number_format(display_stat($stats['states'], 6)) ?>+</strong>
              <span class="gsc-label">States &amp; UTs engaged</span>
            </div>
          </div>
          <div class="grp-stat-card">
            <div class="gsc-icon gsc-cyan"><i class="fas fa-building-columns"></i></div>
            <div class="gsc-info">
              <strong class="gsc-val"><?= number_format(display_stat($stats['partners'], 250)) ?>+</strong>
              <span class="gsc-label">Institutions onboarded</span>
            </div>
          </div>
          <div class="grp-stat-card">
            <div class="gsc-icon gsc-green"><i class="fas fa-graduation-cap"></i></div>
            <div class="gsc-info">
              <strong class="gsc-val"><?= number_format(display_stat($stats['students'], 25000)) ?>+</strong>
              <span class="gsc-label">Students trained</span>
            </div>
          </div>
          <div class="grp-stat-card">
            <div class="gsc-icon gsc-teal"><i class="fas fa-chart-column"></i></div>
            <div class="gsc-info">
              <strong class="gsc-val">100%</strong>
              <span class="gsc-label">Batch-wise tracking</span>
            </div>
          </div>
          <div class="grp-stat-card">
            <div class="gsc-icon gsc-purple"><i class="fas fa-layer-group"></i></div>
            <div class="gsc-info">
              <strong class="gsc-val">L1 – L2</strong>
              <span class="gsc-label">Structured training levels</span>
            </div>
          </div>
        </div>

      </div>

      <!-- Bottom Row: Quote Box & White Showcase Card -->
      <div class="govt-reach-bottom">
        <div class="grp-quote-box">
          <span class="grp-quote-icon qm-start"><i class="fas fa-quote-left"></i></span>
          <div class="grp-quote-text">
            <p>Empowering every district with future-ready skills.</p>
            <cite>— SSD Prayas</cite>
          </div>
          <span class="grp-quote-icon qm-end"><i class="fas fa-quote-right"></i></span>
        </div>

        <a href="<?= e(url('ai-for-school')) ?>" class="grp-showcase-card">
          <div class="gsc-thumb-wrap">
            <img src="<?= e(asset_v('img/govt-school-students.jpg')) ?>" alt="Students in school computer lab" class="gsc-thumb">
          </div>
          <div class="gsc-text">
            <span class="gsc-sub">From Schools</span>
            <strong class="gsc-main">to Stronger States</strong>
          </div>
          <span class="gsc-arrow"><i class="fas fa-arrow-right"></i></span>
        </a>
      </div>

    </div>
  </div>
</section>

<!-- Section 4: How a State Project Runs -->
<section class="section-govt-runs">
  <div class="container">
    <div class="section-head is-center reveal">
      <div class="pill-badge pill-green"><i class="fas fa-diagram-project"></i> DELIVERY MODEL</div>
      <h2>How a State Project <span class="text-blue">Runs</span></h2>
      <p class="sec-subtitle">A proven, end-to-end model that works for districts, states and large scale roll-outs.</p>
    </div>

    <div class="govt-milestones-grid">
      
      <article class="govt-step-card reveal">
        <div class="gstep-num gstep-blue">01</div>
        <h4>Scoping &amp; MoU</h4>
        <p>Districts, schools and target numbers are agreed with the department and recorded against the project.</p>
      </article>

      <div class="gstep-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></div>

      <article class="govt-step-card reveal">
        <div class="gstep-num gstep-green">02</div>
        <h4>Partner Onboarding</h4>
        <p>Each participating school or institute is onboarded into the partner database with district and scale.</p>
      </article>

      <div class="gstep-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></div>

      <article class="govt-step-card reveal">
        <div class="gstep-num gstep-amber">03</div>
        <h4>Educator Batches</h4>
        <p>Government teachers are grouped into L1 and L2 batches and trained in a phased district-by-district calendar.</p>
      </article>

      <div class="gstep-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></div>

      <article class="govt-step-card reveal">
        <div class="gstep-num gstep-red">04</div>
        <h4>Student Roll-out</h4>
        <p>Trained educators, supported by our faculty, deliver the graded AI curriculum in their own classrooms.</p>
      </article>

      <div class="gstep-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></div>

      <article class="govt-step-card reveal">
        <div class="gstep-num gstep-purple">05</div>
        <h4>E-Check &amp; Audit</h4>
        <p>Each batch is verified, with attendance, assessment and outcome records available to the department.</p>
      </article>

      <div class="gstep-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></div>

      <article class="govt-step-card reveal">
        <div class="gstep-num gstep-cyan">06</div>
        <h4>Certification &amp; Report</h4>
        <p>Certificates are issued and a consolidated report is shared covering coverage, completion and certification.</p>
      </article>

    </div>
  </div>
</section>

<!-- Section 5: CTA Banner -->
<section class="section-govt-cta">
  <div class="container">
    <div class="govt-cta-banner reveal">
      <div class="gcb-watermark" aria-hidden="true">
        <img src="<?= e(asset_v('img/govt-dome-watermark.svg')) ?>" alt="">
      </div>
      <div class="gcb-content">
        <span class="gcb-eyebrow">LET'S BUILD IMPACT TOGETHER</span>
        <h2 class="gcb-title">Planning a district or state programme?</h2>
        <p class="gcb-desc">Share the scope — districts, schools and target numbers — and we will come back with a phased delivery plan and timeline.</p>
      </div>
      <div class="gcb-actions">
        <a href="<?= e(url('contact')) ?>" class="btn-cta-yellow">Discuss a Project <i class="fas fa-arrow-right"></i></a>
        <a href="mailto:<?= e(SITE_EMAIL) ?>" class="btn-cta-outline"><i class="fas fa-envelope"></i> Email Us</a>
      </div>
    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
