<?php
session_start();
if (empty($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'User', ENT_QUOTES);
$userId = $_SESSION['user_id'];
$profileImage = $_SESSION['profile_image'] ?? null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Feed — LinkedIn Clone</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* --- Visual / Layout --- */
    body { background: #f3f6f9; font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial; }
    .navbar { background: linear-gradient(135deg,#0077b5,#0a66c2); }
    .navbar a, .navbar .text-white { color: #fff !important; }
    .btn-gradient { background: linear-gradient(135deg,#0077b5,#0a66c2); color:#fff; border:0; border-radius:8px; padding:6px 14px; }
    .sidebar-card { background:#fff; border-radius:12px; padding:18px; box-shadow:0 2px 10px rgba(0,0,0,0.04); margin-bottom:18px; }
    .post-box, .post-card { background:#fff; border-radius:12px; padding:18px; box-shadow:0 2px 12px rgba(0,0,0,0.04); margin-bottom:18px; }
    .post-image { max-width:100%; border-radius:10px; margin-top:10px; }
    .avatar { width:50px; height:50px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-weight:700; background:linear-gradient(135deg,#0077b5,#0a66c2); box-shadow:0 0 0 3px #fff; }
    .small-muted { font-size:0.85rem; color:#6c757d; }
    .comment-box { background:#f8f9fb; border-radius:10px; padding:8px; margin-bottom:8px; }
    .reply-input { margin-top:8px; }
    .rounded-white-border { box-shadow:0 0 0 3px #fff; border-radius:50%; }
    .trend-item { padding:8px 0; border-bottom:1px solid #eee; }
    .comment-controls button { margin-right:6px; }
    .like-active { color: #d63384; }
    .comment-count-badge { font-weight:600; }
    .post-content { white-space: pre-wrap; }
    .btn-sm { font-size: .78rem; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4">
  <div class="container d-flex justify-content-between align-items-center">
    <a class="navbar-brand fw-semibold" href="#">LinkedIn Clone</a>
    <div class="d-flex align-items-center gap-3 text-white">
      <div class="d-flex align-items-center gap-2">
        <?php if ($profileImage): ?>
          <img src="../<?= htmlspecialchars($profileImage) ?>" class="rounded-circle border border-2 border-light" style="width:38px;height:38px;object-fit:cover;">
        <?php else: ?>
          <div class="avatar border border-2 border-light" style="width:38px;height:38px;font-size:0.95rem;"><?= strtoupper(substr($userName,0,1)) ?></div>
        <?php endif; ?>
        <span><?= $userName ?></span>
      </div>
      <a href="logout.php" class="btn-gradient text-decoration-none">Logout</a>
    </div>
  </div>
</nav>

<div class="container">
  <div class="row g-4">
    <!-- Left sidebar -->
    <div class="col-lg-3 d-none d-lg-block">
      <div class="sidebar-card text-center">
        <?php if ($profileImage): ?>
          <img src="../<?= htmlspecialchars($profileImage) ?>" class="rounded-circle mb-2" style="width:70px;height:70px;object-fit:cover;">
        <?php else: ?>
          <div class="avatar mx-auto mb-2" style="width:70px;height:70px;font-size:1.6rem;"><?= strtoupper(substr($userName,0,1)) ?></div>
        <?php endif; ?>
        <h6 class="mb-0"><?= $userName ?></h6>
        <p class="text-muted small mb-3">Member since <?= date('Y') ?></p>
        <a href="profile.php?user_id=<?= $userId ?>" class="btn-gradient btn-sm w-100">View Profile</a>
      </div>

      <div class="sidebar-card">
        <h6 class="fw-bold mb-2">Your Stats</h6>
        <p class="mb-1 small text-muted">Posts: <span id="postCount">--</span></p>
        <p class="mb-1 small text-muted">Likes: <span id="likeCount">--</span></p>
      </div>
    </div>

    <!-- Center feed -->
    <div class="col-lg-6 col-md-8 mx-auto">
      <div class="post-box">
        <form id="postForm" enctype="multipart/form-data">
          <textarea name="content" id="postContent" class="form-control mb-3" rows="3" placeholder="Share your thoughts..." ></textarea>
          <div class="d-flex justify-content-between align-items-center">
            <input type="file" name="image" id="postImage" accept="image/*" class="form-control form-control-sm w-50">
            <div>
              <button id="postSubmit" class="btn-gradient fw-semibold" type="submit">Post</button>
            </div>
          </div>
        </form>
      </div>

      <div id="feed"></div>
    </div>

    <!-- Right sidebar -->
    <div class="col-lg-3 d-none d-lg-block">
      <div class="sidebar-card">
        <h6 class="fw-bold mb-2">Trending Topics</h6>
        <div class="trend-item">#WebDevelopment</div>
        <div class="trend-item">#Internships</div>
        <div class="trend-item">#TechCareers</div>
        <div class="trend-item">#Design</div>
        <div class="trend-item">#AI</div>
      </div>

      <div class="sidebar-card">
        <h6 class="fw-bold mb-2">People you may know</h6>
        <div id="peopleBox"></div>
      </div>
    </div>
  </div>
</div>

<script>
const API_BASE = 'https://astonishing-essence.up.railway.app/api/'; // <-- REPLACE with your Railway backend URL

function escapeHtml(text){ if(!text) return ''; return text.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;"); }

// ===== Render a single post =====
function renderPost(p){
  const created = new Date(p.created_at).toLocaleString();
  const liked = p.user_liked==1?'❤️':'🤍';
  const own = p.user_id == <?= json_encode($userId) ?>;
  const controls = own ? `<button class="btn btn-sm btn-gradient edit-post" data-id="${p.id}">Edit</button>
                          <button class="btn btn-sm btn-outline-danger delete-post" data-id="${p.id}">Delete</button>` : '';
  const img = p.image_path ? `<img src="../${escapeHtml(p.image_path)}" class="post-image">` : '';
  const avatar = p.profile_image
    ? `<img src="../${escapeHtml(p.profile_image)}" class="rounded-circle me-2" style="width:40px;height:40px;object-fit:cover;">`
    : `<div class="avatar me-2" style="width:40px;height:40px;font-size:1rem;">${escapeHtml(p.user_name.charAt(0).toUpperCase())}</div>`;
  
  return `
    <div class="post-card" data-id="${p.id}">
      <div class="d-flex justify-content-between align-items-start">
        <div class="d-flex align-items-center gap-2">
          ${avatar}
          <div>
            <div class="user-name">${escapeHtml(p.user_name)}</div>
            <small class="text-muted">${created}</small>
          </div>
        </div>
        <div>${controls}</div>
      </div>
      <div class="mt-3 text-dark">${escapeHtml(p.content).replace(/\n/g,'<br>')}</div>
      ${img}
      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-sm btn-outline-primary like-btn">${liked} ${p.like_count||0}</button>
        <button class="btn btn-sm btn-outline-secondary comment-toggle">💬 Comments (${p.comment_count||0})</button>
      </div>
      <div class="comments mt-2" style="display:none;"></div>
    </div>`;
}

// ===== Fetch JSON helper =====
async function getJSON(url){ const r = await fetch(url); return r.json(); }

// ===== Load feed =====
async function loadFeed(){
  const res = await getJSON(API_BASE + 'fetch_posts.php');
  const feed = document.getElementById('feed');
  if(!res.success){ feed.innerHTML='<div class="alert alert-danger">Failed to load posts</div>'; return; }
  if(res.posts.length==0){ feed.innerHTML='<div class="alert alert-secondary">No posts yet</div>'; return; }
  feed.innerHTML=res.posts.map(renderPost).join('');
  const myPosts = res.posts.filter(p => p.user_id == <?= json_encode($userId) ?>);
  document.getElementById('postCount').innerText = myPosts.length;
  document.getElementById('likeCount').innerText = myPosts.reduce((sum,p)=>sum+(p.like_count||0),0);
}

// ===== Post new content =====
document.getElementById('postForm').addEventListener('submit', async e=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await fetch(API_BASE + 'create_post.php',{method:'POST',body:fd}).then(r=>r.json());
  if(res.success){ e.target.reset(); loadFeed(); } else alert(res.error||'Failed to post');
});

// ===== Like post =====
document.addEventListener('click', async e=>{
  if(e.target.closest('.like-btn')){
    const btn = e.target.closest('.like-btn');
    const postId = btn.closest('.post-card').dataset.id;
    const res = await fetch(API_BASE + 'toggle_like.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({post_id:postId})
    }).then(r=>r.json());
    if(res.success) btn.innerHTML = (res.status==='liked'?'❤️':'🤍')+' '+res.likes;
  }
});

// ===== Delete post =====
document.addEventListener('click', async e=>{
  if(e.target.classList.contains('delete-post')){
    if(!confirm('Delete this post?')) return;
    const id = e.target.dataset.id;
    const res = await fetch(API_BASE + 'delete_post.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({post_id:id})
    }).then(r=>r.json());
    if(res.success) loadFeed(); else alert(res.error);
  }
});

// ===== Edit post =====
document.addEventListener('click', async e=>{
  if(e.target.classList.contains('edit-post')){
    const card = e.target.closest('.post-card');
    const id = card.dataset.id;
    const contentDiv = card.querySelector('.text-dark');
    const oldText = contentDiv.innerText.trim();

    const textarea = document.createElement('textarea');
    textarea.className = 'form-control mb-2';
    textarea.value = oldText;

    const saveBtn = document.createElement('button');
    saveBtn.className = 'btn btn-sm btn-gradient me-2';
    saveBtn.textContent = 'Save';

    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn btn-sm btn-outline-secondary';
    cancelBtn.textContent = 'Cancel';

    const controlDiv = document.createElement('div');
    controlDiv.className = 'mt-2';
    controlDiv.appendChild(saveBtn);
    controlDiv.appendChild(cancelBtn);

    contentDiv.innerHTML = '';
    contentDiv.appendChild(textarea);
    contentDiv.appendChild(controlDiv);

    cancelBtn.addEventListener('click', () => loadFeed());
    saveBtn.addEventListener('click', async () => {
      const newText = textarea.value.trim();
      if(!newText) return alert('Content cannot be empty');
      const res = await fetch(API_BASE + 'update_post.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({post_id:id, content:newText})
      }).then(r=>r.json());
      if(res.success) loadFeed(); else alert(res.error||'Failed to update');
    });
  }
});

// ===== Comments & Replies =====
document.addEventListener('click', async e=>{
  // Toggle comments
  if(e.target.classList.contains('comment-toggle')){
    const postCard = e.target.closest('.post-card');
    const commentDiv = postCard.querySelector('.comments');
    const postId = postCard.dataset.id;
    if(commentDiv.style.display==='none'||!commentDiv.style.display){
      commentDiv.style.display='block';
      commentDiv.innerHTML='<div class="text-muted small mb-2">Loading comments...</div>';
      const res = await fetch(API_BASE + `fetch_comments.php?post_id=${postId}`).then(r=>r.json());
      if(!res.success){ commentDiv.innerHTML='<div class="text-danger small">Error loading comments</div>'; return; }
      renderComments(commentDiv, res.comments, postId);
    } else commentDiv.style.display='none';
  }

  // Add comment
  if(e.target.classList.contains('add-comment')){
    const commentDiv = e.target.closest('.comments');
    const postCard = e.target.closest('.post-card');
    const postId = postCard.dataset.id;
    const input = commentDiv.querySelector('input');
    const text = input.value.trim();
    if(!text) return;
    const res = await fetch(API_BASE + 'add_comment.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({post_id:postId, text})
    }).then(r=>r.json());
    if(res.success){
      input.value = '';
      const refresh = await fetch(API_BASE + `fetch_comments.php?post_id=${postId}`).then(r=>r.json());
      renderComments(commentDiv, refresh.comments, postId);
      loadFeed(); // update comment count
    }
  }

  // Reply to comment
  if(e.target.classList.contains('reply-comment')){
    const replyBtn = e.target;
    const commentId = replyBtn.dataset.commentId;
    const input = document.querySelector(`#reply-input-${commentId}`);
    const text = input.value.trim();
    if(!text) return;
    const res = await fetch(API_BASE + 'add_reply.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({comment_id:commentId, text})
    }).then(r=>r.json());
    if(res.success){
      input.value = '';
      const postId = replyBtn.dataset.postId;
      const commentDiv = document.querySelector(`.post-card[data-id='${postId}'] .comments`);
      const refresh = await fetch(API_BASE + `fetch_comments.php?post_id=${postId}`).then(r=>r.json());
      renderComments(commentDiv, refresh.comments, postId);
    }
  }

  // Like comment
  if(e.target.classList.contains('like-comment')){
    const btn = e.target;
    const commentId = btn.dataset.commentId;
    const res = await fetch(API_BASE + 'toggle_comment_like.php',{
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({comment_id:commentId})
    }).then(r=>r.json());
    if(res.success) btn.innerText = (res.status==='liked'?'❤️':'🤍')+' '+res.likes;
  }
});

// ===== Render comments =====
function renderComments(container, comments, postId){
  const commentsHtml = comments.map(c=>{
    const pic = c.profile_image
      ? `<img src="../${escapeHtml(c.profile_image)}" class="rounded-circle me-2" style="width:28px;height:28px;object-fit:cover;">`
      : `<div class="avatar me-2" style="width:28px;height:28px;font-size:0.9rem;">${escapeHtml(c.user_name.charAt(0).toUpperCase())}</div>`;
    return `<div class="d-flex align-items-start mb-2">
      ${pic}
      <div class="comment-box flex-grow-1">
        <span class="fw-semibold">${escapeHtml(c.user_name)}</span><br>
        <span>${escapeHtml(c.text)}</span>
        <div class="mt-1 d-flex gap-2">
          <button class="btn btn-sm btn-outline-primary like-comment" data-comment-id="${c.id}" data-post-id="${postId}">${c.user_liked==1?'❤️':'🤍'} ${c.like_count||0}</button>
          <button class="btn btn-sm btn-outline-secondary reply-toggle" data-comment-id="${c.id}" data-post-id="${postId}">Reply</button>
        </div>
        <div class="reply-box mt-1" id="reply-box-${c.id}" style="display:none;">
          <input type="text" class="form-control form-control-sm mb-1" placeholder="Write a reply..." id="reply-input-${c.id}">
          <button class="btn btn-sm btn-gradient reply-comment" data-comment-id="${c.id}" data-post-id="${postId}">Post</button>
        </div>
      </div>
    </div>`;
  }).join('');
  container.innerHTML = commentsHtml + `
    <div class="input-group input-group-sm mt-2">
      <input type="text" class="form-control comment-input" placeholder="Add a comment...">
      <button class="btn btn-gradient add-comment">Post</button>
    </div>`;
}

// ===== Load people (sidebar) =====
async function loadPeople(){
  const res = await getJSON(API_BASE + 'fetch_users.php');
  const box = document.getElementById('peopleBox');
  if(!res.success){ box.innerHTML='<div class="text-muted small">Failed</div>'; return; }
  box.innerHTML = res.users.map(u=>{
    const pic = u.profile_image
      ? `<img src="../${escapeHtml(u.profile_image)}" class="rounded-circle me-2" style="width:30px;height:30px;object-fit:cover;">`
      : `<div class="avatar me-2" style="width:30px;height:30px;font-size:0.9rem;">${escapeHtml(u.name.charAt(0).toUpperCase())}</div>`;
    return `<div class="d-flex align-items-center trend-item text-dark">
              ${pic}<span>${escapeHtml(u.name)}</span>
            </div>`;
  }).join('');
}

// ===== Initial load =====
loadPeople();
loadFeed();
</script>

</body>
</html>