<?php include 'header.php'; ?>

<style>
:root{
  --nav-h: 72px;
}
@media (max-width: 991.98px){ :root{ --nav-h: 60px; } }

/* Clear fixed navbar */
html, body { height: 100%; }
body { margin:0; padding-top: var(--nav-h); background:#0b1410; }

/* Full-bleed interactive hero */
.hero {
  position: relative;
  height: 82vh;
  min-height: 520px;
  isolation: isolate;
  overflow: hidden;
  border-bottom-left-radius: 16px;
  border-bottom-right-radius: 16px;
  background: radial-gradient(80% 60% at 50% 100%, #093418 0%, #0b1410 60%);
}

/* Canvas layers */
#pitchFx, #sparkFx { position:absolute; inset:0; display:block; }
#sparkFx { pointer-events:none; z-index:2; }
.pitch-overlay { position:absolute; inset:0; z-index:1; pointer-events:none;
  background: linear-gradient(to top, rgba(0,0,0,.35), rgba(0,0,0,.15) 30%, rgba(0,0,0,.35) 85%);
}

/* Caption (no buttons) */
.hero-caption { position:absolute; z-index:3; left:50%; transform:translateX(-50%); bottom:9vh; text-align:center; }
.hero-title   { color:#ecfdf5; font-weight:800; letter-spacing:.5px; text-shadow:0 6px 24px rgba(0,0,0,.4);
  font-size: clamp(1.8rem, 1rem + 3vw, 3rem); margin-bottom:.25rem; }
.hero-sub     { color:#bbf7d0; font-size: clamp(1rem, .9rem + 1vw, 1.35rem); min-height:1.6em; }
.typer { border-right:2px solid rgba(255,255,255,.75); padding-right:2px; }

/* Tiny mode indicator pill */
.mode-pill { position:absolute; z-index:3; top:14px; right:14px; background:rgba(255,255,255,.1);
  color:#e2e8f0; border:1px solid rgba(255,255,255,.2); padding:.35rem .6rem; border-radius:999px; font-size:.85rem; }

/* Contact – high contrast + tidy */
#contact-us { background: linear-gradient(180deg, #ffffff 0%, #f3faf5 100%); color:#0f172a !important; }
#contact-us h2 { color:#065f46 !important; }
#contact-us p  { color:#0f172a !important; }
#contact-us a  { color:#065f46 !important; text-decoration:none; }
#contact-us a:hover { color:#059669 !important; text-decoration:underline; }
</style>

<section class="hero">
  <!-- Dynamic pitch canvas -->
  <canvas id="pitchFx" width="1280" height="720" aria-hidden="true"></canvas>
  <!-- Sparkle particles layer -->
  <canvas id="sparkFx" width="1280" height="720" aria-hidden="true"></canvas>
  <!-- Subtle gradient for contrast -->
  <div class="pitch-overlay"></div>

  <div class="mode-pill" id="modePill">Mode: Football</div>

  <!-- Overlay caption (no buttons) -->
  <div class="hero-caption">
    <div class="hero-title" id="heroTitle">Ground</div>
    <div class="hero-sub">
      <span>Care For Square • </span><span class="typer" id="typer"></span>
    </div>
  </div>
</section>

<section id="contact-us" class="text-center">
  <div class="container py-5">
    <h2 class="mb-3">Contact Us</h2>
    <p class="mb-1">📞 +1 234 567 890</p>
    <p>✉️ admin@gmail.com</p>
    <div class="mt-3">
      <a href="#" class="me-3">Facebook</a>
      <a href="#" class="me-3">Twitter/X</a>
      <a href="#">Instagram</a>
    </div>
  </div>
</section>

<script>
/* =========================
   Interactive Pitch Renderer
   (mode rotation fixed)
   ========================= */
(function(){
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const hero  = document.querySelector('.hero');
  const pitch = document.getElementById('pitchFx');
  const spark = document.getElementById('sparkFx');
  const ctx   = pitch.getContext('2d');
  const spx   = spark.getContext('2d');
  const pill  = document.getElementById('modePill');

  // Fit canvases to hero
  function fit(){
    const r = hero.getBoundingClientRect();
    pitch.width = spark.width = Math.max(960, r.width|0);
    pitch.height = spark.height = Math.max(420, r.height|0);
  }
  fit(); addEventListener('resize', fit);

  // Modes (auto-rotate) — independent from RAF so it works even with reduced motion
  const MODES = ['Football','Rugby','Cricket','Multi-Sport'];
  let modeIndex = 0;
  const MODE_INTERVAL = 5500; // ms

  function rotateMode(){
    modeIndex = (modeIndex + 1) % MODES.length;
    pill.textContent = 'Mode: ' + MODES[modeIndex];
    // force an immediate redraw when reduced motion is on
    drawFrame();
  }
  // Start rotation timer regardless of motion prefs
  setInterval(rotateMode, MODE_INTERVAL);

  // Mouse parallax target
  let mx = pitch.width/2, my = pitch.height/2;
  hero.addEventListener('mousemove', (e)=>{
    const r = hero.getBoundingClientRect();
    mx = e.clientX - r.left; my = e.clientY - r.top;
  }, {passive:true});

  // Windy stripe phase for mowing effect
  let stripePhase = 0;

  function drawStripes(w, h){
    // animated mowing stripes (slower if reduced)
    stripePhase += reduce ? 0.0015 : 0.004;
    const bands = 12;
    for(let i=0;i<bands;i++){
      const col = i%2===0 ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.06)';
      ctx.fillStyle = col;
      const bandX = Math.floor((w/bands)*i + Math.sin(stripePhase + i*0.4)*(reduce ? 2 : 6));
      ctx.fillRect(bandX, 0, Math.ceil(w/bands), h);
    }
  }

  function drawOutline(w, h){
    ctx.strokeStyle = '#f0fff4';
    ctx.lineWidth = Math.max(2, w*0.0023);
    ctx.strokeRect(w*0.04, h*0.06, w*0.92, h*0.88);
  }

  function drawFootball(w, h){
    drawOutline(w,h);
    // half-way line + circle
    ctx.beginPath(); ctx.moveTo(w/2, h*0.06); ctx.lineTo(w/2, h*0.94); ctx.stroke();
    ctx.beginPath(); ctx.arc(w/2, h/2, h*0.08, 0, Math.PI*2); ctx.stroke();
    // penalty boxes
    ctx.strokeRect(w*0.04, h/2 - h*0.15, w*0.12, h*0.30);
    ctx.strokeRect(w*0.84, h/2 - h*0.15, w*0.12, h*0.30);
  }

  function drawRugby(w,h){
    drawOutline(w,h);
    // try lines + halfway
    const lines = [w*0.19, w*0.5, w*0.81];
    lines.forEach(x=>{ ctx.beginPath(); ctx.moveTo(x, h*0.06); ctx.lineTo(x, h*0.94); ctx.stroke(); });
    // simple posts
    ctx.lineWidth *= 1.2;
    [w*0.19, w*0.81].forEach(x=>{
      ctx.beginPath(); ctx.moveTo(x, h*0.5 - h*0.06); ctx.lineTo(x, h*0.5 + h*0.06); ctx.stroke();
    });
  }

  function drawCricket(w,h){
    // ellipse boundary
    ctx.beginPath();
    ctx.ellipse(w/2, h/2, w*0.46, h*0.40, 0, 0, Math.PI*2);
    ctx.stroke();
    // pitch strip
    ctx.fillStyle = '#b08968';
    ctx.fillRect(w/2 - w*0.09, h/2 - h*0.02, w*0.18, h*0.04);
    ctx.strokeRect(w/2 - w*0.09, h/2 - h*0.02, w*0.18, h*0.04);
    // inner ring
    ctx.beginPath(); ctx.arc(w/2, h/2, h*0.12, 0, Math.PI*2); ctx.stroke();
  }

  function drawMulti(w,h){
    drawOutline(w,h);
    // thirds
    ctx.beginPath(); ctx.moveTo(w/3, h*0.06); ctx.lineTo(w/3, h*0.94); ctx.stroke();
    ctx.beginPath(); ctx.moveTo(2*w/3, h*0.06); ctx.lineTo(2*w/3, h*0.94); ctx.stroke();
    // mini centers
    [w/6, w/2, 5*w/6].forEach(cx0=>{
      ctx.beginPath(); ctx.arc(cx0, h/2, h*0.06, 0, Math.PI*2); ctx.stroke();
    });
  }

  // spark particles
  const N = 90, parts = [];
  const COLORS = ['#86efac','#34d399','#4ade80','#22c55e','#16a34a'];
  function seedParticles(){
    parts.length = 0;
    const count = reduce ? 35 : N;
    for(let i=0;i<count;i++){
      parts.push({
        x: Math.random()*pitch.width, y: Math.random()*pitch.height,
        vx: (Math.random()-0.5)*0.4, vy:(Math.random()-0.5)*0.25,
        s: 1.5 + Math.random()*2.5, c: COLORS[(Math.random()*COLORS.length)|0],
        t: Math.random()*Math.PI*2, sp: (reduce ? 0.006 : 0.01) + Math.random()*0.02
      });
    }
  }
  seedParticles(); addEventListener('resize', seedParticles);

  // One frame draw (callable by RAF and by timers)
  function drawFrame(){
    const w = pitch.width, h = pitch.height;

    // background turf gradient
    const g = ctx.createLinearGradient(0,0,0,h);
    g.addColorStop(0,   '#0c3b20');
    g.addColorStop(0.5, '#0c3a1f');
    g.addColorStop(1,   '#0a2f19');
    ctx.fillStyle = g;
    ctx.fillRect(0,0,w,h);

    // mowing stripes
    drawStripes(w,h);

    // white markings
    ctx.strokeStyle = '#f0fff4';
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.lineWidth = Math.max(2, w*0.0023);

    const m = MODES[modeIndex];
    if (m==='Football') drawFootball(w,h);
    else if (m==='Rugby') drawRugby(w,h);
    else if (m==='Cricket') drawCricket(w,h);
    else drawMulti(w,h);

    // spark layer (skipped if reduced motion)
    spx.clearRect(0,0,w,h);
    if (!reduce){
      parts.forEach(p=>{
        const dx = mx - p.x, dy = my - p.y, d = Math.hypot(dx,dy)||1;
        const pull = d<180 ? (180-d)/180*0.02 : 0;
        p.vx += dx/d * pull; p.vy += dy/d * pull * 0.008;
        p.vx *= 0.992; p.vy *= 0.992; p.t += p.sp;
        p.x += p.vx; p.y += p.vy;

        if(p.x < -40) p.x = w+40; if(p.x > w+40) p.x = -40;
        if(p.y < -40) p.y = h+40; if(p.y > h+40) p.y = -40;

        spx.save();
        spx.translate(p.x,p.y);
        spx.rotate(p.t);
        spx.globalAlpha = 0.65;
        spx.fillStyle = p.c;
        spx.beginPath(); spx.ellipse(0,0, p.s*2.1, p.s, 0, 0, Math.PI*2); spx.fill();
        spx.restore();
      });
    }
  }

  // RAF loop always runs; we scale motion based on preference, but keep updates alive
  let running = true;
  function loop(){
    if (!running) return;
    drawFrame();
    requestAnimationFrame(loop);
  }
  loop();

  // Pause when tab is hidden (battery friendly), resume when visible
  document.addEventListener('visibilitychange', ()=>{
    running = !document.hidden;
    if (running) loop();
  });
})();

/* Typewriter rotating words (no buttons) */
(function(){
  const words = ["Maintain", "Renovate", "Mark", "Protect"];
  const el = document.getElementById('typer');
  let wi=0, ci=0, typing=true, pause=0;
  function loop(){
    if(!el) return;
    const w = words[wi];
    if(typing){ el.textContent = w.slice(0, ++ci); if(ci > w.length){ typing=false; pause=12; } }
    else { if(pause-- <= 0){ el.textContent = w.slice(0, --ci); if(ci===0){ typing=true; wi=(wi+1)%words.length; } } }
    setTimeout(loop, 90);
  }
  loop();
})();
</script>
