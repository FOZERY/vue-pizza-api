<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/config/config.php";

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
class AuthMiddleware
{
    private $secret_key;
    public function __construct() {
        $this->secret_key = $_ENV["JWT_KEY"];
    }
    public function validateToken($jwt = null, $role = null)
    {
        try {
            if(!$jwt) {
                http_response_code(401);
                echo json_encode(array("message"=>"Не авторизован."));
                return false;
            }
            $decoded = JWT::decode($jwt, new Key($this->secret_key, "HS256"));
            if(!empty($role) && $decoded->data->role != $role) {
                http_response_code(403);
                echo json_encode(array("message"=>"Нет доступа."));
                return false;
            }

            http_response_code(200);
            return $decoded->data;
        } catch(Exception $e) {
            http_response_code(401);
            echo(json_encode(array("message" => "Не авторизован.")));
            return false;
        }
    }
}