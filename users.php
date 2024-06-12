<?php
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

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if the user ID is provided in the request
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        
        // Query to retrieve the user's email based on the provided user ID
        $query = "SELECT * FROM users WHERE id = $user_id";
        $result = mysqli_query($db, $query);

        if ($result) {
            // Check if any rows are returned
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                // Return the user's email in JSON format
                echo json_encode(array($row));
            } else {
                // If no matching user is found, return an error message
                http_response_code(404);
                echo json_encode(array("message" => "User not found"));
            }
        } else {
            // If the query fails, return an error message
            http_response_code(500);
            echo json_encode(array("message" => "Internal server error"));
        }
    } else {
        // If the user ID is not provided, return an error message
        http_response_code(400);
        echo json_encode(array("message" => "User ID is required"));
    }
} else {
    // If the request method is not GET, return an error message
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
}
?>
