<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id'])){
    $_SESSION['redirect_after_login'] = "review_order.php";
    header("Location: signin.php");
    exit();
}

if(empty($_SESSION['subscription_temp'])){
    header("Location: take_subscription.php");
    exit();
}

$sub = $_SESSION['subscription_temp'];
include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Review Order - FieldCraft</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  :root{ --nav-h:72px; }
  @media (max-width:991.98px){ :root{ --nav-h:60px; } }
  body{ padding-top:var(--nav-h); background:#f7faf9; }

  .stepper{ display:flex; gap:10px; align-items:center; margin-bottom:16px; }
  .step{ display:flex; align-items:center; gap:8px; }
  .step .dot{
    width:28px; height:28px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;
    font-weight:700; border:2px solid #198754; color:#198754; background:#e8f5e9;
  }
  .step.active .dot{ background:#198754; color:#fff; }
  .step .lbl{ font-weight:600; color:#198754; }
  .divider{ flex:1; height:2px; background: repeating-linear-gradient(90deg, #c5e7d0 0 8px, transparent 8px 14px); }

  .cele-card{ border-radius:16px; }
  .summary-icon{ width:36px; height:36px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:#eaf7ef; color:#166534; margin-right:8px; }
</style>
</head>
<body>

<div class="container py-5">
  <div class="card shadow-lg p-4 cele-card">

    <!-- Stepper -->
    <div class="stepper">
      <div class="step"><span class="dot">1</span><span class="lbl">Select Plan</span></div>
      <div class="divider"></div>
      <div class="step active"><span class="dot">2</span><span class="lbl">Review & Address</span></div>
      <div class="divider"></div>
      <div class="step"><span class="dot">3</span><span class="lbl">Confirm</span></div>
    </div>

    <h3 class="mb-3">Review Your Order</h3>

    <div class="row g-3">
      <div class="col-lg-6">
        <div class="list-group mb-3">
          <div class="list-group-item">
            <span class="summary-icon">📧</span>
            <strong>Email:</strong> <?= htmlspecialchars($sub['email']) ?>
          </div>
          <div class="list-group-item">
            <span class="summary-icon">🏟️</span>
            <strong>Ground:</strong> <?= htmlspecialchars($sub['ground']) ?>
          </div>
          <div class="list-group-item">
            <span class="summary-icon">📦</span>
            <strong>Plan:</strong> <?= htmlspecialchars($sub['plan']) ?>
          </div>
          <div class="list-group-item">
            <span class="summary-icon">🗓️</span>
            <strong>Duration:</strong> <?= htmlspecialchars($sub['duration']) ?>
          </div>
        </div>
        <div class="alert alert-success mb-4">
          <strong>Good to know:</strong> We’ll confirm your schedule after address verification.
          <br>We <u>never</u> ask for card details online.
        </div>
      </div>

      <div class="col-lg-6">
        <form action="order_confirm.php" method="POST" id="reviewForm" novalidate>
          <h5 class="mb-2">Ground Address</h5>
          <div class="mb-2">
            <label class="form-label">Postcode</label>
            <input type="text" class="form-control" name="postcode" placeholder="e.g., CB2 1TN" required
                   pattern="^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$" title="Enter a valid UK postcode (e.g., CB2 1TN)">
            <div class="invalid-feedback">Enter a valid UK postcode.</div>
          </div>
          <div class="mb-2">
            <label class="form-label">Town (Only Cambridge)</label>
            <input type="text" class="form-control" name="town" placeholder="Cambridge" required>
            <div class="invalid-feedback">Town must be Cambridge.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Address Line 1</label>
            <input type="text" class="form-control" name="address1" placeholder="Address Line 1" required>
            <div class="invalid-feedback">Please enter the address.</div>
          </div>

          <!-- ❌ No payment/card fields at all -->

          <div class="d-grid">
            <button type="submit" class="btn btn-success btn-lg">Confirm Order</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
// Cambridge-only + HTML5 validation (no card checks)
(function(){
  const form = document.getElementById('reviewForm');
  form.addEventListener('submit', function(e){
    // built-in validity first
    if (!form.checkValidity()) {
      e.preventDefault(); e.stopPropagation();
      form.classList.add('was-validated');
      return;
    }
    // Cambridge-only rule
    const town = (form.town.value || '').trim().toLowerCase();
    if (town !== 'cambridge') {
      e.preventDefault();
      alert('Our services are only available in Cambridge.');
      form.town.focus();
    }
  });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
