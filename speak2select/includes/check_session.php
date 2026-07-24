<?php
require_once __DIR__ . '/init.php';

if (!defined('APP_ACCESS')) {
    http_response_code(403);
    exit('Forbidden');
}

if (!isset($_SESSION['user_id'], $_SESSION['session_token'])) {
    echo 'logout';
    exit;
}

$user_id = $_SESSION['user_id'];
$session_token = $_SESSION['session_token'];

$stmt = $conn->prepare("SELECT session_token FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$db_token = $stmt->fetchColumn();

if (!$db_token || $db_token !== $session_token) {
    $_SESSION = [];
    session_destroy();
    echo 'logout';
} else {
    echo 'ok';
}
