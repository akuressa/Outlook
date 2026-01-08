<?php
set_time_limit(0);
/**
 * Cron Job Script - Send Appointment Reminders
 * This script should be run daily at 9 AM via cron job
 * 
 * Cron job setup example:
 * 0 9 * * * /usr/bin/php /path/to/send_appointment_reminders.php
 * 
 * Or for Windows Task Scheduler:
 * php.exe "C:\xampp\htdocs\FTP Files_Outlook_Project\kundenmanager\send_appointment_reminders.php"
 */

include_once('init.php');

/**
 * Send appointment reminder email
 */
function send_appointment_email($appointment) {
    return true;
    // require_once('includes/class.phpmailer.php');
    // require_once('includes/class.smtp.php');
    
    // $mail = new PHPMailer();
    // $mail->IsSMTP();
    // // Old PHPMailer doesn't support SSL/TLS directly
    // // Try port 587 first (TLS may be negotiated by server)
    // // If that doesn't work, try port 25 or 465
    // $mail->Host = "alfa3203.alfahosting-server.de";
    // $mail->Port = 587;
    // $mail->SMTPAuth = true;
    // $mail->Username = "web4067p17";
    // $mail->Password = "5WtgplOL";
    
    // $mail->From = "noreply-termin@nrwsystem.de";
    // $mail->FromName = "Termin Erinnerung";
    // $mail->AddAddress($appointment['email_address'], $appointment['customer']);
    // $mail->AddBCC("noreply-termin@nrwsystem.de", "Termin Erinnerung");
    
    // $mail->IsHTML(true);
    // $mail->CharSet = "UTF-8";
    
    // $mail->Subject = "Termin Erinnerung: " . $appointment['subject'];
    
    // $start_date_formatted = date('d.m.Y H:i', strtotime($appointment['start_date']));
    // $end_date_formatted = date('d.m.Y H:i', strtotime($appointment['end_date']));
    
    // $mail->Body = "
    // <html>
    // <head>
    //     <style>
    //         body { font-family: Arial, sans-serif; }
    //         .container { max-width: 600px; margin: 0 auto; padding: 20px; }
    //         .header { background-color: #4CAF50; color: white; padding: 15px; text-align: center; }
    //         .content { padding: 20px; background-color: #f9f9f9; }
    //         .info-row { margin: 10px 0; }
    //         .label { font-weight: bold; }
    //     </style>
    // </head>
    // <body>
    //     <div class='container'>
    //         <div class='header'>
    //             <h2>Termin Erinnerung</h2>
    //         </div>
    //         <div class='content'>
    //             <p>Sehr geehrte/r " . htmlspecialchars($appointment['customer']) . ",</p>
    //             <p>dies ist eine Erinnerung an Ihren bevorstehenden Termin:</p>
                
    //             <div class='info-row'>
    //                 <span class='label'>Betreff:</span> " . htmlspecialchars($appointment['subject']) . "
    //             </div>
    //             <div class='info-row'>
    //                 <span class='label'>Filiale:</span> " . htmlspecialchars($appointment['branch_name']) . "
    //             </div>
    //             <div class='info-row'>
    //                 <span class='label'>Startdatum:</span> " . $start_date_formatted . "
    //             </div>
    //             <div class='info-row'>
    //                 <span class='label'>Enddatum:</span> " . $end_date_formatted . "
    //             </div>";
    
    // if (!empty($appointment['location'])) {
    //     $mail->Body .= "<div class='info-row'><span class='label'>Ort:</span> " . htmlspecialchars($appointment['location']) . "</div>";
    // }
    
    // if (!empty($appointment['description'])) {
    //     $mail->Body .= "<div class='info-row'><span class='label'>Beschreibung:</span><br>" . nl2br(htmlspecialchars($appointment['description'])) . "</div>";
    // }
    
    // $mail->Body .= "
    //             <p>Bitte bestätigen Sie Ihre Teilnahme oder kontaktieren Sie uns bei Fragen.</p>
    //             <p>Mit freundlichen Grüßen,<br>Ihr Team</p>
    //         </div>
    //     </div>
    // </body>
    // </html>";
    
    // try {
    //     if ($mail->Send()) {
    //         return true;
    //     } else {
    //         error_log("Email Error: " . $mail->ErrorInfo);
    //         return false;
    //     }
    // } catch (Exception $e) {
    //     error_log("Email Exception: " . $e->getMessage());
    //     return false;
    // }
}

