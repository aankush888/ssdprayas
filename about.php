<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'about';
$page_title = 'About Us | ' . SITE_NAME;
$page_desc  = 'SSD Prayas is an AI skilling mission taking practical Artificial Intelligence education to students, educators and professionals across India — online, offline and at government scale.';

$stats  = impact_stats($pdo);
$states = rows($pdo, "SELECT name FROM states WHERE is_active = 1 ORDER BY sort_order, name");

include __DIR__ . '/includes/header.php';
?>

<main class="page-about">

<!-- ============ HERO SECTION ============ -->
<section class="about-hero">
  <div class="container about-hero-container">
    <div class="about-hero-content">
      <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; About Us</p>
      
      <div class="pill-badge pill-blue">
        <i class="fas fa-compass"></i> OUR PURPOSE
      </div>
      
      <h1 class="about-hero-title">
        Building India's <br>
        <span class="text-blue">AI-Ready</span> <span class="text-green">Generation</span>
      </h1>
      
      <p class="about-hero-desc">
        SSD Prayas exists for one reason — to make sure practical AI skills reach every classroom, every teacher and every working professional, not just the ones in metro cities.
      </p>
      
      <div class="about-trust-row">
        <div class="about-trust-item">
          <span class="ati-icon ati-blue"><i class="fas fa-users"></i></span>
          <span>People-Centric Approach</span>
        </div>
        <div class="about-trust-item">
          <span class="ati-icon ati-blue"><i class="fas fa-cubes"></i></span>
          <span>Scalable &amp; Auditable</span>
        </div>
        <div class="about-trust-item">
          <span class="ati-icon ati-teal"><i class="fas fa-circle-check"></i></span>
          <span>Real Impact on Ground</span>
        </div>
      </div>
      
      <div class="about-hero-actions">
        <a href="#story" class="btn btn-blue-pill">Our Story <i class="fas fa-arrow-right"></i></a>
        <a href="<?= e(url('contact')) ?>" class="btn btn-white-play-pill">
          <span class="play-icon-circle"><i class="fas fa-play"></i></span> Watch Video
        </a>
      </div>
    </div>

    <!-- Floating glass badge over hero photo -->
    <aside class="about-bharat-badge" aria-label="Empowering Students Educators Institutions">
      <div class="abb-line abb-dim">Empowering</div>
      <div class="abb-line abb-bold">Students</div>
      <div class="abb-line abb-bold">Educators</div>
      <div class="abb-line abb-dim">Institutions</div>
      <div class="abb-line abb-bold">A Stronger Bharat</div>
      <div class="abb-stripes">
        <span class="stripe-saffron"></span>
        <span class="stripe-green"></span>
      </div>
    </aside>
  </div>
</section>

<!-- ============ SECTION 2: OUR STORY ============ -->
<section class="section section-about-story" id="story">
  <div class="container">
    <div class="story-grid reveal">
      
      <div class="story-left">
        <div class="pill-badge pill-blue"><i class="fas fa-seedling"></i> OUR STORY</div>
        <h2>From a Simple Question <br>to a <span class="text-blue">National Mission</span></h2>
        
        <p class="story-text">
          AI is rewriting how the world works, but the skills to build with it were reaching only a narrow slice of Indian students. Schools in Tier 2 and Tier 3 cities, government schools and district institutions had the ambition — what they lacked was curriculum, trained teachers and a delivery system that actually worked on the ground.
        </p>
        
        <p class="story-text">
          SSD Prayas was built to close that gap. We do not just drop a syllabus and leave. We train the school's own educators, run the classes inside existing computer labs, track every batch through assessment and e-checks, and certify both teachers and students. The result is an AI ecosystem that keeps running after we have moved to the next district.
        </p>
        
        <div class="story-checks">
          <div class="story-check-item">
            <span class="check-icon"><i class="fas fa-circle-check"></i></span>
            <span>NEP 2020-aligned from day one</span>
          </div>
          <div class="story-check-item">
            <span class="check-icon"><i class="fas fa-circle-check"></i></span>
            <span>Google for Education Professional Development Partner</span>
          </div>
          <div class="story-check-item">
            <span class="check-icon"><i class="fas fa-circle-check"></i></span>
            <span>Built for scale — single schools to statewide projects</span>
          </div>
        </div>
      </div>

      <div class="story-right">
        <div class="story-media-wrap">
          <img src="<?= e(asset_v('img/about-story.jpg')) ?>" alt="SSD Prayas Teacher and Students in Classroom" class="story-main-img">
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============ SECTION 3: MISSION, VISION & VALUES ============ -->
<section class="section section-about-mvv">
  <div class="container">
    <div class="section-head is-center reveal" style="position: relative;">
      <div class="pill-badge pill-blue"><i class="fas fa-compass"></i> WHAT DRIVES US</div>
      <h2>Mission, Vision &amp; <span class="text-blue">Values</span></h2>
      <p class="sec-subtitle">Our work is guided by a clear purpose, a bold vision and values that keep us grounded.</p>
      
      <div class="mvv-badge-calligraphy" aria-hidden="true">
        <span>People</span>
        <span>Education</span>
        <strong>Progress</strong>
        <svg viewBox="0 0 80 8" class="calligraphy-swoosh"><path d="M0,4 Q40,8 80,2" stroke="#10b981" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
      </div>
    </div>

    <div class="about-mvv-grid">
      <article class="mvv-card reveal">
        <div class="mvv-icon mvv-blue"><i class="fas fa-bullseye"></i></div>
        <h3>Our Mission</h3>
        <p>To deliver practical, hands-on AI education to every learner group — students, educators and professionals — regardless of their city, board or background.</p>
      </article>

      <article class="mvv-card reveal">
        <div class="mvv-icon mvv-green"><i class="fas fa-eye"></i></div>
        <h3>Our Vision</h3>
        <p>An India where a Class 6 student in a district school has the same access to AI skills as one in a metro private school.</p>
      </article>

      <article class="mvv-card reveal">
        <div class="mvv-icon mvv-amber"><i class="fas fa-users"></i></div>
        <h3>Our Values</h3>
        <p>Build, don't just consume. Train the trainer. Measure everything. And never let geography decide a child's opportunity.</p>
      </article>
    </div>
  </div>
