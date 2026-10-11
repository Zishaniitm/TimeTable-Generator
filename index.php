<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'StudySync — Timetable & Study Planner Generator';
$pageDesc  = 'StudySync automatically balances your weekly study schedule across your available days. Drag to fine-tune, mark sessions complete with undo, and track academic insights.';
$signedIn  = (bool) current_user();
require __DIR__ . '/includes/header.php';
?>
<div class="landing">
  <!-- Aesthetic Landing Navigation -->
  <header class="container landing-head">
    <a href="<?= e(url('index.php')) ?>" class="brand">
      <span class="brand-spark">&#x2728;</span> Study<span>Sync</span>
    </a>
    
    <nav class="landing-nav" aria-label="Landing Navigation">
      <a href="#features" class="landing-link">Features</a>
      <a href="#demo" class="landing-link">Interactive Demo</a>
      <a href="#how-it-works" class="landing-link">How It Works</a>
      <a href="#faq" class="landing-link">FAQ</a>
    </nav>

    <div class="landing-actions">
      <button type="button" class="theme-toggle-btn btn btn-ghost btn-sm btn-icon" title="Toggle dark/light mode">
        <span class="theme-icon">&#x1F319;</span>
      </button>
      <?php if ($signedIn): ?>
        <a class="btn btn-sm" href="<?= e(url('dashboard.php')) ?>">Open Dashboard &rarr;</a>
      <?php else: ?>
        <a class="btn btn-ghost btn-sm" href="<?= e(url('auth.php')) ?>">Sign In</a>
        <a class="btn btn-sm" href="<?= e(url('auth.php?mode=signup')) ?>">Get Started Free</a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="hero-glow">
    <div class="container hero">
      <div class="hero-text-col">
        <div class="pill-badge">
          <span class="pill-spark">&#x1F393;</span>
          <span>Intelligent Round-Robin Planner &middot; 100% Free for Students</span>
        </div>
        
        <h1 class="hero-title">
          Study Smarter, Not Harder.<br>
          <span class="gradient-text">Balanced in One Click.</span>
        </h1>
        
        <p class="lead">
          Eliminate exam-week panic and chaotic cramming. Tell StudySync your subjects and weekly hours — our anti-burnout algorithm spreads them evenly across your week with fluid drag-and-drop and one-tap session tracking with instant undo.
        </p>

        <div class="actions">
          <a class="btn btn-lg btn-glow" href="<?= e(url($signedIn ? 'dashboard.php' : 'auth.php?mode=signup')) ?>">
            <span>&#x26A1;</span> <?= $signedIn ? 'Go to Dashboard' : 'Generate My Timetable Free' ?>
          </a>
          <a class="btn btn-lg btn-outline" href="#demo">
            <span>&#x1F440;</span> Try Live Preview
          </a>
        </div>

        <div class="hero-perks">
          <span class="perk-item">&#x2713; Zero Clashing Slots</span>
          <span class="perk-item">&#x2713; Fluid Drag &amp; Swap</span>
          <span class="perk-item">&#x2713; Mark Done &amp; 1-Tap Undo</span>
          <span class="perk-item">&#x2713; Pure Fast PHP 8</span>
        </div>
      </div>

      <!-- Live Interactive Demo Widget directly on Hero! -->
      <div class="hero-widget-col" id="demo">
        <div class="surface mock-app-window">
          <div class="mock-window-header">
            <div class="mock-window-dots">
              <span class="mock-dot red"></span>
              <span class="mock-dot yellow"></span>
              <span class="mock-dot green"></span>
            </div>
            <span class="mock-window-title">StudySync &mdash; Live Interactive Preview</span>
            <span class="badge-pill mini-badge">Interactive Demo</span>
          </div>

          <div class="mock-window-body">
            <!-- Simulated Tracker Header -->
            <div class="demo-tracker-row">
              <div>
                <span class="small font-medium">Weekly Adherence:</span>
                <span class="tracker-pct" id="demo-pct">75%</span>
                <span class="muted small">(<span id="demo-done-count">3</span> of 4 sessions completed)</span>
              </div>
              <span class="badge-tag" id="demo-undo-hint">&#x1F4A1; Click any slot to toggle done/undo</span>
            </div>
            <div class="progress-bar-wrap mb-sm">
              <div class="progress-bar-fill" id="demo-bar" style="width: 75%"></div>
            </div>

            <!-- Mini Grid -->
            <div class="mock-grid">
              <div class="mock-grid-head">
                <span class="mock-col-title">Time</span>
                <span class="mock-col-title">Monday</span>
                <span class="mock-col-title">Wednesday</span>
                <span class="mock-col-title">Friday</span>
              </div>

              <!-- Row 1 -->
              <div class="mock-grid-row">
                <span class="mock-time-label">4:00 PM</span>
                <div class="mock-slot done" data-sub="Data Structures" style="background: #2f7fb5">
                  <div class="mock-slot-content">
                    <span class="mock-sub-title">Data Structures</span>
                    <span class="mock-sub-hour">4:00 PM</span>
                  </div>
                  <button type="button" class="mock-check is-done" title="Completed! Click to undo">&#x2713;</button>
                </div>
                <div class="mock-slot done" data-sub="Machine Learning" style="background: #2fa08a">
                  <div class="mock-slot-content">
                    <span class="mock-sub-title">Machine Learning</span>
                    <span class="mock-sub-hour">4:00 PM</span>
                  </div>
                  <button type="button" class="mock-check is-done" title="Completed! Click to undo">&#x2713;</button>
                </div>
                <div class="mock-slot" data-sub="Web Architecture" style="background: #7d6ff0">
                  <div class="mock-slot-content">
                    <span class="mock-sub-title">Web Architecture</span>
                    <span class="mock-sub-hour">4:00 PM</span>
                  </div>
                  <button type="button" class="mock-check" title="Click to complete"><span class="circle-ring"></span></button>
                </div>
              </div>

              <!-- Row 2 -->
              <div class="mock-grid-row">
                <span class="mock-time-label">6:00 PM</span>
                <div class="mock-slot done" data-sub="Database Systems" style="background: #d98a3a">
                  <div class="mock-slot-content">
                    <span class="mock-sub-title">Database Systems</span>
                    <span class="mock-sub-hour">6:00 PM</span>
                  </div>
                  <button type="button" class="mock-check is-done" title="Completed! Click to undo">&#x2713;</button>
                </div>
                <div class="mock-cell-empty">
                  <span class="muted small">&#x2615; Break / Chill</span>
                </div>
                <div class="mock-slot" data-sub="Algorithms Lab" style="background: #5b8def">
                  <div class="mock-slot-content">
                    <span class="mock-sub-title">Algorithms Lab</span>
                    <span class="mock-sub-hour">6:00 PM</span>
                  </div>
                  <button type="button" class="mock-check" title="Click to complete"><span class="circle-ring"></span></button>
                </div>
              </div>
            </div>

            <div class="demo-footer-note">
              <span class="hint-icon">&#x21A9;</span>
              <span class="small muted"><strong>Try it:</strong> Click the sessions above! Watch them mark complete or undo immediately if you accidentally clicked.</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Value Metrics Bar -->
  <section class="container value-metrics-section">
    <div class="surface value-metrics-grid">
      <div class="metric-block">
        <span class="metric-num">1 Click</span>
        <span class="metric-label muted">Instant clash-free generation</span>
      </div>
      <div class="metric-block">
        <span class="metric-num">0 Clashes</span>
        <span class="metric-label muted">Strict conflict avoidance</span>
      </div>
      <div class="metric-block">
        <span class="metric-num">100% Free</span>
        <span class="metric-label muted">No ads, no subscriptions</span>
      </div>
      <div class="metric-block">
        <span class="metric-num">1-Tap Undo</span>
        <span class="metric-label muted">Never lose accurate tracking</span>
      </div>
    </div>
  </section>

  <!-- Aesthetic Feature Cards Grid -->
  <section class="container features-section" id="features">
    <div class="section-head text-center">
      <span class="badge-pill">Built for College Success</span>
      <h2 class="section-title">Everything you need to master your semester</h2>
      <p class="muted section-sub">Carefully designed features tailored to busy university students.</p>
    </div>

    <div class="features-grid mt-lg">
      <div class="surface feature-card">
        <div class="feature-icon-bubble" style="background: #e0f2fe; color: #0284c7;">&#x26A1;</div>
        <h3>Intelligent Round-Robin</h3>
        <p>Our scheduler spaces out intensive courses across the week so you study consistently rather than burning out during late-night cramming.</p>
      </div>

      <div class="surface feature-card">
        <div class="feature-icon-bubble" style="background: #fef3c7; color: #d97706;">&#x1F4C5;</div>
        <h3>Fluid Drag, Drop &amp; Swap</h3>
        <p>College plans change fast. Grab any study block and drop it to an empty slot or swap two sessions in one smooth gesture on mouse or touch.</p>
      </div>

      <div class="surface feature-card">
        <div class="feature-icon-bubble" style="background: #dcfce7; color: #16a34a;">&#x2705;</div>
        <h3>Mark Done with 1-Tap Undo</h3>
        <p>Tick off study blocks as you finish them. Clicked a session by accident? Click it again or hit Undo on the toast notification to restore immediately.</p>
      </div>

      <div class="surface feature-card">
        <div class="feature-icon-bubble" style="background: #f3e8ff; color: #9333ea;">&#x1F4CA;</div>
        <h3>Visual Adherence &amp; Heatmaps</h3>
        <p>See planned vs. completed study hours with beautiful bar charts and daily workload heatmaps to keep your motivation high.</p>
      </div>

      <div class="surface feature-card">
        <div class="feature-icon-bubble" style="background: #ffe4e6; color: #e11d48;">&#x23F0;</div>
        <h3>Custom Study Windows</h3>
        <p>Set your exact active study days and hours (e.g. 4 PM to 10 PM) to ensure your schedule fits around your lectures and lab timings.</p>
      </div>

      <div class="surface feature-card">
        <div class="feature-icon-bubble" style="background: #e2e8f0; color: #334155;">&#x1F319;</div>
        <h3>Eye-Friendly Dark Mode</h3>
        <p>Late night in the library? Switch between calm light mode and deep contrast dark mode anytime to protect your eyes.</p>
      </div>
    </div>
  </section>

  <!-- How It Works in 3 Steps -->
  <section class="container how-section" id="how-it-works">
    <div class="section-head text-center">
      <span class="badge-pill">Simple 3-Step Routine</span>
      <h2 class="section-title">How StudySync Works</h2>
      <p class="muted section-sub">Go from course list to an actionable study timetable in under 60 seconds.</p>
    </div>

    <div class="steps-grid mt-lg">
      <div class="surface step-card">
        <span class="step-num">01</span>
        <h3>Add Your Courses</h3>
        <p class="muted">Enter your course names, pick a color tag, and designate target hours needed per week.</p>
      </div>

      <div class="surface step-card">
        <span class="step-num">02</span>
        <h3>Generate in 1 Click</h3>
        <p class="muted">The round-robin algorithm calculates the least-loaded days and builds a balanced timetable.</p>
      </div>

      <div class="surface step-card">
        <span class="step-num">03</span>
        <h3>Track, Tick &amp; Undo</h3>
        <p class="muted">Follow your week, drag blocks if plans shift, mark sessions done, and undo anytime with zero friction.</p>
      </div>
    </div>
  </section>

  <!-- Student Testimonial / Viva Presentation Highlights -->
  <section class="container testimonials-section">
    <div class="surface testimonial-spotlight card-pad">
      <div class="row-between">
        <div class="spotlight-left">
          <span class="badge-pill">&#x1F393; Built by Students, For Students</span>
          <h2 class="mt-sm">"The only study planner that doesn't feel like a chore."</h2>
          <p class="muted mt-xs">
            "Most calendar apps make you manually plan every single hour from scratch. StudySync did all the heavy lifting in one click, and being able to undo accidental clicks on my phone is such a lifesaver!"
          </p>
          <div class="spotlight-author mt">
            <strong>Rohan Patel</strong>
            <span class="muted small">Computer Science &bull; 3rd Year College</span>
          </div>
        </div>
        <div class="spotlight-right">
          <div class="stat-highlight-box">
            <span class="stat-highlight-num">100%</span>
            <span class="stat-highlight-desc">Conflict-free algorithm</span>
          </div>
          <div class="stat-highlight-box mt-sm">
            <span class="stat-highlight-num">0ms</span>
            <span class="stat-highlight-desc">Zero bloat vanilla performance</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Interactive FAQ Accordion -->
  <section class="container faq-section" id="faq">
    <div class="section-head text-center">
      <span class="badge-pill">Got Questions?</span>
      <h2 class="section-title">Frequently Asked Questions</h2>
      <p class="muted section-sub">Clear answers to everything you need to know.</p>
    </div>

    <div class="faq-list mt-lg">
      <div class="surface faq-item">
        <button type="button" class="faq-question">
          <span>How does the round-robin algorithm prevent cramming?</span>
          <span class="faq-toggle-icon">+</span>
        </button>
        <div class="faq-answer">
          <p>Instead of clustering all your hours on one or two days, StudySync sorts subjects by weekly hours and distributes one hour at a time onto the least-loaded study day that does not already contain that subject. This guarantees balanced pacing throughout the week.</p>
        </div>
      </div>

      <div class="surface faq-item">
        <button type="button" class="faq-question">
          <span>What if I accidentally mark a study session as completed?</span>
          <span class="faq-toggle-icon">+</span>
        </button>
        <div class="faq-answer">
          <p>Don't worry! Every session can be toggled back to incomplete simply by clicking the session or its checkmark again. In addition, an interactive toast notification with an <strong>"Undo"</strong> button appears immediately after completion so you can revert it with one tap.</p>
        </div>
      </div>

      <div class="surface faq-item">
        <button type="button" class="faq-question">
          <span>Does drag-and-drop work on mobile phones and tablets?</span>
          <span class="faq-toggle-icon">+</span>
        </button>
        <div class="faq-answer">
          <p>Yes! StudySync uses the modern HTML5 Pointer Events API, which handles touch screens, trackpads, and mouse pointers equally well. You can drag and drop study blocks directly on your phone.</p>
        </div>
      </div>

      <div class="surface faq-item">
        <button type="button" class="faq-question">
          <span>What is the technology stack behind this project?</span>
          <span class="faq-toggle-icon">+</span>
        </button>
        <div class="faq-answer">
          <p>StudySync is built purely on <strong>PHP 8.0+ (PDO)</strong>, <strong>MySQL / MariaDB</strong>, semantic <strong>HTML5</strong>, modern <strong>CSS3</strong> (with Grid, Flexbox, custom properties, and OKLCH color science), and vanilla <strong>ES6 JavaScript</strong>. There is zero heavy framework bloat, making it blazing fast and reliable to host anywhere.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Final Call to Action -->
  <section class="container cta-banner-section">
    <div class="surface cta-banner card-pad text-center">
      <span class="badge-pill mb-xs">&#x2728; Free &bull; Fast &bull; Beautiful</span>
      <h2>Ready to take control of your study week?</h2>
      <p class="muted max-480 mx-auto mt-xs">
        Join students generating balanced, clash-free study routines in seconds.
      </p>
      <div class="actions justify-center mt">
        <a class="btn btn-lg btn-glow" href="<?= e(url($signedIn ? 'dashboard.php' : 'auth.php?mode=signup')) ?>">
          <?= $signedIn ? 'Open My Dashboard &rarr;' : 'Create Free Account &rarr;' ?>
        </a>
      </div>
    </div>
  </section>

  <!-- Landing Footer -->
  <footer class="landing-foot">
    <div class="container footer-content">
      <div class="row-between">
        <div class="footer-brand">
          <span class="brand"><span class="brand-spark">&#x2728;</span> Study<span>Sync</span></span>
          <p class="muted small mt-xs">Intelligent Timetable &amp; Study Planner Generator</p>
        </div>
        <div class="footer-meta text-right">
          <span class="badge-tag">Academic College Project</span>
          <p class="muted small mt-xs">Built with PHP 8 &bull; PDO MySQL &bull; Vanilla JS</p>
        </div>
      </div>
    </div>
  </footer>
