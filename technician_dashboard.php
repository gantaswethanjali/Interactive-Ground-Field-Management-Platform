<?php
session_start();
include 'db.php';

if (!isset($_SESSION['tech_id'])) {
    header("Location: technician_login.php");
    exit();
}

$tech_id    = $_SESSION['tech_id'];
$tech_email = $_SESSION['tech_email'];
$tech_emp   = $_SESSION['tech_emp'];
$tech_name  = $_SESSION['tech_name'] ?? $tech_email;

$msg = "";

/* ---------- Claim / Unclaim ---------- */
if (isset($_GET['claim'])) {
    $sub_id = (int)$_GET['claim'];
    $stmt = $conn->prepare("SELECT * FROM subscriptions WHERE id=:id AND technician_id IS NULL");
    $stmt->execute([':id'=>$sub_id]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $u = $conn->prepare("UPDATE subscriptions SET technician_id=:tid, technician_name=:tname WHERE id=:id");
        $u->execute([':tid'=>$tech_id, ':tname'=>$tech_name, ':id'=>$sub_id]);
        $msg = "✅ You have successfully claimed this job.";
    } else {
        $msg = "⚠️ This job has already been claimed by another technician.";
    }
}

if (isset($_GET['unclaim'])) {
    $sub_id = (int)$_GET['unclaim'];
    $u = $conn->prepare("UPDATE subscriptions SET technician_id=NULL, technician_name=NULL WHERE id=:id AND technician_id=:tid");
    $u->execute([':id'=>$sub_id, ':tid'=>$tech_id]);
    $msg = $u->rowCount() > 0 ? "🔄 You have unclaimed this job." : "⚠️ You cannot unclaim a job that isn’t yours.";
}

