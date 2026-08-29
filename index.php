<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'home';
$page_title = SITE_NAME . ' | AI Skilling for Students, Educators & Professionals';
$page_desc  = 'SSD Prayas delivers NEP 2020-aligned AI skilling across India — Class 3 to 12 students, school educators and working professionals. Online and offline, with government and school partnerships.';

/* ---------- Contact form ---------- */
$form_ok = '';
$form_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'enquiry') {
    if (!csrf_check()) {
        $form_err = 'Session expired. Please refresh the page and try again.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $org      = trim($_POST['organisation'] ?? '');
        $audience = $_POST['audience'] ?? 'school';
        $state_id = $_POST['state_id'] !== '' ? (int)$_POST['state_id'] : null;
        $message  = trim($_POST['message'] ?? '');

        $allowed_audience = ['student','educator','professional','school','government','partner','other'];
        if (!in_array($audience, $allowed_audience, true)) {
            $audience = 'other';
        }

        if ($name === '' || $phone === '') {
            $form_err = 'Please enter your name and phone number.';
        } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $form_err = 'Please enter a valid email address.';
        } elseif (!preg_match('/^[0-9+\-\s()]{8,20}$/', $phone)) {
            $form_err = 'Please enter a valid phone number.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO enquiries (name, email, phone, organisation, audience, state_id, message)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([$name, $email, $phone, $org, $audience, $state_id, $message]);
                $form_ok = 'Thank you ' . $name . '! Your enquiry is with our team — we will call you back within 24 hours.';
            } catch (PDOException $e) {
                error_log('Enquiry insert failed: ' . $e->getMessage());
                $form_err = 'Something went wrong. Please call us on ' . CONTACT_PHONE . '.';
            }
        }
    }
}

/* ---------- Page data ---------- */
$stats  = impact_stats($pdo);
$states = rows($pdo, "SELECT id, name FROM states WHERE is_active = 1 ORDER BY sort_order, name");
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
        <a href="#contact" class="btn btn-primary">Start Your AI Journey <i class="fas fa-arrow-right"></i></a>
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
          <a href="#programmes" class="link-arrow">See the programme <span>→</span></a>
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
          <a href="#programmes" class="link-arrow">See the training <span>→</span></a>
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
          <a href="#programmes" class="link-arrow">See the courses <span>→</span></a>
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

<!-- ============ PROGRAMMES — LEARNING JOURNEY ============ -->
<section class="prog" id="programmes">
  <div class="container">

    <div class="section-head is-center reveal">
      <span class="eyebrow"><i class="fas fa-route"></i> Our Programmes</span>
      <h2>The SSD Prayas <span class="grad-text">Learning Journey</span></h2>
      <p>AI is not one course taught once. The curriculum grows with the learner — from a
         Class 3 child meeting a computer, to a Class 12 student building real AI projects.</p>
    </div>

    <div class="journey">
      <article class="jstep reveal">
        <div class="jstep-dot"><i class="fas fa-shapes"></i></div>
        <span class="jstep-grade">Class 3–5</span>
        <h3>Digital Foundations</h3>
        <p>Computer confidence, safe internet habits and a first, playful introduction to
           what artificial intelligence actually is.</p>
      </article>

      <article class="jstep reveal">
        <div class="jstep-dot"><i class="fas fa-puzzle-piece"></i></div>
        <span class="jstep-grade">Class 6–8</span>
        <h3>AI Fundamentals</h3>
        <p>Computational thinking, block-based coding and the core ideas behind machine
           learning — taught through hands-on activities.</p>
      </article>

      <article class="jstep reveal">
        <div class="jstep-dot"><i class="fas fa-diagram-project"></i></div>
        <span class="jstep-grade">Class 9–10</span>
        <h3>Applied Machine Learning</h3>
        <p>Real datasets, real AI tools and real problems. Students start building projects
           worth putting in a portfolio.</p>
      </article>

      <article class="jstep reveal">
        <div class="jstep-dot"><i class="fas fa-rocket"></i></div>
        <span class="jstep-grade">Class 11–12</span>
        <h3>Specialisation &amp; Careers</h3>
        <p>Advanced AI, capstone projects, certification and clear guidance on the courses
           and careers that follow.</p>
      </article>
    </div>

    <div class="prog-panels">
      <article class="ppanel reveal">
        <div class="ppanel-ico"><i class="fas fa-chalkboard-user"></i></div>
        <div>
          <h3>Educator Training — L1 &amp; L2</h3>
          <p>Your own teachers are trained, assessed and certified so AI teaching keeps
             running on campus after our team moves on.</p>
          <div class="prog-tags">
            <span class="prog-tag">L1 Foundation</span>
            <span class="prog-tag">L2 Advanced</span>
            <span class="prog-tag">Gemini certified</span>
            <span class="prog-tag">E-check verified</span>
          </div>
          <a href="<?= e(url('careers')) ?>" class="link-arrow">Join as an educator <span>→</span></a>
        </div>
      </article>

      <article class="ppanel reveal">
        <div class="ppanel-ico"><i class="fas fa-briefcase"></i></div>
        <div>
          <h3>Professional Upskilling</h3>
          <p>Applied AI for working professionals — the tools and workflows actually used
             on the job, taught on evenings and weekends.</p>
          <div class="prog-tags">
            <span class="prog-tag">Applied AI</span>
            <span class="prog-tag">Prompt engineering</span>
            <span class="prog-tag">Live projects</span>
            <span class="prog-tag">Online friendly</span>
          </div>
          <a href="#contact" class="link-arrow">Talk to an advisor <span>→</span></a>
        </div>
      </article>
    </div>

  </div>
