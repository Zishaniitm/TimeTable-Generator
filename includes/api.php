<?php
// Shared bootstrap for JSON endpoints.
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/scheduler.php';

header('Content-Type: application/json; charset=utf-8');

function json_out($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function api_user(): array {
    $u = current_user();
    if (!$u) json_out(['error' => 'Not signed in'], 401);
    return $u;
}

/** Read JSON body, enforce POST + CSRF. */
function api_post(): array {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'POST required'], 405);
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) $body = [];
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($body['csrf'] ?? null);
    if (!csrf_check($token)) json_out(['error' => 'Invalid CSRF token. Please refresh the page.'], 403);
    return $body;
}