</section>

<!-- ============ SECTION 4: FOUR PILLARS ============ -->
<section class="section section-about-pillars">
  <div class="container">
    <div class="section-head is-center reveal">
      <div class="pill-badge pill-blue"><i class="fas fa-cubes"></i> WHAT WE DO</div>
      <h2>Four Pillars of <span class="text-blue">SSD Prayas</span></h2>
      <p class="sec-subtitle">A comprehensive model designed to create long-term, measurable impact.</p>
    </div>

    <div class="about-pillars-grid">
      <article class="pillar-card reveal">
        <div class="pillar-icon p-blue"><i class="fas fa-graduation-cap"></i></div>
        <h3>Student Skilling</h3>
        <p>Grade-wise AI curriculum for Class 3 to 12 with hands-on projects and certification.</p>
        <a href="<?= e(url('ai-for-school')) ?>" class="pillar-link pbtn-blue" aria-label="Learn more about Student Skilling"><i class="fas fa-arrow-right"></i></a>
      </article>

      <article class="pillar-card reveal">
        <div class="pillar-icon p-green"><i class="fas fa-chalkboard-user"></i></div>
        <h3>Educator Training</h3>
        <p>Structured L1 and L2 programmes that turn existing teachers into confident AI educators.</p>
        <a href="<?= e(url('programmes')) ?>#educators" class="pillar-link pbtn-green" aria-label="Learn more about Educator Training"><i class="fas fa-arrow-right"></i></a>
      </article>

      <article class="pillar-card reveal">
        <div class="pillar-icon p-amber"><i class="fas fa-briefcase"></i></div>
        <h3>Professional Upskilling</h3>
        <p>Applied AI programmes for working professionals, delivered online and on weekends.</p>
        <a href="<?= e(url('programmes')) ?>#professionals" class="pillar-link pbtn-amber" aria-label="Learn more about Professional Upskilling"><i class="fas fa-arrow-right"></i></a>
      </article>

      <article class="pillar-card reveal">
        <div class="pillar-icon p-red"><i class="fas fa-building-columns"></i></div>
        <h3>Government Projects</h3>
        <p>Large-scale, multi-state skilling projects with full batch and certification tracking.</p>
        <a href="<?= e(url('government')) ?>" class="pillar-link pbtn-red" aria-label="Learn more about Government Projects"><i class="fas fa-arrow-right"></i></a>
      </article>
    </div>
  </div>
</section>

