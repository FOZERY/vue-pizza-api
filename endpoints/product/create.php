<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/Database.php";
include_once "../../objects/Product.php";
include_once "../../config/config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

if (empty($_POST["name"]) || empty($_POST["description"]) || empty($_POST["price"]) || empty($_POST["type_id"])) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют запрашиваемые параметры.")));
}

$data = [
    "name" => trim($_POST["name"]),
    "description" => trim($_POST["description"]),
    "price" => trim($_POST["price"]),
    "type_id" => trim($_POST["type_id"]),
    "image" => "",
];

if (empty($_FILES['image']["name"])) {
    http_response_code(400);
    die(json_encode(array("message" => "Файл изображения отсутствует.")));
}

$imageName = $_FILES["image"]["name"];
$fileTmp = $_FILES["image"]["tmp_name"];

$allowed_extensions = ["png", "jpeg", "jpg", "svg"];
$fileExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowed_extensions)) {
    http_response_code(400);
    echo "Неправильный формат файла";
    return;
}

$destination = $_ENV["ROOT_PATH"] . "/static/products/" . $imageName;
if (!move_uploaded_file($fileTmp, $destination)) {
    http_response_code(500);
    die(json_encode(array("message" => "Ошибка загрузки файла")));
}

$data["image"] = "/static/products/" . $imageName;

$product->name = $data["name"];
$product->description = $data["description"];
$product->price = $data["price"];
$product->product_type_id = $data["type_id"];
$product->image = $data["image"];

if ($product->create()) {
    http_response_code(201);
    echo json_encode(array("message" => "Продукт был создан."), JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(503);
    die(json_encode(array("message" => "Невозможно создать продукт."), JSON_UNESCAPED_UNICODE));
}
