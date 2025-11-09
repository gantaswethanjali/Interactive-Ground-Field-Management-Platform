<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || empty($_SESSION['subscription_temp'])){
    header("Location: take_subscription.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sub = $_SESSION['subscription_temp'];

// ✅ Address only (NO CARD DATA)
$order = [
    'postcode'  => $_POST['postcode']  ?? '',
    'town'      => $_POST['town']      ?? '',
    'address1'  => $_POST['address1']  ?? ''
];
$_SESSION['order_temp'] = $order;

// subscription id created during checkout (if already inserted earlier)
$subscription_id = $_SESSION['subscription_id'] ?? null;

// ✅ If already inserted (from checkout), just update address fields
if ($subscription_id) {
    $stmt = $conn->prepare("
        UPDATE subscriptions 
        SET address1 = :address1, town = :town, postcode = :postcode 
        WHERE id = :id AND user_id = :uid
    ");
    $stmt->execute([
        ':address1' => $order['address1'],
        ':town'     => $order['town'],
        ':postcode' => $order['postcode'],
        ':id'       => $subscription_id,
        ':uid'      => $user_id
    ]);
}
// ✅ Otherwise, safe fallback insert (no card info stored)
else {
    $stmt = $conn->prepare("
        INSERT INTO subscriptions (user_id, ground, plan, duration, address1, town, postcode)
        VALUES (:uid, :ground, :plan, :duration, :address1, :town, :postcode)
    ");
    $stmt->execute([
        ':uid'      => $user_id,
        ':ground'   => $sub['ground'],
        ':plan'     => $sub['plan'],
        ':duration' => $sub['duration'],
        ':address1' => $order['address1'],
        ':town'     => $order['town'],
        ':postcode' => $order['postcode']
    ]);
    $_SESSION['subscription_id'] = $conn->lastInsertId();
}

// ✅ Clean session (prevents duplicates on refresh)
unset($_SESSION['subscription_temp']);
unset($_SESSION['checkout_inserted']);
unset($_SESSION['review_inserted']);

include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Order Confirmed - FieldCraft</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  :root{ --nav-h: 72px; }
  @media (max-width: 991.98px){ :root{ --nav-h: 60px; } }
  /* Soft spacing so content isn’t hidden behind fixed header */
  body { padding-top: var(--nav-h); background:#f7faf9; }
  .cele-card { position:relative; overflow:hidden; border-radius:16px; }
  #confetti { position:absolute; inset:0; pointer-events:none; }
</style>
</head>
<body>

<div class="container py-5">
  <div class="card shadow-lg p-4 cele-card">
    <canvas id="confetti"></canvas>
    <div class="d-flex align-items-center mb-3">
      <span class="badge bg-success me-2">✓</span>
      <h3 class="m-0">Order Overview</h3>
    </div>

    <ul class="list-group mb-3">
      <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($sub['email']) ?></li>
      <li class="list-group-item"><strong>Ground:</strong> <?= htmlspecialchars($sub['ground']) ?></li>
      <li class="list-group-item"><strong>Plan:</strong> <?= htmlspecialchars($sub['plan']) ?></li>
      <li class="list-group-item"><strong>Duration:</strong> <?= htmlspecialchars($sub['duration']) ?></li>
      <li class="list-group-item">
        <strong>Address:</strong>
        <?= htmlspecialchars($order['address1']) ?>, <?= htmlspecialchars($order['town']) ?>, <?= htmlspecialchars($order['postcode']) ?>
      </li>
      <!-- ❌ Removed any card display (no last4 or any card storage) -->
    </ul>

    <div class="d-grid">
      <a href="portal.php" class="btn btn-primary btn-lg">Continue to Portal</a>
    </div>
  </div>
</div>

<script>
// Tiny confetti burst (front-end only)
(function(){
  const c = document.getElementById('confetti');
  const card = c.parentElement;
  function fit(){
    c.width = card.clientWidth;
    c.height = card.clientHeight;
  }
  const ctx = c.getContext('2d');
  fit(); addEventListener('resize', fit);

  const W = ()=>c.width, H = ()=>c.height;
  const bits = Array.from({length: 120}, ()=>({
    x: Math.random()*W(), y: -20 - Math.random()*120,
    vx: (Math.random()-0.5)*2, vy: 1+Math.random()*2,
    s: 2+Math.random()*3, a: 1, hue: 110+Math.random()*80
  }));
  const t0 = performance.now();
  c.style.display='block';

  function loop(now){
    ctx.clearRect(0,0,W(),H());
    bits.forEach(b=>{
      b.x += b.vx; b.y += b.vy; b.vy += 0.02; b.a *= 0.986;
      ctx.fillStyle = `hsla(${b.hue},70%,55%,${b.a})`;
      ctx.fillRect(b.x, b.y, b.s, b.s);
    });
    if (now - t0 < 1600) requestAnimationFrame(loop);
    else c.style.display='none';
  }
  requestAnimationFrame(loop);
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
