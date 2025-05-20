<?php
require_once(__DIR__.'/config.php');

class User
{
    private $instance;
    private $conn;

    public static function instance()
    {
        static $instance = null;
        if ($instance===null)
            $instance = new User();
        return $instance;
    }

    private function verifyTableStructure() {
        $requiredColumns = [
            'user_id' => 'int',
            'name' => 'varchar',
            'surname' => 'varchar',
            'email' => 'varchar',
            'password' => 'varchar',
            'salt' => 'varchar',
            'api_key' => 'varchar',
            'user_type' => 'enum'
        ];
        
        $result = $this->conn->query("DESCRIBE user");
        $existingColumns = [];
        while ($row = $result->fetch_assoc()) {
            $existingColumns[$row['Field']] = $row['Type'];
        }
        
        foreach ($requiredColumns as $col => $type) {
            if (!array_key_exists($col, $existingColumns)) {
                die(json_encode([
                    'status' => 'error',
                    'message' => "Missing column: $col"
                ]));
            }
        }
    }
    
    private function __construct() {
        $this->conn = new mysqli(
            "wheatley.cs.up.ac.za", 
            // student id
            "", 
            // db password
            "", 
            // db name
            ""
        );
        
        if ($this->conn->connect_error) {
            die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
        }
    }
    
    public function __destruct()
    {
        $this->conn->close();
    }

    public function validAPI($api_key)
    {
        if (strlen($api_key) < 32)
        {
            $obj = [
                'status' => 'error',
                'timestamp' => time(),
                'data' => "Invalid api_key"
            ];
            echo json_encode([$obj], JSON_PRETTY_PRINT);
            exit();
        }
    }

    public function generateAPI()
    {
        $length = 32;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $api_key = '';
        
        for ($i = 0; $i < $length; $i++) {
            $api_key .= $characters[rand(0, $charactersLength - 1)];
        }
        
        $check = $this->conn->prepare("SELECT user_id FROM user WHERE api_key = ?");
        $check->bind_param("s", $api_key);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $check->close();
            return $this->generateAPI();
        }
        
