<?php
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "weather1";

        try {
            // First try to connect to existing database
            $this->conn = new mysqli($servername, $username, $password, $dbname);
            
            // If database doesn't exist, create it
            if ($this->conn->connect_error && strpos($this->conn->connect_error, "Unknown database") !== false) {
                // Connect without database
                $this->conn = new mysqli($servername, $username, $password);
                
                if ($this->conn->connect_error) {
                    throw new Exception("Connection failed: " . $this->conn->connect_error);
                }
                
                // Create database
                if (!$this->conn->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
                    throw new Exception("Error creating database: " . $this->conn->error);
                }
                
                // Select the database
                $this->conn->select_db($dbname);
                
                // Create tables
                $this->createTables();
            } elseif ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            // Set charset
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        try {
            // Create weather table if it doesn't exist
            $createWeatherTable = "
                CREATE TABLE IF NOT EXISTS weather (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    city VARCHAR(100) NOT NULL,
                    country VARCHAR(100) NOT NULL,
                    temp DECIMAL(5,2) NOT NULL,
                    pressure INT NOT NULL,
                    humidity INT NOT NULL,
                    speed DECIMAL(5,2) NOT NULL,
                    weather_icon VARCHAR(10) NOT NULL,
                    day_of_week VARCHAR(20) NOT NULL,
                    weather_description VARCHAR(100) NOT NULL,
                    weather_when DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";
            
            if (!$this->conn->query($createWeatherTable)) {
                throw new Exception("Error creating weather table: " . $this->conn->error);
            }
            
        } catch (Exception $e) {
            throw new Exception("Error creating tables: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// If this file is accessed directly, attempt to create database and tables
if (basename($_SERVER['PHP_SELF']) == 'connect.php') {
    try {
        $db = Database::getInstance();
        echo json_encode(['success' => true, 'message' => 'Database connection successful']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => true, 'message' => $e->getMessage()]);
    }
}