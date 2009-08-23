<?php
require "../data/lib/phpmailer/phpmailer.php";

$email = new PHPMailer();
$email->Mailer = "mail";
//$email->Host = "mail.e-noise.com";
//$email->Port = 25;
//$email->SMTPAuth = true;
//$email->Username = "xinc@e-noise.com";
//$email->Password = "";
$email->From = "xinc@e-noise.com";
$email->FromName = "Xinc Continuous Integration Server";

// Sets the hostname to use in Message-Id and Received headers and as default HELO string. 
// If empty, the value returned by SERVER_NAME is used or 'localhost.localdomain'.
$email->Hostname = "mail.e-noise.com";
        
$email->Subject = "this is a notification";
$email->Body = "the notification body";
$email->AddAddress("luis.montero@e-noise.com", "Luis Montero");

if ($email->Send() !== true) {
    trigger_error("Error sending notification");
}
                    