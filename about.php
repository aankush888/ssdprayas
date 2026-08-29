<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'about';
$page_title = 'About Us | ' . SITE_NAME;
$page_desc  = 'SSD Prayas is an AI skilling mission taking practical Artificial Intelligence education to students, educators and professionals across India — online, offline and at government scale.';

$stats = impact_stats($pdo);
$states = rows($pdo, "SELECT name FROM states WHERE is_active = 1 ORDER BY sort_order, name");

include __DIR__ . '/includes/header.php';
?>

<main>

<section class="page-hero">
  <div class="container">
    <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; About Us</p>
    <h1>Building India's <span class="grad-text">AI-Ready</span> Generation</h1>
    <p>SSD Prayas exists for one reason — to make sure practical AI skills reach every classroom,
       every teacher and every working professional, not just the ones in metro cities.</p>
  </div>
</section>

<!-- Story -->
<section class="section">
  <div class="container split">
    <div class="reveal">
      <span class="eyebrow"><i class="fas fa-seedling"></i> Our Story</span>
      <h2>From a Simple Question to a <span class="text-brand">National Mission</span></h2>
      <p style="margin:18px 0">
        AI is rewriting how the world works, but the skills to build with it were reaching only a
        narrow slice of Indian students. Schools in Tier 2 and Tier 3 cities, government schools and
        district institutions had the ambition — what they lacked was curriculum, trained teachers
        and a delivery system that actually worked on the ground.
      </p>
      <p style="margin-bottom:18px">
        SSD Prayas was built to close that gap. We do not just drop a syllabus and leave. We train
        the school's own educators, run the classes inside existing computer labs, track every batch
        through assessment and e-check, and certify both teachers and students. The result is an AI
        ecosystem that keeps running after we have moved to the next district.
      </p>
      <ul class="tick-list">
        <li>NEP 2020-aligned from day one</li>
        <li>Google for Education Professional Development Partner</li>
        <li>Built for scale — single schools to statewide projects</li>
      </ul>
    </div>
    <div class="split-media reveal">
      <img src="<?= e(asset('student-laptop.png')) ?>" alt="Students learning AI in a school computer lab" loading="lazy">
    </div>
  </div>
</section>

<!-- Mission / Vision / Values -->
<section class="section section-soft">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow is-green"><i class="fas fa-compass"></i> What Drives Us</span>
      <h2>Mission, Vision &amp; <span class="text-brand">Values</span></h2>
    </div>

    <div class="grid grid-3">
      <article class="card reveal">
        <div class="card-icon i-blue"><i class="fas fa-bullseye"></i></div>
        <h3>Our Mission</h3>
        <p>To deliver practical, hands-on AI education to every learner group — students, educators
           and professionals — regardless of their city, board or background.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-green"><i class="fas fa-eye"></i></div>
        <h3>Our Vision</h3>
        <p>An India where a Class 6 student in a district school has the same access to AI skills
           as one in a metro private school.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-yellow"><i class="fas fa-hand-holding-heart"></i></div>
        <h3>Our Values</h3>
        <p>Build, don't just consume. Train the trainer. Measure everything. And never let
           geography decide a child's opportunity.</p>
      </article>
    </div>
  </div>
</section>

<!-- What we do -->
<section class="section">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow"><i class="fas fa-cubes"></i> What We Do</span>
      <h2>Four Pillars of <span class="text-brand">SSD Prayas</span></h2>
    </div>

    <div class="grid grid-4">
      <article class="card reveal">
        <div class="card-icon i-blue"><i class="fas fa-graduation-cap"></i></div>
        <h3>Student Skilling</h3>
        <p>Grade-wise AI curriculum for Class 3 to 12 with hands-on projects and certification.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-green"><i class="fas fa-chalkboard-user"></i></div>
        <h3>Educator Training</h3>
        <p>Structured L1 and L2 batches that turn existing teachers into confident AI educators.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-yellow"><i class="fas fa-briefcase"></i></div>
        <h3>Professional Upskilling</h3>
        <p>Applied AI programmes for working professionals, delivered online and on weekends.</p>
      </article>
      <article class="card reveal">
        <div class="card-icon i-red"><i class="fas fa-landmark"></i></div>
        <h3>Government Projects</h3>
        <p>Large-scale, multi-state skilling projects with full batch and certification tracking.</p>
      </article>
    </div>
  </div>
</section>

<!-- Leadership -->
<section class="section section-soft" id="leadership">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow is-red"><i class="fas fa-users"></i> Leadership</span>
      <h2>The People Behind <span class="text-brand">SSD Prayas</span></h2>
      <p>A team that has spent years inside classrooms, education policy and technology delivery.</p>
    </div>

    <div class="grid grid-2" style="max-width:840px;margin-inline:auto">
      <!-- TODO: real designations, photos aur bio client se lene hain -->
      <article class="team-card reveal">
        <div class="team-avatar">AS</div>
        <h3>Ashish Sir</h3>
        <p class="team-role">Leadership &amp; Strategy</p>
        <p>Leads SSD Prayas partnerships and programme strategy, working directly with schools,
           institutions and government departments on AI skilling roll-outs.</p>
      </article>

      <article class="team-card reveal">
        <div class="team-avatar">MS</div>
        <h3>Manoj Sir</h3>
        <p class="team-role">Programmes &amp; Delivery</p>
        <p>Oversees curriculum delivery, educator training batches and on-ground execution across
           partner institutions and project states.</p>
      </article>
    </div>
  </div>
</section>

<!-- Reach -->
<section class="section">
  <div class="container">
    <div class="gov-panel reveal">
      <div class="gov-split">
        <div>
          <span class="eyebrow"><i class="fas fa-map-location-dot"></i> Our Reach</span>
          <h2>Working Across <br>Multiple States of India</h2>
          <p style="margin-top:18px">Our partner, educator and student databases are maintained
             state-wise, so every batch, certification and outcome stays traceable.</p>
          <div class="state-chips">
            <?php foreach ($states as $st): ?>
              <span class="state-chip"><i class="fas fa-location-dot"></i> <?= e($st['name']) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="gov-metrics">
          <div class="gov-metric">
            <strong><?= number_format(display_stat($stats['partners'], 250)) ?>+</strong>
            <span>Partner institutions</span>
          </div>
          <div class="gov-metric">
            <strong><?= number_format(display_stat($stats['educators'], 800)) ?>+</strong>
            <span>Educators trained</span>
          </div>
          <div class="gov-metric">
            <strong><?= number_format(display_stat($stats['students'], 25000)) ?>+</strong>
            <span>Students impacted</span>
          </div>
          <div class="gov-metric">
            <strong><?= number_format(display_stat($stats['states'], 6)) ?>+</strong>
            <span>States engaged</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section-tight" style="padding-bottom:90px">
  <div class="container">
    <div class="cta-band reveal">
      <div>
        <h2>Want to work with us?</h2>
        <p>Partner your institution, join our educator network, or talk to us about a
           district or state-level project.</p>
      </div>
      <div class="cta-actions">
        <a href="<?= e(url('/')) ?>#contact" class="btn btn-yellow">Contact Our Team <i class="fas fa-arrow-right"></i></a>
        <a href="<?= e(url('careers')) ?>" class="btn btn-light">View Careers</a>
      </div>
    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
