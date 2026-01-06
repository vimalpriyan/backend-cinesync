<?php
// 1. ERROR REPORTING & HEADERS
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle Preflight Request (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 2. DB CONNECTION
require_once __DIR__ . '/../config/db_connect.php';

// 3. DEFINE ROUTES MAP
require_once __DIR__ . '/routes.php';

// 4. GET THE REQUESTED ACTION
$action = $_GET['action'] ?? '';

// 5. ROUTE THE REQUEST
if (array_key_exists($action, $routes)) {
    $route = $routes[$action];
    $controllerPath = __DIR__ . '/../controllers/' . $route['file'];
    
    // Check if Controller file exists
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        $controllerName = $route['class'];
        
        // Check if Class exists
        if (class_exists($controllerName)) {
            $controller = new $controllerName($conn);
            $methodName = $route['method'];
            
            // Check if Method exists
            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
            } else {
                echo json_encode(["status" => "error", "message" => "Method '$methodName' not found in $controllerName"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Class '$controllerName' not found"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Controller file not found: " . $route['file']]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid Action: $action. Route not defined."
    ]);
}
?>