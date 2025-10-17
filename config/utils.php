<?php
class Utils {
    public static function extractHashtags($text) {
        preg_match_all('/#([a-zA-Z0-9_]+)/', $text, $matches);
        return $matches[1];
    }
    
    public static function formatText($text) {
        return preg_replace('/#([a-zA-Z0-9_]+)/', '<span style="color:#1877f2">#$1</span>', htmlspecialchars($text));
    }
    
    public static function timeAgo($datetime) {
        $time = time() - strtotime($datetime);
        if ($time < 60) return 'hozir';
        if ($time < 3600) return floor($time/60) . ' daqiqa oldin';
        if ($time < 86400) return floor($time/3600) . ' soat oldin';
        return floor($time/86400) . ' kun oldin';
    }
    
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
}