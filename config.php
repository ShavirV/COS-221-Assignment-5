<?php
// Database constants
// i made the config a singleton these credentials are only used here
// see construct function in api to see how i have altered it
// config should now be implemented and used correctly now
// url

require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'details.env');
$dotenv->load();

define('DB_HOST', 'wheatley.cs.up.ac.za');
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_NAME', $_ENV['DB_NAME']);

// *Singleton db now*
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_error) {
            error_log("DB connection failed: " . $this->conn->connect_error);
            die(json_encode([
                "status" => "error",
                "timestamp" => round(microtime(true) * 1000),
                "data" => "Database connection failed"
            ]));
        }
    }

    public static function instance() {
        if (self::$instance === null) 
        {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // destruct function now in config no longer in api.php
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>