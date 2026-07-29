<?php
namespace Model;

require_once __DIR__ . '/../../config/database.php';

class Item {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function getByDepartment($departmentCode) {
        $stmt = $this->conn->prepare("SELECT * FROM Item WHERE Department_Code = ?");
        $stmt->bind_param("s", $departmentCode);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPrice($itemId) {
        $stmt = $this->conn->prepare("SELECT Price FROM Item WHERE Item_Id = ?");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        return $stmt->get_result()->fetch_column();
    }

    public function getById($itemId) {
        $stmt = $this->conn->prepare("SELECT * FROM Item WHERE Item_Id = ?");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}