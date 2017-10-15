<?php

include_once('../class.phpmailer.php');
include("../class.smtp.php");

$mail             = new PHPMailer(); // defaults to using php "mail()"

//$body             = $mail->getFile('contents.html');
$body             = eregi_replace("[\]",'',$body);

$mail->From       = "info@phpmexico.com.mx";
$mail->FromName   = "First Last";

$mail->Subject    = "PHPMailer Test Subject via mail()";

$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($body); 

$mail->AddAddress("alfdixi@gmail.com", "John Doe");

//$mail->AddAttachment("images/phpmailer.gif");             // attachment

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}
?>