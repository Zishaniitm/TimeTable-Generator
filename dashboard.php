<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/scheduler.php';
$user = require_login();

$st = db()->prepare('SELECT id, name, color, hours_per_week FROM subjects WHERE user_id = ? ORDER BY created_at, id');
$st->execute([$user['id']]);
$subjects = $st->fetchAll();

$st = db()->prepare('SELECT COUNT(*) FROM timetable_slots WHERE user_id = ?');
$st->execute([$user['id']]);
$slotCount = (int)$st->fetchColumn();

$st = db()->prepare('SELECT COUNT(*) FROM timetable_slots WHERE user_id = ? AND is_completed = 1');
$st->execute([$user['id']]);
$completedCount = (int)$st->fetchColumn();

$totalHours = array_sum(array_column($subjects, 'hours_per_week'));
$capacity   = count($user['study_days']) * max(0, $user['day_end_hour'] - $user['day_start_hour']);

// Fetch today's study sessions
$todayDow = (int)date('w'); // 0 = Sun .. 6 = Sat
$dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$todayName = $dayNames[$todayDow];

$st = db()->prepare('
    SELECT s.id, s.subject_id, s.start_hour, s.is_completed, sub.name, sub.color
    FROM timetable_slots s
    JOIN subjects sub ON s.subject_id = sub.id
    WHERE s.user_id = ? AND s.day_of_week = ?
    ORDER BY s.start_hour ASC
');
$st->execute([$user['id'], $todayDow]);
$todaySlots = $st->fetchAll();

$pageTitle = 'Student Dashboard — StudySync';
$pageDesc  = 'Manage your subjects, plan your weekly hours, and track today\'s study sessions.';
$appLayout = true; $active = 'dashboard';
$scripts = ['dashboard.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="stack-lg">
  <!-- Welcome Banner -->
  <div class="surface hero-banner card-pad">
    <div class="row-between">
      <div>
        <div class="badge-pill mb-xs">
          <span class="badge-dot"></span> Academic Planner
        </div>
        <h1 class="banner-title">Welcome back, <?= e($user['display_name'] ?: 'Student') ?>! &#x1F393;</h1>
        <p class="muted banner-lead">Keep your weekly study workload balanced and hit your academic milestones.</p>
      </div>
      <div class="row-gap banner-actions">
        <button class="btn" id="generate-btn" type="button">
          <span>&#x26A1;</span> Generate timetable
        </button>
        <a class="btn btn-outline" href="<?= e(url('timetable.php')) ?>">Open timetable</a>
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid-4 stats-grid mt">
      <div class="stat-pill">
        <span class="stat-icon">&#x1F4DA;</span>
        <div>
          <span class="stat-num"><?= count($subjects) ?></span>
          <span class="stat-label muted">Courses</span>
        </div>
      </div>
      <div class="stat-pill">
        <span class="stat-icon">&#x23F1;&#xFE0F;</span>
        <div>
          <span class="stat-num" id="sum-hours"><?= (int)$totalHours ?></span>
          <span class="stat-label muted">Hours / Week</span>
        </div>
      </div>
      <div class="stat-pill">
        <span class="stat-icon">&#x1F5D3;&#xFE0F;</span>
        <div>
          <span class="stat-num"><?= $slotCount ?></span>
          <span class="stat-label muted">Total Scheduled</span>
        </div>
      </div>
      <div class="stat-pill">
        <span class="stat-icon">&#x2705;</span>
        <div>
          <span class="stat-num"><?= $completedCount ?></span>
          <span class="stat-label muted">Completed</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Today's Study Sessions (Actionable with Undo!) -->
  <?php if ($slotCount > 0): ?>
  <section class="surface card-pad today-section">
    <div class="row-between">
      <div>
        <div class="row-gap align-center">
          <h2>Today's Sessions &#x1F4C5;</h2>
          <span class="badge-pill"><?= $todayName ?></span>
        </div>
        <p class="muted small mt-xs">Check off sessions as you finish them. Click again anytime to undo!</p>
      </div>
      <a href="<?= e(url('timetable.php')) ?>" class="btn btn-ghost btn-sm">Full timetable &rarr;</a>
    </div>

    <?php if ($todaySlots): ?>
      <div class="today-slots-grid mt" id="today-slots-list">
        <?php foreach ($todaySlots as $ts): 
          $h = (int)$ts['start_hour'];
          $timeStr = ((($h + 11) % 12) + 1) . ':00 ' . ($h >= 12 ? 'PM' : 'AM');
          $done = (bool)$ts['is_completed'];
        ?>
          <div class="today-slot-item surface<?= $done ? ' is-completed' : '' ?>" data-id="<?= (int)$ts['id'] ?>">
            <div class="slot-color-bar" style="background: <?= e($ts['color']) ?>"></div>
            <div class="today-slot-details">
              <span class="today-time muted small"><?= e($timeStr) ?></span>
              <strong class="today-subject-name"><?= e($ts['name']) ?></strong>
            </div>
            <button type="button" class="today-check-btn<?= $done ? ' is-done' : '' ?>" 
                    title="<?= $done ? 'Completed! Click to undo' : 'Mark as done' ?>">
              <?= $done ? '&#x2713;' : '<span class="circle-ring"></span>' ?>
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="today-empty mt">
        <p class="muted">&#x1F389; No study sessions scheduled for today (<?= $todayName ?>). Rest up or review your notes!</p>
      </div>
    <?php endif; ?>
  </section>
  <?php endif; ?>

  <!-- Main Management: Add Subject & Subject List -->
  <div class="grid-dash">
    <!-- Add Subject Card -->
    <section class="surface card-pad">
      <div class="card-header-styled">
        <span class="section-icon">&#x2795;</span>
        <div>
          <h2>Add Course / Subject</h2>
          <p class="muted small">Define how many hours you want to study each week.</p>
        </div>
      </div>

      <form id="add-form" class="stack mt">
        <div class="field">
          <label for="subject-name">Subject / Course Name</label>
          <input id="subject-name" name="name" maxlength="100" required placeholder="e.g. Data Structures &amp; Algorithms">
        </div>
        <div class="field">
          <label for="subject-hours">Weekly Target Hours</label>
          <input id="subject-hours" name="hours" type="number" min="1" max="40" value="3">
          <span class="muted small">Our algorithm will balance these hours evenly across your week.</span>
        </div>
        <div class="field">
          <label>Color Theme Tag</label>
          <div class="swatches" id="swatches">
            <?php foreach (SUBJECT_COLORS as $i => $c): ?>
              <button type="button" class="swatch<?= $i === 0 ? ' selected' : '' ?>" data-color="<?= e($c) ?>"
                      aria-label="Color <?= e($c) ?>" style="background:<?= e($c) ?>"></button>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="pt-xs">
          <button type="submit" class="btn btn-block">Add Course</button>
        </div>
      </form>
    </section>

    <!-- My Subjects List -->
    <section class="surface card-pad">
      <div class="row-between">
        <div class="card-header-styled">
          <span class="section-icon">&#x1F4D6;</span>
          <div>
            <h2>My Enrolled Courses</h2>
            <p class="muted small">Courses included in timetable generation.</p>
          </div>
        </div>
        <span class="badge-pill muted" id="capacity-badge">
          <?= (int)$capacity ?> max slots
        </span>
      </div>

      <ul class="subject-list mt" id="subject-list">
        <?php if (!$subjects): ?>
          <li class="empty">No courses added yet. Add your first subject on the left to start planning!</li>
        <?php endif; ?>
        <?php foreach ($subjects as $s): ?>
          <li class="subject-item" data-id="<?= (int)$s['id'] ?>" data-hours="<?= (int)$s['hours_per_week'] ?>">
            <span class="dot" style="background:<?= e($s['color']) ?>"></span>
            <span class="subject-name"><?= e($s['name']) ?></span>
            <span class="badge-tag muted small push"><?= (int)$s['hours_per_week'] ?> h/week</span>
            <button type="button" class="btn btn-ghost btn-icon del-btn" aria-label="Delete <?= e($s['name']) ?>" title="Remove course">&#x1F5D1;</button>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>
  </div>
</div>

<script>
  window.STUDYSYNC = {
    capacity: <?= (int)$capacity ?>,
    scheduled: <?= $slotCount ?>,
    colors: <?= json_encode(SUBJECT_COLORS) ?>
  };
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
