<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
$pdo = db();

$st = $pdo->prepare('SELECT id, name, color FROM subjects WHERE user_id = ? ORDER BY created_at, id');
$st->execute([$user['id']]);
$subjects = $st->fetchAll();

$st = $pdo->prepare('SELECT id, subject_id, day_of_week, start_hour, is_completed FROM timetable_slots WHERE user_id = ? ORDER BY day_of_week, start_hour');
$st->execute([$user['id']]);
$slots = array_map(fn($r) => [
    'id' => (int)$r['id'], 'subject_id' => (int)$r['subject_id'],
    'day_of_week' => (int)$r['day_of_week'], 'start_hour' => (int)$r['start_hour'],
    'is_completed' => (bool)$r['is_completed'],
], $st->fetchAll());

$totalSlots = count($slots);
$completedSlots = count(array_filter($slots, fn($s) => $s['is_completed']));
$adherencePct = $totalSlots > 0 ? (int)round(($completedSlots / $totalSlots) * 100) : 0;

$pageTitle = 'Weekly Timetable — StudySync';
$pageDesc  = 'Your balanced weekly study timetable. Drag blocks to customize, tap to mark done or undo anytime.';
$appLayout = true; $active = 'timetable';
$scripts = ['timetable.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="stack-lg">
  <div class="row-between timetable-header-row">
    <div>
      <div class="badge-pill mb-xs">
        <span class="badge-dot"></span> Interactive Weekly Grid
      </div>
      <h1 class="page-title">Weekly Study Timetable</h1>
      <p class="muted">Click any session to mark it complete (or click again to undo). Drag blocks to reschedule.</p>
    </div>
    <div class="row-gap">
      <a href="<?= e(url('dashboard.php')) ?>" class="btn btn-outline btn-sm">Manage subjects</a>
      <button class="btn btn-sm" id="regen-btn" type="button">
        <span>&#x21BB;</span> Regenerate timetable
      </button>
    </div>
  </div>

  <!-- Weekly Adherence Quick Tracker -->
  <div id="tt-stats" class="surface tracker-banner" <?= $totalSlots === 0 ? 'hidden' : '' ?>>
    <div class="tracker-info">
      <div>
        <span class="tracker-title">Weekly Progress</span>
        <span class="tracker-sub muted">
          <strong id="tt-done"><?= $completedSlots ?></strong> of <strong id="tt-total"><?= $totalSlots ?></strong> sessions completed
        </span>
      </div>
      <span class="tracker-pct" id="tt-pct"><?= $adherencePct ?>%</span>
    </div>
    <div class="progress-bar-wrap">
      <div class="progress-bar-fill" id="tt-progress-bar" style="width: <?= $adherencePct ?>%"></div>
    </div>
    <div class="tracker-hint">
      <span class="hint-icon">&#x1F4A1;</span>
      <span>Accidentally completed a block? Just click it again to undo, or click the toast undo button!</span>
    </div>
  </div>

  <div id="empty" class="surface empty-box card-pad" hidden>
    <div class="empty-icon">&#x1F4DA;</div>
    <h2>No timetable generated yet</h2>
    <p class="muted max-360">Add your courses and study hours in the dashboard, then click Generate to create your clash-free weekly schedule.</p>
    <div class="mt">
      <a href="<?= e(url('dashboard.php')) ?>" class="btn">Go to Dashboard</a>
    </div>
  </div>

  <div id="grid-wrap" class="surface grid-wrap" hidden>
    <div id="grid" class="tt-grid"></div>
  </div>

  <div class="surface card-pad legend-card">
    <div class="legend-header">
      <span class="legend-title">Subject Tags</span>
      <span class="muted small">Color-coded for clarity</span>
    </div>
    <div id="legend" class="legend mt-sm"></div>
  </div>
</div>

<script>
  window.STUDYSYNC = <?= json_encode([
      'subjects' => $subjects,
      'slots'    => $slots,
      'days'     => $user['study_days'] ?: [0,1,2,3,4,5,6],
      'start'    => $user['day_start_hour'],
      'end'      => $user['day_end_hour'],
  ]) ?>;
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
