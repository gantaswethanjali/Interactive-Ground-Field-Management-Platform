<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['email'] = $user['email'];
		if (!empty($_SESSION['subscription_temp'])) {
			header("Location: checkout.php");
		} elseif (!empty($_SESSION['redirect_after_login'])) {
			header("Location: " . $_SESSION['redirect_after_login']);
			unset($_SESSION['redirect_after_login']);
		} else {
				header("Location: portal.php");
    }
    exit();
    } else {
        $error = "Invalid email or password.";
        header("Location: signin.php?error=" . urlencode($error));
        exit();
    }
}
?>
