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
/* ========== Configuration ========== */
const CURRENT_USER_ID = <?= json_encode($userId) ?>;

/* ========== Helpers ========== */
function escapeHtml(text){
  if (text === null || text === undefined) return '';
  return String(text)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;');
}
function formatDate(s){
  try { return new Date(s).toLocaleString(); } catch(e) { return s; }
}
async function getJSON(url){
  const r = await fetch(url, { cache: 'no-store' });
  return r.json();
}

/* ========== Renderers ========== */
function renderPostCard(p){
  const liked = p.user_liked == 1;
  const heart = liked ? '❤️' : '🤍';
  const avatarHtml = p.profile_image
    ? `<img src="../${escapeHtml(p.profile_image)}" class="rounded-circle me-2 rounded-white-border" style="width:48px;height:48px;object-fit:cover;">`
    : `<div class="avatar me-2" style="width:48px;height:48px;font-size:1.1rem;">${escapeHtml(p.user_name.charAt(0).toUpperCase())}</div>`;
  const imageHtml = p.image_path ? `<div class="mt-3"><img src="../${escapeHtml(p.image_path)}" class="post-image"></div>` : '';
  const controls = (p.user_id == CURRENT_USER_ID)
    ? `<div class="d-flex gap-2">
         <button class="btn btn-sm btn-outline-secondary edit-post" data-id="${p.id}">Edit</button>
         <button class="btn btn-sm btn-outline-danger delete-post" data-id="${p.id}">Delete</button>
       </div>`
    : '';

  // Note: comment_count is displayed but authoritative count comes from fetch_comments response
  return `
    <div class="post-card" data-id="${p.id}">
      <div class="d-flex justify-content-between align-items-start">
        <div class="d-flex align-items-center">
          ${avatarHtml}
          <div>
            <div class="user-name">${escapeHtml(p.user_name)}</div>
            <small class="small-muted">${formatDate(p.created_at)}</small>
          </div>
        </div>
        ${controls}
      </div>

      <div class="post-content mt-3">${escapeHtml(p.content).replace(/\n/g,'<br>')}</div>
      ${imageHtml}

      <div class="mt-3 d-flex gap-3 align-items-center">
        <button class="btn btn-sm btn-outline-primary like-post" data-post="${p.id}" data-liked="${p.user_liked?1:0}">${heart} <span class="like-count">${p.like_count||0}</span></button>
        <button class="btn btn-sm btn-outline-secondary toggle-comments" data-post="${p.id}">💬 Comments (<span class="comment-count">${p.comment_count||0}</span>)</button>
      </div>

      <div class="comments mt-3" style="display:none;"></div>
    </div>`;
}

/* Render comments recursively */
function renderCommentsHtml(comments){
  let html = '';
  function render(list, depth=0){
    list.forEach(c=>{
      const pad = depth * 18;
      const liked = c.user_liked == 1;
      const heart = liked ? '❤️' : '🤍';
      const pic = c.profile_image ? `<img src="../${escapeHtml(c.profile_image)}" class="rounded-circle me-2" style="width:34px;height:34px;object-fit:cover;border:2px solid #fff;">`
                                 : `<div class="avatar me-2" style="width:34px;height:34px;font-size:0.85rem;">${escapeHtml(c.user_name.charAt(0).toUpperCase())}</div>`;
      html += `
        <div class="d-flex comment-wrapper" style="margin-left:${pad}px;margin-bottom:8px;" data-comment-id="${c.id}">
          ${pic}
          <div style="flex:1;">
            <div class="comment-box">
              <div class="fw-semibold">${escapeHtml(c.user_name)}</div>
              <div class="small-muted">${formatDate(c.created_at)}</div>
              <div class="mt-1">${escapeHtml(c.text)}</div>
              <div class="mt-2 small text-muted comment-controls">
                <button class="btn btn-sm btn-outline-primary like-comment" data-id="${c.id}">${heart} <span class="c-like-count">${c.like_count||0}</span></button>
                <button class="btn btn-sm btn-outline-secondary reply-btn" data-id="${c.id}">Reply</button>
              </div>
            </div>
            <div class="replies mt-2"></div>
          </div>
        </div>
      `;
      if (c.replies && c.replies.length) render(c.replies, depth + 1);
    });
  }
  render(comments);
  return html;
}

/* Input block appended to bottom of comments */
function commentInputHtml(){
  return `
    <div class="mt-2">
      <div class="input-group input-group-sm">
        <input type="text" class="form-control comment-input" placeholder="Add a comment...">
        <button class="btn btn-gradient add-comment-btn">Post</button>
      </div>
    </div>`;
}

