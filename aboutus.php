<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>About Us – FieldCraft</title>

<style>
:root{ --nav-h: 72px; }
@media (max-width: 991.98px){ :root{ --nav-h: 60px; } }

/* ⛔️ no body padding — we flush to the navbar */
html, body { height: 100%; }

/* Pull the hero background up under the fixed nav so there's NO visible gap */
.hero-wrap{
  position: relative;
  margin-top: calc(-1 * var(--nav-h));   /* background slides under nav */
  padding-top: var(--nav-h);             /* content starts below nav */
  overflow: hidden; isolation: isolate;
  background: radial-gradient(80% 60% at 50% 100%, #093418 0%, #0b1410 60%);
}

/* animated turf */
.turf{
  position:absolute; inset:0; z-index:0; pointer-events:none;
  background:
    linear-gradient(0deg, rgba(255,255,255,.06) 50%, rgba(0,0,0,0) 50%) 0 0/100% 24px,
    linear-gradient(90deg, rgba(255,255,255,.05) 50%, rgba(0,0,0,0) 50%) 0 0/24px 100%;
  opacity:.25; animation: drift 16s linear infinite;
}
@keyframes drift{
  from{ background-position: 0 0, 0 0; }
  to  { background-position: 0 24px, 24px 0; }
}

.hero{ position:relative; z-index:1; color:#ecfdf5; }
.hero h1{ font-weight:800; letter-spacing:.4px; text-shadow:0 6px 18px rgba(0,0,0,.35); }
.hero p.lead{ color:#bbf7d0; }

.about-section img{ transition: transform .35s ease, box-shadow .35s ease; }
.about-section img:hover{ transform: scale(1.02); box-shadow: 0 16px 40px rgba(0,0,0,.18); }

/* counters */
.stats{ display:grid; grid-template-columns: repeat(3,1fr); gap:16px; }
@media (max-width:768px){ .stats{ grid-template-columns:1fr; } }
.stat-card{ background:#fff; border-radius:14px; padding:18px; text-align:center; box-shadow:0 10px 24px rgba(0,0,0,.08); }
.stat-card .num{ font-size: clamp(1.6rem, 1.1rem + 2.2vw, 2.4rem); font-weight:800; color:#065f46; }
.stat-card .lbl{ color:#334155; }

/* values */
.value-card{ transition: transform .15s ease, box-shadow .15s ease; border-radius:14px; background:#fff; box-shadow:0 10px 24px rgba(0,0,0,.08); }
.value-card[data-tilt]:hover{ box-shadow:0 16px 36px rgba(0,0,0,.12); }

/* timeline */
.timeline{ position:relative; padding-left:28px; }
.timeline::before{ content:""; position:absolute; left:12px; top:0; bottom:0; width:2px; background:#e2e8f0; }
.t-item{ position:relative; margin-bottom:18px; }
.t-item::before{
  content:""; position:absolute; left:-2px; top:3px; width:12px; height:12px; border-radius:50%;
  background:#10b981; box-shadow:0 0 0 4px rgba(16,185,129,.15);
}

/* faq */
.accordion-button:not(.collapsed){ background:#eaf7ef; color:#065f46; }

/* headings */
.section-title{ font-weight:700; border-left:6px solid #198754; padding-left:10px; }

/* kill accidental top margins that could show a strip */
.hero + section,
.about-section,
section:first-of-type { margin-top: 0 !important; }
</style>
</head>
<body>

<!-- HERO (now flush to navbar) -->
<section class="hero-wrap">
  <div class="turf" aria-hidden="true"></div>
  <div class="hero container py-5">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1 class="mb-2">About FieldCraft</h1>
        <p class="lead mb-3">Care for every square — with smart scheduling, AI-assisted insights, and grounded expertise.</p>
        <div class="stats">
          <div class="stat-card"><div class="num" data-count="25">0</div><div class="lbl">Years of Mentored Know-How</div></div>
          <div class="stat-card"><div class="num" data-count="300">0</div><div class="lbl">+ Pitches Served</div></div>
          <div class="stat-card"><div class="num" data-count="98">0</div><div class="lbl">% Client Satisfaction</div></div>
        </div>
      </div>
      <div class="col-lg-5 mt-4 mt-lg-0 text-center">
        <img src="assets/logo1.jpeg" class="img-fluid rounded-3 shadow" alt="FieldCraft Services" style="max-height:300px; object-fit:cover;">
      </div>
    </div>
  </div>
</section>

<!-- ABOUT COPY -->
<section class="about-section py-5">
  <div class="container">
    <div class="row align-items-start">
      <div class="col-md-6">
        <h2 class="mb-3 section-title">Who We Are</h2>
        <p><strong>FieldCraft</strong> is the UK’s first AI-powered online platform for sports field and facility maintenance. We bring instant booking, smart scheduling, and intelligent field condition analysis to schools, councils, and sports clubs.</p>
        <p>We believe every playing surface — from open fields to indoor halls — deserves care that matches the passion of the people who use it. Whether it’s a school ground, a local club pitch, or a community sports hall, our mission is to keep your facilities in top condition, season after season.</p>
      </div>
      <div class="col-md-6">
        <h2 class="mb-3 section-title">What We Do</h2>
        <p>We specialise in sports field and facility maintenance, offering tailored services for natural surfaces, sports halls, and artificial fields on request. Our focus is on maintaining performance, safety, and longevity — reserving full renovations for when they’re truly necessary.</p>
        <p>Guided by a mentor with over <strong>25 years of industry experience</strong>, we combine hands-on knowledge with practical, cost-conscious solutions. You get reliable service and honest advice — every time.</p>
      </div>
    </div>
  </div>
</section>

<!-- VALUES -->
<section class="py-5 bg-light">
  <div class="container">
    <h3 class="mb-4 text-center">Our Commitment</h3>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="p-4 value-card" data-tilt>
          <div class="text-success display-6"><i class="bi bi-tools"></i></div>
          <h5 class="mt-2">Expert Maintenance</h5>
          <p class="mb-0">Tailored plans for each surface to ensure lasting quality and better play performance.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 value-card" data-tilt>
          <div class="text-success display-6"><i class="bi bi-people"></i></div>
          <h5 class="mt-2">Trusted Experience</h5>
          <p class="mb-0">Decades of know-how, dependable service, and straight, professional advice.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="p-4 value-card" data-tilt>
          <div class="text-success display-6"><i class="bi bi-heart"></i></div>
          <h5 class="mt-2">Community Focus</h5>
          <p class="mb-0">We care for the spaces where local teams and communities come together.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- TIMELINE + FAQ -->
<section class="py-5">
  <div class="container">
    <h3 class="mb-4 text-center">How We Work</h3>
    <div class="row g-4">
      <div class="col-md-6">
        <div class="timeline">
          <div class="t-item"><h6 class="mb-1">1. Quick Discovery</h6><p class="mb-0 text-muted">Tell us your ground type, play frequency, and priorities.</p></div>
          <div class="t-item"><h6 class="mb-1">2. Smart Plan</h6><p class="mb-0 text-muted">We propose an in-season plan aligned to GMA / FA / RFU / ECB guidance.</p></div>
          <div class="t-item"><h6 class="mb-1">3. On-Site Care</h6><p class="mb-0 text-muted">Technicians deliver routine works, match prep, and seasonal attention.</p></div>
          <div class="t-item"><h6 class="mb-1">4. Simple Reporting</h6><p class="mb-0 text-muted">Photos, readings, and notes visible in your portal — no paperwork chase.</p></div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="accordion" id="faq">
          <div class="accordion-item">
            <h2 class="accordion-header" id="q1">
              <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a1">
                Do you cover artificial or hybrid surfaces?
              </button>
            </h2>
            <div id="a1" class="accordion-collapse collapse show" data-bs-parent="#faq">
              <div class="accordion-body">Yes — we maintain natural surfaces routinely and can support artificial or hybrid on request, including inspections and deep cleans via partners.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="q2">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a2">
                Can we start small before committing?
              </button>
            </h2>
            <div id="a2" class="accordion-collapse collapse" data-bs-parent="#faq">
              <div class="accordion-body">Absolutely — book a one-time service to trial our approach, then move to a weekly/monthly/annual plan.</div>
            </div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header" id="q3">
              <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3">
                Do you follow national standards?
              </button>
            </h2>
            <div id="a3" class="accordion-collapse collapse" data-bs-parent="#faq">
              <div class="accordion-body">Yes — aligned with <strong>GMA</strong>, <strong>FA</strong>, <strong>RFU</strong> and <strong>ECB</strong> guidelines.</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
// counters
(function(){
  const els = document.querySelectorAll('.stat-card .num');
  const animate = (el) => {
    const end = parseInt(el.getAttribute('data-count'), 10) || 0;
    const start = 0, dur = 1200, t0 = performance.now();
    function tick(now){
      const p = Math.min(1, (now - t0) / dur);
      const val = Math.round(start + (end - start) * (0.5 - Math.cos(p*Math.PI)/2));
      el.textContent = val + (el.getAttribute('data-count') === '98' ? '%' : '');
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  };
  const io = new IntersectionObserver((es)=>es.forEach(e=>{ if(e.isIntersecting){ animate(e.target); io.unobserve(e.target); } }), {threshold:.3});
  document.querySelectorAll('.stat-card .num').forEach(el=>io.observe(el));
})();

// tilt cards
(function(){
  const cards = document.querySelectorAll('[data-tilt]');
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduce) return;
  cards.forEach(card=>{
    card.addEventListener('mousemove',(e)=>{
      const r=card.getBoundingClientRect();
      const x=(e.clientX-(r.left+r.width/2))/r.width;
      const y=(e.clientY-(r.top+r.height/2))/r.height;
      card.style.transform=`perspective(700px) rotateY(${x*8}deg) rotateX(${y*-8}deg)`;
    });
    card.addEventListener('mouseleave',()=> card.style.transform='');
  });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
