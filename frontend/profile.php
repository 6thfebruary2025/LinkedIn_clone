<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

// Fixed absolute directory reference mapping from public/ to root/api/
require_once __DIR__ . '/../api/config.php';

$userId = $_GET['user_id'] ?? $_SESSION['user_id'];

// Fetch user info via standard PDO mapping settled in config.php
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
    body { background-color: #f3f6f9; font-family: system-ui, -apple-system, sans-serif; }
    .profile-banner { height: 150px; background: linear-gradient(135deg, #0077b5, #0a66c2); border-top-left-radius: 8px; border-top-right-radius: 8px; }
    .avatar-holder { margin-top: -75px; margin-left: 24px; }
    .avatar-img { width: 150px; height: 150px; object-fit: cover; border: 4px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .card-custom { background: white; border: 1px solid #e0e0e0; border-radius: 8px; margin-bottom: 20px; padding: 24px; }
  </style>
</head>
<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
      <a class="navbar-brand" href="feed.php">LinkedIn Clone</a>
      <div class="navbar-nav ms-auto">
        <a class="nav-link text-white" href="feed.php">Home Feed</a>
        <a class="nav-link text-white" href="logout.php">Logout</a>
      </div>
    </div>
  </nav>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        
        <div class="card-custom p-0 overflow-hidden">
          <div class="profile-banner"></div>
          <div class="avatar-holder d-flex align-items-end justify-content-between pe-4 pb-3">
            <div class="position-relative">
              <img src="../<?= !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'assets/default-avatar.png' ?>" class="rounded-circle avatar-img bg-light" id="profileAvatar">
              <?php if ($isOwnProfile): ?>
                <input type="file" id="profileImageInput" class="d-none" accept="image/*">
                <button class="btn btn-sm btn-light position-absolute bottom-0 end-0 border" onclick="document.getElementById('profileImageInput').click();">📷</button>
              <?php endif; ?>
            </div>
          </div>
          <div class="px-4 pb-4">
            <h2 class="fw-bold m-0"><?= htmlspecialchars($user['name']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
            <div class="badge bg-secondary"><?= $postCount ?> Total Posts</div>
          </div>
        </div>

        <div class="card-custom">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="fw-bold m-0">About</h4>
            <?php if ($isOwnProfile): ?>
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAboutModal">Edit</button>
            <?php endif; ?>
          </div>
          <p class="text-secondary mb-0"><?= !empty($user['about']) ? nl2br(htmlspecialchars($user['about'])) : 'No about description provided yet.' ?></p>
        </div>

        <div class="card-custom">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="fw-bold m-0">Skills</h4>
            <?php if ($isOwnProfile): ?>
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editSkillsModal">Edit</button>
            <?php endif; ?>
          </div>
          <p class="text-secondary mb-0"><?= !empty($user['skills']) ? htmlspecialchars($user['skills']) : 'No skills listed yet.' ?></p>
        </div>

      </div>
    </div>
  </div>

  <?php if ($isOwnProfile): ?>
  <div class="modal fade" id="editAboutModal" mercantile-tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="editAboutForm">
          <div class="modal-header">
            <h5 class="modal-title">Edit About Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <textarea class="form-control" name="about" rows="4"><?= htmlspecialchars($user['about'] ?? '') ?></textarea>
          </div>
          <div class="modal-content modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="saveAboutBtn">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="editSkillsModal" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form id="editSkillsForm">
          <div class="modal-header">
            <h5 class="modal-title">Edit Skills Set</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="text" class="form-control" name="skills" value="<?= htmlspecialchars($user['skills'] ?? '') ?>" placeholder="e.g. PHP, JavaScript, SQL">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="saveSkillsBtn">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Handles runtime environment routing dynamically for production
    const API_PREFIX = window.location.pathname.includes('/public/') ? '../api/' : '/api/';

    document.getElementById('profileImageInput').addEventListener('change', async function() {
      const formData = new FormData();
      formData.append('profile_image', this.files[0]);
      const res = await fetch(API_PREFIX + 'upload_profile_image.php', { method:'POST', body:formData }).then(r=>r.json());
      if(res.success){ location.reload(); } else { alert(res.error || 'Upload error'); }
    });

    document.getElementById('saveAboutBtn').addEventListener('click', async () => {
      const formData = new FormData(document.getElementById('editAboutForm'));
      const res = await fetch(API_PREFIX + 'update_about.php', { method:'POST', body:formData }).then(r=>r.json());
      if(res.success){ location.reload(); }
    });

    document.getElementById('saveSkillsBtn').addEventListener('click', async () => {
      const formData = new FormData(document.getElementById('editSkillsForm'));
      const res = await fetch(API_PREFIX + 'update_skills.php', { method:'POST', body:formData }).then(r=>r.json());
      if(res.success){ location.reload(); }
    });
  </script>
  <?php endif; ?>
</body>
</html>
