<?php
function send_mock_email($to, $subject, $message) {
    $logFile = __DIR__ . '/../email_logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    
    $logEntry = "=========================================================\n";
    $logEntry .= "Time    : $timestamp\n";
    $logEntry .= "To      : $to\n";
    $logEntry .= "Subject : $subject\n";
    $logEntry .= "Message :\n$message\n";
    $logEntry .= "=========================================================\n\n";
    
    // Attempt native mail() first, fallback to log
    // In local Windows, mail() usually fails without SMTP. We log it regardless for debugging.
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    @mail($to, $subject, $message, "From: admin@uinssc.ac.id\r\nContent-Type: text/plain; charset=UTF-8");
    
    return true;
}
?>
