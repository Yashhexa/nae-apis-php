<?php

// Include PHPMailer autoload.php file
require './phpmailer/PHPMailer.php';
require './phpmailer/SMTP.php';
require './phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create a new PHPMailer instance
$mail = new PHPMailer;

// SMTP configuration
$mail->isSMTP();
$mail->Host = 'ded4482.inmotionhosting.com';
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = 'ehuertas@newageenclosures.com';
$mail->Password = 'New@ge123';
$mail->SMTPSecure = 'ssl';

// Sender and recipient details
$mail->setFrom('ehuertas@newageenclosures.com', 'Sender Name');
$mail->addAddress('yash@hexacoder.com', 'Yash Shah');

// Email subject and body
$mail->Subject = 'Test Email via PHPMailer';
$mail->Body = 'This is a test email sent via PHPMailer using SMTP authentication.';

// Send the email
if ($mail->send()) {
    echo 'Email sent successfully.';
} else {
    echo 'Error sending email: ' . $mail->ErrorInfo;
}
