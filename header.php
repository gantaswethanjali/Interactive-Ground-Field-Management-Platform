<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Swetha</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Neue+Machina:wght@400;500;700&display=swap" rel="stylesheet">

  <!-- ✨ Header flair styles (self-contained) -->
  <style>
    /* Reduce Motion */
    @media (prefers-reduced-motion: reduce) {
      [data-magnetic] { transition: none !important; }
      .ink-link::after { transition: none !important; }
    }

    /* Underline “ink” animation for nav links */
    .ink-link { position: relative; text-decoration: none !important; }
    .ink-link::after {
      content: "";
      position: absolute; left: 0; bottom: .2rem;
      height: 2px; width: 100%;
      background: currentColor;
      transform: scaleX(0); transform-origin: right;
      opacity: .6; transition: transform .28s ease;
    }
    .ink-link:hover::after,
    .ink-link:focus::after,
    .nav-link.active.ink-link::after {
      transform: scaleX(1); transform-origin: left;
    }

    /* Magnetic micro-parallax */
    [data-magnetic] {
      position: relative;
      will-change: transform, filter;
      transition: transform .15s ease, filter .2s ease;
    }
    [data-magnetic]:hover { filter: brightness(1.06); }

    /* Confetti canvas */
    #fc-confetti { position: fixed; inset: 0; pointer-events: none; z-index: 2147483000; }

    /* Thin scroll progress */
    #fc-scrollbar {
      position: fixed; top: 0; left: 0;
      height: 3px; width: 0%;
      background: linear-gradient(90deg, #6ee7ff, #a78bfa, #f472b6);
      z-index: 2147483001; transition: width .12s linear;
    }

    /* Optional brand logo sizing (kept from your css) */
    .navbar-logo {
      height: 25px; width:auto; max-height:25px; object-fit:contain; opacity:.95; margin-right:6px; vertical-align:middle;
    }
    .navbar-logo:hover { transform: scale(1.05); opacity:1; }
  </style>
</head>
<body>

<!-- Scroll progress bar -->
<div id="fc-scrollbar" aria-hidden="true"></div>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation" data-magnetic>
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Centered Brand -->
    <a class="navbar-brand d-flex align-items-center mx-auto" href="index.php" data-confetti data-magnetic>
      <img src="assets/logo.jpg" alt="FieldCraft Logo" class="navbar-logo me-2">
      Ground
    </a>

    <!-- Links -->
    <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link ink-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link ink-link" href="services.php">Field Plan</a></li>
        <li class="nav-item"><a class="nav-link ink-link" href="aboutus.php">About</a></li>
        <li class="nav-item"><a class="nav-link ink-link" href="index.php#contact-us">Contact</a></li>
      </ul>
    </div>

    <!-- Sign In -->
    <div class="d-flex">
      <a class="btn btn-outline-light" href="dashboard.php" data-magnetic data-confetti>Sign In</a>
    </div>
  </div>
</nav>

<!-- Header flair script -->
<script>
(function(){
  'use strict';
  const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Magnetic hover */
  document.addEventListener('mousemove', function(e){
    if (reduce) return;
    const t = e.target.closest && e.target.closest('[data-magnetic]');
    if(!t) return;
    const r = t.getBoundingClientRect();
    const x = e.clientX - (r.left + r.width/2);
    const y = e.clientY - (r.top  + r.height/2);
    t.style.transform = `translate(${x*0.06}px, ${y*0.06}px)`;
  }, {passive:true});
  document.addEventListener('mouseleave', function(e){
    const t = e.target && e.target.closest && e.target.closest('[data-magnetic]');
    if(t) t.style.transform = '';
  }, true);

  /* Confetti */
  let cv, cx, parts=[], raf;
  function ensureCanvas(){
    if(cv) return;
    cv = document.createElement('canvas');
    cv.id = 'fc-confetti';
    cx = cv.getContext('2d');
    document.body.appendChild(cv);
    fit();
    addEventListener('resize', fit);
  }
  function fit(){ if(cv){ cv.width = innerWidth; cv.height = innerHeight; } }
  function burst(x,y,n=140){
    ensureCanvas();
    for(let i=0;i<n;i++){
      parts.push({ x,y, vx:(Math.random()-0.5)*10, vy:Math.random()*-7-4, life:80, size:2+Math.random()*3,
        c:`hsl(${Math.random()*360},90%,60%)` });
    }
    if(!raf) loop();
  }
  function loop(){
    raf = requestAnimationFrame(loop);
    cx.clearRect(0,0,cv.width,cv.height);
    parts = parts.filter(p=>p.life-- > 0).map(p=>{
      p.vy += 0.18; p.x += p.vx; p.y += p.vy;
      cx.fillStyle = p.c; cx.fillRect(p.x, p.y, p.size, p.size);
      return p;
    });
    if(!parts.length){ cancelAnimationFrame(raf); raf=null; }
  }
  document.addEventListener('click', function(e){
    const btn = e.target.closest && e.target.closest('[data-confetti]');
    if(!btn) return;
    const r = btn.getBoundingClientRect();
    burst(r.left + r.width/2, r.top + scrollY + r.height/2);
  });

  /* Scroll progress */
  const bar = document.getElementById('fc-scrollbar');
  function onScroll(){
    const st = document.documentElement.scrollTop || document.body.scrollTop;
    const docH = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    const p = docH > 0 ? (st / docH) * 100 : 0;
    bar && (bar.style.width = p + '%');
  }
  document.addEventListener('scroll', onScroll, {passive:true});
  onScroll();
})();
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
