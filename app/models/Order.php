<?php
namespace Model;

require_once __DIR__ . '/../../config/database.php';

class Order {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function create($userId, $tripId, $totalPrice, $status = 'processing') {
        $stmt = $this->conn->prepare("INSERT INTO Orders (User_Id, Trip_Id, Total_Price, Status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iids", $userId, $tripId, $totalPrice, $status);
        $stmt->execute();
        return $this->conn->insert_id;
    }

    public function addItem($orderId, $itemId, $quantity) {
        $stmt = $this->conn->prepare("INSERT INTO Order_Items (Order_Id, Item_Id, Quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $orderId, $itemId, $quantity);
        return $stmt->execute();
    }

    public function getUnreviewedItems($userId) {
        $stmt = $this->conn->prepare("
            SELECT o.Order_Id, oi.Item_Id, i.Item_name 
            FROM Orders o
            JOIN Order_Items oi ON o.Order_Id = oi.Order_Id
            JOIN Item i ON oi.Item_Id = i.Item_Id
            LEFT JOIN Reviews r ON o.Order_Id = r.Order_Id AND oi.Item_Id = r.Item_Id
            WHERE o.User_Id = ? AND r.Review_Id IS NULL
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}