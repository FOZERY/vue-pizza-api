<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/Database.php";
include_once "../../objects/Product.php";
include_once "../../config/path.php";

// получаем соединение с базой данных
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();

// подготовка объекта
$product = new Product($db);

if (empty($_POST["id"])) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют запрашиваемые параметры.")));
}

$data = [
    "id" => trim($_POST["id"]),
    "name" => !empty($_POST["name"]) ? trim($_POST["name"]) : null,
    "description" => !empty($_POST["description"]) ? trim($_POST["description"]) : null,
    "price" => !empty($_POST["price"]) ? trim($_POST["price"]) : null,
    "type_id" => !empty($_POST["type_id"]) ? trim($_POST["type_id"]) : null,
    "image" => null,
];

if (!empty($_FILES['image']["name"])) {
    $imageName = $_FILES["image"]["name"];
    $fileTmp = $_FILES["image"]["tmp_name"];

    $allowed_extensions = ["png", "jpeg", "jpg", "svg"];
    $fileExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

    if (!in_array($fileExtension, $allowed_extensions)) {
        http_response_code(400);
        echo "Неправильный формат файла";
        return;
    }

    $destination = ROOT_PATH . "/static/products/" . $imageName;
    if (!move_uploaded_file($fileTmp, $destination)) {
        http_response_code(500);
        die(json_encode(array("message" => "Ошибка загрузки файла")));
    }

    $data["image"] = "/static/products/" . $imageName;
}

if ($product->update($data["id"], $data["name"], $data["description"], $data["price"], $data["type_id"], $data["image"])) {
    http_response_code(200);
    echo json_encode(array("message" => "Продукт был обновлён."), JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(503);
    die(json_encode(array("message" => "Невозможно обновить продукт."), JSON_UNESCAPED_UNICODE));
}
