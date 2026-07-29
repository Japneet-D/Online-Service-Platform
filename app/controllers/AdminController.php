<?php
namespace Controller;

require_once __DIR__ . '/../../config/database.php';

class AdminController {
    private function checkAdmin() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
            header("Location: index.php?route=home");
            exit();
        }
    }

    public function insert() {
        $this->checkAdmin();
        global $conn;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $table = $_POST['table'];
            $data = array_filter($_POST, function($value) { return $value !== ''; });
            unset($data['table']);
            if (empty($data)) {
                $_SESSION['admin_message'] = ['type' => 'danger', 'text' => 'At least one field must be filled'];
            } else {
                try {
                    $columns = implode(', ', array_keys($data));
                    $placeholders = implode(', ', array_fill(0, count($data), '?'));
                    $types = str_repeat('s', count($data));
                    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...array_values($data));
                    $stmt->execute();
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Record inserted successfully!'];
                } catch (\Exception $e) {
                    $_SESSION['admin_message'] = ['type' => 'danger', 'text' => 'Error: ' . $e->getMessage()];
                }
            }
        }
        header("Location: index.php?route=insert");
        exit();
    }

    public function delete() {
        $this->checkAdmin();
        global $conn;
        $tables = [
            'Users' => 'User_Id', 'Item' => 'Item_Id', 'Branch' => 'Branch_Id',
            'Truck' => 'Truck_Id', 'Trip' => 'Trip_Id', 'Orders' => 'Order_Id', 'Payment' => 'Payment_Id'
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $table = $_POST['table'];
            $id = $_POST['id'];
            try {
                $stmt = $conn->prepare("DELETE FROM $table WHERE {$tables[$table]} = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Record deleted successfully!'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'danger', 'text' => 'No record found with that ID'];
                }
            } catch (\Exception $e) {
                $_SESSION['admin_message'] = ['type' => 'danger', 'text' => 'Error: ' . $e->getMessage()];
            }
        }
        header("Location: index.php?route=delete");
        exit();
    }

    public function select() {
        $this->checkAdmin();
        global $conn;
        $tables = [
            'Users' => ['Name', 'Email', 'User_Type'],
            'Item' => ['Item_name', 'Department_Code'],
            'Branch' => ['Name', 'City'],
            'Truck' => ['License_Plate', 'Availability'],
            'Trip' => ['Destination_Address'],
            'Orders' => ['Status'],
            'Payment' => ['Status']
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $table = $_POST['table'];
            $searchTerm = "%{$_POST['search_term']}%";
            try {
                $columns = implode(' LIKE ? OR ', $tables[$table]) . ' LIKE ?';
                $sql = "SELECT * FROM $table WHERE $columns";
                $stmt = $conn->prepare($sql);
                $params = array_fill(0, count($tables[$table]), $searchTerm);
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
                $stmt->execute();
                $_SESSION['admin_select_results'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $_SESSION['admin_select_table'] = $table;
            } catch (\Exception $e) {
                $_SESSION['admin_message'] = ['type' => 'danger', 'text' => 'Error: ' . $e->getMessage()];
            }
        }
        header("Location: index.php?route=select");
        exit();
    }

    public function update() {
        $this->checkAdmin();
        global $conn;
        $primaryKeys = [
            'Users' => 'User_Id', 'Item' => 'Item_Id', 'Branch' => 'Branch_Id',
            'Truck' => 'Truck_Id', 'Trip' => 'Trip_Id', 'Orders' => 'Order_Id', 'Payment' => 'Payment_Id'
        ];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $table = $_POST['table'];
            $id = $_POST['id'];
            $column = $_POST['column'];
            $value = $_POST['value'];
            try {
                $stmt = $conn->prepare("UPDATE $table SET $column = ? WHERE {$primaryKeys[$table]} = ?");
                $stmt->bind_param('si', $value, $id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $_SESSION['admin_message'] = ['type' => 'success', 'text' => 'Record updated successfully!'];
                } else {
                    $_SESSION['admin_message'] = ['type' => 'danger', 'text' => 'No changes made or invalid ID'];
                }
            } catch (\Exception $e) {
                $_SESSION['admin_message'] = ['type' => 'danger', 'text' => 'Error: ' . $e->getMessage()];
            }
        }
        header("Location: index.php?route=update");
        exit();
    }
}