<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'contact';
$page_title = 'Contact Us | ' . SITE_NAME;
$page_desc  = 'Talk to SSD Prayas about bringing AI education to your school, institution or team. Call, WhatsApp or send us an enquiry — we respond within 24 hours.';

$form_ok  = '';
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
        $state_id = ($_POST['state_id'] ?? '') !== '' ? (int)$_POST['state_id'] : null;
        $message  = trim($_POST['message'] ?? '');

        $allowed = ['school','student','educator','professional','government','partner','other'];
        if (!in_array($audience, $allowed, true)) {
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

$states = rows($pdo, "SELECT id, name FROM states WHERE is_active = 1 ORDER BY sort_order, name");

include __DIR__ . '/includes/header.php';
?>

<main class="page-contact">

  <!-- Section 1: Hero Banner -->
  <section class="contact-hero">
    <div class="container contact-hero-container">
      <div class="contact-hero-content">
        <p class="crumbs">
          <a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; <span>Contact</span>
        </p>

        <span class="pill-badge pill-green">
          <i class="fas fa-comments"></i> GET IN TOUCH
        </span>

        <h1 class="contact-hero-title">
          Let's Plan Your<br>
          <span class="text-blue">AI</span> <span class="text-green">Roll-out</span>
        </h1>

        <p class="contact-hero-desc">
          Whether it is one school, a district programme or a state-level project — tell us what you need and our team will design the roll-out with you.
        </p>

        <div class="contact-trust-row">
          <div class="contact-trust-item">
            <div class="cti-icon-blue">
              <i class="fas fa-users"></i>
            </div>
            <div class="cti-text">
              <strong>Collaborate</strong>
              <span>for Impact</span>
            </div>
          </div>

          <div class="contact-trust-item">
            <div class="cti-icon-blue">
              <i class="fas fa-headset"></i>
            </div>
            <div class="cti-text">
              <strong>Support</strong>
              <span>at Every Step</span>
            </div>
          </div>

          <div class="contact-trust-item">
            <div class="cti-icon-blue">
              <i class="fas fa-chart-line"></i>
            </div>
            <div class="cti-text">
              <strong>Stronger</strong>
              <span>Classrooms</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Sticky Note on top right matching design -->
    <div class="contact-sticky-note">
      <div class="csn-pin"><i class="fas fa-thumbtack"></i></div>
      <div class="csn-text">
        Partner<br>with us for<br>a brighter<br>tomorrow
      </div>
    </div>
  </section>

  <!-- Section 2: Split Contact Channels & Form -->
  <section class="section-contact-main">
    <div class="container">
      <div class="contact-split-grid">

        <!-- Left Column: Contact Channels -->
        <div class="contact-channels-col">
          <h2 class="contact-left-heading">
            We're Here <span class="text-blue">to Help</span>
          </h2>
          <p class="contact-left-desc">
            Reach out to us through any of the following channels. Our team is happy to assist you.
          </p>

          <div class="contact-cards-stack">
            <!-- Call Card -->
            <a class="contact-channel-card" href="tel:+<?= e(CONTACT_PHONE_RAW) ?>">
              <div class="ccc-icon-wrap icon-blue">
                <i class="fas fa-phone"></i>
              </div>
              <div class="ccc-info">
                <div class="ccc-title">Call Us</div>
                <div class="ccc-primary-text"><?= e(CONTACT_PHONE) ?></div>
                <div class="ccc-subtext">Mon – Sat, 10 AM – 7 PM</div>
              </div>
            </a>

            <!-- WhatsApp Card -->
            <a class="contact-channel-card" href="<?= e(whatsapp_link('Hello SSD Prayas! I want to enquire about AI programmes.')) ?>" target="_blank" rel="noopener">
              <div class="ccc-icon-wrap icon-green">
                <i class="fab fa-whatsapp"></i>
              </div>
              <div class="ccc-info">
                <div class="ccc-title">WhatsApp</div>
                <div class="ccc-primary-text"><?= e(CONTACT_PHONE) ?></div>
                <div class="ccc-subtext">Same number — chat anytime</div>
              </div>
            </a>

            <!-- Visit Us Card -->
            <div class="contact-channel-card">
              <div class="ccc-icon-wrap icon-amber">
                <i class="fas fa-location-dot"></i>
              </div>
              <div class="ccc-info">
                <div class="ccc-title">Visit Us</div>
                <div class="ccc-primary-text" style="font-size:13.5px;line-height:1.45;font-weight:600">
                  <?= e(CONTACT_ADDRESS) ?>
                </div>
                <a href="https://maps.google.com/?q=The+DM+Tower+Kolar+Road+Bhopal" target="_blank" rel="noopener" class="btn-view-maps">
                  View on Maps <span>→</span>
                </a>
              </div>
            </div>

            <!-- Email Us Card -->
            <div class="contact-channel-card">
              <div class="ccc-icon-wrap icon-purple">
                <i class="fas fa-envelope"></i>
              </div>
              <div class="ccc-info">
                <div class="ccc-title">Email Us</div>
                <div class="ccc-primary-text">
                  <a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a>
                </div>
                <div class="ccc-primary-text">
                  <a href="mailto:<?= e(SITE_EMAIL_ALT) ?>"><?= e(SITE_EMAIL_ALT) ?></a>
                </div>
              </div>
            </div>

            <!-- Green Accent Quote Card -->
            <div class="contact-quote-card">
              <div class="cqc-icon">
                <i class="fas fa-handshake"></i>
              </div>
              <p class="cqc-text">
                &ldquo;Building AI-ready classrooms across India, one partnership at a time.&rdquo;
              </p>
            </div>
          </div>
        </div>

        <!-- Right Column: Enquiry Form Card -->
        <div class="contact-form-card">
          <div class="cfc-header">
            <div class="cfc-icon-badge">
              <i class="fas fa-paper-plane"></i>
            </div>
            <h2 class="cfc-title">
              Send Us an <span class="text-blue">Enquiry</span>
            </h2>
            <p class="cfc-desc">
              Share your details and requirements. We'll get back to you soon.
            </p>
          </div>

          <?php if ($form_ok): ?>
            <div class="alert alert-success" data-autohide style="margin-bottom:20px;">
              <i class="fas fa-circle-check"></i>
              <span><?= e($form_ok) ?></span>
            </div>
          <?php endif; ?>
          <?php if ($form_err): ?>
            <div class="alert alert-error" style="margin-bottom:20px;">
              <i class="fas fa-circle-exclamation"></i>
              <span><?= e($form_err) ?></span>
            </div>
          <?php endif; ?>

          <form method="POST" action="<?= e(url('contact')) ?>" class="cfc-form">
            <?= csrf_field() ?>
            <input type="hidden" name="form" value="enquiry">

            <div class="cfc-grid">
              <div class="cfc-field">
                <label for="f-name" class="cfc-label">Your Name <span class="req">*</span></label>
                <input type="text" id="f-name" name="name" required class="cfc-input" placeholder="Enter your full name">
              </div>

              <div class="cfc-field">
                <label for="f-phone" class="cfc-label">Phone <span class="req">*</span></label>
                <input type="tel" id="f-phone" name="phone" required class="cfc-input" placeholder="10-digit mobile number">
              </div>

              <div class="cfc-field">
                <label for="f-email" class="cfc-label">Email <span class="req">*</span></label>
                <input type="email" id="f-email" name="email" required class="cfc-input" placeholder="you@exemple.com">
              </div>

              <div class="cfc-field">
                <label for="f-org" class="cfc-label">School / Organisation</label>
                <input type="text" id="f-org" name="organisation" class="cfc-input" placeholder="Institution name">
              </div>

              <div class="cfc-field">
                <label for="f-audience" class="cfc-label">I am enquiring as</label>
                <select id="f-audience" name="audience" class="cfc-select">
                  <option value="school">A School</option>
                  <option value="student">A Student / Parent</option>
                  <option value="educator">An Educator</option>
                  <option value="professional">A Professional</option>
                  <option value="government">Government Department</option>
                  <option value="partner">Prospective Partner</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="cfc-field">
                <label for="f-state" class="cfc-label">State</label>
                <select id="f-state" name="state_id" class="cfc-select">
                  <option value="">Select state</option>
                  <?php foreach ($states as $st): ?>
                    <option value="<?= (int)$st['id'] ?>"><?= e($st['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="cfc-field cfc-field-full">
                <label for="f-msg" class="cfc-label">Your Requirement</label>
                <textarea id="f-msg" name="message" class="cfc-textarea" placeholder="Tell us about your school, batch size, classes, timeline, or any specific requirement..."></textarea>
              </div>
            </div>

            <button type="submit" class="btn-contact-submit">
              Send Enquiry <i class="fas fa-paper-plane" style="font-size:13px"></i>
            </button>

            <div class="cfc-privacy-hint">
              <i class="fas fa-lock"></i>
              <span>Your details stay with SSD Prayas. We never share them.</span>
            </div>
          </form>

          <!-- Bottom Artwork / Calligraphy -->
          <div class="contact-art-together">
            <i class="fas fa-paper-plane cat-plane"></i>
            <div class="cat-text">
              Together<br>for a<br><strong style="color:#2563eb;font-size:15px">Smarter India</strong>
            </div>
            <svg class="cat-swash" viewBox="0 0 100 24" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M5 18C30 5 70 20 95 6" stroke="#FF9933" stroke-width="3" stroke-linecap="round"/>
              <path d="M15 20C40 7 75 22 95 10" stroke="#138808" stroke-width="3" stroke-linecap="round"/>
            </svg>
          </div>
        </div>

      </div>
    </div>
  </section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
