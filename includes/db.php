<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $ex) {
            error_log('StudySync DB connection failed: ' . $ex->getMessage());
            http_response_code(500);
            $isApi = strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api/') !== false;
            if ($isApi) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Database connection failed. Check includes/config.php.']);
            } else {
                echo '<!DOCTYPE html><meta charset="utf-8"><title>StudySync — setup needed</title>'
                   . '<body style="font-family:system-ui;max-width:36rem;margin:4rem auto;padding:0 1rem">'
                   . '<h1>Database connection failed</h1>'
                   . '<p>Make sure MySQL/MariaDB is running, you have imported <code>database/schema.sql</code>, '
                   . 'and the credentials in <code>includes/config.php</code> are correct.</p></body>';
            }
            exit;
        }
    }
    return $pdo;
}