<!-- ============ SECTION 5: LEADERSHIP ============ -->
<section class="section section-about-people" id="leadership">
  <div class="container">
    <div class="section-head is-center reveal" style="position: relative;">
      <div class="pill-badge pill-red"><i class="fas fa-users"></i> LEADERSHIP</div>
      <h2>The People Behind <span class="text-blue">SSD Prayas</span></h2>
      <p class="sec-subtitle">A team that has spent years inside classrooms, education policy and technology delivery.</p>
      
      <div class="people-badge-calligraphy" aria-hidden="true">
        <span>Same</span>
        <span>Opportunities</span>
        <strong>For Every Learner</strong>
        <svg viewBox="0 0 100 8" class="calligraphy-swoosh"><path d="M0,4 Q50,8 100,2" stroke="#10b981" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
      </div>
    </div>

    <div class="about-people-grid">
      <article class="leader-card reveal">
        <div class="leader-avatar-wrap">
          <img src="<?= e(asset_v('img/team-ashish.png')) ?>" alt="Ashish Sir" class="leader-avatar">
        </div>
        <div class="leader-info">
          <h3>Ashish Sir</h3>
          <p class="leader-role">Leadership &amp; Strategy</p>
          <p class="leader-bio">Leads SSD Prayas' partnership and programme strategy, working directly with schools, institutions and government departments on AI skilling roll-outs.</p>
        </div>
      </article>

      <article class="leader-card reveal">
        <div class="leader-avatar-wrap">
          <img src="<?= e(asset_v('img/team-manoj.png')) ?>" alt="Manoj Sir" class="leader-avatar">
        </div>
        <div class="leader-info">
          <h3>Manoj Sir</h3>
          <p class="leader-role">Programmes &amp; Delivery</p>
          <p class="leader-bio">Oversees curriculum delivery, educator training batches and on-ground execution across partner institutions and project states.</p>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- ============ SECTION 6: WORKING ACROSS STATES ============ -->
<section class="section-about-reach">
  <div class="container">
    <div class="govt-reach-panel reveal">
      <div class="govt-reach-top">
        
        <!-- Left: Info & State Chips -->
        <div class="grp-left">
          <div class="grp-eyebrow"><i class="fas fa-map-location-dot"></i> OUR REACH</div>
          <h2 class="grp-title">Working Across <br>Multiple States of India</h2>
          <p class="grp-desc">Our partner, educator and student databases are maintained state-wise, so every batch, certification and outcome stays traceable.</p>
          
          <div class="grp-chips">
            <?php foreach ($states as $st): ?>
              <span class="grp-chip"><i class="fas fa-location-dot"></i> <?= e($st['name']) ?></span>
            <?php endforeach; ?>
            <span class="grp-chip grp-chip-more">+ More States</span>
          </div>
        </div>

        <!-- Center: Illuminated India Map -->
        <div class="grp-center">
          <div class="grp-map-frame">
            <img src="<?= e(asset_v('img/govt-india-nodes.jpg')) ?>" alt="SSD Prayas Pan-India Network Map" class="grp-map-img">
            <div class="grp-map-overlay"></div>
          </div>
        </div>

        <!-- Right: 4 Stacked Metric Cards -->
        <div class="grp-right">
          <div class="grp-stat-card">
            <div class="gsc-icon gsc-cyan"><i class="fas fa-building-columns"></i></div>
            <div class="gsc-info">
              <strong class="gsc-val"><?= number_format(display_stat($stats['partners'], 250)) ?>+</strong>
              <span class="gsc-label">Partner Institutions</span>
            </div>
          </div>
          <div class="grp-stat-card">
            <div class="gsc-icon gsc-blue"><i class="fas fa-chalkboard-user"></i></div>
            <div class="gsc-info">
              <strong class="gsc-val"><?= number_format(display_stat($stats['educators'], 800)) ?>+</strong>
              <span class="gsc-label">Educators Trained</span>
            </div>
          </div>
          <div class="grp-stat-card">
            <div class="gsc-icon gsc-teal"><i class="fas fa-users"></i></div>
            <div class="gsc-info">
              <strong class="gsc-val"><?= number_format(display_stat($stats['students'], 25000)) ?>+</strong>
              <span class="gsc-label">Students Impacted</span>
            </div>
          </div>
          <div class="grp-stat-card">
            <div class="gsc-icon gsc-purple"><i class="fas fa-award"></i></div>
            <div class="gsc-info">
              <strong class="gsc-val"><?= number_format(display_stat($stats['states'], 6)) ?>+</strong>
              <span class="gsc-label">States Engaged</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- ============ SECTION 7: CTA BANNER ============ -->
<section class="section-about-cta">
  <div class="container">
    <div class="about-cta-banner reveal">
      <div class="acb-content">
        <h2 class="acb-title">Want to work with us?</h2>
        <p class="acb-desc">Partner your institution, join our educator network, or talk to us about a district or state-level project.</p>
      </div>
      <div class="acb-actions">
        <a href="<?= e(url('contact')) ?>" class="btn-cta-yellow">Contact Our Team <i class="fas fa-arrow-right"></i></a>
        <a href="<?= e(url('careers')) ?>" class="btn-cta-outline"><i class="fas fa-briefcase"></i> View Careers</a>
      </div>
    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
