<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/Database.php";
include_once "../../objects/Courier.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();
$courier = new Courier($db);

if (empty($_POST["name"]) || empty($_POST["surname"]) || empty($_POST["phone"])) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют запрашиваемые параметры.")));
}

$data = [
    "name" => !empty($_POST["name"]) ? trim($_POST["name"]) : null,
    "surname" => !empty($_POST["surname"]) ? trim($_POST["surname"]) : null,
    "patronymic" => !empty($_POST["patronymic"]) ? trim($_POST["patronymic"]) : null,
    "phone" =>  !empty($_POST["phone"]) ? trim($_POST["phone"]) : null
];

if (strlen($data["phone"]) > 12) {
    http_response_code(400);
    die(json_encode(array("message" => "Неверная длина номера телефона")));
}

if ($courier->create($data["name"], $data["surname"], $data["patronymic"], $data["phone"])) {
    http_response_code(201);
    echo json_encode(array("message" => "Курьер был создан."), JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(503);
    die(json_encode(array("message" => "Невозможно создать курьера."), JSON_UNESCAPED_UNICODE));
}
