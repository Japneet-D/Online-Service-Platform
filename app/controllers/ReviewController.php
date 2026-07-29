<?php
namespace Controller;

require_once __DIR__ . '/../../config/database.php';

class ReviewController {
    public function submit() {
        global $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?route=review");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $orderId = $_POST['order_id'];
        $itemId = $_POST['item_id'];
        $rating = $_POST['rating'];
        $reviewText = $_POST['review_text'];

        $stmt = $conn->prepare("INSERT INTO Reviews (User_Id, Order_Id, Item_Id, Rating, Review_Text) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiis", $userId, $orderId, $itemId, $rating, $reviewText);
        if ($stmt->execute()) {
            $_SESSION['review_message'] = "Review submitted successfully!";
        } else {
            $_SESSION['review_message'] = "Error submitting review. Please try again.";
        }
        header("Location: index.php?route=review");
        exit();
    }
}