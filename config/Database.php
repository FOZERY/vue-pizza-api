<?php

class Database
{
    private $host = "db";
    private $port = "5432";
    private $db_name = "pizza_delivery";
    private $username = "postgres";
    private $password = "password";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO("pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
        } catch (PDOException $exception) {
            echo "Ошибка подключения: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
