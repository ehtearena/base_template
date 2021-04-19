<?php
session_start();
date_default_timezone_set('Africa/Nairobi');

//error_reporting(E_ALL);
//ini_set('display_errors','1');

require_once "/home/ewp/public_html/mis/app/cronStrap.php";
require_once "/home/ewp/public_html/mis/tcpdf/config/lang/eng.php";
require_once "/home/ewp/public_html/mis/tcpdf/tcpdf.php";
require_once "/home/ewp/public_html/mis/helper/helper_functions_orig.php";
require_once "/home/ewp/public_html/mis/controller/alternate.php";
require_once "/home/ewp/public_html/mis/controller/mail.php";
require_once "/home/ewp/public_html/mis/controller/form.php";
require_once "/home/ewp/public_html/mis/controller/booking.php";
require_once "/home/ewp/public_html/mis/model/common_functions_orig.php";
require_once "/home/ewp/public_html/mis/helper/PHPMailer/class.phpmailer.php";

function LOGGER($file,$line,$class,$action,$output)
{
	if ($_SESSION['boot']->logger == true)
	{
		$usr = " [A] ";
		if (isset($_SESSION['user_logged']))
		{
			$usr = " [".$_SESSION['user_logged']."] ";
		}

		//echo date('D, d M Y H:i:s T')." $usr [".$file.":".$line." ".$class."=>".$action."] ".$output."\n<BR>";
	}
}

$boot = new bootStrap();
$_SESSION['boot'] = $boot;

$clazz = new bookingClass();

$clazz->updateGoogleCalendar();
