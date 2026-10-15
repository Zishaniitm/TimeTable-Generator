<?php
// Database settings. Override with environment variables or edit the defaults (XAMPP/WAMP/Cloud friendly).
define('DB_HOST', getenv('DB_HOST') ?: 'sql301.infinityfree.com');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'if0_43143714_StudySync');
define('DB_USER', getenv('DB_USER') ?: 'if0_43143714');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'Zishan9044');
define('APP_NAME', 'StudySync');

// Base URL path of the app (auto-detected so it works in /htdocs/studysync or at the web root).
$base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if (substr($base, -4) === '/api') { $base = substr($base, 0, -4); }
define('BASE_URL', $base);
