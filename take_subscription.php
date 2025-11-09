<?php
session_start();
include 'db.php';
unset($_SESSION['subscription_temp']);
?>
<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Take Subscription - FieldCraft</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
/* ===== Layout: clear fixed navbar ===== */
:root{
  --nav-h: 72px;          /* adjust if your nav height differs */
  --nav-gap: 20px;
  --fc-green:#4caf50; --fc-green-2:#81c784; --fc-sky:#d0efff;
}
@media (max-width: 991.98px){ :root{ --nav-h: 60px; } }
html,body{height:100%}
body{
  padding-top: calc(var(--nav-h) + var(--nav-gap));
  min-height:100vh; overflow-x:hidden;
  background: linear-gradient(to top, #98db9a 0%, #bfe7ae 35%, var(--fc-sky) 100%);
}
@media (prefers-reduced-motion: reduce){
  .field-bg *,.pitch-canvas,.price-badge{ animation: none !important; transition: none !important; }
}

/* ===== Field background ===== */
.field-bg{position:fixed;inset:0;z-index:-2;overflow:hidden;background:linear-gradient(to top,#8fd694 0%,#b4e0a1 40%,#d0efff 100%)}
.field-bg .grass{position:absolute;left:0;right:0;bottom:0;height:200px;background:linear-gradient(to top,var(--fc-green) 0%,var(--fc-green-2) 100%);overflow:hidden}
.field-bg .grass::before{content:"";position:absolute;inset:0;background:url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='400' height='140'><path fill='%234caf50' d='M0,140 C40,120 80,120 120,140 C160,160 200,160 240,140 C280,120 320,120 360,140 L400,140 L400,0 L0,0 Z'/></svg>") repeat-x bottom;animation:wave 9s linear infinite;opacity:.55}
@keyframes wave{from{background-position-x:0}to{background-position-x:400px}}
.field-bg .cloud{position:absolute;background:#fff;border-radius:50%;box-shadow:60px 20px 0 10px #fff,120px 25px 0 0 #fff,90px -10px 0 10px #fff,30px 10px 0 10px #fff;width:110px;height:65px;opacity:.85;filter:drop-shadow(0 6px 10px rgba(0,0,0,.06));animation:drift 55s linear infinite}
.field-bg .cloud.c1{top:90px;left:-160px;animation-delay:-8s}
.field-bg .cloud.c2{top:150px;left:-260px;width:140px;height:80px;animation-delay:-28s}
.field-bg .cloud.c3{top:60px;left:-420px;width:170px;height:90px;animation-delay:-18s}
@keyframes drift{from{transform:translateX(0)}to{transform:translateX(130vw)}}

/* ===== Card ===== */
.sub-card{
  background: rgba(255,255,255,.96);
  backdrop-filter: blur(8px);
  border: none; border-radius: 16px;
  box-shadow: 0 20px 40px rgba(0,0,0,.12);
}
h2.section-title{
  font-size: clamp(1.25rem, .9rem + 1.6vw, 1.75rem);
}

/* ===== Pitch preview (SVG in a canvas) ===== */
.pitch-wrap{ position: relative; border-radius: 12px; overflow: hidden; background: #2e7d32; }
.pitch-canvas{ width:100%; height:180px; display:block; }
.pitch-label{
  position:absolute; right:8px; bottom:8px; background:rgba(255,255,255,.85);
  padding:.25rem .5rem; border-radius:999px; font-weight:600; font-size:.9rem;
}

/* ===== Price display ===== */
.price-badge{
  display:inline-flex; align-items:center; gap:.5rem;
  padding:.5rem .9rem; border-radius:999px;
  background:#eefaf0; color:#166534; font-weight:700;
  box-shadow: inset 0 0 0 2px rgba(76,175,80,.15);
  transition: transform .15s ease;
}
.price-badge.bump{ transform: scale(1.05); }
.save-text{ font-size:.9rem; color:#0d6efd; font-weight:600; }

/* wiggle animation for invalid combo */
@keyframes wiggle{0%,100%{transform:translateX(0)}20%{transform:translateX(-5px)}40%{transform:translateX(5px)}60%{transform:translateX(-4px)}80%{transform:translateX(4px)}}
.wiggle{ animation: wiggle .4s linear; }

/* CTA */
.btn-checkout{ position:relative; overflow:hidden; border-radius:12px; }
.btn-checkout .mower{ position:absolute; left:-42px; bottom:-6px; font-size:22px; animation:mower 2s ease-in-out infinite; opacity:.9 }
@keyframes mower{0%{transform:translateX(0)}50%{transform:translateX(26px) rotate(-2deg)}100%{transform:translateX(0)}}
</style>
</head>
<body>

<!-- Background -->
<div class="field-bg" aria-hidden="true">
  <div class="cloud c1"></div>
  <div class="cloud c2"></div>
  <div class="cloud c3"></div>
  <div class="grass"></div>
</div>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card sub-card p-4">
        <h2 class="section-title text-center mb-3 text-success">Take Subscription</h2>

        <!-- Pitch preview -->
        <div class="pitch-wrap mb-4">
          <canvas id="pitch" class="pitch-canvas" width="900" height="300" aria-hidden="true"></canvas>
          <span class="pitch-label" id="pitchLabel">Football pitch</span>
        </div>

        <form id="subscriptionForm" action="check_email.php" method="GET" novalidate>
          <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" placeholder="example@example.com" required>
            <div class="invalid-feedback">Enter a valid email.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Select Ground</label>
            <select class="form-select" id="ground" name="ground" required>
              <option value="">-- Select Ground --</option>
              <option value="football">Football</option>
              <option value="rugby">Rugby</option>
              <option value="cricket">Cricket</option>
              <option value="fieldplan">Field Plan (3-in-1)</option>
            </select>
            <div class="invalid-feedback">Select a ground.</div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Select Plan</label>
              <select class="form-select" id="plan" name="plan" required>
                <option value="">-- Select Plan --</option>
                <option value="basic">Basic</option>
                <option value="standard">Standard</option>
                <option value="premium">Premium</option>
                <option value="elite">Elite</option>
              </select>
              <div class="form-text">Plans depend on ground – unavailable ones will be hidden.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Select Duration</label>
              <select class="form-select" id="duration" name="duration" required>
                <option value="">-- Select Duration --</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="annual">Annual</option>
              </select>
            </div>
          </div>

          <div class="text-center mb-3">
            <div class="price-badge" id="priceBadge">
              Total: <span id="totalPrice">£0</span>
            </div>
            <div class="save-text mt-1" id="saveHint" style="display:none;"></div>
          </div>

          <div class="d-grid">
            <button type="submit" class="btn btn-success btn-lg btn-checkout" data-confetti>
              <span class="mower">🚜</span>
              Checkout
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
/* ===== Pricing (kept exactly as your structure) ===== */
const pricing = {
  football:{basic:{weekly:120,monthly:400,annual:5760},
            standard:{weekly:180,monthly:650,annual:7800},
            premium:{weekly:300,monthly:900,annual:10800}},
  rugby:{basic:{weekly:140,monthly:550,annual:6600},
         standard:{weekly:180,monthly:720,annual:8640},
         premium:{weekly:250,monthly:1000,annual:12000}},
  cricket:{basic:{weekly:150,monthly:450,annual:5000},
           standard:{weekly:220,monthly:600,annual:7720},
           premium:{weekly:300,monthly:750,annual:10000}},
  fieldplan:{standard:{weekly:300,monthly:1200,annual:14400},
             premium:{weekly:410,monthly:1650,annual:19800},
             elite:{weekly:550,monthly:2200,annual:26400}}
};

const ground = document.getElementById('ground');
const plan = document.getElementById('plan');
const duration = document.getElementById('duration');
const totalPrice = document.getElementById('totalPrice');
const priceBadge = document.getElementById('priceBadge');
const saveHint = document.getElementById('saveHint');

/* ===== Pitch preview drawing ===== */
const pitchCanvas = document.getElementById('pitch');
const ctx = pitchCanvas.getContext('2d');
const label = document.getElementById('pitchLabel');

function drawPitch(type='football'){
  const w = pitchCanvas.width, h = pitchCanvas.height;
  ctx.clearRect(0,0,w,h);
  // grass
  ctx.fillStyle = '#2e7d32';
  ctx.fillRect(0,0,w,h);
  // mowing stripes
  for(let i=0;i<12;i++){
    ctx.fillStyle = i%2==0 ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.05)';
    ctx.fillRect((w/12)*i,0,w/12,h);
  }
  ctx.strokeStyle = '#ffffff';
  ctx.lineWidth = 3;

  if(type==='football'){
    label.textContent='Football pitch';
    // outer
    ctx.strokeRect(30,20,w-60,h-40);
    // halfway + center circle
    ctx.beginPath(); ctx.moveTo(w/2,20); ctx.lineTo(w/2,h-20); ctx.stroke();
    ctx.beginPath(); ctx.arc(w/2,h/2,50,0,Math.PI*2); ctx.stroke();
    // penalty boxes
    ctx.strokeRect(30, h/2-70, 120, 140);
    ctx.strokeRect(w-150, h/2-70, 120, 140);
  } else if(type==='rugby'){
    label.textContent='Rugby pitch';
    ctx.strokeRect(30,20,w-60,h-40);
    // try lines + halfway
    const lines=[w*.15,w*.5,w*.85];
    lines.forEach(x=>{ ctx.beginPath(); ctx.moveTo(x,20); ctx.lineTo(x,h-20); ctx.stroke(); });
    // goal posts
    ctx.lineWidth = 5;
    [w*.15,w*.85].forEach(x=>{
      ctx.beginPath(); ctx.moveTo(x, h/2-30); ctx.lineTo(x, h/2+30); ctx.stroke();
    });
  } else if(type==='cricket'){
    label.textContent='Cricket field';
    // big oval
    ctx.beginPath(); ctx.ellipse(w/2,h/2, w/2-40, h/2-40, 0, 0, Math.PI*2); ctx.stroke();
    // pitch strip
    ctx.fillStyle = '#b08968'; ctx.fillRect(w/2-70,h/2-12,140,24);
    ctx.strokeStyle = '#fff'; ctx.lineWidth=2;
    ctx.strokeRect(w/2-70,h/2-12,140,24);
    // inner circle
    ctx.beginPath(); ctx.arc(w/2,h/2, 80, 0, Math.PI*2); ctx.stroke();
  } else { // fieldplan (3-in-1)
    label.textContent='Field Plan (3-in-1)';
    // thirds
    ctx.strokeRect(30,20,w-60,h-40);
    ctx.beginPath(); ctx.moveTo(w/3,20); ctx.lineTo(w/3,h-20); ctx.stroke();
    ctx.beginPath(); ctx.moveTo(2*w/3,20); ctx.lineTo(2*w/3,h-20); ctx.stroke();
    // mini center circles
    ctx.beginPath(); ctx.arc(w/6,h/2,35,0,Math.PI*2); ctx.stroke();
    ctx.beginPath(); ctx.arc(w/2,h/2,35,0,Math.PI*2); ctx.stroke();
    ctx.beginPath(); ctx.arc(5*w/6,h/2,35,0,Math.PI*2); ctx.stroke();
  }
}
drawPitch('football');

/* ===== Options filtering ===== */
function syncPlans(){
  const g = ground.value;
  // allowed plans = keys of pricing[g]
  const allowed = g && pricing[g] ? Object.keys(pricing[g]) : [];
  // show/hide options
  Array.from(plan.options).forEach(opt=>{
    if(!opt.value) return;
    const ok = allowed.includes(opt.value);
    opt.hidden = !ok;
  });
  // if current plan invalid, reset
  if(plan.value && !allowed.includes(plan.value)){ plan.value = ""; wiggle(plan); }
}

function wiggle(el){ el.classList.remove('wiggle'); void el.offsetWidth; el.classList.add('wiggle'); setTimeout(()=>el.classList.remove('wiggle'),450); }

/* ===== Price update with counter animation & savings hint ===== */
function updatePrice(){
  const g=ground.value, p=plan.value, d=duration.value;
  const newPrice = pricing[g]?.[p]?.[d] || 0;
  const oldText = totalPrice.textContent.replace(/[£,]/g,'');
  const oldPrice = parseInt(oldText || '0', 10);

  // bump animation
  priceBadge.classList.remove('bump'); void priceBadge.offsetWidth; priceBadge.classList.add('bump');

  // animate counter
  const steps = 18, diff = newPrice - oldPrice;
  if(steps>0){
    let i=0;
    const tick = () => {
      i++;
      const val = Math.round(oldPrice + diff*(i/steps));
      totalPrice.textContent = "£" + (val).toLocaleString();
      if(i<steps) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  } else {
    totalPrice.textContent = "£" + newPrice.toLocaleString();
  }

  // savings hint: show monthly vs annual equivalence (if applicable)
  if(g && p && (d==='annual' || d==='monthly')){
    const monthly = pricing[g]?.[p]?.['monthly'];
    const annual = pricing[g]?.[p]?.['annual'];
    if(monthly && annual){
      const equiv = Math.round(annual / 12);
      if(d==='annual' && monthly > equiv){
        saveHint.style.display = '';
        saveHint.textContent = `Save about £${(monthly - equiv).toLocaleString()}/month vs monthly billing.`;
      } else if(d==='monthly' && annual){
        const perMonthAnnual = Math.round(annual/12);
        if(perMonthAnnual < monthly){
          saveHint.style.display = '';
          saveHint.textContent = `Annual averages £${perMonthAnnual.toLocaleString()}/month (you'd save ~£${(monthly - perMonthAnnual).toLocaleString()}/month).`;
        } else { saveHint.style.display='none'; }
      } else { saveHint.style.display='none'; }
    } else { saveHint.style.display='none'; }
  } else {
    saveHint.style.display='none';
  }
}

/* ===== Events ===== */
ground.addEventListener('change', () => {
  const g = ground.value || 'football';
  syncPlans();
  // auto-pick first available plan if none chosen
  if(!plan.value){
    const first = Array.from(plan.options).find(o=>o.value && !o.hidden);
    if(first) plan.value = first.value;
  }
  // default duration if empty
  if(!duration.value) duration.value = 'monthly';
  // draw pitch
  drawPitch(g==='fieldplan' ? 'fieldplan' : g);
  updatePrice();
});

[plan,duration].forEach(el => el.addEventListener('change', updatePrice));

/* init on load */
syncPlans(); updatePrice(); drawPitch('football');

/* ===== Form validation ===== */
document.getElementById('subscriptionForm').addEventListener('submit', e=>{
  const f = e.target;
  if(!f.checkValidity()){
    e.preventDefault(); e.stopPropagation();
    // friendly wiggle on the missing field(s)
    [ground,plan,duration].forEach(el => { if(!el.value) wiggle(el); });
  }
  f.classList.add('was-validated');
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
