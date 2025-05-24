<?php

//ENV stuff
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'details.env');
$dotenv->load();

define('EMAIL_HOST', $_ENV['EMAIL_HOST']);
define('EMAIL_ADDR', $_ENV['EMAIL_ADDR']);
define('EMAIL_PASS', $_ENV['EMAIL_PASS']);

require_once __DIR__ . '/phpmailer/PHPMailer.php';
require_once __DIR__ . '/phpmailer/SMTP.php';
require_once __DIR__ . '/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendWishlistEmail($to, $subject, $bodyHtml) {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = 'error_log'; //debug
        // SMTP config
        $mail->isSMTP();
        $mail->Host = EMAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = EMAIL_ADDR; // SMTP username
        $mail->Password = EMAIL_PASS;  //app password 
        $mail->SMTPSecure = 'tls';          //or 'ssl' but thats more complicated
        $mail->Port = 587;                  //or 465 for SSL

        $mail->setFrom(EMAIL_ADDR, 'Wishlist Notifier');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHtml;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}
