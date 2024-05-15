<?php

class Product
{
    // подключение к базе данных и таблице "products"
    private $conn;
    private $table_name = "products";

    // свойства объекта
    public $id;
    public $name;
    public $description;
    public $price;
    public $product_type_id;
    public $type_id;
    public $type_name;
    public $type_name_ru;
    public $inSlider;
    public $image;

    // конструктор для соединения с базой данных
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
        $query = "SELECT 
            p.id, p.name, p.description, p.price, p.image, t.id as type_id, t.type_name, t.type_name_ru, p.inSlider 
        FROM
            " . $this->table_name . " AS p
        LEFT JOIN
            product_type AS t
        ON p.product_type_id = t.id";

        if (isset($this->name) && isset($this->type_id)) {
            $query .= " WHERE p.name LIKE :name AND p.product_type_id = :type_id";
        } elseif (isset($this->name)) {
            $query .= " WHERE p.name LIKE :name";
        } elseif (isset($this->type_id)) {
            $query .= " WHERE t.id = :type_id";
        }
        $query .= " ORDER BY p.price DESC";

        $stmtParams = [];

        if (isset($this->name)) $stmtParams[":name"] = $this->name . "%";
        if (isset($this->type_id)) $stmtParams[":type_id"] = $this->type_id;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($stmtParams);
        return $stmt;
    }

    public function create()
    {
        $query = "INSERT INTO 
            " . $this->table_name . "(name,description,price,product_type_id,image)
        VALUES (:name, :description, :price, :product_type_id, :image)";

        $stmtParams = [
            ":name" => $this->name,
            ":description" => $this->description,
            ":price" => $this->price,
            ":product_type_id" => $this->product_type_id,
            ":image" => $this->image
        ];
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute($stmtParams)) {
            return true;
        }
        return false;
    }

    public function readOne()
    {
        $query = "SELECT 
        p.id, p.name, p.description, p.price, p.image, t.type_name, t.type_name_ru, p.inSlider 
    FROM
        " . $this->table_name . " AS p
    LEFT JOIN
        product_type AS t
    ON p.product_type_id = t.id
    WHERE p.id = :id
    ORDER BY p.price DESC";

        $stmt = $this->conn->prepare($query);

        $stmt->execute([":id" => $this->id]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->name = $product["name"] ?? null;
        $this->description = $product["description"] ?? null;
        $this->price = $product["price"] ?? null;
        $this->type_name = $product["type_name"] ?? null;
        $this->type_name_ru = $product["type_name_ru"] ?? null;
        $this->image = $product["image"] ?? null;
        $this->inSlider = $product["inSlider"] ?? null;

        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "price" => $this->price,
            "type_name" => $this->type_name,
            "type_name_ru" => $this->type_name_ru,
            "image" => $this->image,
            "inSlider" => $this->inSlider
        ];
    }

    public function update($id, $name, $description, $price, $product_type_id, $image)
    {
        try {
            $stmtParams = [];
            $setValues = [];

            $query = "UPDATE 
        " . $this->table_name . " SET";

            if (isset($name)) {
                $setValues[] = "name = :name";
                $stmtParams[":name"] = $name;
            }
            if (isset($description)) {
                $setValues[] = "description = :description";
                $stmtParams[":description"] = $description;
            }
            if (isset($price)) {
                $setValues[] = "price = :price";
                $stmtParams[":price"] = $price;
            }
            if (isset($product_type_id)) {
                $setValues[] = "product_type_id = :product_type_id";
                $stmtParams[":product_type_id"] = $product_type_id;
            }
            if (isset($image)) {
                $setValues[] = "image = :image";
                $stmtParams[":image"] = $image;
            }
            $query .= " " . implode(", ", $setValues);
            $query .= " WHERE id = :id";

            $stmtParams[":id"] = $id;

            $stmt = $this->conn->prepare($query);
            $stmt->execute($stmtParams);
            return true;
        } catch (Exception $error) {
            echo (json_encode(array("message" => $error->getMessage())));
            return false;
        }
    }

    public function delete()
    {
        $query = "DELETE FROM 
        " . $this->table_name . " WHERE id = :id";

        $stmtParams = [
            ":id" => $this->id,
        ];
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute($stmtParams)) {
            return true;
        }
        return false;
    }
}
