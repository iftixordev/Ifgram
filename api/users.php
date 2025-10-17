<?php
require_once '../config/database.php';

class Users {
    private $db;
    private $database;
    
    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
    }
    
    public function getProfile($user_id) {
        $stmt = $this->db->prepare("
            SELECT id, username, bio, avatar_url, is_verified, 
                   followers_count, following_count, posts_count, created_at
            FROM users WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetch();
    }
    
    public function updateProfile($user_id, $bio, $avatar_url = null) {
        $sql = "UPDATE users SET bio = ?";
        $params = [$bio];
        
        if ($avatar_url) {
            $sql .= ", avatar_url = ?";
            $params[] = $avatar_url;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $user_id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function follow($follower_id, $following_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = ?");
        $stmt->execute([$follower_id, $following_id]);
        $exists = $stmt->fetchColumn();
        
        if ($exists) {
            $stmt = $this->db->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
            $result = $stmt->execute([$follower_id, $following_id]);
            $this->database->updateCounts('users', $following_id, 'followers_count', -1);
            $this->database->updateCounts('users', $follower_id, 'following_count', -1);
        } else {
            $stmt = $this->db->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
            $result = $stmt->execute([$follower_id, $following_id]);
            $this->database->updateCounts('users', $following_id, 'followers_count');
            $this->database->updateCounts('users', $follower_id, 'following_count');
        }
        
        return $result;
    }
    
    public function search($query, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT id, username, bio, avatar_url, is_verified, followers_count
            FROM users 
            WHERE username LIKE ? OR bio LIKE ?
            ORDER BY followers_count DESC
            LIMIT ?
        ");
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getUserPosts($user_id, $limit = 12, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT id, media_url, likes_count, comments_count, created_at
            FROM posts 
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $limit, $offset]);
        return $stmt->fetchAll();
    }
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $users = new Users();
    
    if ($_GET['action'] === 'profile') {
        $profile = $users->getProfile($_GET['user_id']);
        echo json_encode($profile);
    } elseif ($_GET['action'] === 'search') {
        $results = $users->search($_GET['q'], $_GET['limit'] ?? 10);
        echo json_encode($results);
    } elseif ($_GET['action'] === 'posts') {
        $posts = $users->getUserPosts($_GET['user_id'], $_GET['limit'] ?? 12, $_GET['offset'] ?? 0);
        echo json_encode($posts);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $users = new Users();
    
    if ($_GET['action'] === 'follow') {
        $result = $users->follow($data['follower_id'], $data['following_id']);
        echo json_encode(['success' => $result]);
    } elseif ($_GET['action'] === 'update') {
        $result = $users->updateProfile($data['user_id'], $data['bio'], $data['avatar_url'] ?? null);
        echo json_encode(['success' => $result]);
    }
}