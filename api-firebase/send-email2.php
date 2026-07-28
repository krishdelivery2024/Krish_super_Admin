<?php 
// header('Access-Control-Allow-Origin: *');
   
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	use PHPMailer\PHPMailer\SMTP;
    require   __DIR__.'/../library/phpmailer/PHPMailer.php';
    require   __DIR__.'/../library/phpmailer/SMTP.php';
    require   __DIR__.'/../library/phpmailer/Exception.php';

function send_email($to,$subject,$message){
	include_once '../includes/crud.php';
	$db=new Database();
	$db->connect();

	include_once '../includes/functions.php';
    $fn = new functions();
    $system_configs = $fn->get_system_configs();
	
	$app_name = $system_configs['app_name'];
	$from_mail = $system_configs['from_mail'];
	$reply_to = $system_configs['reply_to'];
	
	$mail = new PHPMailer();
	$mail->isSMTP();
//	$mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Host = $system_configs['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $from_mail; 
    $mail->Password = $system_configs['mail_password'];
    $mail->SMTPSecure = $system_configs['smtp_secure'];
    $mail->Port = $system_configs['smtp_port'];

    $mail->setFrom($from_mail, $app_name);
    $mail->addReplyTo($reply_to, $app_name);
    $mail->addAddress($to, 'Tim'); 
 //   $mail->addCC('cc1@example.com', 'Elena');
  //  $mail->addBCC('bcc1@example.com', 'Alex');
    $mail->Subject =$subject;
    $mail->isHTML(true);
    $mail->Body =$message;
    if($mail->send()){
        return true;
    }else{
        return false;
       
    }
	
}
function send_promo_email($message){
	include_once '../includes/crud.php';
	$db=new Database();
	$db->connect();
	
	include_once '../includes/functions.php';
    $fn = new functions();
    $system_configs = $fn->get_system_configs();
	
	$app_name = $system_configs['app_name'];
	$from_mail = $system_configs['from_mail'];
	$reply_to = $system_configs['reply_to'];
	
    $subject="New Limited Offer From ".$system_configs['app_name'];
	
	$mail = new PHPMailer();
	$mail->isSMTP();
//	$mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Host = $system_configs['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $from_mail; 
    $mail->Password = $system_configs['mail_password'];
    $mail->SMTPSecure = $system_configs['smtp_secure'];
    $mail->Port = $system_configs['smtp_port'];
    
    $mail->setFrom($from_mail, $app_name);
    $mail->addReplyTo($reply_to, $app_name);
    $sql = "select email from `users`";
        $db->sql($sql);
        $addr = $db->getResult();
        $mail->AddAddress($from_mail);
    foreach ($addr as $ad) {
        $mail->addBCC($ad['email']);
               
    }
 //   $mail->addCC('cc1@example.com', 'Elena');
  //  $mail->addBCC('bcc1@example.com', 'Alex');
    $mail->Subject =$subject;
    $mail->isHTML(true);
    $mail->Body =$message;
    if($mail->send()){
        return true;
    }else{
        return false;
    }
	
}
/*$to="karthick.s@spiderindia.com";
$subject="Hi";
$message="Hello";
send_promo_email($message);*/
?>