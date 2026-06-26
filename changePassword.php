<?php
session_start();

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/include/dbConfig.php';

$message = "";
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT user_id, full_name, email, password_hash
    FROM users
    WHERE user_id = ? AND status='Active'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Invalid user account.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $currentPassword = trim($_POST['current_password']);
    $newPassword     = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if (!password_verify($currentPassword, $user['password_hash'])) {

        $message = "<div class='error'>Current password is incorrect.</div>";

    } elseif (strlen($newPassword) < 8) {

        $message = "<div class='error'>New password must be at least 8 characters long.</div>";

    } elseif ($newPassword !== $confirmPassword) {

        $message = "<div class='error'>New password and Confirm password do not match.</div>";

    } else {

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE users
            SET password_hash = ?, updated_at = NOW()
            WHERE user_id = ?
        ");

        $stmt->bind_param("si", $hashedPassword, $user_id);

        if ($stmt->execute()) {

            $message = "<div class='success'>Password changed successfully.</div>";

        } else {

            $message = "<div class='error'>Unable to update password.</div>";

        }

        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password</title>

<style>

body{
    margin:0;
    font-family:Arial,sans-serif;
    background:#f4f6f9;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}

.container{
    width:400px;
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,.1);
}

h2{
    margin-top:0;
    color:#333;
}

p{
    color:#666;
    font-size:14px;
}

.form-group{
    margin-bottom:18px;
}

label{
    display:block;
    margin-bottom:6px;
    font-weight:bold;
}

input{
    width:100%;
    padding:12px;
    border:1px solid #ccc;
    border-radius:8px;
    box-sizing:border-box;
}

button{
    width:100%;
    padding:12px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:8px;
    font-size:16px;
    cursor:pointer;
}

button:hover{
    background:#1d4ed8;
}

.success{
    background:#dcfce7;
    color:#166534;
    padding:12px;
    border-radius:6px;
    margin-bottom:15px;
}

.error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:6px;
    margin-bottom:15px;
}

.back-btn{
    display:block;
    text-align:center;
    margin-top:15px;
    text-decoration:none;
    color:#2563eb;
}

.back-btn:hover{
    text-decoration:underline;
}

.password-hint{
    font-size:12px;
    color:#777;
    margin-top:5px;
}

</style>

</head>
<body>

<div class="container">

    <h2>Change Password</h2>

    <p>
        Welcome,
        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
    </p>

    <?php echo $message; ?>

    <form method="POST">

        <div class="form-group">
            <label>Current Password</label>
            <input type="password"
                   name="current_password"
                   required>
        </div>

        <div class="form-group">
            <label>New Password</label>
            <input type="password"
                   name="new_password"
                   required>

            <div class="password-hint">
                Minimum 8 characters recommended.
            </div>
        </div>

        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password"
                   name="confirm_password"
                   required>
        </div>

        <button type="submit">
            Change Password
        </button>

    </form>

    <a href="dashboard.php" class="back-btn">
        ← Back to Dashboard
    </a>

</div>

</body>
</html>