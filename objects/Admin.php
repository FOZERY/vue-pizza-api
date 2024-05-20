<?php
class Admin
{
    private $conn;
    private $table_name = "admins";

    private $id;
    private $name;
    private $surname;
    private $login;
    private $password;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setSurname($surname) {
        $this->surname = $surname;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function setLogin($login) {
        $this->login = $login;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function createAdmin($name, $surname, $login, $password) {
        $this->setName($name);
        $this->setSurname($surname);
        $this->setLogin($login);
        $this->setPassword($password);
    }

    public function loginExists()
    {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE login = ? LIMIT 1";

            $stmt = $this->conn->prepare($query);

            $this->login = htmlspecialchars(strip_tags($this->login));
            $stmt->bindParam(1, $this->login);
            $stmt->execute();

            $num = $stmt->rowCount();

            if ($num > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->surname = $row['surname'];
                $this->login = $row['login'];
                $this->password = $row['password'];

                return true;
            }
            return false;
        } catch (Exception $error) {
            http_response_code(500);
            echo json_encode(array("message"=>"Произошла ошибка на сервере"));
            die();
        }
    }
}