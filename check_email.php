<?php
session_start();
include 'db.php';

$email = trim($_GET['email'] ?? '');
$ground = $_GET['ground'] ?? '';
$plan = $_GET['plan'] ?? '';
$duration = $_GET['duration'] ?? '';
$address1 = $_GET['address1'] ?? '';
$town = $_GET['town'] ?? '';
$postcode = $_GET['postcode'] ?? '';

if (empty($email) || empty($ground) || empty($plan) || empty($duration)) {
    header("Location: take_subscription.php");
    exit();
}

// Save temporary subscription info
$_SESSION['subscription_temp'] = compact('email', 'ground', 'plan', 'duration', 'address1', 'town', 'postcode');

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$_SESSION['redirect_after_login'] = "checkout.php";

if ($user) {
    header("Location: signin.php?email=" . urlencode($email));
} else {
    header("Location: register.php?email=" . urlencode($email));
}
exit();
?>
