<?php
require 'phpmailer/class.phpmailer.php';

function sendApiDetails($email){
	$mail = new PHPMailer;

	$mail->IsSMTP();                                      // Set mailer to use SMTP
	$mail->Host = 'mail.avoindata.net';  // Specify main and backup server
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = 'api@avoindata.net';                            // SMTP username
	$mail->Password = 'Zia5dekk';                           // SMTP password
	$mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted

	$mail->From = 'api@avoindata.net';
	$mail->FromName = 'Avoindata API [no reply]';
	//$mail->AddAddress('');  // Add a recipient
	$mail->AddAddress($email);               // Name is optional
	//$mail->AddReplyTo('info@example.com', 'Information');
	$mail->AddCC('jarkko.moilanen@hermia.fi');
	//$mail->AddBCC('bcc@example.com');

	//$mail->WordWrap = 50;                                 // Set word wrap to 50 characters

	// $mail->IsHTML(true);                                  // Set email format to HTML

	$mail->Subject = 'Domain on rekisteröity onnituneesti Avoindata.net API:in tietokantaan.';
	$mail->Body    = 'Ohjeet miten lähetät kysymyksen avoindata.net palveluun API:n kautta löytyy alta';
	//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

	if(!$mail->Send()) {
	   echo 'Message could not be sent.';
	   echo 'Mailer Error: ' . $mail->ErrorInfo;
	
	   
	}else{
	
	  echo "Message sent to ".$email." -> ";
	}
	
 
  }

sendApiDetails("jarkko.moilanen@ossoil.com");



?>
