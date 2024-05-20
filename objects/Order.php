<?php
require_once "Product.php";
class Order
{
    private $conn;
    private $table_name = "orders";

    private $id;
    private $courier_id;
    private $customer_id;
    private $order_type_id;
    private $order_status_id;
    private $order_time;
    private $order_price;
    private $order_items;
    private $delivery_price;
    private $delivery_requested_time;
    private $delivery_actual_time;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setCustomerId($customer_id) {
        $this->customer_id = $customer_id;
    }

    public function setCourierId($courier_id) {
        $this->courier_id = $courier_id;
    }

    public function setOrderTypeId($order_type_id) {
        $this->order_type_id = $order_type_id;
    }

    public function setOrderStatusId($order_status_id) {
        $this->order_status_id = $order_status_id;
    }

    public function setOrderTime($order_time) {
        $this->order_time = $order_time;
    }

    public function setOrderPrice($order_price) {
        $this->order_price = $order_price;
    }

    public function setOrderItems($order_items) {
        $this->order_items = $order_items;
    }

    public function setDeliveryPrice($delivery_price) {
        $this->delivery_price = $delivery_price;
    }

    public function setDeliveryRequestedTime($delivery_requested_time) {
        $this->delivery_requested_time = $delivery_requested_time;
    }

    public function setDeliveryActualTime($delivery_actual_time) {
        $this->delivery_actual_time = $delivery_actual_time;
    }

    public function getId() {
        return $this->id;
    }

    public function getCustomerId() {
        return $this->customer_id;
    }

    public function getCourierId() {
        return $this->courier_id;
    }

    public function getOrderTypeId() {
        return $this->order_type_id;
    }

    public function getOrderStatusId() {
        return $this->order_status_id;
    }
    public function getOrderTime() {
        return $this->order_time;
    }

    public function getOrderPrice() {
        return $this->order_price;
    }

    public function getOrderItems() {
        return $this->order_items;
    }

    public function getDeliveryPrice() {
        return $this->delivery_price;
    }

    public function getDeliveryRequestedTime() {
        return $this->delivery_requested_time;
    }

    public function getDeliveryActualTime() {
        return $this->delivery_actual_time;
    }

    public function countOrderPrice() {
        try {
            $product = new Product($this->conn);
            $result = 0;
            $items = $this->getOrderItems();
            foreach ($items as $item) {
                $product->id = $item->id;
                ['price' => $itemPrice] = $product->readOne();
                $result += $item->quantity * $itemPrice;
            }
            $this->setOrderPrice($result);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }

    public function create()
    {
        $this->conn->beginTransaction();
        try {
            $this->countOrderPrice();

            $query = "INSERT INTO orders 
            (customer_id, order_type_id, order_status_id, order_time, order_price, delivery_price) 
            VALUES (:customer_id, :order_type_id, :order_status_id, NOW(), :order_price, :delivery_price)";

            $stmt = $this->conn->prepare($query);

            $stmt->bindValue(':customer_id', $this->getCustomerId(), PDO::PARAM_INT);
            $stmt->bindValue(':order_type_id', $this->getOrderTypeId(), PDO::PARAM_INT);
            $stmt->bindValue(':order_status_id', $this->getOrderStatusId(), PDO::PARAM_INT);
            $stmt->bindValue(':order_price', (int)$this->getOrderPrice(), PDO::PARAM_INT);
            $stmt->bindValue(':delivery_price', (int)$this->getDeliveryPrice(), PDO::PARAM_INT);

            $stmt->execute();

            $this->setId($this->conn->lastInsertId());

            $items = $this->getOrderItems();
            $values = ('?, ?, ?');
            $query = "INSERT INTO order_products (product_id, quantity, order_id)
            VALUES ";

            $query .= str_repeat("($values), ", count($items)-1) . "($values);";
            $stmt = $this->conn->prepare($query);

            foreach ($items as $item) {
                $item->order_id = $this->getId();
            }

            $params = array_merge(...array_map(fn($item) => array_values((array)$item),$items));
            $stmt->execute($params);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}