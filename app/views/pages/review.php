<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?route=signin");
    exit();
}

// Display messages from session (set by ReviewController)
$message = '';
if (isset($_SESSION['review_message'])) {
    $message = $_SESSION['review_message'];
    unset($_SESSION['review_message']);
}

// Get unreviewed items from all orders
$stmt = $conn->prepare("
    SELECT o.Order_Id, oi.Item_Id, i.Item_name 
    FROM Orders o
    JOIN Order_Items oi ON o.Order_Id = oi.Order_Id
    JOIN Item i ON oi.Item_Id = i.Item_Id
    LEFT JOIN Reviews r ON o.Order_Id = r.Order_Id AND oi.Item_Id = r.Item_Id
    WHERE o.User_Id = ? 
    AND r.Review_Id IS NULL
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <?php if ($message): ?>
    <div class="alert alert-<?= strpos($message, 'success') !== false ? 'success' : 'danger' ?>">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <h2>Leave a Review</h2>
    <?php foreach ($items as $item): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5><?= htmlspecialchars($item['Item_name']) ?></h5>
            <form action="index.php?route=review_submit" method="POST">
                <input type="hidden" name="order_id" value="<?= $item['Order_Id'] ?>">
                <input type="hidden" name="item_id" value="<?= $item['Item_Id'] ?>">
                <div class="form-group">
                    <label>Rating (1-5)</label>
                    <input type="number" name="rating" min="1" max="5" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Review</label>
                    <textarea name="review_text" class="form-control" maxlength="255"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit Review</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> ?>