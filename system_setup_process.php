<?php
ob_start();
error_reporting(0);
session_start();
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';
header('Content-Type: application/json'); // Important!
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $system_name = trim($_POST['system_name']);
    $contact_email = trim($_POST['contact_email']);
    $contact_phone = trim($_POST['contact_phone']);
    $appointment_duration = intval($_POST['appointment_duration']);
    $working_hours = trim($_POST['working_hours']);
    $max_appointments_per_day = intval($_POST['max_appointments_per_day']);
    $file_upload_limit_mb = intval($_POST['file_upload_limit_mb']);
    $theme_color = trim($_POST['theme_color']);

    $logo = null;
    if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0){
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logo = 'logo_'.time().'.'.$ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], 'uploads/'.$logo);
    }

    $stmt = $conn->query("SELECT id, logo FROM system_settings ORDER BY id DESC LIMIT 1");
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    try {
        if($exists){
            $sql = "UPDATE system_settings SET system_name=:system_name, contact_email=:contact_email,
                    contact_phone=:contact_phone, appointment_duration=:appointment_duration,
                    working_hours=:working_hours, max_appointments_per_day=:max_appointments_per_day,
                    file_upload_limit_mb=:file_upload_limit_mb, theme_color=:theme_color";

            $params = [
                ':system_name'=>$system_name,
                ':contact_email'=>$contact_email,
                ':contact_phone'=>$contact_phone,
                ':appointment_duration'=>$appointment_duration,
                ':working_hours'=>$working_hours,
                ':max_appointments_per_day'=>$max_appointments_per_day,
                ':file_upload_limit_mb'=>$file_upload_limit_mb,
                ':theme_color'=>$theme_color
            ];

            if($logo){
                $sql .= ", logo=:logo";
                $params[':logo'] = $logo;

                // Optional: delete old logo file
                if(!empty($exists['logo']) && file_exists('uploads/'.$exists['logo'])){
                    unlink('uploads/'.$exists['logo']);
                }
            }

            $sql .= " WHERE id=".$exists['id'];
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        } else {
            $sql = "INSERT INTO system_settings 
                    (system_name, contact_email, contact_phone, appointment_duration,
                     working_hours, max_appointments_per_day, file_upload_limit_mb, theme_color, logo)
                    VALUES (:system_name, :contact_email, :contact_phone, :appointment_duration,
                            :working_hours, :max_appointments_per_day, :file_upload_limit_mb, :theme_color, :logo)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':system_name'=>$system_name,
                ':contact_email'=>$contact_email,
                ':contact_phone'=>$contact_phone,
                ':appointment_duration'=>$appointment_duration,
                ':working_hours'=>$working_hours,
                ':max_appointments_per_day'=>$max_appointments_per_day,
                ':file_upload_limit_mb'=>$file_upload_limit_mb,
                ':theme_color'=>$theme_color,
                ':logo'=>$logo
            ]);
        }

        echo json_encode(['status'=>'success','message'=>'System settings saved successfully!']);
    } catch(PDOException $e){
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
} else {
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
}
