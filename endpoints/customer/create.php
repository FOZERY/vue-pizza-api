<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/Database.php";
include_once "../../objects/Customer.php";
include_once "../../config/path.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();
$customer = new Customer($db);

if (empty($_POST["name"]) || empty($_POST["phone"])) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют запрашиваемые параметры.")));
}

$data = [
    "name" => !empty($_POST["name"]) ? trim($_POST["name"]) : null,
    "email" => !empty($_POST["email"]) ? trim($_POST["email"]) : null,
    "phone" =>  !empty($_POST["phone"]) ? trim($_POST["phone"]) : null,
    "address" =>  !empty($_POST["address"]) ? trim($_POST["address"]) : null
];

if (strlen($data["phone"]) > 11) {
    http_response_code(400);
    die(json_encode(array("message" => "Неверная длина номера телефона")));
}

if ($customer->create($data["name"],  $data["email"], $data["phone"], $data["address"])) {
    http_response_code(201);
    echo json_encode(array("message" => "Клинт был создан."), JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(503);
    die(json_encode(array("message" => "Невозможно создать клиента."), JSON_UNESCAPED_UNICODE));
}
