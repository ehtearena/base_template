<?php
class bootStrap
{
	public $defaultLayout;
	public $appRoot;
	public $appPublic;
	public $appPath;

	public $controller;
	public $action;

	public $user;
	public $password;
	public $host;
	public $database;
	public $siteAddress;

	public $debug;
	public $logger;
	public $loggerPath;
	public $errorEmail;
	public $rmisUser;
	public $rmisHost;
	public $rmisPass;
	public $rmisNotificationEmail;

	public function __construct()
	{
		$ini_array = parse_ini_file("config.ini", true);

		$this->defaultLayout = $ini_array['bootstrap']['defaultLayout'];
		$this->appRoot = $ini_array['bootstrap']['appRoot'];
		$this->user = $ini_array['database']['user'];
		$this->password = $ini_array['database']['password'];
		$this->host = $ini_array['database']['host'];
		$this->database = $ini_array['database']['database'];
		$this->appPublic = $ini_array['bootstrap']['appPublic'];
		$this->debug = $ini_array['bootstrap']['debug'];
		$this->appPath = $ini_array['bootstrap']['appPath'];
		$this->logger = $ini_array['bootstrap']['logger'] == 'true'? true: false;
		$this->siteAddress = $ini_array['bootstrap']['siteAddress'];
		$this->loggerPath = $ini_array['bootstrap']['loggerPath'];
		$this->errorEmail = $ini_array['bootstrap']['errorEmail'];

		$controller = "index";
		$action = "index";
		
		if (isset($_SERVER['REQUEST_URI']))
		{
			$abs_path = preg_split("/\//", preg_replace('/\//', '', $_SERVER['REQUEST_URI'], 1));
	//LOCAL ONLY:
		//$abs_path = preg_split("/\//", str_replace($this->appPublic, "", $_SERVER['REQUEST_URI']));
			$abs_rem = preg_split("/\?/", $abs_path[0]);
		}

		if (isset ($abs_rem[0]) && $abs_rem[0] != "" && $abs_rem[0] != "robots.txt" && $abs_rem[0] != "favicon.ico")
		{
			$controller = $abs_rem[0];
		}

		if (isset ($abs_path[1]) && $abs_path[1] != "")
		{
			$abs_rem = preg_split("/\?/", $abs_path[1]);
			if (isset ($abs_rem[0]))
			{
				$action = $abs_rem[0];
			}
		}
		$this->controller = $controller;
		$this->action = $action;
	}

	function getController()
	{
		//ensure controller is loaded
		$thisController = $this->controller;
		return $thisController;
	}

	
	function getAction()
	{
		return $this->action;
	}

	function getDefaultLayout()
	{
		return $this->defaultLayout;
	}

	function setDefaultLayout($defaultLayout)
	{
		$this->defaultLayout = defaultLayout;
	}

	function getAppRoot()
	{
		return $this->appRoot;
	}

	function setAppRoot($appRoot)
	{
		$this->appRoot = appRoot;
	}

	function getAppPublic()
	{
		return $this->appPublic;
	}

	function setAppPublic($appPublic)
	{
		$this->appPublic = $appPublic;
	}

	function getUser()
	{
		return $this->user;
	}

	function setUser($user)
	{
		$this->user = $user;
	}

	function getPassword()
	{
		return $this->password;
	}

	function setPassword($password)
	{
		$this->password = $password;
	}

	function getHost()
	{
		return $this->host;
	}

	function setHost($host)
	{
		$this->host = $host;
	}

	function getDebug()
	{
		return $this->debug;
	}

	function getDatabase()
	{
		return $this->database;
	}

	function setDatabase($database)
	{
		$this->database = $database;
	}
}
?>
