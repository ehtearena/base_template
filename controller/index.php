<?php

class indexClass
{
	public function __construct()
    {
    
    }
    
    public function index()
    {
    	if (isset($_SESSION) && isset($_SESSION['user_level']) && $_SESSION['user_level'] > 0)
    	{
    		$view = "";
    		return renderView(__CLASS__,__METHOD__,$view);
    	}
    	else
    	{
    		if (file_exists("../controller/user.php")) { require_once "../controller/user.php";}
    		$clazz = new userClass();
    		return $clazz->loginPage();
    	}
    }
}

?>