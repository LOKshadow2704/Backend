<?php
require(__DIR__ . '/../config/database.php');

class Database{
    public function connect_db(){
        $dsn = "mysql:host=" . DatabaseConfig::$host . ";dbname=" . DatabaseConfig::$dbname .";charset=utf8mb4";
        try
        {
            $connect = new PDO($dsn, DatabaseConfig::$username , DatabaseConfig::$password);
            $connect -> setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            return $connect;
        }catch(PDOException $e)
        {
            echo "Connection failed; " . $e->getMessage();
            return false;
        }
    }

    public function disconnect_db(PDO $connect){
        try{
            $connect = null;
            return true;
        }catch(PDOException $e){
            return false;
        }
    }
}