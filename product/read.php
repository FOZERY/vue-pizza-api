<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../config/database.php";
include_once "../objects/product.php";

// получаем соединение с базой данных
$database = new Database();
$db = $database->getConnection();

// инициализируем объект
$product = new Product($db);

$product->name = $_GET["name"] ?? null;
$product->type_name = $_GET["type_name"] ?? null;

$stmt = $product->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($products);
}
