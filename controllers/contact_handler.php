<?php
/**
 * Tiano Hotel and Suites - Inbound Contact Message Processor
 */
require_once __DIR__ . '/../include/db.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$name         = isset($_POST['name']) ? trim($_POST['name']) : '';
$email        = isset($_POST['email']) ? trim($_POST['email']) : '';
$phone_number = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : '';
$msg_subject  = isset($_POST['msg_subject']) ? trim($_POST['msg_subject']) : '';
$message      = isset($_POST['message']) ? trim($_POST['message']) : '';

if (empty($name) || empty($email) || empty($msg_subject) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields (Name, Email, Subject, and Message).']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone_number, msg_subject, message, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
    // Dispatch inquiry email to hotel support
    require_once __DIR__ . '/../include/mailer.php';
    $inquiryBody = "<p>You have received a new inquiry from the website contact form:</p>"
        . "<ul>"
        . "<li><strong>From:</strong> " . htmlspecialchars($name) . "</li>"
        . "<li><strong>Email:</strong> " . htmlspecialchars($email) . "</li>"
        . "<li><strong>Phone:</strong> " . htmlspecialchars($phone_number ?: 'Not provided') . "</li>"
        . "<li><strong>Subject:</strong> " . htmlspecialchars($msg_subject) . "</li>"
        . "</ul>"
        . "<div style='background:#f8fafc;padding:16px;border-left:4px solid #E73539;border-radius:6px;'>"
        . "<strong>Message:</strong><br>" . nl2br(htmlspecialchars($message))
        . "</div>";
    $inquiryHtml = buildTianoEmailTemplate("New Inbound Message: " . htmlspecialchars($msg_subject), $inquiryBody);
    @sendTianoMail(TIANO_SMTP_USER, 'Tiano Hotel Reception', "New Contact Message: " . $msg_subject, $inquiryHtml, '', $email);

    echo json_encode([
        'status'  => 'success',
        'message' => 'Thank you! Your message has been received. Our management team will reply shortly.'
    ]);
} catch (Exception $e) {
    error_log("Contact message submission error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'A database error occurred while submitting your message. Please contact us directly via phone or WhatsApp.']);
}
