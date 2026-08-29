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

        $allowed = ['student','educator','professional','school','government','partner','other'];
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

<main>

<section class="page-hero has-photo bg-contact">
  <div class="container">
    <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; Contact</p>
    <span class="eyebrow is-green"><i class="fas fa-comments"></i> Get In Touch</span>
    <h1>Let's Plan Your <span class="grad-text">AI Roll-out</span></h1>
    <p>Whether it is one school, a district programme or a state-level project — tell us what
       you need and our team will design the roll-out with you.</p>
  </div>
</section>

<section class="section">
  <div class="container split" style="align-items:start">

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
          <p>
            <a href="mailto:<?= e(SITE_EMAIL) ?>" style="color:var(--brand)"><?= e(SITE_EMAIL) ?></a><br>
            <a href="mailto:<?= e(SITE_EMAIL_ALT) ?>" style="color:var(--brand)"><?= e(SITE_EMAIL_ALT) ?></a>
          </p>
        </div>
      </div>
    </div>

    <div class="form-card reveal">
      <?php if ($form_ok): ?>
        <div class="alert alert-success" data-autohide><i class="fas fa-circle-check"></i><span><?= e($form_ok) ?></span></div>
      <?php endif; ?>
      <?php if ($form_err): ?>
        <div class="alert alert-error"><i class="fas fa-circle-exclamation"></i><span><?= e($form_err) ?></span></div>
      <?php endif; ?>

      <form method="POST" action="<?= e(url('contact')) ?>">
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

        <button type="submit" class="btn btn-primary btn-block">Send Enquiry <i class="fas fa-paper-plane"></i></button>
        <p class="field-hint" style="text-align:center;margin-top:14px">
          Your details stay with SSD Prayas. We never share them.
        </p>
      </form>
    </div>

  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
