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
    public $in_slider;
    public $image;

    // конструктор для соединения с базой данных
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
       /*
        $query = "SELECT
            p.id, p.name, p.description, p.price, p.image, t.id as type_id, t.type_name, t.type_name_ru, p.in_slider 
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
        $query .= " ORDER BY p.price DESC";S
       */
        $query = "SELECT * FROM get_products(:name, :type_id);";
        $stmtParams = [
            ":name"=>$this->name ?? null,
            ":type_id"=>$this->type_id ?? null,
        ];

        $stmt = $this->conn->prepare($query);
        $stmt->execute($stmtParams);
        return $stmt;
    }

    public function create()
    {
        /*
        $query = "INSERT INTO
            " . $this->table_name . "(name,description,price,product_type_id,image)
        VALUES (:name, :description, :price, :product_type_id, :image)";
        */

        $query = "CALL create_product(:name, :description, :price, :product_type_id, :image)";

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
    /*
        $query = "SELECT
        p.id, p.name, p.description, p.price, p.image, t.type_name, t.type_name_ru, p.in_slider
    FROM products AS p
    LEFT JOIN
        product_type AS t
    ON p.product_type_id = t.id
    WHERE p.id = :id";
    */
        $query = "SELECT * FROM get_product_details(:id)"; // использование функции

        $stmt = $this->conn->prepare($query);

        $stmt->execute([":id" => $this->id]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->name = $product["name"] ?? null;
        $this->description = $product["description"] ?? null;
        $this->price = $product["price"] ?? null;
        $this->type_name = $product["type_name"] ?? null;
        $this->type_name_ru = $product["type_name_ru"] ?? null;
        $this->image = $product["image"] ?? null;
        $this->in_slider = $product["in_slider"] ?? null;

        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "price" => $this->price,
            "type_name" => $this->type_name,
            "type_name_ru" => $this->type_name_ru,
            "image" => $this->image,
            "in_slider" => $this->in_slider
        ];
    }

    public function update($id, $name, $description, $price, $product_type_id, $image)
    {
        try {
            $stmtParams = [];
            $setValues = [];


            $query = "CALL update_product(:id, :name, :description, :price, :product_type_id,:image);";

            $stmtParams = [
                ":id"=>$id,
                ":name"=>$name ?? null,
                ":description"=>$description ?? null,
                ":price"=>$price ?? null,
                ":product_type_id"=>$product_type_id ?? null,
                ":image"=>$image ?? null,
            ];

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
        /*
        $query = "DELETE FROM
        " . $this->table_name . " WHERE id = :id";
        */
        $query = "CALL delete_product(:id)";

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
