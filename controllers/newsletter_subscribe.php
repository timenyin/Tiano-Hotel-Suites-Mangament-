<?php
/**
 * Tiano Hotel and Suites - Newsletter Subscription Processor
 */
require_once __DIR__ . '/../include/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : (isset($_POST['EMAIL']) ? trim($_POST['EMAIL']) : '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO newsletter_subscribers (email, status, subscribed_at) VALUES (?, 'active', NOW()) ON DUPLICATE KEY UPDATE status = 'active'");
    $stmt->execute([$email]);

    echo json_encode(['status' => 'success', 'message' => 'Thank you for subscribing to Tiano Hotel updates!']);
} catch (Exception $e) {
    error_log("Newsletter subscription error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Could not record subscription. Please try again.']);
}
