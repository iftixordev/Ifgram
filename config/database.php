<?php
class Database {
    private $pdo;
    
    public function __construct() {
        $this->pdo = new PDO('sqlite:' . __DIR__ . '/../data/ifgram.db');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->createTables();
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function updateCounts($table, $id, $field, $increment = 1) {
        $sql = "UPDATE {$table} SET {$field} = {$field} + ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$increment, $id]);
    }
    
    private function createTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            bio TEXT,
            avatar_url TEXT,
            is_verified INTEGER DEFAULT 0,
            followers_count INTEGER DEFAULT 0,
            following_count INTEGER DEFAULT 0,
            posts_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            media_url TEXT NOT NULL,
            media_type TEXT DEFAULT 'image',
            caption TEXT,
            likes_count INTEGER DEFAULT 0,
            comments_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users (id)
        );
        
        CREATE TABLE IF NOT EXISTS stories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            media_url TEXT NOT NULL,
            media_type TEXT DEFAULT 'image',
            expires_at DATETIME,
            views_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users (id)
        );
        
        CREATE TABLE IF NOT EXISTS comments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            text TEXT NOT NULL,
            parent_id INTEGER,
            likes_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts (id),
            FOREIGN KEY (user_id) REFERENCES users (id),
            FOREIGN KEY (parent_id) REFERENCES comments (id)
        );
        
        CREATE TABLE IF NOT EXISTS likes (
            user_id INTEGER NOT NULL,
            post_id INTEGER,
            comment_id INTEGER,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (user_id, post_id, comment_id),
            FOREIGN KEY (user_id) REFERENCES users (id),
            FOREIGN KEY (post_id) REFERENCES posts (id),
            FOREIGN KEY (comment_id) REFERENCES comments (id)
        );
        
        CREATE TABLE IF NOT EXISTS follows (
            follower_id INTEGER NOT NULL,
            following_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (follower_id, following_id),
            FOREIGN KEY (follower_id) REFERENCES users (id),
            FOREIGN KEY (following_id) REFERENCES users (id)
        );
        
        CREATE TABLE IF NOT EXISTS hashtags (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL,
            posts_count INTEGER DEFAULT 0
        );
        
        CREATE TABLE IF NOT EXISTS post_hashtags (
            post_id INTEGER NOT NULL,
            hashtag_id INTEGER NOT NULL,
            PRIMARY KEY (post_id, hashtag_id),
            FOREIGN KEY (post_id) REFERENCES posts (id),
            FOREIGN KEY (hashtag_id) REFERENCES hashtags (id)
        );
        ";
        
        $this->pdo->exec($sql);
    }
}