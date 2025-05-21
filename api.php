<?php
//require_once(__DIR__.'/config.php');
// test

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

    // altered func to handle errors better and to work accordingly with new config
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
        
        try {
            $result = $this->conn->query("DESCRIBE `user`");
            if ($result === false) {
                error_log("Table check failed: ".$this->conn->error);
                return false;
            }
            
            $existingColumns = [];
            while ($row = $result->fetch_assoc()) {
                $existingColumns[$row['Field']] = $row['Type'];
            }
            
            foreach ($requiredColumns as $col => $type) {
                if (!array_key_exists($col, $existingColumns)) {
                    error_log("Missing column: $col");
                    return false;
                }
            }
            return true;
        } catch (Exception $e) {
            error_log("Table verification error: ".$e->getMessage());
            return false;
        }
    }
    
    private function __construct() {
        // changed to match new config.php
        if (!class_exists('Database')) 
        {
            die(json_encode([
                'status' => 'error',
                'message' => 'Database configuration not loaded'
            ]));
        }
    
        try {
            $db = Database::instance();
            $this->conn = $db->getConnection();
            
            if ($this->conn->connect_error) 
            {
                throw new Exception('Database connection failed');
            }
            
            // Verify table structure func
            if (!$this->verifyTableStructure())
            {
                throw new Exception('Table verification failed');
            }
            
        } catch (Exception $e) {
            die(json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]));
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
        $hashedInput = hash("sha256", $salt . $password);
        
        try {
            $stmt = $this->conn->prepare("INSERT INTO user (name, surname, email, password, salt, api_key, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) 
            {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            $stmt->bind_param("sssssss", $name, $surname, $email, $hashedInput, $salt, $api_key, $user_type);
            
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

    /* 
    work in progress shav code starts here
    */

    //will also handle returning errors 
    //provides the response, a json object and an http status code 
    public function respond($status, $data, $http_code = 200){
        http_response_code($http_code);
        echo json_encode([
            "status"=> $status,
            "timestamp" => round(microtime(true) *1000),
            "data" => $data
        ]); //data will be passed in, either the return or error object
        exit();
    }
    
    //this is reused a lot, just check if the user with the assoc api key exists   
    //also returns the user's type. use to check if a customer is trying to do things only an admin can do 
    public function validateKey($key){
        if (!$key) $this->respond("error", "API key not set", 400);
        
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE api_key = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        //throw error since the key isnt valid
        $result = $stmt->get_result();
        if ($result->num_rows <= 0){
            $this->respond("error", "Invalid API key",400);
        }
        return $result->fetch_assoc()["user_type"];
    }
    
    //this will be used for most product population
    public function getAllProducts($data){
        $this->validateKey($data["api_key"]);
        
        if (empty($data["return"])){
            $this->respond("error", "Return parameters not set",400);
        }
        
        //validate return params
        $returnParams = ["product_id", "name", "description", "brand", "image_url"];
        
        $return = $data["return"];
        
        if ($return == "*"){
            $sql = "SELECT * FROM product";
        } else {
            //check for malformed inupt
            if (!is_array($return)) {
                $this->respond("error", "return parameters must be an array of strings or a *",400);
            }   
            //is an array so normalise
            $return = array_map("strtolower", $data["return"]);
            //validate return params
            foreach ($return as $field){
                if (!in_array(strtolower($field), $returnParams)) {
                    $this->respond("error", "$field is not a valid return parameter",400);
                }
            }

            //format return so that SQL likes it
            $cols = implode(",", array_map(function($f) { return "`$f`"; }, $return));
            $sql = "SELECT $cols FROM product";
        }
        
        //optional limit
        if (!isset($data["limit"])){
            $limit = 50;
        }
        else {
            $limit = $data["limit"];
        }
        
        //restrict limit instead of screaming at the user
        //only scream at them if its not numeric
        if (!is_int($limit)) {
            $this->respond("error", "Limit needs to be an integer between 1 and 500",400);
        }
        else if ($limit > 500){
            $limit = 500;
        }
        else if ($limit < 1){
            $limit = 1;
        }
    
        //sort - do the same check as with return 
        //i want to expand on this and add the attributes only obtainable by joins ************************
        if (!isset ($data["sort"]) || !is_string($data["sort"])){
            $sort = "product_id";
        }
        else {
            $sort = $data["sort"];
        }

        $sort_options = ["product_id", "name", "brand"];
        if (!in_array($sort, $sort_options)){
            $this->respond("error", "Invalid selection for sort",400);
        }

        $order = isset($data["order"]) && 
        strtoupper($data["order"]) === "DESC" ? "DESC" : "ASC";

        $fuzzy = isset($data["fuzzy"]) && is_bool($data["fuzzy"]) ? $data["fuzzy"] : true;
        //search will be in json format 
        //also expand on this with joins ******************************************************************
        $search = isset($data["search"]) ? $data["search"] : null;

        $searchParams = ["product_id", "name", "brand"];
        
        $wheres = [];
        $params = [];
        $paramTypes = "";
        
        if ($search){
            if (!is_array($search)){
           $this->respond("error", "Search must be a JSON object",400);
        }
        //validate all values
        foreach ($search as $key => $value){
            //normalise, less errors for no reason
            $keyLower = strtolower($key);
            
            //invalid parameter, stop
            if (!in_array($keyLower, $searchParams)){
                $this->respond("error", "$key is not a valid search parameter", 400);
            }

            switch ($keyLower){
                case "product_id":
                    $wheres[] = "`product_id` = ?";
                    $paramTypes .= 'i';
                    $params[] = $value;
                    break;
                //these cases work the same
                case "name":
                case "brand":
                if ($fuzzy) { //fuzzy search uses LIKE keyword
                    $wheres[] = "`$keyLower` LIKE ?";
                    $paramTypes .= 's';
                    $params[] = "%" . $value . "%";
                } else {
                    $wheres[] = "`$keyLower` = ?";
                    $paramTypes .= 's';
                    $params[] = $value;
                }
                break;
            }
        }
    }

    //finally assemble sql and execute
    $sql .= count($wheres) > 0 ? " WHERE " . implode(" AND ", $wheres) : "";
    $sql .= " ORDER BY `$sort` $order LIMIT ?";
    $paramTypes .= 'i';
    $params[] = $limit;

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param($paramTypes, ...$params);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $this->respond("success", $products, 201);
    } else {
        $this->respond("error", "Database request failed",500);
    }  
}

    //just returns all rows in the offers table, a less useful getOffer
    public function getAllOffers($data){
        $this->validateKey($data["api_key"]);

        $stmt = $this->conn->prepare("SELECT * FROM offers");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows <= 0){
            $this->respond("success", "Offers table is empty",400);
        }
        //table is not empty, populate offers
        $offers = [];

        while($row = $result->fetch_assoc()){
            $offers[] = $row;
        }
        $this->respond("success", $offers, 200); 

    }

    //use this to get all offers for one specific product
    public function getOffer($data){
        $this->validateKey($data["api_key"]);
        
        $prodId = $data["product_id"];
        if(!$prodId || !is_string($prodId)){
            $this->respond("error", "malformed or misssing product_id", 400);
        }

        $stmt = $this->conn->prepare("SELECT * FROM offers WHERE product_id = ?");
        $stmt->bind_param("s", $prodId);
        
        if ($stmt->execute()){
            $result = $stmt->get_result();
            $offers = [];
            while ($row = $result->fetch_assoc()) {
                $offers[] = $row;
            }
            $this->respond("success", $offers, 200);
        } else {
            $this->respond("error", "database request failed", 500);
        }
    }

    //for the passed in id, gets the lowest price where there is stock
    public function getBestOffer($data){
        $this->validateKey($data["api_key"]);

        $prodId = $data["product_id"];
        if(!$prodId || !is_string($prodId)){
            $this->respond("error", "malformed or misssing product_id", 400);
        }

        //sort by asc to get lowest price at first index
        $stmt = $this->conn->prepare("SELECT * FROM offers WHERE product_id = ? AND stock > 0 ORDER BY price ASC LIMIT 1");
        $stmt->bind_param("s", $prodId);

        if ($stmt->execute()){
            $result = $stmt->get_result();
            //respond with best offer
            if ($row = $result->fetch_assoc()){
                $this->respond("success", $row, 200);
            }
            else { //no valid offers 
                $this->respond("success", "no offers found with stock for this product", 200);
            }
        } else {
            $this->respond("error", "database request failed", 500);
        }
    }

    public function createProduct($data){
        //all fields need to be filled in
        $fields = ["name", "description", "brand", "image_url"];
        foreach ( $fields as $field ) {
            if (empty($data[$field])){
                respond("error","$field not set", 400);
            }
        }
        
        //only admins can add products
        if ($this->validateKey($data["api_key"]) !== "admin"){
            $this->respond("error", "you need to be an admin to add products", 403);
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

            //these types will change along with the products and retailers structure
            //this is the big chunky one for now 
            //need to add more sort and search options by joining to associated tables
            //maybe do some fancy things like sort by num reviews or avg *s
            case "GetAllProducts":
                $user->getAllProducts($decodeObj);
            break;
            case "GetAllOffers":
                $user->getAllOffers($decodeObj);
            break;
            case "GetOffer":
                $user->getOffer($decodeObj);
            break;
            case "GetBestOffer":
                $user->getBestOffer($decodeObj);
            break;
            case "CreateProduct":
                $user->createProduct($decodeObj);
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
