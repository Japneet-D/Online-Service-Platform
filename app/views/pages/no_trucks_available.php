<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../config/database.php';

// Check if error message exists
if (!isset($_SESSION['truck_error'])) {
    header("Location: index.php?route=home");
    exit();
}

$errorDetails = $_SESSION['truck_error'];
unset($_SESSION['truck_error']);
?>

<div class="container text-center mt-5">
    <div class="alert alert-danger">
        <h2>🚚 Truck Availability Issue</h2>
        <p class="lead mt-3"><?= nl2br(htmlspecialchars($errorDetails['message'])) ?></p>
        
        <?php if (!empty($errorDetails['next_available'])): ?>
        <div class="mt-4">
            <p class="h5">Suggested Availability:</p>
            <p class="h4 text-primary">
                <?= date('F j, Y \a\t g:i A', strtotime($errorDetails['next_available'])) ?>
            </p>
        </div>
        <?php endif; ?>

        <div class="mt-4">
            <p>Please try ordering again after the suggested time.</p>
            <a href="index.php?route=home" class="btn btn-primary btn-lg">
                Return to Home Page
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>