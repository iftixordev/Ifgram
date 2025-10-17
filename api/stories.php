<?php
require_once '../config/database.php';

class Stories {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function create($user_id, $media_url, $media_type = 'image') {
        $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $stmt = $this->db->prepare("INSERT INTO stories (user_id, media_url, media_type, expires_at) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$user_id, $media_url, $media_type, $expires_at]);
    }
    
    public function getActiveStories($user_id) {
        $stmt = $this->db->prepare("
            SELECT s.*, u.username, u.avatar_url
            FROM stories s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN follows f ON f.following_id = s.user_id
            WHERE (f.follower_id = ? OR s.user_id = ?) AND s.expires_at > datetime('now')
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$user_id, $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserStories($user_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM stories 
            WHERE user_id = ? AND expires_at > datetime('now')
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function viewStory($story_id) {
        $stmt = $this->db->prepare("UPDATE stories SET views_count = views_count + 1 WHERE id = ?");
        return $stmt->execute([$story_id]);
    }
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stories = new Stories();
    
    if ($_GET['action'] === 'create') {
        $result = $stories->create($data['user_id'], $data['media_url'], $data['media_type'] ?? 'image');
        echo json_encode(['success' => $result]);
    } elseif ($_GET['action'] === 'view') {
        $result = $stories->viewStory($data['story_id']);
        echo json_encode(['success' => $result]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stories = new Stories();
    
    if ($_GET['action'] === 'feed') {
        $activeStories = $stories->getActiveStories($_GET['user_id']);
        echo json_encode($activeStories);
    } elseif ($_GET['action'] === 'user') {
        $userStories = $stories->getUserStories($_GET['user_id']);
        echo json_encode($userStories);
    }
}