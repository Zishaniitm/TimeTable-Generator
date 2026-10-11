<?php
require_once __DIR__ . '/../includes/api.php';
$user = api_user();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $st = $pdo->prepare('SELECT id, name, color, hours_per_week FROM subjects WHERE user_id = ? ORDER BY created_at, id');
    $st->execute([$user['id']]);
    json_out(['subjects' => $st->fetchAll()]);
}

$body = api_post();
$action = $body['action'] ?? '';

if ($action === 'add') {
    $name  = trim((string)($body['name'] ?? ''));
    $hours = (int)($body['hours_per_week'] ?? 0);
    $color = (string)($body['color'] ?? '');
    if ($name === '' || mb_strlen($name) > 100) json_out(['error' => 'Enter a subject name (max 100 characters).'], 422);
    if ($hours < 1 || $hours > 40) json_out(['error' => 'Hours per week must be between 1 and 40.'], 422);
    if (!in_array($color, SUBJECT_COLORS, true)) $color = SUBJECT_COLORS[0];
    $st = $pdo->prepare('INSERT INTO subjects (user_id, name, color, hours_per_week) VALUES (?, ?, ?, ?)');
    $st->execute([$user['id'], $name, $color, $hours]);
    json_out(['ok' => true, 'id' => (int)$pdo->lastInsertId()]);
}

if ($action === 'delete') {
    $st = $pdo->prepare('DELETE FROM subjects WHERE id = ? AND user_id = ?');
    $st->execute([(int)($body['id'] ?? 0), $user['id']]);
    json_out(['ok' => true]);
}

json_out(['error' => 'Unknown action'], 400);
