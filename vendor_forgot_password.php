<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password - FieldCraft</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
  :root{ --nav-h:72px; }
  @media (max-width: 991.98px){ :root{ --nav-h:60px; } }
  html, body { height:100%; }
  body { margin:0; padding-top: var(--nav-h); background:#0b1410; }
  .stadium-wrap{ min-height: calc(100vh - var(--nav-h)); position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center; background: radial-gradient(120% 80% at 50% 100%, #0f3d22 0%, #0b1410 55%, #08130c 100%); animation: bgPulse 10s ease-in-out infinite alternate; }
  @keyframes bgPulse{ 0%{background-size:100% 100%} 100%{background-size:120% 110%} }
  .fx-layer{ position:absolute; inset:0; display:block; }
  #stripeFx{ opacity:.22; } #sparkFx{ opacity:.85; pointer-events:none; } #linesFx{ opacity:.5; pointer-events:none; }

  .card-reset{ position:relative; z-index:3; width:100%; max-width:520px; border-radius:16px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.45); background:#ffffffee; backdrop-filter: blur(4px); }
  .card-reset .header{ background: linear-gradient(135deg,#16a34a,#059669); color:#fff; padding:18px 22px; }
  .position-relative .bi{ cursor:pointer; }
  .caps-hint{ display:none; color:#b91c1c; font-size:.85rem; }
</style>
</head>
<body>

<div class="stadium-wrap">
  <canvas id="stripeFx" class="fx-layer" width="1280" height="720"></canvas>
  <canvas id="sparkFx"  class="fx-layer" width="1280" height="720"></canvas>
  <canvas id="linesFx"  class="fx-layer" width="1280" height="720"></canvas>

  <div class="card card-reset">
    <div class="header d-flex align-items-center justify-content-between">
      <div class="fw-bold">FieldCraft • Reset Password</div>
      <div class="small">Vendor account</div>
    </div>

    <div class="p-4">
      <h3 class="text-center mb-3">Reset Password</h3>

      <form id="forgotPasswordForm" action="vendor_forgot_password_process.php" method="POST" novalidate>
        <div class="mb-3">
          <label for="email" class="form-label">Registered Email</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="example@example.com" required>
          <div class="text-danger small mt-1" id="emailError"></div>
        </div>

        <div class="mb-1 position-relative">
          <label for="password" class="form-label d-flex justify-content-between align-items-center">
            <span>New Password</span>
            <span class="text-success small">8+ chars • upper • number • symbol</span>
          </label>
          <input type="password" class="form-control" id="password" name="password" required>
          <i class="bi bi-eye-slash position-absolute top-50 end-0 translate-middle-y me-3" id="togglePassword"></i>
          <div class="text-danger small mt-1" id="passwordError"></div>
          <div class="caps-hint mt-1" id="capsHint1">Caps Lock is ON</div>
        </div>

        <div class="mb-3 position-relative">
          <label for="confirm_password" class="form-label">Retype New Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
          <i class="bi bi-eye-slash position-absolute top-50 end-0 translate-middle-y me-3" id="toggleConfirm"></i>
          <div class="text-danger small mt-1" id="confirmError"></div>
          <div class="caps-hint mt-1" id="capsHint2">Caps Lock is ON</div>
        </div>

        <button type="submit" class="btn btn-success w-100">Reset Password</button>

        <div class="text-center mt-3">
          <a href="signin.php">Back to Sign In</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function fit(){ const wrap=document.querySelector('.stadium-wrap'); ['stripeFx','sparkFx','linesFx'].forEach(id=>{const c=document.getElementById(id); c.width=wrap.clientWidth; c.height=wrap.clientHeight;});}
addEventListener('resize', fit); fit();

/* Stripes */
(function(){const c=stripeFx,ctx=c.getContext('2d');let t=0;(function draw(){const w=c.width,h=c.height;ctx.clearRect(0,0,w,h);for(let i=0;i<14;i++){const x=(w/14)*i+Math.sin(t+i*.5)*8;ctx.fillStyle=i%2?'rgba(255,255,255,.09)':'rgba(0,0,0,.12)';ctx.fillRect(x,0,Math.ceil(w/14),h);} t+=0.01; requestAnimationFrame(draw);})();})();
/* Glow */
(function(){const c=sparkFx,ctx=c.getContext('2d');const P=[];let mx=c.width/2,my=c.height/2;const COL=['#86efac','#34d399','#4ade80','#22c55e','#a7f3d0'];function seed(){P.length=0;for(let i=0;i<90;i++){P.push({x:Math.random()*c.width,y:Math.random()*c.height,vx:(Math.random()-0.5)*.4,vy:(Math.random()-0.5)*.25,s:1.5+Math.random()*2.5,c:COL[(Math.random()*COL.length)|0],t:Math.random()*Math.PI*2,sp:0.01+Math.random()*0.02});}} seed(); addEventListener('resize',seed); c.parentElement.addEventListener('mousemove',e=>{const r=c.getBoundingClientRect(); mx=e.clientX-r.left; my=e.clientY-r.top;},{passive:true}); (function draw(){const w=c.width,h=c.height;ctx.clearRect(0,0,w,h);P.forEach(p=>{const dx=mx-p.x,dy=my-p.y,d=Math.hypot(dx,dy)||1;const pull=d<220?(220-d)/220*.03:0; p.vx+=dx/d*pull; p.vy+=dy/d*pull*.01; p.vx*=.992; p.vy*=.992; p.t+=p.sp; p.x+=p.vx; p.y+=p.vy; if(p.x<-40)p.x=w+40;if(p.x>w+40)p.x=-40;if(p.y<-40)p.y=h+40;if(p.y>h+40)p.y=-40; ctx.save(); ctx.translate(p.x,p.y); ctx.rotate(p.t); ctx.globalAlpha=.7; ctx.fillStyle=p.c; ctx.beginPath(); ctx.ellipse(0,0,p.s*2,p.s,0,0,Math.PI*2); ctx.fill(); ctx.restore();}); requestAnimationFrame(draw);})();})();
/* Lines */
(function(){const c=linesFx,ctx=c.getContext('2d');function draw(){const w=c.width,h=c.height;ctx.clearRect(0,0,w,h);ctx.strokeStyle='rgba(240,255,244,.45)';ctx.lineWidth=Math.max(1.5,w*0.002);ctx.strokeRect(w*0.06,h*0.08,w*0.88,h*0.84);ctx.beginPath();ctx.moveTo(w/3,h*0.08);ctx.lineTo(w/3,h*0.92);ctx.stroke();ctx.beginPath();ctx.moveTo(2*w/3,h*0.08);ctx.lineTo(2*w/3,h*0.92);ctx.stroke();[w/6,w/2,5*w/6].forEach(cx=>{ctx.beginPath();ctx.arc(cx,h/2,h*0.08,0,Math.PI*2);ctx.stroke();});} draw(); addEventListener('resize',draw);})();

/* Validation and toggles (same logic you had) */
const emailInput=document.getElementById('email');
const passwordInput=document.getElementById('password');
const confirmInput=document.getElementById('confirm_password');

function validateEmail(){ const r=/^[^\s@]+@[^\s@]+\.[^\s@]+$/; const ok=r.test(emailInput.value.trim()); document.getElementById('emailError').textContent=ok?'':'Please enter a valid email.'; return ok; }
function validatePassword(){ const r=/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/; const ok=r.test(passwordInput.value); document.getElementById('passwordError').textContent=ok?'':'Password must be 8+ chars, include uppercase, number, and special character.'; return ok; }
function validateConfirm(){ const ok=passwordInput.value===confirmInput.value; document.getElementById('confirmError').textContent=ok?'':'Passwords do not match.'; return ok; }

emailInput.addEventListener('input', validateEmail);
passwordInput.addEventListener('input', validatePassword);
confirmInput.addEventListener('input', validateConfirm);

document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
  if (!validateEmail() | !validatePassword() | !validateConfirm()) e.preventDefault();
});

// Toggle visibility
document.getElementById('togglePassword').addEventListener('click', function() {
  const t = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
  passwordInput.setAttribute('type', t);
  this.classList.toggle('bi-eye'); this.classList.toggle('bi-eye-slash');
});
document.getElementById('toggleConfirm').addEventListener('click', function() {
  const t = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
  confirmInput.setAttribute('type', t);
  this.classList.toggle('bi-eye'); this.classList.toggle('bi-eye-slash');
});

// Caps lock hints
passwordInput.addEventListener('keyup', (e)=>{ document.getElementById('capsHint1').style.display = e.getModifierState && e.getModifierState('CapsLock') ? 'block':'none'; });
confirmInput.addEventListener('keyup', (e)=>{ document.getElementById('capsHint2').style.display = e.getModifierState && e.getModifierState('CapsLock') ? 'block':'none'; });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
