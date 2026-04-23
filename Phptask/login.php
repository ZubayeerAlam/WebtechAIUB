<?php
require_once 'config.php';
session_start();

// Already logged in → go to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

// Pre-fill email from cookie if available
$remembered_email = isset($_COOKIE[COOKIE_EMAIL]) ? htmlspecialchars($_COOKIE[COOKIE_EMAIL]) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                // Start session
                session_regenerate_id(true);
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email']= $user['email'];

                // Remember email cookie
                if ($remember) {
                    setcookie(COOKIE_EMAIL, $email, time() + COOKIE_EXPIRY, '/', '', false, true);
                } else {
                    setcookie(COOKIE_EMAIL, '', time() - 3600, '/');
                }

                // Store last login time in cookie
                $loginTime = date('Y-m-d H:i:s');
                setcookie(COOKIE_LAST_LOGIN, $loginTime, time() + COOKIE_EXPIRY, '/', '', false, true);

                header("Location: dashboard.php");
                exit;
            } else {
                $error = 'Incorrect email or password.';
            }
        } else {
            $error = 'Incorrect email or password.';
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — Vault</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  :root {
    --bg: #0c0c0e;
    --surface: #141416;
    --border: #242428;
    --text: #e8e6e0;
    --muted: #7a7870;
    --accent: #c8a96e;
    --accent-dim: rgba(200,169,110,0.12);
    --error: #e07070;
    --radius: 4px;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
  }

  body::before {
    content: '';
    position: fixed;
    width: 700px; height: 700px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(200,169,110,0.05) 0%, transparent 70%);
    bottom: -300px; left: -200px;
    pointer-events: none;
  }

  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 2px;
    width: 100%;
    max-width: 400px;
    padding: 3rem 2.5rem;
    position: relative;
    animation: slideUp 0.5s cubic-bezier(0.22,1,0.36,1) both;
  }

  @keyframes slideUp {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .card-top-line {
    position: absolute;
    top: 0; left: 10%; right: 10%;
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--accent), transparent);
  }

  .brand {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 2rem;
    display: block;
  }

  h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.15;
    margin-bottom: 0.5rem;
  }

  .subtitle {
    font-size: 0.85rem;
    color: var(--muted);
    margin-bottom: 2rem;
    font-weight: 300;
  }

  .alert {
    padding: 0.75rem 1rem;
    border-radius: var(--radius);
    font-size: 0.85rem;
    margin-bottom: 1.5rem;
    background: rgba(224,112,112,0.1);
    border: 1px solid rgba(224,112,112,0.3);
    color: var(--error);
  }

  .form-group {
    margin-bottom: 1.25rem;
  }

  label {
    display: block;
    font-size: 0.72rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 0.5rem;
    font-weight: 500;
  }

  input[type="email"],
  input[type="password"] {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 0.75rem 1rem;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    border-radius: var(--radius);
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  input[type="email"]:focus,
  input[type="password"]:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
  }
  input::placeholder { color: var(--muted); opacity: 0.6; }

  .remember-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 1.5rem;
  }
  .remember-row input[type="checkbox"] {
    width: 16px; height: 16px;
    accent-color: var(--accent);
    cursor: pointer;
  }
  .remember-row label {
    font-size: 0.82rem;
    letter-spacing: 0;
    text-transform: none;
    color: var(--muted);
    margin: 0;
    cursor: pointer;
  }

  .btn {
    width: 100%;
    padding: 0.85rem 1rem;
    background: var(--accent);
    color: #0c0c0e;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.85rem;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    border: none;
    border-radius: var(--radius);
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s;
  }
  .btn:hover { opacity: 0.88; transform: translateY(-1px); }
  .btn:active { transform: translateY(0); }

  .divider {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 1.75rem 0 1.5rem;
  }
  .divider::before, .divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
  }
  .divider span {
    font-size: 0.72rem;
    color: var(--muted);
    letter-spacing: 0.1em;
    text-transform: uppercase;
  }

  .link-row {
    text-align: center;
    font-size: 0.85rem;
    color: var(--muted);
  }
  .link-row a {
    color: var(--accent);
    text-decoration: none;
    font-weight: 500;
  }
  .link-row a:hover { text-decoration: underline; }

  .cookie-hint {
    font-size: 0.72rem;
    color: var(--muted);
    margin-top: 0.35rem;
    opacity: 0.7;
  }
</style>
</head>
<body>
<div class="card">
  <div class="card-top-line"></div>

  <span class="brand">Vault</span>
  <h1>Welcome<br>back.</h1>
  <p class="subtitle">Sign in to access your dashboard.</p>

  <?php if ($error): ?>
    <div class="alert">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($remembered_email): ?>
    <p class="cookie-hint" style="margin-bottom:1rem;">✓ Email remembered from your last session.</p>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email"
             placeholder="jane@example.com"
             value="<?= $remembered_email ?>" required>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="••••••••" required>
    </div>

    <div class="remember-row">
      <input type="checkbox" id="remember" name="remember"
             <?= $remembered_email ? 'checked' : '' ?>>
      <label for="remember">Remember my email for next time</label>
    </div>

    <button type="submit" class="btn">Sign In →</button>
  </form>

  <div class="divider"><span>New here?</span></div>
  <div class="link-row"><a href="register.php">Create a free account</a></div>
</div>
</body>
</html>
