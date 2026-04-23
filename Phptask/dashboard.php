<?php
require_once 'config.php';
session_start();

// Protect this page — redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name  = htmlspecialchars($_SESSION['user_name']);
$user_email = htmlspecialchars($_SESSION['user_email']);

// Get last login time from cookie
$last_login = isset($_COOKIE[COOKIE_LAST_LOGIN]) 
    ? htmlspecialchars($_COOKIE[COOKIE_LAST_LOGIN]) 
    : null;

// Get member since date from DB
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$member_since = $row ? date('F j, Y', strtotime($row['created_at'])) : 'Unknown';
$stmt->close();
$conn->close();

// Greeting based on hour
$hour = (int) date('G');
if ($hour < 12)      $greeting = 'Good morning';
elseif ($hour < 17)  $greeting = 'Good afternoon';
else                 $greeting = 'Good evening';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Vault</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0c0c0e;
    --surface: #141416;
    --surface-2: #1a1a1c;
    --border: #242428;
    --text: #e8e6e0;
    --muted: #7a7870;
    --accent: #c8a96e;
    --accent-dim: rgba(200,169,110,0.1);
    --success: #70c8a0;
    --radius: 4px;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
  }

  /* Ambient glow */
  .ambient {
    position: fixed;
    width: 800px; height: 800px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(200,169,110,0.04) 0%, transparent 65%);
    top: -300px; right: -300px;
    pointer-events: none;
    z-index: 0;
  }

  /* ── Header ── */
  header {
    border-bottom: 1px solid var(--border);
    padding: 0 3rem;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    background: rgba(12,12,14,0.9);
    backdrop-filter: blur(12px);
    z-index: 100;
  }

  .brand {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--accent);
  }

  .header-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
  }

  .user-pill {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.82rem;
    color: var(--muted);
  }
  .user-avatar {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: var(--accent-dim);
    border: 1px solid var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 0.8rem;
    color: var(--accent);
    font-weight: 700;
  }

  .logout-btn {
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--muted);
    text-decoration: none;
    border: 1px solid var(--border);
    padding: 0.4rem 1rem;
    border-radius: var(--radius);
    transition: border-color 0.2s, color 0.2s;
  }
  .logout-btn:hover { border-color: var(--accent); color: var(--accent); }

  /* ── Main layout ── */
  main {
    max-width: 1000px;
    margin: 0 auto;
    padding: 3rem 2rem;
    position: relative;
    z-index: 1;
  }

  /* ── Hero welcome ── */
  .hero {
    margin-bottom: 3rem;
    animation: slideUp 0.5s cubic-bezier(0.22,1,0.36,1) both;
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .greeting-tag {
    font-size: 0.72rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .greeting-tag::before {
    content: '';
    width: 24px; height: 1px;
    background: var(--accent);
    display: inline-block;
  }

  .hero h1 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(2rem, 5vw, 3.2rem);
    font-weight: 700;
    line-height: 1.1;
    margin-bottom: 0.75rem;
  }
  .hero h1 em {
    font-style: italic;
    color: var(--accent);
  }

  .hero-sub {
    font-size: 0.9rem;
    color: var(--muted);
    font-weight: 300;
  }

  /* ── Stats grid ── */
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1px;
    background: var(--border);
    border: 1px solid var(--border);
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 2rem;
    animation: slideUp 0.5s 0.1s cubic-bezier(0.22,1,0.36,1) both;
  }

  .stat-card {
    background: var(--surface);
    padding: 1.5rem;
    position: relative;
    transition: background 0.2s;
  }
  .stat-card:hover { background: var(--surface-2); }

  .stat-label {
    font-size: 0.68rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 0.6rem;
  }
  .stat-value {
    font-size: 1.05rem;
    font-weight: 500;
    color: var(--text);
    word-break: break-all;
  }
  .stat-icon {
    position: absolute;
    top: 1.25rem; right: 1.25rem;
    font-size: 1.1rem;
    opacity: 0.35;
  }

  /* ── Info panel ── */
  .info-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 2px;
    padding: 2rem;
    margin-bottom: 2rem;
    animation: slideUp 0.5s 0.2s cubic-bezier(0.22,1,0.36,1) both;
  }

  .panel-title {
    font-family: 'Playfair Display', serif;
    font-size: 1rem;
    margin-bottom: 1.25rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .session-badge {
    font-size: 0.68rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--success);
    background: rgba(112,200,160,0.1);
    border: 1px solid rgba(112,200,160,0.25);
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 0.4rem;
  }
  .session-badge::before {
    content: '';
    width: 6px; height: 6px;
    background: var(--success);
    border-radius: 50%;
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
  }

  .info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border);
    gap: 1rem;
  }
  .info-row:last-child { border-bottom: none; padding-bottom: 0; }

  .info-key {
    font-size: 0.78rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    flex-shrink: 0;
    min-width: 120px;
  }
  .info-val {
    font-size: 0.88rem;
    color: var(--text);
    text-align: right;
    word-break: break-all;
  }
  .info-val .badge {
    display: inline-block;
    font-size: 0.68rem;
    padding: 0.2rem 0.6rem;
    border-radius: 20px;
    background: var(--accent-dim);
    color: var(--accent);
    border: 1px solid rgba(200,169,110,0.25);
    letter-spacing: 0.06em;
  }

  /* ── Cookie info panel ── */
  .cookie-panel {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 2px;
    padding: 1.5rem 2rem;
    animation: slideUp 0.5s 0.3s cubic-bezier(0.22,1,0.36,1) both;
  }

  .cookie-panel .panel-title {
    font-size: 0.9rem;
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    color: var(--muted);
  }

  .cookie-tag {
    font-size: 0.68rem;
    padding: 0.2rem 0.5rem;
    border-radius: 3px;
    background: rgba(200,169,110,0.08);
    border: 1px solid rgba(200,169,110,0.2);
    color: var(--accent);
    font-family: 'Courier New', monospace;
  }

  @media (max-width: 600px) {
    header { padding: 0 1.25rem; }
    main { padding: 2rem 1.25rem; }
    .info-key { min-width: 100px; }
  }
