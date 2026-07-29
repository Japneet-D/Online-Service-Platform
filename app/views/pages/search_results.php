<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../config/database.php';

// Get search results from session (set by SearchController)
$results = [];
if (isset($_SESSION['search_results'])) {
    $results = $_SESSION['search_results'];
    unset($_SESSION['search_results']);
} else {
    // Fallback: if no session, redirect to home
    header("Location: index.php?route=home");
    exit();
}
?>

<div class="container">
    <h2 class="my-4">Order Search Results</h2>
    
    <?php if (!empty($results)): ?>
        <?php foreach ($results as $order): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5>Order #<?= htmlspecialchars($order['Order_Id']) ?></h5>
                            <p class="mb-1">
                                <strong>User:</strong> 
                                <?= htmlspecialchars($order['user_name']) ?> 
                                (ID: <?= $order['User_Id'] ?>)
                            </p>
                            <p class="mb-1">
                                <strong>Total:</strong> 
                                $<?= number_format($order['Total_Price'], 2) ?>
                            </p>
                            <p class="mb-1">
                                <strong>Status:</strong> 
                                <?= htmlspecialchars($order['Status']) ?>
                            </p>
                            <?php if (!empty($order['Transaction_Id'])): ?>
                                <p class="mb-1">
                                    <strong>Transaction ID:</strong> 
                                    <?= htmlspecialchars($order['Transaction_Id']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 border-start">
                            <p class="mb-1">
                                <strong>Order Date:</strong> 
                                <?= date('M j, Y g:i A', strtotime($order['Order_Date'])) ?>
                            </p>
                            <?php if (!empty($order['Destination_Address'])): ?>
                                <p class="mb-1">
                                    <strong>Destination:</strong> 
                                    <?= htmlspecialchars($order['Destination_Address']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info">No orders found matching your criteria</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>