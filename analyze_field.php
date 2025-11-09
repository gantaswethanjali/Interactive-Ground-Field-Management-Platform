<?php
// analyze_field.php
session_start();

// ===== Helper: call local analyzer (no API changes) =====
function call_local_analyzer($abs_path) {
    $img_data = @file_get_contents($abs_path);
    if ($img_data === false) return ['ok'=>false, 'caption'=>'Could not read the uploaded file.'];

    $payload = json_encode(["image" => "data:image/jpeg;base64," . base64_encode($img_data)]);
    $ch = curl_init("http://localhost:5000/analyze");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 7,           // graceful timeout (frontend will still show the image)
        CURLOPT_CONNECTTIMEOUT => 3
    ]);
    $response = curl_exec($ch);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['ok'=>false, 'caption'=>"Analyzer unreachable. ($err)"];
    }
    $result  = json_decode($response, true);
    $caption = $result['caption'] ?? 'Analysis failed.';
    return ['ok'=>true, 'caption'=>$caption];
}

// ===== Handle POST (upload + analyze) =====
$result  = null;
$webPath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['field_image'])) {
    $file = $_FILES['field_image'];
    $upload_dir_abs = __DIR__ . '/uploads/';
    $upload_dir_web = 'uploads/';

    if (!is_dir($upload_dir_abs)) @mkdir($upload_dir_abs, 0777, true);

    // simple sanitize
    $base = preg_replace('/[^\w.\- ]+/', '_', basename($file['name']));
    $target_abs = $upload_dir_abs . time() . '_' . $base;
    $webPath    = $upload_dir_web . basename($target_abs);

    // store first
    @move_uploaded_file($file['tmp_name'], $target_abs);

    // call analyzer (same endpoint)
    $result = call_local_analyzer($target_abs);
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Field Image Analysis (Local) - FieldCraft</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
:root{ --nav-h:72px; }
@media (max-width: 991.98px){ :root{ --nav-h:60px; } }

/* Remove any visual gap and keep content below the fixed header */
html, body { height: 100%; }
body { margin:0; padding-top: var(--nav-h); background:#0b1410; color:#0f172a; }

.page-wrap { min-height: calc(100vh - var(--nav-h)); background:#f7faf9; }

.card-rounded{ border-radius:16px; }

/* Drag & drop uploader */
.uploader {
  position: relative;
  border: 2px dashed #9ca3af;
  border-radius: 14px;
  background: #ffffff;
  padding: 18px;
  transition: border-color .2s, background .2s, box-shadow .2s, transform .12s;
}
.uploader.dragover {
  border-color: #10b981;
  background: #effcf6;
  box-shadow: 0 0 0 4px rgba(16,185,129,.12) inset;
  transform: translateY(-1px);
}
.uploader input[type=file]{
  position:absolute; inset:0; opacity:0; cursor:pointer;
}
.preview {
  display:none; margin-top:10px;
  border-radius: 10px; overflow:hidden; border:1px solid #e5e7eb;
}
.preview img{ width:100%; height:320px; object-fit:cover; display:block; }

/* Progress (UX only, native POST) */
.progress-wrap { display:none; }
.progress { height: 6px; border-radius: 999px; }

/* Result card confetti */
.result-card{ position:relative; overflow:hidden; }
#confettiFX{ position:absolute; inset:0; pointer-events:none; display:none; }

.badge-soft {
  background:#eaf7ef; color:#166534; border:1px solid #86efac;
  border-radius:999px; padding:.2rem .6rem; font-size:.85rem;
}
</style>
</head>
<body>
<div class="page-wrap">
  <div class="container py-4">

    <div class="card card-rounded shadow p-4 mb-4">
      <h2 class="mb-1">Upload Field Image for Local AI Analysis</h2>
      <div class="text-muted mb-3">Works with your local analyzer at <code>http://localhost:5000/analyze</code>. No cloud required.</div>

      <form method="POST" enctype="multipart/form-data" id="analyzeForm" novalidate>
        <div class="row g-3">
          <div class="col-lg-7">
            <div class="uploader" id="dropArea">
              <input type="file" name="field_image" id="fileInput" accept="image/*" required>
              <div class="text-center py-3">
                <div class="mb-1"><strong>Drag & drop</strong> a field photo here, or <u>click to choose</u>.</div>
                <div class="text-muted">JPG / PNG / WebP recommended • Max few MB for speed</div>
                <div id="fileName" class="small mt-1 text-muted"></div>
              </div>
            </div>

            <div class="preview" id="previewBox">
              <img id="previewImg" alt="Preview">
            </div>

            <div class="progress-wrap mt-3" id="progressWrap">
              <div class="progress">
                <div class="progress-bar bg-success" id="progressBar" role="progressbar" style="width:0%"></div>
              </div>
              <div class="small text-muted mt-1" id="progressText">Preparing upload…</div>
            </div>
          </div>

          <div class="col-lg-5">
            <div class="border rounded p-3 h-100 bg-white">
              <h6 class="mb-2">Tips for best results</h6>
              <ul class="small mb-2">
                <li>Stand high enough to capture a broad part of the pitch.</li>
                <li>Avoid heavy shadows; overcast light works great.</li>
                <li>Keep the frame steady to reduce motion blur.</li>
              </ul>
              <div class="small text-muted">This page never stores card details (we never ask for them).</div>
              <button type="submit" class="btn btn-success w-100 mt-3" id="analyzeBtn">Analyze Field</button>
            </div>
          </div>
        </div>
      </form>
    </div>

    <?php if ($result): ?>
    <div class="card card-rounded shadow p-4 result-card">
      <canvas id="confettiFX"></canvas>
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h4 class="mb-0">AI Analysis Result</h4>
        <span class="badge-soft"><?= $result['ok'] ? 'Local AI ✓' : 'Offline / Fallback' ?></span>
      </div>
      <div class="row g-3">
        <div class="col-lg-6">
          <?php if ($webPath): ?>
            <img src="<?= htmlspecialchars($webPath) ?>" alt="Uploaded Field" class="img-fluid rounded border">
          <?php endif; ?>
        </div>
        <div class="col-lg-6">
          <div class="border rounded p-3 bg-white">
            <pre class="m-0" style="white-space:pre-wrap; font-size:0.95rem;"><?= htmlspecialchars($result['caption']) ?></pre>
          </div>
          <div class="small text-muted mt-2">If your analyzer service is offline, you’ll see a fallback note above.</div>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<script>
// ===== Drag & drop + preview (front-end only) =====
(function(){
  const drop  = document.getElementById('dropArea');
  const input = document.getElementById('fileInput');
  const name  = document.getElementById('fileName');
  const pBox  = document.getElementById('previewBox');
  const pImg  = document.getElementById('previewImg');

  function showFile(f){
    if(!f){ name.textContent = ''; pBox.style.display='none'; return; }
    name.textContent = `Selected: ${f.name} (${Math.round(f.size/1024)} KB)`;
    const url = URL.createObjectURL(f);
    pImg.src = url; pBox.style.display='block';
  }

  ['dragover','dragenter'].forEach(ev => drop.addEventListener(ev, e=>{e.preventDefault(); drop.classList.add('dragover');}));
  ;['dragleave','drop'].forEach(ev => drop.addEventListener(ev, e=>{e.preventDefault(); drop.classList.remove('dragover');}));
  drop.addEventListener('drop', e=>{
    const f = e.dataTransfer.files?.[0];
    if(!f) return;
    if(!f.type.startsWith('image/')) return alert('Please choose an image file.');
    input.files = e.dataTransfer.files;
    showFile(f);
  });
  input.addEventListener('change', ()=> showFile(input.files?.[0]));
})();

// ===== Fake progress while native POST happens =====
(function(){
  const form = document.getElementById('analyzeForm');
  const bar  = document.getElementById('progressBar');
  const wrap = document.getElementById('progressWrap');
  const txt  = document.getElementById('progressText');
  const btn  = document.getElementById('analyzeBtn');

  form.addEventListener('submit', function(e){
    const f = document.getElementById('fileInput').files?.[0];
    if(!f){ e.preventDefault(); alert('Please choose a field image.'); return; }
    if(!f.type.startsWith('image/')){ e.preventDefault(); alert('Only image files are allowed.'); return; }

    wrap.style.display = 'block';
    btn.disabled = true;

    let p = 0;
    const t = setInterval(()=>{
      p = Math.min(95, p + 5 + Math.random()*8);
      bar.style.width = p + '%';
      txt.textContent = 'Analyzing on local server…';
    }, 140);
    window.addEventListener('beforeunload', ()=> clearInterval(t));
  });
})();
</script>

<?php if ($result && $result['ok']): ?>
<script>
// ===== Small confetti burst on success (front-end only) =====
(function(){
  const cvs = document.getElementById('confettiFX');
  if(!cvs) return;
  const card = cvs.parentElement;
  function fit(){ cvs.width = card.clientWidth; cvs.height = card.clientHeight; }
  fit(); addEventListener('resize', fit);
  const cx = cvs.getContext('2d');
  const bits = Array.from({length: 100}, ()=>({
    x: Math.random()*cvs.width, y: -20 - Math.random()*120,
    vx: (Math.random()-0.5)*2, vy: 1+Math.random()*2,
    s: 2+Math.random()*3, a: 1, hue: 110+Math.random()*80
  }));
  cvs.style.display='block';
  const t0 = performance.now();
  (function loop(now){
    cx.clearRect(0,0,cvs.width,cvs.height);
    bits.forEach(b=>{
      b.x += b.vx; b.y += b.vy; b.vy += 0.02; b.a *= 0.986;
      cx.fillStyle = `hsla(${b.hue},70%,55%,${b.a})`;
      cx.fillRect(b.x, b.y, b.s, b.s);
    });
    if(now - t0 < 1500) requestAnimationFrame(loop);
    else cvs.style.display='none';
  })(t0);
})();
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
