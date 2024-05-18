<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../objects/ExolveAPI.php';
require_once '../../config/Database.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

if (empty($_POST["phone"])) {
    http_response_code(400);
    die(json_encode(array("message" => "Отсутствуют запрашиваемые параметры.")));
}

$database = new Database();
$db = $database->getConnection();

$randomCode = strval(random_int(1000, 9999));

$phone = trim($_POST["phone"]);

$text = "{$randomCode}";

$exolveApi = new ExolveAPI($phone, $text);

$result = $text;
//$result = $exolveApi->sendMessage();
if($result) {
    $sql = "INSERT INTO otp(phone,code,timestamp) VALUES(:phone,:code,NOW());";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ":phone" => $phone,
        ":code" => $randomCode,
    ]);
    $opt_id = $db->lastInsertId();

    http_response_code(200);
    echo(json_encode(array("code"=>$randomCode,"otp_id"=>$opt_id)));
} else {
    http_response_code(500);
    echo(json_encode(array("message"=>"Ошибка отправки СМС")));
}