</section>

<!-- ============ AI FOR SCHOOL PROGRAMME ============ -->
<section class="section" id="ai-for-school">
  <div class="container split">

    <div class="reveal">
      <span class="eyebrow is-green"><i class="fas fa-school"></i> Flagship Programme</span>
      <h2>The <span class="text-brand">AI for School</span> Programme</h2>
      <p style="margin:18px 0 26px">
        Our flagship school engagement. SSD Prayas partners with a school, trains its educators,
        delivers the AI curriculum inside existing computer labs, and certifies both the teachers
        and the students — a complete, self-sustaining AI ecosystem on campus.
      </p>

      <ul class="tick-list">
        <li>Grade-wise AI curriculum for Class 3 to 12</li>
        <li>On-campus educator training and certification</li>
        <li>Project exhibitions and student portfolios</li>
        <li>Assessment, e-check and certification cycle</li>
        <li>Runs on the school's existing infrastructure</li>
      </ul>

      <div class="hero-actions" style="margin-bottom:0">
        <a href="#contact" class="btn btn-primary">Partner Your School <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>

    <div class="split-media reveal">
      <img src="<?= e(asset('student-laptop.png')) ?>" alt="Students in an AI class at school" loading="lazy">
    </div>

  </div>
</section>

<!-- ============ MODE ============ -->
<section class="section section-soft" id="mode">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow is-yellow"><i class="fas fa-tower-broadcast"></i> Delivery Mode</span>
      <h2>Learn <span class="text-brand">Online</span>, <span class="text-brand">Offline</span> or Both</h2>
      <p>Geography should never decide who gets AI education. We deliver the same curriculum
         in whichever format works for your school, district or team.</p>
    </div>

    <div class="mode-grid">
      <div class="mode-card reveal">
        <i class="fas fa-chalkboard-user"></i>
        <h3>Offline</h3>
        <p>Trained faculty travel to your campus and run hands-on sessions in your existing
           computer lab — the format most schools and government projects prefer.</p>
      </div>
      <div class="mode-card reveal">
        <i class="fas fa-video"></i>
        <h3>Online</h3>
        <p>Live instructor-led virtual classrooms with recordings, digital worksheets and
           remote mentor support — ideal for professionals and remote districts.</p>
      </div>
      <div class="mode-card reveal">
        <i class="fas fa-shuffle"></i>
        <h3>Hybrid</h3>
        <p>Offline practical labs combined with online theory and mentoring — the model we
           use for large multi-district educator training.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============ GOVERNMENT PROJECTS ============ -->
<section class="section" id="government">
  <div class="container">
    <div class="gov-panel reveal">
      <div class="gov-split">

        <div>
          <span class="eyebrow"><i class="fas fa-landmark"></i> Government Projects</span>
          <h2>Large-Scale AI Skilling, <br>Across Multiple States</h2>
          <p style="margin:18px 0 8px">
            SSD Prayas executes government and institutional AI skilling projects at state scale —
            managing partner onboarding, educator training batches, student enrolment and
            certification tracking through a single monitored system.
          </p>

          <div class="state-chips">
            <?php foreach (array_slice($states, 0, 6) as $st): ?>
              <span class="state-chip"><i class="fas fa-location-dot"></i> <?= e($st['name']) ?></span>
            <?php endforeach; ?>
            <span class="state-chip">&amp; more</span>
          </div>
        </div>

        <div class="gov-metrics">
          <div class="gov-metric">
            <strong><?= number_format($show_states) ?>+</strong>
            <span>States &amp; UTs engaged</span>
          </div>
          <div class="gov-metric">
            <strong><?= number_format($show_partners) ?>+</strong>
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
        <a href="#contact" class="btn btn-yellow">Start Your AI Journey <i class="fas fa-arrow-right"></i></a>
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

