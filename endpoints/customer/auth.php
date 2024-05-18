<?php
// Заголовки
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Expose-Headers: *");

if($_SERVER["REQUEST_METHOD"]==="OPTIONS") {
    http_response_code(200);
    die();
}
// Требуется для декодирования JWT
include_once "../../config/Core.php";
include_once "../../libs/php-jwt/src/JWT.php";
include_once "../../libs/php-jwt/src/Key.php";
include_once "../../middleware/AuthMiddleware.php";
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

if($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$headers = apache_request_headers();

if(!empty($headers["authorization"])) {
    $jwt = explode(" ", $headers["authorization"])[1];
} else {
    $jwt = null;
}

$authMiddleware = new AuthMiddleware();
$decodedData = $authMiddleware->validateToken($jwt);
if($decodedData) {
    $token = array(
        "iss" => Core::$iss,
        "aud" => Core::$aud,
        "iat" => Core::$iat,
        "nbf" => Core::$nbf,
        "data" => array(
            "id" => $decodedData->id,
            "name" => $decodedData->name,
            "surname" => $decodedData->surname,
            "phone" => $decodedData->phone,
            "email" => $decodedData->email,
            "role" => $decodedData->role,
        )
    );


    $jwt = JWT::encode($token, Core::$key, 'HS256');
    echo json_encode(
        array(
            "jwt" => $jwt
        )
    );
}

