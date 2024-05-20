<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once "../../config/Database.php";
include_once "../../objects/Order.php";

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    die();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();
$order = new Order($db);

$data = json_decode(file_get_contents("php://input"));

if(empty($data->customer_id) || empty($data->order_type_id) || empty($data->order_items)) {
    http_response_code(400);
    die(json_encode(array("message"=>"Указаны не все параметры")));
}

$customer_id = $data->customer_id;
$order_type_id = $data->order_type_id;
$order_items = $data->order_items;
$order->setCustomerId($customer_id);
$order->setOrderTypeId($order_type_id);
$order->setOrderItems($order_items);

$order->setOrderStatusId(1); // 1 - принят
$order->setOrderTime(strval(time())); // 1 - принят
$order->setDeliveryPrice(150); // 1 - принят

try {
    $order->create();
    http_response_code(200);
    echo json_encode(array("message"=>"Заказ оформлен!"));
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode(array("message"=>$e->getMessage())));
}



