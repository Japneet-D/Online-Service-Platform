<?php
namespace Controller;

require_once __DIR__ . '/../../config/database.php';

class CartController {
    private function ensureSession() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
            exit();
        }
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    }

    public function add() {
        $this->ensureSession();
        $itemId = $_POST['item_id'];
        $quantity = $_POST['quantity'];

        $found = false;
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $itemId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $_SESSION['cart'][] = ['id' => $itemId, 'quantity' => $quantity];
        }
        echo json_encode(['status' => 'success']);
    }

    public function update() {
        $this->ensureSession();
        $itemId = $_POST['item_id'];
        $quantity = (int)$_POST['quantity'];

        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $itemId) {
                $item['quantity'] = $quantity;
                break;
            }
        }
        echo json_encode(['status' => 'success']);
    }

    public function remove() {
        $this->ensureSession();
        $itemId = (int)$_POST['item_id'];

        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['id'] == $itemId) {
                unset($_SESSION['cart'][$index]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                echo json_encode(['status' => 'success']);
                return;
            }
        }
        echo json_encode(['status' => 'error', 'message' => 'Item not found']);
    }

    public function content() {
        global $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
            echo '<p class="text-muted">No items in cart</p>';
            return;
        }

        foreach ($_SESSION['cart'] as $item) {
            $stmt = $conn->prepare("SELECT Item_name FROM Item WHERE Item_Id = ?");
            $stmt->bind_param("i", $item['id']);
            $stmt->execute();
            $itemData = $stmt->get_result()->fetch_assoc();
            if ($itemData) {
                echo '<div class="cart-item mb-2">' . htmlspecialchars($itemData['Item_name']) . ' (Qty: ' . $item['quantity'] . ')</div>';
            }
        }
    }
}