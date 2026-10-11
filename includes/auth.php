<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['csrf'];
}
function csrf_check($token): bool {
    return !empty($_SESSION['csrf']) && is_string($token) && hash_equals($_SESSION['csrf'], $token);
}

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $user = false;
    if ($user === false) {
        $st = db()->prepare('SELECT id, email, display_name, study_days, day_start_hour, day_end_hour FROM users WHERE id = ?');
        $st->execute([$_SESSION['user_id']]);
        $user = $st->fetch() ?: null;
        if ($user) {
            $user['study_days'] = array_values(array_filter(
                array_map('intval', array_filter(explode(',', $user['study_days']), 'strlen')),
                fn($d) => $d >= 0 && $d <= 6
            ));
            $user['day_start_hour'] = (int)$user['day_start_hour'];
            $user['day_end_hour']   = (int)$user['day_end_hour'];
        }
    }
    return $user;
}

function require_login(): array {
    $u = current_user();
    if (!$u) { header('Location: ' . url('auth.php')); exit; }
    return $u;
}

function flash(string $type, string $msg): void { $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg]; }
function pull_flash(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }
