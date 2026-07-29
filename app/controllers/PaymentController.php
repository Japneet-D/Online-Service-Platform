<?php
namespace Controller;

require_once __DIR__ . '/../../config/database.php';

class PaymentController {
    public function process() {
        global $conn;
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['current_order']) || !isset($_SESSION['delivery_details']) || empty($_SESSION['cart'])) {
            header("Location: index.php?route=cart");
            exit();
        }

        $deliveryDetails = $_SESSION['delivery_details'];
        $requiredKeys = ['branch_id', 'delivery_date', 'delivery_time', 'distance', 'duration', 'destination_address', 'shipping_type', 'shipping_cost'];
        foreach ($requiredKeys as $key) {
            if (!isset($deliveryDetails[$key])) die("Invalid delivery details structure");
        }

        $deliveryDateTime = $deliveryDetails['delivery_date'] . ' ' . $deliveryDetails['delivery_time'];

        try {
            $conn->begin_transaction();

            // Truck availability check
            $truckStmt = $conn->prepare("
                SELECT t.Truck_Id FROM Truck t
                WHERE t.Truck_Id NOT IN (
                    SELECT tr.Truck_Id FROM Trip tr
                    WHERE tr.Truck_Id = t.Truck_Id
                    AND (
                        (tr.Delivery_Date = ? AND tr.Delivery_Time BETWEEN ? - INTERVAL 1 HOUR AND ? + INTERVAL 1 HOUR)
                        OR CONCAT(tr.Delivery_Date, ' ', tr.Delivery_Time) > NOW()
                    )
                )
                AND (t.Availability = 'available' OR t.Availability = 'in_transit')
                ORDER BY RAND() LIMIT 1
            ");
            $truckStmt->bind_param("sss", $deliveryDetails['delivery_date'], $deliveryDetails['delivery_time'], $deliveryDetails['delivery_time']);
            $truckStmt->execute();
            $truck = $truckStmt->get_result()->fetch_assoc();

            if (!$truck) {
                $conn->rollback();
                $_SESSION['truck_error'] = ['message' => 'No available trucks for your selected time.', 'next_available' => null];
                header("Location: index.php?route=no_trucks");
                exit();
            }

            $distanceKm = $deliveryDetails['distance'] / 1000;
            $durationHours = $deliveryDetails['duration'] / 3600;

            // Insert Trip
            $tripStmt = $conn->prepare("INSERT INTO Trip (Branch_Id, Destination_Address, Distance, Estimated_Time, Truck_Id, Delivery_Date, Delivery_Time, Delivery_Type, Delivery_Cost) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $deliveryType = $deliveryDetails['shipping_type'];
            $shippingCost = $deliveryDetails['shipping_cost'];
            $tripStmt->bind_param("isddisssd", $deliveryDetails['branch_id'], $deliveryDetails['destination_address'], $distanceKm, $durationHours, $truck['Truck_Id'], $deliveryDetails['delivery_date'], $deliveryDetails['delivery_time'], $deliveryType, $shippingCost);
            $tripStmt->execute();
            $tripId = $conn->insert_id;

            // Insert Order
            $orderTotal = $_SESSION['current_order']['total_price'] + $shippingCost;
            $orderStmt = $conn->prepare("INSERT INTO Orders (User_Id, Trip_Id, Total_Price, Status) VALUES (?, ?, ?, 'processing')");
            $orderStmt->bind_param("iid", $_SESSION['user_id'], $tripId, $orderTotal);
            $orderStmt->execute();
            $orderId = $conn->insert_id;

            // Order Items
            foreach ($_SESSION['cart'] as $item) {
                $stmt = $conn->prepare("INSERT INTO Order_Items (Order_Id, Item_Id, Quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $orderId, $item['id'], $item['quantity']);
                $stmt->execute();
            }

            // Payment
            $paymentMethod = $_POST['payment_method'];
            $transactionId = 'TX-' . uniqid();
            $paymentStmt = $conn->prepare("INSERT INTO Payment (Order_Id, Amount, Payment_Method, Transaction_Id, Status) VALUES (?, ?, ?, ?, 'completed')");
            $paymentStmt->bind_param("idss", $orderId, $_SESSION['current_order']['total_price'], $paymentMethod, $transactionId);
            $paymentStmt->execute();

            // Update Truck
            $updateTruck = $conn->prepare("UPDATE Truck SET Availability = 'in_transit' WHERE Truck_Id = ?");
            $updateTruck->bind_param("i", $truck['Truck_Id']);
            $updateTruck->execute();

            $conn->commit();

            unset($_SESSION['cart'], $_SESSION['current_order'], $_SESSION['delivery_details']);
            $_SESSION['payment_success'] = [
                'order_id' => $orderId,
                'amount' => (float)$orderTotal,
                'transaction_id' => $transactionId,
                'delivery_date' => $deliveryDetails['delivery_date'],
                'delivery_time' => $deliveryDetails['delivery_time'],
                'shipping_type' => $deliveryDetails['shipping_type']
            ];
            header("Location: index.php?route=payment_success");
            exit();
        } catch (\Exception $e) {
            $conn->rollback();
            $_SESSION['payment_error'] = "An error occurred: " . $e->getMessage();
            header("Location: index.php?route=payment");
            exit();
        }
    }
}