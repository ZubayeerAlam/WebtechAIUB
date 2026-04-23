<?php
require_once 'config.php';
session_start();

// Already logged in → go to dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $conn = getDBConnection();

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $stmt->close();

            // Hash password and insert user
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashedPassword);

            if ($stmt->execute()) {
                $success = 'Account created successfully! You can now log in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
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
<title>Register — Vault</title>
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
    --success: #70c8a0;
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

  /* Ambient background */
  body::before {
    content: '';
    position: fixed;
    width: 600px; height: 600px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(200,169,110,0.06) 0%, transparent 70%);
    top: -200px; right: -200px;
    pointer-events: none;
  }
  body::after {
    content: '';
    position: fixed;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(112,120,200,0.04) 0%, transparent 70%);
    bottom: -150px; left: -100px;
    pointer-events: none;
  }

  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 2px;
    width: 100%;
    max-width: 440px;
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
    font-size: 1.9rem;
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
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }
  .alert-error   { background: rgba(224,112,112,0.1); border: 1px solid rgba(224,112,112,0.3); color: var(--error); }
  .alert-success { background: rgba(112,200,160,0.1); border: 1px solid rgba(112,200,160,0.3); color: var(--success); }

  .form-group {
    margin-bottom: 1.25rem;
    animation: fadeIn 0.4s ease both;
  }
  .form-group:nth-child(1) { animation-delay: 0.1s; }
  .form-group:nth-child(2) { animation-delay: 0.15s; }
  .form-group:nth-child(3) { animation-delay: 0.2s; }
  .form-group:nth-child(4) { animation-delay: 0.25s; }

  @keyframes fadeIn {
    from { opacity: 0; transform: translateX(-8px); }
    to   { opacity: 1; transform: translateX(0); }
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

  input {
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
  input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-dim);
  }
  input::placeholder { color: var(--muted); opacity: 0.6; }

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
    margin-top: 0.75rem;
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
</style>
</head>
<body>
<div class="card">
  <div class="card-top-line"></div>

  <span class="brand">Vault</span>
  <h1>Create<br>your account</h1>
  <p class="subtitle">Join securely. It only takes a moment.</p>

  <?php if ($error): ?>
    <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" placeholder="Jane Smith"
             value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" placeholder="jane@example.com"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    </div>

    <div class="form-group">
      <label for="password">Password <span style="color:var(--muted);font-size:0.68rem;">(min. 8 chars)</span></label>
      <input type="password" id="password" name="password" placeholder="••••••••" required>
    </div>

    <div class="form-group">
      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn">Create Account →</button>
  </form>

  <div class="divider"><span>Already a member?</span></div>
  <div class="link-row"><a href="login.php">Sign in to your account</a></div>
</div>
</body>
</html>
