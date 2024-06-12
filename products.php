<?php
// Establish MySQL connection
$host = '192.249.120.151';
$user = 'newage32_3d';
$password = '$Newage2240!!!';
$database = 'newage32_nae';

// Create connection
$db = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}


// Allow from any origin
header("Access-Control-Allow-Origin: *");

// Allow credentials if needed
header('Access-Control-Allow-Credentials: true');

// Set cache duration for preflight requests (OPTIONS)
header('Access-Control-Max-Age: 86400');    // cache for 1 day

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

// Define API endpoint
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Extract the productId parameter from the request
    $productId = $_GET['productId'] ?? '';

    if (!$productId || $productId == "") {
        http_response_code(400); // Bad Request
        echo json_encode(['error' => 'Product Id is required']);
    } else {
        // Query to get the full product object based on product ID
        $getProductQuery = 'SELECT * FROM products WHERE id = ?';

        // Prepare statement
        $stmt = mysqli_prepare($db, $getProductQuery);

        // Bind parameters
        mysqli_stmt_bind_param($stmt, 'i', $productId);

        // Execute statement
        mysqli_stmt_execute($stmt);

        // Get result
        $result = mysqli_stmt_get_result($stmt);

        // Check if product exists
        if (mysqli_num_rows($result) > 0) {
            $product = mysqli_fetch_assoc($result);
            http_response_code(200);
            echo json_encode(['status' => 200, 'product' => $product]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['error' => 'Product not found']);
        }
    }
}

// Close connection
mysqli_close($db);
?>