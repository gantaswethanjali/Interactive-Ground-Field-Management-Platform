<?php include 'header.php'; ?>

<style>
/* === FIELDCRAFT DASHBOARD THEME === */
@media (prefers-reduced-motion: reduce) {
  .field-bg *, .dash-card, [data-magnetic] { animation: none !important; transition: none !important; }
}

/* Body reset */
body {
  background: linear-gradient(to top, #98fb98 0%, #b7e1a1 15%, #a8d0f0 100%);
  overflow-x: hidden;
  min-height: 100vh;
  font-family: 'Neue Machina', sans-serif;
}

/* --- Sky and Grass Layers --- */
.field-bg {
  position: fixed;
  inset: 0;
  z-index: -2;
  overflow: hidden;
  background: linear-gradient(to top, #8fd694 0%, #b4e0a1 40%, #d0efff 100%);
}
.field-bg .grass {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 180px;
  background: linear-gradient(to top, #4caf50 0%, #81c784 100%);
  overflow: hidden;
  transform-origin: bottom;
}
.field-bg .grass::before {
  content: "";
  position: absolute;
  inset: 0;
  background: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='400' height='120'><path fill='%234caf50' d='M0,120 C40,100 80,100 120,120 C160,140 200,140 240,120 C280,100 320,100 360,120 L400,120 L400,0 L0,0 Z'/></svg>") repeat-x;
  animation: wave 8s linear infinite;
  opacity: 0.5;
}
@keyframes wave {
  from { background-position-x: 0; }
  to { background-position-x: 400px; }
}

/* --- Floating Clouds --- */
.field-bg .cloud {
  position: absolute;
  background: #fff;
  border-radius: 50%;
  box-shadow:
    60px 20px 0 10px #fff,
    120px 25px 0 0 #fff,
    90px -10px 0 10px #fff,
    30px 10px 0 10px #fff;
  width: 100px;
  height: 60px;
  opacity: 0.8;
  animation: drift 50s linear infinite;
}
.field-bg .cloud:nth-child(1){ top: 80px; left: -150px; animation-delay: -5s; }
.field-bg .cloud:nth-child(2){ top: 150px; left: -300px; width: 130px; height: 70px; animation-delay: -25s; }
.field-bg .cloud:nth-child(3){ top: 50px; left: -400px; width: 150px; height: 80px; animation-delay: -15s; }

@keyframes drift {
  from { transform: translateX(0); }
  to { transform: translateX(130vw); }
}

/* --- Falling leaves --- */
.field-bg .leaf {
  position: absolute;
  top: -40px;
  width: 24px;
  height: 24px;
  background: radial-gradient(circle at center, #ffb347 40%, #ffcc33 70%);
  border-radius: 0 50% 50% 50%;
  transform: rotate(45deg);
  opacity: 0.8;
  animation: fall linear infinite;
}
@keyframes fall {
  0%   { transform: translateY(0) rotate(0deg); opacity: 1; }
  80%  { opacity: 1; }
  100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
}
<?php for($i=1;$i<=8;$i++): ?>
.field-bg .leaf:nth-child(<?=3+$i?>){
  left: <?=rand(0,100)?>%;
  animation-duration: <?=rand(10,25)?>s;
  animation-delay: -<?=rand(0,25)?>s;
}
<?php endfor; ?>

/* --- Card + Buttons --- */
.dash-card {
  animation: dashFadeIn .8s ease forwards;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(8px);
  border: none;
  border-radius: 20px;
  transition: transform .3s ease, box-shadow .3s ease;
}
@keyframes dashFadeIn {
  from { opacity: 0; transform: translateY(30px) scale(0.98); }
  to   { opacity: 1; transform: none; }
}
.dash-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0,0,0,.15);
}

.btn-glow {
  position: relative;
  overflow: hidden;
  z-index: 0;
}
.btn-glow::before {
  content: "";
  position: absolute;
  inset: -2px;
  background: linear-gradient(90deg,#6ee7ff,#a78bfa,#f472b6,#6ee7ff);
  background-size: 300%;
  filter: blur(4px);
  z-index: -1;
  opacity: 0;
  transition: opacity .4s ease;
  border-radius: inherit;
}
.btn-glow:hover::before {
  opacity: 1;
  animation: glowmove 4s linear infinite;
}
@keyframes glowmove {
  0% { background-position: 0% 50%; }
  100% { background-position: 300% 50%; }
}
</style>

<!-- 🌾 Dynamic field background -->
<div class="field-bg">
  <div class="cloud"></div>
  <div class="cloud"></div>
  <div class="cloud"></div>
  <div class="grass"></div>
  <!-- falling leaves -->
  <div class="leaf"></div>
  <div class="leaf"></div>
  <div class="leaf"></div>
  <div class="leaf"></div>
  <div class="leaf"></div>
  <div class="leaf"></div>
  <div class="leaf"></div>
  <div class="leaf"></div>
</div>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <div class="card p-5 shadow-lg text-center dash-card" style="max-width: 500px; width: 100%;">
    <h1 class="mb-4">Welcome to FieldCraft!</h1>
    <p class="lead">Let’s keep those fields in perfect shape — choose your role:</p>

    <div class="d-grid gap-3 mt-4">
      <a href="technician_signin.php" 
         class="btn btn-primary btn-lg btn-glow"
         data-magnetic data-confetti>
         ⚙️ Technician Sign In
      </a>
      <a href="signin.php" 
         class="btn btn-success btn-lg btn-glow"
         data-magnetic data-confetti>
         🌿 Client Sign In
      </a>
    </div>
  </div>
</div>

<script>
(function(){
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(reduce) return;
  // soft stagger button entry
  const btns = document.querySelectorAll('.dash-card .btn');
  btns.forEach((b,i)=>{
    b.style.opacity=0;
    b.style.transform='translateY(10px)';
    setTimeout(()=>{
      b.style.transition='opacity .4s ease, transform .4s ease';
      b.style.opacity=1;
      b.style.transform='none';
    }, 400 + i*180);
  });
})();
</script>

<?php include 'footer.php'; ?>
