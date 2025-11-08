<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

require_once __DIR__ . '/../api/config.php';

$userId = $_GET['user_id'] ?? $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare("SELECT id, name, email, profile_image, about, skills, created_at FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("User not found");

// Count user posts
$stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
$stmt->execute([$userId]);
$postCount = $stmt->fetchColumn();

$isOwnProfile = ($userId == $_SESSION['user_id']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($user['name']) ?> | LinkedIn Clone</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --linkedin-blue: #0a66c2;
      --linkedin-bg: #f3f2ef;
      --card-radius: 8px;
    }
    body {
      background: var(--linkedin-bg);
      font-family: "Inter", system-ui, sans-serif;
    }

    .navbar {
      background: linear-gradient(135deg, #0077b5, var(--linkedin-blue));
    }
    .btn-linkedin {
      background-color: var(--linkedin-blue);
      color: #fff;
      border-radius: 4px;
      font-weight: 500;
    }
    .btn-linkedin:hover {
      background-color: #004182;
      color: #fff;
    }

    .cover {
      height: 230px;
      background: linear-gradient(135deg, #0077b5, var(--linkedin-blue));
      position: relative;
    }
    .profile-container {
      max-width: 900px;
      margin: -80px auto 40px;
    }
    .profile-card {
      background: #fff;
      border-radius: var(--card-radius);
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      padding: 20px;
      position: relative;
    }
    .profile-photo {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #fff;
      position: absolute;
      top: -75px;
      left: 30px;
      background: #fff;
    }
    .profile-info {
      margin-left: 200px;
      margin-top: 20px;
    }
    .profile-info h3 {
      font-weight: 600;
      margin-bottom: 5px;
    }
    .profile-info p {
      color: #666;
      margin: 0;
    }
    .section-card {
      background: #fff;
      border-radius: var(--card-radius);
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
      padding: 25px;
      margin-top: 20px;
    }
    .post-card {
      background: #fff;
      border-radius: var(--card-radius);
      box-shadow: 0 1px 4px rgba(0,0,0,0.08);
      padding: 20px;
      margin-bottom: 15px;
    }
    .post-card img {
      max-width: 100%;
      border-radius: 6px;
      margin-top: 10px;
    }
    .upload-label {
      color: var(--linkedin-blue);
      font-size: 0.9rem;
      cursor: pointer;
      display: inline-block;
      margin-top: 8px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand fw-semibold" href="feed.php">LinkedIn Clone</a>
    <div class="d-flex align-items-center gap-3 text-white">
      <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
      <a href="logout.php" class="btn btn-linkedin">Logout</a>
    </div>
  </div>
</nav>

<div class="cover"></div>

<div class="profile-container">
  <div class="profile-card">
    <?php if ($user['profile_image']): ?>
      <img src="../<?= htmlspecialchars($user['profile_image']) ?>" class="profile-photo" alt="Profile">
    <?php else: ?>
      <img src="https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=0077b5&color=fff" class="profile-photo" alt="Profile">
    <?php endif; ?>

    <div class="profile-info">
      <h3><?= htmlspecialchars($user['name']) ?></h3>
      <p><?= htmlspecialchars($user['email']) ?></p>
      <p class="text-muted small">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
      <p class="text-muted small">Posts: <?= $postCount ?></p>
      <?php if ($isOwnProfile): ?>
        <form id="uploadForm" enctype="multipart/form-data" class="mt-1">
          <input type="file" name="profile_image" id="profileImageInput" accept="image/*" hidden>
          <label for="profileImageInput" class="upload-label">Change profile picture</label>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- About Section -->
  <div class="section-card">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="fw-semibold mb-0">About</h5>
      <?php if ($isOwnProfile): ?>
        <button class="btn btn-sm btn-linkedin" data-bs-toggle="modal" data-bs-target="#editAboutModal">Edit</button>
      <?php endif; ?>
    </div>
    <p id="aboutText"><?= $user['about'] ? nl2br(htmlspecialchars($user['about'])) : '<span class="text-muted">No about info added.</span>' ?></p>
  </div>

  <!-- Skills Section -->
  <div class="section-card">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="fw-semibold mb-0">Skills</h5>
      <?php if ($isOwnProfile): ?>
        <button class="btn btn-sm btn-linkedin" data-bs-toggle="modal" data-bs-target="#editSkillsModal">Edit</button>
      <?php endif; ?>
    </div>
    <?php if ($user['skills']): ?>
      <?php
        $skills = explode(',', $user['skills']);
        foreach($skills as $s){
          echo '<span class="badge bg-light text-dark border me-1 mb-1">'.htmlspecialchars(trim($s)).'</span>';
        }
      ?>
    <?php else: ?>
      <p class="text-muted mb-0">No skills added.</p>
    <?php endif; ?>
  </div>

  <!-- Posts Section -->
  <div class="section-card">
    <h5 class="fw-semibold mb-3">Recent Posts</h5>
    <div id="userPosts"></div>
  </div>
</div>

<!-- Edit About Modal -->
<div class="modal fade" id="editAboutModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit About</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editAboutForm">
          <textarea name="about" class="form-control" rows="5"><?= htmlspecialchars($user['about'] ?? '') ?></textarea>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="saveAboutBtn" class="btn btn-linkedin">Save</button>
      </div>
    </div>
  </div>
</div>

<!-- Edit Skills Modal -->
<div class="modal fade" id="editSkillsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Skills (comma separated)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="editSkillsForm">
          <input type="text" name="skills" class="form-control" value="<?= htmlspecialchars($user['skills'] ?? '') ?>">
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="saveSkillsBtn" class="btn btn-linkedin">Save</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function getJSON(url){ const r = await fetch(url); return r.json(); }

async function loadUserPosts(){
  const res = await getJSON('../api/fetch_posts.php');
  const box = document.getElementById('userPosts');
  if(!res.success){ box.innerHTML='<div class="alert alert-danger">Could not load posts</div>'; return; }
  const posts = res.posts.filter(p => p.user_id == <?= json_encode($userId) ?>);
  if(posts.length === 0){ box.innerHTML='<div class="text-muted">No posts yet.</div>'; return; }

  box.innerHTML = posts.map(p => `
    <div class="post-card">
      <div>${p.content}</div>
      ${p.image_path ? `<img src="../${p.image_path}" alt="Post image">` : ''}
      <small class="text-muted d-block mt-2">${new Date(p.created_at).toLocaleString()}</small>
    </div>
  `).join('');
}

<?php if ($isOwnProfile): ?>
// Profile image upload
document.getElementById('profileImageInput').addEventListener('change', async function(){
  const formData = new FormData();
  formData.append('profile_image', this.files[0]);
  const res = await fetch('../api/upload_profile_image.php', { method:'POST', body:formData }).then(r=>r.json());
  if(res.success){ location.reload(); }
});

// Save About
document.getElementById('saveAboutBtn').addEventListener('click', async ()=>{
  const formData = new FormData(document.getElementById('editAboutForm'));
  const res = await fetch('../api/update_about.php',{method:'POST',body:formData}).then(r=>r.json());
  if(res.success){ location.reload(); }
});

// Save Skills
document.getElementById('saveSkillsBtn').addEventListener('click', async ()=>{
  const formData = new FormData(document.getElementById('editSkillsForm'));
  const res = await fetch('../api/update_skills.php',{method:'POST',body:formData}).then(r=>r.json());
  if(res.success){ location.reload(); }
});
<?php endif; ?>

loadUserPosts();
</script>
</body>
</html>
