<?php

if (file_exists("../helper/PHPMailer/class.phpmailer.php"))
{
	require_once('../helper/PHPMailer/class.phpmailer.php');
}

class mailClass
{
	public function bulknotifyMembers()
	{

		$members = db_fetch("","","","","m.notification_sent_HD = 9","m.*, r.name_ID, e.name as entityname","member m LEFT JOIN registration r ON r.id = m.registration_HD LEFT JOIN entity_type e ON e.id = r.entity_type_ID", false, false, null);

		foreach ($members as $member)
		{
			ob_start();
			include "../templates/notify_member.php";
			$tmpl = ob_get_contents();
			ob_end_clean();

			$tmpl = str_replace("@LINK@", $_SESSION['boot']->siteAddress . "registration/memberlink?id=" . $member['email_hash_HD'], $tmpl);
			$tmpl = str_replace("@BUSINESS@", $member['member_name'], $tmpl);
			$tmpl = str_replace("@ENTITY@", $member['name_ID'], $tmpl);
			$tmpl = str_replace("@ENTITY_TYPE@", $member['entityname'], $tmpl);

			//send email
			$to = $member['email'];
			$subject = "Update ".$member['member_name']." as a member of ".$member['name_ID'];
			$body = $tmpl;

			db_update($member['id'],"member", array(notification_sent_HD), array(1));

			$content = $this->send($to, $body, $subject, null);
		}

		$_SESSION['layout'] = "login";
		$_POST['layout'] = "login";
		return " response:ok";
	}

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

		$mail1 = new PHPMailer();

		if (isset($attachment) and is_array($attachment))
		{
			$cidx = 0;
			foreach($attachment as $att)
			{
				$cidx ++;
				$mail1->AddInlineStringAttachment($att,"ATT10".$cidx, str_replace(" ","",$subject).$cidx.".pdf", "base64", 'application/octet-stream');
			}
		}

		if (isset($attachment) and !is_array($attachment))
		{
			$mail1->AddInlineStringAttachment($attachment,"ATT101", str_replace(" ","",$subject).".pdf", "base64", 'application/octet-stream');
		}


		  $mail1->IsSMTP();
			$mail1->SMTPAuth = true;
			$mail1->SMTPSecure = 'ssl';
			$mail1->Host = "smtp.gmail.com";
			$mail1->Port = 465; // or 587
			$mail1->IsHTML(true);
			$mail1->Username = "mseacrm@gmail.com";
			$mail1->Password = "1845730Aws@";
			$mail1->SetFrom("mseacrm@gmail.com","MSEA Registrar");

		$mail1->AltBody = "To view the message, please use an HTML compatible email viewer!";

		if (is_array($to))
		{
			foreach ($to as $t)
			{
				if (trim($t) != "")
				{
						$mail1->AddAddress(trim($t));
				}
			}
		}
		else
		{
if (trim($to) != "")
{
			//	logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"TMEA. To: ".$to);
				$mail1->AddAddress($to);
}
		}

		$mail1->Subject = $subject;
		$mail1->MsgHTML($body."<BR><BR>Email: ".$to);

		if(!$mail1->Send())
		{
                      return $mail1->ErrorInfo;
		}
		else
		{
                     return "OK";
		}
	}
 public function test()
{
$mail = new PHPMailer();
$mail->IsSMTP(); // enable SMTP
$mail->SMTPDebug = 1; // debugging: 1 = errors and messages, 2 = messages only
$mail->SMTPAuth = true; // authentication enabled
$mail->SMTPSecure = 'ssl';
$mail->Host = "smtp.gmail.com";
$mail->Port = 465; // or 587
$mail->IsHTML(true);
$mail->Username = "mseacrm@gmail.com";
$mail->Password = "sSh9SeDq,r68";
$mail->SetFrom("mseacrm@gmail.com","MSEA Registrar");

			$mail->AltBody = "This is a test email. ";
			$mail->AddAddress($_GET['to']);
			$mail->Subject = "Test Mail from MSEA Registrar";
			$mail->MsgHTML("This is a test email. Please ignore.");

			if(!$mail->Send())
			{
				echo $mail->ErrorInfo;
			}
			else
			{
				echo "OK";
			}
}
}
