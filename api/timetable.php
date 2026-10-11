<?php
require_once __DIR__ . '/../includes/api.php';
$user = api_user();
$pdo = db();

function fetch_slots(PDO $pdo, int $uid): array {
    $st = $pdo->prepare('SELECT id, subject_id, day_of_week, start_hour, is_completed FROM timetable_slots WHERE user_id = ? ORDER BY day_of_week, start_hour');
    $st->execute([$uid]);
    return array_map(fn($r) => [
        'id' => (int)$r['id'], 'subject_id' => (int)$r['subject_id'],
        'day_of_week' => (int)$r['day_of_week'], 'start_hour' => (int)$r['start_hour'],
        'is_completed' => (bool)$r['is_completed'],
    ], $st->fetchAll());
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_out(['slots' => fetch_slots($pdo, $user['id'])]);
}

$body = api_post();
$action = $body['action'] ?? '';

if ($action === 'generate') {
    $st = $pdo->prepare('SELECT id, name, hours_per_week FROM subjects WHERE user_id = ?');
    $st->execute([$user['id']]);
    $subjects = $st->fetchAll();
    if (!$subjects) json_out(['error' => 'Add at least one subject first.'], 422);

    $result = generate_timetable($subjects, $user['study_days'], $user['day_start_hour'], $user['day_end_hour']);

    $pdo->beginTransaction();
    try {
        $pdo->prepare('DELETE FROM timetable_slots WHERE user_id = ?')->execute([$user['id']]);
        $ins = $pdo->prepare('INSERT INTO timetable_slots (user_id, subject_id, day_of_week, start_hour) VALUES (?, ?, ?, ?)');
        foreach ($result['slots'] as $s) {
            $ins->execute([$user['id'], $s['subject_id'], $s['day_of_week'], $s['start_hour']]);
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        $pdo->rollBack();
        json_out(['error' => 'Could not generate the timetable.'], 500);
    }
    json_out(['ok' => true, 'placed' => count($result['slots']), 'unplaced' => $result['unplaced']]);
}

if ($action === 'move') {
    $id   = (int)($body['id'] ?? 0);
    $day  = (int)($body['day'] ?? -1);
    $hour = (int)($body['hour'] ?? -1);
    if ($day < 0 || $day > 6 || $hour < 0 || $hour > 23) json_out(['error' => 'Invalid target.'], 422);

    $st = $pdo->prepare('SELECT id, day_of_week, start_hour FROM timetable_slots WHERE id = ? AND user_id = ?');
    $st->execute([$id, $user['id']]);
    $slot = $st->fetch();
    if (!$slot) json_out(['error' => 'Session not found.'], 404);

    $st = $pdo->prepare('SELECT id FROM timetable_slots WHERE user_id = ? AND day_of_week = ? AND start_hour = ? AND id <> ?');
    $st->execute([$user['id'], $day, $hour, $id]);
    $target = $st->fetch();

    $pdo->beginTransaction();
    try {
        $upd = $pdo->prepare('UPDATE timetable_slots SET day_of_week = ?, start_hour = ? WHERE id = ? AND user_id = ?');
        $upd->execute([$day, $hour, $id, $user['id']]);
        if ($target) { // swap
            $upd->execute([$slot['day_of_week'], $slot['start_hour'], $target['id'], $user['id']]);
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        $pdo->rollBack();
        json_out(['error' => 'Could not save that change.'], 500);
    }
    json_out(['ok' => true, 'swapped' => (bool)$target]);
}

if ($action === 'toggle') {
    $done = !empty($body['done']) ? 1 : 0;
    $st = $pdo->prepare('UPDATE timetable_slots SET is_completed = ? WHERE id = ? AND user_id = ?');
    $st->execute([$done, (int)($body['id'] ?? 0), $user['id']]);
    json_out(['ok' => true]);
}

json_out(['error' => 'Unknown action'], 400);
