<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PATCH");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/Database.php";
include_once "../../objects/Customer.php";
include_once "../../middleware/AuthMiddleware.php";

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    die();
}

// получаем соединение с базой данных
if ($_SERVER["REQUEST_METHOD"] !== "PATCH") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();

// подготовка объекта
$customer = new Customer($db);

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id) || empty($data->phone)) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют запрашиваемые параметры.")));
}

$headers = apache_request_headers();

if (!empty($headers["Authorization"])) {
    $jwt = explode(" ", $headers["Authorization"])[1];
} else {
    $jwt = null;
}

$authMiddleware = new AuthMiddleware();
$decodedData = $authMiddleware->validateToken($jwt);
if ($decodedData) {
    if($decodedData->id !== $data->id && $decodedData->role !== "admin") {
        http_response_code(403);
        die(json_encode(array("message"=>"Отказано в доступе.")));
    }

    $data = [
        "id" => trim($data->id),
        "name" => !empty($data->name) ? trim($data->name) : null,
        "email" => !empty($data->email) ? trim($data->email) : null,
        "phone" => trim($data->phone),
        "address" => !empty($data->address) ? trim($data->address) : null
    ];

    var_dump($data);
    if ($customer->update($data["id"], $data["name"], $data["email"], $data["phone"])) {
        http_response_code(200);
        echo json_encode(array("message" => "Продукт был обновлён."), JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(503);
        die(json_encode(array("message" => "Невозможно обновить продукт."), JSON_UNESCAPED_UNICODE));
    }
}