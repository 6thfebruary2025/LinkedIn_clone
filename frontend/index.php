<script>
  // Clean cross-environment API pathway configuration
  const API_PREFIX = window.location.pathname.includes('/public/') ? '../api/' : '/api/';

  document.getElementById('loginForm').addEventListener('submit', async e => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target).entries());
    const res = await fetch(API_PREFIX + 'login.php', {
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
    const res = await fetch(API_PREFIX + 'signup.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify(data)
    }).then(r => r.json());
    if (res.success) {
      alert('Signup complete! Please log in.');
      showLogin();
    } else {
      alert(res.error || 'Signup failed');
    }
  });
</script>
