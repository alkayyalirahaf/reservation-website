<?php
// Set the default timezone for all date/time operations
date_default_timezone_set('Asia/Amman');

class Database
{
    private $servername = "localhost";
    private $username   = "root";
    private $password   = "";
    private $dbname     = "cleaning_reservations";
    private $pdo;

    public function __construct()
    {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->servername};dbname={$this->dbname};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}