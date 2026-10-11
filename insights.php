<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
$pdo = db();

$st = $pdo->prepare('SELECT id, name FROM subjects WHERE user_id = ? ORDER BY created_at, id');
$st->execute([$user['id']]);
$subjects = $st->fetchAll();

$st = $pdo->prepare('SELECT subject_id, day_of_week, is_completed FROM timetable_slots WHERE user_id = ?');
$st->execute([$user['id']]);
$slots = $st->fetchAll();

$total     = count($slots);
$completed = count(array_filter($slots, fn($s) => (int)$s['is_completed'] === 1));
$adherence = $total === 0 ? 0 : (int)round($completed / $total * 100);

$perSubject = [];
foreach ($subjects as $sub) {
    $mine = array_filter($slots, fn($s) => (int)$s['subject_id'] === (int)$sub['id']);
    $perSubject[] = [
        'name' => $sub['name'],
        'planned' => count($mine),
        'completed' => count(array_filter($mine, fn($s) => (int)$s['is_completed'] === 1)),
    ];
}
$dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
$perDay = [];
foreach ($dayLabels as $i => $label) {
    $perDay[] = ['label' => $label, 'count' => count(array_filter($slots, fn($s) => (int)$s['day_of_week'] === $i))];
}
$busiest = max(1, ...array_column($perDay, 'count'));
$maxBar  = max(1, ...array_merge([0], array_column($perSubject, 'planned')));

$statusMessage = 'Add subjects and generate a timetable to see your analytics.';
$statusBadge = 'Getting Started';
if ($total > 0) {
    if ($adherence >= 80) {
        $statusMessage = '🔥 Incredible dedication! You are consistently hitting your study goals.';
        $statusBadge = 'Top Performer';
    } elseif ($adherence >= 50) {
        $statusMessage = '💪 Good momentum! Keep marking sessions as you finish them.';
        $statusBadge = 'On Track';
    } else {
        $statusMessage = '🎯 Study blocks await! Complete your first sessions to boost adherence.';
        $statusBadge = 'In Progress';
    }
}

$pageTitle = 'Study Insights & Analytics — StudySync';
$pageDesc  = 'Track planned versus completed study hours, weekly adherence and busiest days.';
$appLayout = true; $active = 'insights';
require __DIR__ . '/includes/header.php';
?>
<div class="stack-lg">
  <div class="row-between">
    <div>
      <div class="badge-pill mb-xs">
        <span class="badge-dot"></span> Performance Tracking
      </div>
      <h1 class="page-title">Weekly Study Insights</h1>
      <p class="muted">Real-time comparison between planned sessions and actual study follow-through.</p>
    </div>
    <div class="row-gap">
      <a class="btn btn-outline btn-sm" href="<?= e(url('timetable.php')) ?>">View Timetable</a>
    </div>
  </div>

  <!-- Key Metrics Row -->
  <div class="grid-3">
    <div class="surface stat-card card-pad">
      <div class="stat-top">
        <span class="muted small font-medium">Sessions Planned</span>
        <span class="stat-icon-sm">&#x1F4CB;</span>
      </div>
      <p class="stat-val"><?= $total ?></p>
      <span class="muted small">Total study blocks scheduled</span>
    </div>

    <div class="surface stat-card card-pad">
      <div class="stat-top">
        <span class="muted small font-medium">Sessions Completed</span>
        <span class="stat-icon-sm">&#x2705;</span>
      </div>
      <p class="stat-val text-success"><?= $completed ?></p>
      <span class="muted small"><?= $total - $completed ?> remaining this week</span>
    </div>

    <div class="surface stat-card card-pad">
      <div class="stat-top">
        <span class="muted small font-medium">Weekly Adherence</span>
        <span class="badge-pill stat-badge-mini"><?= $statusBadge ?></span>
      </div>
      <p class="stat-val text-primary"><?= $adherence ?>%</p>
      <div class="mini-progress-track">
        <div class="mini-progress-fill" style="width: <?= $adherence ?>%"></div>
      </div>
    </div>
  </div>

  <!-- Motivational Banner -->
  <div class="surface card-pad status-insight-banner">
    <div class="status-insight-content">
      <span class="status-insight-icon">&#x1F4A1;</span>
      <div>
        <strong><?= e($statusBadge) ?>:</strong>
        <span class="muted"><?= e($statusMessage) ?></span>
      </div>
    </div>
  </div>

  <!-- Planned vs Completed Chart -->
  <section class="surface card-pad">
    <div class="row-between">
      <div>
        <h2>Planned vs. Completed Hours per Course</h2>
        <p class="muted small">Compare your study targets against completed hours for each subject.</p>
      </div>
      <div class="legend">
        <span class="legend-item"><span class="dot" style="background:var(--chart-1)"></span>Planned Target</span>
        <span class="legend-item"><span class="dot" style="background:var(--chart-2)"></span>Completed</span>
      </div>
    </div>

    <?php if (!$perSubject || $total === 0): ?>
      <div class="empty-box mt">
        <p class="muted">Add courses and generate a timetable to view this comparative bar chart.</p>
      </div>
    <?php else: ?>
      <div class="bars mt" role="img" aria-label="Bar chart of planned and completed hours per subject">
        <?php foreach ($perSubject as $row): ?>
          <div class="bar-group">
            <div class="bar-pair">
              <div class="bar planned" style="height:<?= round($row['planned'] / $maxBar * 100) ?>%" title="Planned: <?= (int)$row['planned'] ?>h">
                <span><?= (int)$row['planned'] ?>h</span>
              </div>
              <div class="bar done" style="height:<?= round($row['completed'] / $maxBar * 100) ?>%" title="Completed: <?= (int)$row['completed'] ?>h">
                <span><?= (int)$row['completed'] ?>h</span>
              </div>
            </div>
            <span class="bar-label" title="<?= e($row['name']) ?>"><?= e($row['name']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- Busiest Days Heatmap -->
  <section class="surface card-pad">
    <div class="row-between">
      <div>
        <h2>Daily Workload Heatmap</h2>
        <p class="muted small">See how your study sessions are distributed across the 7 days of the week.</p>
      </div>
      <span class="badge-pill muted">Anti-Clash Balanced</span>
    </div>

    <div class="heat mt">
      <?php foreach ($perDay as $d): $r = $d['count'] / $busiest; ?>
        <div class="heat-col">
          <div class="heat-cell" style="background:color-mix(in oklab, var(--primary) <?= round(12 + $r * 72) ?>%, var(--card));<?= $r > 0.5 ? 'color:var(--primary-foreground)' : '' ?>"
               title="<?= $d['count'] ?> session(s) scheduled for <?= e($d['label']) ?>">
            <span class="heat-num"><?= (int)$d['count'] ?></span>
            <span class="heat-unit">hrs</span>
          </div>
          <span class="muted small heat-label"><?= e($d['label']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
