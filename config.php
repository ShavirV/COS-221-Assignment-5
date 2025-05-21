<?php

// using shavir config cause it works
//this file will store all the database stuff, so we'll include
//it in the header file since thats present on all pages
//singleton design pattern since we only need one database connection
class Database {
    private static $instance = null;
    private $conn;

    private $host = "wheatley.cs.up.ac.za";
    // student id
    private $user = "";
    // db password
    private $pass = "";
    // db name
    private $db = "";

    //establish a connection to the database
    private function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);

        if ($this->conn->connect_error) 
        {
            die(json_encode([
                "status" => "error",
                "timestamp" => round(microtime(true) * 1000),
                "data" => "Database connection failed: " . $this->conn->connect_error
            ]));
        }
    }

    public function __destruct() {
        //disconnect from the database
        $this->conn->close();
    }

    public static function instance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance->conn;
    }

    public function getConnection() {
        return $this->conn;
    }
}

?>