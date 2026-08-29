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

<main>

<section class="page-hero">
  <div class="container">
    <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; Careers</p>
    <span class="eyebrow is-green"><i class="fas fa-user-plus"></i> We're Hiring Educators</span>
    <h1>Teach the Skill That <span class="grad-text">Changes Careers</span></h1>
    <p>SSD Prayas hires <strong>educators only</strong>. If you can hold a classroom and you are
       willing to learn AI properly, we will train you, certify you and put you in front of
       students who need you.</p>
    <div class="hero-actions" style="margin-bottom:0">
      <a href="#apply" class="btn btn-primary">Apply Now <i class="fas fa-arrow-right"></i></a>
      <a href="<?= e(whatsapp_link('Hello SSD Prayas, I want to apply as an educator.')) ?>"
         class="btn btn-ghost" target="_blank" rel="noopener">
        <i class="fab fa-whatsapp" style="color:#25D366"></i> Ask a Question
      </a>
    </div>
  </div>
</section>

<!-- Role snapshot -->
<section class="section">
  <div class="container">
    <div class="grid grid-4">
      <div class="card reveal">
        <div class="card-icon i-blue"><i class="fas fa-user-tie"></i></div>
        <h3>Role</h3>
        <p>AI Educator / Trainer — school students (Class 3–12) and teacher training batches.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon i-green"><i class="fas fa-shuffle"></i></div>
        <h3>Work Mode</h3>
        <p><strong style="color:var(--green)">Hybrid</strong> — on-campus sessions combined with
           online training, planning and mentoring.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon i-yellow"><i class="fas fa-location-dot"></i></div>
        <h3>Locations</h3>
        <p>Across our project states — Madhya Pradesh, Jharkhand, Chhattisgarh, Rajasthan, UP,
           Uttarakhand and more.</p>
      </div>
      <div class="card reveal">
        <div class="card-icon i-red"><i class="fas fa-certificate"></i></div>
        <h3>Certification</h3>
        <p>Selected educators go through our L1 and L2 training and become certified
           SSD Prayas educators.</p>
      </div>
    </div>
  </div>
</section>

<!-- What you'll do / need -->
<section class="section section-soft">
  <div class="container split" style="align-items:start">
    <div class="reveal">
      <span class="eyebrow"><i class="fas fa-list-check"></i> The Role</span>
      <h2>What You'll <span class="text-brand">Do</span></h2>
      <div class="timeline" style="margin-top:26px">
        <div class="timeline-item">
          <h3>Deliver AI sessions</h3>
          <p>Run hands-on, project-based AI classes in partner school computer labs.</p>
        </div>
        <div class="timeline-item">
          <h3>Train fellow teachers</h3>
          <p>Support L1 and L2 educator batches so schools can sustain AI teaching themselves.</p>
        </div>
        <div class="timeline-item">
          <h3>Guide student projects</h3>
          <p>Mentor students through project work, exhibitions and portfolio building.</p>
        </div>
        <div class="timeline-item">
          <h3>Track and report</h3>
          <p>Maintain batch attendance, assessment records and certification readiness.</p>
        </div>
      </div>
    </div>

    <div class="reveal">
      <span class="eyebrow is-green"><i class="fas fa-user-check"></i> Requirements</span>
      <h2>What We're <span class="text-brand">Looking For</span></h2>
      <ul class="tick-list" style="margin-top:26px">
        <li>Graduate or postgraduate — B.Ed / B.Tech / MCA / M.Sc preferred</li>
        <li>Teaching or training experience (school, coaching or corporate)</li>
        <li>Comfort with computers; AI knowledge helpful but we will train you</li>
        <li>Willingness to travel within the assigned district or state</li>
        <li>Clear communication in Hindi and English</li>
      </ul>
      <div class="alert alert-success" style="margin-top:26px">
        <i class="fas fa-circle-info"></i>
        <span>Freshers with strong teaching ability are welcome — training and certification
              are provided by SSD Prayas.</span>
      </div>
    </div>
  </div>
</section>

<!-- Application form -->
<section class="section" id="apply">
  <div class="container-sm">
    <div class="section-head is-center reveal" style="margin-bottom:36px">
      <span class="eyebrow is-red"><i class="fas fa-paper-plane"></i> Application</span>
      <h2>Apply as an <span class="text-brand">Educator</span></h2>
      <p>Fill this in once. Your profile goes straight to our hiring team.</p>
    </div>

    <div class="form-card reveal">
      <?php if ($form_ok): ?>
        <div class="alert alert-success"><i class="fas fa-circle-check"></i><span><?= e($form_ok) ?></span></div>
      <?php endif; ?>
      <?php if ($form_err): ?>
        <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><span><?= e($form_err) ?></span></div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data" action="<?= e(url('careers')) ?>#apply">
        <?= csrf_field() ?>
        <input type="hidden" name="form" value="application">

        <div class="field-row">
          <div class="field">
            <label for="a-name">Full Name <span class="req">*</span></label>
            <input type="text" id="a-name" name="full_name" required placeholder="Your full name">
          </div>
          <div class="field">
            <label for="a-phone">Phone <span class="req">*</span></label>
            <input type="tel" id="a-phone" name="phone" required placeholder="10-digit mobile number">
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="a-email">Email <span class="req">*</span></label>
            <input type="email" id="a-email" name="email" required placeholder="you@example.com">
          </div>
          <div class="field">
            <label for="a-position">Applying For</label>
            <select id="a-position" name="position">
              <option value="AI Educator">AI Educator</option>
              <option value="Senior AI Educator">Senior AI Educator</option>
              <option value="Master Trainer">Master Trainer</option>
              <option value="Programme Coordinator">Programme Coordinator</option>
            </select>
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="a-state">Preferred State</label>
            <select id="a-state" name="state_id">
              <option value="">Select state</option>
              <?php foreach ($states as $st): ?>
                <option value="<?= (int)$st['id'] ?>"><?= e($st['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="a-city">City / District</label>
            <input type="text" id="a-city" name="city" placeholder="e.g. Bhopal">
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="a-qual">Highest Qualification</label>
            <input type="text" id="a-qual" name="qualification" placeholder="e.g. B.Ed, M.Sc Computer Science">
          </div>
          <div class="field">
            <label for="a-exp">Teaching Experience (years)</label>
            <input type="number" id="a-exp" name="experience_years" min="0" max="50" value="0">
          </div>
        </div>

        <div class="field-row">
          <div class="field">
            <label for="a-subject">Subject Expertise</label>
            <input type="text" id="a-subject" name="subject_expertise" placeholder="e.g. Computer Science, Maths">
          </div>
          <div class="field">
            <label for="a-mode">Preferred Work Mode</label>
            <select id="a-mode" name="work_mode">
              <option value="hybrid" selected>Hybrid (recommended)</option>
              <option value="offline">Offline / On-campus</option>
              <option value="online">Online</option>
            </select>
          </div>
        </div>

        <div class="field">
          <label for="a-resume">Resume <span class="req">*</span></label>
          <input type="file" id="a-resume" name="resume" required accept=".pdf,.doc,.docx">
          <p class="field-hint">PDF or Word file, maximum <?= (int)MAX_UPLOAD_MB ?> MB.</p>
        </div>

        <div class="field">
          <label for="a-note">Why do you want to teach AI?</label>
          <textarea id="a-note" name="cover_note" placeholder="A few lines about yourself and your teaching approach…"></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
          Submit Application <i class="fas fa-paper-plane"></i>
        </button>
      </form>
    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
