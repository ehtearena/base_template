<?php
session_start();

date_default_timezone_set('Africa/Nairobi');

error_reporting(E_ALL);
ini_set('display_errors','Off');

ini_set('max_execution_time', 900);

register_shutdown_function('shutdown');

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");          // HTTP/1.0
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
//header('X-Content-Type-Options: nosniff');

if (function_exists ('ini_set'))
{
	@ ini_set("session.gc_maxlifetime","10800");

	// Use cookies to store the session ID on the client side
	@ ini_set ('session.use_only_cookies', 1);

	// Disable transparent Session ID support
	@ ini_set ('session.use_trans_sid',    0);
}

require_once "../app/bootStrap_orig.php";
require_once "../app/myAppORIG.php";

$boot = new bootStrap();

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] == true)
{
	//lets confirm if the person accessing is the one who logged in
	if(isset($_SESSION['fingerprint']))
	{
		if ($_SESSION['fingerprint'] = MD5($_SERVER['HTTP_USER_AGENT']."ejbatsair"))
		{
			//we're okay
		}
		else
		{
			$boot->controller = "user";
			$boot->action = "login";
		}
	}
}
else
{
	if (!isset($_SESSION['zetoken']))
	{
		$_SESSION['zetoken'] = md5(uniqid(rand(), true));
	}
}


$_SESSION['boot'] = $boot;

$app = new myApp($boot,"");

echo $app->render();

function shutdown()
{
   print_r(debug_backtrace(), true);

}
?>
