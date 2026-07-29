<?php
namespace Model;

require_once __DIR__ . '/../../config/database.php';

class Payment {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function create($orderId, $amount, $method, $transactionId, $status = 'completed') {
        $stmt = $this->conn->prepare("INSERT INTO Payment (Order_Id, Amount, Payment_Method, Transaction_Id, Status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $orderId, $amount, $method, $transactionId, $status);
        return $stmt->execute();
    }
}