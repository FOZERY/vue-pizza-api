<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once "../../config/Database.php";
require_once '../../objects/Admin.php';
include_once "../../libs/php-jwt/src/JWT.php";

use \Firebase\JWT\JWT;


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    die();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();

$admin = new Admin($db);

$data = json_decode(file_get_contents("php://input"));

if (empty($data->login) || empty($data->password)) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют запрашиваемые параметры")));
}

$admin->setLogin($data->login);
$loginExists = $admin->loginExists();

if ($loginExists && password_verify($data->password, $admin->getPassword())) {
    try {
        $adminData = [
            "id" => $admin->getId(),
            "login" => $admin->getLogin(),
            "role"=> "admin"
        ];

        $token = array(
            "iss" => $_ENV["JWT_ISS"],
            "aud" => $_ENV["JWT_AUD"],
            "iat" => $_ENV["JWT_IAT"],
            "nbf" => $_ENV["JWT_NBF"],
            "data" => $adminData
        );
        http_response_code(200);

        $jwt = JWT::encode($token, $_ENV["JWT_KEY"], 'HS256');
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
    http_response_code(401);
    echo(json_encode(array("message" => "Ошибка входа. Неверные данные.")));
}