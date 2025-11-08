<?php
session_start();
if (!empty($_SESSION['user_id'])) {
  header('Location: feed.php');
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>LinkedIn Clone – Simple Social Media Website</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      background-color: #f3f6f9;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .auth-container {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      overflow: hidden;
      display: flex;
      width: 940px;
      max-width: 95%;
    }
    .auth-left {
      flex: 1;
      background: linear-gradient(135deg, #0077b5, #0a66c2);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      flex-direction: column;
      padding: 50px 30px;
    }
    .auth-left h2 {
      font-size: 1.9rem;
      font-weight: 700;
      line-height: 1.3;
    }
    .auth-right {
      flex: 1;
      padding: 45px 40px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .form-control, .btn { border-radius: 10px; }
    .toggle-btn {
      cursor: pointer;
      user-select: none;
      color: #0a66c2;
      font-weight: 500;
    }
    .input-group button {
      border-color: #ced4da;
    }
    .eye-icon {
      width: 20px;
      height: 20px;
    }
  </style>
</head>
<body>

<div class="auth-container">
  <div class="auth-left">
    <h2>LinkedIn Clone</h2>
    <p class="text-white-50 mt-3"> Simple Social Media Website</p>
  </div>

  <div class="auth-right">
    <h4 id="formTitle" class="mb-3 text-center">Login to your account</h4>

    <!-- Login Form -->
    <form id="loginForm">
      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input id="passwordLogin" type="password" class="form-control" name="password" required>
          <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('passwordLogin','eyeLogin')" title="Show / hide password">
            <img id="eyeLogin" src="https://img.icons8.com/ios-filled/50/000000/closed-eye.png" class="eye-icon">
          </button>
        </div>
      </div>
      <button class="btn w-100 mb-3" style="background:linear-gradient(135deg,#0077b5,#0a66c2);color:#fff;" type="submit">Login</button>

      <div class="text-center text-muted">Don't have an account?
        <span class="toggle-btn" onclick="showSignup()">Create one</span>
      </div>
    </form>

    <!-- Sign Up Form -->
    <form id="signupForm" class="d-none">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input id="passwordSignup" type="password" class="form-control" name="password" required>
          <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('passwordSignup','eyeSignup')">
            <img id="eyeSignup" src="https://img.icons8.com/ios-filled/50/000000/closed-eye.png" class="eye-icon">
          </button>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
          <input id="confirmPasswordSignup" type="password" class="form-control" name="confirm_password" required>
          <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirmPasswordSignup','eyeConfirm')">
            <img id="eyeConfirm" src="https://img.icons8.com/ios-filled/50/000000/closed-eye.png" class="eye-icon">
          </button>
        </div>
        <small id="passwordError" class="text-danger d-none">Passwords do not match!</small>
      </div>
      <button class="btn btn-primary w-100 mb-3" type="submit">Sign Up</button>
      <div class="text-center text-muted">Already have an account?
        <span class="toggle-btn" onclick="showLogin()">Login here</span>
      </div>
    </form>
  </div>
</div>

<script>
function togglePassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (input.type === "password") {
    input.type = "text";
    icon.src = "https://img.icons8.com/ios-filled/50/000000/visible--v1.png";
  } else {
    input.type = "password";
    icon.src = "https://img.icons8.com/ios-filled/50/000000/closed-eye.png";
  }
}

function showSignup(){
  document.getElementById('loginForm').classList.add('d-none');
  document.getElementById('signupForm').classList.remove('d-none');
  document.getElementById('formTitle').textContent = 'Create your account';
}
function showLogin(){
  document.getElementById('signupForm').classList.add('d-none');
  document.getElementById('loginForm').classList.remove('d-none');
  document.getElementById('formTitle').textContent = 'Login to your account';
}

document.getElementById('loginForm').addEventListener('submit', async e => {
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target).entries());
  const res = await fetch('../api/login.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(data)
  }).then(r => r.json());
  if (res.success) window.location.href = 'feed.php';
  else alert(res.error || 'Invalid credentials');
});

document.getElementById('signupForm').addEventListener('submit', async e => {
  e.preventDefault();
  const pass = document.getElementById('passwordSignup').value;
  const confirm = document.getElementById('confirmPasswordSignup').value;
  const error = document.getElementById('passwordError');
  if (pass !== confirm) {
    error.classList.remove('d-none');
    return;
  }
  error.classList.add('d-none');
  const data = Object.fromEntries(new FormData(e.target).entries());
  const res = await fetch('../api/signup.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(data)
  }).then(r => r.json());
  if (res.success) {
    alert('Account created! You can log in now.');
    showLogin();
  } else {
    alert(res.error || 'Signup failed');
  }
});
</script>
</body>
</html>
