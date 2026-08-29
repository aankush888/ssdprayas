<?php $home = ($page ?? 'home') === 'home' ? '' : url('/'); ?>

<footer class="site-footer" id="footer">
  <div class="container">
    <div class="footer-grid">

      <div class="footer-about">
        <a href="<?= e(url('/')) ?>" class="brand">
          <img class="brand-logo is-inverted" src="<?= e(asset(SITE_LOGO)) ?>"
               alt="<?= e(SITE_NAME) ?>" width="602" height="134">
        </a>
        <p>AI skilling for students, educators and professionals — delivered online and offline
           with schools, institutes and state governments across India.</p>
        <div class="socials">
          <a href="<?= e(SOCIAL_FACEBOOK) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="<?= e(SOCIAL_INSTAGRAM) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="<?= e(SOCIAL_LINKEDIN) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <a href="<?= e(SOCIAL_YOUTUBE) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
      </div>

      <div>
        <h4>Programmes</h4>
        <a href="<?= e($home) ?>#programmes">AI for Students (Class 3–12)</a>
        <a href="<?= e($home) ?>#programmes">AI for Educators</a>
        <a href="<?= e($home) ?>#programmes">AI for Professionals</a>
        <a href="<?= e($home) ?>#ai-for-school">AI for School Programme</a>
        <a href="<?= e($home) ?>#government">Government Projects</a>
      </div>

      <div>
        <h4>Company</h4>
        <a href="<?= e(url('about')) ?>">About Us</a>
        <a href="<?= e(url('about')) ?>#leadership">Leadership</a>
        <a href="<?= e(url('careers')) ?>">Careers</a>
        <a href="<?= e(url('blogs')) ?>">Blogs</a>
        <a href="<?= e($home) ?>#contact">Contact Us</a>
      </div>

      <div>
        <h4>Get in Touch</h4>
        <a href="tel:+<?= e(CONTACT_PHONE_RAW) ?>"><i class="fas fa-phone" style="width:18px"></i> <?= e(CONTACT_PHONE) ?></a>
        <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener"><i class="fab fa-whatsapp" style="width:18px"></i> WhatsApp (same number)</a>
        <a href="mailto:<?= e(SITE_EMAIL) ?>"><i class="fas fa-envelope" style="width:18px"></i> <?= e(SITE_EMAIL) ?></a>
        <a href="mailto:<?= e(SITE_EMAIL_ALT) ?>"><i class="fas fa-envelope" style="width:18px"></i> <?= e(SITE_EMAIL_ALT) ?></a>
        <a href="<?= e($home) ?>#contact"><i class="fas fa-location-dot" style="width:18px"></i> The DM Tower, Bhopal</a>
      </div>

    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> <?= e(SITE_NAME) ?>. All rights reserved.</p>
      <p>Website by <a href="<?= e(CREDIT_COPYRIGHT_URL) ?>" style="color:#fff;font-weight:600"><?= e(CREDIT_COPYRIGHT) ?></a></p>
    </div>
  </div>
</footer>

<a href="<?= e(whatsapp_link('Hello SSD Prayas, I would like to know more about your AI programmes.')) ?>"
   class="wa-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
  <i class="fab fa-whatsapp"></i>
</a>

<script src="<?= e(asset_v('js/main.js')) ?>"></script>
</body>
</html>
