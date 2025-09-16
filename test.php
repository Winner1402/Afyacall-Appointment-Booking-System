<?php
require 'vendor/autoload.php'; // Composer autoload
require 'config/email_config.php'; // your SMTP constants

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE;
    $mail->Port = SMTP_PORT;

    $mail->SMTPDebug = 2;           // Show verbose debug info
    $mail->Debugoutput = 'html';    // Show debug in browser

    // Recipients
    $mail->setFrom(FROM_EMAIL, FROM_NAME);
    $mail->addAddress('winnermlay14@gmail.com', 'Winner Mlay'); // test recipient
    $mail->addReplyTo(REPLY_TO_EMAIL, REPLY_TO_NAME);

    // Content
    $mail->isHTML(true);
    $mail->Subject = "Test Email from AfyaCall";
    $mail->Body    = "<p>Hello Winner,</p><p>This is a test email from AfyaCall Health Services.</p>";
    $mail->AltBody = "Hello Winner, This is a test email from AfyaCall Health Services.";

    $mail->send();
    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}
?>
