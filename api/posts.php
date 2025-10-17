<?php
require_once '../config/database.php';

class Posts {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function create($user_id, $media_url, $caption) {
        $stmt = $this->db->prepare("INSERT INTO posts (user_id, media_url, caption) VALUES (?, ?, ?)");
        return $stmt->execute([$user_id, $media_url, $caption]);
    }
    
    public function getFeed($user_id) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username, u.avatar_url,
                   COUNT(l.post_id) as likes_count
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN follows f ON f.following_id = p.user_id
            LEFT JOIN likes l ON l.post_id = p.id
            WHERE f.follower_id = ? OR p.user_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$user_id, $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function like($user_id, $post_id) {
        $stmt = $this->db->prepare("INSERT OR IGNORE INTO likes (user_id, post_id) VALUES (?, ?)");
        return $stmt->execute([$user_id, $post_id]);
    }
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $posts = new Posts();
    
    if ($_GET['action'] === 'create') {
        $result = $posts->create($data['user_id'], $data['media_url'], $data['caption']);
        echo json_encode(['success' => $result]);
    } elseif ($_GET['action'] === 'like') {
        $result = $posts->like($data['user_id'], $data['post_id']);
        echo json_encode(['success' => $result]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'feed') {
    $posts = new Posts();
    $feed = $posts->getFeed($_GET['user_id']);
    echo json_encode($feed);
}