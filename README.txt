# LinkedIn Clone Project

A full-stack social media web application inspired by LinkedIn. Users can create posts, like/unlike posts, comment and reply to comments, like/unlike comments, and view trending topics and suggested people.

---

##  Tech Stack

- **Frontend:** HTML, CSS (Bootstrap), JavaScript  
- **Backend:** PHP, MySQL  
- **Deployment:** Render/Railway (backend + PHP)  

---

##  Features

1. **User Authentication**
   - Signup and login functionality
   - Profile picture upload

2. **Post Feed**
   - Create posts with text and optional image
   - Edit and delete your own posts
   - Like/unlike posts
   - Display like count dynamically

3. **Comments**
   - Add comments to posts
   - Reply to comments (nested comments)
   - Like/unlike comments
   - Comment count updates dynamically
   - Comments only visible when "Comments" button is toggled

4. **Sidebars**
   - Trending topics
   - People you may know

5. **Responsive UI**
   - LinkedIn-style layout
   - Bordered profile avatars
   - Interactive buttons with hover effects

---

##  Project Structure
project-root/
│
├─ public/
│ ├─ assets/ (CSS, JS, images)
│ ├─ feed.php
│ ├─ index.php
│ ├─ profile.php
│ ├─ logout.php
│
├─ api/
│ ├─ signup.php
│ ├─ create_post.php
│ ├─ fetch_posts.php
│ ├─ add_comment.php
│ ├─ fetch_comments.ph
│ ├─ toggle_like.php
│ ├─ toggle_comment_like.php
│ ├─ delete_post.php
│ ├─ update_post.php
│ └─ fetch_users.php
│
└─ README.md



---

##  How to Run Locally

1. Clone the repository:

```bash
git clone https://github.com/<yourusername>/<yourrepo>.git

2. Navigate to the project folder:

cd <yourrepo>


3. Start a local PHP server (or use XAMPP/WAMP):

php -S localhost:8000 -t public


4. Create a MySQL database and import any provided SQL tables (or create manually).

5. Update the database connection in your PHP files (e.g., db.php) if needed.

6. Open in your browser: http://localhost:8000

Live Project

Frontend & Backend URL:

GitHub Repository

GitHub Repo Link
