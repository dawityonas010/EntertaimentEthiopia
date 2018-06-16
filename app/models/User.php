<?php

class Database
{
    private static $ins = null;

    private static $host = "localhost";
    private static $user = "root";
    private static $pass = "";
    private static $dbname = "entertaiment_ethiopia";

    private static $dbh;
    private $error;
    private $stmt;

    public static function getInstance()
    {
        if (self::$ins == null) {
            self::$ins = new Database;
        }
        return self::$ins;
    }

    public function __construct()
    {
        // Set DSN
        $dsn = 'mysql:host=' . self::$host . ';dbname=' . self::$dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        // Create a new PDO instanace
        try {
            self::$dbh = new PDO ($dsn, self::$user, self::$pass, $options);
        }        // Catch any errors
        catch (PDOException $e) {
            $this->error = $e->getMessage();
        }
    }

    // Prepare statement with query
    public function query($query)
    {
        $this->stmt = self::$dbh->prepare($query);
    }

    // Bind values
    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value) :
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value) :
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value) :
                    $type = PDO::PARAM_NULL;
                    break;
                default :
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Execute the prepared statement
    public function execute()
    {
        return $this->stmt->execute();
    }

    // Get result set as array of objects
    public function resultset()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Get single record as object
    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    // Get record row count
    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    // Returns the last inserted ID
    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }
}

class User
{
    public $name;
    public $username;
    public $email;
    protected $password;

//    private $db;

    public function __construct($uname = null, $pwd = null, $email = null, $name = null)
    {
        $this->username = $uname;
        $this->password = $pwd;
        $this->email = $email;
        $this->name = $name;

//      $this->db = new Database;
    }

    public function getPassord()
    {
        return $this->password;
    }

    public function setPassword($pwd)
    {
        $this->password = $pwd;
    }

    // Add User / Register
    public function addUser($data)
    {
      // Prepare Query
        Database::getInstance()->query('INSERT INTO users (name, email,password) 
      VALUES (:name, :email, :password)');

      // Bind Values
        Database::getInstance()->bind(':name', $data['name']);
        Database::getInstance()->bind(':email', $data['email']);
        Database::getInstance()->bind(':password', $data['password']);
      
      //Execute
        if (Database::getInstance()->execute()) {
        return true;
      } else {
        return false;
      }
    }

    // Find User by Email
    public function findUserByEmail($email){
        Database::getInstance()->query("SELECT * FROM users WHERE email = :email");
        Database::getInstance()->bind(':email', $email);

        $row = Database::getInstance()->single();

      //Check Rows
        if (Database::getInstance()->rowCount() > 0) {
        return true;
      } else {
        return false;
      }
    }

    // Login / Authenticate User
    public function verify($data)
    {
        Database::getInstance()->query("SELECT * FROM users WHERE username = :uname");
        Database::getInstance()->bind(':uname', $data['username']);

        $row = Database::getInstance()->single();

//      $hashed_password = $row->password;
//      password_verify($password, $hashed_password);
        if (isset($row->username)) {
            return true;
      } else {
        return false;
      }
    }

    //gets the type of the user
    public function authenticateUser($data)
    {
        Database::getInstance()->query("SELECT * FROM `users` WHERE `username` = :uname AND `password` = :pwd");
        Database::getInstance()->bind(':uname', $data['username']);
        Database::getInstance()->bind(':pwd', $data['password']);

        $row = Database::getInstance()->single();

        $ut = $row->userType;
        return $ut;
    }

    // Find User By ID
    public function getUserById($id){
        Database::getInstance()->query("SELECT * FROM users WHERE id = :id");
        Database::getInstance()->bind(':id', $id);

        $row = Database::getInstance()->single();

        return $row;
    }

}
/*
$u=new User("dawit","test123");
$f = $u->verify(["username"=>"dawit","password"=>"test123"]);
if($f){
  echo 'pases';
}else{
  echo 'dont pass';
}
*/