</style>
</head>
<body>
<div class="ambient"></div>

<header>
  <span class="brand">Vault</span>
  <div class="header-right">
    <div class="user-pill">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
      <span><?= $user_name ?></span>
    </div>
    <a href="logout.php" class="logout-btn">Sign out</a>
  </div>
</header>

<main>
  <!-- Hero -->
  <div class="hero">
    <div class="greeting-tag"><?= $greeting ?></div>
    <h1><?= $greeting ?>,<br><em><?= $user_name ?>.</em></h1>
    <p class="hero-sub">Your session is active. Here's your account overview.</p>
  </div>

  <!-- Stats grid -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Signed in as</div>
      <div class="stat-value"><?= $user_email ?></div>
      <div class="stat-icon">@</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Member since</div>
      <div class="stat-value"><?= $member_since ?></div>
      <div class="stat-icon">📅</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Last login</div>
      <div class="stat-value">
        <?= $last_login ? $last_login : '<span style="color:var(--muted);font-size:0.8rem;">Not recorded yet</span>' ?>
      </div>
      <div class="stat-icon">🕐</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Current time</div>
      <div class="stat-value"><?= date('H:i — D, M j') ?></div>
      <div class="stat-icon">⏱</div>
    </div>
  </div>

  <!-- Session details -->
  <div class="info-panel">
    <div class="panel-title">
      Session Details
      <div class="session-badge">Session Active</div>
    </div>

    <div class="info-row">
      <span class="info-key">User ID</span>
      <span class="info-val"><span class="badge">#<?= htmlspecialchars($_SESSION['user_id']) ?></span></span>
    </div>
    <div class="info-row">
      <span class="info-key">Full Name</span>
      <span class="info-val"><?= $user_name ?></span>
    </div>
    <div class="info-row">
      <span class="info-key">Email</span>
      <span class="info-val"><?= $user_email ?></span>
    </div>
    <div class="info-row">
      <span class="info-key">Session ID</span>
      <span class="info-val" style="font-family:'Courier New',monospace;font-size:0.78rem;color:var(--muted);">
        <?= substr(session_id(), 0, 20) ?>…
      </span>
    </div>
    <div class="info-row">
      <span class="info-key">Auth Method</span>
      <span class="info-val"><span class="badge">PHP Session</span></span>
    </div>
  </div>

  <!-- Cookie info -->
  <div class="cookie-panel">
    <div class="panel-title">
      Active Cookies
    </div>
    <div class="info-row">
      <span class="info-key">Email cookie</span>
      <span class="info-val">
        <?php if (isset($_COOKIE[COOKIE_EMAIL])): ?>
          <span class="cookie-tag"><?= COOKIE_EMAIL ?></span>
          &nbsp;<?= htmlspecialchars($_COOKIE[COOKIE_EMAIL]) ?>
        <?php else: ?>
          <span style="color:var(--muted);font-size:0.82rem;">Not set — check "Remember my email" at login</span>
        <?php endif; ?>
      </span>
    </div>
    <div class="info-row">
      <span class="info-key">Last login</span>
      <span class="info-val">
        <?php if ($last_login): ?>
          <span class="cookie-tag"><?= COOKIE_LAST_LOGIN ?></span>
          &nbsp;<?= $last_login ?>
        <?php else: ?>
          <span style="color:var(--muted);font-size:0.82rem;">Not recorded</span>
        <?php endif; ?>
      </span>
    </div>
  </div>
</main>
</body>
</html>
