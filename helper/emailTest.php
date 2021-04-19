<?php

			$to = "ehteshamm@gmail.com";  		
			$subject = "TMEA Online Contracts Management Portal";

			$body = 'Hi Ezekiel, Please forward me this mail when you get it. Thanks.';

			require_once('PHPMailer/class.phpmailer.php');
			
			$mail = new PHPMailer();
	
			$mail->IsSMTP();
			$mail->Host = "212.22.188.154";
			$mail->SMTPAuth = true;
			$mail->Port = 465;
			$mail->SMTPSecure = "ssl";
			$mail->Username = "contractsmanagement@trademarkea.com";
			$mail->Password = "contracted2013";
			$mail->SetFrom('contractsmanagement@trademarkea.com', 'TMEA Contracts Management');

			$mail->AddAddress($to);
			
			$mail->Subject = $subject;
			$mail->MsgHTML($body);
	
			if(!$mail->Send()) 
			{
				echo print_r($mail,true);
			} 
			else 
			{
				echo "OK";
			}
?>