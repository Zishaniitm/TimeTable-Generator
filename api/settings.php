<?php
require_once __DIR__ . '/../includes/api.php';
$user = api_user();
$body = api_post();
$action = $body['action'] ?? '';

if ($action === 'save') {
    $days = array_values(array_unique(array_filter(
        array_map('intval', (array)($body['study_days'] ?? [])), fn($d) => $d >= 0 && $d <= 6
    )));
    sort($days);
    $start = (int)($body['day_start_hour'] ?? -1);
    $end   = (int)($body['day_end_hour'] ?? -1);
    if (!$days) json_out(['error' => 'Pick at least one study day.'], 422);
    if ($start < 0 || $start > 23 || $end < 1 || $end > 24 || $end <= $start) {
        json_out(['error' => 'The end time must be after the start time.'], 422);
    }
    $st = db()->prepare('UPDATE users SET study_days = ?, day_start_hour = ?, day_end_hour = ? WHERE id = ?');
    $st->execute([implode(',', $days), $start, $end, $user['id']]);
    json_out(['ok' => true]);
}

if ($action === 'delete_account') {
    // Subjects and slots are removed by ON DELETE CASCADE.
    db()->prepare('DELETE FROM users WHERE id = ?')->execute([$user['id']]);
    $_SESSION = [];
    session_destroy();
    json_out(['ok' => true]);
}

json_out(['error' => 'Unknown action'], 400);
