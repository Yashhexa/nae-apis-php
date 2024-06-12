<?php

require './phpmailer/PHPMailer.php';
require './phpmailer/SMTP.php';
require './phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST request for submitting order quotation data

    // Extract other data from the request
    $user_details = $_POST['user_details'] ?? '';
    $status = $_POST['status'] ?? '';
    $user_type = $_POST['user_type'] ?? '';
    $totalPrice = $_POST['total_price'] ?? '';
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;

    // Example of inserting data into the database without file paths
    $insertQuery = "INSERT INTO order_quotation (user_details, status, total_price, user_type, user_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $insertQuery);
    mysqli_stmt_bind_param($stmt, 'ssssi', $user_details, $status, $totalPrice, $user_type, $user_id);
    mysqli_stmt_execute($stmt);

    // Check if insertion was successful
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        $orderId = mysqli_insert_id($db); // Get the ID of the newly created order
        http_response_code(201); // Created
        echo json_encode(['status' => 201, 'message' => 'Order quotation submitted successfully', 'order_id' => $orderId]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['error' => 'Failed to submit order quotation']);
    }
}

// Close connection
mysqli_close($db);
?>
