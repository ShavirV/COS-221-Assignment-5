<?php
require_once(__DIR__.'/config.php');

/* setting up composer (required for reading env files)

first we need to add php to the system path 

Step 1: 
download php from https://windows.php.net/download/ 
(download the first zip file marked thread safe)

Step 2:
unzip the file and place it in your C drive 

Step 3: ok now it gets a bit tricky 
in the folder you just unzipped and moved to C drive,
find the file called php.ini-development
copy it and rename the copy to php.ini
this is the file that will be used to configure php

Step 4: yes now it gets worse
Press win+R and type in "sysdm.cpl"
go to the advanced tab and click on environment variables
in the user variables section, click new and add a new variable with the name php and the path of the folder you unzipped
click ok until all the windows close

(this worked the first time however when doing it again i had to place it in the system variables section under path 
find the one that says path and click edit 
you will see a list of links
click new and add ur php file path in there
click ok till all boxes close)

Step 5:
restart vs code and open a new terminal 
run command "php -v"
if it works, you should see the version of php you downloaded

PHP 8.0.30 (cli) (built: Sep  1 2023 14:15:38) ( ZTS Visual C++ 2019 x64 )
Copyright (c) The PHP Group
Zend Engine v4.0.30, Copyright (c) Zend Technologies

this was my output, if u see something similar u good

Now because some of our group doesnt wanna use windows 
Here is the isntructions for installing php on mac os 

make sure homebrew is installed
run the command
brew install php

restart vs code and open a new terminal
check if php is installed by running the command
php -v



Now we gotta actually install composer 

Step 1: 
download composer installer from https://getcomposer.org/download/
its the first link on the page

Step 2: 
in the installer, select the php.exe file in the folder you unzipped

step 3: 
complete installation and check if it worked by running the command
"composer -v" 


i cant figure out how to do this on mac os so u gonna have to figure it out yourself
sorry dude

if it doesnt work, try restarting vs code again

next we install the dotenv package
composer require vlucas/phpdotenv


*/