<!-- ============ CONTACT ============ -->
<section class="section" id="contact">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow is-green"><i class="fas fa-comments"></i> Get In Touch</span>
      <h2>Let's Plan Your <span class="text-brand">AI Roll-out</span></h2>
      <p>Tell us about your school, institution or team. Our team responds within 24 hours.</p>
    </div>

    <div class="split" style="align-items:start">

      <div class="reveal">
        <a class="contact-item" href="tel:+<?= e(CONTACT_PHONE_RAW) ?>">
          <i class="fas fa-phone"></i>
          <div>
            <b>Call Us</b>
            <p><?= e(CONTACT_PHONE) ?><br><span style="color:var(--muted);font-size:13px">Mon–Sat, 10 AM – 7 PM</span></p>
          </div>
        </a>

        <a class="contact-item" href="<?= e(whatsapp_link('Hello SSD Prayas!')) ?>" target="_blank" rel="noopener">
          <i class="fab fa-whatsapp"></i>
          <div>
            <b>WhatsApp</b>
            <p><?= e(CONTACT_PHONE) ?><br><span style="color:var(--muted);font-size:13px">Same number — chat anytime</span></p>
          </div>
        </a>

        <div class="contact-item">
          <i class="fas fa-location-dot"></i>
          <div>
            <b>Visit Us</b>
            <p><?= e(CONTACT_ADDRESS) ?></p>
          </div>
        </div>

        <div class="contact-item">
          <i class="fas fa-envelope"></i>
          <div>
            <b>Email Us</b>
            <p><a href="mailto:<?= e(SITE_EMAIL) ?>" style="color:var(--brand)"><?= e(SITE_EMAIL) ?></a></p>
          </div>
        </div>
      </div>

      <div class="form-card reveal">
        <?php if ($form_ok): ?>
          <div class="alert alert-success" data-autohide>
            <i class="fas fa-circle-check"></i><span><?= e($form_ok) ?></span>
          </div>
        <?php endif; ?>
        <?php if ($form_err): ?>
          <div class="alert alert-error">
            <i class="fas fa-circle-exclamation"></i><span><?= e($form_err) ?></span>
          </div>
        <?php endif; ?>

        <form method="POST" action="<?= e(url('/')) ?>#contact">
          <?= csrf_field() ?>
          <input type="hidden" name="form" value="enquiry">

          <div class="field-row">
            <div class="field">
              <label for="f-name">Your Name <span class="req">*</span></label>
              <input type="text" id="f-name" name="name" required placeholder="Full name">
            </div>
            <div class="field">
              <label for="f-phone">Phone <span class="req">*</span></label>
              <input type="tel" id="f-phone" name="phone" required placeholder="10-digit mobile number">
            </div>
          </div>

          <div class="field-row">
            <div class="field">
              <label for="f-email">Email</label>
              <input type="email" id="f-email" name="email" placeholder="you@example.com">
            </div>
            <div class="field">
              <label for="f-org">School / Organisation</label>
              <input type="text" id="f-org" name="organisation" placeholder="Institution name">
            </div>
          </div>

          <div class="field-row">
            <div class="field">
              <label for="f-audience">I am enquiring as</label>
              <select id="f-audience" name="audience">
                <option value="school">A School</option>
                <option value="student">A Student / Parent</option>
                <option value="educator">An Educator</option>
                <option value="professional">A Professional</option>
                <option value="government">Government Department</option>
                <option value="partner">Prospective Partner</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="field">
              <label for="f-state">State</label>
              <select id="f-state" name="state_id">
                <option value="">Select state</option>
                <?php foreach ($states as $st): ?>
                  <option value="<?= (int)$st['id'] ?>"><?= e($st['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="field">
            <label for="f-msg">Your Requirement</label>
            <textarea id="f-msg" name="message" placeholder="Tell us about your school, batch size, classes, timeline…"></textarea>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            Send Enquiry <i class="fas fa-paper-plane"></i>
          </button>
          <p class="field-hint" style="text-align:center;margin-top:14px">
            Your details stay with SSD Prayas. We never share them.
          </p>
        </form>
      </div>

    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
