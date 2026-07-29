<?php
namespace Model;

require_once __DIR__ . '/../../config/database.php';

class Truck {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function updateStatus($truckId, $status) {
        $stmt = $this->conn->prepare("UPDATE Truck SET Availability = ? WHERE Truck_Id = ?");
        $stmt->bind_param("si", $status, $truckId);
        return $stmt->execute();
    }
}