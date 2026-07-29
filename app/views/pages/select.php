<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../config/database.php';

if ($_SESSION['user_type'] !== 'admin') {
    header("Location: index.php?route=home");
    exit();
}

$tables = [
    'Users' => ['Name', 'Email', 'User_Type'],
    'Item' => ['Item_name', 'Department_Code'],
    'Branch' => ['Name', 'City'],
    'Truck' => ['License_Plate', 'Availability'],
    'Trip' => ['Destination_Address'],
    'Orders' => ['Status'],
    'Payment' => ['Status'],
    'Reviews' => ['Rating', 'Review_Text']  
];

$results = [];
$selectedTable = '';
if (isset($_SESSION['admin_select_results'])) {
    $results = $_SESSION['admin_select_results'];
    $selectedTable = $_SESSION['admin_select_table'];
    unset($_SESSION['admin_select_results'], $_SESSION['admin_select_table']);
}

$message = '';
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']);
}
?>

<div class="container">
    <h2 class="my-4">Search Records</h2>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?= $message['type'] ?>"><?= htmlspecialchars($message['text']) ?></div>
    <?php endif; ?>

    <form action="index.php?route=admin_select" method="POST">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <select name="table" class="form-select">
                    <?php foreach ($tables as $name => $columns): ?>
                    <option value="<?= $name ?>" <?= ($name == $selectedTable) ? 'selected' : '' ?>><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-7">
                <input type="text" name="search_term" class="form-control" placeholder="Search term">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </div>
    </form>

    <?php if (!empty($results)): ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <?php foreach ($results[0] as $col => $val): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $row): ?>
                        <tr>
                            <?php foreach ($row as $val): ?>
                            <td><?= htmlspecialchars($val) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <div class="alert alert-info">No records found</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> ?>