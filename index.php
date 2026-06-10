<?php
// index.php — COSMOPASS main page (PHP + MySQL backend)
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>COSMOPASS — Space Event Booking</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;900&family=Rajdhani:wght@300;400;600&display=swap" rel="stylesheet"/>
<style>
  :root{--void:#020408;--deep:#050d18;--panel:#0a1628;--glow:#00e5ff;--amber:#ffb700;--nova:#ff4d6d;--star:#e8f4ff;--muted:#4a6080;--glass:rgba(10,22,40,0.85);}
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
  html{scroll-behavior:smooth;}
  body{background:var(--void);color:var(--star);font-family:'Rajdhani',sans-serif;font-size:16px;min-height:100vh;overflow-x:hidden;cursor:crosshair;}
  #starfield{position:fixed;inset:0;z-index:0;pointer-events:none;}
  .nebula{position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 60% 40% at 20% 30%,rgba(0,100,180,0.12) 0%,transparent 70%),radial-gradient(ellipse 50% 60% at 80% 70%,rgba(100,0,180,0.10) 0%,transparent 70%),radial-gradient(ellipse 80% 50% at 50% 50%,rgba(0,229,255,0.04) 0%,transparent 70%);}
  .wrapper{position:relative;z-index:1;max-width:1100px;margin:0 auto;padding:0 24px;}
  header{padding:28px 0 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(0,229,255,0.1);}
  .logo{font-family:'Orbitron',monospace;font-size:1.2rem;font-weight:900;letter-spacing:0.3em;color:var(--glow);text-shadow:0 0 20px rgba(0,229,255,0.6);}
  .logo span{color:var(--amber);}
  .nav-links{display:flex;gap:16px;align-items:center;}
  .nav-tag{font-size:0.75rem;letter-spacing:0.2em;color:var(--muted);text-transform:uppercase;}
  .admin-link{font-family:'Orbitron',monospace;font-size:0.55rem;letter-spacing:0.15em;color:var(--nova);border:1px solid rgba(255,77,109,0.3);padding:6px 12px;border-radius:6px;text-decoration:none;}
  .admin-link:hover{background:rgba(255,77,109,0.1);}
  .hero{padding:60px 0 40px;text-align:center;animation:fadeUp 0.9s ease both;}
  @keyframes fadeUp{from{opacity:0;transform:translateY(30px);}to{opacity:1;transform:translateY(0);}}
  .hero-eyebrow{font-size:0.7rem;letter-spacing:0.35em;color:var(--glow);text-transform:uppercase;margin-bottom:14px;display:flex;align-items:center;justify-content:center;gap:10px;}
  .hero-eyebrow::before,.hero-eyebrow::after{content:'';flex:1;max-width:80px;height:1px;background:linear-gradient(90deg,transparent,var(--glow));}
  .hero-eyebrow::after{transform:scaleX(-1);}
  h1{font-family:'Orbitron',monospace;font-size:clamp(2rem,6vw,4rem);font-weight:900;line-height:1.05;letter-spacing:0.05em;margin-bottom:16px;}
  h1 .line2{color:var(--glow);display:block;text-shadow:0 0 40px rgba(0,229,255,0.5);}
  .hero-sub{color:var(--muted);font-size:1rem;letter-spacing:0.08em;max-width:500px;margin:0 auto 36px;line-height:1.7;}
  .steps{display:flex;justify-content:center;gap:0;margin-bottom:48px;font-size:0.7rem;letter-spacing:0.15em;text-transform:uppercase;}
  .step{display:flex;align-items:center;gap:8px;color:var(--muted);transition:color 0.3s;}
  .step.active{color:var(--glow);}
  .step.done{color:var(--amber);}
  .step-num{width:24px;height:24px;border-radius:50%;border:1px solid currentColor;display:flex;align-items:center;justify-content:center;font-family:'Orbitron',monospace;font-size:0.6rem;}
  .step.active .step-num{background:var(--glow);color:var(--void);border-color:var(--glow);box-shadow:0 0 15px rgba(0,229,255,0.5);}
  .step.done .step-num{background:var(--amber);color:var(--void);border-color:var(--amber);}
  .step-line{width:40px;height:1px;background:var(--muted);margin:0 4px;}
  .sec-title{font-family:'Orbitron',monospace;font-size:0.65rem;letter-spacing:0.3em;color:var(--glow);text-transform:uppercase;margin-bottom:20px;display:flex;align-items:center;gap:12px;}
  .sec-title::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,rgba(0,229,255,0.3),transparent);}
  #step-events{animation:fadeUp 0.7s ease both;}
  .events-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-bottom:40px;}
  .event-card{background:var(--panel);border:1px solid rgba(0,229,255,0.08);border-radius:12px;overflow:hidden;cursor:pointer;transition:transform 0.3s,border-color 0.3s,box-shadow 0.3s;position:relative;}
  .event-card:hover{transform:translateY(-4px);border-color:rgba(0,229,255,0.3);box-shadow:0 8px 40px rgba(0,229,255,0.1);}
  .event-card.selected{border-color:var(--glow);box-shadow:0 0 30px rgba(0,229,255,0.25);}
  .event-card.selected::after{content:'✓ SELECTED';position:absolute;top:12px;right:12px;background:var(--glow);color:var(--void);font-family:'Orbitron',monospace;font-size:0.5rem;font-weight:700;padding:4px 8px;border-radius:4px;letter-spacing:0.1em;}
  .event-banner{height:120px;position:relative;display:flex;align-items:center;justify-content:center;font-size:3rem;}
  .event-banner::after{content:'';position:absolute;inset:0;background:linear-gradient(to bottom,transparent 40%,var(--panel));}
  .event-body{padding:16px 20px 20px;}
  .event-date{font-size:0.65rem;letter-spacing:0.2em;color:var(--amber);text-transform:uppercase;margin-bottom:6px;}
  .event-name{font-family:'Orbitron',monospace;font-size:0.9rem;font-weight:600;margin-bottom:6px;line-height:1.3;}
  .event-loc{font-size:0.8rem;color:var(--muted);letter-spacing:0.05em;margin-bottom:12px;}
  .event-tags{display:flex;flex-wrap:wrap;gap:6px;}
  .tag{font-size:0.6rem;letter-spacing:0.12em;padding:3px 8px;border-radius:20px;border:1px solid;text-transform:uppercase;}
  .tag-glow{color:var(--glow);border-color:rgba(0,229,255,0.3);}
  .tag-amber{color:var(--amber);border-color:rgba(255,183,0,0.3);}
  .tag-nova{color:var(--nova);border-color:rgba(255,77,109,0.3);}
  #step-seats{display:none;animation:fadeUp 0.7s ease both;}
  .seat-layout{background:var(--panel);border:1px solid rgba(0,229,255,0.1);border-radius:16px;padding:32px;margin-bottom:24px;}
  .launch-pad{text-align:center;font-family:'Orbitron',monospace;font-size:0.55rem;letter-spacing:0.3em;color:var(--glow);border:1px solid rgba(0,229,255,0.3);border-radius:8px;padding:10px;margin-bottom:32px;background:rgba(0,229,255,0.04);text-transform:uppercase;}
  .zone-label{font-size:0.6rem;letter-spacing:0.2em;color:var(--muted);text-align:center;text-transform:uppercase;margin-bottom:10px;}
  .seat-row{display:flex;justify-content:center;gap:8px;margin-bottom:8px;flex-wrap:wrap;}
  .seat{width:32px;height:32px;border-radius:6px;border:1px solid rgba(0,229,255,0.2);background:rgba(0,229,255,0.05);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.5rem;font-family:'Orbitron',monospace;color:var(--muted);transition:all 0.2s;}
  .seat:hover:not(.booked){border-color:var(--glow);background:rgba(0,229,255,0.15);color:var(--glow);transform:scale(1.1);}
  .seat.selected-seat{border-color:var(--glow);background:var(--glow);color:var(--void);box-shadow:0 0 12px rgba(0,229,255,0.5);}
  .seat.booked{border-color:rgba(255,77,109,0.2);background:rgba(255,77,109,0.05);color:rgba(255,77,109,0.3);cursor:not-allowed;}
  .seat.vip{border-color:rgba(255,183,0,0.3);background:rgba(255,183,0,0.06);color:var(--amber);}
  .seat.vip:hover:not(.booked){background:rgba(255,183,0,0.2);border-color:var(--amber);box-shadow:0 0 12px rgba(255,183,0,0.4);}
  .seat.vip.selected-seat{background:var(--amber);color:var(--void);border-color:var(--amber);box-shadow:0 0 12px rgba(255,183,0,0.5);}
  .seat-divider{width:20px;}
  .zone-separator{display:flex;align-items:center;gap:12px;margin:20px 0 10px;}
  .zone-line{flex:1;height:1px;background:rgba(0,229,255,0.08);}
  .legend{display:flex;justify-content:center;gap:24px;margin-top:24px;flex-wrap:wrap;}
  .legend-item{display:flex;align-items:center;gap:6px;font-size:0.7rem;color:var(--muted);}
  .legend-dot{width:12px;height:12px;border-radius:3px;border:1px solid;}
  .summary-bar{background:var(--glass);border:1px solid rgba(0,229,255,0.15);border-radius:12px;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;backdrop-filter:blur(10px);margin-bottom:40px;}
  .summary-info{font-size:0.8rem;color:var(--muted);}
  .summary-info strong{color:var(--star);font-family:'Orbitron',monospace;font-size:0.75rem;}
  .summary-price{font-family:'Orbitron',monospace;font-size:1.4rem;color:var(--glow);}
  #step-form{display:none;animation:fadeUp 0.7s ease both;}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;}
  .form-group{display:flex;flex-direction:column;gap:6px;}
  .form-group.full{grid-column:1/-1;}
  label{font-size:0.65rem;letter-spacing:0.2em;color:var(--muted);text-transform:uppercase;}
  input,select{background:var(--panel);border:1px solid rgba(0,229,255,0.15);border-radius:8px;color:var(--star);font-family:'Rajdhani',sans-serif;font-size:0.95rem;padding:10px 14px;outline:none;transition:border-color 0.2s,box-shadow 0.2s;}
  input:focus,select:focus{border-color:var(--glow);box-shadow:0 0 0 3px rgba(0,229,255,0.1);}
  select option{background:var(--panel);}
  .btn{font-family:'Orbitron',monospace;font-size:0.7rem;font-weight:600;letter-spacing:0.15em;text-transform:uppercase;padding:14px 32px;border-radius:8px;border:none;cursor:pointer;transition:all 0.25s;display:inline-flex;align-items:center;gap:10px;}
  .btn-primary{background:var(--glow);color:var(--void);box-shadow:0 0 30px rgba(0,229,255,0.3);}
  .btn-primary:hover{box-shadow:0 0 50px rgba(0,229,255,0.5);transform:translateY(-2px);}
  .btn-primary:disabled{opacity:0.3;cursor:not-allowed;transform:none;box-shadow:none;}
  .btn-ghost{background:transparent;color:var(--muted);border:1px solid rgba(255,255,255,0.1);}
  .btn-ghost:hover{color:var(--star);border-color:rgba(255,255,255,0.3);}
  .btn-row{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:48px;}
  #step-confirm{display:none;text-align:center;animation:fadeUp 0.7s ease both;padding:40px 0 80px;}
  .confirm-orbit{width:120px;height:120px;border-radius:50%;border:2px solid var(--glow);margin:0 auto 32px;display:flex;align-items:center;justify-content:center;font-size:3rem;box-shadow:0 0 40px rgba(0,229,255,0.3),inset 0 0 40px rgba(0,229,255,0.05);animation:orbit-pulse 2s ease-in-out infinite;}
  @keyframes orbit-pulse{0%,100%{box-shadow:0 0 40px rgba(0,229,255,0.3),inset 0 0 40px rgba(0,229,255,0.05);}50%{box-shadow:0 0 80px rgba(0,229,255,0.6),inset 0 0 60px rgba(0,229,255,0.1);}}
  .confirm-title{font-family:'Orbitron',monospace;font-size:clamp(1.4rem,4vw,2.2rem);font-weight:900;margin-bottom:8px;}
  .confirm-sub{color:var(--muted);margin-bottom:32px;letter-spacing:0.05em;}
  .ticket-card{background:var(--panel);border:1px solid rgba(0,229,255,0.2);border-radius:16px;max-width:420px;margin:0 auto 32px;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.5);}
  .ticket-header{background:linear-gradient(135deg,#001a30,#002a48);padding:24px;border-bottom:1px dashed rgba(0,229,255,0.2);position:relative;}
  .ticket-header::before,.ticket-header::after{content:'';position:absolute;bottom:-12px;width:24px;height:24px;border-radius:50%;background:var(--void);border:1px solid rgba(0,229,255,0.2);}
  .ticket-header::before{left:-12px;}
  .ticket-header::after{right:-12px;}
  .ticket-event{font-family:'Orbitron',monospace;font-size:0.9rem;color:var(--glow);margin-bottom:4px;}
  .ticket-id{font-size:0.65rem;color:var(--muted);letter-spacing:0.15em;}
  .ticket-body{padding:28px 24px 24px;}
  .ticket-row{display:flex;justify-content:space-between;margin-bottom:12px;font-size:0.85rem;}
  .ticket-row .tl{color:var(--muted);}
  .ticket-row .tr{color:var(--star);font-weight:600;letter-spacing:0.05em;}
  .ticket-qr{margin-top:20px;display:flex;flex-direction:column;align-items:center;gap:8px;}
  .qr-box{width:80px;height:80px;background:white;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:#000;}
  .qr-label{font-size:0.6rem;letter-spacing:0.2em;color:var(--muted);text-transform:uppercase;}
  .shooting-star{position:fixed;width:2px;height:80px;background:linear-gradient(to bottom,transparent,white,transparent);animation:shoot 2s linear;pointer-events:none;z-index:0;}
  @keyframes shoot{from{opacity:1;transform:translateY(-100px) rotate(25deg);}to{opacity:0;transform:translateY(600px) rotate(25deg);}}
  .error-msg{background:rgba(255,77,109,0.1);border:1px solid rgba(255,77,109,0.3);border-radius:8px;padding:12px 16px;color:var(--nova);font-size:0.85rem;margin-bottom:16px;display:none;}
  .loading{opacity:0.5;pointer-events:none;}
  @media(max-width:600px){.form-grid{grid-template-columns:1fr;}.steps{gap:4px;font-size:0.6rem;}.step-line{width:20px;}h1{font-size:1.8rem;}.seat{width:26px;height:26px;font-size:0.4rem;}}
</style>
</head>
<body>
<canvas id="starfield"></canvas>
<div class="nebula"></div>

<div class="wrapper">
  <header>
    <div class="logo">COSMO<span>PASS</span></div>
    <div class="nav-links">
      <div class="nav-tag">Space Event Ticketing ✦ 2027</div>
      <a href="admin.php" class="admin-link">⬤ ADMIN</a>
    </div>
  </header>

  <div class="hero">
    <div class="hero-eyebrow">✦ Mission Control Open ✦</div>
    <h1>BOOK YOUR SEAT<span class="line2">BEYOND EARTH</span></h1>
    <p class="hero-sub">Reserve your place at humanity's most extraordinary events — launches, meteor showers, and cosmic rendezvous.</p>
    <div class="steps">
      <div class="step active" id="st1"><div class="step-num">1</div>Choose Event</div>
      <div class="step-line"></div>
      <div class="step" id="st2"><div class="step-num">2</div>Select Seat</div>
      <div class="step-line"></div>
      <div class="step" id="st3"><div class="step-num">3</div>Your Details</div>
      <div class="step-line"></div>
      <div class="step" id="st4"><div class="step-num">4</div>Launch!</div>
    </div>
  </div>

  <!-- STEP 1 -->
  <div id="step-events">
    <div class="sec-title">Upcoming Missions</div>
    <div class="events-grid" id="events-grid">
      <div style="color:var(--muted);letter-spacing:0.1em;font-size:0.85rem;padding:40px;text-align:center;grid-column:1/-1">
        🛸 Loading missions...
      </div>
    </div>
    <div class="btn-row" style="justify-content:flex-end">
      <button class="btn btn-primary" id="btn-to-seats" onclick="goToSeats()" disabled>SELECT SEATS →</button>
    </div>
  </div>

  <!-- STEP 2 -->
  <div id="step-seats">
    <div class="sec-title">Choose Your Viewing Zone</div>
    <div class="seat-layout">
      <div class="launch-pad">🚀 &nbsp; Launch / Stage Direction &nbsp; 🚀</div>
      <div class="zone-label" style="color:var(--amber)">VIP OBSERVATORY DECK — <span id="vip-price"></span></div>
      <div class="seat-row" id="vip-row-1"></div>
      <div class="seat-row" id="vip-row-2"></div>
      <div class="zone-separator"><div class="zone-line"></div><span style="font-size:0.6rem;letter-spacing:0.2em;color:var(--muted)">STANDARD ZONE</span><div class="zone-line"></div></div>
      <div class="zone-label">STANDARD VIEWING — <span id="std-price"></span></div>
      <div class="seat-row" id="std-row-1"></div>
      <div class="seat-row" id="std-row-2"></div>
      <div class="seat-row" id="std-row-3"></div>
      <div class="seat-row" id="std-row-4"></div>
      <div class="legend">
        <div class="legend-item"><div class="legend-dot" style="background:rgba(0,229,255,0.05);border-color:rgba(0,229,255,0.3)"></div>Available</div>
        <div class="legend-item"><div class="legend-dot" style="background:var(--glow);border-color:var(--glow)"></div>Selected</div>
        <div class="legend-item"><div class="legend-dot" style="background:rgba(255,183,0,0.06);border-color:rgba(255,183,0,0.3)"></div>VIP</div>
        <div class="legend-item"><div class="legend-dot" style="background:rgba(255,77,109,0.05);border-color:rgba(255,77,109,0.2)"></div>Booked</div>
      </div>
    </div>
    <div class="summary-bar">
      <div class="summary-info"><strong id="sum-event">—</strong><br>Seats: <span id="sum-seats">None selected</span></div>
      <div><div class="summary-price" id="sum-price">₹0</div><span style="font-size:0.6rem;color:var(--muted);letter-spacing:0.1em;">TOTAL</span></div>
    </div>
    <div class="btn-row">
      <button class="btn btn-ghost" onclick="goBack('step-seats','step-events',1)">← BACK</button>
      <button class="btn btn-primary" id="btn-to-form" onclick="goToForm()" disabled>CONTINUE →</button>
    </div>
  </div>

  <!-- STEP 3 -->
  <div id="step-form">
    <div class="sec-title">Mission Crew Details</div>
    <div id="form-error" class="error-msg"></div>
    <div class="form-grid">
      <div class="form-group"><label>First Name</label><input type="text" id="f-first" placeholder="Ramya"/></div>
      <div class="form-group"><label>Last Name</label><input type="text" id="f-last" placeholder="Gajendran"/></div>
      <div class="form-group full"><label>Email Address</label><input type="email" id="f-email" placeholder="ramya@cosmopass.space"/></div>
      <div class="form-group"><label>Phone Number</label><input type="tel" id="f-phone" placeholder="+91 9876543210"/></div>
      <div class="form-group"><label>Country / Region</label>
        <select id="f-country">
          <option>India</option><option>United States</option><option>United Kingdom</option>
          <option>UAE</option><option>Singapore</option><option>Australia</option><option>Other</option>
        </select>
      </div>
      <div class="form-group full"><label>Special Requirements</label><input type="text" id="f-special" placeholder="Wheelchair access, dietary needs, etc."/></div>
    </div>
    <div class="btn-row">
      <button class="btn btn-ghost" onclick="goBack('step-form','step-seats',2)">← BACK</button>
      <button class="btn btn-primary" id="btn-confirm" onclick="confirmBooking()">🚀 &nbsp; LAUNCH BOOKING</button>
    </div>
  </div>

  <!-- STEP 4 -->
  <div id="step-confirm">
    <div class="confirm-orbit">🛸</div>
    <div class="confirm-title">MISSION CONFIRMED</div>
    <div class="confirm-sub">Your seat among the stars has been reserved.<br>Check your email for boarding details.</div>
    <div class="ticket-card">
      <div class="ticket-header">
        <div class="ticket-event" id="tk-event">—</div>
        <div class="ticket-id" id="tk-id">PASS-XXXXXXXX</div>
      </div>
      <div class="ticket-body">
        <div class="ticket-row"><span class="tl">Passenger</span><span class="tr" id="tk-name">—</span></div>
        <div class="ticket-row"><span class="tl">Seats</span><span class="tr" id="tk-seats">—</span></div>
        <div class="ticket-row"><span class="tl">Zone</span><span class="tr" id="tk-zone">—</span></div>
        <div class="ticket-row"><span class="tl">Total Paid</span><span class="tr" id="tk-price" style="color:var(--glow)">—</span></div>
        <div class="ticket-qr"><div class="qr-box">⬛</div><div class="qr-label">Scan at gate · Boarding Pass</div></div>
      </div>
    </div>
    <button class="btn btn-ghost" onclick="resetAll()">← BOOK ANOTHER MISSION</button>
  </div>
</div>

<script>
// ── STARFIELD ──
const canvas = document.getElementById('starfield'), ctx = canvas.getContext('2d');
let stars = [];
function resize(){ canvas.width=innerWidth; canvas.height=innerHeight; initStars(); }
function initStars(){ stars = Array.from({length:200},()=>({x:Math.random()*canvas.width,y:Math.random()*canvas.height,r:Math.random()*1.5+0.3,o:Math.random(),speed:Math.random()*0.004+0.001})); }
function drawStars(){ ctx.clearRect(0,0,canvas.width,canvas.height); stars.forEach(s=>{ s.o+=s.speed; if(s.o>1)s.o=0; const a=Math.abs(Math.sin(s.o*Math.PI)); ctx.beginPath(); ctx.arc(s.x,s.y,s.r,0,Math.PI*2); ctx.fillStyle=`rgba(220,240,255,${a*0.8+0.1})`; ctx.fill(); }); requestAnimationFrame(drawStars); }
window.addEventListener('resize',resize); resize(); drawStars();
function shootingStar(){ const el=document.createElement('div'); el.className='shooting-star'; el.style.left=Math.random()*80+10+'vw'; el.style.top='-50px'; document.body.appendChild(el); setTimeout(()=>el.remove(),2100); }
setInterval(shootingStar,4000); setTimeout(shootingStar,800);

// ── STATE ──
let events = [], selectedEvent = null, selectedSeats = [], totalPrice = 0;

// ── LOAD EVENTS FROM PHP API ──
async function loadEvents() {
  try {
    const res  = await fetch('api/events.php');
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    events = data.events;
    renderEvents();
  } catch(e) {
    document.getElementById('events-grid').innerHTML =
      `<div style="color:var(--nova);padding:40px;text-align:center;grid-column:1/-1">⚠️ Failed to load events: ${e.message}</div>`;
  }
}

const banners = [
  {bg:'linear-gradient(135deg,#0a1a2e,#1a3a5c)', tags:['<span class="tag tag-nova">Live Launch</span>','<span class="tag tag-amber">VIP Deck</span>','<span class="tag tag-glow">Observatory</span>']},
  {bg:'linear-gradient(135deg,#0d1a0a,#1a3a10)', tags:['<span class="tag tag-glow">Dark Sky</span>','<span class="tag tag-amber">Telescope</span>']},
  {bg:'linear-gradient(135deg,#1a0a00,#3a1a00)', tags:['<span class="tag tag-amber">Eclipse Path</span>','<span class="tag tag-nova">Rare Event</span>','<span class="tag tag-glow">Live Stream</span>']}
];

function renderEvents() {
  const grid = document.getElementById('events-grid');
  grid.innerHTML = events.map((ev, i) => {
    const b = banners[i % banners.length];
    return `
    <div class="event-card" onclick="selectEvent(this,${ev.id})">
      <div class="event-banner" style="background:${b.bg}">${ev.icon}</div>
      <div class="event-body">
        <div class="event-date">${ev.event_date}</div>
        <div class="event-name">${ev.name}</div>
        <div class="event-loc">📍 ${ev.location}</div>
        <div class="event-tags">${b.tags.join('')}</div>
      </div>
    </div>`;
  }).join('');
}

function selectEvent(card, id) {
  document.querySelectorAll('.event-card').forEach(c=>c.classList.remove('selected'));
  card.classList.add('selected');
  selectedEvent = events.find(e=>e.id==id);
  document.getElementById('btn-to-seats').disabled = false;
}

// ── STEP 2: LOAD SEATS FROM PHP API ──
async function goToSeats() {
  if (!selectedEvent) return;
  document.getElementById('step-events').style.display = 'none';
  const ss = document.getElementById('step-seats');
  ss.style.display = 'block'; setStep(2);
  document.getElementById('sum-event').textContent    = selectedEvent.name;
  document.getElementById('vip-price').textContent    = '₹' + Number(selectedEvent.vip_price).toLocaleString('en-IN');
  document.getElementById('std-price').textContent    = '₹' + Number(selectedEvent.std_price).toLocaleString('en-IN');
  selectedSeats = []; updateSummary();

  // Fetch real seat availability
  try {
    const res  = await fetch(`api/seats.php?event_id=${selectedEvent.id}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.error);
    buildSeats(data.seats);
  } catch(e) {
    alert('Could not load seat map: ' + e.message);
  }
}

function buildSeats(seatData) {
  const byCode = {};
  seatData.forEach(s => byCode[s.seat_code] = s);

  function makeRow(rowId, codes) {
    const row = document.getElementById(rowId);
    row.innerHTML = '';
    codes.forEach((code, i) => {
      if (i === Math.floor(codes.length/2)) {
        const d = document.createElement('div'); d.className='seat-divider'; row.appendChild(d);
      }
      const s = byCode[code] || { seat_code: code, zone:'standard', status:'available' };
      const isVip    = s.zone === 'vip';
      const isBooked = s.status === 'booked';
      const btn = document.createElement('div');
      btn.className = 'seat' + (isVip?' vip':'') + (isBooked?' booked':'');
      btn.textContent = code;
      if (!isBooked) btn.onclick = () => toggleSeat(btn, code, isVip);
      row.appendChild(btn);
    });
  }

  makeRow('vip-row-1', ['A1','A2','A3','A4','A5','A6']);
  makeRow('vip-row-2', ['B1','B2','B3','B4','B5','B6','B7']);
  makeRow('std-row-1', ['C1','C2','C3','C4','C5','C6','C7','C8']);
  makeRow('std-row-2', ['D1','D2','D3','D4','D5','D6','D7','D8']);
  makeRow('std-row-3', ['E1','E2','E3','E4','E5','E6','E7','E8']);
  makeRow('std-row-4', ['F1','F2','F3','F4','F5','F6','F7','F8']);
}

function toggleSeat(btn, id, vip) {
  const idx = selectedSeats.findIndex(s=>s.id===id);
  if (idx > -1) { selectedSeats.splice(idx,1); btn.classList.remove('selected-seat'); }
  else {
    if (selectedSeats.length >= 6) { alert('Max 6 seats per booking'); return; }
    selectedSeats.push({id,vip}); btn.classList.add('selected-seat');
  }
  updateSummary();
}

function updateSummary() {
  const stdP = selectedEvent ? selectedEvent.std_price : 0;
  const vipP = selectedEvent ? selectedEvent.vip_price : 0;
  totalPrice = selectedSeats.reduce((sum,s)=>sum+(s.vip?vipP:stdP),0);
  document.getElementById('sum-seats').textContent = selectedSeats.length ? selectedSeats.map(s=>s.id).join(', ') : 'None selected';
  document.getElementById('sum-price').textContent = '₹'+totalPrice.toLocaleString('en-IN');
  document.getElementById('btn-to-form').disabled = selectedSeats.length===0;
}

function goToForm() {
  document.getElementById('step-seats').style.display='none';
  document.getElementById('step-form').style.display='block'; setStep(3);
}

function goBack(hide,show,n) {
  document.getElementById(hide).style.display='none';
  document.getElementById(show).style.display='block'; setStep(n);
}

// ── STEP 3 → POST to PHP ──
async function confirmBooking() {
  const first = document.getElementById('f-first').value.trim();
  const last  = document.getElementById('f-last').value.trim();
  const email = document.getElementById('f-email').value.trim();
  const errEl = document.getElementById('form-error');
  errEl.style.display='none';

  if (!first||!last||!email){ errEl.textContent='⚠️ Please fill in your name and email.'; errEl.style.display='block'; return; }

  const hasVip = selectedSeats.some(s=>s.vip);
  const hasStd = selectedSeats.some(s=>!s.vip);
  const zone   = hasVip&&hasStd?'VIP + Standard':hasVip?'VIP Observatory':'Standard';

  const payload = {
    event_id:    selectedEvent.id,
    first_name:  first,
    last_name:   last,
    email,
    phone:       document.getElementById('f-phone').value.trim(),
    country:     document.getElementById('f-country').value,
    special_req: document.getElementById('f-special').value.trim(),
    seats:       selectedSeats,
    zone,
    total_price: totalPrice
  };

  const btn = document.getElementById('btn-confirm');
  btn.disabled=true; btn.textContent='LAUNCHING...';

  try {
    const res  = await fetch('api/book.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload) });
    const data = await res.json();

    if (!data.success) {
      errEl.textContent = '⚠️ ' + data.error;
      errEl.style.display='block';
      btn.disabled=false; btn.innerHTML='🚀 &nbsp; LAUNCH BOOKING';
      return;
    }

    // Show confirmation
    document.getElementById('tk-event').textContent  = selectedEvent.name;
    document.getElementById('tk-id').textContent     = data.pass_id;
    document.getElementById('tk-name').textContent   = first+' '+last;
    document.getElementById('tk-seats').textContent  = data.seats;
    document.getElementById('tk-zone').textContent   = zone;
    document.getElementById('tk-price').textContent  = '₹'+Number(data.total_price).toLocaleString('en-IN');

    document.getElementById('step-form').style.display='none';
    document.getElementById('step-confirm').style.display='block'; setStep(4);
    for(let i=0;i<5;i++) setTimeout(shootingStar,i*250);

  } catch(e) {
    errEl.textContent='⚠️ Network error: '+e.message;
    errEl.style.display='block';
    btn.disabled=false; btn.innerHTML='🚀 &nbsp; LAUNCH BOOKING';
  }
}

function resetAll() {
  selectedEvent=null; selectedSeats=[]; totalPrice=0;
  document.getElementById('step-confirm').style.display='none';
  document.getElementById('step-events').style.display='block';
  document.querySelectorAll('.event-card').forEach(c=>c.classList.remove('selected'));
  document.getElementById('btn-to-seats').disabled=true;
  setStep(1);
}

function setStep(n) {
  [1,2,3,4].forEach(i=>{
    const el=document.getElementById('st'+i);
    el.classList.remove('active','done');
    if(i<n) el.classList.add('done');
    else if(i===n) el.classList.add('active');
  });
}

// Init
loadEvents();
</script>
</body>
</html>