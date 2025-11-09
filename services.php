<?php include 'header.php'; ?>
<main>

<style>
/* ===== Clear fixed navbar gap ===== */
:root{
  --nav-h: 72px;
  --nav-gap: 20px;
}
@media (max-width: 991.98px){ :root{ --nav-h: 60px; } }
body{ padding-top: calc(var(--nav-h) + var(--nav-gap)); }

/* ===== Page vibe ===== */
.page-hero{
  text-align:center; margin-bottom: 1rem;
}
.page-hero h1{
  font-size: clamp(1.4rem, 1rem + 2vw, 2.1rem);
}
.page-hero p{ color:#6c757d; }

/* Billing toggle */
.billing-toggle{
  display:flex; gap:.5rem; justify-content:center; align-items:center; flex-wrap:wrap;
  margin: .75rem 0 2rem 0;
}
.billing-toggle .btn{
  border-radius: 999px;
}
.badge-best{
  display:inline-block; margin-left:.4rem; font-size:.75rem; font-weight:700;
  background:linear-gradient(90deg,#6ee7ff,#a78bfa,#f472b6); -webkit-background-clip:text; background-clip:text; color:transparent;
}

/* Sport chips */
.sport-chips{ display:flex; flex-wrap:wrap; gap:.5rem; justify-content:center; margin-bottom:1.5rem; }
.sport-chips .btn{ border-radius:999px }

/* Plan sections */
.plan-section {
  background: #fff;
  border-radius: 15px;
  box-shadow: 0 0 15px rgba(0,0,0,0.08);
  padding: 26px;
  margin-bottom: 40px;
}
.plan-section h3 {
  border-left: 6px solid #198754;
  padding-left: 10px;
  margin-bottom: 16px;
  font-weight: 700;
  font-size: clamp(1.1rem, .95rem + .8vw, 1.35rem);
}

.table thead { background-color: #212529; color: #fff; }
.table td, .table th { vertical-align: middle; }

/* Tier row tints */
.tier-basic    { background-color: #e8f5e9; }
.tier-standard { background-color: #e3f2fd; }
.tier-premium  { background-color: #fff8e1; }
.tier-elite    { background-color: #fce4ec; }

/* Hover glow + slight lift */
.table tbody tr{
  transition: transform .12s ease, box-shadow .12s ease, background .2s ease;
}
.table tbody tr:hover{
  transform: translateY(-1px);
  box-shadow: 0 8px 16px rgba(0,0,0,.06);
}

/* Price highlight by billing period */
:root{ --accent:#198754; }
.highlight-weekly  td[data-type="weekly"],
.highlight-monthly td[data-type="monthly"],
.highlight-annual  td[data-type="annual"]{
  outline: 2px solid var(--accent);
  background: #eaf7ef !important;
  font-weight: 700;
}

/* Sticky CTA */
.sticky-cta{
  position: sticky; bottom: 12px; z-index: 10;
  background: rgba(255,255,255,.95);
  backdrop-filter: blur(6px);
  border-radius: 16px;
  box-shadow: 0 12px 24px rgba(0,0,0,.12);
  padding: 12px 16px;
  display:flex; align-items:center; justify-content:center; gap:1rem; flex-wrap:wrap;
}
.sticky-cta .btn{ border-radius:12px }
.mower{ position:relative; left:-6px }

/* Smooth section anchor offset */
.section-anchor{ position: relative; top: -80px; visibility: hidden; }
html{ scroll-behavior: smooth; }
</style>

<div class="container my-4">

  <div class="page-hero">
    <h1 class="fw-bold text-success">Subscription Plans</h1>
    <p>
      Tailored professional maintenance for <strong>Football</strong>, <strong>Rugby</strong>, <strong>Cricket</strong>, and <strong>Multi-Sport Fields</strong>.<br>
      Each plan follows <strong>GMA</strong>, <strong>FA</strong>, <strong>RFU</strong>, and <strong>ECB</strong> maintenance standards.
    </p>

    <!-- Billing period toggle -->
    <div class="billing-toggle" role="group" aria-label="Billing period">
      <button class="btn btn-outline-success active" data-bill="weekly">Weekly</button>
      <button class="btn btn-outline-success" data-bill="monthly">Monthly <span class="badge-best">Popular</span></button>
      <button class="btn btn-outline-success" data-bill="annual">Annual <span class="badge-best">Best value</span></button>
    </div>

    <!-- Sport chips -->
    <div class="sport-chips">
      <a href="#football"  class="btn btn-outline-dark btn-sm">⚽ Football</a>
      <a href="#rugby"     class="btn btn-outline-dark btn-sm">🏉 Rugby</a>
      <a href="#cricket"   class="btn btn-outline-dark btn-sm">🏏 Cricket</a>
      <a href="#fieldplan" class="btn btn-outline-dark btn-sm">🌿 Multi-Sport</a>
    </div>
  </div>

  <!-- FOOTBALL -->
  <span id="football" class="section-anchor"></span>
  <div class="plan-section plan-football">
    <h3>⚽ Football Pitch Maintenance</h3>
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle pricing-table">
        <thead><tr><th>Tier</th><th>Weekly</th><th>Monthly</th><th>Annually</th><th>Includes</th></tr></thead>
        <tbody>
          <tr class="tier-basic">
            <td>Basic</td>
            <td data-type="weekly"  data-price="120">£120</td>
            <td data-type="monthly" data-price="400">£400</td>
            <td data-type="annual"  data-price="5760">£5,760</td>
            <td class="text-start">Weekly mowing, fortnightly marking, fertiliser 2×/yr, light aeration twice, weed control once, AI photo analysis, real-time dashboard access.</td>
          </tr>
          <tr class="tier-standard">
            <td>Standard</td>
            <td data-type="weekly"  data-price="180">£180</td>
            <td data-type="monthly" data-price="650">£650</td>
            <td data-type="annual"  data-price="7800">£7,800</td>
            <td class="text-start">Everything in Basic + monthly aeration, fertiliser every 8 weeks, end-of-season scarification.</td>
          </tr>
          <tr class="tier-premium">
            <td>Premium</td>
            <td data-type="weekly"  data-price="300">£300</td>
            <td data-type="monthly" data-price="900">£900</td>
            <td data-type="annual"  data-price="10800">£10,800</td>
            <td class="text-start">All in Standard + tailored fertiliser (4–6×/yr), deep decompaction, irrigation support, full renovation.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- RUGBY -->
  <span id="rugby" class="section-anchor"></span>
  <div class="plan-section plan-rugby">
    <h3>🏉 Rugby Pitch Maintenance</h3>
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle pricing-table">
        <thead><tr><th>Tier</th><th>Weekly</th><th>Monthly</th><th>Annually</th><th>Includes</th></tr></thead>
        <tbody>
          <tr class="tier-basic">
            <td>Basic</td>
            <td data-type="weekly"  data-price="140">£140</td>
            <td data-type="monthly" data-price="550">£550</td>
            <td data-type="annual"  data-price="6600">£6,600</td>
            <td class="text-start">Weekly mowing, fertiliser 2×/yr, light aeration quarterly, weed control once, AI photo analysis, real-time dashboard access.</td>
          </tr>
          <tr class="tier-standard">
            <td>Standard</td>
            <td data-type="weekly"  data-price="180">£180</td>
            <td data-type="monthly" data-price="720">£720</td>
            <td data-type="annual"  data-price="8640">£8,640</td>
            <td class="text-start">Everything in Basic + fertiliser every 6–8 weeks, monthly aeration, end-of-season decompaction.</td>
          </tr>
          <tr class="tier-premium">
            <td>Premium</td>
            <td data-type="weekly"  data-price="250">£250</td>
            <td data-type="monthly" data-price="1000">£1,000</td>
            <td data-type="annual"  data-price="12000">£12,000</td>
            <td class="text-start">All in Standard + tailored fertiliser (6×/yr), irrigation checks, full renovation.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- CRICKET -->
  <span id="cricket" class="section-anchor"></span>
  <div class="plan-section plan-cricket">
    <h3>🏏 Cricket Pitch Maintenance</h3>
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle pricing-table">
        <thead><tr><th>Tier</th><th>Weekly</th><th>Monthly</th><th>Annually</th><th>Includes</th></tr></thead>
        <tbody>
          <tr class="tier-basic">
            <td>Basic</td>
            <td data-type="weekly"  data-price="150">£150</td>
            <td data-type="monthly" data-price="450">£450</td>
            <td data-type="annual"  data-price="5000">£5,000</td>
            <td class="text-start">Weekly mowing (square & outfield), line marking pre-match, fertiliser 2×/yr, aeration twice, weed control once, AI analysis, dashboard access.</td>
          </tr>
          <tr class="tier-standard">
            <td>Standard</td>
            <td data-type="weekly"  data-price="220">£220</td>
            <td data-type="monthly" data-price="600">£600</td>
            <td data-type="annual"  data-price="7720">£7,720</td>
            <td class="text-start">Everything in Basic + scarification & brushing, monthly aeration, end-of-season work.</td>
          </tr>
          <tr class="tier-premium">
            <td>Premium</td>
            <td data-type="weekly"  data-price="300">£300</td>
            <td data-type="monthly" data-price="750">£750</td>
            <td data-type="annual"  data-price="10000">£10,000</td>
            <td class="text-start">All in Standard + deep spiking, tailored fertiliser, irrigation check, 24/7 support.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- FIELD PLAN -->
  <span id="fieldplan" class="section-anchor"></span>
  <div class="plan-section plan-fieldplan">
    <h3>🌿 Multi-Sport Field Plan (3-in-1)</h3>
    <div class="table-responsive">
      <table class="table table-bordered table-hover text-center align-middle pricing-table">
        <thead><tr><th>Tier</th><th>Weekly</th><th>Monthly</th><th>Annually</th><th>Includes</th></tr></thead>
        <tbody>
          <tr class="tier-standard">
            <td>🟩 Standard</td>
            <td data-type="weekly"  data-price="300">£300</td>
            <td data-type="monthly" data-price="1200">£1,200</td>
            <td data-type="annual"  data-price="14400">£14,400</td>
            <td class="text-start">Weekly mowing, fortnightly marking, fertiliser 3×/yr, light aeration quarterly, weed control once.</td>
          </tr>
          <tr class="tier-premium">
            <td>🟦 Premium</td>
            <td data-type="weekly"  data-price="410">£410</td>
            <td data-type="monthly" data-price="1650">£1,650</td>
            <td data-type="annual"  data-price="19800">£19,800</td>
            <td class="text-start">Everything in Standard + monthly aeration, irrigation checks in season, end-of-season scarification.</td>
          </tr>
          <tr class="tier-elite">
            <td>🏆 Elite</td>
            <td data-type="weekly"  data-price="550">£550</td>
            <td data-type="monthly" data-price="2200">£2,200</td>
            <td data-type="annual"  data-price="26400">£26,400</td>
            <td class="text-start">All in Premium + tailored fertiliser (6–8×/yr), deep decompaction, pest control, full renovation.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="alert alert-secondary small">
    <strong>Notes:</strong><br>
    • Prices follow GMA/FA/RFU/ECB guidance.<br>
    • For full-size grass surfaces (~6,000–8,000 m²).<br>
    • Additional costs may apply for materials (seed, fertiliser, loam).<br>
    • Annual plans can be invoiced monthly or prepaid with a 5% discount <span class="badge-best">Best value</span>.
  </div>

  <!-- Sticky CTA -->
  <div class="sticky-cta mt-4">
    <span class="text-muted">Ready to customise?</span>
    <a href="take_subscription.php" class="btn btn-success btn-lg" data-confetti>
      <span class="mower">🚜</span>
      Take Subscription
    </a>
    <a href="once.php" class="btn btn-outline-primary btn-lg" data-magnetic>One-Time Payment</a>
  </div>

</div>
</main>

<script>
(() => {
  // Billing toggle logic: add highlight class to each table based on selection
  const toggleBtns = document.querySelectorAll('.billing-toggle [data-bill]');
  const tables = document.querySelectorAll('.pricing-table');

  function setBilling(mode){
    // buttons active state
    toggleBtns.forEach(b=>{
      b.classList.toggle('active', b.dataset.bill === mode);
    });

    // highlight target column
    tables.forEach(t=>{
      t.classList.remove('highlight-weekly','highlight-monthly','highlight-annual');
      t.classList.add('highlight-' + mode);
      // also update displayed currency text from data-price (keeps formatting fresh)
      t.querySelectorAll(`td[data-type]`).forEach(td=>{
        const val = td.getAttribute('data-price');
        if(!val) return;
        // keep thousands comma for annuals
        const n = Number(val);
        td.textContent = '£' + (n >= 1000 ? n.toLocaleString() : n);
      });
    });
  }

  // default to Monthly highlight
  setBilling('monthly');

  toggleBtns.forEach(b=>{
    b.addEventListener('click', ()=> setBilling(b.dataset.bill));
  });

  // Smooth anchor scroll is handled by CSS (scroll-behavior)
})();
</script>
