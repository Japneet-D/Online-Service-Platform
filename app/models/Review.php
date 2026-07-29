<?php
namespace Model;

require_once __DIR__ . '/../../config/database.php';

class Review {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function create($userId, $orderId, $itemId, $rating, $reviewText) {
        $stmt = $this->conn->prepare("
            INSERT INTO Reviews (User_Id, Order_Id, Item_Id, Rating, Review_Text)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiiis", $userId, $orderId, $itemId, $rating, $reviewText);
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