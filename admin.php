<?php
// ============================================
//  admin.php — View all bookings (admin page)
//  Open: localhost/cosmopass/admin.php
// ============================================
require_once 'config.php';

$pdo = getDB();

// Stats
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$totalRevenue  = $pdo->query("SELECT COALESCE(SUM(total_price),0) FROM bookings")->fetchColumn();
$totalSeats    = $pdo->query("SELECT COUNT(*) FROM seats WHERE status='booked'")->fetchColumn();

// All bookings with event name
$bookings = $pdo->query(
    "SELECT b.*, e.name AS event_name, e.event_date
     FROM bookings b
     JOIN events e ON b.event_id = e.id
     ORDER BY b.booked_at DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>COSMOPASS — Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Rajdhani:wght@400;600&display=swap" rel="stylesheet"/>
<style>
  :root{--void:#020408;--panel:#0a1628;--glow:#00e5ff;--amber:#ffb700;--nova:#ff4d6d;--star:#e8f4ff;--muted:#4a6080;}
  *{box-sizing:border-box;margin:0;padding:0;}
  body{background:var(--void);color:var(--star);font-family:'Rajdhani',sans-serif;min-height:100vh;}
  .wrapper{max-width:1200px;margin:0 auto;padding:32px 24px;}
  header{display:flex;align-items:center;justify-content:space-between;margin-bottom:40px;padding-bottom:20px;border-bottom:1px solid rgba(0,229,255,0.1);}
  .logo{font-family:'Orbitron',monospace;font-size:1.1rem;font-weight:900;letter-spacing:0.3em;color:var(--glow);}
  .logo span{color:var(--amber);}
  .admin-badge{font-size:0.65rem;letter-spacing:0.2em;color:var(--nova);border:1px solid rgba(255,77,109,0.3);padding:4px 10px;border-radius:20px;}
  .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:40px;}
  .stat-card{background:var(--panel);border:1px solid rgba(0,229,255,0.1);border-radius:12px;padding:24px;text-align:center;}
  .stat-val{font-family:'Orbitron',monospace;font-size:2rem;font-weight:700;color:var(--glow);margin-bottom:4px;}
  .stat-val.amber{color:var(--amber);}
  .stat-val.nova{color:var(--nova);}
  .stat-label{font-size:0.7rem;letter-spacing:0.2em;color:var(--muted);text-transform:uppercase;}
  .sec-title{font-family:'Orbitron',monospace;font-size:0.65rem;letter-spacing:0.3em;color:var(--glow);text-transform:uppercase;margin-bottom:20px;display:flex;align-items:center;gap:12px;}
  .sec-title::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,rgba(0,229,255,0.3),transparent);}
  .table-wrap{overflow-x:auto;}
  table{width:100%;border-collapse:collapse;font-size:0.85rem;}
  th{font-family:'Orbitron',monospace;font-size:0.55rem;letter-spacing:0.15em;color:var(--muted);text-transform:uppercase;padding:12px 16px;border-bottom:1px solid rgba(0,229,255,0.1);text-align:left;white-space:nowrap;}
  td{padding:12px 16px;border-bottom:1px solid rgba(255,255,255,0.04);vertical-align:middle;}
  tr:hover td{background:rgba(0,229,255,0.03);}
  .pass-id{font-family:'Orbitron',monospace;font-size:0.65rem;color:var(--glow);}
  .seats-cell{font-family:'Orbitron',monospace;font-size:0.7rem;color:var(--amber);}
  .zone-vip{color:var(--amber);font-size:0.7rem;letter-spacing:0.1em;}
  .zone-std{color:var(--glow);font-size:0.7rem;letter-spacing:0.1em;}
  .price{font-family:'Orbitron',monospace;color:var(--glow);}
  .empty{text-align:center;padding:60px;color:var(--muted);font-size:0.9rem;letter-spacing:0.1em;}
  a.back{font-family:'Orbitron',monospace;font-size:0.6rem;letter-spacing:0.15em;color:var(--muted);text-decoration:none;border:1px solid rgba(255,255,255,0.1);padding:8px 16px;border-radius:6px;}
  a.back:hover{color:var(--star);}
</style>
</head>
<body>
<div class="wrapper">
  <header>
    <div class="logo">COSMO<span>PASS</span> &nbsp;/&nbsp; <span style="color:var(--muted);font-size:0.8rem">ADMIN</span></div>
    <div style="display:flex;gap:12px;align-items:center;">
      <span class="admin-badge">⬤ MISSION CONTROL</span>
      <a href="index.php" class="back">← BOOKING SITE</a>
    </div>
  </header>

  <!-- STATS -->
  <div class="stats">
    <div class="stat-card">
      <div class="stat-val"><?= $totalBookings ?></div>
      <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-card">
      <div class="stat-val amber">₹<?= number_format($totalRevenue) ?></div>
      <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card">
      <div class="stat-val nova"><?= $totalSeats ?></div>
      <div class="stat-label">Seats Booked</div>
    </div>
  </div>

  <!-- BOOKINGS TABLE -->
  <div class="sec-title">All Bookings</div>
  <div class="table-wrap">
    <?php if (empty($bookings)): ?>
      <div class="empty">🛸 No bookings yet. Launch the site to get started!</div>
    <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Pass ID</th>
          <th>Passenger</th>
          <th>Email</th>
          <th>Event</th>
          <th>Date</th>
          <th>Seats</th>
          <th>Zone</th>
          <th>Total</th>
          <th>Booked At</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr>
          <td class="pass-id"><?= htmlspecialchars($b['pass_id']) ?></td>
          <td><?= htmlspecialchars($b['first_name'].' '.$b['last_name']) ?></td>
          <td style="color:var(--muted);font-size:0.8rem"><?= htmlspecialchars($b['email']) ?></td>
          <td style="font-size:0.8rem"><?= htmlspecialchars($b['event_name']) ?></td>
          <td style="color:var(--muted);font-size:0.75rem"><?= htmlspecialchars($b['event_date']) ?></td>
          <td class="seats-cell"><?= htmlspecialchars($b['seats']) ?></td>
          <td><?php
            $z = $b['zone'];
            if (str_contains($z,'VIP') && str_contains($z,'Standard')) echo '<span class="zone-vip">VIP+STD</span>';
            elseif (str_contains($z,'VIP')) echo '<span class="zone-vip">VIP</span>';
            else echo '<span class="zone-std">STD</span>';
          ?></td>
          <td class="price">₹<?= number_format($b['total_price']) ?></td>
          <td style="color:var(--muted);font-size:0.75rem"><?= date('d M Y, H:i', strtotime($b['booked_at'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
</body>
</html>