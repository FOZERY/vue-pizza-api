<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Expose-Headers: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once "../../config/Database.php";
include_once "../../objects/Customer.php";
include_once "../../middleware/AuthMiddleware.php";

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    die();
}
// получаем соединение с базой данных
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();

// инициализируем объект
$customer = new Customer($db);


if (empty($_GET["id"])) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют необходимые параметры.")));
}

$id = trim($_GET["id"]);

$headers = apache_request_headers();

if (!empty($headers["Authorization"])) {
    $jwt = explode(" ", $headers["Authorization"])[1];
} else {
    $jwt = null;
}

$authMiddleware = new AuthMiddleware();
$decodedData = $authMiddleware->validateToken($jwt);
if ($decodedData) {
    if ((int)$decodedData->id !== (int)$_GET["id"]) {
        http_response_code(403);
        die(json_encode(array("message" => "Нет доступа.")));
    }
    $customerData = $customer->readOne($id);
    if ($customerData) {
        http_response_code(200);
        echo json_encode($customerData);
    } else {
        http_response_code(404);
        die(json_encode(array("message" => "Клиент не найден.")));
    }
}

