<?php

class Customer
{
    // подключение к базе данных и таблице "products"
    private $conn;
    private $table_name = "customers";

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

            $query = "SELECT c.id AS id, c.name AS name, c.surname AS surname, c.email AS email, c.phone AS phone, addr.id AS address_id, addr.address AS address 
        FROM " . $this->table_name  . " AS c
        LEFT JOIN 
        address_customer AS ac
        ON ac.customer_id = c.id
        LEFT JOIN
        addresses AS addr
        ON ac.address_id = addr.id";

            $stmtParams = [];
            if (isset($searchBy)) {
                $query .= " WHERE name LIKE :searchBy OR surname LIKE :searchBy OR email LIKE :searchBy OR phone LIKE :searchBy";
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

    public function create($name = null, $surname = null, $email = null, $phone = null, $address = null)
    {
        $this->conn->beginTransaction();
        try {
            if (isset($address)) {
                $query = "SELECT id FROM addresses WHERE address = :address";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([":address" => $address]);

                $existingAddress = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($existingAddress) {
                    $addressId = $existingAddress['id'];
                } else {
                    $query = "INSERT INTO addresses (address) VALUES (:address)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([":address" => $address]);
                    $addressId = $this->conn->lastInsertId();
                }
            }

            $query = "INSERT INTO 
                " . $this->table_name . "(name,surname,email,phone)
            VALUES (:name, :surname, :email, :phone)";

            $stmtParams = [
                ":name" => $name,
                ":surname" => $surname ?? null,
                ":email" => $email ?? null,
                ":phone" => $phone,
            ];

            $stmt = $this->conn->prepare($query);
            $stmt->execute($stmtParams);

            $customerId = $this->conn->lastInsertId();

            if (isset($addressId)) {
                $query = "INSERT INTO address_customer(address_id, customer_id) VALUES (:address_id, :customer_id)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ":address_id" => $addressId,
                    ":customer_id" => $customerId
                ]);
            }

            $this->conn->commit();

            return true;
        } catch (PDOException $error) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function update($id, $name, $surname, $email, $phone)
    {
        $this->conn->beginTransaction();
        try {
            $stmtParams = [];
            $setValues = [];

            $query = "UPDATE 
            " . $this->table_name . " SET";

            if (isset($name)) {
                $setValues[] = "name = :name";
                $stmtParams[":name"] = $name;
            }
            if (isset($surname)) {
                $setValues[] = "surname = :surname";
                $stmtParams[":surname"] = $surname;
            }
            if (isset($email)) {
                $setValues[] = "email = :email";
                $stmtParams[":email"] = $email;
            }
            if (isset($phone)) {
                $setValues[] = "phone = :phone";
                $stmtParams[":phone"] = $phone;
            }
            $query .= " " . implode(", ", $setValues);
            $query .= " WHERE id = :id";

            $stmtParams[":id"] = $id;

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

    public function delete()
    {
        try {
            $query = "DELETE FROM 
            " . $this->table_name . " WHERE id = :id";

            $stmtParams = [
                ":id" => $this->id,
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
