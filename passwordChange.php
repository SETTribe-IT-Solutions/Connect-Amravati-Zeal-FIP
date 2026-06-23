<?php
session_start();
require_once __DIR__ . '/include/dbConfig.php';

/*
|--------------------------------------------------------------------------
| User Must Be Logged In
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$message = "";

/*
|--------------------------------------------------------------------------
| Get Logged In User
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT user_id, full_name, password_hash
    FROM users
    WHERE user_id = ? AND status='Active'
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

if (!$user) {
    die("User account not found.");
}

/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $current_password = trim($_POST['current_password']);
    $new_password     = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (!password_verify($current_password, $user['password_hash'])) {

        $message = "<div class='error'>Current password is incorrect.</div>";

    } elseif (strlen($new_password) < 8) {

        $message = "<div class='error'>New password must be at least 8 characters.</div>";

    } elseif ($new_password !== $confirm_password) {

        $message = "<div class='error'>New password and Confirm password do not match.</div>";

    } else {

        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $update = $conn->prepare("
            UPDATE users
            SET password_hash = ?, updated_at = NOW()
            WHERE user_id = ?
        ");

        $update->bind_param("si", $new_hash, $user_id);

        if ($update->execute()) {

            $message = "<div class='success'>
                            Password changed successfully.
                        </div>";

        } else {

            $message = "<div class='error'>
                            Failed to update password.
                        </div>";
        }

        $update->close();
    }
}
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
    background:#f4f6f9;
    font-family:Arial,sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}

.container{
    width:420px;
    background:#fff;
    padding:30px;
    border-radius:12px;
    box-shadow:0 5px 20px rgba(0,0,0,.1);
}

h2{
    margin-top:0;
    color:#333;
}

.form-group{
    margin-bottom:15px;
}

label{
    display:block;
    margin-bottom:5px;
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
    border:none;
    background:#2563eb;
    color:#fff;
    border-radius:8px;
    cursor:pointer;
    font-size:16px;
}

button:hover{
    background:#1d4ed8;
}

.success{
    background:#dcfce7;
    color:#166534;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}

.error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}

.back{
    text-align:center;
    margin-top:15px;
}

.back a{
    text-decoration:none;
    color:#2563eb;
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

    <div class="back">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

</div>

</body>
</html>
