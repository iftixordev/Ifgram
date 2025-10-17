<!DOCTYPE html>
<html>
<head>
    <title>Ifgram</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #fafafa; }
        .header { background: white; border-bottom: 1px solid #dbdbdb; padding: 16px 0; position: fixed; top: 0; width: 100%; z-index: 100; }
        .header-content { max-width: 975px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 0 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #262626; }
        .nav-icons { display: flex; gap: 20px; }
        .nav-icons i { font-size: 24px; color: #262626; cursor: pointer; }
        .container { max-width: 614px; margin: 80px auto 0; padding: 0 20px; }
        .auth-modal { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 200; }
        .auth-form { background: white; padding: 40px; border-radius: 8px; width: 350px; text-align: center; }
        .auth-form h2 { margin-bottom: 30px; color: #262626; }
        .form-group { margin-bottom: 15px; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #dbdbdb; border-radius: 4px; background: #fafafa; }
        .btn { width: 100%; padding: 12px; background: #0095f6; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .btn:hover { background: #1877f2; }
        .btn-secondary { background: transparent; color: #0095f6; border: 1px solid #0095f6; }
        .post-card { background: white; border: 1px solid #dbdbdb; border-radius: 8px; margin-bottom: 24px; }
        .post-header { padding: 16px; display: flex; align-items: center; gap: 12px; }
        .avatar { width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; }
        .post-username { font-weight: 600; color: #262626; }
        .post-image { width: 100%; max-height: 600px; object-fit: cover; }
        .post-actions { padding: 16px; }
        .action-buttons { display: flex; gap: 16px; margin-bottom: 12px; }
        .action-buttons i { font-size: 24px; cursor: pointer; color: #262626; }
        .action-buttons .liked { color: #ed4956; }
        .likes-count { font-weight: 600; color: #262626; margin-bottom: 8px; }
        .post-caption { color: #262626; line-height: 1.4; }
        .post-time { color: #8e8e8e; font-size: 12px; margin-top: 8px; }
        .create-post { background: white; border: 1px solid #dbdbdb; border-radius: 8px; padding: 20px; margin-bottom: 24px; }
        .create-post textarea { width: 100%; border: none; resize: none; font-size: 16px; padding: 12px; }
        .create-post input { width: 100%; padding: 12px; border: 1px solid #dbdbdb; border-radius: 4px; margin-bottom: 12px; }
        .stories { display: flex; gap: 16px; padding: 16px 0; overflow-x: auto; }
        .story { text-align: center; min-width: 66px; }
        .story-avatar { width: 66px; height: 66px; border-radius: 50%; background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); padding: 2px; margin-bottom: 8px; }
        .story-inner { width: 100%; height: 100%; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; color: #262626; font-weight: bold; }
        .story-username { font-size: 12px; color: #262626; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">Ifgram</div>
            <div class="nav-icons">
                <i class="fas fa-home" onclick="loadFeed()"></i>
                <i class="fas fa-plus-square" onclick="toggleCreatePost()"></i>
                <i class="fas fa-user" onclick="showProfile()"></i>
                <i class="fas fa-sign-out-alt" onclick="logout()" id="logout-btn" class="hidden"></i>
            </div>
        </div>
    </div>

    <div id="auth-modal" class="auth-modal">
        <div class="auth-form">
            <h2 id="auth-title">Ifgram'ga kirish</h2>
            <div id="login-form">
                <div class="form-group">
                    <input type="email" id="email" placeholder="Email">
                </div>
                <div class="form-group">
                    <input type="password" id="password" placeholder="Parol">
                </div>
                <button class="btn" onclick="login()">Kirish</button>
                <button class="btn btn-secondary" onclick="showRegister()">Ro'yxatdan o'tish</button>
            </div>
            <div id="register-form" class="hidden">
                <div class="form-group">
                    <input type="text" id="reg_username" placeholder="Foydalanuvchi nomi">
                </div>
                <div class="form-group">
                    <input type="email" id="reg_email" placeholder="Email">
                </div>
                <div class="form-group">
                    <input type="password" id="reg_password" placeholder="Parol">
                </div>
                <button class="btn" onclick="register()">Ro'yxatdan o'tish</button>
                <button class="btn btn-secondary" onclick="showLogin()">Kirish</button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="stories" id="stories"></div>
        
        <div class="create-post hidden" id="create-post">
            <input type="text" id="media_url" placeholder="Rasm URL">
            <textarea id="caption" placeholder="Izoh yozing..." rows="3"></textarea>
            <button class="btn" onclick="createPost()">Joylash</button>
        </div>
        
        <div id="feed"></div>
    </div>

    <script>
        let currentUser = null;
        
        function showLogin() {
            document.getElementById('auth-title').textContent = "Ifgram'ga kirish";
            document.getElementById('login-form').classList.remove('hidden');
            document.getElementById('register-form').classList.add('hidden');
        }
        
        function showRegister() {
            document.getElementById('auth-title').textContent = "Ro'yxatdan o'tish";
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('register-form').classList.remove('hidden');
        }
        
        async function login() {
            const response = await fetch('api/auth.php?action=login', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value
                })
            });
            const result = await response.json();
            if (result.success) {
                currentUser = result.user;
                document.getElementById('auth-modal').style.display = 'none';
                document.getElementById('logout-btn').classList.remove('hidden');
                loadFeed();
                loadStories();
            }
        }
        
        async function register() {
            const response = await fetch('api/auth.php?action=register', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    username: document.getElementById('reg_username').value,
                    email: document.getElementById('reg_email').value,
                    password: document.getElementById('reg_password').value
                })
            });
            const result = await response.json();
            if (result.success) showLogin();
        }
        
        function toggleCreatePost() {
            const form = document.getElementById('create-post');
            form.classList.toggle('hidden');
        }
        
        async function createPost() {
            const response = await fetch('api/posts.php?action=create', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    user_id: currentUser.id,
                    media_url: document.getElementById('media_url').value,
                    caption: document.getElementById('caption').value
                })
            });
            const result = await response.json();
            if (result.success) {
                document.getElementById('media_url').value = '';
                document.getElementById('caption').value = '';
                document.getElementById('create-post').classList.add('hidden');
                loadFeed();
            }
        }
        
        async function loadFeed() {
            const response = await fetch(`api/posts.php?action=feed&user_id=${currentUser.id}`);
            const posts = await response.json();
            const feed = document.getElementById('feed');
            feed.innerHTML = posts.map(post => `
                <div class="post-card">
                    <div class="post-header">
                        <div class="avatar">${post.username[0].toUpperCase()}</div>
                        <div class="post-username">${post.username}</div>
                    </div>
                    <img src="${post.media_url}" class="post-image" alt="Post">
                    <div class="post-actions">
                        <div class="action-buttons">
                            <i class="fas fa-heart" onclick="likePost(${post.id})"></i>
                            <i class="fas fa-comment"></i>
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div class="likes-count">${post.likes_count} yoqtirishlar</div>
                        <div class="post-caption"><strong>${post.username}</strong> ${post.caption}</div>
                        <div class="post-time">${timeAgo(post.created_at)}</div>
                    </div>
                </div>
            `).join('');
        }
        
        async function loadStories() {
            const stories = document.getElementById('stories');
            stories.innerHTML = `
                <div class="story">
                    <div class="story-avatar">
                        <div class="story-inner">${currentUser.username[0].toUpperCase()}</div>
                    </div>
                    <div class="story-username">Sizning hikoyangiz</div>
                </div>
            `;
        }
        
        async function likePost(postId) {
            await fetch('api/posts.php?action=like', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    user_id: currentUser.id,
                    post_id: postId
                })
            });
            loadFeed();
        }
        
        function timeAgo(dateString) {
            const now = new Date();
            const postDate = new Date(dateString);
            const diffMs = now - postDate;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMins / 60);
            const diffDays = Math.floor(diffHours / 24);
            
            if (diffMins < 60) return `${diffMins} daqiqa oldin`;
            if (diffHours < 24) return `${diffHours} soat oldin`;
            return `${diffDays} kun oldin`;
        }
        
        function logout() {
            currentUser = null;
            document.getElementById('auth-modal').style.display = 'flex';
            document.getElementById('logout-btn').classList.add('hidden');
            document.getElementById('feed').innerHTML = '';
            document.getElementById('stories').innerHTML = '';
        }
        
        function showProfile() {
            alert(`Profil: ${currentUser.username}`);
        }
    </script>
</body>
</html>