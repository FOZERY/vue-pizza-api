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
    public $type_name;
    public $inSlider;

    // конструктор для соединения с базой данных
    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
        $query = "SELECT 
            p.id, p.name, p.description, p.price, p.image, t.type_name, t.type_name_ru, p.inSlider 
        FROM
            " . $this->table_name . " AS p
        LEFT JOIN
            product_type AS t
        ON p.product_type_id = t.id";

        if (isset($this->name) && isset($this->type_name)) {
            $query .= " WHERE p.name LIKE :name AND t.type_name = :type_name";
        } elseif (isset($this->name)) {
            $query .= " WHERE p.name LIKE :name";
        }
        $query .= " ORDER BY p.price DESC";

        $stmtParams = [];

        if (isset($this->name)) $stmtParams[":name"] = $this->name . "%";
        if (isset($this->type_name)) $stmtParams[":type_name"] = $this->type_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($stmtParams);
        return $stmt;
    }
    // здесь будет метод read()
}