/**
 * Send appointment reminder SMS via Infobip
 */
function send_appointment_sms($appointment) {
    $phone = $appointment['phone_no_1'];
    
    // Remove any non-numeric characters except +
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    

    
    $start_date_formatted = date('d.m.Y H:i', strtotime($appointment['start_date']));
    
    $message = "Termin Erinnerung: " . $appointment['subject'] . " am " . $start_date_formatted . ". Filiale: " . $appointment['branch_name'];
    $apiKey = '•••••••••••••••••ee01'; 

    $url = 'gr6jgw.api.infobip.com/sms/2/text/advanced';
    
    $requestData = [
        'messages' => [
            [
                'destinations' => [
                    ['to' => $phone]
                ],
                'from' => 'ServiceSMS',
                'text' => $message
            ]
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: App ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        echo "SMS sent successfully! Response: " . print_r($result, true);
        return true;
    } else {
        echo "SMS Error - HTTP Code: $http_code, Response: $response" . ($curl_error ? ", cURL Error: $curl_error" : "");
        return false;
    }
}

/**
 * Log notification to database
 */
function log_notification($appointment_id, $type, $is_success) {
    $fields = "appointment_id, type, is_success";
    $values = ":appointment_id, :type, :is_success";
    $execute = array(
        ':appointment_id' => $appointment_id,
        ':type' => $type,
        ':is_success' => ($is_success ? 1 : 0)
    );
    
    save(NOTIFICATIONS, $fields, $values, $execute);
}

// ========== MAIN EXECUTION ==========

// Get tomorrow's date
$tomorrow = date('Y-m-d', strtotime('+1 day'));

// Fetch appointments scheduled for tomorrow
$table = APPOINMENTS . " as a, " . USERS . " as u_1, " . USERS . " as u_2";
$where = "WHERE DATE(a.start_date) = :tomorrow_date AND a.user_id=u_1.id AND a.branch_id=u_2.id";
$fields = "a.id, a.subject, a.location, a.start_date, a.end_date, a.description, u_1.person_name as customer, u_1.email_address, u_1.phone_no_1, u_2.branch_name";
$execute = array(':tomorrow_date' => $tomorrow);

$appointments = find('all', $table, $fields, $where, $execute);

$log_messages = array();
$log_messages[] = "=== Appointment Reminder Script Started at " . date('Y-m-d H:i:s') . " ===";
$log_messages[] = "Checking appointments for: " . $tomorrow;
$log_messages[] = "Found " . count($appointments) . " appointment(s)";

if (!empty($appointments)) {
    foreach ($appointments as $appointment) {
        $appointment_id = $appointment['id'];
        $customer_name = $appointment['customer'];
        $email = $appointment['email_address'];
        $phone = $appointment['phone_no_1'];
        
        $log_messages[] = "\nProcessing Appointment ID: $appointment_id - Customer: $customer_name";
        
        // Send Email Reminder
        if (!empty($email)) {
            $email_sent = send_appointment_email($appointment);
            log_notification($appointment_id, 'email', $email_sent);
            $log_messages[] = "Email sent to $email: " . ($email_sent ? "SUCCESS" : "FAILED");
        } else {
            $log_messages[] = "Email skipped - No email address";
            log_notification($appointment_id, 'email', false);
        }
        
        // Send SMS Reminder
        if (!empty($phone)) {
            $sms_sent = send_appointment_sms($appointment);
            log_notification($appointment_id, 'sms', $sms_sent);
            $log_messages[] = "SMS sent to $phone: " . ($sms_sent ? "SUCCESS" : "FAILED");
        } else {
            $log_messages[] = "SMS skipped - No phone number";
            log_notification($appointment_id, 'sms', false);
        }
    }
} else {
    $log_messages[] = "No appointments found for tomorrow.";
}

$log_messages[] = "\n=== Script Completed at " . date('Y-m-d H:i:s') . " ===";

// Output log (for cron job monitoring)
echo implode("\n", $log_messages) . "\n";
?>
