<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'careers';
$page_title = 'Careers | ' . SITE_NAME;
$page_desc  = 'Join SSD Prayas as an AI Educator. Hybrid roles across multiple states — train school students and teachers in practical Artificial Intelligence. Apply with your resume.';

$form_ok  = '';
$form_err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'application') {
    if (!csrf_check()) {
        $form_err = 'Session expired. Please refresh the page and try again.';
    } else {
        $name       = trim($_POST['full_name'] ?? '');
        $email      = trim($_POST['email'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $state_id   = ($_POST['state_id'] ?? '') !== '' ? (int)$_POST['state_id'] : null;
        $city       = trim($_POST['city'] ?? '');
        $position   = trim($_POST['position'] ?? 'AI Educator');
        $qual       = trim($_POST['qualification'] ?? '');
        $exp        = (int)($_POST['experience_years'] ?? 0);
        $subject    = trim($_POST['subject_expertise'] ?? '');
        $work_mode  = $_POST['work_mode'] ?? 'hybrid';
        $note       = trim($_POST['cover_note'] ?? '');

        if (!in_array($work_mode, ['online', 'offline', 'hybrid'], true)) {
            $work_mode = 'hybrid';
        }

        if ($name === '' || $email === '' || $phone === '') {
            $form_err = 'Name, email and phone are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $form_err = 'Please enter a valid email address.';
        } elseif (!preg_match('/^[0-9+\-\s()]{8,20}$/', $phone)) {
            $form_err = 'Please enter a valid phone number.';
        } elseif (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
            $form_err = 'Please attach your resume (PDF or Word, max ' . MAX_UPLOAD_MB . ' MB).';
        } else {
            $resume = handle_upload('resume', UPLOAD_RESUMES, ['pdf', 'doc', 'docx'], 'cv-');

            if ($resume === null) {
                $form_err = 'Resume upload failed. Use a PDF or Word file under ' . MAX_UPLOAD_MB . ' MB.';
            } else {
                try {
                    $stmt = $pdo->prepare(
                        "INSERT INTO job_applications
                         (full_name, email, phone, state_id, city, position, qualification,
                          experience_years, subject_expertise, work_mode, resume_path, cover_note)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([$name, $email, $phone, $state_id, $city, $position, $qual,
                                    $exp, $subject, $work_mode, $resume, $note]);
                    $form_ok = 'Thank you ' . $name . '! Your application has been received. '
                             . 'Our team reviews every profile and will reach out if there is a match.';
                } catch (PDOException $e) {
                    error_log('Application insert failed: ' . $e->getMessage());
                    $form_err = 'Something went wrong. Please email us at ' . SITE_EMAIL . '.';
                }
            }
        }
    }
}

$states = rows($pdo, "SELECT id, name FROM states WHERE is_active = 1 ORDER BY sort_order, name");

include __DIR__ . '/includes/header.php';
?>

<main class="page-careers">

<!-- ============ SECTION 1: HERO ============ -->
<section class="career-hero">
  <div class="container career-hero-container">
    <div class="career-hero-content">
      <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; Careers</p>
      
      <div class="career-pill-badge is-green pill-badge pill-green">
        <i class="fas fa-user-plus"></i> WE'RE HIRING EDUCATORS
      </div>
      
      <h1 class="career-hero-title">
        Teach the Skill That <br>
        <span class="text-blue">Changes</span> <span class="text-green">Careers</span>
      </h1>
      
      <p class="career-hero-desc">
        SSD Prayas hires educators only. If you can hold a classroom and you are willing to learn AI properly, we will train you, certify you and put you in front of students who need you.
      </p>
      
      <div class="career-hero-actions">
        <a href="#apply" class="btn-career-apply">Apply Now <i class="fas fa-arrow-right"></i></a>
        <a href="<?= e(whatsapp_link('Hello SSD Prayas, I want to apply as an educator.')) ?>"
           class="btn-career-ask" target="_blank" rel="noopener">
          <i class="fab fa-whatsapp"></i> Ask a Question
        </a>
      </div>

      <div class="career-trust-row">
        <div class="career-trust-item">
          <span class="cti-icon cti-blue"><i class="fas fa-users"></i></span>
          <div class="cti-text">
            <strong>Meaningful Impact</strong>
            <span>Shape future-ready learners</span>
          </div>
        </div>
        <div class="career-trust-item">
          <span class="cti-icon cti-teal"><i class="fas fa-lightbulb"></i></span>
          <div class="cti-text">
            <strong>Learn &amp; Grow</strong>
            <span>Get trained in AI</span>
          </div>
        </div>
        <div class="career-trust-item">
          <span class="cti-icon cti-cyan"><i class="fas fa-shield-halved"></i></span>
          <div class="cti-text">
            <strong>Be Part of a Mission</strong>
            <span>A skilled and stronger Bharat</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Floating quote card on top right -->
  <aside class="career-quote-card" aria-label="Better Educators Brighter Futures">
    <span class="quote-mark">“</span>
    <div class="quote-lines">
      <span>Better</span>
      <span>Educators</span>
      <span>Brighter</span>
      <span>Futures</span>
    </div>
  </aside>
</section>

<!-- ============ SECTION 2: ROLE SNAPSHOT (Continuous Right to Left Ticker) ============ -->
<section class="section section-career-snapshot">
  <div class="career-ticker-wrapper" aria-label="Role snapshot continuous ticker">
    <div class="career-ticker-track">
      <?php for ($i = 0; $i < 4; $i++): ?>
      <div class="career-ticker-set" <?= $i > 0 ? 'aria-hidden="true"' : '' ?>>
        <div class="career-snap-card">
          <div class="csc-icon csc-blue"><i class="fas fa-user-tie"></i></div>
          <h3>Role</h3>
          <p>AI Educator / Trainer — school students (Class 3–12) and teacher training batches.</p>
        </div>

        <div class="career-snap-card">
          <div class="csc-icon csc-green"><i class="fas fa-shuffle"></i></div>
          <h3>Work Mode</h3>
          <p><strong class="text-green">Hybrid</strong> — on-campus sessions combined with online training, planning and mentoring.</p>
        </div>

        <div class="career-snap-card">
          <div class="csc-icon csc-yellow"><i class="fas fa-location-dot"></i></div>
          <h3>Locations</h3>
          <p>Across our project states — Madhya Pradesh, Jharkhand, Chhattisgarh, Rajasthan, UP, Uttarakhand and more.</p>
        </div>

        <div class="career-snap-card">
          <div class="csc-icon csc-red"><i class="fas fa-certificate"></i></div>
          <h3>Certification</h3>
          <p>Selected educators go through our L1 and L2 training and become certified SSD Prayas educators.</p>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</section>

<!-- ============ SECTION 3: GOOGLE CERTIFIED MASTER TRAINER CREDENTIALS ============ -->
<section class="section section-career-certifications">
  <div class="container">
    <div class="section-head is-center reveal">
      <div class="pill-badge pill-blue"><i class="fab fa-google"></i> GOOGLE CERTIFIED MASTER TRAINER</div>
      <h2>Verified <span class="text-blue">Google for Education</span> Accreditations</h2>
      <p class="sec-subtitle">SSD Prayas AI programmes and educator training modules are led by certified Google for Education trainers.</p>
    </div>

    <div class="career-certs-grid reveal">
      <!-- Certificate 1: Google Certified Educator Level 1 -->
      <div class="cert-flip-card" tabindex="0" role="button" aria-label="Google Certified Educator Level 1 certificate, hover or tap to flip">
        <div class="cert-flip-inner">
          <!-- Front Face -->
          <div class="cert-card-face cert-face-front">
            <div class="cert-card-stripe"></div>
            <div class="cert-card-body">
              <div class="cert-header">
                <div class="cert-badge-wrap bg-blue-subtle">
                  <i class="fab fa-google text-blue"></i>
                </div>
                <span class="cert-status-badge"><i class="fas fa-circle-check"></i> Verified</span>
              </div>
              <span class="cert-recipient"><i class="fas fa-user-graduate"></i> Apurv Kumar Persai</span>
              <h3 class="cert-title">Google Certified Educator Level 1</h3>
              <p class="cert-desc">Demonstrates foundational skills to integrate Google for Education tools and collaborative AI technologies in the classroom.</p>
              <div class="cert-footer">
                <span class="cert-issuer">Google for Education</span>
                <span class="btn-cert-flip-hint"><i class="fas fa-arrows-rotate"></i> Flip Certificate</span>
              </div>
            </div>
          </div>

          <!-- Back Face (High-Res Certificate Image) -->
          <div class="cert-card-face cert-face-back">
            <div class="cert-card-stripe"></div>
            <div class="cert-back-body">
              <div class="cert-back-top">
                <span class="cert-back-tag"><i class="fab fa-google text-blue"></i> Official Credential</span>
                <span class="cert-back-flip-hint"><i class="fas fa-rotate-left"></i> Flip back</span>
              </div>
              <div class="cert-img-frame">
                <img src="<?= e(asset_v('img/cert-google-educator-l1.png')) ?>" alt="Apurv Kumar Persai - Google Certified Educator Level 1 Certificate" class="cert-full-img">
              </div>
              <div class="cert-back-footer">
                <span class="cert-back-meta"><i class="fas fa-calendar-check"></i> Valid thru 2029</span>
                <span class="btn-cert-verify">
                  <span>Verify Online</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Certificate 2: Google Certified Educator Level 2 -->
      <div class="cert-flip-card" tabindex="0" role="button" aria-label="Google Certified Educator Level 2 certificate, hover or tap to flip">
        <div class="cert-flip-inner">
          <!-- Front Face -->
          <div class="cert-card-face cert-face-front">
            <div class="cert-card-stripe"></div>
            <div class="cert-card-body">
              <div class="cert-header">
                <div class="cert-badge-wrap bg-green-subtle">
                  <i class="fab fa-google text-green"></i>
                </div>
                <span class="cert-status-badge"><i class="fas fa-circle-check"></i> Verified</span>
              </div>
              <span class="cert-recipient"><i class="fas fa-user-graduate"></i> Apurv Kumar Persai</span>
              <h3 class="cert-title">Google Certified Educator Level 2</h3>
              <p class="cert-desc">Validates advanced expertise in pedagogical mastery, digital curriculum workflows, and leading transformative classroom experiences.</p>
              <div class="cert-footer">
                <span class="cert-issuer">Google for Education</span>
                <span class="btn-cert-flip-hint"><i class="fas fa-arrows-rotate"></i> Flip Certificate</span>
              </div>
            </div>
          </div>

          <!-- Back Face (High-Res Certificate Image) -->
          <div class="cert-card-face cert-face-back">
            <div class="cert-card-stripe"></div>
            <div class="cert-back-body">
              <div class="cert-back-top">
                <span class="cert-back-tag"><i class="fab fa-google text-green"></i> Official Credential</span>
                <span class="cert-back-flip-hint"><i class="fas fa-rotate-left"></i> Flip back</span>
              </div>
              <div class="cert-img-frame">
                <img src="<?= e(asset_v('img/cert-google-educator-l2.png')) ?>" alt="Apurv Kumar Persai - Google Certified Educator Level 2 Certificate" class="cert-full-img">
              </div>
              <div class="cert-back-footer">
                <span class="cert-back-meta"><i class="fas fa-calendar-check"></i> Valid thru 2029</span>
                <span class="btn-cert-verify">
                  <span>Verify Online</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Certificate 3: Gemini Certified Educator -->
      <div class="cert-flip-card" tabindex="0" role="button" aria-label="Gemini Certified Educator certificate, hover or tap to flip">
        <div class="cert-flip-inner">
          <!-- Front Face -->
          <div class="cert-card-face cert-face-front">
            <div class="cert-card-stripe"></div>
            <div class="cert-card-body">
              <div class="cert-header">
                <div class="cert-badge-wrap bg-purple-subtle">
                  <i class="fas fa-wand-magic-sparkles text-purple"></i>
                </div>
                <span class="cert-status-badge"><i class="fas fa-circle-check"></i> Verified</span>
              </div>
              <span class="cert-recipient"><i class="fas fa-user-graduate"></i> Apurv Kumar Persai</span>
              <h3 class="cert-title">Gemini Certified Educator</h3>
              <p class="cert-desc">Specialized accreditation in Google Gemini generative AI, prompt engineering for educators, and adaptive student AI tutoring.</p>
              <div class="cert-footer">
                <span class="cert-issuer">Google for Education</span>
                <span class="btn-cert-flip-hint"><i class="fas fa-arrows-rotate"></i> Flip Certificate</span>
              </div>
            </div>
          </div>

          <!-- Back Face (High-Res Certificate Image) -->
          <div class="cert-card-face cert-face-back">
            <div class="cert-card-stripe"></div>
            <div class="cert-back-body">
              <div class="cert-back-top">
                <span class="cert-back-tag"><i class="fas fa-wand-magic-sparkles text-purple"></i> Official Credential</span>
                <span class="cert-back-flip-hint"><i class="fas fa-rotate-left"></i> Flip back</span>
              </div>
              <div class="cert-img-frame">
                <img src="<?= e(asset_v('img/cert-gemini-educator.png')) ?>" alt="Apurv Kumar Persai - Gemini Certified Educator Certificate" class="cert-full-img">
              </div>
              <div class="cert-back-footer">
                <span class="cert-back-meta"><i class="fas fa-calendar-check"></i> Valid thru 2029</span>
                <span class="btn-cert-verify">
                  <span>Verify Online</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Certificate 4: Trainer Skills Assessment -->
      <div class="cert-flip-card" tabindex="0" role="button" aria-label="Trainer Skills Assessment certificate, hover or tap to flip">
        <div class="cert-flip-inner">
          <!-- Front Face -->
          <div class="cert-card-face cert-face-front">
            <div class="cert-card-stripe"></div>
            <div class="cert-card-body">
              <div class="cert-header">
                <div class="cert-badge-wrap bg-amber-subtle">
                  <i class="fas fa-chalkboard-user text-amber"></i>
                </div>
                <span class="cert-status-badge"><i class="fas fa-circle-check"></i> Verified</span>
              </div>
              <span class="cert-recipient"><i class="fas fa-user-graduate"></i> Apurv Kumar Persai</span>
              <h3 class="cert-title">Trainer Skills Assessment</h3>
              <p class="cert-desc">Certified competency in training adult educators, institutional workshop leadership, and designing school-level AI capability programmes.</p>
              <div class="cert-footer">
                <span class="cert-issuer">Google for Education</span>
                <span class="btn-cert-flip-hint"><i class="fas fa-arrows-rotate"></i> Flip Certificate</span>
              </div>
            </div>
          </div>

          <!-- Back Face (High-Res Certificate Image) -->
          <div class="cert-card-face cert-face-back">
            <div class="cert-card-stripe"></div>
            <div class="cert-back-body">
              <div class="cert-back-top">
                <span class="cert-back-tag"><i class="fas fa-chalkboard-user text-amber"></i> Official Credential</span>
                <span class="cert-back-flip-hint"><i class="fas fa-rotate-left"></i> Flip back</span>
              </div>
              <div class="cert-img-frame">
                <img src="<?= e(asset_v('img/cert-trainer-skills.png')) ?>" alt="Apurv Kumar Persai - Trainer Skills Assessment Certificate" class="cert-full-img">
              </div>
              <div class="cert-back-footer">
                <span class="cert-back-meta"><i class="fas fa-calendar-check"></i> Valid thru 2029</span>
                <span class="btn-cert-verify">
                  <span>Verify Online</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ============ SECTION 3: ROLE & REQUIREMENTS ============ -->
<section class="section section-career-details">
  <div class="container">
    <div class="career-split-grid reveal">
      
      <!-- Left: What You'll Do -->
      <div class="career-card-box ccb-role">
        <div class="pill-badge pill-blue"><i class="fas fa-list-check"></i> THE ROLE</div>
        <h2>What You’ll <span class="text-blue">Do</span></h2>
        
        <div class="career-timeline">
          <div class="ctl-item">
            <div class="ctl-icon"><i class="fas fa-laptop-code"></i></div>
            <div class="ctl-content">
              <h3>Deliver AI sessions</h3>
              <p>Run hands-on, project-based AI classes in partner school computer labs.</p>
            </div>
          </div>
          <div class="ctl-item">
            <div class="ctl-icon"><i class="fas fa-chalkboard-user"></i></div>
            <div class="ctl-content">
              <h3>Train fellow teachers</h3>
              <p>Support L1 and L2 educator batches so schools can sustain AI teaching themselves.</p>
            </div>
          </div>
          <div class="ctl-item">
            <div class="ctl-icon"><i class="fas fa-book-open"></i></div>
            <div class="ctl-content">
              <h3>Guide student projects</h3>
              <p>Mentor students through project work, exhibitions and portfolio building.</p>
            </div>
          </div>
          <div class="ctl-item">
            <div class="ctl-icon"><i class="fas fa-chart-simple"></i></div>
            <div class="ctl-content">
              <h3>Track and report</h3>
              <p>Maintain batch attendance, assessment records and certification readiness.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right: What We're Looking For -->
      <div class="career-card-box ccb-reqs">
        <div class="pill-badge pill-green"><i class="fas fa-user-check"></i> REQUIREMENTS</div>
        <h2>What We’re <span class="text-green">Looking For</span></h2>
        
        <ul class="career-checklist">
          <li>
            <span class="chk-icon"><i class="fas fa-circle-check"></i></span>
            <span>Graduate or postgraduate — B.Ed / B.Tech / MCA / M.Sc preferred</span>
          </li>
          <li>
            <span class="chk-icon"><i class="fas fa-circle-check"></i></span>
            <span>Teaching or training experience (school, coaching or corporate)</span>
          </li>
          <li>
            <span class="chk-icon"><i class="fas fa-circle-check"></i></span>
            <span>Comfort with computers; AI knowledge helpful but we will train you</span>
          </li>
          <li>
            <span class="chk-icon"><i class="fas fa-circle-check"></i></span>
            <span>Willingness to travel within the assigned district or state</span>
          </li>
          <li>
            <span class="chk-icon"><i class="fas fa-circle-check"></i></span>
            <span>Clear communication in Hindi and English</span>
          </li>
        </ul>

        <div class="career-fresher-alert">
          <div class="cfa-icon"><i class="fas fa-users"></i></div>
          <div class="cfa-text">
            Freshers with strong teaching ability are welcome — training and certification are provided by SSD Prayas.
          </div>
          <div class="cfa-leaf"><i class="fas fa-seedling"></i></div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- ============ SECTION 4: APPLICATION FORM & CARD ============ -->
<section class="section section-career-apply" id="apply">
  <div class="container">
    <div class="career-apply-grid">
      
      <!-- Left: Form -->
      <div class="career-form-card reveal">
        <div class="pill-badge pill-red"><i class="fas fa-paper-plane"></i> APPLICATION</div>
        <h2>Apply as an <span class="text-blue">Educator</span></h2>
        <p class="cfc-subtitle">Fill this in once. Your profile goes straight to our hiring team.</p>

        <?php if ($form_ok): ?>
          <div class="alert alert-success" style="margin-bottom: 20px;"><i class="fas fa-circle-check"></i><span><?= e($form_ok) ?></span></div>
        <?php endif; ?>
        <?php if ($form_err): ?>
          <div class="alert alert-error" style="margin-bottom: 20px;"><i class="fas fa-circle-exclamation"></i><span><?= e($form_err) ?></span></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" action="<?= e(url('careers')) ?>#apply" class="career-form">
          <?= csrf_field() ?>
          <input type="hidden" name="form" value="application">

          <div class="c-field-grid">
            <div class="c-field">
              <label for="a-name">Full Name <span class="req">*</span></label>
              <input type="text" id="a-name" name="full_name" required placeholder="Your full name" value="<?= e($_POST['full_name'] ?? '') ?>">
            </div>
            <div class="c-field">
              <label for="a-phone">Phone <span class="req">*</span></label>
              <input type="tel" id="a-phone" name="phone" required placeholder="10-digit mobile number" value="<?= e($_POST['phone'] ?? '') ?>">
            </div>

            <div class="c-field">
              <label for="a-email">Email <span class="req">*</span></label>
              <input type="email" id="a-email" name="email" required placeholder="you@example.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            <div class="c-field">
              <label for="a-position">Applying For</label>
              <select id="a-position" name="position">
                <option value="AI Educator" <?= (($_POST['position'] ?? '') === 'AI Educator' ? 'selected' : '') ?>>AI Educator</option>
                <option value="Senior AI Educator" <?= (($_POST['position'] ?? '') === 'Senior AI Educator' ? 'selected' : '') ?>>Senior AI Educator</option>
                <option value="Master Trainer" <?= (($_POST['position'] ?? '') === 'Master Trainer' ? 'selected' : '') ?>>Master Trainer</option>
                <option value="Programme Coordinator" <?= (($_POST['position'] ?? '') === 'Programme Coordinator' ? 'selected' : '') ?>>Programme Coordinator</option>
              </select>
            </div>

            <div class="c-field">
              <label for="a-state">Preferred State</label>
              <select id="a-state" name="state_id">
                <option value="">Select state</option>
                <?php foreach ($states as $st): ?>
                  <option value="<?= (int)$st['id'] ?>" <?= ((int)($_POST['state_id'] ?? 0) === (int)$st['id'] ? 'selected' : '') ?>><?= e($st['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="c-field">
              <label for="a-city">City / District</label>
              <input type="text" id="a-city" name="city" placeholder="e.g. Bhopal" value="<?= e($_POST['city'] ?? '') ?>">
            </div>

            <div class="c-field">
              <label for="a-qual">Highest Qualification</label>
              <input type="text" id="a-qual" name="qualification" placeholder="e.g. B.Ed, M.Sc Computer Science" value="<?= e($_POST['qualification'] ?? '') ?>">
            </div>
            <div class="c-field">
              <label for="a-exp">Teaching Experience (years)</label>
              <input type="number" id="a-exp" name="experience_years" min="0" max="50" value="<?= e($_POST['experience_years'] ?? '0') ?>">
            </div>

            <div class="c-field">
              <label for="a-subject">Subject Expertise</label>
              <input type="text" id="a-subject" name="subject_expertise" placeholder="e.g. Computer Science, Maths" value="<?= e($_POST['subject_expertise'] ?? '') ?>">
            </div>
            <div class="c-field">
              <label for="a-mode">Preferred Work Mode</label>
              <select id="a-mode" name="work_mode">
                <option value="hybrid" <?= (($_POST['work_mode'] ?? 'hybrid') === 'hybrid' ? 'selected' : '') ?>>Hybrid (recommended)</option>
                <option value="offline" <?= (($_POST['work_mode'] ?? '') === 'offline' ? 'selected' : '') ?>>Offline / On-campus</option>
                <option value="online" <?= (($_POST['work_mode'] ?? '') === 'online' ? 'selected' : '') ?>>Online</option>
              </select>
            </div>
          </div>

          <div class="c-field c-field-full" style="margin-top: 14px;">
            <label for="a-resume">Resume <span class="req">*</span></label>
            <div class="file-upload-wrap">
              <input type="file" id="a-resume" name="resume" required accept=".pdf,.doc,.docx" class="custom-file-input">
            </div>
            <p class="field-hint">PDF or Word file, maximum <?= (int)MAX_UPLOAD_MB ?> MB.</p>
          </div>

          <div class="c-field c-field-full" style="margin-top: 14px;">
            <label for="a-note">Why do you want to teach AI?</label>
            <textarea id="a-note" name="cover_note" rows="3" placeholder="A few lines about yourself and your teaching approach…"><?= e($_POST['cover_note'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn-career-submit">
            Submit Application <i class="fas fa-paper-plane"></i>
          </button>
        </form>
      </div>

      <!-- Right: Side Graphic Card -->
      <div class="career-side-wrap">
        <div class="career-side-card">
          <img src="<?= e(asset_v('img/career-side-card.jpg')) ?>" alt="Let's Build a Skilled Bharat Together - SSD Prayas" class="career-side-img">
        </div>
      </div>

    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
