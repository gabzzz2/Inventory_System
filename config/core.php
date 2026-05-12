<?php
// config/core.php
session_start();

// Theme Management
if (isset($_GET['theme'])) {
    $theme_val = $_GET['theme'] === 'dark' ? 'dark' : 'light';
    setcookie('theme', $theme_val, time() + (86400 * 30), "/"); // Save for 30 days
    $_SESSION['theme'] = $theme_val;
    
    // Remove theme param from URL to keep it clean
    $url = strtok($_SERVER["REQUEST_URI"], '?');
    $params = $_GET;
    unset($params['theme']);
    $query = http_build_query($params);
    header("Location: " . $url . ($query ? "?$query" : ""));
    exit();
}
// Check Cookie first, then Session, default to light
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : (isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light');

// Database
class Database {
    private $host = "localhost";
    private $db_name = "inventory_db";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            die("Connection error: " . $e->getMessage());
        }
        return $this->conn;
    }
}

// Security
define('APP_SECRET_KEY', 'clothes_inventory_2024_secure_key');
function e($string) { return htmlspecialchars($string, ENT_QUOTES, 'UTF-8'); }
function generateToken() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verifyToken($token) {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        header('HTTP/1.1 403 Forbidden');
        die('CSRF validation failed.');
    }
}
function hashID($id) { return hash_hmac('sha256', (string)$id, APP_SECRET_KEY); }
function redirect($path) { header("Location: $path"); exit(); }
function isLoggedIn() { return isset($_SESSION['user_id']); }

// Role-based Access Control
function checkAccess($allowed_role) {
    if (!isLoggedIn()) redirect('../auth.php');
    if ($_SESSION['role'] !== $allowed_role) {
        header('HTTP/1.1 403 Forbidden');
        die('Unauthorized access.');
    }
}
?>
