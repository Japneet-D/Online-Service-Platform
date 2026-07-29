<?php
namespace Controller;

require_once __DIR__ . '/../../config/database.php';

class AuthController {
    public function login() {
        global $conn;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=signin");
            exit();
        }
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT * FROM Users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['Password'])) {
                session_start();
                $_SESSION['user_id'] = $user['User_Id'];
                $_SESSION['user_type'] = $user['User_Type'];
                header("Location: index.php?route=home");
                exit();
            } else {
                echo "Invalid password!";
            }
        } else {
            echo "User not found!";
        }
    }

    public function signup() {
        global $conn;
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=signup");
            exit();
        }
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $address = $_POST['address'];

        // Check if email exists
        $stmt = $conn->prepare("SELECT * FROM Users WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            die("Email already exists!");
        }

        $stmt = $conn->prepare("INSERT INTO Users (Name, Email, Password, Address, User_Type) VALUES (?, ?, ?, ?, 'user')");
        $stmt->bind_param("ssss", $name, $email, $password, $address);
        if ($stmt->execute()) {
            header("Location: index.php?route=signin");
        } else {
            echo "Error: " . $conn->error;
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: index.php?route=home");
        exit();
    }
}