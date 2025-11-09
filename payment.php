<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = 'payment.php';
    header('Location: signin.php');
    exit;
}

$service_prices = [
  "Mowing (Regular Cutting)" => 120,
  "Edging & Trimming" => 70,
  "Aeration" => 180,
  "Fertilising" => 110,
  "Overseeding" => 190,
  "Top Dressing" => 220,
  "Scarification" => 240,
  "Watering / Irrigation" => 90,
  "Weed & Pest Control" => 160,
  "Line Marking" => 80,
  "Goal Mouth / Wear Zone Repair" => 180,
  "Seasonal Renovation" => 1000,
  "Compaction Relief" => 300,
  "Drainage Management" => 420,
  "Match Preparation" => 120,
  "Seasonal Grass Management" => 200,
  "Pest, Disease & Weed Monitoring" => 90
];

$selected_services = $_SESSION['one_time_selected_services'] ?? [];
$selected_dates = $_SESSION['one_time_selected_dates'] ?? [];
?>

<?php include 'header.php'; ?>

<style>
:root { --fc-green:#4caf50; --fc-green-2:#81c784; --fc-sky:#d0efff; --fc-card:rgba(255,255,255,.96); }
@media (prefers-reduced-motion: reduce) { .field-bg *, .pay-card, .pay-cta { animation:none!important; transition:none!important; } }
body { background: linear-gradient(to top, #98db9a 0%, #bfe7ae 35%, var(--fc-sky) 100%); overflow-x:hidden; }

/* background scene */
.field-bg{position:fixed;inset:0;z-index:-2;overflow:hidden;background:linear-gradient(to top,#8fd694 0%,#b4e0a1 40%,#d0efff 100%)}
.field-bg .grass{position:absolute;left:0;right:0;bottom:0;height:200px;background:linear-gradient(to top,var(--fc-green) 0%,var(--fc-green-2) 100%);overflow:hidden}
.field-bg .grass::before{content:"";position:absolute;inset:0;background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='400' height='140'><path fill='%234caf50' d='M0,140 C40,120 80,120 120,140 C160,160 200,160 240,140 C280,120 320,120 360,140 L400,140 L400,0 L0,0 Z'/></svg>") repeat-x bottom;animation:wave 9s linear infinite;opacity:.55}
@keyframes wave{from{background-position-x:0}to{background-position-x:400px}}
.field-bg .cloud{position:absolute;background:#fff;border-radius:50%;box-shadow:60px 20px 0 10px #fff,120px 25px 0 0 #fff,90px -10px 0 10px #fff,30px 10px 0 10px #fff;width:110px;height:65px;opacity:.85;animation:drift 55s linear infinite}
.field-bg .cloud.c1{top:90px;left:-160px;animation-delay:-8s}
.field-bg .cloud.c2{top:150px;left:-260px;width:140px;height:80px;animation-delay:-28s}
.field-bg .cloud.c3{top:60px;left:-420px;width:170px;height:90px;animation-delay:-18s}
@keyframes drift{from{transform:translateX(0)}to{transform:translateX(130vw)}}
.field-bg .leaf{position:absolute;top:-40px;width:22px;height:22px;background:radial-gradient(circle at 40% 40%,#ffb347 35%,#ffcc33 70%);border-radius:0 50% 50% 50%;transform:rotate(45deg);opacity:.9;animation:fall linear infinite}
@keyframes fall{0%{transform:translateY(0) rotate(0)}100%{transform:translateY(110vh) rotate(360deg)}}

/* card */
.pay-card{background:var(--fc-card);backdrop-filter:blur(8px);border:none;border-radius:20px;box-shadow:0 20px 40px rgba(0,0,0,.12);animation:cardIn .7s ease forwards}
@keyframes cardIn{from{opacity:0;transform:translateY(24px) scale(.98)}to{opacity:1;transform:none}}
.pay-cta{position:relative;overflow:hidden;border-radius:14px;transition:transform .12s ease, box-shadow .2s ease}
.pay-cta:hover{transform:translateY(-2px);box-shadow:0 12px 22px rgba(76,175,80,.28)}
.pay-cta .mower{position:absolute;left:-48px;bottom:-6px;font-size:26px;animation:mowerSlide 2s ease-in-out infinite;opacity:.85}
@keyframes mowerSlide{0%{transform:translateX(0)}50%{transform:translateX(30px) rotate(-2deg)}100%{transform:translateX(0)}}
.fc-heading{display:inline-block;position:relative;padding-bottom:.3rem}
.fc-heading::after{content:"";position:absolute;left:50%;transform:translateX(-50%);bottom:-6px;width:72px;height:4px;border-radius:999px;background:linear-gradient(90deg,#6ee7ff,#a78bfa,#f472b6)}
</style>

<!-- background -->
<div class="field-bg" aria-hidden="true">
  <div class="cloud c1"></div>
  <div class="cloud c2"></div>
  <div class="cloud c3"></div>
  <?php for($i=0;$i<10;$i++): ?>
    <div class="leaf" style="left:<?=rand(2,98)?>%; animation-duration: <?=rand(12,26)?>s; animation-delay:-<?=rand(0,26)?>s;"></div>
  <?php endfor; ?>
  <div class="grass"></div>
</div>

<div class="container py-5">
  <h2 class="text-center mb-4 text-success fc-heading">Confirm Your One-Time Service</h2>

  <?php if (empty($selected_services)): ?>
    <div class="alert alert-danger text-center mb-4">
      ⚠️ No selected services found. Please <a href="once.php" class="alert-link">choose your services</a> again.
    </div>
  <?php else: ?>

    <!-- Dates -->
    <div class="mb-4">
      <h5 class="mb-2">Preferred Dates</h5>
      <?php if (empty($selected_dates)): ?>
        <p><em>No dates selected</em></p>
      <?php else: ?>
        <ul class="list-group list-group-flush">
          <?php foreach ($selected_dates as $date): ?>
            <li class="list-group-item"><?= htmlspecialchars(date('F j, Y', strtotime($date))) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- Summary -->
    <div class="card pay-card p-4 mb-4">
      <table class="table table-bordered align-middle mb-0">
        <thead class="table-success text-center">
          <tr><th>Service</th><th>Price (£)</th></tr>
        </thead>
        <tbody>
          <?php $total=0;
          foreach ($selected_services as $srv):
            $srv = trim($srv);
            $price = $service_prices[$srv] ?? 0;
            $total += $price; ?>
            <tr>
              <td><?= htmlspecialchars($srv) ?></td>
              <td class="text-center">£<?= number_format($price, 2) ?></td>
            </tr>
          <?php endforeach; ?>
          <tr class="fw-bold table-light">
            <td class="text-end">Total</td>
            <td class="text-center">£<?= number_format($total, 2) ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Simulated confirmation only (no card fields) -->
    <div class="card pay-card p-4">
      <h5 class="mb-3">Service Location</h5>
      <form id="paymentForm" novalidate>
        <div class="mb-3">
          <label class="form-label">Address</label>
          <input type="text" name="service_address" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Town / City</label>
          <input type="text" name="service_town" class="form-control" required>
        </div>
        <div class="mb-4">
          <label class="form-label">Postcode</label>
          <input type="text" name="service_postcode" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100 btn-lg pay-cta" data-confetti>
          <span class="mower">🚜</span>
          Confirm Booking
        </button>
      </form>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  const form = document.getElementById('paymentForm');
  if (form) {
    form.addEventListener('submit', e => {
      e.preventDefault();
      e.stopPropagation();
      form.classList.add('was-validated');
      setTimeout(()=>{
        alert('✅ Booking confirmed! Thank you for choosing FieldCraft.');
        window.location.href = 'index.php';
      }, 600);
    });
  }
})();
</script>
