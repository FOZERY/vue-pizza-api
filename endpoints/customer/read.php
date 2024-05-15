<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header('Access-Control-Expose-Headers: Link');

include_once "../../config/Database.php";
include_once "../../objects/Customer.php";

// получаем соединение с базой данных
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    die(json_encode(array("message" => "This method is not allowed.")));
}

$database = new Database();
$db = $database->getConnection();

// инициализируем объект
$customer = new Customer($db);

$sortBy = !empty($_GET["sortBy"]) ? trim($_GET["sortBy"]) : null;
$searchBy = !empty($_GET["searchBy"]) ? trim($_GET["searchBy"]) : null;
$page = !empty($_GET["page"]) ? intval($_GET["page"]) : 1;
$limit = 30;
$offset = ($page - 1) * $limit;

if (isset($sortBy)) {
    if (str_starts_with($sortBy, '-')) {
        $sortBy = substr($sortBy, 1) . " DESC";
    }
}

$stmt = $customer->read($sortBy, $searchBy, $limit, $offset);
$num = $stmt->rowCount();

if ($num > 0) {
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalPages = ceil($num / $limit);

    if ($page > 1) {
        $links[] = "<" . $_SERVER["REQUEST_URI"] . "?page=" . ($page - 1) . "&limit=" . $limit . "&sortBy=" . $sortBy . "&searchBy=" . $searchBy . "> rel=\"prev\"";
    }
    if ($page < $totalPages) {
        $links[] = "<" . $_SERVER['REQUEST_URI'] . "?page=" . ($page + 1) . "&limit=" . $limit . "&sortBy=" . $sortBy . "&searchBy=" . $searchBy . ">; rel=\"next\"";
    }
    $links[] = "<" . $_SERVER['REQUEST_URI'] . "?page=1&limit=" . $limit . "&sortBy=" . $sortBy . "&searchBy=" . $searchBy . ">; rel=\"first\"";
    $links[] = "<" . $_SERVER['REQUEST_URI'] . "?page=" . $totalPages . "&limit=" . $limit . "&sortBy=" . $sortBy . "&searchBy=" . $searchBy . ">; rel=\"last\"";

    header("Link: " . implode(", ", $links));

    http_response_code(200);
    echo json_encode($customers);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "Клиенты не найдены."), JSON_UNESCAPED_UNICODE);
}
