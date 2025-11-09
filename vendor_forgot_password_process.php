<?php
include 'db.php'; // DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.'); window.history.back();</script>";
        exit;
    }

    // Validate password
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit;
    }

    $passwordRegex = "/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/";
    if (!preg_match($passwordRegex, $password)) {
        echo "<script>alert('Password must be 8+ chars, 1 uppercase, 1 number, 1 special character.'); window.history.back();</script>";
        exit;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Update password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->execute([$hashedPassword, $email]);

        echo "<script>alert('Password updated successfully. Please sign in.'); window.location='signin.php';</script>";
    } else {
        echo "<script>alert('Email does not exist. Please register.'); window.location='register.php';</script>";
    }
}
?>
