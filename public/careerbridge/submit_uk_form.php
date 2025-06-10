<?php
// submit_uk_form.php
header('Content-Type: application/json');
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database config
$host = 'localhost';
$db   = 'careerbridge';
$user = 'root';
$pass = '';

// Connect to database
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Get POST data
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];
if ($name === '') $errors['name'] = 'Name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required.';
if ($phone === '' || !preg_match('/^[0-9\-\+\s]{7,20}$/', $phone)) $errors['phone'] = 'Valid phone number is required.';
if ($message === '') $errors['message'] = 'Message is required.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Prepare and insert
$stmt = $conn->prepare('INSERT INTO uk_applications (name, email, phone, message) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssss', $name, $email, $phone, $message);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save application.']);
}
$stmt->close();
$conn->close();
