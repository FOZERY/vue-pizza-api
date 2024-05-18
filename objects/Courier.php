<?php

class Courier
{
    // подключение к базе данных и таблице "products"
    private $conn;
    private $table_name = "couriers";

    // свойства объекта
    public $id;
    public $name;
    public $surname;
    public $email;
    public $phone;
    public $address;

    // конструктор для соединения с базой данных
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read($sortBy, $searchBy, $limit, $offset)
    {
        try {
            $sortBy = $sortBy ?? "id ASC";

            $query = "SELECT cour.id AS id, cour.name AS name, cour.surname AS surname, cour.patronymic AS patronymic, cour.phone AS phone FROM " . $this->table_name . " AS cour";

            $stmtParams = [];
            if (isset($searchBy)) {
                $query .= " WHERE name LIKE :searchBy OR surname LIKE :searchBy OR patronymic LIKE :searchBy OR phone LIKE :searchBy";
                $stmtParams = [":searchBy" => $searchBy . "%"];
            }
            $query .= " ORDER BY " . $sortBy;
            $query .= " LIMIT " . $limit . " OFFSET " . $offset;

            $stmt = $this->conn->prepare($query);
            $stmt->execute($stmtParams);
            return $stmt;
        } catch (PDOException $error) {
            die(json_encode(array("message" => $error->getMessage())));
        }
    }

    public function create($name, $surname, $patronymic, $phone)
    {
        $this->conn->beginTransaction();
        try {
            /*
            $query = "INSERT INTO 
                " . $this->table_name . "(name,surname,patronymic,phone)
            VALUES (:name, :surname, :patronymic, :phone)";


            */
            $query = "CALL insert_courier(:name, :surname, :patronymic, :phone);";

            $stmtParams = [
                ":name" => $name,
                ":surname" => $surname,
                ":patronymic" => $patronymic ?? null,
                ":phone" => $phone
            ];

            $stmt = $this->conn->prepare($query);
            $stmt->execute($stmtParams);

            $this->conn->commit();

            return true;
        } catch (PDOException $error) {
            echo $error->getMessage();
            $this->conn->rollBack();
            return false;
        }
    }

    public function update($id, $name, $surname, $patronymic, $phone)
    {
        $this->conn->beginTransaction();
        try {
            $query = "CALL update_courier(:id, :name, :surname, :patronymic, :phone);";

            $stmtParams = [
                ":id"=>$id,
                ":name"=>$name ?? null,
                ":surname"=>$surname ?? null,
                ":patronymic"=>$patronymic ?? null,
                ":phone"=>$phone ?? null
            ];

            $stmt = $this->conn->prepare($query);
            $stmt->execute($stmtParams);

            $this->conn->commit();
            return true;
        } catch (PDOException $error) {
            $this->conn->rollBack();
            echo (json_encode(array("message" => $error->getMessage())));
            return false;
        }
    }

    public function delete($id)
    {
        try {
            /*
            $query = "DELETE FROM 
            " . $this->table_name . " WHERE id = :id";
            */
            $query = "CALL delete_courier(:id);";

            $stmtParams = [
                ":id" => $id,
            ];
            $stmt = $this->conn->prepare($query);
            $stmt->execute($stmtParams);
            return true;
        } catch (PDOException $error) {
            echo (json_encode(array("message" => $error->getMessage())));
            return false;
        }
    }
}
