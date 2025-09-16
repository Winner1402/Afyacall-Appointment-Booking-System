<?php
include 'auth_check.php';
authorize(['admin']);
include 'config/db.php';

require 'vendor/autoload.php'; // ensure mPDF installed

// Get form data
$reportType = $_POST['report_type'] ?? 'appointments';
$timeframe = $_POST['timeframe'] ?? 'daily';
$exportFormat = $_POST['export_format'] ?? 'pdf';

// Official info
$orgName = "AfyaCall Health Services";
$orgTagline = "Enhancing Healthcare Access";
$orgAddress = "Dar es Salaam, Tanzania";
$orgPhone = "0900011111";
$orgEmail = "info@afyacall.co.tz";
$logoPath = __DIR__ . '/assets/images/logo.jpeg';  // ensure correct path

// Determine date range label
$dateLabel = '';
if ($timeframe == 'daily') {
    $dateLabel = date('F j, Y');
} elseif ($timeframe == 'weekly') {
    // last 7 days
    $dateLabel = "Week ending " . date('F j, Y');
} elseif ($timeframe == 'monthly') {
    $dateLabel = date('F, Y');
}

// Fetch data based on report type
$data = [];
$headers = [];

if ($reportType == 'appointments') {
    $sql = "
        SELECT a.id as ID,
               u.name AS Patient,
               du.name AS Doctor,
               s.name AS Specialty,
               ds.slot_datetime AS 'Date & Time',
               a.status AS Status
        FROM appointments a
        JOIN users u ON a.patient_id = u.id
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users du ON d.user_id = du.id
        JOIN specialties s ON d.specialty_id = s.id
        JOIN doctor_slots ds ON a.slot_id = ds.id
        WHERE
    ";
    // filter by timeframe
    if ($timeframe == 'daily') {
        $sql .= " DATE(ds.slot_datetime) = CURDATE() ";
    } elseif ($timeframe == 'weekly') {
        $sql .= " ds.slot_datetime >= CURDATE() - INTERVAL 7 DAY ";
    } else {  // monthly
        $sql .= " MONTH(ds.slot_datetime) = MONTH(CURDATE()) AND YEAR(ds.slot_datetime) = YEAR(CURDATE()) ";
    }
    $sql .= " ORDER BY ds.slot_datetime ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $headers = ['ID','Patient','Doctor','Specialty','Date & Time','Status'];
} elseif ($reportType == 'doctors') {
    $sql = "
        SELECT d.id AS ID,
               u.name AS Doctor,
               u.email AS Email,
               u.phone AS Phone,
               s.name AS Specialty
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        JOIN specialties s ON d.specialty_id = s.id
        ORDER BY u.name ASC
    ";
    $stmt = $conn->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $headers = ['ID','Doctor','Email','Phone','Specialty'];
} elseif ($reportType == 'patients') {
    $sql = "
        SELECT u.id AS ID,
               u.name AS Patient,
               u.email AS Email,
               u.phone AS Phone
        FROM users u
        WHERE u.role = 'patient'
        ORDER BY u.name ASC
    ";
    $stmt = $conn->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $headers = ['ID','Patient','Email','Phone'];
} else {
    exit("Invalid report type");
}

// Build document title
$docTitle = ucfirst($reportType) . " Report - " . ucfirst($timeframe);

