<?php
/**
 * api/verify_registration.php - AJAX endpoint to verify registration details uniqueness
 */
session_start();
header('Content-Type: application/json');
require_once '../include/dbConfig.php';

// Parse incoming request payload
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

$email = isset($data['email']) ? trim($data['email']) : '';
$username = isset($data['username']) ? trim($data['username']) : '';

if (empty($email) || empty($username)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and Username are required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

try {
    // 1. Check duplicate email in users
    $stmt1 = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
    $stmt1->bind_param("s", $email);
    $stmt1->execute();
    if ($stmt1->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email address is already registered.']);
        $stmt1->close();
        exit;
    }
    $stmt1->close();

    // 2. Check duplicate email in pending requests
    $stmt2 = $conn->prepare("SELECT id FROM user_registration_requests WHERE email = ? AND request_status = 'Pending' LIMIT 1");
    $stmt2->bind_param("s", $email);
    $stmt2->execute();
    if ($stmt2->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'A pending registration request already exists for this email.']);
        $stmt2->close();
        exit;
    }
    $stmt2->close();

    // 3. Check duplicate username in users (check employee_code and email)
    $stmt3 = $conn->prepare("SELECT user_id FROM users WHERE employee_code = ? OR email = ? LIMIT 1");
    $stmt3->bind_param("ss", $username, $username);
    $stmt3->execute();
    if ($stmt3->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username is already taken by a registered user.']);
        $stmt3->close();
        exit;
    }
    $stmt3->close();

    // 4. Check duplicate username in pending requests
    $stmt4 = $conn->prepare("SELECT id FROM user_registration_requests WHERE username = ? AND request_status = 'Pending' LIMIT 1");
    $stmt4->bind_param("s", $username);
    $stmt4->execute();
    if ($stmt4->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'A pending registration request already exists for this username.']);
        $stmt4->close();
        exit;
    }
    $stmt4->close();

    // Verification Success
    echo json_encode(['status' => 'success', 'message' => 'Details verified. No duplicate records found.']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
