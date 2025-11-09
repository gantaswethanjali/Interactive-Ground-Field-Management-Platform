<?php
session_start();
include 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $employee_number = trim($_POST['employee_number']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM technicians WHERE email = :email AND employee_number = :emp");
    $stmt->execute([':email' => $email, ':emp' => $employee_number]);
    $technician = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($technician && $technician['password'] === ($password)) {
        $_SESSION['tech_id']    = $technician['id'];
        $_SESSION['tech_email'] = $technician['email'];
        $_SESSION['tech_emp']   = $technician['employee_number'];
        $_SESSION['tech_name']  = $technician['name'] ?? $technician['email'];
        header("Location: technician_dashboard.php");
        exit();
    } else {
        $error = "Invalid email, employee number, or password.";
    }
}

include 'header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Technician Login - FieldCraft</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root{ --nav-h:72px; }
    @media (max-width: 991.98px){ :root{ --nav-h:60px; } }

    html, body { height:100%; }
    body { margin:0; padding-top: var(--nav-h); background:#0b1410; }

    /* Interactive backdrop container */
    .stadium-wrap{
      min-height: calc(100vh - var(--nav-h));
      position:relative;
      display:flex; align-items:center; justify-content:center;
      overflow:hidden;
      /* animated gradient so it never looks flat black */
      background: radial-gradient(120% 80% at 50% 100%, #0f3d22 0%, #0b1410 55%, #08130c 100%);
      animation: bgPulse 10s ease-in-out infinite alternate;
    }
    @keyframes bgPulse{
      0% { background-size:100% 100%; }
      100% { background-size:120% 110%; }
    }

    /* Canvas layers */
    .fx-layer{ position:absolute; inset:0; display:block; }
    #stripeFx{ opacity:.22; }
    #sparkFx{ opacity:.85; pointer-events:none; }

    .card-login{
      position:relative; z-index:3; width:100%; max-width:420px;
      border-radius:16px; overflow:hidden;
      box-shadow:0 20px 60px rgba(0,0,0,.45);
      background:#ffffffee; backdrop-filter: blur(4px);
    }
    .card-login .header{
      background: linear-gradient(135deg,#16a34a,#059669);
      color:#fff; padding:18px 22px;
    }
    .toggle-eye{ cursor:pointer; user-select:none; }
    .caps-hint{ display:none; color:#b91c1c; font-size:.85rem; }
  </style>
</head>
<body>
<div class="stadium-wrap">
  <!-- animated mowing stripes -->
  <canvas id="stripeFx" class="fx-layer" width="1280" height="720" aria-hidden="true"></canvas>
  <!-- interactive glow particles -->
  <canvas id="sparkFx"  class="fx-layer" width="1280" height="720" aria-hidden="true"></canvas>
  <!-- faint field lines -->
  <canvas id="linesFx"  class="fx-layer" width="1280" height="720" aria-hidden="true"></canvas>

  <div class="card card-login">
    <div class="header d-flex align-items-center justify-content-between">
      <div class="fw-bold">FieldCraft • Technician Login</div>
      <div class="small">Welcome back</div>
    </div>

    <div class="p-4">
      <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="" id="techForm" novalidate>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" required>
          <div class="invalid-feedback">Please enter a valid email.</div>
        </div>

        <div class="mb-3">
          <label for="employee_number" class="form-label">Employee Number</label>
          <input type="text" class="form-control" id="employee_number" name="employee_number" maxlength="6" required>
          <div class="invalid-feedback">Employee number is required.</div>
        </div>

        <div class="mb-1">
          <label for="password" class="form-label d-flex justify-content-between align-items-center">
            <span>Password</span>
            <span class="toggle-eye text-success" id="toggleEye" title="Show/Hide">👁️</span>
          </label>
          <input type="password" class="form-control" id="password" name="password" required>
          <div class="invalid-feedback">Password is required.</div>
          <div class="caps-hint mt-1" id="capsHint">Caps Lock is ON</div>
        </div>

        <div class="d-grid mt-3">
          <button type="submit" class="btn btn-success btn-lg">Login</button>
        </div>
      </form>

      <div class="text-center mt-3 small text-muted">
        Tip: Check Caps Lock and your employee number.
      </div>
    </div>
  </div>
</div>

<script>
/* Fit all canvases to container */
function fitCanvases(){
  const wrap = document.querySelector('.stadium-wrap');
  const W = wrap.clientWidth, H = wrap.clientHeight;
  ['stripeFx','sparkFx','linesFx'].forEach(id=>{
    const c = document.getElementById(id);
    c.width = W; c.height = H;
  });
}
addEventListener('resize', fitCanvases); fitCanvases();

/* Stripes */
(function(){
  const c = document.getElementById('stripeFx'), ctx = c.getContext('2d');
  let t=0;
  function draw(){
    const w=c.width, h=c.height;
    ctx.clearRect(0,0,w,h);
    for(let i=0;i<14;i++){
      const x = (w/14)*i + Math.sin(t + i*.5)*8;
      ctx.fillStyle = i%2? 'rgba(255,255,255,.09)' : 'rgba(0,0,0,.12)';
      ctx.fillRect(x, 0, Math.ceil(w/14), h);
    }
    t+=0.01; requestAnimationFrame(draw);
  }
  draw();
})();

/* Glow particles that follow mouse */
(function(){
  const c = document.getElementById('sparkFx'), ctx = c.getContext('2d');
  const parts = [];
  let mx = c.width/2, my = c.height/2;
  const COLORS = ['#86efac','#34d399','#4ade80','#22c55e','#a7f3d0'];
  function seed(){
    parts.length = 0;
    for(let i=0;i<80;i++){
      parts.push({
        x: Math.random()*c.width, y: Math.random()*c.height,
        vx: (Math.random()-0.5)*0.4, vy:(Math.random()-0.5)*0.25,
        s: 1.5 + Math.random()*2.5, c: COLORS[(Math.random()*COLORS.length)|0],
        t: Math.random()*Math.PI*2, sp: 0.01 + Math.random()*0.02
      });
    }
  }
  seed(); addEventListener('resize', seed);
  c.parentElement.addEventListener('mousemove', (e)=>{
    const r = c.getBoundingClientRect();
    mx = e.clientX - r.left; my = e.clientY - r.top;
  }, {passive:true});

  function draw(){
    const w=c.width, h=c.height;
    ctx.clearRect(0,0,w,h);
    parts.forEach(p=>{
      const dx=mx-p.x, dy=my-p.y, d=Math.hypot(dx,dy)||1;
      const pull = d<200 ? (200-d)/200*0.03 : 0;
      p.vx += dx/d*pull; p.vy += dy/d*pull*0.01;
      p.vx *= 0.992; p.vy *= 0.992; p.t += p.sp;
      p.x += p.vx; p.y += p.vy;
      if(p.x < -40) p.x = w+40; if(p.x > w+40) p.x = -40;
      if(p.y < -40) p.y = h+40; if(p.y > h+40) p.y = -40;

      ctx.save();
      ctx.translate(p.x,p.y); ctx.rotate(p.t);
      ctx.globalAlpha = 0.7;
      ctx.fillStyle = p.c;
      ctx.beginPath(); ctx.ellipse(0,0, p.s*2, p.s, 0, 0, Math.PI*2); ctx.fill();
      ctx.restore();
    });
    requestAnimationFrame(draw);
  }
  draw();
})();

/* Faint field markings */
(function(){
  const c = document.getElementById('linesFx'), ctx = c.getContext('2d');
  function render(){
    const w=c.width, h=c.height;
    ctx.clearRect(0,0,w,h);
    ctx.strokeStyle='rgba(240,255,244,.45)';
    ctx.lineWidth = Math.max(1.5, w*0.002);
    // boundary
    ctx.strokeRect(w*0.05, h*0.08, w*0.90, h*0.84);
    // halfway & center circle
    ctx.beginPath(); ctx.moveTo(w/2, h*0.08); ctx.lineTo(w/2, h*0.92); ctx.stroke();
    ctx.beginPath(); ctx.arc(w/2, h/2, h*0.09, 0, Math.PI*2); ctx.stroke();
  }
  render(); addEventListener('resize', render);
})();

/* Form UX */
(function(){
  const form = document.getElementById('techForm');
  const emp  = document.getElementById('employee_number');
  const pass = document.getElementById('password');
  const eye  = document.getElementById('toggleEye');
  const caps = document.getElementById('capsHint');

  emp.addEventListener('input', ()=>{ emp.value = emp.value.replace(/[^a-z0-9]/gi,'').slice(0,6); });
  eye.addEventListener('click', ()=>{
    const t = pass.type==='password' ? 'text' : 'password';
    pass.type = t; eye.textContent = t==='text' ? '🙈' : '👁️';
  });
  pass.addEventListener('keyup', (e)=>{ caps.style.display = e.getModifierState && e.getModifierState('CapsLock') ? 'block':'none'; });
  form.addEventListener('submit', (e)=>{
    ['email','employee_number','password'].forEach(id=>{
      const el=document.getElementById(id); if(el && typeof el.value==='string') el.value = el.value.trim();
    });
    if(!form.checkValidity()){ e.preventDefault(); e.stopPropagation(); form.classList.add('was-validated'); }
  });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