/* ---------- Fetch data for tables ---------- */
$subs = $conn->query("
    SELECT s.*, u.email AS client_email 
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    ORDER BY s.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT id, ground, town FROM subscriptions WHERE technician_id=:tid ORDER BY created_at DESC");
$stmt->execute([':tid'=>$tech_id]);
$mySubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Technician Dashboard - FieldCraft</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>
  <style>
    :root { --topbar-h: 58px; }
    html, body { height: 100%; }
    body { padding-top: var(--topbar-h); background:#f7faf9; }

    /* Minimal top strip (NOT a navbar) — only Sign Out */
    .topbar {
      position: fixed; top:0; left:0; right:0; height: var(--topbar-h);
      display:flex; align-items:center; justify-content:flex-end;
      padding: 0 14px; background:#0f172a; color:#e2e8f0; z-index: 1000;
      box-shadow: 0 2px 6px rgba(0,0,0,.18);
    }

    .container { max-width: 1200px; }
    .card { border-radius: 14px; }
    .card-gradient { background: linear-gradient(135deg,#e3f2fd 0%, #f1f8e9 100%); border: none; border-radius: 16px; }

    .badge-claimed { background-color:#ffc107; color:#000; }
    .badge-unclaimed { background-color:#6c757d; }

    /* fun but subtle chart polish */
    .chart-wrap { position: relative; overflow: hidden; border-radius: 12px; }
    .chart-wrap::after{
      content:""; position:absolute; inset:0; pointer-events:none;
      background: radial-gradient(900px 300px at var(--mx,50%) var(--my,0%), rgba(34,197,94,.08), transparent 50%);
      opacity:0; transition:opacity .2s ease;
    }
    .chart-wrap:hover::after{ opacity:1; }

    .section-title{ font-weight:700; border-left:6px solid #198754; padding-left:10px; }
  </style>
</head>
<body>

<!-- TOP STRIP: Sign Out only -->
<div class="topbar">
  <a href="tech_logout.php" class="btn btn-outline-light btn-sm">Sign Out</a>
</div>

<div class="container py-4">
  <h2 class="mb-1">Welcome, <?= htmlspecialchars($tech_name) ?> 👋</h2>
  <p class="text-muted mb-3">Employee #<?= htmlspecialchars($tech_emp) ?></p>
  <?php if (!empty($msg)): ?>
    <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>

  <div class="row g-4 mt-1">
    <!-- LEFT: Jobs list -->
    <div class="col-lg-8">
      <div class="card p-4 shadow-sm">
        <h4 class="mb-3 section-title">All Subscriptions & Claims</h4>
        <?php if ($subs): ?>
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-dark"><tr>
                <th>Client</th><th>Ground</th><th>Plan</th><th>Location</th><th>Technician</th><th>Action</th>
              </tr></thead>
              <tbody>
              <?php foreach ($subs as $s): ?>
                <tr>
                  <td><?= htmlspecialchars($s['client_email']) ?></td>
                  <td><?= htmlspecialchars($s['ground']) ?></td>
                  <td><?= htmlspecialchars($s['plan']) ?></td>
                  <td><?= htmlspecialchars($s['address1']) ?>, <?= htmlspecialchars($s['town']) ?></td>
                  <td>
                    <?php if ($s['technician_id']): ?>
                      <span class="badge badge-claimed">Claimed by <?= htmlspecialchars($s['technician_name']) ?></span>
                    <?php else: ?>
                      <span class="badge badge-unclaimed">Unclaimed</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!$s['technician_id']): ?>
                      <a href="?claim=<?= (int)$s['id'] ?>" class="btn btn-sm btn-primary" onclick="return confirm('Claim this subscription?')">Claim</a>
                    <?php elseif ($s['technician_id']==$tech_id): ?>
                      <a href="?unclaim=<?= (int)$s['id'] ?>" class="btn btn-sm btn-warning" onclick="return confirm('Unclaim this job?')">Unclaim</a>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info">No subscriptions found.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- RIGHT: Upload panel -->
    <div class="col-lg-4">
      <?php include 'tech_upload_box.php'; ?>
    </div>
  </div>

  <!-- ========== TECH LIVE METRICS DASHBOARD (PERSISTENT) ========== -->
  <div class="mt-5">
    <div class="card card-gradient shadow p-3 mb-3">
      <div class="d-flex align-items-center justify-content-between">
        <h4 class="mb-0 fw-semibold text-success">📊 Live Maintenance Metrics</h4>
        <div id="weatherBox" class="badge bg-info text-dark p-2">Weather: loading…</div>
      </div>
    </div>

    <div class="row g-4">
      <!-- INPUT BOX -->
      <div class="col-lg-4">
        <div class="card shadow p-3">
          <h5 class="mb-3">Enter/Update Readings</h5>

          <div class="mb-2">
            <label class="form-label">Your Claimed Subscription</label>
            <select id="subSelect" class="form-select">
              <option value="">-- choose --</option>
              <?php foreach ($mySubs as $r): ?>
                <option value="<?= (int)$r['id'] ?>">#<?= (int)$r['id'] ?> — <?= htmlspecialchars($r['ground']) ?> (<?= htmlspecialchars($r['town']) ?>)</option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Only subscriptions you have claimed will show.</div>
          </div>

          <div class="mb-2">
            <label class="form-label">Client Email (of this subscription)</label>
            <input type="email" id="clientEmail" class="form-control" placeholder="client@example.com">
            <div class="form-text">Used to ensure this update shows only to the correct client.</div>
          </div>

          <div class="row">
            <div class="col-6 mb-2"><label class="form-label">Moisture %</label><input id="f_moist" type="number" step="0.1" class="form-control"></div>
            <div class="col-6 mb-2"><label class="form-label">Gmax</label><input id="f_gmax" type="number" step="0.1" class="form-control"></div>
            <div class="col-6 mb-2"><label class="form-label">Compaction (psi)</label><input id="f_comp" type="number" step="0.1" class="form-control"></div>
            <div class="col-6 mb-2"><label class="form-label">Height (mm)</label><input id="f_height" type="number" step="0.1" class="form-control"></div>
            <div class="col-6 mb-2"><label class="form-label">Temperature (°C)</label><input id="f_temp" type="number" step="0.1" class="form-control"></div>
            <div class="col-6 mb-2"><label class="form-label">Rainfall (mm)</label><input id="f_rain" type="number" step="0.1" class="form-control"></div>
          </div>
          <div class="mb-2">
            <label class="form-label">Notes</label>
            <textarea id="f_notes" rows="2" class="form-control" placeholder="Work done, observations, hazards…"></textarea>
          </div>

          <button id="btnSave" class="btn btn-success w-100">💾 Save Reading</button>
          <div id="saveMsg" class="mt-2 small text-muted"></div>
        </div>
      </div>

      <!-- BAR CHARTS -->
      <div class="col-lg-8">
        <div class="card shadow p-3 mb-3 chart-wrap">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-0">🌱 Moisture & Gmax</h5><span class="text-muted small">Hover to see “previous vs now”</span>
          </div>
          <canvas id="chartMG" height="160"></canvas>
        </div>
        <div class="card shadow p-3 mb-3 chart-wrap">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-0">🏗️ Compaction & Height</h5><span class="text-muted small">Hover to see “previous vs now”</span>
          </div>
          <canvas id="chartCH" height="160"></canvas>
        </div>
        <div class="card shadow p-3 chart-wrap">
          <div class="d-flex align-items-center justify-content-between">
            <h5 class="mb-0">🌦️ Temperature & Rainfall</h5><span class="text-muted small">Hover to see “previous vs now”</span>
          </div>
          <canvas id="chartTR" height="160"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ========== Weather ==========
(function(){
  const wb = document.getElementById('weatherBox');
  const render = (obj) => {
    if (!obj) { wb.textContent = 'Weather unavailable'; return; }
    const cw = obj.current_weather || {};
    wb.textContent = `Temp ${cw.temperature ?? '?'}°C • Wind ${cw.windspeed ?? '?'} km/h`;
  };
  const fetchW = (lat,lon)=> fetch(`https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current_weather=true`)
                                .then(r=>r.json()).then(render).catch(()=>render(null));
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(p=>fetchW(p.coords.latitude,p.coords.longitude), ()=>fetchW(52.2053,0.1218));
  } else { fetchW(52.2053,0.1218); }
})();

// ========== Charts (bar) with “previous vs now” tooltips ==========
let chartMG, chartCH, chartTR;

function prevAt(dataArr, idx){
  if (idx <= 0) return null;
  for (let j = idx - 1; j >= 0; j--) {
    const v = dataArr[j];
    if (v !== null && v !== undefined) return v;
  }
  return null;
}

const previousTooltip = {
  id: 'previousTooltip',
  callbacks: {
    label: function(context){
      const ds = context.dataset;
      const idx = context.dataIndex;
      const now = context.parsed.y;
      const prev = prevAt(ds.data, idx);
      if (prev == null) return `${ds.label}: ${now}`;
      const diff = (now - prev).toFixed(2);
      const arrow = now >= prev ? '▲' : '▼';
      return `${ds.label}: ${now} (prev ${prev}, ${arrow}${diff})`;
    }
  }
};

function makeBarChart(canvasId, labels, datasets){
  return new Chart(document.getElementById(canvasId), {
    type: 'bar',
    data: { labels, datasets },
    options: {
      responsive: true,
      interaction: { mode: 'index', intersect: false },
      plugins: { tooltip: previousTooltip },
      scales: { x: { }, y:{ beginAtZero:true } },
      animation: { duration: 700, easing: 'easeOutCubic' },
      elements: { bar: { borderRadius: 6 } }
    }
  });
}

function initCharts(){
  chartMG = makeBarChart('chartMG', [], [
    { label:'Moisture %', data:[], backgroundColor:'#42a5f5' },
    { label:'Gmax',       data:[], backgroundColor:'#ef5350' }
  ]);
  chartCH = makeBarChart('chartCH', [], [
    { label:'Compaction (psi)', data:[], backgroundColor:'#26a69a' },
    { label:'Height (mm)',      data:[], backgroundColor:'#fbc02d' }
  ]);
  chartTR = makeBarChart('chartTR', [], [
    { label:'Temperature °C', data:[], backgroundColor:'#fb8c00' },
    { label:'Rainfall mm',    data:[], backgroundColor:'#7e57c2' }
  ]);
}
initCharts();

// Mouse glow over chart cards
document.querySelectorAll('.chart-wrap').forEach(card=>{
  card.addEventListener('mousemove', e=>{
    const r = card.getBoundingClientRect();
    const mx = ((e.clientX - r.left) / r.width) * 100;
    const my = ((e.clientY - r.top) / r.height) * 100;
    card.style.setProperty('--mx', mx + '%');
    card.style.setProperty('--my', my + '%');
  });
});

// Load series for selected subscription (keep same endpoint and limit)
const subSelect = document.getElementById('subSelect');
function loadSeries(){
  const sid = subSelect.value;
  if (!sid) return;
  fetch(`readings_series.php?subscription_id=${encodeURIComponent(sid)}&limit=60`)
    .then(r=>r.json())
    .then(d=>{
      if (!d.ok) return;
      const L = d.labels;
      chartMG.data.labels = L; chartMG.data.datasets[0].data = d.moisture; chartMG.data.datasets[1].data = d.gmax; chartMG.update();
      chartCH.data.labels = L; chartCH.data.datasets[0].data = d.compaction; chartCH.data.datasets[1].data = d.height; chartCH.update();
      chartTR.data.labels = L; chartTR.data.datasets[0].data = d.temperature; chartTR.data.datasets[1].data = d.rainfall; chartTR.update();
    });
}
subSelect.addEventListener('change', loadSeries);
setInterval(loadSeries, 12000);

// Save reading — unchanged endpoint and fields
document.getElementById('btnSave').addEventListener('click', ()=>{
  const sid = subSelect.value;
  const clientEmail = document.getElementById('clientEmail').value.trim();
  if (!sid) { alert('Choose a subscription first.'); return; }
  if (!clientEmail) { alert('Enter the client email for this subscription.'); return; }

  const data = new URLSearchParams({
    subscription_id: sid,
    client_email: clientEmail,
    moisture:   document.getElementById('f_moist').value,
    gmax:       document.getElementById('f_gmax').value,
    compaction: document.getElementById('f_comp').value,
    height:     document.getElementById('f_height').value,
    temperature:document.getElementById('f_temp').value,
    rainfall:   document.getElementById('f_rain').value,
    notes:      document.getElementById('f_notes').value
  });

  const btn = document.getElementById('btnSave');
  const msg = document.getElementById('saveMsg');
  btn.disabled = true;
  fetch('readings_save.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: data.toString()
  }).then(r=>r.json()).then(d=>{
    if (d.ok) {
      msg.innerHTML = '<span class="text-success">✅ Saved!</span>';
      loadSeries();
    } else {
      msg.innerHTML = '<span class="text-danger">❌ '+ (d.error || 'Save failed') +'</span>';
    }
    setTimeout(()=> msg.innerHTML='', 2500);
  }).catch(()=>{
    msg.innerHTML = '<span class="text-danger">Network error</span>';
    setTimeout(()=> msg.innerHTML='', 2500);
  }).finally(()=> btn.disabled = false);
});
</script>
</body>
</html>
