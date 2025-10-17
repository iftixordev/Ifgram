<?php
require_once '../config/database.php';
require_once '../config/utils.php';

class Posts {
    private $db;
    private $database;
    
    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->getConnection();
    }
    
    public function create($user_id, $media_url, $caption) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO posts (user_id, media_url, caption) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $media_url, $caption]);
            $post_id = $this->db->lastInsertId();
            
            // Extract and save hashtags
            $hashtags = Utils::extractHashtags($caption);
            foreach ($hashtags as $tag) {
                $this->saveHashtag($post_id, $tag);
            }
            
            // Update user posts count
            $this->database->updateCounts('users', $user_id, 'posts_count');
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    private function saveHashtag($post_id, $tag) {
        $stmt = $this->db->prepare("INSERT OR IGNORE INTO hashtags (name) VALUES (?)");
        $stmt->execute([$tag]);
        
        $stmt = $this->db->prepare("SELECT id FROM hashtags WHERE name = ?");
        $stmt->execute([$tag]);
        $hashtag_id = $stmt->fetchColumn();
        
        $stmt = $this->db->prepare("INSERT OR IGNORE INTO post_hashtags (post_id, hashtag_id) VALUES (?, ?)");
        $stmt->execute([$post_id, $hashtag_id]);
        
        $this->database->updateCounts('hashtags', $hashtag_id, 'posts_count');
    }
    
    public function getFeed($user_id, $limit = 20, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username, u.avatar_url, u.is_verified,
                   (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND comment_id IS NULL) as likes_count,
                   (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comments_count,
                   (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = ? AND comment_id IS NULL) as user_liked
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN follows f ON f.following_id = p.user_id
            WHERE f.follower_id = ? OR p.user_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $user_id, $user_id, $limit, $offset]);
        $posts = $stmt->fetchAll();
        
        foreach ($posts as &$post) {
            $post['caption'] = Utils::formatText($post['caption']);
            $post['time_ago'] = Utils::timeAgo($post['created_at']);
        }
        
        return $posts;
    }
    
    public function like($user_id, $post_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM likes WHERE user_id = ? AND post_id = ? AND comment_id IS NULL");
        $stmt->execute([$user_id, $post_id]);
        $exists = $stmt->fetchColumn();
        
        if ($exists) {
            $stmt = $this->db->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ? AND comment_id IS NULL");
            $result = $stmt->execute([$user_id, $post_id]);
            $this->database->updateCounts('posts', $post_id, 'likes_count', -1);
        } else {
            $stmt = $this->db->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
            $result = $stmt->execute([$user_id, $post_id]);
            $this->database->updateCounts('posts', $post_id, 'likes_count');
        }
        
        return $result;
    }
    
    public function addComment($post_id, $user_id, $text, $parent_id = null) {
        $stmt = $this->db->prepare("INSERT INTO comments (post_id, user_id, text, parent_id) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$post_id, $user_id, $text, $parent_id]);
        
        if ($result) {
            $this->database->updateCounts('posts', $post_id, 'comments_count');
        }
        
        return $result;
    }
    
    public function getComments($post_id) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username, u.avatar_url
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$post_id]);
        return $stmt->fetchAll();
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
    } elseif ($_GET['action'] === 'comment') {
        $result = $posts->addComment($data['post_id'], $data['user_id'], $data['text'], $data['parent_id'] ?? null);
        echo json_encode(['success' => $result]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $posts = new Posts();
    
    if ($_GET['action'] === 'feed') {
        $limit = $_GET['limit'] ?? 20;
        $offset = $_GET['offset'] ?? 0;
        $feed = $posts->getFeed($_GET['user_id'], $limit, $offset);
        echo json_encode($feed);
    } elseif ($_GET['action'] === 'comments') {
        $comments = $posts->getComments($_GET['post_id']);
        echo json_encode($comments);
    }
}