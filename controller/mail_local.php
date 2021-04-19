<?php
if (file_exists("../helper/PHPMailer/class.phpmailer.php"))
{
	require_once('../helper/PHPMailer/class.phpmailer.php');
}

class mailClass
{
    public function index()
    {
    	$view = "This controller manages emails";
		return renderView(__CLASS__,__METHOD__,$view);
    }
    
    public function send($to, $body, $subject, $attachment)
    {
		logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"To: ".$to."; Subject:".$subject);
    	if (strpos($to,",") != FALSE)
		{
			$to = preg_split("/,/",$to); 	
		}
    	
		$mail = new PHPMailer();

		if (isset($attachment) and is_array($attachment))
		{  
			$cidx = 0;
			foreach($attachment as $att)
			{
				$cidx ++;
				$mail->AddInlineStringAttachment($att,"ATT10".$cidx, "att.pdf", "base64", 'application/octet-stream');
			}
		}

		if (isset($attachment) and !is_array($attachment))
		{  
			$mail->AddInlineStringAttachment($attachment,"ATT101", "att.pdf", "base64", 'application/octet-stream');
		}


		$mail->IsSMTP();
//		$mail->SMTPAuth = true;
		$mail->Host = "localhost";
//		$mail->Port = 465;
//		$mail->SMTPSecure = "ssl";
//		$mail->Username = "info@echo-systems.net";
//		$mail->Password = "ejbatsair";
		$mail->SetFrom('contractsmanagement@trademarkea.com', 'TMEA Contracts Management');
		$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; 
		if (is_array($to))
		{
			foreach ($to as $t)
			{
				$mail->AddAddress($t);
			}		
		}
		else
		{
			$mail->AddAddress($to);
		}
		$mail->Subject = $subject;
		$mail->MsgHTML($body);

		if(!$mail->Send()) 
		{
			return $mail->ErrorInfo;
		} 
		else 
		{
			return "OK";
		}
	}
}
?>