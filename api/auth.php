<?php
require_once '../config/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = (new Database())->getConnection();
    }
    
    public function register($username, $email, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $email, $hash]);
    }
    
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        return false;
    }
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $auth = new Auth();
    
    if ($_GET['action'] === 'register') {
        $result = $auth->register($data['username'], $data['email'], $data['password']);
        echo json_encode(['success' => $result]);
    } elseif ($_GET['action'] === 'login') {
        $user = $auth->login($data['email'], $data['password']);
        echo json_encode($user ? ['success' => true, 'user' => $user] : ['success' => false]);
    }
}