<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: signin.php");
  exit();
}

$user_id = $_SESSION['user_id'];

/* Subscriptions */
$stmt = $conn->prepare("SELECT * FROM subscriptions WHERE user_id=:uid");
$stmt->execute([':uid'=>$user_id]);
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Portal - FieldCraft</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1"></script>

<style>
  :root { --topbar-h: 58px; }
  html, body { height: 100%; }
  body { padding-top: var(--topbar-h); background:#f7faf9; }

  /* Minimal top bar (NOT a navbar) with only Sign Out */
  .topbar {
    position: fixed; top:0; left:0; right:0; height: var(--topbar-h);
    display:flex; align-items:center; justify-content:flex-end;
    padding: 0 14px; background:#0f172a; color:#e2e8f0; z-index: 1000;
    box-shadow: 0 2px 6px rgba(0,0,0,.18);
  }

  .page-wrap { max-width: 1200px; }

  .dash-card{ background: linear-gradient(135deg,#fce4ec 0%, #e3f2fd 100%); border: none; border-radius: 16px; }
  .card{ border-radius: 14px; }

  .analyze-chip{ display:inline-flex; align-items:center; gap:.4rem; padding:.25rem .6rem; border-radius:999px; background:#eaf7ef; color:#166534; font-weight:600; }
  #loadingSpin{ vertical-align:-2px; }

  .report-img{ width:100%; max-height:250px; object-fit:cover; border-radius:8px; }

  .docs-list{ max-height: 350px; overflow-y:auto; }

  .section-title{ font-weight:700; border-left:6px solid #198754; padding-left:10px; }
  #custWeather{ background:#cffafe !important; color:#0f172a !important; }
</style>
</head>
<body>

<!-- ONLY Sign Out -->
<div class="topbar">
  <a href="logout.php" class="btn btn-outline-light btn-sm">Sign Out</a>
</div>

<div class="container page-wrap py-3">
  <h2 class="m-0 mb-2">Welcome, <?= htmlspecialchars($_SESSION['email']) ?></h2>

  <div class="row g-4 mt-1">
    <!-- LEFT COLUMN -->
    <div class="col-lg-8">

      <!-- Subscriptions -->
      <h5 class="section-title mb-2">Your Subscriptions</h5>
      <?php if($subs): ?>
        <?php foreach($subs as $s): ?>
          <div class="card shadow-sm p-3 mb-3">
            <ul class="list-group list-group-flush">
              <li class="list-group-item"><strong>Ground:</strong> <?= htmlspecialchars($s['ground']) ?></li>
              <li class="list-group-item"><strong>Plan:</strong> <?= htmlspecialchars($s['plan']) ?></li>
              <li class="list-group-item"><strong>Duration:</strong> <?= htmlspecialchars($s['duration']) ?></li>
              <li class="list-group-item">
                <strong>Address:</strong>
                <?= htmlspecialchars($s['address1']) ?>, <?= htmlspecialchars($s['town']) ?>, <?= htmlspecialchars($s['postcode']) ?>
              </li>
              <?php if (!empty($s['technician_name'])): ?>
                <li class="list-group-item">
                  <strong>Technician:</strong>
                  <span class="badge bg-success"><?= htmlspecialchars($s['technician_name']) ?></span> assigned.
                </li>
              <?php else: ?>
                <li class="list-group-item text-muted">
                  <span class="badge bg-secondary">Unassigned</span> <em>No technician assigned yet.</em>
                </li>
              <?php endif; ?>
            </ul>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="alert alert-info">
          You have no active subscriptions.
          <a href="take_subscription.php">Take Subscription</a>
        </div>
      <?php endif; ?>

      <!-- AI Analyzer -->
      <div class="card p-3 shadow-sm mb-4 mt-4">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="m-0">AI Field Analyzer (For Members)</h5>
          <span class="analyze-chip">📷 Upload and analyze</span>
        </div>
        <form action="analyze_field_remote.php" method="POST" enctype="multipart/form-data" id="analyzeForm" class="mt-3">
          <input type="file" name="field_image" class="form-control mb-2" accept="image/*" required>
          <button class="btn btn-success w-100" id="analyzeBtn">
            <span class="spinner-border spinner-border-sm me-2 d-none" id="loadingSpin"></span>
            Analyze Field Photo
          </button>
        </form>

        <?php if(isset($_SESSION['flash'])): ?>
          <div class="alert alert-info mt-3 mb-0">
            <?= htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['analysis_overlay']) || !empty($_SESSION['analysis_bars'])): ?>
          <div class="mt-3 d-flex align-items-center flex-wrap gap-2">
            <?php if (!empty($_SESSION['analysis_overlay'])): ?>
              <img src="<?= htmlspecialchars($_SESSION['analysis_overlay']) ?>" alt="Green cover heatmap" class="img-fluid" style="max-width:420px;border-radius:10px;">
            <?php endif; ?>
            <?php if (!empty($_SESSION['analysis_bars'])): ?>
              <img src="<?= htmlspecialchars($_SESSION['analysis_bars']) ?>" alt="Condition bars" class="img-fluid" style="max-height:40px">
            <?php endif; ?>
          </div>
          <?php unset($_SESSION['analysis_overlay'], $_SESSION['analysis_bars']); ?>
        <?php endif; ?>
      </div>

      <!-- Analysis Reports -->
      <div class="card p-3 shadow-sm">
        <h5 class="mb-3">Field Analysis Reports</h5>
        <?php
          $reports = $conn->prepare("SELECT * FROM field_analysis WHERE user_id = :uid ORDER BY analyzed_at DESC");
          $reports->execute([':uid' => $user_id]);
          $rows = $reports->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <?php if ($rows): ?>
          <?php foreach ($rows as $r): ?>
            <div class="border rounded p-2 mb-2">
              <img src="<?= htmlspecialchars($r['image_path']) ?>" class="report-img" alt="Uploaded field">
              <div class="mt-1">
                <small><strong>Date:</strong> <?= htmlspecialchars($r['analyzed_at']) ?></small><br>
                <small>
                  <strong>Green:</strong> <?= $r['green_pct'] ?>% •
                  <strong>Brown:</strong> <?= $r['brown_pct'] ?>% •
                  <strong>Bare:</strong> <?= $r['bare_pct'] ?>% •
                  <strong>Wet:</strong> <?= $r['dark_pct'] ?>% •
                  <strong>Patchiness:</strong> <?= $r['patchiness'] ?>
                </small>
                <div class="mt-1"><em><?= htmlspecialchars($r['summary']) ?></em></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert alert-secondary mb-0">No analysis reports yet.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-lg-4">
      <?php
      // Hide request
      if (isset($_GET['hide_file']) && is_numeric($_GET['hide_file'])) {
          $file_id = (int)$_GET['hide_file'];
          $update = $conn->prepare("UPDATE technician_files SET client_visible=0 WHERE id=:id AND client_id=:cid");
          $update->execute([':id'=>$file_id, ':cid'=>$user_id]);
          echo '<div class="alert alert-success">🗑️ File hidden successfully from your view.</div>';
      }
      // Visible files
      $file_stmt = $conn->prepare("
          SELECT f.id, f.file_name, f.uploaded_at, t.email AS technician_email
          FROM technician_files f
          JOIN technicians t ON f.technician_id = t.id
          WHERE f.client_id = :cid AND f.client_visible = 1
          ORDER BY f.uploaded_at DESC
      ");
      $file_stmt->execute([':cid'=>$user_id]);
      $files = $file_stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <div class="card p-3 shadow-sm">
        <h5 class="mb-3">Your Service Documents</h5>
        <?php if ($files): ?>
          <div class="list-group docs-list mb-3">
            <?php foreach ($files as $file): ?>
              <div class="list-group-item d-flex justify-content-between align-items-start">
                <div>
                  <a href="viewer.php?id=<?= (int)$file['id'] ?>" target="previewFrame" class="text-decoration-none">
                    <?= htmlspecialchars($file['file_name']) ?>
                  </a><br>
                  <small class="text-muted">
                    Uploaded by: <?= htmlspecialchars($file['technician_email']) ?><br>
                    <?= htmlspecialchars($file['uploaded_at']) ?>
                  </small>
                </div>
                <a href="?hide_file=<?= (int)$file['id'] ?>" class="btn btn-sm btn-danger ms-2"
                   onclick="return confirm('Hide this file from your view? The technician will still have it.');">
                  Hide
                </a>
              </div>
            <?php endforeach; ?>
          </div>
          <h6 class="mb-2">Preview</h6>
          <iframe name="previewFrame" src="" style="width:100%;height:400px;border:none;"></iframe>
        <?php else: ?>
          <div class="alert alert-secondary mb-0">No visible documents.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Metrics -->
  <div class="card dash-card shadow p-3 mt-4">
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="mb-0 fw-semibold text-primary">📈 Your Ground Metrics</h4>
      <div id="custWeather" class="badge bg-info text-dark p-2">Weather: loading…</div>
    </div>

    <div class="mt-3">
      <label class="form-label">Select Subscription</label>
      <select id="custSub" class="form-select" style="max-width:480px;">
        <option value="">-- choose --</option>
        <?php foreach($subs as $r): ?>
          <option value="<?= (int)$r['id'] ?>">#<?= (int)$r['id'] ?> — <?= htmlspecialchars($r['ground']) ?> (<?= htmlspecialchars($r['town']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="row g-3 mt-2">
      <div class="col-lg-4">
        <div class="border rounded p-2 bg-white">
          <div class="d-flex justify-content-between">
            <strong>Moisture &amp; Gmax</strong><small class="text-muted">Hover for previous</small>
          </div>
          <canvas id="cMG" height="150"></canvas>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="border rounded p-2 bg-white">
          <div class="d-flex justify-content-between">
            <strong>Compaction &amp; Height</strong><small class="text-muted">Hover for previous</small>
          </div>
          <canvas id="cCH" height="150"></canvas>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="border rounded p-2 bg-white">
          <div class="d-flex justify-content-between">
            <strong>Temperature &amp; Rainfall</strong><small class="text-muted">Hover for previous</small>
          </div>
          <canvas id="cTR" height="150"></canvas>
        </div>
      </div>
    </div>
  </div>
  <!-- END Metrics -->

</div>

<script>
// Spinner while analyzing
const f = document.getElementById('analyzeForm');
if (f) {
  f.addEventListener('submit', () => {
    document.getElementById('analyzeBtn').disabled = true;
    document.getElementById('loadingSpin').classList.remove('d-none');
  });
}

// Weather badge
(function(){
  const wb = document.getElementById('custWeather');
  const render = (obj)=> {
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

// Charts
function prevAt(arr, idx){
  if (idx<=0) return null;
  for (let j=idx-1;j>=0;j--){ const v = arr[j]; if (v!==null && v!==undefined) return v; }
  return null;
}
const previousTooltip = {
  id:'previousTooltip',
  callbacks:{
    label: function(ctx){
      const ds = ctx.dataset, i = ctx.dataIndex, now = ctx.parsed.y, prev = prevAt(ds.data,i);
      if (prev==null) return `${ds.label}: ${now}`;
      const diff=(now-prev).toFixed(2), arrow = now>=prev?'▲':'▼';
      return `${ds.label}: ${now} (prev ${prev}, ${arrow}${diff})`;
    }
  }
};
function makeBar(canvasId, datasets){
  return new Chart(document.getElementById(canvasId),{
    type:'bar',
    data:{ labels:[], datasets },
    options:{
      responsive:true,
      interaction:{ mode:'index', intersect:false },
      plugins:{ tooltip: previousTooltip },
      scales:{ x:{}, y:{ beginAtZero:true } }
    }
  });
}
const cMG = makeBar('cMG', [
  { label:'Moisture %', data:[], backgroundColor:'#42a5f5' },
  { label:'Gmax',       data:[], backgroundColor:'#ef5350' }
]);
const cCH = makeBar('cCH', [
  { label:'Compaction (psi)', data:[], backgroundColor:'#26a69a' },
  { label:'Height (mm)',      data:[], backgroundColor:'#fbc02d' }
]);
const cTR = makeBar('cTR', [
  { label:'Temperature °C', data:[], backgroundColor:'#fb8c00' },
  { label:'Rainfall mm',    data:[], backgroundColor:'#7e57c2' }
]);
const custSub = document.getElementById('custSub');
function loadCustSeries(){
  const sid = custSub.value;
  if (!sid) return;
  fetch(`readings_series.php?subscription_id=${encodeURIComponent(sid)}&limit=60`)
    .then(r=>r.json())
    .then(d=>{
      if (!d.ok) return;
      const L = d.labels;
      cMG.data.labels = L; cMG.data.datasets[0].data = d.moisture;   cMG.data.datasets[1].data = d.gmax;       cMG.update();
      cCH.data.labels = L; cCH.data.datasets[0].data = d.compaction; cCH.data.datasets[1].data = d.height;     cCH.update();
      cTR.data.labels = L; cTR.data.datasets[0].data = d.temperature;cTR.data.datasets[1].data = d.rainfall;   cTR.update();
    });
}
custSub.addEventListener('change', loadCustSeries);
setInterval(loadCustSeries, 12000);
</script>

</body>
</html>
