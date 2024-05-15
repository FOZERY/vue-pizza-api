<?php

class Address
{
    // подключение к базе данных и таблице "products"
    private $conn;
    private $table_name = "addresses";

    // свойства объекта
    public $id;
    public $address;

    // конструктор для соединения с базой данных
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create($address, $customer_id)
    {
        $this->conn->beginTransaction();
        try {
            $query = "SELECT id FROM addresses WHERE address = :address";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([":address" => $address]);

            $existingAddress = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($existingAddress) {
                $address_id = $existingAddress['id'];
            } else {
                $query = "INSERT INTO addresses (address) VALUES (:address)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([":address" => $address]);
                $address_id = $this->conn->lastInsertId();
            }

            // проверка, что клиент с таким id есть
            $query = "SELECT phone FROM customers WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([":id" => $customer_id]);
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception("Ошибка: клиента с таким id нет");
            }

            $query = "INSERT INTO address_customer(address_id, customer_id)
            VALUES (:address_id, :customer_id)";
            $stmtParams = [
                ":address_id" => $address_id,
                ":customer_id" => $customer_id
            ];

            $stmt = $this->conn->prepare($query);
            $stmt->execute($stmtParams);

            $this->conn->commit();

            return true;
        } catch (Exception $error) {
            $this->conn->rollBack();
            echo (json_encode(array("message" => $error->getMessage())));
            return false;
        }
    }
}
