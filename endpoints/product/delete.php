<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// подключим файл для соединения с базой и объектом Product
include_once "../../config/Database.php";
include_once "../../objects/Product.php";
include_once "../../config/path.php";

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

$product->id = $_POST["id"];

if ($product->delete()) {
    http_response_code(200);
    echo json_encode(array("message" => "Продукт был удалён"), JSON_UNESCAPED_UNICODE);
}
// если не удается удалить товар
else {
    // код ответа - 503 Сервис не доступен
    http_response_code(503);

    // сообщим об этом пользователю
    echo json_encode(array("message" => "Не удалось удалить продукт"));
}
