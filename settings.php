<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();

$dayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
function fmt_hour(int $h): string {
    if ($h === 24) return '12 AM (midnight)';
    return (($h + 11) % 12 + 1) . ' ' . ($h >= 12 ? 'PM' : 'AM');
}
$capacity = count($user['study_days']) * max(0, $user['day_end_hour'] - $user['day_start_hour']);

$pageTitle = 'Settings — StudySync';
$pageDesc  = 'Choose your study days and daily study hours, or delete your StudySync account.';
$appLayout = true; $active = 'settings';
$scripts = ['settings.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="narrow stack-lg">
  <div>
    <h1 class="page-title">Settings</h1>
    <p class="muted">Your available study window shapes every generated timetable.</p>
  </div>

  <section class="surface card-pad stack-lg">
    <div class="field">
      <label>Study days</label>
      <div class="chips" id="day-chips">
        <?php foreach ($dayLabels as $i => $label): ?>
          <button type="button" class="chip<?= in_array($i, $user['study_days'], true) ? ' active' : '' ?>" data-day="<?= $i ?>"><?= $label ?></button>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="grid-2">
      <div class="field">
        <label for="start-hour">Day starts at</label>
        <select id="start-hour">
          <?php for ($h = 0; $h < 24; $h++): ?>
            <option value="<?= $h ?>"<?= $h === $user['day_start_hour'] ? ' selected' : '' ?>><?= e(fmt_hour($h)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="field">
        <label for="end-hour">Day ends at</label>
        <select id="end-hour">
          <?php for ($h = 1; $h <= 24; $h++): ?>
            <option value="<?= $h ?>"<?= $h === $user['day_end_hour'] ? ' selected' : '' ?>><?= e(fmt_hour($h)) ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <p class="muted" id="capacity">That gives you <?= $capacity ?> study slot<?= $capacity === 1 ? '' : 's' ?> each week.</p>
    <div><button class="btn" id="save-btn" type="button">Save settings</button></div>
  </section>

  <section class="surface card-pad stack">
    <h2>Delete account</h2>
    <p class="muted">This permanently removes your subjects, timetable and login. It cannot be undone.</p>
    <div><button class="btn btn-danger" id="delete-btn" type="button">Delete my account</button></div>
  </section>
</div>

<dialog id="confirm-dialog" class="dialog">
  <h2>Delete your StudySync account?</h2>
  <p class="muted">Everything you have saved — subjects, timetable and progress — will be erased.</p>
  <div class="dialog-actions">
    <button class="btn btn-outline" id="cancel-delete" type="button">Cancel</button>
    <button class="btn btn-danger" id="confirm-delete" type="button">Delete permanently</button>
  </div>
</dialog>
<?php require __DIR__ . '/includes/footer.php'; ?>
