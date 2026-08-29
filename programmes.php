<?php
require_once __DIR__ . '/includes/functions.php';

$page       = 'programmes';
$page_title = 'Programmes | ' . SITE_NAME;
$page_desc  = 'SSD Prayas AI programmes: a grade-wise curriculum for Class 3 to 12, L1 and L2 educator training, and applied AI upskilling for working professionals.';

include __DIR__ . '/includes/header.php';
?>

<main>

<section class="page-hero has-photo bg-prog">
  <div class="container">
    <p class="crumbs"><a href="<?= e(url('/')) ?>">Home</a> &nbsp;/&nbsp; Programmes</p>
    <span class="eyebrow"><i class="fas fa-route"></i> Our Programmes</span>
    <h1>The SSD Prayas <span class="grad-text">Learning Journey</span></h1>
    <p>AI is not one course taught once. Our curriculum grows with the learner — from a Class 3
       child meeting a computer, to a Class 12 student building real AI projects, to a teacher
       who can carry the whole programme forward.</p>
    <div class="hero-actions">
      <a href="<?= e(url('contact')) ?>" class="btn btn-primary">Enrol Your School <i class="fas fa-arrow-right"></i></a>
      <a href="<?= e(url('ai-for-school')) ?>" class="btn btn-ghost">AI for School Programme</a>
    </div>
  </div>
</section>

<!-- Grade journey -->
<section class="prog">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow"><i class="fas fa-graduation-cap"></i> For Students</span>
      <h2>Class 3 to 12, <span class="grad-text">Grade by Grade</span></h2>
      <p>Each stage builds on the last. No child is thrown into machine learning on day one.</p>
    </div>

    <div class="journey">
      <article class="jstep reveal">
        <div class="jstep-dot"><i class="fas fa-shapes"></i></div>
        <span class="jstep-grade">Class 3–5</span>
        <h3>Digital Foundations</h3>
        <p>Computer confidence, safe internet habits and a first, playful introduction to what
           artificial intelligence actually is.</p>
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
          <a href="<?= e(url('contact')) ?>" class="link-arrow">Talk to an advisor <span>→</span></a>
        </div>
      </article>
    </div>
  </div>
</section>

<!-- Delivery mode -->
<section class="section section-soft" id="mode">
  <div class="container">
    <div class="section-head is-center reveal">
      <span class="eyebrow is-yellow"><i class="fas fa-tower-broadcast"></i> Delivery Mode</span>
      <h2>Learn <span class="text-brand">Online</span>, <span class="text-brand">Offline</span> or Both</h2>
      <p>Geography should never decide who gets AI education. The same curriculum is delivered
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

<!-- CTA -->
<section class="section-tight" style="padding-bottom:90px">
  <div class="container">
    <div class="cta-band reveal">
      <div>
        <h2>Not sure which programme fits?</h2>
        <p>Tell us the classes, the batch size and your timeline. We will map the right
           curriculum and delivery mode for you.</p>
      </div>
      <div class="cta-actions">
        <a href="<?= e(url('contact')) ?>" class="btn btn-yellow">Talk to Our Team <i class="fas fa-arrow-right"></i></a>
        <a href="tel:+<?= e(CONTACT_PHONE_RAW) ?>" class="btn btn-light"><i class="fas fa-phone"></i> Call Now</a>
      </div>
    </div>
  </div>
</section>

</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
