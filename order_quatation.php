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
    // Handle POST request for updating order with PDF, model file, and JSON file

    if (isset($_POST['order_id'])) {
        $orderId = $_POST['order_id'];

        // Check if the PDF file, customize model file, and JSON file are uploaded successfully
        if (isset($_FILES['pdf_file']['tmp_name']) && isset($_FILES['customize_model_file']['tmp_name']) && isset($_FILES['customize_json_file']['tmp_name'])) {
            // Define upload directories
            $pdfUploadDir = 'order-quotations/';
            $customizeModelUploadDir = 'customized_models/';
            $customizeJsonUploadDir = 'customized_jsons/';

            // Move uploaded PDF file to the directory
            $pdfFile = $pdfUploadDir . basename($_FILES['pdf_file']['name']);
            // Move uploaded customize model file to the directory
            $customizeModelFile = $customizeModelUploadDir . basename($_FILES['customize_model_file']['name']);
            // Move uploaded JSON file to the directory
            $customizeJsonFile = $customizeJsonUploadDir . basename($_FILES['customize_json_file']['name']);

            if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $pdfFile) && move_uploaded_file($_FILES['customize_model_file']['tmp_name'], $customizeModelFile) && move_uploaded_file($_FILES['customize_json_file']['tmp_name'], $customizeJsonFile)) {
                // Update the order entry with the file paths
                $updateQuery = "UPDATE order_quotation SET pdf_file = ?, customized_model_file = ?, customized_json = ? WHERE id = ?";
                $stmt = mysqli_prepare($db, $updateQuery);
                mysqli_stmt_bind_param($stmt, 'sssi', $pdfFile, $customizeModelFile, $customizeJsonFile, $orderId);
                mysqli_stmt_execute($stmt);

                // Check if update was successful
                if (mysqli_stmt_affected_rows($stmt) > 0) {
                    http_response_code(200); // OK
                    echo json_encode(['status' => 200, 'message' => 'Order updated successfully']);

                    // Send email only if status is "email"
                    $query = "SELECT status, user_details FROM order_quotation WHERE id = ?";
                    $stmt = mysqli_prepare($db, $query);
                    mysqli_stmt_bind_param($stmt, 'i', $orderId);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $order = mysqli_fetch_assoc($result);

                    if ($order['status'] === 'email') {
                        // Your email sending code here
                        // Retrieve email content from the database
                        $emailQuery = "SELECT * FROM email_content";
                        $result = mysqli_query($db, $emailQuery);
                        $row = mysqli_fetch_assoc($result);

                        // Send email using PHPMailer
                        $mail = new PHPMailer(true);

                        try {
                            //Server settings
                            $mail->isSMTP();
                            $mail->Host = 'ded4482.inmotionhosting.com';
                            $mail->Port = 465;
                            $mail->SMTPAuth = true;
                            $mail->Username = 'ehuertas@newageenclosures.com';
                            $mail->Password = 'New@ge123';
                            $mail->SMTPSecure = 'ssl';
    
                            // Recipients
                            $mail->setFrom($row['sender_email'], $row['sender_name']);
                            $mail->addAddress($order['user_details'], 'Recipient Name');
    
                            // Attachments
                            $mail->addAttachment($pdfFile); // Add attachments
                            $mail->addAttachment($customizeModelFile); // Add model file as attachment
                            $mail->addAttachment($customizeJsonFile); // Add JSON file as attachment
    
                            // Content
                            $mail->isHTML(true);
                            $currentDate = date("Y-m-d");  // Set email format to HTML
                            $mail->Subject = $row['email_subject'] . " - " . $currentDate;
                            $body = $row['email_content'];
    
                            $mail->Body = $body;
    
                            $mail->send();
                            echo json_encode(array("message" => "Email sent successfully"));
                        } catch (Exception $e) {
                            echo json_encode(array("error" => "Failed to send email: " . $mail->ErrorInfo));
                        }
                    }
                }
            }
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Handle GET request for retrieving order quotations
    if (isset($_GET['id'])) {
        $orderId = $_GET['id'];
        
        // Fetch order details from the database
        $query = "SELECT * FROM order_quotation WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $orderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($order = mysqli_fetch_assoc($result)) {
            http_response_code(200); // OK
            echo json_encode(['status' => 200, 'order' => $order]);
        } else {
            http_response_code(404); // Not Found
            echo json_encode(['status' => 404, 'message' => 'Order not found']);
        }
    } else {
        // If no specific order ID is provided, fetch all orders
        $query = "SELECT * FROM order_quotation";
        $result = mysqli_query($db, $query);

        $orders = [];
        while ($order = mysqli_fetch_assoc($result)) {
            $orders[] = $order;
        }

        http_response_code(200); // OK
        echo json_encode(['status' => 200, 'orders' => $orders]);
    }
}