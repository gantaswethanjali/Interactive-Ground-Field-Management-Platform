<?php
session_start();
include 'db.php';

$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$password = $_POST['password'];
$confirmPassword = $_POST['confirm_password'];
$redirect = $_POST['redirect'] ?? '';

$errors = [];

// Validate input
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email.";
if (!preg_match("/^0\d{10}$/", $phone)) $errors['phone'] = "Invalid phone number.";
if ($password !== $confirmPassword) $errors['confirm_password'] = "Passwords do not match.";
if (strlen($password) < 8) $errors['password'] = "Password must be at least 8 characters.";

if (!empty($errors)) {
    header("Location: register.php?errors=" . urlencode(json_encode($errors)) . "&email=" . urlencode($email) . "&phone=" . urlencode($phone));
    exit();
}

// Check if user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->rowCount() > 0) {
    header("Location: signin.php?error=" . urlencode("User already exists. Please sign in."));
    exit();
}

// Insert user
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (email, phone, password) VALUES (?, ?, ?)");
$stmt->execute([$email, $phone, $hashed]);
$user_id = $conn->lastInsertId();

// Auto login
$_SESSION['user_id'] = $user_id;
$_SESSION['email'] = $email;

// Go straight to checkout if subscription is pending
if (!empty($_SESSION['subscription_temp'])) {
    header("Location: checkout.php");
    exit();
}

// Otherwise go to portal
header("Location: portal.php");
exit();
?>
