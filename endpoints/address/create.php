<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/Database.php";
include_once "../../objects/Address.php";
include_once "../../config/path.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();
$address = new Address($db);

if (!isset($_POST["customer_id"]) || !isset($_POST["address"])) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют запрашиваемые параметры.")));
}

$data = [
    "customer_id" => isset($_POST["customer_id"]) ? trim($_POST["customer_id"]) : null,
    "address" => isset($_POST["address"]) ? trim($_POST["address"]) : null
];

if ($address->create($data["address"], $data["customer_id"])) {
    http_response_code(201);
    echo json_encode(array("message" => "Адрес был добавлен."), JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(503);
    die(json_encode(array("message" => "Невозможно добавить адрес."), JSON_UNESCAPED_UNICODE));
}
