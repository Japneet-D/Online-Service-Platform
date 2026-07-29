<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../config/database.php';

// Display any payment errors
if (isset($_SESSION['payment_error'])) {
    echo '<script>alert("'.htmlspecialchars($_SESSION['payment_error']).'");</script>';
    unset($_SESSION['payment_error']);
}

// Check valid context: user, cart, delivery details
if (!isset($_SESSION['user_id'], $_SESSION['cart'], $_SESSION['delivery_details']) || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Fetch delivery details and branch info
$delivery = $_SESSION['delivery_details'];
$stmt = $conn->prepare("SELECT Name FROM Branch WHERE Branch_Id = ?");
$stmt->bind_param("i", $delivery['branch_id']);
$stmt->execute();
$branch = $stmt->get_result()->fetch_assoc();

// Calculate total price from cart
$totalPrice = 0;
foreach ($_SESSION['cart'] as $item) {
    $stmt = $conn->prepare("SELECT Price FROM Item WHERE Item_Id = ?");
    $stmt->bind_param("i", $item['id']);
    $stmt->execute();
    $price = $stmt->get_result()->fetch_column();
    $totalPrice += $price * $item['quantity'];
}

// Add shipping cost
$shippingCost = $_SESSION['delivery_details']['shipping_cost'];
$totalPrice += $shippingCost;

// Store total for process_payment.php
$_SESSION['current_order'] = ['total_price' => $totalPrice];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Processing</title>
    <style>
        .invoice-box {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
        }
        .payment-form input {
            margin: 0.5rem 0;
            padding: 0.5rem;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="invoice-box">
            <h2 class="text-center mb-4">Order Invoice</h2>
            
            <div class="mb-4">
                <h4>Order Details</h4>
                <p><strong>Order Reference:</strong> Pending Payment Confirmation</p>
                <p><strong>Delivery Branch:</strong> <?= htmlspecialchars($branch['Name']) ?></p>
                <p><strong>Delivery Address:</strong> <?= htmlspecialchars($delivery['destination_address']) ?></p>
                <p><strong>Delivery Distance:</strong> <?= number_format($delivery['distance'] / 1000, 2) ?> km</p>
                <p><strong>Scheduled Delivery:</strong> <?= $delivery['delivery_date'] ?> at <?= $delivery['delivery_time'] ?></p>
                <div class="alert alert-info mt-3">
                    Your official Order ID will be generated after successful payment.
                </div>
            </div>

            <p><strong>Shipping Cost:</strong> $<?= number_format($shippingCost, 2) ?></p>
            <p class="h4">Total: <span class="total-price">$<?= number_format($totalPrice, 2) ?></span></p>

            <form id="payment-form" action="index.php?route=payment_process" method="POST">
                <h4 class="mb-3">Payment Information</h4>

                <div class="form-group">
                    <label>Payment Method</label>
                    <select class="form-control" name="payment_method" id="payment-method" required>
                        <option value="credit">Credit Card</option>
                        <option value="debit">Debit Card</option>
                        <option value="gift">Gift Card</option>
                        <option value="cash">Cash on Delivery</option>
                    </select>
                </div>

                <!-- Credit/Debit Card Fields -->
                <div id="card-fields">
                    <div class="form-group">
                        <label>Card Number</label>
                        <input type="text" name="card_number" class="form-control" pattern="[0-9]{16}" placeholder="4111111111111111">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Expiration Date</label>
                            <input type="month" name="exp_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>CVV</label>
                            <input type="text" name="cvv" class="form-control" pattern="[0-9]{3}" placeholder="123">
                        </div>
                    </div>
                </div>

                <!-- Gift Card Fields (no required attributes by default) -->
                <div id="gift-fields" style="display:none;">
                    <div class="form-group">
                        <label>Gift Card Number</label>
                        <input type="text" name="gift_number" class="form-control" pattern="[0-9]{16}" placeholder="1234123412341234">
                    </div>
                    <div class="form-group">
                        <label>PIN</label>
                        <input type="text" name="gift_pin" class="form-control" pattern="[0-9]{4}" placeholder="1234">
                    </div>
                </div>

                <!-- Cash on Delivery Fields -->
                <div id="cash-fields" style="display:none;">
                    <div class="alert alert-info">
                        No payment information required for Cash on Delivery
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-lg mt-4 w-100">
                    Confirm Payment - $<?= number_format($totalPrice, 2) ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        function setRequiredFields() {
            const method = document.getElementById('payment-method').value;
            const cardFields = document.getElementById('card-fields');
            const giftFields = document.getElementById('gift-fields');
            const cashFields = document.getElementById('cash-fields');
            
            // First, remove required from all inputs inside all sections
            cardFields.querySelectorAll('input').forEach(field => field.required = false);
            giftFields.querySelectorAll('input').forEach(field => field.required = false);
            
            // Hide all sections
            cardFields.style.display = 'none';
            giftFields.style.display = 'none';
            cashFields.style.display = 'none';
            
            // Show and set required based on selection
            if (method === 'credit' || method === 'debit') {
                cardFields.style.display = 'block';
                cardFields.querySelectorAll('input').forEach(field => field.required = true);
            } else if (method === 'gift') {
                giftFields.style.display = 'block';
                giftFields.querySelectorAll('input').forEach(field => field.required = true);
            } else if (method === 'cash') {
                cashFields.style.display = 'block';
            }
        }
        
        // Run on page load and on change
        document.getElementById('payment-method').addEventListener('change', setRequiredFields);
        setRequiredFields();
    </script>
</body>
</html>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>