// Generate PDF
if ($exportFormat == 'pdf') {
    // Use mPDF
    $mpdf = new \Mpdf\Mpdf(['margin_left'=>15, 'margin_right'=>15, 'margin_top'=>40, 'margin_bottom'=>30]);
    
    // Header with logo
    $headerHtml = '
    <div style="display:flex; align-items:center;">
      <div style="flex:1;">
        <img src="' . $logoPath . '" style="height:60px;" />
      </div>
      <div style="flex:3; text-align:right; font-family: sans-serif;">
        <h2 style="margin:0;">' . htmlspecialchars($orgName) . '</h2>
        <p style="margin:0;">' . htmlspecialchars($orgAddress) . '</p>
        <p style="margin:0;">Phone: ' . htmlspecialchars($orgPhone) . ' | Email: ' . htmlspecialchars($orgEmail) . '</p>
      </div>
    </div>
    <hr style="margin-top:10px; margin-bottom:20px;" />
    <div style="text-align:center; font-family: sans-serif;">
        <h3 style="margin:0;">' . htmlspecialchars($docTitle) . '</h3>
        <p style="margin:0;">' . htmlspecialchars($dateLabel) . '</p>
    </div>
    <br />
    ';
    $mpdf->WriteHTML($headerHtml);
    
    // Table
    $mpdf->SetFont('Arial','',10);
    $tableHtml = '<table border="1" cellpadding="6" cellspacing="0" width="100%" style="border-collapse: collapse; font-family: sans-serif; font-size:10pt;">';
    // headers
    $tableHtml .= '<tr style="background-color:#127137; color:#ffffff;">';
    foreach ($headers as $h) {
        $tableHtml .= '<th>' . htmlspecialchars($h) . '</th>';
    }
    $tableHtml .= '</tr>';
    // data rows
    if (empty($data)) {
        $tableHtml .= '<tr><td colspan="' . count($headers) . '" style="text-align:center;">No records found.</td></tr>';
    } else {
        foreach ($data as $row) {
            $tableHtml .= '<tr>';
            foreach ($headers as $key) {
                $cell = $row[$key] ?? '';
                $tableHtml .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $tableHtml .= '</tr>';
        }
    }
    $tableHtml .= '</table><br />';
    $mpdf->WriteHTML($tableHtml);
    
    // Remarks / Footer
    $remarksHtml = '<hr /><p style="font-family: sans-serif; font-size:9pt;">Remarks: This report is computer-generated and does not require a signature. Generated on ' . date('F j, Y, g:i a') . '</p>';
    $mpdf->WriteHTML($remarksHtml);

    // Output file
    $filename = $reportType . "_Report_" . $timeframe . "_" . date('Ymd_His') . ".pdf";
    $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    exit();
}

// Generate CSV (Excel friendly)
if ($exportFormat == 'excel' || $exportFormat == 'csv') {
    $filename = $reportType . "_Report_" . $timeframe . "_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    // Official header rows
    fputcsv($out, [$orgName]);
    fputcsv($out, [$orgAddress . " | Phone: " . $orgPhone . " | Email: " . $orgEmail]);
    fputcsv($out, []);
    fputcsv($out, [$docTitle]);
    fputcsv($out, [$dateLabel]);
    fputcsv($out, []);
    // Column headers
    fputcsv($out, $headers);
    // Data
    if (empty($data)) {
        fputcsv($out, array_fill(0, count($headers), 'No records found.'));
    } else {
        foreach ($data as $row) {
            $line = [];
            foreach ($headers as $h) {
                $line[] = $row[$h] ?? '';
            }
            fputcsv($out, $line);
        }
    }
    // Remarks
    fputcsv($out, []);
    fputcsv($out, ["Remarks: This report is computer-generated and does not require a signature. Generated on " . date('F j, Y, g:i a')]);
    fclose($out);
    exit();
}

// Generate Word document (simple HTML + Word headers)
if ($exportFormat == 'word') {
    $filename = $reportType . "_Report_" . $timeframe . "_" . date('Ymd_His') . ".doc";
    header("Content-type: application/vnd.ms-word");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    echo "<html>";
    echo "<head><meta charset='UTF-8'><title>" . htmlspecialchars($docTitle) . "</title></head>";
    echo "<body>";
    echo "<div style='display:flex; align-items:center;'>";
    echo "<div style='float:left;'><img src='" . $logoPath . "' style='height:60px;'/></div>";
    echo "<div style='float:right; text-align:right; font-family: sans-serif;'>";
    echo "<h2 style='margin:0;'>" . htmlspecialchars($orgName) . "</h2>";
    echo "<p style='margin:0;'>" . htmlspecialchars($orgAddress) . "</p>";
    echo "<p style='margin:0;'>Phone: " . htmlspecialchars($orgPhone) . " | Email: " . htmlspecialchars($orgEmail) . "</p>";
    echo "</div>";
    echo "<div style='clear:both;'></div>";
    echo "<hr style='margin-top:10px; margin-bottom:20px;' />";
    echo "<h3 style='text-align:center;'>" . htmlspecialchars($docTitle) . "</h3>";
    echo "<p style='text-align:center;'>" . htmlspecialchars($dateLabel) . "</p>";
    echo "<br />";

    // Table
    echo "<table border='1' cellpadding='6' cellspacing='0' width='100%' style='border-collapse: collapse; font-family: sans-serif; font-size:10pt;'>";
    echo "<tr style='background-color:#127137; color:#ffffff;'>";
    foreach ($headers as $h) {
        echo "<th>" . htmlspecialchars($h) . "</th>";
    }
    echo "</tr>";
    if (empty($data)) {
        echo "<tr><td colspan='" . count($headers) . "' style='text-align:center;'>No records found.</td></tr>";
    } else {
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($headers as $key) {
                echo "<td>" . htmlspecialchars($row[$key] ?? '') . "</td>";
            }
            echo "</tr>";
        }
    }
    echo "</table><br />";
echo "<p style='font-family:sans-serif; font-size:9pt;'>
<b>Remarks:</b> This report is computer-generated and does not require a signature.<br>
<b>Created by:</b> " . htmlspecialchars($_SESSION['user_name']) . " (" . htmlspecialchars($_SESSION['user_role']) . ")<br>
<b>Generated on:</b> " . date('F j, Y, g:i a') . "
</p>";
    echo "</body>";
    echo "</html>";
    exit();
}

// If nothing matched
echo "Unsupported export format.";
exit();
?>
