<?php
namespace Controller;

require_once __DIR__ . '/../../config/database.php';

class SearchController {
    public function results() {
        global $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_GET['search_term']) || !preg_match('/^\d*,\s*\d*$/', $_GET['search_term'])) {
            header("Location: index.php?route=home");
            exit();
        }

        list($userId, $orderId) = array_pad(array_map('trim', explode(',', $_GET['search_term'])), 2, '');
        $userId = is_numeric($userId) ? (int)$userId : null;
        $orderId = is_numeric($orderId) ? (int)$orderId : null;

        $query = "SELECT o.*, u.Name AS user_name, t.Destination_Address, p.Transaction_Id, p.Payment_Date 
                  FROM Orders o
                  JOIN Users u ON o.User_Id = u.User_Id
                  LEFT JOIN Trip t ON o.Trip_Id = t.Trip_Id
                  LEFT JOIN Payment p ON o.Order_Id = p.Order_Id
                  WHERE 1=1";
        $params = [];
        $types = '';
        if ($userId !== null) {
            $query .= " AND o.User_Id = ?";
            $params[] = $userId;
            $types .= 'i';
        }
        if ($orderId !== null) {
            $query .= " AND o.Order_Id = ?";
            $params[] = $orderId;
            $types .= 'i';
        }

        $stmt = $conn->prepare($query);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Store results in session to display in view
        $_SESSION['search_results'] = $results;
        header("Location: index.php?route=search_results");
        exit();
    }
}