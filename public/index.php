<?php
// public/index.php

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('VIEWS_PATH', APP_PATH . '/views/pages');
define('LAYOUTS_PATH', APP_PATH . '/views/layouts');

// Autoload controllers (simple mapping)
spl_autoload_register(function ($class) {
    $prefix = 'Controller\\';
    $base_dir = APP_PATH . '/controllers/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

// Load database configuration (needed by some controllers)
require CONFIG_PATH . '/database.php';

// Simple routing
$route = isset($_GET['route']) ? $_GET['route'] : 'home';

switch ($route) {
    case 'home':
        require VIEWS_PATH . '/index.php';
        break;
    case 'about':
        require VIEWS_PATH . '/about.php';
        break;
    case 'signin':
        require VIEWS_PATH . '/signin.php';
        break;
    case 'signup':
        require VIEWS_PATH . '/signup.php';
        break;
    case 'shopping':
        require VIEWS_PATH . '/shopping.php';
        break;
    case 'cart':
        require VIEWS_PATH . '/cart.php';
        break;
    case 'delivery':
        require VIEWS_PATH . '/delivery.php';
        break;
    case 'payment':
        require VIEWS_PATH . '/payment.php';
        break;
    case 'payment_success':
        require VIEWS_PATH . '/payment_success.php';
        break;
    case 'review':
        require VIEWS_PATH . '/review.php';
        break;
    case 'search_results':
        require VIEWS_PATH . '/search_results.php';
        break;
    case 'no_trucks':
        require VIEWS_PATH . '/no_trucks_available.php';
        break;
    case 'insert':
        require VIEWS_PATH . '/insert.php';
        break;
    case 'delete':
        require VIEWS_PATH . '/delete.php';
        break;
    case 'select':
        require VIEWS_PATH . '/select.php';
        break;
    case 'update':
        require VIEWS_PATH . '/update.php';
        break;
    // Controller actions (API-like)
    case 'auth_login':
        $ctrl = new Controller\AuthController();
        $ctrl->login();
        break;
    case 'auth_signup':
        $ctrl = new Controller\AuthController();
        $ctrl->signup();
        break;
    case 'auth_logout':
        $ctrl = new Controller\AuthController();
        $ctrl->logout();
        break;
    case 'cart_add':
        $ctrl = new Controller\CartController();
        $ctrl->add();
        break;
    case 'cart_update':
        $ctrl = new Controller\CartController();
        $ctrl->update();
        break;
    case 'cart_remove':
        $ctrl = new Controller\CartController();
        $ctrl->remove();
        break;
    case 'cart_content':
        $ctrl = new Controller\CartController();
        $ctrl->content();
        break;
    case 'delivery_process':
        $ctrl = new Controller\DeliveryController();
        $ctrl->process();
        break;
    case 'payment_process':
        $ctrl = new Controller\PaymentController();
        $ctrl->process();
        break;
    case 'review_submit':
        $ctrl = new Controller\ReviewController();
        $ctrl->submit();
        break;
    case 'search':
        $ctrl = new Controller\SearchController();
        $ctrl->results();
        break;
    case 'admin_insert':
        $ctrl = new Controller\AdminController();
        $ctrl->insert();
        break;
    case 'admin_delete':
        $ctrl = new Controller\AdminController();
        $ctrl->delete();
        break;
    case 'admin_select':
        $ctrl = new Controller\AdminController();
        $ctrl->select();
        break;
    case 'admin_update':
        $ctrl = new Controller\AdminController();
        $ctrl->update();
        break;
    default:
        http_response_code(404);
        echo "404 - Page not found";
}