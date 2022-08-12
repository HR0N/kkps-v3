<?php

//include "env.php";
//use env\Env;
namespace mydb;

class myDB{
    private $mysql_host;
    private $mysql_user;
    private $mysql_password;
    private $mysql_database;
    public function __construct($env)
    {
        $this->mysql_host = $env::$DB_HOST;
        $this->mysql_user  = $env::$DB_USERNAME;
        $this->mysql_password = $env::$DB_PASSWORD;
        $this->mysql_database = $env::$DB_DATABASE;
    }
    public function connect(){
        $connect=mysqli_connect($this->mysql_host, $this->mysql_user, $this->mysql_password, $this->mysql_database);
        mysqli_set_charset($connect, "utf8mb4");
        if ($connect->connect_error) {
            die("Connection failed: " . $connect->connect_error);
        }
        return $connect;
    }
    public function get_all($sql){
        $result = $this->connect()->query($sql);
        return mysqli_fetch_all($result);
    }
    public function set_last_order($order){
        $sql = "UPDATE `last_order` SET `last_order`='".$order."' WHERE 1";
        $result = $this->connect()->query($sql);
        return ($result);
    }
    public function get_backup_order(){
        $sql = "SELECT * FROM `backup_order` WHERE 1";
        $result = $this->connect()->query($sql);
        return mysqli_fetch_all($result);
    }
    public function get_errors_count(){
        $sql = "SELECT * FROM `errors_count` WHERE 1";
        $result = $this->connect()->query($sql);
        return mysqli_fetch_all($result);
    }
    public function set_backup_order($order){
        $sql = "UPDATE `backup_order` SET `backup_order`='".$order."' WHERE 1";
        $result = $this->connect()->query($sql);
        return ($result);
    }
    public function set_errors_count($errors_count){
        $sql = "UPDATE `errors_count` SET `errors_count`='".$errors_count."' WHERE 1";
        $result = $this->connect()->query($sql);
        return ($result);
    }
    public function get_last_iteration_timestamp(){
        $sql = "SELECT * FROM `last_iteration` WHERE 1";
        $result = $this->connect()->query($sql);
        return mysqli_fetch_all($result);
    }
    public function set_last_iteration_timestamp($timestamp){
        $sql = "UPDATE `last_iteration` SET `timestamp`='".$timestamp."' WHERE 1";
        $result = $this->connect()->query($sql);
        return ($result);
    }
    public function get_dropped_errors(){
        $sql = "SELECT * FROM `drop_errors` WHERE 1";
        $result = $this->connect()->query($sql);
        return mysqli_fetch_all($result);
    }
    public function set_dropped_errors($timestamp){
        $sql = "UPDATE `drop_errors` SET `errors_count`='".$timestamp."' WHERE 1";
        $result = $this->connect()->query($sql);
        return ($result);
    }
}

