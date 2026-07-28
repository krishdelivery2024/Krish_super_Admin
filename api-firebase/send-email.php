<?php 
// header('Access-Control-Allow-Origin: *');
// Manually include PHPMailer classes
include_once '../library/phpmailer/PHPMailer.php';
include_once '../library/phpmailer/SMTP.php';
include_once '../library/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($to,$subject,$message){ 
	include_once '../includes/crud.php';
	$db=new Database();
	$db->connect();
	
	include_once '../includes/functions.php';
    $fn = new functions();
    $system_configs = $fn->get_system_configs();
	
	$app_name = $system_configs['app_name'];
	$username = $system_configs['smtp_username'];
	$password = $system_configs['mail_password'];
	$from_mail = $system_configs['from_mail'];
	$reply_to = $system_configs['reply_to'];
	$smtp_host = $system_configs['smtp_host'];
	
	//send email
	/*$headers = "From: ".$app_name."<".$from_mail.">\n";
	$headers .= "Reply-To: ".$reply_to."\n";
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\n";
		if(!mail($to,$subject,$message,$headers))
			return false;
		else
			return true;*/
			


    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0; // Enable verbose debug output (0 = off, 1 = client messages, 2 = client and server messages)
         $mail->isSMTP();                                       // Use SMTP
        $mail->Host       = $smtp_host;               // Set SMTP server
        $mail->SMTPAuth   = true;                             // Enable SMTP authentication
        $mail->Username   = $username;         // SMTP username
        $mail->Password   = $password;                  // SMTP password
        $mail->SMTPSecure = 'tls';   // Enable TLS encryption
        $mail->Port       = 587;                              // TCP port to connect to
    
        // Recipients
        $mail->setFrom($username, $app_name);
        $mail->addAddress($to, $app_name);   // Add recipient
    
        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $message;
        // Send the email
        $mail->send();
        return true;
        //echo 'Message has been sent';
    } catch (Exception $e) {
        return false;
        //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>