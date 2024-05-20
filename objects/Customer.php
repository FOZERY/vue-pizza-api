<?php

class Customer
{
    // подключение к базе данных и таблице "products"
    private $conn;
    private $table_name = "customers";

    // свойства объекта
    public $id;
    public $name;
    public $email;
    public $phone;
    public $address_id;
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

            $query = "SELECT c.id AS id, c.name AS name, c.email AS email, c.phone AS phone, addr.id AS address_id, addr.address AS address 
        FROM " . $this->table_name  . " AS c
        LEFT JOIN 
        address_customer AS ac
        ON ac.customer_id = c.id
        LEFT JOIN
        addresses AS addr
        ON ac.address_id = addr.id";

            $stmtParams = [];
            if (isset($searchBy)) {
                $query .= " WHERE name LIKE :searchBy OR email LIKE :searchBy OR phone LIKE :searchBy";
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

    public function readOne($id)
    {
        try {
            $query = "CALL get_customer_details(:id);";
            $stmt = $this->conn->prepare($query);

            $stmt->execute([
                ":id" => $id,
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $error) {
            die(json_encode(array("message" => $error->getMessage())));

        }
    }

    public function create($name = null, $email = null, $phone = null, $address = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;

        $this->conn->beginTransaction();
        try {
            if (isset($this->address)) {
                $query = "SELECT id FROM addresses WHERE address = :address";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([":address" => $this->address]);

                $existingAddress = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($existingAddress) {
                    $addressId = $existingAddress['id'];
                } else {
                    $query = "INSERT INTO addresses (address) VALUES (:address)";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute([":address" => $this->address]);
                    $addressId = $this->conn->lastInsertId();
                }
            }

            $query = "INSERT INTO 
                " . $this->table_name . "(name,email,phone)
            VALUES (:name, :email, :phone)";

            $stmtParams = [
                ":name" => $this->name,
                ":email" => $this->email ?? null,
                ":phone" => $this->phone,
            ];

            $stmt = $this->conn->prepare($query);
            $stmt->execute($stmtParams);

            $this->id = $this->conn->lastInsertId();

            if (isset($addressId)) {
                $query = "INSERT INTO address_customer(address_id, customer_id) VALUES (:address_id, :customer_id)";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ":address_id" => $addressId,
                    ":customer_id" => $this->id
                ]);
            }

            $this->conn->commit();

            return true;
        } catch (PDOException $error) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function update($id, $name, $email, $phone)
    {
        $this->conn->beginTransaction();
        try {
            $query = "CALL update_customer(:id, :name, :email, :phone)";

            $stmtParams=[
                ":id"=>$id,
                ":name"=>$name ?? null,
                ":email"=>$email ?? null,
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

    public function delete()
    {
        try {
            $query = "CALL delete_customer(:id);";

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

    public function phoneExists($phone)
    {
        $query = "SELECT c.id AS id, c.name AS name, c.email AS email, c.phone AS phone FROM " . $this->table_name  . " AS c
        WHERE phone = :phone;";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([
            ":phone"=>$phone,
        ]);

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->id = $row["id"];
            $this->name = $row["name"];
            $this->email = $row["email"];
            $this->phone = $row["phone"];

            return true;
        }
        return false;
    }
}
