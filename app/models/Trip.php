<?php
namespace Model;

require_once __DIR__ . '/../../config/database.php';

class Trip {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    public function create($branchId, $destinationAddress, $distance, $estimatedTime, $truckId, $deliveryDate, $deliveryTime, $deliveryType, $deliveryCost) {
        $stmt = $this->conn->prepare("
            INSERT INTO Trip (Branch_Id, Destination_Address, Distance, Estimated_Time, Truck_Id, Delivery_Date, Delivery_Time, Delivery_Type, Delivery_Cost)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isddissd", $branchId, $destinationAddress, $distance, $estimatedTime, $truckId, $deliveryDate, $deliveryTime, $deliveryType, $deliveryCost);
        $stmt->execute();
        return $this->conn->insert_id;
    }

    public function getAvailableTruck($deliveryDate, $deliveryTime) {
        $stmt = $this->conn->prepare("
            SELECT t.Truck_Id 
            FROM Truck t
            WHERE t.Truck_Id NOT IN (
                SELECT tr.Truck_Id 
                FROM Trip tr 
                WHERE tr.Truck_Id = t.Truck_Id 
                AND (
                    (tr.Delivery_Date = ? AND tr.Delivery_Time BETWEEN ? - INTERVAL 1 HOUR AND ? + INTERVAL 1 HOUR)
                    OR CONCAT(tr.Delivery_Date, ' ', tr.Delivery_Time) > NOW()
                )
            )
            AND (t.Availability = 'available' OR t.Availability = 'in_transit')
            ORDER BY RAND()
            LIMIT 1
        ");
        $stmt->bind_param("sss", $deliveryDate, $deliveryTime, $deliveryTime);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}