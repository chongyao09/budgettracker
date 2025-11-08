<?php
$host = 'localhost';
$db   = 'budgettracker_new';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email']) && !isset($_POST['new_password'])) {
    // Step 1: Verify name and email
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $stmt = $conn->prepare('SELECT id FROM users WHERE name = ? AND email = ?');
    $stmt->bind_param('ss', $name, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // User found, show password reset form
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Set New Password</title>
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="style.css">
            <style>
                body.reset-bg {
                    background: #e6ecf4;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .reset-container {
                    background: #fff;
                    border-radius: 18px;
                    box-shadow: 0 8px 32px rgba(44, 62, 80, 0.10);
                    padding: 48px 36px 36px 36px;
                    max-width: 400px;
                    width: 100%;
                    position: relative;
                    z-index: 2;
                }
                .reset-container h2 {
                    font-family: \'Montserrat\', sans-serif;
                    font-size: 2rem;
                    font-weight: 700;
                    margin-bottom: 24px;
                    text-align: center;
                }
                .reset-form {
                    display: flex;
                    flex-direction: column;
                    gap: 18px;
                }
                .reset-form label {
                    font-weight: 600;
                    margin-bottom: 6px;
                }
                .reset-form input {
                    padding: 12px 14px;
                    border-radius: 8px;
                    border: 1px solid #dbe2ef;
                    font-size: 1rem;
                    font-family: \'Montserrat\', sans-serif;
                }
                .reset-btn {
                    background: #7b2ff2;
                    color: #fff;
                    border: none;
                    padding: 12px 0;
                    border-radius: 30px;
                    font-size: 1.1rem;
                    font-weight: 700;
                    cursor: pointer;
                    margin-top: 10px;
                    transition: background 0.2s;
                }
                .reset-btn:hover {
                    background: #3498db;
                }
                .reset-back {
                    display: block;
                    text-align: center;
                    margin-top: 18px;
                    color: #7b2ff2;
                    text-decoration: none;
                    font-weight: 600;
                    transition: color 0.2s;
                }
                .reset-back:hover {
                    color: #3498db;
                }
            </style>
        </head>
        <body class="reset-bg">
            <div class="reset-container">
                <h2>Set New Password</h2>
                <form class="reset-form" method="POST" action="reset_password.php">
                    <input type="hidden" name="name" value="'.htmlspecialchars($name).'">
                    <input type="hidden" name="email" value="'.htmlspecialchars($email).'">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                    <button type="submit" class="reset-btn">Reset Password</button>
                </form>
                <a href="credential.html" class="reset-back">&larr; Back to Sign In</a>
            </div>
        </body>
        </html>';
    } else {
        echo '<h2>No user found with that name and email.</h2>';
        echo '<a href="forgot_password.html">Try Again</a>';
    }
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'], $_POST['name'], $_POST['email'])) {
    // Step 2: Update password
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare('UPDATE users SET password_hash = ? WHERE name = ? AND email = ?');
    $stmt->bind_param('sss', $password_hash, $name, $email);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo '<!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="refresh" content="3;url=credential.html">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Password Reset Successful</title>
            <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="style.css">
            <style>
                body.reset-bg {
                    background: #e6ecf4;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .reset-container {
                    background: #fff;
                    border-radius: 18px;
                    box-shadow: 0 8px 32px rgba(44, 62, 80, 0.10);
                    padding: 48px 36px 36px 36px;
                    max-width: 400px;
                    width: 100%;
                    position: relative;
                    z-index: 2;
                    text-align: center;
                }
                .reset-container h2 {
                    font-family: "Montserrat", sans-serif;
                    font-size: 2rem;
                    font-weight: 700;
                    margin-bottom: 24px;
                }
                .reset-container p {
                    font-size: 1.1rem;
                    color: #444;
                }
                .reset-container a {
                    color: #7b2ff2;
                    font-weight: 600;
                    text-decoration: none;
                    display: inline-block;
                    margin-top: 18px;
                }
                .reset-container a:hover {
                    color: #3498db;
                }
            </style>
        </head>
        <body class="reset-bg">
            <div class="reset-container">
                <h2>Password reset successful!</h2>
                <p>You will be redirected to sign in in a moment.<br>
                <a href="credential.html">Click here if you are not redirected.</a></p>
            </div>
        </body>
        </html>';
    } else {
        echo '<h2>Error: Could not reset password.</h2>';
        echo '<a href="forgot_password.html">Try Again</a>';
    }
    $stmt->close();
} else {
    header('Location: forgot_password.html');
    exit();
}
$conn->close();
?>
