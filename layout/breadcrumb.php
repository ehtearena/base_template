<?php
echo "<a href='".$_SESSION['boot']->getAppPublic()."index'>Home</a>"; 

if ($_SESSION['boot']->getController() != "index")
{
	echo " <img src='".$_SESSION['boot']->getAppPublic()."images/rightarrow.png' alt='ra'> <a href='".$_SESSION['boot']->getAppPublic().$_SESSION['boot']->getController()."'>".UCFirst($_SESSION['boot']->getController())."</a>";
}

if ($_SESSION['boot']->getAction() != "index")
{
	echo " <img src='".$_SESSION['boot']->getAppPublic()."images/rightarrow.png' alt='ra'> ".UCfirst($_SESSION['boot']->getAction());
} 
?>
<div id="logstate">
<?php
	if (isset($_SESSION['user_name']) && $_SESSION['user_name'] != "")
	{
		echo "<span><a href='".$_SESSION['boot']->getAppPublic()."user'>".$_SESSION['user_name']."</a></span> logged in<a href='".$_SESSION['boot']->getAppPublic()."user/logout'>| Logout</a> | <a href='".$_SESSION['boot']->getAppPublic()."user/changePassPage'>Change Password</a>";
	}
	else
	{
		echo "<a href='".$_SESSION['boot']->getAppPublic()."user/loginPage'>Login</a>/<a href='".$_SESSION['boot']->getAppPublic()."user/registerPage'>Register</a>";
	}
?>
</div>