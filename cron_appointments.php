<?php
// --- cron_appointments.php ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
include 'config/db.php';
include 'config/email_config.php';

date_default_timezone_set('Africa/Dar_es_Salaam');
file_put_contents('cron_log.txt', date('Y-m-d H:i:s') . " Cron started\n", FILE_APPEND);

// --- Helper function to send email & create notification ---
function sendReminder($conn, $patient, $doctor, $slot_datetime, $appointment_id) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($patient['email'], $patient['name']);
        $mail->addAddress($doctor['email'], $doctor['name']);
        $mail->addReplyTo(REPLY_TO_EMAIL, REPLY_TO_NAME);

        $mail->isHTML(true);
        $mail->Subject = 'Appointment Reminder';
        $mail->Body    = "
            <p>Hello {$patient['name']} and Dr. {$doctor['name']},</p>
            <p>This is a reminder for your appointment scheduled on <b>{$slot_datetime}</b>.</p>
            <p>Thank you,<br>AfyaCall Team</p>
        ";

        $mail->send();
        file_put_contents('cron_log.txt', date('Y-m-d H:i:s') . " Reminder sent for appointment ID {$appointment_id}\n", FILE_APPEND);

        // Insert notifications for patient and doctor
        $stmtNotif = $conn->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, 'info')");
        $stmtNotif->execute([$patient['id'], "Reminder: Your appointment on {$slot_datetime}"]);
        $stmtNotif->execute([$doctor['id'], "Reminder: Appointment with {$patient['name']} on {$slot_datetime}"]);

    } catch (Exception $e) {
        file_put_contents('cron_log.txt', date('Y-m-d H:i:s') . " Reminder failed for appointment ID {$appointment_id}. Error: {$mail->ErrorInfo}\n", FILE_APPEND);
    }
}

// --- 1. Next-day reminders ---
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$stmt = $conn->prepare("
    SELECT a.id, a.patient_id, a.doctor_id, ds.slot_datetime,
           p.name AS patient_name, p.email AS patient_email,
           duser.name AS doctor_name, duser.email AS doctor_email
    FROM appointments a
    JOIN doctor_slots ds ON a.slot_id = ds.id
    JOIN users p ON a.patient_id = p.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users duser ON d.user_id = duser.id
    WHERE a.status='confirmed' AND DATE(ds.slot_datetime) = ?
");
$stmt->execute([$tomorrow]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($appointments as $appt) {
    $patient = ['id'=>$appt['patient_id'],'name'=>$appt['patient_name'],'email'=>$appt['patient_email']];
    $doctor = ['id'=>$appt['doctor_id'],'name'=>$appt['doctor_name'],'email'=>$appt['doctor_email']];
    sendReminder($conn, $patient, $doctor, $appt['slot_datetime'], $appt['id']);
}

// --- 2. Same-day reminders (1 hour before) ---
$now = date('Y-m-d H:i:s');
$oneHourLater = date('Y-m-d H:i:s', strtotime('+1 hour'));
$stmt2 = $conn->prepare("
    SELECT a.id, a.patient_id, a.doctor_id, ds.slot_datetime,
           p.name AS patient_name, p.email AS patient_email,
           duser.name AS doctor_name, duser.email AS doctor_email
    FROM appointments a
    JOIN doctor_slots ds ON a.slot_id = ds.id
    JOIN users p ON a.patient_id = p.id
    JOIN doctors d ON a.doctor_id = d.id
    JOIN users duser ON d.user_id = duser.id
    WHERE a.status='confirmed' AND ds.slot_datetime BETWEEN ? AND ?
");
$stmt2->execute([$now, $oneHourLater]);
$appointmentsToday = $stmt2->fetchAll(PDO::FETCH_ASSOC);

foreach ($appointmentsToday as $appt) {
    $patient = ['id'=>$appt['patient_id'],'name'=>$appt['patient_name'],'email'=>$appt['patient_email']];
    $doctor = ['id'=>$appt['doctor_id'],'name'=>$appt['doctor_name'],'email'=>$appt['doctor_email']];
    sendReminder($conn, $patient, $doctor, $appt['slot_datetime'], $appt['id']);
}

// --- 3. Auto-mark past confirmed appointments as missed ---
$stmt3 = $conn->prepare("
    UPDATE appointments a
    JOIN doctor_slots ds ON a.slot_id = ds.id
    SET a.status='missed'
    WHERE a.status='confirmed' AND ds.slot_datetime < ?
");
$stmt3->execute([$now]);
$updatedRows = $stmt3->rowCount();
file_put_contents('cron_log.txt', date('Y-m-d H:i:s') . " Auto-marked $updatedRows past appointments as missed\n", FILE_APPEND);

file_put_contents('cron_log.txt', date('Y-m-d H:i:s') . " Cron finished\n\n", FILE_APPEND);
