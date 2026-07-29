<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../config/database.php';

if ($_SESSION['user_type'] !== 'admin') {
    header("Location: index.php?route=home");
    exit();
}

$tables = [
    'Users' => 'User_Id',
    'Item' => 'Item_Id',
    'Branch' => 'Branch_Id',
    'Truck' => 'Truck_Id',
    'Trip' => 'Trip_Id',
    'Orders' => 'Order_Id',
    'Payment' => 'Payment_Id',
    'Reviews' => 'Review_Id'   
];

$message = '';
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']);
}
?>

<div class="container">
    <h2 class="my-4">Delete Records</h2>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?= $message['type'] ?>"><?= htmlspecialchars($message['text']) ?></div>
    <?php endif; ?>

    <form action="index.php?route=admin_delete" method="POST">
        <div class="row g-3">
            <div class="col-md-4">
                <select name="table" class="form-select" required>
                    <?php foreach ($tables as $name => $pk): ?>
                    <option value="<?= $name ?>"><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" name="id" class="form-control" placeholder="ID" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> ?>