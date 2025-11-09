<?php
session_start();
?>
<?php include 'header.php'; ?>

<style>
/* ===== Layout & spacing to clear fixed navbar ===== */
:root{
  /* adjust if your navbar is taller/shorter */
  --nav-h: 72px;          /* desktop nav height */
  --nav-gap: 20px;        /* extra breathing room below nav */
  --fc-green:#4caf50; --fc-green-2:#81c784; --fc-sky:#d0efff; --fc-card:#ffffff; --fc-ink:#212529;
}
@media (max-width: 991.98px){
  :root{ --nav-h: 60px; } /* typical mobile navbar */
}
html, body { height: 100%; }
body{
  padding-top: calc(var(--nav-h) + var(--nav-gap)); /* 👈 ensures gap below fixed nav */
  background: linear-gradient(to top, #98db9a 0%, #bfe7ae 35%, var(--fc-sky) 100%);
  min-height: 100vh; overflow-x: hidden; color: var(--fc-ink);
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce){
  .field-bg *,.section-card,.sticky-summary,[data-magnetic]{animation:none!important;transition:none!important}
}

/* ===== FieldCraft background (sky, clouds, leaves, grass) ===== */
.field-bg{position:fixed;inset:0;z-index:-2;overflow:hidden;background:linear-gradient(to top,#8fd694 0%,#b4e0a1 40%,#d0efff 100%)}
.field-bg .grass{position:absolute;left:0;right:0;bottom:0;height:200px;background:linear-gradient(to top,var(--fc-green) 0%,var(--fc-green-2) 100%);overflow:hidden}
.field-bg .grass::before{content:"";position:absolute;inset:0;background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='400' height='140'><path fill='%234caf50' d='M0,140 C40,120 80,120 120,140 C160,160 200,160 240,140 C280,120 320,120 360,140 L400,140 L400,0 L0,0 Z'/></svg>") repeat-x bottom;animation:wave 9s linear infinite;opacity:.55}
@keyframes wave{from{background-position-x:0}to{background-position-x:400px}}
.field-bg .cloud{position:absolute;background:#fff;border-radius:50%;
  box-shadow:60px 20px 0 10px #fff,120px 25px 0 0 #fff,90px -10px 0 10px #fff,30px 10px 0 10px #fff;
  width:110px;height:65px;opacity:.85;animation:drift 55s linear infinite}
.field-bg .cloud.c1{top:90px;left:-160px;animation-delay:-8s}
.field-bg .cloud.c2{top:150px;left:-260px;width:140px;height:80px;animation-delay:-28s}
.field-bg .cloud.c3{top:60px;left:-420px;width:170px;height:90px;animation-delay:-18s}
@keyframes drift{from{transform:translateX(0)}to{transform:translateX(130vw)}}
.field-bg .leaf{position:absolute;top:-40px;width:22px;height:22px;background:radial-gradient(circle at 40% 40%,#ffb347 35%,#ffcc33 70%);
  border-radius:0 50% 50% 50%;transform:rotate(45deg);opacity:.9;animation:fall linear infinite}
@keyframes fall{0%{transform:translateY(0) rotate(0)}100%{transform:translateY(110vh) rotate(360deg)}}

/* ===== Cards, tables, and typography polish ===== */
.container{ max-width: 1200px; }

.page-intro{ text-align:center; margin-bottom: 1.25rem; }
.page-intro h2{
  font-size: clamp(1.4rem, 1rem + 1.6vw, 2rem);
  line-height: 1.2;
  margin-bottom: .25rem;            /* tighter */
}
.page-intro p{
  margin: 0 0 .75rem 0;             /* smaller gap */
  color: #6c757d;
}
.page-intro .btn{ margin-top: .5rem; }

.section-card{
  background: var(--fc-card);
  border-radius: 15px;
  box-shadow: 0 0 15px rgba(0,0,0,0.08);
  margin-bottom: 28px;              /* slightly tighter stack */
  padding: 20px;                    /* a touch smaller */
  animation: cardIn .6s ease forwards;
}
@keyframes cardIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}

.section-card h3{
  border-left: 6px solid #198754;
  padding-left: 10px;
  margin: 0 0 14px 0;               /* trimmed */
  font-weight: 700;
  font-size: clamp(1.05rem, .95rem + .6vw, 1.25rem);
}

.service-table th{
  background-color:#212529;color:#fff;text-align:center;vertical-align:middle
}
.service-table td{ vertical-align: middle; }
.service-table tbody tr:hover{ background:#f3f8f4; }
.service-table input[type="checkbox"]:checked:not(:disabled) ~ *{ background:#eefaf0; }

/* actions row */
.table-actions{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center}
.table-actions .btn-sm{border-radius:999px}

/* sticky summary */
.sticky-summary{
  position: sticky; bottom: 12px; z-index: 10;
  background: rgba(255,255,255,.95);
  backdrop-filter: blur(6px);
  border-radius: 16px; box-shadow: 0 12px 24px rgba(0,0,0,.12);
  padding: 10px 14px;
}
.sticky-summary .pill{
  display:inline-flex;align-items:center;gap:.5rem;
  padding:6px 12px;border-radius:999px;background:#e8f6ec;font-weight:600;color:#166534
}
.sticky-summary .total{ font-size:1.05rem; font-weight:700 }

.btn-continue{ position:relative; overflow:hidden; border-radius:12px }
.btn-continue:hover{ transform: translateY(-1px) }
.btn-continue .mower{ position:absolute; left:-42px; bottom:-6px; font-size:22px; animation:mower 2s ease-in-out infinite; opacity:.9 }
@keyframes mower{0%{transform:translateX(0)}50%{transform:translateX(26px) rotate(-2deg)}100%{transform:translateX(0)}}

/* extra breathing room on very small screens */
@media (max-width: 575.98px){
  .section-card{ padding:16px }
}
</style>

<!-- Background -->
<div class="field-bg" aria-hidden="true">
  <div class="cloud c1"></div>
  <div class="cloud c2"></div>
  <div class="cloud c3"></div>
  <?php for($i=0;$i<8;$i++): ?>
    <div class="leaf" style="left:<?=rand(2,98)?>%; animation-duration: <?=rand(12,26)?>s; animation-delay:-<?=rand(0,26)?>s;"></div>
  <?php endfor; ?>
  <div class="grass"></div>
</div>

<div class="container py-4">
  <div class="page-intro">
    <h2 class="fw-bold text-success">FieldCraft Outdoor Services</h2>
    <p>Professional, reliable one-time maintenance services for your sports field.</p>
    <a href="C:/Users/arsha/Downloads/xampp/htdocs/FieldCraft/assets/FieldCraft_Outdoor_Services_Brochure.pdf" target="_blank" class="btn btn-outline-primary btn-sm" data-magnetic>
      📘 View / Download Price Brochure
    </a>
  </div>

  <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger text-center">
      <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <form id="serviceForm" action="once_form_process.php" method="POST">

    <!-- Routine -->
    <div class="section-card">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="m-0">🏟️ Outdoor – Routine Pitch Maintenance</h3>
        <div class="table-actions">
          <button class="btn btn-sm btn-outline-success" type="button" id="selectAllRoutine">Select all</button>
          <button class="btn btn-sm btn-outline-secondary" type="button" id="clearRoutine">Clear</button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered service-table align-middle text-center">
          <thead>
            <tr><th>Select</th><th>Service</th><th>Purpose / Definition</th><th>Price (from £)</th></tr>
          </thead>
          <tbody>
            <?php
            $routine_services = [
              ["Mowing (Regular Cutting)", "Cutting grass to sport-specific height.", 120],
              ["Edging & Trimming", "Neatening pitch edges and borders.", 70],
              ["Aeration", "Relieves compaction; improves drainage.", 180],
              ["Fertilising", "Nutrient boost for healthy turf.", 110],
              ["Overseeding", "Maintains turf density and recovery.", 190],
              ["Top Dressing", "Levels surface and improves soil texture.", 220],
              ["Scarification", "Removes thatch, moss, and debris.", 240],
              ["Watering / Irrigation", "Keeps turf hydrated in dry periods.", 90],
              ["Weed & Pest Control", "Targets weeds, insects, or turf diseases.", 160],
              ["Line Marking", "Professional pitch markings to regulation size.", 80],
              ["Goal Mouth / Wear Zone Repair", "Repairs worn areas with turf or seed.", 180],
            ];
            foreach ($routine_services as $index => $srv) {
              $id = "routine_" . $index;
              echo "<tr>
                      <td><input type='checkbox' name='services[]' id='{$id}' value='" . htmlspecialchars($srv[0]) . "' class='form-check-input'></td>
                      <td class='text-start'><label for='{$id}' class='fw-semibold mb-0'>" . htmlspecialchars($srv[0]) . "</label></td>
                      <td class='text-start'>" . htmlspecialchars($srv[1]) . "</td>
                      <td><strong>£" . htmlspecialchars($srv[2]) . "</strong></td>
                    </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Specialised -->
    <div class="section-card">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="m-0">🌾 Outdoor – Specialised & Seasonal Works</h3>
        <div class="table-actions">
          <button class="btn btn-sm btn-outline-success" type="button" id="selectAllSpecial">Select all</button>
          <button class="btn btn-sm btn-outline-secondary" type="button" id="clearSpecial">Clear</button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered service-table align-middle text-center">
          <thead>
            <tr><th>Select</th><th>Service</th><th>Purpose / Definition</th><th>Price (from £)</th></tr>
          </thead>
          <tbody>
            <?php
            $special_services = [
              ["Seasonal Renovation", "End-of-season deep maintenance & restoration.", 1000],
              ["Compaction Relief", "Full soil structure restoration.", 300],
              ["Drainage Management", "Preventing waterlogging and improving playability.", 420],
              ["Match Preparation", "Line marking, rolling, brushing, watering, prep.", 120],
              ["Seasonal Grass Management", "Overseeding for summer/winter transition.", 200],
              ["Pest, Disease & Weed Monitoring", "Inspection & early treatment.", 90],
            ];
            foreach ($special_services as $index => $srv) {
              $id = "special_" . $index;
              echo "<tr>
                      <td><input type='checkbox' name='services[]' id='{$id}' value='" . htmlspecialchars($srv[0]) . "' class='form-check-input'></td>
                      <td class='text-start'><label for='{$id}' class='fw-semibold mb-0'>" . htmlspecialchars($srv[0]) . "</label></td>
                      <td class='text-start'>" . htmlspecialchars($srv[1]) . "</td>
                      <td><strong>£" . htmlspecialchars($srv[2]) . "</strong></td>
                    </tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
      <p class="text-muted small mt-2 mb-0">Prices may vary based on field size, condition, and travel distance.</p>
    </div>

    <!-- Dates -->
    <div class="section-card">
      <h4 class="mb-2">Preferred Service Dates (up to 3)</h4>
      <input type="date" name="service_dates[]" class="form-control mb-2" min="<?= date('Y-m-d') ?>" />
      <input type="date" name="service_dates[]" class="form-control mb-2" min="<?= date('Y-m-d') ?>" />
      <input type="date" name="service_dates[]" class="form-control mb-2" min="<?= date('Y-m-d') ?>" />
      <small class="text-muted">Leave any unused dates empty.</small>
    </div>

    <!-- sticky summary -->
    <div class="sticky-summary d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div class="pill">
        <span id="selCount">0</span> selected • Total: <span class="total">£<span id="selTotal">0.00</span></span>
      </div>
      <button type="submit" class="btn btn-success btn-lg btn-continue" id="continueBtn" data-confetti>
        <span class="mower">🚜</span>
        Continue
      </button>
    </div>

  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
  const priceMap = new Map([
    ["Mowing (Regular Cutting)", 120],
    ["Edging & Trimming", 70],
    ["Aeration", 180],
    ["Fertilising", 110],
    ["Overseeding", 190],
    ["Top Dressing", 220],
    ["Scarification", 240],
    ["Watering / Irrigation", 90],
    ["Weed & Pest Control", 160],
    ["Line Marking", 80],
    ["Goal Mouth / Wear Zone Repair", 180],
    ["Seasonal Renovation", 1000],
    ["Compaction Relief", 300],
    ["Drainage Management", 420],
    ["Match Preparation", 120],
    ["Seasonal Grass Management", 200],
    ["Pest, Disease & Weed Monitoring", 90],
  ]);

  const form = document.getElementById('serviceForm');
  const checkboxes = form.querySelectorAll('input[type="checkbox"][name="services[]"]');
  const selCount = document.getElementById('selCount');
  const selTotal = document.getElementById('selTotal');

  function recalc(){
    let count = 0, total = 0;
    checkboxes.forEach(cb => {
      if(cb.checked){
        count++;
        const name = cb.value.trim();
        if(priceMap.has(name)) total += priceMap.get(name);
      }
    });
    selCount.textContent = count;
    selTotal.textContent = total.toFixed(2);
  }
  checkboxes.forEach(cb => cb.addEventListener('change', recalc));
  recalc();

  // Select / Clear helpers
  const selectAllRoutine = document.getElementById('selectAllRoutine');
  const clearRoutine = document.getElementById('clearRoutine');
  const selectAllSpecial = document.getElementById('selectAllSpecial');
  const clearSpecial = document.getElementById('clearSpecial');

  function toggleGroup(prefix, checked){
    form.querySelectorAll('input[id^="'+prefix+'"]').forEach(cb => { cb.checked = checked; });
    recalc();
  }
  selectAllRoutine?.addEventListener('click', () => toggleGroup('routine_', true));
  clearRoutine?.addEventListener('click', () => toggleGroup('routine_', false));
  selectAllSpecial?.addEventListener('click', () => toggleGroup('special_', true));
  clearSpecial?.addEventListener('click', () => toggleGroup('special_', false));
})();
</script>
