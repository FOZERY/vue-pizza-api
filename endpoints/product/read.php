<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once "../../config/Database.php";
include_once "../../objects/Product.php";

// получаем соединение с базой данных
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();

// инициализируем объект
$product = new Product($db);

$product->name = $_GET["name"] ?? null;
$product->type_id = $_GET["type_id"] ?? null;

$stmt = $product->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode($products);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Продукты не найдены."), JSON_UNESCAPED_UNICODE);
}