/* Count comments recursively (fallback if API doesn't return count) */
function countCommentsRecursive(comments){
  let n = 0;
  function rec(list){
    list.forEach(c=>{ n++; if(c.replies && c.replies.length) rec(c.replies); });
  }
  if(Array.isArray(comments)) rec(comments);
  return n;
}

/* Update small aggregated stats on left from DOM (fast) */
function updateSidebarStatsFromDOM(){
  // postCount = number of .post-card nodes
  const posts = document.querySelectorAll('.post-card');
  document.getElementById('postCount').innerText = posts.length;
  // likeCount = sum of .like-count in posts
  let sumLikes = 0;
  posts.forEach(p=>{
    const span = p.querySelector('.like-count');
    if(span) sumLikes += parseInt(span.textContent || '0');
  });
  document.getElementById('likeCount').innerText = sumLikes;
}

/* ========== Main load functions ========== */
async function loadFeed(){
  try{
    const res = await getJSON('../api/fetch_posts.php');
    const feed = document.getElementById('feed');
    if(!res || !res.success){ feed.innerHTML = '<div class="alert alert-danger">Failed to load feed</div>'; return; }
    feed.innerHTML = res.posts.map(renderPostCard).join('');
    updateSidebarStatsFromDOM();
  } catch(err){
    console.error(err);
    document.getElementById('feed').innerHTML = '<div class="alert alert-danger">Network error</div>';
  }
}

async function loadPeople(){
  try{
    const res = await getJSON('../api/fetch_users.php');
    const box = document.getElementById('peopleBox');
    if(!res || !res.success){ box.innerHTML = '<div class="small-muted">Failed to load</div>'; return; }
    box.innerHTML = res.users.map(u=>{
      const pic = u.profile_image ? `<img src="../${escapeHtml(u.profile_image)}" class="rounded-circle me-2" style="width:34px;height:34px;object-fit:cover;">`
                                : `<div class="avatar me-2" style="width:34px;height:34px;font-size:0.9rem;">${escapeHtml(u.name.charAt(0).toUpperCase())}</div>`;
      return `<div class="d-flex align-items-center trend-item text-dark">${pic}<span>${escapeHtml(u.name)}</span></div>`;
    }).join('');
  } catch(err){ console.error(err); }
}

/* ========== Post creation (multipart) ========== */
document.getElementById('postForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const submitBtn = document.getElementById('postSubmit');
  const fd = new FormData(this);
  submitBtn.disabled = true; submitBtn.textContent = 'Posting...';
  try{
    // create_post.php should accept form-data { content, image } and return { success:true, post_id:... } on success
    const res = await fetch('../api/create_post.php', { method: 'POST', body: fd }).then(r=>r.json());
    if(res.success){
      this.reset();
      await loadFeed();
    } else {
      alert(res.error || 'Failed to create post');
    }
  } catch(err){
    console.error(err);
    alert('Network error while posting');
  }
  submitBtn.disabled = false; submitBtn.textContent = 'Post';
});

