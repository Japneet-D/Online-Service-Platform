<?php
namespace Controller;

require_once __DIR__ . '/../../config/database.php';

class DeliveryController {
public function process() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
        header("Location: index.php?route=delivery");
        exit();
    }

    // Required fields (distance and duration are now optional)
    $required = ['branch_id', 'delivery_date', 'delivery_time', 'destination_address', 'shipping_type'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Missing required field: $field");
        }
    }

    // Use provided distance/duration if available, otherwise set defaults
    $distance = isset($_POST['distance']) && $_POST['distance'] !== '' ? (float)$_POST['distance'] : 0;
    $duration = isset($_POST['duration']) && $_POST['duration'] !== '' ? (float)$_POST['duration'] : 0;

    $_SESSION['delivery_details'] = [
        'branch_id' => (int)$_POST['branch_id'],
        'delivery_date' => $_POST['delivery_date'],
        'delivery_time' => $_POST['delivery_time'],
        'distance' => $distance,
        'duration' => $duration,
        'destination_address' => htmlspecialchars($_POST['destination_address']),
        'shipping_type' => $_POST['shipping_type'],
        'shipping_cost' => ($_POST['shipping_type'] === 'express') ? 15.00 : 0.00
    ];

    header("Location: index.php?route=payment");
    exit();
}
}