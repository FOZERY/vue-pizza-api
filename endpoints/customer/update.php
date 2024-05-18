<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/Database.php";
include_once "../../objects/Customer.php";

// получаем соединение с базой данных
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();

// подготовка объекта
$customer = new Customer($db);

if (empty($_POST["id"])) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют запрашиваемые параметры.")));
}

$data = [
    "id" => trim($_POST["id"]),
    "name" => !empty($_POST["name"]) ? trim($_POST["name"]) : null,
    "surname" => !empty($_POST["surname"]) ? trim($_POST["surname"]) : null,
    "email" => !empty($_POST["email"]) ? trim($_POST["email"]) : null,
    "phone" =>  !empty($_POST["phone"]) ? trim($_POST["phone"]) : null,
    "address" =>  !empty($_POST["address"]) ? trim($_POST["address"]) : null
];


if ($customer->update($data["id"], $data["name"], $data["surname"], $data["email"], $data["phone"])) {
    http_response_code(200);
    echo json_encode(array("message" => "Продукт был обновлён."), JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(503);
    die(json_encode(array("message" => "Невозможно обновить продукт."), JSON_UNESCAPED_UNICODE));
}
