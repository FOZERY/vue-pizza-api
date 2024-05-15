<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

include_once "../../config/Database.php";
include_once "../../objects/Product.php";

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

if (empty($_GET["id"])) {
    http_response_code(400);
    die(json_encode(array("message" => "GET параметр не установлен.")));
}

$product->id = $_GET["id"];

$product = $product->readOne();

if ($product["name"] != null) {
    http_response_code(200);
    echo json_encode($product);
} else {
    http_response_code(404);
    die(json_encode(array("message" => "Продукт не найден.")));
}
