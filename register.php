<?php
session_start();
include 'header.php';

$prefill_email = $_GET['email'] ?? '';
$redirect = $_SESSION['redirect_after_login'] ?? '';
$errors = json_decode($_GET['errors'] ?? '{}', true);
$success = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Register - FieldCraft</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  :root{ --nav-h:72px; }
  @media (max-width: 991.98px){ :root{ --nav-h:60px; } }
  html, body { height:100%; }
  body { margin:0; padding-top: var(--nav-h); background:#0b1410; }
  .stadium-wrap{ min-height: calc(100vh - var(--nav-h)); position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center; background: radial-gradient(120% 80% at 50% 100%, #0f3d22 0%, #0b1410 55%, #08130c 100%); animation: bgPulse 10s ease-in-out infinite alternate; }
  @keyframes bgPulse{ 0%{background-size:100% 100%} 100%{background-size:120% 110%} }
  .fx-layer{ position:absolute; inset:0; display:block; }
  #stripeFx{ opacity:.22; } #sparkFx{ opacity:.85; pointer-events:none; } #linesFx{ opacity:.5; pointer-events:none; }

  .card-register{ position:relative; z-index:3; width:100%; max-width:520px; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.45); background:#ffffffee; backdrop-filter: blur(4px); }
  .card-register .header{ background: linear-gradient(135deg,#16a34a,#059669); color:#fff; padding:18px 22px; }
  .toggle-eye{ cursor:pointer; user-select:none; }
  .caps-hint{ display:none; color:#b91c1c; font-size:.85rem; }
  .strength{ height:6px; border-radius:999px; background:#e5e7eb; overflow:hidden; }
  .strength > div{ height:100%; width:0%; transition: width .25s ease; background: linear-gradient(90deg,#ef4444,#f59e0b,#10b981); }
</style>
</head>
<body>
<div class="stadium-wrap">
  <canvas id="stripeFx" class="fx-layer" width="1280" height="720"></canvas>
  <canvas id="sparkFx"  class="fx-layer" width="1280" height="720"></canvas>
  <canvas id="linesFx"  class="fx-layer" width="1280" height="720"></canvas>

  <div class="card card-register">
    <div class="header d-flex align-items-center justify-content-between">
      <div class="fw-bold">FieldCraft • Register</div>
      <div class="small">Create your account</div>
    </div>

    <div class="p-4">
      <h3 class="text-center mb-3">Register</h3>

      <?php if(!empty($success)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form action="register_process.php" method="POST" id="registerForm" novalidate>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" id="email" name="email" value="<?= htmlspecialchars($prefill_email) ?>" required>
          <div class="invalid-feedback"><?= $errors['email'] ?? 'Enter a valid email.' ?></div>
        </div>

        <div class="mb-3">
          <label for="phone" class="form-label">Phone</label>
          <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>" required>
          <div class="invalid-feedback"><?= $errors['phone'] ?? 'Enter a valid phone number.' ?></div>
        </div>

        <div class="mb-1">
          <label for="password" class="form-label d-flex justify-content-between align-items-center">
            <span>Password</span>
            <span class="toggle-eye text-success" id="togglePass" title="Show/Hide">👁️</span>
          </label>
          <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" id="password" name="password" required>
          <div class="invalid-feedback"><?= $errors['password'] ?? 'Enter a valid password.' ?></div>
          <div class="caps-hint mt-1" id="capsHint">Caps Lock is ON</div>
          <div class="strength mt-2"><div id="strengthBar"></div></div>
          <div class="small text-muted mt-1" id="strengthText">Use 8+ chars, with upper, number & symbol.</div>
        </div>

        <div class="mb-3">
          <label for="confirm_password" class="form-label d-flex justify-content-between align-items-center">
            <span>Confirm Password</span>
            <span class="toggle-eye text-success" id="toggleConfirm" title="Show/Hide">👁️</span>
          </label>
          <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" id="confirm_password" name="confirm_password" required>
          <div class="invalid-feedback"><?= $errors['confirm_password'] ?? 'Confirm your password.' ?></div>
        </div>

        <div class="d-grid mt-2">
          <button type="submit" class="btn btn-success btn-lg">Register</button>
        </div>
      </form>

      <div class="mt-3 text-center">
        Already have an account? <a href="signin.php">Sign In</a>
      </div>
    </div>
  </div>
</div>

<script>
function fit(){ const wrap=document.querySelector('.stadium-wrap'); ['stripeFx','sparkFx','linesFx'].forEach(id=>{const c=document.getElementById(id); c.width=wrap.clientWidth; c.height=wrap.clientHeight;});}
addEventListener('resize', fit); fit();

/* Stripes */
(function(){const c=stripeFx,ctx=c.getContext('2d');let t=0; (function draw(){const w=c.width,h=c.height;ctx.clearRect(0,0,w,h);for(let i=0;i<14;i++){const x=(w/14)*i+Math.sin(t+i*.5)*8;ctx.fillStyle=i%2?'rgba(255,255,255,.09)':'rgba(0,0,0,.12)';ctx.fillRect(x,0,Math.ceil(w/14),h);} t+=0.01; requestAnimationFrame(draw);})();})();
/* Glow */
(function(){const c=sparkFx,ctx=c.getContext('2d');const P=[];let mx=c.width/2,my=c.height/2;const COL=['#86efac','#34d399','#4ade80','#22c55e','#a7f3d0'];function seed(){P.length=0;for(let i=0;i<90;i++){P.push({x:Math.random()*c.width,y:Math.random()*c.height,vx:(Math.random()-0.5)*.4,vy:(Math.random()-0.5)*.25,s:1.5+Math.random()*2.5,c:COL[(Math.random()*COL.length)|0],t:Math.random()*Math.PI*2,sp:0.01+Math.random()*0.02});}} seed(); addEventListener('resize',seed); c.parentElement.addEventListener('mousemove',e=>{const r=c.getBoundingClientRect(); mx=e.clientX-r.left; my=e.clientY-r.top;},{passive:true}); (function draw(){const w=c.width,h=c.height;ctx.clearRect(0,0,w,h);P.forEach(p=>{const dx=mx-p.x,dy=my-p.y,d=Math.hypot(dx,dy)||1;const pull=d<220?(220-d)/220*.03:0; p.vx+=dx/d*pull; p.vy+=dy/d*pull*.01; p.vx*=.992; p.vy*=.992; p.t+=p.sp; p.x+=p.vx; p.y+=p.vy; if(p.x<-40)p.x=w+40;if(p.x>w+40)p.x=-40;if(p.y<-40)p.y=h+40;if(p.y>h+40)p.y=-40; ctx.save(); ctx.translate(p.x,p.y); ctx.rotate(p.t); ctx.globalAlpha=.7; ctx.fillStyle=p.c; ctx.beginPath(); ctx.ellipse(0,0,p.s*2,p.s,0,0,Math.PI*2); ctx.fill(); ctx.restore();}); requestAnimationFrame(draw);})();})();
/* Lines */
(function(){const c=linesFx,ctx=c.getContext('2d');function draw(){const w=c.width,h=c.height;ctx.clearRect(0,0,w,h);ctx.strokeStyle='rgba(240,255,244,.45)';ctx.lineWidth=Math.max(1.5,w*0.002);ctx.strokeRect(w*0.06,h*0.08,w*0.88,h*0.84);ctx.beginPath();ctx.moveTo(w/3,h*0.08);ctx.lineTo(w/3,h*0.92);ctx.stroke();ctx.beginPath();ctx.moveTo(2*w/3,h*0.08);ctx.lineTo(2*w/3,h*0.92);ctx.stroke();[w/6,w/2,5*w/6].forEach(cx=>{ctx.beginPath();ctx.arc(cx,h/2,h*0.08,0,Math.PI*2);ctx.stroke();});} draw(); addEventListener('resize',draw);})();

/* Form UX */
(function(){
  const form=document.getElementById('registerForm');
  const pass=document.getElementById('password');
  const conf=document.getElementById('confirm_password');
  const eyeP=document.getElementById('togglePass');
  const eyeC=document.getElementById('toggleConfirm');
  const caps=document.getElementById('capsHint');
  const bar=document.getElementById('strengthBar');
  const txt=document.getElementById('strengthText');
  const phone=document.getElementById('phone');

  function score(v){ let s=0; if(v.length>=8)s++; if(/[A-Z]/.test(v))s++; if(/\d/.test(v))s++; if(/[!@#$%^&*()_\-+=[\]{};:'",.<>/?\\|`~]/.test(v))s++; if(v.length>=12)s++; return Math.min(4,s); }
  function setBar(n){ const pct=[0,30,55,80,100][n]; bar.style.width=pct+'%'; const labels=['Too weak','Weak','Okay','Strong','Great']; txt.textContent=labels[n]+' • Use 8+ chars, with upper, number & symbol.'; }
  pass.addEventListener('input',()=> setBar(score(pass.value)));
  pass.addEventListener('keyup',(e)=>{ caps.style.display=e.getModifierState&&e.getModifierState('CapsLock')?'block':'none'; });
  eyeP.addEventListener('click',()=>{ const t=pass.type==='password'?'text':'password'; pass.type=t; eyeP.textContent=t==='text'?'🙈':'👁️'; });
  eyeC.addEventListener('click',()=>{ const t=conf.type==='password'?'text':'password'; conf.type=t; eyeC.textContent=t==='text'?'🙈':'👁️'; });
  phone.addEventListener('input',()=>{ phone.value=phone.value.replace(/[^\d+\s()-]/g,'').slice(0,20); });
  form.addEventListener('submit',(e)=>{ if(pass.value!==conf.value){ conf.setCustomValidity('Passwords do not match'); } else { conf.setCustomValidity(''); } if(!form.checkValidity()){ e.preventDefault(); e.stopPropagation(); form.classList.add('was-validated'); }});
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
