<!DOCTYPE html>
<html>
<head>
    <title>Ifgram</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; }
        .post { border: 1px solid #ddd; margin: 20px 0; padding: 15px; }
        .form { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Ifgram</h1>
        
        <div class="form">
            <h3>Login</h3>
            <input type="email" id="email" placeholder="Email">
            <input type="password" id="password" placeholder="Password">
            <button onclick="login()">Login</button>
        </div>
        
        <div class="form">
            <h3>Register</h3>
            <input type="text" id="reg_username" placeholder="Username">
            <input type="email" id="reg_email" placeholder="Email">
            <input type="password" id="reg_password" placeholder="Password">
            <button onclick="register()">Register</button>
        </div>
        
        <div class="form" id="post-form" style="display:none;">
            <h3>Create Post</h3>
            <input type="text" id="media_url" placeholder="Image URL">
            <textarea id="caption" placeholder="Caption"></textarea>
            <button onclick="createPost()">Post</button>
        </div>
        
        <div id="feed"></div>
    </div>

    <script>
        let currentUser = null;
        
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
                document.getElementById('post-form').style.display = 'block';
                loadFeed();
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
            alert(result.success ? 'Registered!' : 'Error');
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
                loadFeed();
            }
        }
        
        async function loadFeed() {
            const response = await fetch(`api/posts.php?action=feed&user_id=${currentUser.id}`);
            const posts = await response.json();
            const feed = document.getElementById('feed');
            feed.innerHTML = posts.map(post => `
                <div class="post">
                    <h4>@${post.username}</h4>
                    <img src="${post.media_url}" style="max-width:100%">
                    <p>${post.caption}</p>
                    <small>${post.likes_count} likes</small>
                    <button onclick="likePost(${post.id})">Like</button>
                </div>
            `).join('');
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
    </script>
</body>
</html>