</div>

<!-- Interactive Demo Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Demo interactive slot toggle & undo
  var mockSlots = document.querySelectorAll('.mock-slot');
  var demoPct = document.getElementById('demo-pct');
  var demoCount = document.getElementById('demo-done-count');
  var demoBar = document.getElementById('demo-bar');

  function updateDemoStats() {
    var total = mockSlots.length;
    var completed = document.querySelectorAll('.mock-slot.done').length;
    var pct = Math.round((completed / total) * 100);
    if (demoPct) demoPct.textContent = pct + '%';
    if (demoCount) demoCount.textContent = completed;
    if (demoBar) demoBar.style.width = pct + '%';
  }

  mockSlots.forEach(function (slot) {
    slot.addEventListener('click', function (e) {
      e.preventDefault();
      var isDone = slot.classList.contains('done');
      var check = slot.querySelector('.mock-check');
      var sub = slot.dataset.sub || 'Session';

      if (isDone) {
        // Undo
        slot.classList.remove('done');
        if (check) {
          check.classList.remove('is-done');
          check.innerHTML = '<span class="circle-ring"></span>';
          check.setAttribute('title', 'Click to complete');
        }
        toast(sub + ': Marked incomplete (reverted) ↩', 'info');
      } else {
        // Complete
        slot.classList.add('done');
        if (check) {
          check.classList.add('is-done');
          check.innerHTML = '&#x2713;';
          check.setAttribute('title', 'Completed! Click to undo');
        }
        toast(sub + ': Marked complete! 🎯', 'success', {
          text: 'Undo',
          onClick: function () {
            slot.classList.remove('done');
            if (check) {
              check.classList.remove('is-done');
              check.innerHTML = '<span class="circle-ring"></span>';
            }
            updateDemoStats();
            toast(sub + ': Reverted to incomplete ↩', 'info');
          }
        });
      }
      updateDemoStats();
    });
  });

  // FAQ Accordion
  document.querySelectorAll('.faq-question').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.faq-item');
      var isOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(function (i) { i.classList.remove('open'); });
      if (!isOpen) item.classList.add('open');
    });
  });
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
