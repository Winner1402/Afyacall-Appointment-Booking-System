<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendAppointmentConfirmationEmail($patientEmail, $patientName, $doctorName, $specialty, $appointmentDateTime, $appointmentId) {
    $formattedDate = date('F j, Y', strtotime($appointmentDateTime));
    $formattedTime = date('h:i A', strtotime($appointmentDateTime));
    
    $subject = "Appointment Confirmation - AfyaCall Health";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
            .footer { background: #333; color: white; padding: 10px; text-align: center; }
            .appointment-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>AfyaCall Health Appointment Confirmation</h2>
            </div>
            <div class='content'>
                <p>Dear $patientName,</p>
                <p>Your appointment has been successfully booked. Here are your appointment details:</p>
                
                <div class='appointment-details'>
                    <h3>Appointment Details</h3>
                    <p><strong>Appointment ID:</strong> #$appointmentId</p>
                    <p><strong>Doctor:</strong> Dr. $doctorName</p>
                    <p><strong>Specialty:</strong> $specialty</p>
                    <p><strong>Date:</strong> $formattedDate</p>
                    <p><strong>Time:</strong> $formattedTime</p>
                </div>
                
                <p><strong>Important Reminders:</strong></p>
                <ul>
                    <li>Please arrive 15 minutes before your appointment time</li>
                    <li>Bring your ID and insurance card (if applicable)</li>
                    <li>You can cancel or reschedule up to 24 hours before your appointment</li>
                </ul>
                
                <p>If you need to cancel or reschedule, please log in to your AfyaCall account.</p>
            </div>
            <div class='footer'>
                <p>Thank you for choosing AfyaCall Health Services</p>
                <p>Contact us: support@afyacall.com | +255-744-432654</p>
            </div>
        </div>
    </body>
    </html>";

    return sendEmail($patientEmail, $patientName, $subject, $body);
}

function sendRescheduleConfirmationEmail($patientEmail, $patientName, $doctorName, $oldDateTime, $newDateTime) {
    $oldFormattedDate = date('F j, Y', strtotime($oldDateTime));
    $oldFormattedTime = date('h:i A', strtotime($oldDateTime));
    $newFormattedDate = date('F j, Y', strtotime($newDateTime));
    $newFormattedTime = date('h:i A', strtotime($newDateTime));
    
    $subject = "Appointment Rescheduled - AfyaCall Health";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #FFA500; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
            .footer { background: #333; color: white; padding: 10px; text-align: center; }
            .appointment-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .change-details { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Appointment Rescheduled</h2>
            </div>
            <div class='content'>
                <p>Dear $patientName,</p>
                <p>Your appointment with Dr. $doctorName has been successfully rescheduled.</p>
                
                <div class='change-details'>
                    <h3>Reschedule Summary</h3>
                    <p><strong>From:</strong> $oldFormattedDate at $oldFormattedTime</p>
                    <p><strong>To:</strong> $newFormattedDate at $newFormattedTime</p>
                </div>
                
                <div class='appointment-details'>
                    <h3>New Appointment Details</h3>
                    <p><strong>Doctor:</strong> Dr. $doctorName</p>
                    <p><strong>Date:</strong> $newFormattedDate</p>
                    <p><strong>Time:</strong> $newFormattedTime</p>
                </div>
                
                <p><strong>Important Notes:</strong></p>
                <ul>
                    <li>Please arrive 15 minutes before your new appointment time</li>
                    <li>Bring your ID and any necessary medical documents</li>
                    <li>If you need to make further changes, please do so at least 24 hours in advance</li>
                </ul>
                
                <p>If you have any questions, please contact our support team.</p>
            </div>
            <div class='footer'>
                <p>Thank you for choosing AfyaCall Health Services</p>
                <p>Contact us: support@afyacall.com |  +255-744-432654</p>
            </div>
        </div>
    </body>
    </html>";

    return sendEmail($patientEmail, $patientName, $subject, $body);
}

function sendCancellationConfirmationEmail($patientEmail, $patientName, $doctorName, $appointmentDateTime) {
    $formattedDate = date('F j, Y', strtotime($appointmentDateTime));
    $formattedTime = date('h:i A', strtotime($appointmentDateTime));
    
    $subject = "Appointment Cancelled - AfyaCall Health";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #FF4D4D; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 20px; }
            .footer { background: #333; color: white; padding: 10px; text-align: center; }
            .cancellation-details { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Appointment Cancellation Confirmation</h2>
            </div>
            <div class='content'>
                <p>Dear $patientName,</p>
                <p>Your appointment with Dr. $doctorName has been successfully cancelled.</p>
                
                <div class='cancellation-details'>
                    <h3>Cancelled Appointment Details</h3>
                    <p><strong>Doctor:</strong> Dr. $doctorName</p>
                    <p><strong>Date:</strong> $formattedDate</p>
                    <p><strong>Time:</strong> $formattedTime</p>
                </div>
                
                <p>If this was a mistake or you'd like to book a new appointment, please log in to your AfyaCall account.</p>
                <p>We hope to serve you again in the future.</p>
            </div>
            <div class='footer'>
                <p>Thank you for choosing AfyaCall Health Services</p>
                <p>Contact us: support@afyacall.com | +255-744-432654</p>
            </div>
        </div>
    </body>
    </html>";

    return sendEmail($patientEmail, $patientName, $subject, $body);
}

function sendEmail($toEmail, $toName, $subject, $body) {
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
        
        // Enable debug if needed
        $mail->SMTPDebug = 0;
        
        // Recipients
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo(REPLY_TO_EMAIL, REPLY_TO_NAME);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        $mail->send();
        error_log("Email sent successfully to: $toEmail");
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}
?>