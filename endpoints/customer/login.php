<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/config/config.php";

// Заголовки
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Здесь будет соединение с БД
include_once "../../config/Database.php";
include_once "../../objects/Customer.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();

$customer = new Customer($db);

if (empty($_POST["phone"]) || empty($_POST["code"] || empty($_POST["otp_id"]))) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют запрашиваемые параметры.")));
}

$sql = "SELECT phone, code FROM otp WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->execute([
    ":id"=>trim($_POST["otp_id"]),
]);
$otp = $stmt->fetch(PDO::FETCH_ASSOC);

if(empty($otp)) {
    http_response_code(500);
    die(json_encode(array("message"=>"Данные об отправленном запросе отсутствуют. Попробуйте запросить код повторно.")));
}

if($otp["code"] !== $_POST["code"] || $otp["phone"] !== $_POST["phone"]) {
    http_response_code(400);
    die(json_encode(array("message"=>"Неправильный код подтверждения.")));
}

$data = [
    "name" => !empty($_POST["name"]) ? trim($_POST["name"]) : null,
    "surname" => !empty($_POST["surname"]) ? trim($_POST["surname"]) : null,
    "email" => !empty($_POST["email"]) ? trim($_POST["email"]) : null,
    "phone" => trim($_POST["phone"]),
    "address" => !empty($_POST["address"]) ? trim($_POST["address"]) : null
];

$phone_exists = $customer->phoneExists($data["phone"]);

include_once "../../libs/php-jwt/src/JWT.php";

use \Firebase\JWT\JWT;

if ($phone_exists) {
    try {
        // подтверждение номера
        // Login
        $token = array(
            "iss" => $_ENV["JWT_ISS"],
            "aud" => $_ENV["JWT_AUD"],
            "iat" => $_ENV["JWT_IAT"],
            "nbf" => $_ENV["JWT_NBF"],
            "data" => array(
                "id" => $customer->id,
                "name" => $customer->name,
                "surname" => $customer->surname,
                "phone" => $customer->phone,
                "email" => $customer->email,
                "role" => "user",
            )
        );

        http_response_code(200);

        $jwt = JWT::encode($token,$_ENV["JWT_KEY"], 'HS256');
        echo json_encode(
            array(
                "jwt" => $jwt
            )
        );
    } catch (Exception $err) {
        http_response_code(500);
        echo json_encode(array("message" => "Ошибка входа"));
    }
} else {
    // Registration
    try {
        if ($customer->create($data["name"], $data["surname"], $data["email"], $data["phone"], $data["address"])) {
            $token = array(
                "iss" => $_ENV["JWT_ISS"],
                "aud" => $_ENV["JWT_AUD"],
                "iat" => $_ENV["JWT_IAT"],
                "nbf" => $_ENV["JWT_NBF"],
                "data" => array(
                    "id" => $customer->id,
                    "name" => $customer->name,
                    "surname" => $customer->surname,
                    "phone" => $customer->phone,
                    "email" => $customer->email,
                    "role" => "user",
                )
            );
            $jwt = JWT::encode($token, $_ENV["JWT_KEY"], 'HS256');

            http_response_code(201);

            echo json_encode(array(
                "jwt" => $jwt), JSON_UNESCAPED_UNICODE);
        } else {
            http_response_code(503);
            die(json_encode(array("message" => "Невозможно создать клиента."), JSON_UNESCAPED_UNICODE));
        }
    } catch (Exception $err) {
        echo(json_encode(array("message" => $err->getMessage())));
    }
}
