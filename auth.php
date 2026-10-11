<?php
require_once __DIR__ . '/includes/auth.php';

if (current_user()) { header('Location: ' . url('dashboard.php')); exit; }

$mode  = (($_POST['mode'] ?? $_GET['mode'] ?? '') === 'signup') ? 'signup' : 'signin';
$error = '';
$email = trim((string)($_POST['email'] ?? ''));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)($_POST['password'] ?? '');
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $pdo = db();
        if ($mode === 'signup') {
            $st = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $st->execute([$email]);
            if ($st->fetch()) {
                $error = 'An account with that email already exists. Try signing in.';
            } else {
                $name = explode('@', $email)[0];
                $st = $pdo->prepare('INSERT INTO users (email, password_hash, display_name) VALUES (?, ?, ?)');
                $st->execute([$email, password_hash($password, PASSWORD_DEFAULT), $name]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$pdo->lastInsertId();
                flash('success', 'Your account has been created. Welcome to StudySync!');
                header('Location: ' . url('dashboard.php')); exit;
            }
        } else {
            $st = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
            $st->execute([$email]);
            $row = $st->fetch();
            if ($row && password_verify($password, $row['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$row['id'];
                flash('success', 'Welcome back!');
                header('Location: ' . url('dashboard.php')); exit;
            }
            $error = 'Incorrect email or password.';
        }
    }
}

$pageTitle = ($mode === 'signup' ? 'Create Account' : 'Sign In') . ' — StudySync';
$pageDesc  = 'Sign in or create a StudySync account to build your clash-free study timetable.';
require __DIR__ . '/includes/header.php';
$isSignin = $mode === 'signin';
?>
<div class="hero-glow auth-wrap">
  <div class="auth-box">
    <div class="auth-header-nav">
      <a href="<?= e(url('index.php')) ?>" class="brand">
        <span class="brand-spark">&#x2728;</span> Study<span>Sync</span>
      </a>
      <button type="button" class="theme-toggle-btn btn btn-ghost btn-sm btn-icon" title="Toggle theme">
        <span class="theme-icon">&#x1F319;</span>
      </button>
    </div>

    <div class="surface auth-card">
      <!-- Mode Tabs -->
      <div class="auth-tabs">
        <a href="<?= e(url('auth.php')) ?>" class="auth-tab<?= $isSignin ? ' active' : '' ?>">Sign In</a>
        <a href="<?= e(url('auth.php?mode=signup')) ?>" class="auth-tab<?= !$isSignin ? ' active' : '' ?>">Create Account</a>
      </div>

      <div class="auth-title-wrap">
        <h1><?= $isSignin ? 'Welcome back! &#x1F44B;' : 'Start planning today &#x1F389;' ?></h1>
        <p class="muted small"><?= $isSignin ? 'Enter your credentials to access your timetable.' : 'Join free — build a balanced study schedule in minutes.' ?></p>
      </div>

      <?php if ($error): ?>
        <div class="alert" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= e(url('auth.php')) ?>" class="stack mt">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="mode" value="<?= e($mode) ?>">
        
        <div class="field">
          <label for="email">Student Email Address</label>
          <input id="email" name="email" type="email" required value="<?= e($email) ?>" 
                 placeholder="student@university.edu" autocomplete="email">
        </div>

        <div class="field">
          <div class="row-between">
            <label for="password">Password</label>
            <span class="muted small">At least 6 characters</span>
          </div>
          <input id="password" name="password" type="password" required minlength="6" 
                 placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                 autocomplete="<?= $isSignin ? 'current-password' : 'new-password' ?>">
        </div>

        <button type="submit" class="btn btn-block btn-lg">
          <?= $isSignin ? 'Sign In &rarr;' : 'Create Free Account &rarr;' ?>
        </button>
      </form>

      <div class="auth-footer-note">
        <p class="muted small text-center">
          <?= $isSignin ? 'Don\'t have an account yet?' : 'Already have an account?' ?>
          <a href="<?= e(url('auth.php' . ($isSignin ? '?mode=signup' : ''))) ?>" class="link-bold">
            <?= $isSignin ? 'Create one for free' : 'Sign in here' ?>
          </a>
        </p>
      </div>
    </div>

    <div class="auth-bottom-links">
      <a href="<?= e(url('index.php')) ?>" class="muted small">&larr; Back to Home</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
