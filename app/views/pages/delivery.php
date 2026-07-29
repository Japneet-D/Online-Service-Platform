<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Fetch user address
$stmt = $conn->prepare("SELECT Address FROM Users WHERE User_Id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$user_address = $user['Address'];

// Fetch branches
$branches = $conn->query("SELECT * FROM Branch");

// Store cart items in session for payment
if (!isset($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Google Maps API Key
$google_maps_api_key = "AIzaSyAF2BPHOPRmQ0p64bPVYuquYmqtsHzmoOg"; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delivery Planning</title>
    <style>
        .branch-card {
            cursor: pointer;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px;
            transition: all 0.3s;
        }
        .branch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        #map {
            height: 500px;
            width: 100%;
            margin-top: 20px;
        }
        .selected-branch {
            border-color: #007bff !important;
            background-color: #f8f9fa;
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?= $google_maps_api_key ?>&libraries=places,directions"></script>
</head>
<body>
    <div class="container">
        <h2 class="my-4">Delivery Planning</h2>
        
        <!-- Branch Selection -->
        <div class="row" id="branch-selection">
            <h4>Select Distribution Branch</h4>
            <?php while($branch = $branches->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="branch-card" 
                     data-branch-id="<?= $branch['Branch_Id'] ?>"
                     data-lat="<?= $branch['Latitude'] ?>"
                     data-lng="<?= $branch['Longitude'] ?>">
                    <h5><?= htmlspecialchars($branch['Name']) ?></h5>
                    <p><?= htmlspecialchars($branch['Address']) ?></p>
                    <p><?= htmlspecialchars($branch['City']) ?>, <?= htmlspecialchars($branch['Province']) ?></p>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

        <!-- Delivery Details Form -->
        <form id="delivery-form" action="index.php?route=delivery_process" method="POST">
            <input type="hidden" name="branch_id" id="branch-id" required>
            <input type="hidden" name="distance" id="distance">
            <input type="hidden" name="duration" id="duration">
            <input type="hidden" name="destination_address" value="<?= htmlspecialchars($user_address) ?>">
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <h4>Delivery Information</h4>
                    
                    <!-- Shipping Type Selection -->
                    <div class="form-group">
                        <label>Shipping Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="shipping_type" 
                                   id="standard" value="standard" checked>
                            <label class="form-check-label" for="standard">
                                Standard Shipping (Free, 5+ business days)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="shipping_type" 
                                   id="express" value="express">
                            <label class="form-check-label" for="express">
                                Express Shipping ($15 - Next business day)
                            </label>
                        </div>
                    </div>

                    <!-- Delivery Date and Time -->
                    <div class="form-group">
                        <label>Preferred Delivery Date</label>
                        <input type="date" class="form-control" name="delivery_date" 
                               id="delivery-date" required>
                    </div>
                    <div class="form-group">
                        <label>Preferred Delivery Time</label>
                        <input type="time" class="form-control" name="delivery_time" 
                               id="delivery-time" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div id="map"></div>
                    <div id="route-info" class="mt-3"></div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg mt-4">
                Confirm Delivery Details
            </button>
        </form>
    </div>

    <script>
    let map, directionsService, directionsRenderer;
    let selectedBranch = null;
    let destinationCoords = null;

    function initMap() {
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 10,
            center: { lat: 43.6532, lng: -79.3832 } // Default to Toronto
        });
        
        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer();
        directionsRenderer.setMap(map);
    }

    // Initialize map
    initMap();

    // Branch selection handler
    document.querySelectorAll('.branch-card').forEach(card => {
        card.addEventListener('click', function() {
            // Remove previous selection
            document.querySelectorAll('.branch-card').forEach(c => 
                c.classList.remove('selected-branch'));
            
            this.classList.add('selected-branch');
            selectedBranch = {
                id: this.dataset.branchId,
                lat: parseFloat(this.dataset.lat),
                lng: parseFloat(this.dataset.lng)
            };
            
            document.getElementById('branch-id').value = selectedBranch.id;
            calculateRoute();
        });
    });

    function calculateRoute() {
        if (!selectedBranch) return;

        // Convert user address to coordinates
        new google.maps.Geocoder().geocode({ address: "<?= $user_address ?>" }, 
        (results, status) => {
            if (status === 'OK') {
                destinationCoords = results[0].geometry.location;
                
                const request = {
                    origin: { lat: selectedBranch.lat, lng: selectedBranch.lng },
                    destination: destinationCoords,
                    travelMode: 'DRIVING'
                };

                directionsService.route(request, (result, status) => {
                    if (status === 'OK') {
                        directionsRenderer.setDirections(result);
                        
                        // Update route info
                        const route = result.routes[0].legs[0];
                        document.getElementById('route-info').innerHTML = `
                            <strong>Optimal Route Details:</strong><br>
                            Distance: ${route.distance.text}<br>
                            Estimated Duration: ${route.duration.text}
                        `;
                        
                        // Store values in form
                        document.getElementById('distance').value = 
                            route.distance.value; // meters
                        document.getElementById('duration').value = 
                            route.duration.value; // seconds
                    }
                });
            }
        });
    }

    // Add shipping type handling
    const shippingType = document.querySelectorAll('input[name="shipping_type"]');
    const dateField = document.getElementById('delivery-date');
    const timeField = document.getElementById('delivery-time');

    function updateShippingFields() {
        const isExpress = document.querySelector('input[name="shipping_type"]:checked').value === 'express';
        
        if(isExpress) {
            // Set date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            dateField.value = tomorrow.toISOString().split('T')[0];
            dateField.min = dateField.value;
            dateField.max = dateField.value;
            dateField.readOnly = true;
            
            // Allow any time
            timeField.removeAttribute('min');
            timeField.removeAttribute('max');
        } else {
            // Standard shipping - 5 days minimum
            const minDate = new Date();
            minDate.setDate(minDate.getDate() + 5);
            dateField.value = '';
            dateField.min = minDate.toISOString().split('T')[0];
            dateField.max = '';
            dateField.readOnly = false;
            
            // Restrict time
            timeField.min = '09:00';
            timeField.max = '18:00';
        }
    }

    // Add event listeners
    shippingType.forEach(radio => {
        radio.addEventListener('change', updateShippingFields);
    });

    // Initial setup
    updateShippingFields();
    </script>
</body>
</html>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>