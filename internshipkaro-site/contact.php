<?php
header('Content-Type: application/json; charset=utf-8');

$to = 'info@dpslearning.com';

function out($ok, $message) {
    echo json_encode(['ok' => $ok, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(false, 'Invalid request.');
}

// Honeypot: bots fill the hidden field.
if (!empty($_POST['website'])) {
    out(true, 'Thank you! We will contact you soon.');
}

$clean = function ($v) {
    return trim(preg_replace('/[\r\n]+/', ' ', strip_tags((string) $v)));
};

$name    = $clean($_POST['name'] ?? '');
$mobile  = $clean($_POST['mobile'] ?? '');
$email   = $clean($_POST['email'] ?? '');
$message = trim(strip_tags((string) ($_POST['message'] ?? '')));

if ($name === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9+\-\s]{8,15}$/', $mobile)) {
    out(false, 'Please fill all fields with valid details.');
}

$body = "Name: $name\nMobile: $mobile\nEmail: $email\n\nMessage:\n$message\n";
$headers = "From: InternshipKaro Website <no-reply@internshipkaro.in>\r\nReply-To: $email\r\nContent-Type: text/plain; charset=utf-8";

if (@mail($to, 'New enquiry from internshipkaro.in', $body, $headers)) {
    out(true, 'Thank you! We will contact you soon.');
}

out(false, 'Could not send right now. Please call or WhatsApp us.');
