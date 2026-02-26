<?php
/**
 * Database Connection
 * Reads directly from .env file and connects to database
 */

// ============================================================================
// LOAD .ENV FILE DIRECTLY
// ============================================================================

$root_dir = dirname(dirname(__FILE__));
$env_file = $root_dir . '/.env';

// Check if .env file exists
if (!file_exists($env_file)) {
    die('ERROR: .env file not found in root directory');
}

// Read .env file and load variables
$env_vars = [];
$lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

foreach ($lines as $line) {
    // Skip comments
    if (strpos(trim($line), '#') === 0) continue;
    
    // Parse KEY=VALUE
    if (strpos($line, '=') !== false) {
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove quotes
        if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        
        $env_vars[$key] = $value;
    }
}

// ============================================================================
// CONNECT TO DATABASE USING .ENV VALUES
// ============================================================================

try {
    // Get database credentials from .env
    $host = $env_vars['DB_HOST'] ?? 'localhost';
    $user = $env_vars['DB_USER'] ?? 'root';
    $password = $env_vars['DB_PASSWORD'] ?? '';
    $database = $env_vars['DB_NAME'];
    $port = $env_vars['DB_PORT'] ?? 3306;
    
    // Create PDO connection
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password);
    
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>