/* ========== Delegated interactions ========== */
document.addEventListener('click', async function(e){
  // ---------------- Post like/unlike ----------------
  const likePostBtn = e.target.closest('.like-post');
  if(likePostBtn){
    const postId = likePostBtn.dataset.post;
    const currentlyLiked = likePostBtn.dataset.liked === '1';
    const countSpan = likePostBtn.querySelector('.like-count');
    let count = parseInt(countSpan.textContent || '0');

    // Optimistic UI:
    if(currentlyLiked){
      count = Math.max(0, count - 1);
      likePostBtn.dataset.liked = '0';
      likePostBtn.innerHTML = `🤍 <span class="like-count">${count}</span>`;
    } else {
      count = count + 1;
      likePostBtn.dataset.liked = '1';
      likePostBtn.innerHTML = `❤️ <span class="like-count">${count}</span>`;
    }
    updateSidebarStatsFromDOM();

    // Backend toggle (expects toggle_like.php to toggle and return { success:true, status:'liked'|'unliked', likes:<count> })
    try{
      const res = await fetch('../api/toggle_like.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ post_id: postId }) }).then(r=>r.json());
      if(!res.success){
        // revert by reloading feed if backend failed
        await loadFeed();
        alert(res.error || 'Failed to toggle like');
      } else {
        // ensure count matches authoritative server
        if(typeof res.likes !== 'undefined'){
          const postCard = document.querySelector(`.post-card[data-id="${postId}"]`);
          if(postCard){
            const lc = postCard.querySelector('.like-count');
            if(lc) lc.textContent = res.likes;
          }
        }
        updateSidebarStatsFromDOM();
      }
    } catch(err){
      console.error(err);
      await loadFeed();
    }
    return;
  }

  // ---------------- Toggle comments (stay open until toggled) ----------------
  const toggleCommentsBtn = e.target.closest('.toggle-comments');
  if(toggleCommentsBtn){
    const postId = toggleCommentsBtn.dataset.post;
    const postCard = toggleCommentsBtn.closest('.post-card');
    const commentsDiv = postCard.querySelector('.comments');
    if(!commentsDiv) return;

    if(commentsDiv.style.display === 'none' || commentsDiv.style.display === ''){
      // open and load comment tree for this post
      commentsDiv.style.display = 'block';
      commentsDiv.innerHTML = '<div class="small-muted">Loading comments...</div>';
      try{
        const res = await getJSON(`../api/fetch_comments.php?post_id=${postId}`);
        if(!res || !res.success){ commentsDiv.innerHTML = '<div class="text-danger small">Failed to load comments</div>'; return; }
        // render comments and append comment input
        commentsDiv.innerHTML = renderCommentsHtml(res.comments || []) + commentInputHtml();
        attachCommentInputHandler(commentsDiv, postCard);
        // update comment-count badge using authoritative count if provided, else compute
        const count = (typeof res.count !== 'undefined') ? res.count : countCommentsRecursive(res.comments || []);
        const ccEl = postCard.querySelector('.comment-count');
        if(ccEl) ccEl.textContent = count;
      } catch(err){
        console.error(err);
        commentsDiv.innerHTML = '<div class="text-danger small">Error loading comments</div>';
      }
    } else {
      // close comments
      commentsDiv.style.display = 'none';
    }
    return;
  }

  // ---------------- Add top-level comment ----------------
  const addCommentBtn = e.target.closest('.add-comment-btn');
  if(addCommentBtn){
    const commentsDiv = addCommentBtn.closest('.comments');
    const postCard = addCommentBtn.closest('.post-card');
    const postId = postCard.dataset.id;
    const input = commentsDiv.querySelector('.comment-input');
    if(!input) return;
    const text = input.value.trim();
    if(!text) return;
    addCommentBtn.disabled = true;
    try{
      // add_comment.php should accept JSON { post_id, text } and return { success:true }
      const res = await fetch('../api/add_comment.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ post_id: postId, text }) }).then(r=>r.json());
      addCommentBtn.disabled = false;
      if(!res.success){ alert(res.error || 'Failed to add comment'); return; }

      // Re-fetch comments for this post to get authoritative nested comments & count
      const fresh = await getJSON(`../api/fetch_comments.php?post_id=${postId}`);
      if(fresh && fresh.success){
        commentsDiv.innerHTML = renderCommentsHtml(fresh.comments || []) + commentInputHtml();
        attachCommentInputHandler(commentsDiv, postCard);
        const ccEl = postCard.querySelector('.comment-count');
        const count = (typeof fresh.count !== 'undefined') ? fresh.count : countCommentsRecursive(fresh.comments || []);
        if(ccEl) ccEl.textContent = count;
        input.value = '';
      }
    } catch(err){
      console.error(err);
      addCommentBtn.disabled = false;
      alert('Network error while adding comment');
    }
    return;
  }

  // ---------------- Reply button: show inline reply input ----------------
  const replyBtn = e.target.closest('.reply-btn');
  if(replyBtn){
    const wrapper = replyBtn.closest('.comment-wrapper');
    if(!wrapper) return;
    if(wrapper.querySelector('.reply-input')) return; // avoid duplicates
    const parentId = replyBtn.dataset.id;
    const replyBox = document.createElement('div');
    replyBox.className = 'reply-input';
    replyBox.innerHTML = `<div class="input-group input-group-sm mt-2">
      <input type="text" class="form-control reply-text" placeholder="Write a reply...">
      <button class="btn btn-gradient send-reply-btn" data-parent="${parentId}">Reply</button>
    </div>`;
    wrapper.appendChild(replyBox);
    const input = replyBox.querySelector('.reply-text');
    input.focus();
    input.addEventListener('keypress', function(ev){ if(ev.key === 'Enter'){ ev.preventDefault(); replyBox.querySelector('.send-reply-btn').click(); } });
    return;
  }

  // ---------------- Send inline reply ----------------
  const sendReplyBtn = e.target.closest('.send-reply-btn');
  if(sendReplyBtn){
    const parentId = sendReplyBtn.dataset.parent;
    const replyBox = sendReplyBtn.closest('.reply-input');
    const textInput = replyBox.querySelector('.reply-text');
    const text = textInput.value.trim();
    if(!text) return;
    sendReplyBtn.disabled = true;
    try{
      // reuse add_comment.php with parent_id; endpoint should accept { parent_id, text } and return { success:true }
      const res = await fetch('../api/add_comment.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ parent_id: parentId, text }) }).then(r=>r.json());
      sendReplyBtn.disabled = false;
      if(!res.success){ alert(res.error || 'Failed to post reply'); return; }
      // refresh comments for this post
      const postCard = sendReplyBtn.closest('.post-card');
      const postId = postCard.dataset.id;
      const commentsDiv = postCard.querySelector('.comments');
      const fresh = await getJSON(`../api/fetch_comments.php?post_id=${postId}`);
      if(fresh && fresh.success){
        commentsDiv.innerHTML = renderCommentsHtml(fresh.comments || []) + commentInputHtml();
        attachCommentInputHandler(commentsDiv, postCard);
        const ccEl = postCard.querySelector('.comment-count');
        const count = (typeof fresh.count !== 'undefined') ? fresh.count : countCommentsRecursive(fresh.comments || []);
        if(ccEl) ccEl.textContent = count;
      }
    } catch(err){
      console.error(err);
      sendReplyBtn.disabled = false;
      alert('Network error while posting reply');
    }
    return;
  }

  // ---------------- Like/unlike comment ----------------
  const likeCommentBtn = e.target.closest('.like-comment');
  if(likeCommentBtn){
    const commentId = likeCommentBtn.dataset.id;
    const countSpan = likeCommentBtn.querySelector('.c-like-count');
    let cnt = parseInt(countSpan.textContent || '0');
    const isLiked = likeCommentBtn.classList.contains('liked');
    if(isLiked){ cnt = Math.max(0, cnt - 1); likeCommentBtn.classList.remove('liked'); likeCommentBtn.innerHTML = `🤍 <span class="c-like-count">${cnt}</span>`; }
    else { cnt = cnt + 1; likeCommentBtn.classList.add('liked'); likeCommentBtn.innerHTML = `❤️ <span class="c-like-count">${cnt}</span>`; }
    try{
      const res = await fetch('../api/toggle_comment_like.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ comment_id: commentId }) }).then(r=>r.json());
      if(!res.success){ await loadFeed(); alert(res.error || 'Failed to toggle comment like'); }
    } catch(err){
      console.error(err);
      await loadFeed();
    }
    return;
  }

  // ---------------- Delete post ----------------
  const deleteBtn = e.target.closest('.delete-post');
  if(deleteBtn){
    if(!confirm('Delete this post?')) return;
    const postId = deleteBtn.dataset.id;
    try{
      const res = await fetch('../api/delete_post.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ post_id: postId }) }).then(r=>r.json());
      if(res.success) await loadFeed(); else alert(res.error || 'Failed to delete');
    } catch(err){
      console.error(err); alert('Network error while deleting post');
    }
    return;
  }

  // ---------------- Edit post (inline) ----------------
  const editBtn = e.target.closest('.edit-post');
  if(editBtn){
    const postCard = editBtn.closest('.post-card');
    const postId = editBtn.dataset.id;
    const contentDiv = postCard.querySelector('.post-content');
    const currentText = contentDiv.innerText.trim();
    const ta = document.createElement('textarea');
    ta.className = 'form-control mb-2';
    ta.value = currentText;
    const saveBtn = document.createElement('button');
    saveBtn.className = 'btn btn-sm btn-gradient me-2';
    saveBtn.textContent = 'Save';
    const cancelBtn = document.createElement('button');
    cancelBtn.className = 'btn btn-sm btn-outline-secondary';
    cancelBtn.textContent = 'Cancel';
    const ctrl = document.createElement('div'); ctrl.className='mt-2'; ctrl.appendChild(saveBtn); ctrl.appendChild(cancelBtn);
    contentDiv.innerHTML = ''; contentDiv.appendChild(ta); contentDiv.appendChild(ctrl);
    cancelBtn.addEventListener('click', ()=> loadFeed());
    saveBtn.addEventListener('click', async ()=>{
      const newText = ta.value.trim();
      if(!newText) return alert('Content cannot be empty');
      saveBtn.disabled = true;
      try{
        const res = await fetch('../api/update_post.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ post_id: postId, content: newText }) }).then(r=>r.json());
        saveBtn.disabled = false;
        if(res.success) await loadFeed(); else alert(res.error || 'Failed to update');
      } catch(err){
        console.error(err); saveBtn.disabled=false; alert('Network error while updating');
      }
    });
    return;
  }

}); // end delegated handler

/* Attach Enter handler to comment input in provided commentsDiv */
function attachCommentInputHandler(commentsDiv, postCard){
  const input = commentsDiv.querySelector('.comment-input');
  const btn = commentsDiv.querySelector('.add-comment-btn');
  if(!input || !btn) return;
  // remove existing handlers by cloning (avoid duplicate bindings)
  const newInput = input.cloneNode(true);
  input.parentNode.replaceChild(newInput, input);
  newInput.addEventListener('keypress', function(e){
    if(e.key === 'Enter'){ e.preventDefault(); btn.click(); }
  });
}

/* ===== initial load ===== */
loadPeople();
loadFeed();
</script>
</body>
</html>
