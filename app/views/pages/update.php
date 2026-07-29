<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../config/database.php';

if ($_SESSION['user_type'] !== 'admin') {
    header("Location: index.php?route=home");
    exit();
}

$tables = [
    'Users' => ['Name', 'Email', 'Address', 'Balance', 'User_Type'],
    'Item' => ['Item_name', 'Price', 'Department_Code'],
    'Branch' => ['Name', 'City', 'Latitude', 'Longitude'],
    'Truck' => ['License_Plate', 'Capacity', 'Availability'],
    'Trip' => ['Destination_Address', 'Distance', 'Estimated_Time'],
    'Orders' => ['Total_Price', 'Status'],
    'Payment' => ['Amount', 'Status'],
    'Reviews' => ['Rating', 'Review_Text']   
];

$message = '';
if (isset($_SESSION['admin_message'])) {
    $message = $_SESSION['admin_message'];
    unset($_SESSION['admin_message']);
}
?>

<div class="container">
    <h2 class="my-4">Update Records</h2>
    
    <?php if (!empty($message)): ?>
    <div class="alert alert-<?= $message['type'] ?>"><?= htmlspecialchars($message['text']) ?></div>
    <?php endif; ?>

    <form action="index.php?route=admin_update" method="POST">
        <div class="row g-3">
            <div class="col-md-3">
                <select name="table" class="form-select" id="tableSelect" required>
                    <?php foreach ($tables as $name => $columns): ?>
                    <option value="<?= $name ?>"><?= $name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="id" class="form-control" placeholder="ID" required>
            </div>
            <div class="col-md-3">
                <select name="column" class="form-select" id="columnSelect"></select>
            </div>
            <div class="col-md-3">
                <div id="valueInput"></div>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </div>
    </form>
</div>

<script>
const tableColumns = {
    'Users': ['Name', 'Email', 'Address', 'Balance', 'User_Type'],
    'Item': ['Item_name', 'Price', 'Department_Code'],
    'Branch': ['Name', 'City', 'Latitude', 'Longitude'],
    'Truck': ['License_Plate', 'Capacity', 'Availability'],
    'Trip': ['Destination_Address', 'Distance', 'Estimated_Time'],
    'Orders': ['Total_Price', 'Status'],
    'Payment': ['Amount', 'Status'],
    'Reviews': ['Rating', 'Review_Text']
};

document.getElementById('tableSelect').addEventListener('change', function() {
    const columns = tableColumns[this.value];
    let html = '';
    columns.forEach(col => {
        html += `<option value="${col}">${col}</option>`;
    });
    document.getElementById('columnSelect').innerHTML = html;
    updateValueInput();
});

document.getElementById('columnSelect').addEventListener('change', updateValueInput);

function updateValueInput() {
    const column = document.getElementById('columnSelect').value;
    let html = '';
    
    if (column === 'User_Type') {
        html = `<select name="value" class="form-select">
                  <option value="user">user</option>
                  <option value="admin">admin</option>
               </select>`;
    } else if (column === 'Availability') {
        html = `<select name="value" class="form-select">
                  <option value="available">available</option>
                  <option value="in_transit">in_transit</option>
                  <option value="maintenance">maintenance</option>
               </select>`;
    } else {
        let type = (column === 'Price' || column === 'Amount' || column === 'Balance') ? 'number' : 'text';
        html = `<input type="${type}" name="value" class="form-control" required
                ${(type === 'number') ? 'step="0.01"' : ''}>`;
    }
    
    document.getElementById('valueInput').innerHTML = html;
}

document.getElementById('tableSelect').dispatchEvent(new Event('change'));
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> ?>