        $check->close();
        return $api_key;
    }

    public function addUser($name, $surname, $email, $password, $user_type)
    {        
        if (empty($name) || empty($surname) || empty($email) || empty($password) || empty($user_type)) {
            $obj = [
                'status' => 'error',
                'timestamp' => time(),
                'data' => "Please ensure all fields are filled in"
            ];
            echo json_encode([$obj], JSON_PRETTY_PRINT);
            exit();
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
        {
            $obj = [
                'status' => 'error',
                'timestamp' => time(),
                'data' => "Invalid email address"
            ];
            echo json_encode([$obj], JSON_PRETTY_PRINT);
            exit();
        }
        
        $validTypes = ['customer', 'admin'];
        if (!in_array($user_type, $validTypes)) 
        {
            $obj = [
                'status' => 'error',
                'timestamp' => time(),
                'data' => "Invalid user type. Must be customer or admin"
            ];
            echo json_encode([$obj], JSON_PRETTY_PRINT);
            exit();
        }
        
        if (strlen($password) < 8 || 
            !preg_match('/[A-Z]/', $password) || 
            !preg_match('/[a-z]/', $password) || 
            !preg_match('/[0-9]/', $password) || 
            !preg_match('/[^a-zA-Z0-9]/', $password)) 
        {
            $obj = [
                'status' => 'error',
                'timestamp' => time(),
                'data' => "Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character"
            ];
            echo json_encode([$obj], JSON_PRETTY_PRINT);
            exit();
        }
        
        $checkEmail = $this->conn->prepare("SELECT user_id FROM user WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();
        
        if ($checkEmail->num_rows > 0) 
        {
            http_response_code(409);
            $obj = [
                'status' => 'error',
                'timestamp' => time(),
                'data' => "Email already exists"
            ];
            echo json_encode([$obj], JSON_PRETTY_PRINT);
            exit();
        }

        $checkEmail->close();
        
        $api_key = $this->generateAPI();

        // adds flavour (unique)
        $salt = bin2hex(random_bytes(16)); 
        $hashedInput = hash("sha256", $user['salt'] . $password);
        
        try {
            $stmt = $this->conn->prepare("INSERT INTO user (name, surname, email, password, salt, api_key, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) 
            {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("sssssss", $name, $surname, $email, $passwordHash, $salt, $api_key, $user_type);
            
            if (!$stmt->execute()) 
            {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            echo json_encode([
                'status' => 'success',
                'timestamp' => time(),
                'data' => [
                    ['api_key' => $api_key] 
                ]
            ], JSON_PRETTY_PRINT);
            
        } catch (Exception $e) {
            http_response_code(500);
            error_log("User registration error: " . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Registration failed',
                'debug' => $e->getMessage() 
            ]);
        }
    }

    public function loginUser($email, $password) {
        
        try {
                if (empty($email) || empty($password)) 
                {
                    throw new Exception("Email and password are required", 400);
                }
        
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
                {
                    throw new Exception("Invalid email format", 400);
                }
        
                $stmt = $this->conn->prepare("SELECT user_id, name, password, salt, api_key, user_type FROM user WHERE email = ?");
                if (!$stmt) 
                {
                    throw new Exception("Database error: " . $this->conn->error, 500);
                }
        
                $stmt->bind_param("s", $email);
                if (!$stmt->execute()) 
                {
                    throw new Exception("Failed to execute query: " . $stmt->error, 500);
                }
        
                $result = $stmt->get_result();
                if ($result->num_rows === 0) 
                {
                    throw new Exception("Invalid email or password", 401);
                }
        
                $user = $result->fetch_assoc();

                error_log("Login attempt for email: " . $email);
                error_log("Stored hash: " . $user['password']);
                error_log("Computed hash: " . hash("sha256", $password . $user['salt']));
                error_log("Salt used: " . $user['salt']);
        
                $hashedInput = hash("sha256", $user['salt'] . $password);
                if ($hashedInput !== $user['password']) 
                {
                    throw new Exception("Invalid email or password", 401);
                }
        
                // Set session variables
                $_SESSION['login'] = true;
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $email;
                $_SESSION['key'] = $user['api_key'];
                $_SESSION['user_type'] = $user['user_type'];
        
                return [
                    'status' => 'success',
                    'timestamp' => time(),
                    'data' => [
                        'api_key' => $user['api_key'],
                        'user_id' => $user['user_id'],
                        'user_type' => $user['user_type']
                    ]
                ];
        
            } catch (Exception $e) 
            {
                return [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode() ?: 500
                ];
            }
    }

    }
    $user = User::instance();
    $jsonObj = file_get_contents('php://input');
    $decodeObj = json_decode($jsonObj, true);

    if (isset($decodeObj['type'])) 
    {
        switch ($decodeObj['type']) 
        {
            case 'Register':
                if (isset($decodeObj['name'], $decodeObj['surname'], $decodeObj['email'], $decodeObj['password'], $decodeObj['user_type'])) {
                    $response = $user->addUser(
                        $decodeObj['name'],
                        $decodeObj['surname'],
                        $decodeObj['email'],
                        $decodeObj['password'],
                        $decodeObj['user_type']
                    );
                } 
                
                else 
                {
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "Missing registration parameters"]);
                }
                break;
            
                case 'Login':
                    if (isset($decodeObj['email'], $decodeObj['password'])) {
                        $response = $user->loginUser(
                            $decodeObj['email'],
                            $decodeObj['password']
                        );

                        http_response_code($response['code'] ?? 200);
                        echo json_encode($response, JSON_PRETTY_PRINT);
                    }
                else 
                {
                    http_response_code(400);
                    echo json_encode([
                        "status" => "error", 
                        "message" => "Email and password required"
                        ]);
                }
                break;

            default:
                http_response_code(400);
                echo json_encode(["status" => "error", "message" => "Invalid request type"]);
                break;
        }
    } 
    
    else
    {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Request type not specified"]);
    }
?>
