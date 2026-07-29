<?php
namespace Model;

require_once __DIR__ . '/../../config/database.php';

class User {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM Users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM Users WHERE User_Id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($name, $email, $password, $address) {
        $stmt = $this->conn->prepare("INSERT INTO Users (Name, Email, Password, Address, User_Type) VALUES (?, ?, ?, ?, 'user')");
        $stmt->bind_param("ssss", $name, $email, $password, $address);
        return $stmt->execute();
    }

    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT User_Id FROM Users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}