ini_set('log_errors', 1); // Enable logging
ini_set('error_log', __DIR__ . '/error.log'); // Path to your log file
error_reporting(E_ALL); // Report all types of errors

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
    
    // in config file no longer needed here
    /*public function __destruct()
    {
        $this->conn->close();
    }*/

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
        // now fixed
        $hashedInput = hash("sha256", $salt . $password);
        
        try {
            $stmt = $this->conn->prepare("INSERT INTO user (name, surname, email, password, salt, api_key, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt) 
            {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            // small name mismatch error fixed
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

                // should be fixed now to match addUser method
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

        if (!$key){
            $this->respond("error", "API key not set", 400);
        }

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
        //$this->validateKey($data["api_key"]);
        //enquire about this, no longer require apikey for product population, should be easier integration

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
        //$this->validateKey($data["api_key"]);

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
                $this->respond("error","$field not set", 400);
            }
        }
        
        //only admins can add products
        if ($this->validateKey($data["api_key"]) !== "admin"){
            $this->respond("error", "you need to be an admin to add products", 403);
        }
        
        $stmt = $this->conn->prepare("INSERT INTO product (name, description, brand, image_url) values (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data["name"], $data["description"], $data["brand"], $data["image_url"]);
        $stmt->execute();   
        
        if ($stmt->execute()){
            $this->respond("success", "Product successfully added", 200);
        } else{
            $this->respond("error", "database request failed", 500);
        }
    }
    
    // update func
    public function updateProduct($data) {
        if ($this->validateKey($data["api_key"]) !== "admin") 
        {
            $this->respond("error", "Must be logged in as admin to update products", 403);
        }
    
        if (empty($data['product_id'])) 
        {
            $this->respond("error", "product id is required", 400);
        }
    
        // check for product exist ? not
        $productId = $data['product_id'];
        $checkStmt = $this->conn->prepare("SELECT product_id FROM product WHERE product_id = ?");
        $checkStmt->bind_param("i", $productId);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows === 0) 
        {
            $checkStmt->close();
            $this->respond("error", "Product not found", 404);
        }
        $checkStmt->close();
    
        // validations
        $updates = [];
        $params = [];
        $types = "";
        
        // added validation can be removed convenient and matches the table structure type
        $fieldRules = [
            'name' => [
                'max_length' => 50,
                'required' => false
            ],
            'description' => [
                'max_length' => 10000, 
                'required' => false
            ],
            'brand' => [
                'max_length' => 50,
                'required' => false
            ],
            'image_url' => [
                'max_length' => 100,
                'required' => false,
                'filter' => FILTER_VALIDATE_URL
            ]
        ];
    
        foreach ($fieldRules as $field => $rules) 
        {
            if (isset($data[$field])) {
                $value = $data[$field];
                
                // check field lengths
                if (strlen($value) > $rules['max_length']) 
                {
                    $this->respond("error", "$field exceeds maximum length of {$rules['max_length']} characters", 400);
                }
                
                // important check -> sees if url is valid!
                if ($field === 'image_url' && $rules['filter'] && !filter_var($value, $rules['filter'])) {
                    $this->respond("error", "Invalid URL format for image_url", 400);
                }
                
                $updates[] = "`$field` = ?";
                $params[] = $value;
                $types .= "s";
            }
        }
    
        if (empty($updates)) 
        {
            $this->respond("error", "No valid fields provided for update", 400);
        }
    
        $params[] = $productId;
        // i means integer 
        $types .= "i"; 
    
        // actual update query 
        try {
            $sql = "UPDATE product SET " . implode(", ", $updates) . " WHERE product_id = ?";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) 
            {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
    
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) 
            {
                throw new Exception("Execute failed: " . $stmt->error);
            }
    
            if ($stmt->affected_rows === 0) 
            {
                $this->respond("success", [
                    "message" => "Product data unchanged",
                    "affected_fields" => array_keys($data)
                ], 200);
            }
    
            $getStmt = $this->conn->prepare("SELECT * FROM product WHERE product_id = ?");
            $getStmt->bind_param("i", $productId);
            $getStmt->execute();
            $result = $getStmt->get_result();
            $updatedProduct = $result->fetch_assoc();
            
            $this->respond("success", [
                "message" => "Product updated successfully",
                "product" => $updatedProduct
            ], 200);
            
        } catch (Exception $e) {
            $this->respond("error", "Failed to update product: " . $e->getMessage(), 500);
        }
    }

    public function deleteProduct($data) {
        if ($this->validateKey($data["api_key"]) !== "admin") 
        {
            $this->respond("error", "Must be logged in as admin to delete products", 403);
        }
    
        // validate product id
        if (empty($data['product_id'])) 
        {
            $this->respond("error", "product_id is required", 400);
        }
    
        $productId = $data['product_id'];
    
        try {
            // check product existence
            $checkStmt = $this->conn->prepare("SELECT product_id FROM product WHERE product_id = ?");
            $checkStmt->bind_param("i", $productId);
            $checkStmt->execute();
            $checkStmt->store_result();
            
            if ($checkStmt->num_rows === 0) 
            {
                $checkStmt->close();
                $this->respond("error", "Product not found", 404);
            }
            $checkStmt->close();
    
            // gets all product details relating to product to be deleted
            $getStmt = $this->conn->prepare("SELECT * FROM product WHERE product_id = ?");
            $getStmt->bind_param("i", $productId);
            $getStmt->execute();
            $result = $getStmt->get_result();
            $product = $result->fetch_assoc();
            $getStmt->close();
    
            // execute delete
            $deleteStmt = $this->conn->prepare("DELETE FROM product WHERE product_id = ?");
            $deleteStmt->bind_param("i", $productId);

            // if fails 
            if (!$deleteStmt->execute()) 
            {
                throw new Exception("Delete failed: " . $deleteStmt->error);
            }
    
            //  validate success ? failure
            if ($deleteStmt->affected_rows === 0) 
            {
                $this->respond("error", "No product was deleted", 500);
            }
    
            // show it deleted
            $this->respond("success", [
                "message" => "Product deleted successfully",
                "deleted_product" => $product
            ], 200);
    
        } catch (Exception $e) {
            $this->respond("error", "Failed to delete product: " . $e->getMessage(), 500);
        }
    }

    // to add: create product, review?

    public function createRetailer($data){
        //check if all fields are filled and valid
        $fields = ["api_key", "name", "retailer_type", "address", "postal_code", "website", "country"];
        foreach ( $fields as $field ) {
            if (empty($data[$field])){
                $this->respond("error","$field not set", 400);
            }
        }

        //only admins can add products
        if ($this->validateKey($data["api_key"]) !== "admin"){
            $this->respond("error", "You need to be an admin to add retailers", 403);
        }

        //prepare the query and insert
        $stmt = $this->conn->prepare("INSERT INTO retailer (name, retailer_type, opening_time, closing_time, address, postal_code, website, country) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $data["name"], $data["retailer_type"], $data["opening_time"],
                           $data["closing_time"], $data["address"], $data["postal_code"], $data["website"], $data["country"]);
        if ($stmt->execute()){
            $this->respond("success", "Retailer added successfully", 200);
        } 
        $this->respond("error", "database entry failed", 500);
        
    }

    public function createOffer($data){
        //check if all fields are filled and valid
        $fields = ["api_key", "product_id", "retailer_id", "stock", "price", "link"];
        foreach ( $fields as $field ) {
            if (empty($data[$field])){
                $this->respond("error","$field not set", 400);
            }
        }

        //only admins can create offers
        if ($this->validateKey($data["api_key"]) !== "admin"){
            $this->respond("error", "You need to be an admin to add retailers", 403);
        }

        //default values for unset values
        $currency = ($data["currency"]) ? $data["currency"] : "ZAR";
        $discount = ($data["discount"]) ? $data["discount"] : 0;

        //insert into database
        $stmt = $this->conn->prepare("INSERT INTO offers (product_id, retailer_id, stock, price, discount, currency, link) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiddss", $data["product_id"], $data["retailer_id"], $data["stock"],
                           $data["price"], $discount, $currency, $data["link"]);
        if ($stmt->execute()){
            $this->respond("success", "Offer added successfully", 200);
        }
        $this->respond("error", "database entry failed", 500);
 
    }

    //review functionality is still kinda up in the air
    public function createReview($data){
        //required fields
        $fields = ["api_key", "rating", "product_id", "comment"];
        foreach ( $fields as $field ) {
            if (empty($data[$field])){
                $this->respond("error","$field not set", 400);
            }
        }
        
        //get user's id for foreign key
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE api_key = ?");
        $stmt->bind_param("s", $data["api_key"]);
        $stmt->execute();
        
        $result = $stmt->get_result();
        if ($result->num_rows <= 0){
            $this->respond("error", "Invalid API key",400);
        }
        //user does exist, get id
        $userId = $result->fetch_assoc()["user_id"];

        //prepare the insertion
        $stmt = $this->conn->prepare("INSERT INTO review (rating, product_id, comment, user_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $data["rating"], $data["product_id"], $data["comment"], $userId);
        if ($stmt->execute()){
            $this->respond("success", "Review created successfully", 200);
        }

        $this->respond("error", "database entry failed", 500);
 
    }

    //returns all reviews for a product given its id
    //allowing this to be called without a key so its easier to integrate
    //(i think its bad practice and we should use default creds but please check me on that i just know this is easier)
    public function getReviews($data){
        if (!$data["product_id"]) $this->respond("error", "product_id not set", 400);

        $stmt = $this->conn->prepare("SELECT * FROM review WHERE product_id = ?");
        $stmt->bind_param("s", $data["product_id"]);

        if ($stmt->execute()){
            $result = $stmt->get_result();
            if ($result->num_rows <= 0){
                $this->respond("success", "no reviews found for this product", 204); //204 no content
            }
            //>=1 review, collect and send out 
            $reviews = [];
            while($row = $result->fetch_assoc()){
                $reviews[] = $row; 
            }
            $this->respond("success", $reviews, 200);
        }
        $this->respond("error", "database query failed", "500");

    }

    public function editReview($data){
        //only the user that made the review can change it
        //since we arent doing a 1984 thing
        if (!$data["api_key"]){
            $this->respond("error", "API key not set", 400);
        }
        
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE api_key = ?");
        $stmt->bind_param("s", $data["api_key"]);
        $stmt->execute();
        
        //throw error since the key isnt valid
        $result = $stmt->get_result();
        if ($result->num_rows <= 0){
            $this->respond("error", "Invalid API key",400);
        }
        $id =  $result->fetch_assoc()["user_id"];       
        
        //check if the review matches the users id
        if (empty($data["review_id"])){
            $this->respond("error", "review_id not set", 400);
        }
        // Check if review belongs to user
        $stmt = $this->conn->prepare("SELECT * FROM review WHERE review_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $data["review_id"], $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows <= 0) {
            $this->respond("error", "review not found or not owned by this user", 403); // Forbidden
        }

        //funny things can be done since we already got the table row
        $originalReview = $result->fetch_assoc();
        //update if need update
        $newRating = isset($data["rating"]) ? $data["rating"] : $originalReview["rating"];
        $newComment = isset($data["comment"]) ? $data["comment"] : $originalReview["comment"];
        
        //do the thing
        $stmt = $this->conn->prepare("UPDATE review SET rating = ?, comment = ? WHERE review_id = ?");
        $stmt->bind_param("isi", $newRating, $newComment, $data["review_id"]);
        
        if ($stmt->execute()) {
            $this->respond("success", "Review updated successfully", 200);
        } 
        $this->respond("error", "Failed to update review", 500);
    }

    public function deleteReview($data){
        //only admins and the user that made the review can delete it 
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE api_key = ?");
        $stmt->bind_param("s", $data["api_key"]);
        $stmt->execute();    
        $result = $stmt->get_result();

        if ($result->num_rows <= 0){
            $this->respond("error", "Invalid API key",400);
        }

        $user = $result->fetch_assoc();
        $id = $user["user_id"];
        $type = $user["user_type"];
        if (empty($data["review_id"])){
            $this->respond("error", "review_id not set", 400);
        }
        
        if ($type === "admin"){
            //check if review exists
            $stmt = $this->conn->prepare("SELECT * FROM review WHERE review_id = ?");
            $stmt->bind_param("i", $data["review_id"]);
        } else {
            //check if review belongs to user
            $stmt = $this->conn->prepare("SELECT * FROM review WHERE review_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $data["review_id"], $id);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows <= 0) {
            $this->respond("error", "review not found or not owned by this user", 403); //forbidden
        }

        //finally, do the delete
        $stmt = $this->conn->prepare("DELETE FROM review WHERE review_id = ?");
        $stmt->bind_param("i", $data["review_id"]);

        if ($stmt->execute()){
            $this->respond("success", "deleted review $id successfully", 200);
        }
        $this->respond("error", "deletion from the database failed", 500);
    }

    //show user's reviews in the user page, allow edit and delete there
    public function yourReviews($data){
        //check key, get id
        $stmt = $this->conn->prepare("SELECT * FROM user WHERE api_key = ?");
        $stmt->bind_param("s", $data["api_key"]);
        $stmt->execute();
        
        //throw error since the key isnt valid
        $result = $stmt->get_result();
        if ($result->num_rows <= 0){
            $this->respond("error", "Invalid API key",400);
        }
        $id =  $result->fetch_assoc()["user_id"];  

        //easy statement
        $stmt = $this->conn->prepare("SELECT * FROM review WHERE user_id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()){
            $result = $stmt->get_result();
            if ($result->num_rows <= 0){
                $this->respond("success", "no reviews found for this product", 204); //204 no content
            }
            //>=1 review, collect and send out 
            $reviews = [];
            while($row = $result->fetch_assoc()){
                $reviews[] = $row; 
            }
            $this->respond("success", $reviews, 200);
        }
        $this->respond("error", "database query failed", "500");
    }

    // update offers func 
    public function updateOffer($data) {
        if ($this->validateKey($data["api_key"]) !== "admin") 
        {
            $this->respond("error", "Must be logged in as admin to update offers", 403);
        }
    
        // Vcheck for proudct and retailer id
        if (empty($data['product_id']) || empty($data['retailer_id'])) 
        {
            $this->respond("error", "Both product_id and retailer_id are required", 400);
        }
    
        $productId = $data['product_id'];
        $retailerId = $data['retailer_id'];
    
        // check if offer is there
        $checkStmt = $this->conn->prepare("SELECT * FROM offers WHERE product_id = ? AND retailer_id = ?");
        $checkStmt->bind_param("ii", $productId, $retailerId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows === 0) {
            $checkStmt->close();
            $this->respond("error", "Offer not found for this product and retailer combination", 404);
        }
        
        $currentOffer = $checkResult->fetch_assoc();
        $checkStmt->close();
    
        $updates = [];
        $params = [];
        $types = "";
        
        // validation
        $fieldRules = [
            'stock' => [
                'type' => 'integer',
                'min' => 0,
                'required' => false
            ],
            'price' => [
                'type' => 'double',
                'min' => 0,
                'required' => false
            ],
            'discount' => [
                'type' => 'double',
                'min' => 0,
                'max' => 100,
                'required' => false
            ],
            'currency' => [
                'type' => 'string',
                'max_length' => 3,
                'required' => false
            ],
            'link' => [
                'type' => 'string',
                'max_length' => 500,
                'filter' => FILTER_VALIDATE_URL,
                'required' => false
            ]
        ];
    
        // Process each field that needs updating
        foreach ($fieldRules as $field => $rules) {
            if (isset($data[$field])) 
            {
                $value = $data[$field];
                
                // Validate 
                if ($rules['type'] === 'integer' && !is_numeric($value)) 
                {
                    $this->respond("error", "$field must be an integer", 400);
                }
                
                if ($rules['type'] === 'double' && !is_numeric($value)) 
                {
                    $this->respond("error", "$field must be a number", 400);
                }
                
                if (isset($rules['min']) && $value < $rules['min']) 
                {
                    $this->respond("error", "$field cannot be less than {$rules['min']}", 400);
                }
                
                if (isset($rules['max']) && $value > $rules['max']) 
                {
                    $this->respond("error", "$field cannot be more than {$rules['max']}", 400);
                }
                
                if ($field === 'link' && $rules['filter'] && !filter_var($value, $rules['filter'])) 
                {
                    $this->respond("error", "Invalid URL format for link", 400);
                }
                
                if ($field === 'currency' && strlen($value) !== 3) 
                {
                    $this->respond("error", "Currency must be a 3-letter code", 400);
                }
                
                $updates[] = "`$field` = ?";
                $params[] = $value;
                $types .= $rules['type'] === 'integer' ? 'i' : ($rules['type'] === 'double' ? 'd' : 's');
            }
        }
    
        if (empty($updates)) 
        {
            $this->respond("error", "No valid fields provided for update", 400);
        }
    
        // Added the product_id and retailer_id to params for WHERE part
        $params[] = $productId;
        $params[] = $retailerId;
        $types .= 'ii';
    
        // execute quwry
        try {
            $sql = "UPDATE offers SET " . implode(", ", $updates) . " WHERE product_id = ? AND retailer_id = ?";
            $stmt = $this->conn->prepare($sql);
            
            if (!$stmt) 
            {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
    
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) 
            {
                throw new Exception("Execute failed: " . $stmt->error);
            }
    
            if ($stmt->affected_rows === 0) 
            {
                $this->respond("success", [
                    "message" => "Offer data unchanged",
                    "affected_fields" => array_keys($data)
                ], 200);
            }
    
            // gets the new updated offer details
            $getStmt = $this->conn->prepare("SELECT * FROM offers WHERE product_id = ? AND retailer_id = ?");
            $getStmt->bind_param("ii", $productId, $retailerId);
            $getStmt->execute();
            $result = $getStmt->get_result();
            $updatedOffer = $result->fetch_assoc();
            
            $this->respond("success", [
                "message" => "Offer updated successfully",
                "offer" => $updatedOffer,
                "previous_values" => $currentOffer
            ], 200);
            
        } catch (Exception $e) {
            $this->respond("error", "Failed to update offer: " . $e->getMessage(), 500);
        }
    }

    public function deleteOffer($data) {
        if ($this->validateKey($data["api_key"]) !== "admin") 
        {
            $this->respond("error", "Must be logged in as admin to delete offers", 403);
        }
    
        if (empty($data['product_id']) || empty($data['retailer_id'])) 
        {
            $this->respond("error", "Both product_id and retailer_id are required", 400);
        }
    
        $productId = $data['product_id'];
        $retailerId = $data['retailer_id'];
    
        try {
            // check if offer exists
            $checkStmt = $this->conn->prepare("SELECT * FROM offers WHERE product_id = ? AND retailer_id = ?");
            $checkStmt->bind_param("ii", $productId, $retailerId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows === 0) 
            {
                $checkStmt->close();
                $this->respond("error", "Offer not found for this product and retailer combination", 404);
            }
            
            $offerToDelete = $checkResult->fetch_assoc();
            $checkStmt->close();
    
            // Execute delete
            $deleteStmt = $this->conn->prepare("DELETE FROM offers WHERE product_id = ? AND retailer_id = ?");
            $deleteStmt->bind_param("ii", $productId, $retailerId);
    
            if (!$deleteStmt->execute()) 
            {
                throw new Exception("Delete failed: " . $deleteStmt->error);
            }
    
            // check if deletion was a success
            if ($deleteStmt->affected_rows === 0) 
            {
                $this->respond("error", "No offer was deleted", 500);
            }
    
            $this->respond("success", [
                "message" => "Offer deleted successfully",
                "deleted_offer" => $offerToDelete
            ], 200);
    
        } catch (Exception $e) {
            $this->respond("error", "Failed to delete offer: " . $e->getMessage(), 500);
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

            case "UpdateProduct":
                $user->updateProduct($decodeObj);
            break;

            case "DeleteProduct":
                $user->deleteProduct($decodeObj);
            break;

            case "CreateRetailer":
                $user->createRetailer($decodeObj);
            break;

            case "CreateOffer":
                $user->createOffer($decodeObj);
            break;

            //still not sure about review functionality
            case "CreateReview":
                $user->createReview($decodeObj);
            break;

            case "GetReviews":
                $user->getReviews($decodeObj);
            break;

            case "UpdateOffer":
                $user->updateOffer($decodeObj);
            break;

            case "EditReview":
                $user->editReview($decodeObj);
            break;

            case "DeleteReview":
                $user->deleteReview($decodeObj);
            break;

            case "YourReviews":
                $user->yourReviews($decodeObj);
            break;

            case "DeleteOffer":
                $user->deleteOffer($decodeObj);
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