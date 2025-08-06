<?php
// db_config.php - Enhanced Database Configuration with Error Handling

// Database Configuration Constants
define('DB_SERVER', 'localhost');
define('DB_PORT', 3306);
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'logistics1_db');
define('DB_CHARSET', 'utf8mb4');

// Error Reporting Configuration
define('DB_DEBUG_MODE', true); // Set to false in production

// Enable error reporting based on debug mode
if (DB_DEBUG_MODE) {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    mysqli_report(MYSQLI_REPORT_OFF);
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Create connection with error handling
try {
    $conn = new mysqli();
    
    // Establish connection with proper error handling
    if (!$conn->real_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT)) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set connection charset
    if (!$conn->set_charset(DB_CHARSET)) {
        throw new Exception("Error loading character set " . DB_CHARSET . ": " . $conn->error);
    }
    
    // Set timezone to UTC for consistency
    $conn->query("SET time_zone = '+00:00'");
    
    // Additional connection validation
    if (!$conn->ping()) {
        throw new Exception("Database connection validation failed");
    }
    
} catch (Exception $e) {
    // Log error appropriately
    error_log("Database connection error: " . $e->getMessage());
    
    // Provide user-friendly error message
    if (DB_DEBUG_MODE) {
        die("Database connection failed: " . htmlspecialchars($e->getMessage()));
    } else {
        die("A database connection error occurred. Please try again later.");
    }
}

// Function to check if connection is active
function isDbConnected($connection) {
    return $connection && $connection->ping();
}

// Function to safely close connection
function closeDbConnection($connection) {
    if ($connection && !$connection->connect_errno) {
        $connection->close();
    }
}

// Register shutdown function to close connection
register_shutdown_function(function() use ($conn) {
    closeDbConnection($conn);
});
?>
