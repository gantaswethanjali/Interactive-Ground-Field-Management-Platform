<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = "checkout.php";
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Load subscription info ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = $_POST['email']    ?? $_SESSION['email'];
    $ground   = $_POST['ground']   ?? '';
    $plan     = $_POST['plan']     ?? '';
    $duration = $_POST['duration'] ?? '';
    $_SESSION['subscription_temp'] = compact('email','ground','plan','duration');
}

$sub = $_SESSION['subscription_temp'] ?? null;

if (!$sub) {
    include 'header.php';
    echo "<div class='container' style='margin-top:90px'><div class='alert alert-warning text-center mt-5'>⚠️ No subscription selected. <a href='take_subscription.php'>Take Subscription</a></div></div>";
    // no footer per your rule
    exit();
}

// Insert to DB ONCE
if (empty($_SESSION['subscription_id'])) {
    $stmt = $conn->prepare("
        INSERT INTO subscriptions (user_id, ground, plan, duration)
        VALUES (:uid, :ground, :plan, :duration)
    ");
    $stmt->execute([
        ':uid'      => $user_id,
        ':ground'   => $sub['ground'],
        ':plan'     => $sub['plan'],
        ':duration' => $sub['duration']
    ]);

    $_SESSION['subscription_id'] = $conn->lastInsertId(); // store inserted ID
}

include 'header.php';
?>

<style>
:root{ --nav-h: 72px; }
@media (max-width: 991.98px){ :root{ --nav-h: 60px; } }
/* keep content clear of fixed navbar */
.checkout-wrap{ padding-top: calc(var(--nav-h) + 16px); background:#f7faf9; min-height: calc(100vh - var(--nav-h)); }
.stepper{ display:flex; gap:10px; align-items:center; margin-bottom:16px; }
.step{ display:flex; align-items:center; gap:8px; }
.step .dot{
  width:28px; height:28px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;
  font-weight:700; border:2px solid #198754; color:#198754; background:#e8f5e9;
}
.step.active .dot{ background:#198754; color:#fff; }
.step .lbl{ font-weight:600; color:#198754; }
.divider{ flex:1; height:2px; background: repeating-linear-gradient(90deg, #c5e7d0 0 8px, transparent 8px 14px); }
.summary-icon{ width:36px; height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:#eaf7ef; color:#166534; margin-right:8px; }
.card-rounded{ border-radius:16px; }
</style>

<div class="checkout-wrap">
  <div class="container py-4">
    <div class="card shadow p-4 mx-auto card-rounded" style="max-width:720px;">
      <!-- Stepper: you are on Step 1 (Review) -->
      <div class="stepper">
        <div class="step active"><span class="dot">1</span><span class="lbl">Review</span></div>
        <div class="divider"></div>
        <div class="step"><span class="dot">2</span><span class="lbl">Address</span></div>
        <div class="divider"></div>
        <div class="step"><span class="dot">3</span><span class="lbl">Confirm</span></div>
      </div>

      <h2 class="mb-3 text-center">Review Your Subscription</h2>

      <ul class="list-group mb-4">
        <li class="list-group-item">
          <span class="summary-icon">📧</span>
          <strong>Email:</strong> <?= htmlspecialchars($sub['email']) ?>
        </li>
        <li class="list-group-item">
          <span class="summary-icon">🏟️</span>
          <strong>Ground:</strong> <?= htmlspecialchars($sub['ground']) ?>
        </li>
        <li class="list-group-item">
          <span class="summary-icon">📦</span>
          <strong>Plan:</strong> <?= htmlspecialchars($sub['plan']) ?>
        </li>
        <li class="list-group-item">
          <span class="summary-icon">🗓️</span>
          <strong>Duration:</strong> <?= htmlspecialchars($sub['duration']) ?>
        </li>
      </ul>

      <div class="alert alert-success">
        <strong>Next:</strong> add the ground address on the next screen.<br>
        We <u>never</u> ask for or store card details online.
      </div>

      <div class="text-center mt-3">
        <a href="review_order.php" class="btn btn-success btn-lg">Continue</a>
      </div>
    </div>
  </div>
</div>
