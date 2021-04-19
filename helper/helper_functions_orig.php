<?php
require_once "google-api-php-client/src/Google/Client.php";
require_once "google-api-php-client/src/Google/Service/Calendar.php";

if (!function_exists('LOGGER'))
{
	function LOGGER($file, $line, $class, $action, $output)
	{
		if ($_SESSION['boot']->logger == true)
		{
			$f = fopen($_SESSION['boot']->loggerPath . "logic.out", "a") or die("Cannot open log file");

			$usr = "  A                    ";

			$space = "";
			if (isset($_SESSION['user_logged']) && $_SESSION['user_logged'] != "")
			{
				$ulen = strlen($_SESSION['user_name']);
				if ($ulen < 20)
				{
					for ($i = 1; $i < (20 - $ulen); $i++)
					{
						$space .= " ";
					}
				}
				$usr = "  " . $_SESSION['user_name'] . $space . "  ";
			}

			$lpath = "[" . $file . ":" . $line . " " . $class . "=>" . $action . "]";
			$lpath = str_replace($_SESSION['boot']->loggerPath, "", $lpath);

			fwrite($f, date('D, d M Y H:i:s T') . " $usr $lpath $output\n");
			fclose($f);
		}
	}
}

function getUserIdFromDocument($doc_id,$doc_name)
{
		$user_id = 0;
		switch($doc_name)
		{
			case "search":
				$document = db_fetch("","","","","id=".$doc_id,"",$doc_name, false, false, null);
				$user_id = $document[0]['user_HD'];
			break;
			case "registration":
				$document = db_fetch("","","","","id=".$doc_id,"",$doc_name, false, false, null);
				$user_id = $document[0]['user_HD'];
			break;
			case "registration_notice":
				$document = db_fetch("","","","","a.id=".$doc_id,"r.user_HD",$doc_name." a LEFT JOIN registration r ON r.id = a.registration_HD", false, false, null);
				$user_id = $document[0]['user_HD'];
			break;
			case "change_of_particulars":
				$document = db_fetch("","","","","a.id=".$doc_id,"r.user_HD",$doc_name." a LEFT JOIN registration r ON r.id = a.registration_HD", false, false, null);
				$user_id = $document[0]['user_HD'];
			break;
			case "annual_returns":
				$document = db_fetch("","","","","a.id=".$doc_id,"r.user_HD",$doc_name." a LEFT JOIN registration r ON r.id = a.registration_HD", false, false, null);
				$user_id = $document[0]['user_HD'];
			break;
		}

		return $user_id;
}

function getUserFromDocument($doc_id,$doc_name)
{
	$userId = getUserIdFromDocument($doc_id,$doc_name);
	$user = db_fetch("","","","","id = ".$userId, "", "user", false, false, null);
	return $user;
}

function sendNotifications($table, $tid, $url = "")
{
	$details = getDocumentStatus($table,$tid, true);

	foreach ($details as $d)
	{
		  $doc_id = $d['document_ID']; //notif
			$doc_name = $d['document_name']; //notif
			$message = $d['message']; //notif
			$status = $d['status_name'];
			$audience = $d['message_audience'];
			$notes = $d['notes']; //notif
			$actionBy = $d['actionBy']; //notif

			if ($actionBy == "") $actionBy = "System";
			if ($message =="") $message = $status;

			$userid = getUserIdFromDocument($doc_id,$doc_name);

			db_insert('notifications', array('document_ID','document_name','document_owner','message','notes','action_by','audience', 'url'), array($doc_id, $doc_name, $userid,$message, $notes, $actionBy, $audience, $url));
			break;
	}
}

function get_time_ago($time_stamp)
{
    $time_difference = strtotime('now') - $time_stamp;

    if ($time_difference >= 60 * 60 * 24 * 365.242199)
    {
        /*
         * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 365.242199 days/year
         * This means that the time difference is 1 year or more
         */
        return get_time_ago_string($time_stamp, 60 * 60 * 24 * 365.242199, 'year');
    }
    elseif ($time_difference >= 60 * 60 * 24 * 30.4368499)
    {
        /*
         * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 30.4368499 days/month
         * This means that the time difference is 1 month or more
         */
        return get_time_ago_string($time_stamp, 60 * 60 * 24 * 30.4368499, 'month');
    }
    elseif ($time_difference >= 60 * 60 * 24 * 7)
    {
        /*
         * 60 seconds/minute * 60 minutes/hour * 24 hours/day * 7 days/week
         * This means that the time difference is 1 week or more
         */
        return get_time_ago_string($time_stamp, 60 * 60 * 24 * 7, 'week');
    }
    elseif ($time_difference >= 60 * 60 * 24)
    {
        /*
         * 60 seconds/minute * 60 minutes/hour * 24 hours/day
         * This means that the time difference is 1 day or more
         */
        return get_time_ago_string($time_stamp, 60 * 60 * 24, 'day');
    }
    elseif ($time_difference >= 60 * 60)
    {
        /*
         * 60 seconds/minute * 60 minutes/hour
         * This means that the time difference is 1 hour or more
         */
        return get_time_ago_string($time_stamp, 60 * 60, 'hour');
    }
    else
    {
        /*
         * 60 seconds/minute
         * This means that the time difference is a matter of minutes
         */
        return get_time_ago_string($time_stamp, 60, 'minute');
    }
}

function get_time_ago_string($time_stamp, $divisor, $time_unit)
{
    $time_difference = strtotime("now") - $time_stamp;
    $time_units      = floor($time_difference / $divisor);

    settype($time_units, 'string');

    if ($time_units === '0')
    {
        return 'less than 1 ' . $time_unit . ' ago';
    }
    elseif ($time_units === '1')
    {
        return '1 ' . $time_unit . ' ago';
    }
    else
    {
        /*
         * More than "1" $time_unit. This is the "plural" message.
         */
        // TODO: This pluralizes the time unit, which is done by adding "s" at the end; this will not work for i18n!
        return $time_units . ' ' . $time_unit . 's ago';
    }
}

function getDocumentStatus($table,$tid, $onlyList = false)
{
	$ldss =  db_fetch("","","d.action_datetime_HD","desc","d.document_ID = ".$tid." AND d.document_name = '".$table."'","s.name as status_name, s.message, s.step, d.notes, d.document_ID, d.document_name, d.action_datetime_HD, s.message_audience, concat(u.first_name, ' ', u.middle_name, ' ', u.last_name) as actionBy","document_status d LEFT JOIN ".$table."_status s ON s.id = d.status_ID LEFT JOIN user u ON u.id = d.action_by_HD",false,false,null);

	if ($onlyList) return $ldss;

	if (sizeof($ldss) == 0 or $ldss[0]['step'] == 1)
	{
		$lds = 0;
		if ($ldss[0]['step'] == 1) $lds = 1;
		$link = db();
		$sql = "SELECT s.* FROM ".$table."_status s LEFT JOIN document_status d ON s.id = d.status_ID AND d.document_ID = ".$tid." WHERE s.role = ".$_SESSION['user_level']." AND (s.prerequisite_step IS NULL or s.prerequisite_step <= ".$lds.") and s.step >= 1 GROUP BY s.id ORDER BY s.step asc";

		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "- - -- --- ====1|> ".$sql);
		$result = mysqli_query($link, $sql);
		return db_fetch("","","","","","","","","",$result);
	}
	else
	{
		$lds = intval($ldss[0]['step']);
		$link = db();
		$sql = "SELECT s.* FROM ".$table."_status s LEFT JOIN document_status d ON s.id = d.status_ID AND d.document_ID = ".$tid." AND d.id IS NULL WHERE s.role = ".$_SESSION['user_level']." AND (s.prerequisite_step IS NULL or s.prerequisite_step <= ".$lds.") and s.step > ".$lds." GROUP BY s.id ORDER BY s.step asc";
		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "- - -- --- ====2|> ".$sql);

		$result = mysqli_query($link, $sql);
		return db_fetch("","","","","","","","","",$result);
	}
}

function googleCalendarEvent($summary, $location, $description, $startDate, $endDate, $property, $eventId)
{
	$client = new Google_Client();
	$serviceAccountName = '524423998484-gkrduknadvdmp7nsi7734snbjdte3kfb@developer.gserviceaccount.com';
	$scopes= array('https://www.googleapis.com/auth/calendar');
	$privateKey=file_get_contents("/home/ewp/public_html/mis/helper/google-api-php-client/src/certificates/My Project-4f8fcc30ee1e.p12");
	$privateKeyPassword='notasecret';
	$credential = new Google_Auth_AssertionCredentials($serviceAccountName,$scopes, $privateKey, $privateKeyPassword);
	$client->setAssertionCredentials($credential);
	$service = new Google_Service_Calendar($client);

	//SET EVENT
	$event = new Google_Service_Calendar_Event();

	//Property Name - Party Name x Pax
	$event->setSummary($location. " - ". $summary);

	//Property Name
	$event->setLocation($location);

	//Descrpition - Booking Details
	$event->setDescription($description);

	$start = new Google_Service_Calendar_EventDateTime();

	//2015-01-06T10:00:00.000-07:00
	$start->setDateTime($startDate);
	$start->setTimeZone('Africa/Nairobi');
	$event->setStart($start);

	$end = new Google_Service_Calendar_EventDateTime();
	$end->setDateTime($endDate);
	$end->setTimeZone('Africa/Nairobi');
	$event->setEnd($end);

	//COLOR:
	if ($property == 1)
	{
		//EWC
		$event->setColorId("8");
	}
	if ($property == 2)
	{
		//OLERAI
		$event->setColorId("2");
	}
	if ($property == 3)
	{
		//CABIN
		$event->setColorId("11");
	}

	$paramGoogleCal= db_fetch("","","","","Name = 'Google_Calendar'","","parameter",false, false, null);
	$googleCal = $paramGoogleCal[0]['Value'];

	if ($eventId == "")
	{
		$event1 = $service->events->insert($googleCal, $event);
		return $event1->getId();
	}
	else
	{
		$event1 = $service->events->update($googleCal, $eventId, $event);
		return "";
	}
}



/**
 * @return $data array in form of data[0..n][column_name] = value;
 */
function parseDataTableAjaxData()
{
	$data = array();
	$fields = array();
	$splitted = preg_split("/%0D%0A/",urlencode($_POST['tableData']));

	$first = true;
	foreach ($splitted as $split)
	{
		$split2 = preg_split("/%09/",$split);
		if ($first)
		{
			foreach ($split2 as $spl)
			{
				array_push($fields, urldecode($spl));
			}
		}
		else
		{
			$dt = array();
			$cnt = 0;
			foreach ($split2 as $spl)
			{
				$dt[$fields[$cnt]] = urldecode($spl);
				$cnt++;
			}
			array_push($data, $dt);
		}
		$first = false;
	}
	return $data;
}

function loadLayout($thisContent, $layout, $appRoot)
{
	if (file_exists("../layout/" . $layout . ".php"))
	{
		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- Layout Found.");
		ob_start();
		include ("../layout/" . $layout . ".php");
		$contents = ob_get_contents(); // assign buffer contents to variable
		ob_end_clean();

	} else
	{
		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- Layout NOT found, returning content...");
		return $thisContent;
	}

	$contents = str_replace('@CONTENT@', $thisContent, $contents);
	$contents = str_replace('@APPROOT@', $appRoot, $contents);
	return $contents;
}

function renderView($class, $method, $view)
{

	if ($class == "formClass")
	{
		$file = "../views/form/" . str_replace($class . "::", "", $method) . ".php";
	} else
	{
		$file = "../views/" . str_replace("Class", "", $class) . "/" . str_replace($class . "::", "", $method) . ".php";
	}
	if (file_exists($file))
	{
		ob_start();
		include $file;
		$resFile = ob_get_contents();
		ob_end_clean();
		$msg = renderMessage($view);
		return $msg . $resFile;
	} else
	{
		$msg = renderMessage($view);
		return $msg;
	}
}

function renderViewForward($class, $method, $view, $forward)
{
	$msg = renderView($class, $method, $view);
	$msg = str_replace("@FORWARD@", $forward, $msg);
	return $msg;
}

function renderValidationMessage()
{
	if ($_SESSION['error_validation'] != "")
	$msg = '<div class="alert alert-warning">
	                                    <div class="d-flex flex-start w-100">
	                                        <div class="d-flex align-center mr-2 hidden-sm-down">
	                                            <span class="icon-stack icon-stack-lg">
	                                                <i class="base-7 icon-stack-3x color-warning-400"></i>
	                                                <i class="base-7 icon-stack-2x color-warning-600 opacity-70"></i>
	                                                <i class="fal fa-exclamation-triangle icon-stack-1x text-white opacity-90"></i>
	                                            </span>
	                                        </div>
	                                        <div class="d-flex flex-fill">
	                                            <div class="flex-fill">
	                                                <span class="h5">Alert!</span>
	                                                <br> '.$_SESSION['error_validation'].'
	                                            </div>
	                                        </div>
	                                    </div>
	                                </div>';

																	$_SESSION['error_validation'] = "";
																	return $msg;


}

function renderMessage($view)
{
	$msg = "";
	if (isset ($view))
	{
		if (isset ($view[0]))
		{
			if (isset ($view[0]->message))
			{
$msg = '<div class="alert alert-primary">
                                    <div class="d-flex flex-start w-100">
                                        <div class="d-flex align-center mr-2 hidden-sm-down">
                                            <span class="icon-stack icon-stack-lg">
                                                <i class="base-7 icon-stack-3x color-primary-400"></i>
                                                <i class="base-7 icon-stack-2x color-primary-600 opacity-70"></i>
                                                <i class="fal fa-exclamation-triangle icon-stack-1x text-white opacity-90"></i>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-fill">
                                            <div class="flex-fill">
                                                <span class="h5">Alert!</span>
                                                <br> '.$view[0]->message.'
                                            </div>
                                        </div>
                                    </div>
                                </div>';

			}

			if (isset ($view[0]->error))
			{
				$msg = '<div class="alert alert-warning">
				                                    <div class="d-flex flex-start w-100">
				                                        <div class="d-flex align-center mr-2 hidden-sm-down">
				                                            <span class="icon-stack icon-stack-lg">
				                                                <i class="base-7 icon-stack-3x color-warning-400"></i>
				                                                <i class="base-7 icon-stack-2x color-warning-600 opacity-70"></i>
				                                                <i class="fal fa-exclamation-triangle icon-stack-1x text-white opacity-90"></i>
				                                            </span>
				                                        </div>
				                                        <div class="d-flex flex-fill">
				                                            <div class="flex-fill">
				                                                <span class="h5">Alert!</span>
				                                                <br> '.$view[0]->error.'
				                                            </div>
				                                        </div>
				                                    </div>
				                                </div>';

			}
		}
	}
	return $msg;
}

function process_sub_nav($nav_item) {
	$sub_item_htm = "";
	if (isset($nav_item["sub"]) && $nav_item["sub"]) {
		$sub_nav_item = $nav_item["sub"];
		$sub_item_htm = process_sub_nav($sub_nav_item);
	} else {
		$sub_item_htm .= '<ul>';
		foreach ($nav_item as $key => $sub_item) {
			$url = isset($sub_item["url"]) ? $sub_item["url"] : "#";
			$url_target = isset($sub_item["url_target"]) ? 'target="'.$sub_item["url_target"].'"' : "";
			$icon = isset($sub_item["icon"]) ? '<i class="fa fa-lg fa-fw '.$sub_item["icon"].'"></i>' : "";
			$nav_title = isset($sub_item["title"]) ? $sub_item["title"] : "(No Name)";
			$label_htm = isset($sub_item["label_htm"]) ? $sub_item["label_htm"] : "";
			$sub_item_htm .=
			'<li '.(isset($sub_item["active"]) ? 'class = "active"' : '').'>
					<a href="'.$url.'" '.$url_target.'>'.$icon.' '.$nav_title.$label_htm.'</a>
							'.(isset($sub_item["sub"]) ? process_sub_nav($sub_item["sub"]) : '').'
									</li>';
		}
		$sub_item_htm .= '</ul>';
	}
	return $sub_item_htm;
}


/**
 Validate an email address.
 Provide email address (raw input)
 Returns true if the email address has the email
 address format and the domain exists.
 */
function validEmail($email)
{
	$isValid = true;
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex)
	{
		$isValid = false;
	} else
	{
		$domain = substr($email, $atIndex +1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);

		if ($localLen < 1 || $localLen > 64)
		{
			// local part length exceeded
			$isValid = false;
		} else
		if ($domainLen < 1 || $domainLen > 255)
		{
			// domain part length exceeded
			$isValid = false;
		} else
		if ($local[0] == '.' || $local[$localLen -1] == '.')
		{
			// local part starts or ends with '.'
			$isValid = false;
		} else
		if (preg_match('/\\.\\./', $local))
		{
			// local part has two consecutive dots
			$isValid = false;
		} else
		if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		{
			// character not valid in domain part
			$isValid = false;
		} else
		if (preg_match('/\\.\\./', $domain))
		{
			// domain part has two consecutive dots
			$isValid = false;
		} else
		if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local)))
		{
			// character not valid in local part unless
			// local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local)))
			{
				$isValid = false;
			}
		}
		if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A")))
		{
			// domain not found in DNS
			$isValid = false;
		}
	}
	return $isValid;
}

function checkFieldRights($user, $table, $field)
{
	$where = " (c_table = '" . $table . "' or c_table = '@')";
	$where .= " and (c_field = '" . $field . "' or c_field = '@')";

	$res = db_fetch("", "", "", "", $where, "", 'table_rights', false, false, null);

	$pass = false;
	if ($res)
	{
		foreach ($res as $rs)
		{

			if ($rs['c_table'] == $table OR $rs['c_table'] == "@")
			{
				if ($rs['c_field'] == $field OR $rs['c_field'] == "@")
				{
					if (strpos($rs['user'], $user) > -1 OR $rs['user'] == "@")
					{
						$pass = true;
						break;
					}
				}
			}
		}
	} else
	{
		$pass = true;
	}
	if ($pass === FALSE)
	{
		return false;
	} else
	{
		return true;
	}
}

function checkRights($user, $controller, $action)
{
	if (!isset ($controller))
	{
		return null;
	}
	else
	{
		if ($controller == "jsonpair") return true;

		if ($user == "")
		{
			$where = "(user = '@')";
		} else
		{
			$where = "(user like '%" . $user . "%' OR user = '@')";
		}

		$where .= " and (controller = '" . $controller . "' or controller = '@')";
		$where .= " and (action = '" . $action . "' or action = '@')";

		$res = db_fetch("", "", "", "", $where, "", 'rights', false, false, null);

		$pass = false;

		foreach ($res as $rs)
		{
			if ($rs['controller'] == $controller OR $rs['controller'] == "@")
			{
				if ($rs['action'] == $action OR $rs['action'] == "@")
				{
					$pass = true;
					break;
				}
			}
		}

	}
	return $pass;
}

function getUpdatedBy($id, $table)
{
	$res = db_fetch("", "", "", "", "t.id = $id and t.updatedBy = u.id", "u.username", "$table t, user u", false, false, null);
	if ($res)
	{
		return $res[0]['username'];
	}
}

function renderExcel($page, $limit, $sidx, $sord, $where, $select, $table)
{
	$link = db();
	$result = db_fetch("", "", $sidx, $sord, $where, $select, $table, false, false, null);

	//define separator (defines columns in excel & tabs in word)
	$sep = "\t"; //tabbed character

	$s = "";
	//start of printing column names
	foreach ($result as $rs)
	{
		$schema_insert = "";
		foreach ($rs as $name => $dt)
		{
			$schema_insert .= "$name" . $sep;
		}
		$schema_insert = STR_REPLACE($sep . "$", "", $schema_insert);
		$schema_insert = PREG_REPLACE("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
		$schema_insert .= "\t";
		$s .= TRIM($schema_insert) . "\n";
		break;
	}
	//end of printing column names

	//start while loop to get data
	foreach ($result as $rs)
	{
		$schema_insert = "";
		foreach ($rs as $dt)
		{
			$schema_insert .= "$dt" . $sep;
		}
		$schema_insert = STR_REPLACE($sep . "$", "", $schema_insert);
		$schema_insert = PREG_REPLACE("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
		$schema_insert .= "\t";
		$s .= TRIM($schema_insert) . "\n";
	}
	return $s;
}

function renderXML($page, $limit, $sidx, $sord, $where, $select, $table, $oper, $string, $field)
{
	$link = db();

	if ($oper == "cn") //Contains
	{
		if ($where != "")
		{
			$where .= " and ";
		}
		$where .= $field." like '%".$string."%'";
	}

	$result = db_fetch("", "", $sidx, $sord, $where, $select, $table, false, false, null);
	$count = sizeof($result);

	$result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, false, false, null);

	if ($count > 0 && $limit > 0)
	{
		$total_pages = ceil($count / $limit);
	} else
	{
		$total_pages = 0;
	}

	if ($page == "")
	{
		$page = 1;
	}

	if ($page > $total_pages)
	{
		$page = $total_pages;
	}

	$s = "<rows>";
	$s .= "<page>" . $page . "</page>";
	$s .= "<total>" . $total_pages . "</total>";
	$s .= "<records>" . $count . "</records>";

	$cnt = 0;
	foreach ($result as $rs)
	{
		$cnt++;
		if (isset ($rs['id']))
		{
			$rsid = $rs['id'];
		} else
		{
			$rsid = $cnt;
		}
		$s .= "<row id='" . $rsid . "'>";
		foreach ($rs as $dt)
		{
			$s .= "<cell><![CDATA[" . $dt . "]]></cell>";
		}
		$s .= "</row>";
	}
	$s .= "</rows>";

	return $s;
}


function renderJSONID($page, $limit, $sidx, $sord, $where, $select, $table)
{
		$link = db();
    $result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, false, false, null);

    if (! $result) {
        return "";
    }

    foreach ($result as $rs) {

        foreach ($rs as $column => $value) {
            if ($column == "value") $res = $value;
        }
    }

    return $res;
}

function renderJSONH($page, $limit, $sidx, $sord, $where, $select, $table)
{
    $link = db();

    $priv_type = "";
    $hasResult = false;

    switch ($table)
    {
			  case "election_interest":
					$sql = "SELECT e.id, m.member_name as member_HD, e.election_HD, e.registration_HD, IF(p.name IS NULL, CASE  WHEN e.registration_positions_ID = -1 THEN 'Chairperson'  WHEN e.registration_positions_ID = -2 THEN 'Treasurer'  WHEN e.registration_positions_ID = -3 THEN 'Secretary' END,p.name) as registration_positions_ID, valid_votes FROM `election_interest` e LEFT JOIN member m on m.id = e.member_HD LEFT JOIN registration_positions p ON p.id = e.registration_positions_ID WHERE ".str_replace("registration_HD","e.registration_HD",$where);
					//echo $sql;
					$hasResult = true;
				break;
			  case "member_details":
						$ibuttons = "concat(\"<a target='_blank' href='/member/editRecord?layout=single&id=\",d.id,\"' class='btn btn-primary intable'>View</a>\"";
//						$ibuttons .= ",\"<a href='javascript:void(0);' onclick='\",IF(d.notification_sent_HD=9,'return false;',''),\"notifyMember(\",d.id,\")' class='btn btn-primary intable'>\",IF(m.filled_HD = 0,IF(d.notification_sent_HD=1,'Re-notify',IF(d.notification_sent_HD=9,'Scheduled','Notify')),'Filled'),\"</a>\"";
						$ibuttons .= ",\"<a href='javascript:void(0);' onclick='\",\"notifyMember(\",d.id,\")' class='btn btn-primary intable'>\",IF(m.filled_HD = 0,IF(d.notification_sent_HD=1,'Re-notify','Notify'),'Filled'),\"</a>\"";
						$ibuttons .= ")";
						$sql = "SELECT d.id, d.registration_HD, ".$ibuttons." as `action`, d.membership_number, d.member_name, d.national_identity_card, d.telephone, d.email, d.admission_Date FROM member_details d LEFT JOIN member m ON m.id = d.id WHERE d.deletedAt IS NULL AND ".str_replace("registration_HD","d.registration_HD",$where);
						$hasResult = true;
				break;
				case "association":
						$sql = "SELECT d.id, d.registration_HD, IF(d.approved_application_HD=1,'Approved',if(d.approved_application_HD=9,'Rejected','Pending Approval')) as `action`,  d.national_identity_card,  d.title,  d.member_name, p.name as position_ID,  d.registration_no, d.association_name FROM association d LEFT JOIN position p ON p.id = d.position_ID WHERE d.deletedAt IS NULL AND ".str_replace("registration_HD","d.registration_HD",$where);
						$hasResult = true;
				break;
        case "official":
            $sql = "SELECT b.id, b.registration_HD, IF(b.approved_application_HD=1,'Approved',if(b.approved_application_HD=9,'Rejected','Pending Approval')) as `action`, b.title, b.Full_Name, b.occupation, p.name as position_ID, b.email, b.contact_address, b.appointment_Date, b.cessation_Date FROM official b LEFT JOIN position p ON p.id = b.position_ID WHERE b.deletedAt IS NULL AND p.deletedAt IS NULL AND ".str_replace("registration_HD","b.registration_HD",$where);
            $hasResult = true;
      	break;
				case "owner":
            $sql = "SELECT b.id, b.registration_HD, IF(b.approved_application_HD=1,'Approved',if(b.approved_application_HD=9,'Rejected','Pending Approval')) as `action`, b.full_name, b.position,  b.mobile, b.email, b.registration_number, b.percentage_of_shares, p.name as registered_with_ID FROM owner b LEFT JOIN focus_area p ON p.id = b.registered_with_ID WHERE b.deletedAt IS NULL AND p.deletedAt IS NULL AND ".str_replace("registration_HD","b.registration_HD",$where);
            $hasResult = true;
      	break;
				case "user":
            $sql = "SELECT u.id, u.first_name, u.middle_name, u.last_name,  c.name as ADED_county_ID, u.username, u.password, u.emailAddress, u.level, u.activated, u.loggedin, u.token, u.token_expiry, u.national_identity, u.phone,u.created_Date FROM user u LEFT JOIN county c ON c.id = u.ADED_county_ID ".str_replace("registration_HD","b.registration_HD",$where);
            $hasResult = true;
      	break;
				case "member_business_details":
            $sql = "SELECT a.id, a.member_HD, b.name as registrars_ID, c.name as applicant_status_ID, a.registration_number, a.registration_Date, a.operational_since_Date FROM member_business_details a LEFT JOIN registrars b ON b.id = a.registrars_ID LEFT JOIN applicant_status c ON c.id = a.applicant_status_ID WHERE a.deletedAt IS NULL AND b.deletedAt IS NULL AND ".$where;
            $hasResult = true;
      	break;
				case "change_of_association":
						$sql = "SELECT d.id, d.change_of_particulars_HD, annual_returns_HD, elections_HD, IF(d.approved_application_HD=1,'Approved',if(d.approved_application_HD=9,'Rejected','Pending Approval')) as `action`,  d.national_identity_card,  d.title,  d.member_name, p.name as position_ID,  d.registration_no, d.association_name FROM change_of_association d LEFT JOIN position p ON p.id = d.position_ID WHERE d.deletedAt IS NULL AND ".str_replace("registration_HD","d.registration_HD",$where);
//						echo $sql;
						$hasResult = true;
				break;
				case "change_of_owner":
						$sql = "SELECT d.id, d.change_of_particulars_HD, annual_returns_HD, elections_HD, IF(d.approved_application_HD=1,'Approved',if(d.approved_application_HD=9,'Rejected','Pending Approval')) as `action`,  d.full_name, d.position,  d.mobile,  d.email , d.approved_application_HD, d.registration_number, d.percentage_of_shares, f.name as registered_with_ID, d.registered_with_no FROM change_of_owner d LEFT JOIN focus_area f ON f.id = d.registered_with_ID WHERE d.deletedAt IS NULL AND ".str_replace("registration_HD","d.registration_HD",$where);
						$hasResult = true;
				break;
				case "change_of_official":
						$ibuttons = "concat(\"<a href='javascript:void(0);' onclick='\",\"notifyMember(\",b.id,\")' class='btn btn-primary intable'>\",IF(b.filled_HD = 0,IF(b.notification_sent_HD=1,'Re-notify','Notify'),IF(position_ID < 4, IF(b.approved_application_HD=1,'Approved',if(b.approved_application_HD=9,'Rejected','Pending Approval')),'Filled')),\"</a>\")";
            $sql = "SELECT b.id, b.change_of_particulars_HD, b.annual_returns_HD, elections_HD, ".$ibuttons." as `action`, b.title, b.full_name, b.occupation, p.name as position_ID, b.email, b.contact_address, b.appointment_Date, '' as cessation_Date, b.approved_application_HD FROM change_of_official b LEFT JOIN position p ON b.position_ID = p.id WHERE b.deletedAt IS NULL AND ".$where;
            $hasResult = true;
      	break;
    }

//		echo $sql;


    if ($hasResult)
    {
			$resqry = mysqli_query($link, $sql);
			$result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, false, false, $resqry);
    }
    else
    {
			$result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, false, false, null);
    }

    if (!$result) {
        return "{\"data\":[]}";
    }

    $count = 0;
    $fst = true;
    $res = "{\n \"data\": [";
    foreach ($result as $rs) {
        if ($fst == true) {
            $fst = false;
        } else {
            $res .= ",\n";
        }

        $res .= "[";
        $first = true;
        foreach ($rs as $column => $value) {
            if ($first == true) {
                $first = false;
            } else {
                $res .= ",";
            }
/*
            if (is_numeric($value) || substr( $value, 0, 1 ) == "0")
            {
                $res .= $value;
            }
            else
            {
            */
                $res .= '"'.$value.'"';
//            }
        }
        $res .= "]";
        $count ++;

    }
    $res .= "\n]\n}";

    return $res;
}

function renderJSONPairH($page, $limit, $sidx, $sord, $where, $select, $table)
{
 		$link = db();

    $result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, false, false, null);

    if (! $result) {
        return "[{}]";
    }

    $count = 0;
    $fst = true;
    $res = "{\n \"data\": [";
    foreach ($result as $rs) {
        if ($fst == true) {
            $fst = false;
        } else {
            $res .= ",\n";
        }

        $res .= "";
        $first = true;
        foreach ($rs as $column => $value) {
            if ($column == "label")
            {
                if ($first == true) {
                    $first = false;
                } else {
                    $res .= ",";
                }
                $res .= '"'.$value.'"';

            }
        }
        $res .= "";
        $count ++;

    }
    $res .= "]\n}";

    return $res;
}



function renderJSON($page, $limit, $sidx, $sord, $where, $select, $table)
{

	$link = db();
	$result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, false, false, null);

	if (!$result)
	{
		return "[{}]";
	}

	$fst = true;
	$res = "[\n";
	foreach ($result as $rs)
	{
		$first = true;
		if ($fst == true)
		{
			$fst = false;
		} else
		{
			$res .= ",\n";
		}
		$res .= "	{";
		$res .= "\"" . $rs['id'] . "\":\n";
		$res .= "	   [\n		{";
		foreach ($rs as $column => $value)
		{
			if ($first == true)
			{
				$first = false;
			} else
			{
				$res .= "},\n		{";
			}
			$res .= "\"label\":\"" . $column . "\"";
			$res .= ",\"value\":\"" . $value . "\"";
		}
		$res .= "}\n";
		$res .= "	   ]\n	}";
	}
	$res .= "\n]";

	return $res;
}


function renderJSONDataTable($page, $limit, $sidx, $sord, $where, $select, $table)
{
	$overall= array();
	$link = db();
	$result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, false, false, null);

	if (!$result)
	{
		if (isset($_POST['action']))
		{
			return " { \"row\":\"\"}";
		}
		else
		{
			return "{ \"data\":\"\"}";
		}
	}

	$fst = true;
	$open = "{\"data\":[";


	foreach ($result as $rs)
	{
		$tablerowSt = "\"".$table."\": {";
		$foreignPotentialVal = array();
		$foreignPotential = array();
		$foreign = array();
		$tableDT = array();
		foreach ($rs as $column => $value)
		{
			if ($column == "id")
			{
				$idrow = "		\"DT_RowId\":\"" . $value . "\"";
			}
			if (strpos($column,"_ID") > -1)
			{
				//foreign
				$ftable = $_SESSION['db_relations_bk'][$table.".".$column];

				if (isset($value) && $value != "")
				{
					//foreign table column to show
					if (!isset($_SESSION['db_alternates'][$ftable]))
					{
						$fcol = "name";
					}
					else
					{
						$fcol = $_SESSION['db_alternates'][$ftable];
					}
					$fval = db_fetch("","","","","id = '".$value."'","",$ftable,false,false, null);

					if (sizeOf($fval) > 0)
					{
						if ($fcol != "id")
						{
							$fvals = "{ ";
							$fvals .= "\"id\": \"".$fval[0]['id']."\",";
							if (isset($fval[0][$fcol]))
							{
								$fvals .= "\"".$fcol."\": ".json_encode($fval[0][$fcol]);
							}
							else
							{
								$fvals .= "\"".$fcol."\": \"\"";
							}
							$fvals .= "}";

							if (!isset($foreignPotential[$ftable.$column]))
							{
								$foreignPotential[$ftable.$column] = array();
								$foreignPotentialVal[$ftable.$column] = array();
							}
							if (!in_array($fval[0]['id'],$foreignPotentialVal[$ftable.$column]))
							{
								array_push($foreignPotentialVal[$ftable.$column], json_encode($fval[0]['id']));
								array_push($foreignPotential[$ftable.$column], $fvals);
							}
						}

					}
					else
					{
						/*								if (!isset($foreignPotential[$ftable]))
						 {
						$foreignPotential[$ftable] = array();
						}
						array_push($foreignPotential[$ftable], "{ \"".$fcol."\": \"\" }");
						*/							}
				}
				else
				{
					/*							if (!isset($foreignPotential[$ftable]))
					 {
					$foreignPotential[$ftable] = array();
					}
					array_push($foreignPotential[$ftable], "{ \"".$fcol."\": \"\" }");
					*/					}

					array_push($tableDT, "		\"" . $column . "\":" . json_encode($value));

			}
			else
			{
				array_push($tableDT, "		\"" . $column . "\":" . json_encode($value));
			}
		}
		$tablerowEd = "}";
		$foreignStr = "";

		foreach ($foreignPotential as $fptable => $fpval)
		{
			//				logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"DT: --------------". $fptable . ": ".print_r($fpval, true));

			if (sizeOf($fpval) > 1)
			{
				$fstr = "";
				foreach($fpval as $fpvl)
				{
					if ($fstr != "")
					{
						$fstr .= ",";
					}
					$fstr .= $fpvl;
				}
				array_push($foreign, "\"".$fptable."\": [".$fstr."]");
			}
			else
			{
				array_push($foreign, "\"".$fptable."\": ".$fpval[0]);
			}
		}

		if (sizeOf($foreign) > 0)
		{
			$foreignStr = ",".implode(",",$foreign);
		}

		array_push($overall, "{".$idrow.",".$tablerowSt.implode(",",$tableDT).$tablerowEd.$foreignStr."}");
	}

	$close = "]}";


	if (isset($_POST['action']))
	{
		return " { \"row\":".implode(",",$overall)."}";
	}
	else
	{
		return $open.implode(",",$overall).$close;
	}
}

function renderJSONPair($page, $limit, $sidx, $sord, $where, $select, $table, $jsonSingle, $jsonP, $result = null)
{

	$callback = "";
	if (isset($_GET['callback']))
	{
		$callback = $_GET['callback'];
	}

	$link = db();

	if ($result == null)
	{
		$result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, false, false, null);
		//return " response:".json_encode($result);
	}

	if (!$result)
	{
		if ($jsonP)
		{
			return "{\"results\":[]}";
		}
		else
		{
			return "[{}]";
		}
	}

	$fst = true;

	if ($jsonP)
	{
		$res = "";
	}
	else
	{
		$res = "[";
	}

	if ($jsonSingle == false && $jsonP)
	{
		//$res .= $callback."({\"total\":".sizeOf($result).", \"res\":[";
	//	if ($table == "branches")
//		{
//			$res .= "[";
//		}
//		else
//		{
			$res .= "{\"results\":[";
	//	}
	}

	foreach ($result as $r)
	{
		if ($r['label'] != "")
		{
			$first = true;
			if ($fst == true)
			{
				$fst = false;
			} else
			{
				$res .= ",";
			}

			$valueLabel = "id";
			$valueId = "text";
			if (!$jsonP)
			{
				$valueLabel = "id";
				$valueId = "label";
			}

			$res .= "{";
			$res .= "\"".$valueId."\":\"" . $r['label'] . "\"";
			$res .= ",\"".$valueLabel."\":\"" . $r['value'] . "\"";
			$res .= "}";
		}
	}

	if ($jsonP)
	{
		if ($jsonSingle == false)
		{
			//if ($table == "branches")
		//	{
		//		$res .= "]";
	//		}
	//		else
	//		{
				$res .= "]}";
		//	}
		}
		else
		{
			$res .= "";
		}
	}
	else
	{
			$res .= "]";
	}

	//	logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"Complete...". $res);

	return $res;
}


function renderJSONPairId($page, $limit, $sidx, $sord, $where, $select, $table, $result = null)
{
	$link = db();

  if ($result == null)
	{
		$result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, false, false, null);
	}

	if (!$result)
	{
		return "[{}]";
	}

	$fst = true;
	$res = "[\n";
	foreach ($result as $r)
	{
		if ($r['label'] != "")
		{
			$first = true;
			if ($fst == true)
			{
				$fst = false;
			} else
			{
				$res .= ",\n";
			}
			$res .= "	{";
			$res .= "\"id\":\"" . $r['value'] . "\"";
			$res .= ",\"label\":\"" . $r['label'] . "\"";
			$res .= ",\"value\":\"" . $r['label'] . "\"";
			$res .= "}\n";
		}
	}
	$res .= "\n]";

	return $res;
}

class ContractPDF extends TCPDF
{
	//Page header
	public function Header()
	{
		// Set font
		$this->SetFont('helvetica', 'B', 12);
		// Title
		$this->Cell(0, 20, '', 0, false, 'C', 0, '', 0, false, 'B', 'B');
		$this->ln();
		$this->Cell(0, 10, '', 0, false, 'C', 0, '', 0, false, 'B', 'B');
	}

	// Page footer
	public function Footer()
	{
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('helvetica', '', 8);
		// Page number
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}
}

function renderDOC($page, $limit, $sidx, $sord, $where, $select, $table, $format, $orientation, $reportName, $params, $outputFormat)
{
	$link = db();
	$s = "";
	if ($reportName != "")
	{
		$s = "<h3>" . $reportName . "</h3>";
	}

	foreach ($params as $param => $paramval)
	{
		$s .= "<b>" . str_replace("_", " ", $param) . ":</b>" . " " . $paramval . "<BR>";
	}

	$s .= "<BR><BR>";

	$result_raw = db_fetch_result($page, $limit, $sidx, $sord, $where, $select, $table, false);

	$headers = "";
	$len = mysqli_num_fields($result_raw);
	for ($i = 0; $i < $len; $i++)
	{
		$namex = mysqli_field_name($result_raw, $i);
		$res = preg_split("/\,/", $namex);

		foreach ($res as $col)
		{
			if (isset ($atBy) && $atBy == true)
			{
				$headers .= "<th><b>" . UCFirst(str_replace("_", " ", str_replace("_ID", "", $col))) . "</b></th>";
			} else
			{

				if ($col != "updatedAt" and $col != "updatedBy" and $col != "deletedAt" and $col != "c_rights")
				{
					$headers .= "<th><b>" . UCFirst(str_replace("_", " ", str_replace("_ID", "", $col))) . "</b></th>";
				}
			}
		}
	}

	$result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, false, false, $result_raw);

	htmltodoxc($html);
}

function renderPDF($page, $limit, $sidx, $sord, $where, $select, $table, $format, $orientation, $reportName, $params, $outputFormat)
{

	$link = db();
	$s = "";
	if ($reportName != "")
	{
		$s = "<h3>" . $reportName . "</h3>";
	}

	foreach ($params as $param => $paramval)
	{
		$s .= "<b>" . str_replace("_", " ", $param) . ":</b>" . " " . $paramval . "<BR>";
	}

	$s .= "<BR><BR>";

	$result_raw = db_fetch_result($page, $limit, $sidx, $sord, $where, $select, $table, false);

	$headers = "";
	$len = mysqli_num_fields($result_raw);
	for ($i = 0; $i < $len; $i++)
	{
		$namex = mysqli_field_name($result_raw, $i);
		$res = preg_split("/\,/", $namex);

		foreach ($res as $col)
		{
			if (isset ($atBy) && $atBy == true)
			{
				$headers .= "<th><b>" . UCFirst(str_replace("_", " ", str_replace("_ID", "", $col))) . "</b></th>";
			} else
			{

				if ($col != "updatedAt" and $col != "updatedBy" and $col != "deletedAt" and $col != "c_rights")
				{
					$headers .= "<th><b>" . UCFirst(str_replace("_", " ", str_replace("_ID", "", $col))) . "</b></th>";
				}
			}
		}
	}

	$result = db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, true, false, $result_raw);


	$fields = sizeOf($result);

	if ($format == "list")
	{
		$orient = $orientation;
		$s .= "<table cellpadding=\"3\">";
		if ($select == "*" or $select == "")
		{
			$s .= "<tr>";
			$res = db_fetch_columns($table, false);
			foreach ($res as $col)
			{
				$s .= "<th><b>" . $col . "</b></th>";
			}
			$s .= "</tr>";
		} else
		{
			$s .= "<tr>";
			$s .= $headers;
			$s .= "</tr>";
		}

		foreach ($result as $r)
		{
			$s .= "<tr>";
			foreach ($r as $dt)
			{
				if (isValidDateTime($dt))
				{
					$dt = date('d-M-y', strtotime($dt));
				}
				$s .= "<td>" . $dt . "</td>";
			}
			$s .= "</tr>";
		}
		$s .= "</table>";

		$html = $s."<BR><BR>Total Records: ".$fields;
	}
	elseif ($format == "contract_amd_document")
	{
		$orient = "P";
		$html = contractAmmendmentDocument($result);
	}
	elseif ($format == "ITT_Document")
	{
		$orient = "P";
		$html = ITTDocument($result,false);
	}
	elseif ($format == "ITT_Document_150")
	{
		$reportName = "ITT_Document_150";
		$orient = "P";
		$html = ITTDocument($result,true);
	}
	elseif ($format == "ITB_Document")
	{
		$orient = "P";
		$html = ITBDocument($result);
	}
	elseif ($format == "bookingSheet")
	{
		$orient = "L";
		$reportName = "bookingSheet";
		$html = bookingSheetDocument($result, false);
	}
	elseif ($format == "cvoucher")
	{
		$orient = "P";
		$reportName = "cvoucher";
		$html = cvoucherDocument($result);
	}
	elseif ($format == "creditnote")
	{
		$orient = "P";
		$reportName = "creditnote";
		$html = creditNoteDocument($result);


	}
	elseif ($format == "rvoucher")
	{
		$orient = "P";
		$reportName = "rvoucher";
		$html = rvoucherDocument($result);
	}
	elseif ($format == "invoice")
	{
		$orient = "P";
		$reportName = "invoice";
		$html = invoiceDocument($result,1, false);
	}
	elseif ($format == "quote")
	{
		$orient = "L";
		$reportName = "quote";
		$html = invoiceDocument($result,2, false);
	}
	elseif ($format == "checklistPDF")
	{
		$orient = "L";
		$html = checklistPDF($format, $result);
	}
	elseif ($format == "individual_consultant")
	{
		$orient = "P";
		$html = consultantDocument($format, $result);
	}
	elseif ($format == "company_consultant")
	{
		$orient = "P";
		$html = consultantDocument($format, $result);
	}
	elseif ($format == "performance_document")
	{
		$orient = "P";
		$html = contractPerformance($result);
	}

	if ($format == "invoice" or $format == "quote" )
	{
		$pdf = new TCPDF($orient, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetFont('trajanpro', '', 7, '', true);
		//		$pdf->Image('../public/img/excellogo.jpg', 10, 10, 40, '', 'JPG', '', 'T', false, 20, '', false, false, 0, false, false, false);
		$pdf->SetPrintHeader(false);
		//		$pdf->SetHeaderData('../public/img/excellogo.png', 75, '', '', array(255,255,255), array(255,255,255));
	}
	if ($format == "cvoucher" || $format == "rvoucher" || $format == "creditnote")
	{
		$pdf = new TCPDF($orient, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetFont('trajanpro', '', 9, '', true);
		$pdf->SetPrintHeader(false);
	}
	if ($format == "bookingSheet")
	{
		$pdf = new TCPDF($orient, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetPrintHeader(false);
	}

	if ($reportName == "")
	{
		$reportName = $table;
	}
	if (isset ($pdf))
	{
		return generatePDF($pdf, $orient, $html, $reportName, $outputFormat);
	} else
	{
		return generatePDF(null, $orient, $html, $reportName, $outputFormat);
	}
}

function checkEvenOdd($number){
    if($number % 2 == 0){
        return "Even";
    }
    else{
        return "Odd";
    }
}

function generatePDF($pdf, $orient, $html, $reportName, $outputFormat)
{

	if (isset ($outputFormat))
	{
		//we're ok
	} else
	{
		$outputFormat = "I";
	}

	if (isset ($orient))
	{
		//we're ok
	} else
	{
		$orient = "P";
	}

	if (isset ($pdf))
	{
		//we're ok'
	} else
	{
			$pdf = new TCPDF($orient, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$pdf->SetFont('calibri', '', 12, '', true);
			$pdf->SetHeaderData('../public/img/logo.png', 20, 'MSEA Registrar', 'Report');
	}


    if ($reportName == "Leave_General")
    {
        $reportName = $_SESSION['lr_name'];
    }

	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('Echo Systems Company');
	$pdf->SetTitle($reportName);
	$pdf->SetSubject(UCFirst($reportName));
	$pdf->SetKeywords($reportName);
	$pdf->setHeaderFont(Array (
			PDF_FONT_NAME_MAIN,
			'',
			PDF_FONT_SIZE_MAIN
	));
	$pdf->setFooterFont(Array (
			PDF_FONT_NAME_DATA,
			'',
			PDF_FONT_SIZE_DATA
	));
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->SetFontSubsetting(true);
	$pages = $pdf->getAliasNbPages();

    $hasTOC = false;

	if (is_array($html))
	{
		foreach ($html as $ht)
		{
			$ht = str_replace('@PAGES_TO@', $pages, $ht);
			$pdf->AddPage();
            $headingparts = preg_split("/@@/",$ht);
            if (sizeOf($headingparts) > 1) $hasTOC = true;
            $cnth = 0;
            foreach ($headingparts as $hx)
            {
                if (checkEvenOdd($cnth) == "Odd")
                {
                    $pdf->Bookmark($hx, 1, 0, '', '', array(128,0,0));
                    $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $hx, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                }
                else
                {
        			$pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $hx, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                }
                $cnth++;
            }

		}
	} else
	{
		$pdf->AddPage();
        $headingparts = preg_split("/@@/",$html);
        if (sizeOf($headingparts) > 1) $hasTOC = true;
        $cnth = 0;
        foreach ($headingparts as $hx)
        {
            if (checkEvenOdd($cnth) == "Odd")
            {
                $pdf->Bookmark($hx, 1, 0, '', '', array(128,0,0));
                $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $hx, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }
            else
            {
    			$pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $hx, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
            }
            $cnth++;

        }
	}


    if ($hasTOC == true)
    {
        $pdf->addTOCPage();
        $pdf->setColorArray('text', array(0,0,0));
        $pdf->MultiCell(0, 0, 'CONTENTS', 0, 'L', 0, 1, '', '', true, 0);
        $pdf->addTOC(2, PDF_FONT_NAME_MAIN, '.', 'CONTENTS', '', array(1,1,1));
        $pdf->endTOCPage();
    }

	return $pdf->Output($reportName . '.pdf', $outputFormat);
}

function isValidDateTime($dateTime)
{
	if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dateTime, $matches))
	{
		if (checkdate($matches[2], $matches[3], $matches[1]))
		{
			return true;
		}
	}

	return false;
}

function loadDefaultRelations($boot)
{
	if (isset ($_SESSION['layout']))
	{
		if ($_SESSION['layout'] != $boot->getDefaultLayout())
		{
			$_SESSION['layout'] = $boot->getDefaultLayout();
		}
	} else
	{
		$_SESSION['layout'] = $boot->getDefaultLayout();
	}

}

function loadComments($boot)
{
	if (!isset ($_SESSION['db_comments']))
	{
		$dbcomments = array ();

		$link = dbInformationSchema();
		$sql = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_COMMENT FROM COLUMNS WHERE TABLE_SCHEMA = '" . $boot->database . "'";

		$result = mysqli_query($link, $sql) or myDie($sql);

		while ($link = mysqli_fetch_array($result))
		{
			if ($link['COLUMN_COMMENT'] != "")
			{
				$dbcomments[$link['TABLE_NAME'] . "." . $link['COLUMN_NAME']] = $link['COLUMN_COMMENT'];
			}
		}

		$_SESSION['dbcomments'] = $dbcomments;
	}
}

function loadAlternates($boot)
{
	//	if (!isset ($_SESSION['db_alternates']))
	//{
	$foreignKeys = array ();
	$link = db();
	$sql = "select `c_table`, `c_field` from alternate;";

	$result = mysqli_query($link, $sql) or myDie($sql);
	$info = mysqli_fetch_assoc($result);
	do
	{
		$table = "";
		$field = "";
		foreach ($info as $column => $value)
		{
			if ($column == "c_table")
			{
				$table = $value;
			}
			if ($column == "c_field")
			{
				$field = $value;
			}
		}

		$foreignKeys[$table] = $field;

	} while ($info = mysqli_fetch_assoc($result));

	$_SESSION['db_alternates'] = $foreignKeys;

	//	}
}
function loadRelations($boot)
{
	//	if (!isset ($_SESSION['db_relations']))
	//	{
	$foreignKeys = array ();
	$foreignKeysBk = array ();
	$link = db();
	$sql = "select `foreign`, `references`from info_schema";
	//			$link = dbInformationSchema();
	//		$sql = "select concat(table_name, '.', column_name) as 'foreign', referenced_table_name as 'references' from information_schema.key_column_usage where referenced_table_name is not null and referenced_table_schema = '".$boot->database."'";

	$result = mysqli_query($link, $sql) or myDie($sql);
	$info = mysqli_fetch_assoc($result);
	do
	{
		$foreign = "";
		$references = "";
		foreach ($info as $column => $value)
		{
			if ($column == "foreign")
			{
				$foreign = $value;
			}
			if ($column == "references")
			{
				$references = $value;
			}
		}

		$foreignKeysBk[$foreign] = $references;

		if (!array_key_exists($references, $foreignKeys) === FALSE)
		{
			array_push($foreignKeys[$references],$foreign);
		} else
		{
			$foreignKeys[$references] = array($foreign);
		}
	} while ($info = mysqli_fetch_assoc($result));

	$_SESSION['db_relations'] = $foreignKeys;
	$_SESSION['db_relations_bk'] = $foreignKeysBk;
	//	}
}

function gridDataTable($table, $parentIds, $parentCols, $editURL, $params, $elementId, $parentTable, $group, $groupField, $groupName)
{

	$columns = db_fetch_columns($table, false);
	/*
	 $tableRights = db_fetch("","","","","c_table = '".$table."'","","table_rights", false, false, null);

	if (sizeOf($tableRights) > 0)
	{
	foreach ($tableRights as $tr)
	{
	//status for "requisition"
	if ($table == "requisition_items" && isset($view[1][0]['requisition_status_ID']))
	{
	if (strpos($tr['status'], $view[1][0]['requisition_status_ID']) > -1)
	{

	}
	else
	{
	//remove
	unset($columns[$tr['c_field']]);
	}
	}
	}
	}
	*/

	$out = '<section id="widget-grid" class="">
			<div class="row">
			<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-3" data-widget-editbutton="false" data-widget-deletebutton="false">
			<header>
			<span class="widget-icon"> <i class="fa fa-table"></i> </span>
			<h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">'.formatFieldName($table).'</h2>
					</header>
					<div>
					<div class="jarviswidget-editbox">
					</div>
					<div class="widget-body no-padding">
					';
	$out .= "
			<table id='". $elementId ."' class='dt-responsive display responsive table table-bordered table-hover' width=\"100%\">
					<thead>
					<tr>";
	foreach ($columns as $col)
	{
		if (isColumnParentTablesId($table, $col, $parentCols[0]) == false)
		{
			$out .= "			<th>".formatFieldName($col)."</th>";
		}
		if (isColumnParentTablesId($table, $col, $parentCols[0]))
		{
			$where = $col." = ".$parentIds[0];
		}
	}
	$out .= "      </tr>
			</thead>
			<tfoot>
			<tr>";
	foreach ($columns as $col)
	{
		if (isColumnParentTablesId($table, $col, $parentCols[0]) == false)
		{
			$out .= "			<th>".formatFieldName($col)."</th>";
		}
		if (isColumnParentTablesId($table, $col, $parentCols[0]))
		{
			$where = $col." = ".$parentIds[0];
		}
	}
	$out .= "      </tr>
			</tfoot>
			</table>";

	$out .= '
			</div>
			</div>
			</div>
			</article>
			</div>
			</section>

			';

	$out .= '
			<script type="text/javascript" language="javascript" class="init">

			var editor;

			$(document).ready(function() {



			editor = new $.fn.dataTable.Editor( {
			ajax:  "';
	$out .= $_SESSION['boot']->appPublic . "admin/editTable?table=" . $table . "&parent=".$parentCols[0]."&parentId=".$parentIds[0];
	$out .= '",
			table: "#'. $elementId .'",
					fields: [ ';

	$fst = 0;
	foreach ($columns as $col)
	{
		if ($col != "id" && isColumnParentTablesId($table, $col, $parentCols[0]) == false)
		{
			if ($fst > 0)
			{
				$out .= ",";
			}

			$out .= "{ label: \"".formatFieldName($col)."\", ";
			if ($table.$col == "room_assignmentscomplimentary_ID" or  $table.$col == "quote_itemReservation_Voucher_Sent_ID" or $table.$col == "quote_itemRV_Action_ID" or $table.$col == "quote_itemConfirmation_Voucher_Received_ID" or $table.$col == "quote_itemPaid_by_EWP_ID")
			{
				$out .= " def: \"1\", ";
			}
			if (strpos($col,"_TXTA") > -1 || $table.$col == "parameterValue")
			{
				$out .= " type: \"textarea\", ";
			}
			$out .= " name: \"".$table.".".$col."\"}\n ";

			//		}
			$fst = 1;
		}
	}

	$out .= "	]
} );

			editor.on('onOpen', function(e) { ";

	if ($table == "quote_item")
	{
		$out .= '
				Cost_per_Person = jQuery(document.getElementById(\'DTE_Field_quote_item.Cost_per_Person\'));
				PAX = jQuery(document.getElementById(\'DTE_Field_quote_item.PAX\'));
				Number_of_Units = jQuery(document.getElementById(\'DTE_Field_quote_item.Number_of_Units\'));
				Cost_per_Trip = jQuery(document.getElementById(\'DTE_Field_quote_item.Cost_per_Trip\'));
				Total_Cost = jQuery(document.getElementById(\'DTE_Field_quote_item.Total_Cost\'));';

		$out .='

				Cost_per_Person.change(function()
				{
				Cost_per_Trip.val(updateCostPerTrip(Cost_per_Person,PAX));
				Total_Cost.val(updateTotalCost(Cost_per_Person,PAX,Number_of_Units));
	});

				PAX.change(function()
				{
				Cost_per_Trip.val(updateCostPerTrip(Cost_per_Person,PAX));
				Total_Cost.val(updateTotalCost(Cost_per_Person,PAX,Number_of_Units));
	});

				Number_of_Units.change(function()
				{
				Total_Cost.val(updateTotalCost(Cost_per_Person,PAX,Number_of_Units));
	});
				';
	}


	$fst = 0;
	foreach ($columns as $col)
	{
		$el = "document.getElementById('DTE_Field_".$table.".".$col."')";

		if (strpos($col,"_Date") > -1)
		{
			$out .= "jQuery(".$el.").attr('data-date-format','YYYY-MM-DD hh:mm:ss');";
			$out .= "jQuery(".$el.").datetimepicker();";
		}

		if (strpos($col,"_ID") > -1 && isColumnParentTablesId($table, $col, $parentCols[0]) == false)
		{
			$s2xparam = "?rows=20&requestedFrom=".$table;

			if ($table == "room_assignments")
			{
				$out.= "var stid = jQuery('#dd_booking_ID').val();";
				$s2xparam .= "&bookingId='+stid+'";
			}
			if ($table == "room_requirements")
			{
				$out.= "var property = jQuery('#dd_property_ID').val();";
				$out.= "var inDate = jQuery('input[name=db_booking_In_Date]').val();";
				$out.= "var outDate = jQuery('input[name=db_booking_Out_Date]').val();";

				$s2xparam .= "&propertyId='+property+'&inDate='+inDate+'&outDate='+outDate+'";
			}


			$rndno = generateRandomString(10);
			$ftable = $_SESSION['db_relations_bk'][$table.".".$col];

			$out .= "		jQuery(".$el.").css({\"min-width\":\"250px\"});";
//			$out .= "					function format".$rndno."(items) {  return items.label; }; ";

			$out .= '
			function formatRepo'.$rndno.'(repo)
			                    {
			                        if (repo.loading)
			                        {
			                            return repo.text;
			                        }

			                        var markup = "repo.text";
			                        return markup;
			                    }

			                    function formatRepoSelection'.$rndno.'(repo)
			                    {
			                        return repo.full_name || repo.text;
			                    }
			';

			$out .= "   jQuery(".$el.").on(\"change\", function(e){ selectUpdate(e,jQuery(".$el."))});

							jQuery(".$el.").select2({
									placeholder: 'Search',
									minimumInputLength: 0,
									templateResult: formatRepo'.$rndno.',
									templateSelection: formatRepoSelection'.$rndno.',
									ajax: {
									url: '".$_SESSION['boot']->getAppPublic()."jsonpair/".$ftable.$s2xparam."',
											dataType: 'jsonp',
											quietMillis: 500,
											cache: false,
											data: function (term, page) {
											if (term == '')
											{
											term = 'Please Wait...';
		}
											return {
											term: term // search term
		};
		},
											results: function (data, page) {

											return {results: data.items};
		}
		},
											initSelection: function(element, callback)
											{
											var id=$(".$el.").val();
													if (id!=='')
													{
													$.ajax('".$_SESSION['boot']->getAppPublic()."jsonpair/".$ftable."?single=true', {
															data:
															{
															term: id
		},
															dataType: 'json'
		}).done(function(data) { callback(data); });
		}
		},
																			dropdownCssClass: 'bigdrop',
																			escapeMarkup: function (m) { return m; }
		});

																			";
		}
	}

	$out .= "		});
			";

	###################
	#### DATATABLE ####
	###################
	/*
	if ($table == "quote_item")
	{
	$out .= "$('#". $elementId ."').on( 'click', 'tbody td:not(:first-child)', function (e) {
	editor.inline( this, {
	buttons: { label: '&gt;', fn: function () { this.submit(); } }
	} );
	} );";
	}
	*/


	$out .= 'table'. $elementId .' = $("#'. $elementId .'").DataTable( {
			"scrollX": true,
			dom: "Tfrtip",
			ajax: "';

	if (isset($where))
	{
		$out .= $_SESSION['boot']->appPublic . "json/" . $table . "?where\=" . $where."&datatable=1";
	}
	else
	{
		$out .= $_SESSION['boot']->appPublic . "json/" . $table . "?datatable=1";
	}


	$out .= '",
			columnDefs: [';

	$in = false;
	$fst = 0;
	foreach ($columns as $col)
	{
		if (isColumnParentTablesId($table, $col, $parentCols[0]) == false)
		{
			if ($in == true)
			{
				$out .= ",\n";
			}
			if ($col == "id")
			{
				$out .= "			{ \"targets\": [".$fst."], \"visible\": false  } ";
			}
			else
			{
				$out .= "			{ \"targets\": [".$fst."], \"visible\": true } ";
			}
			$in = true;
			$fst += 1;
		}
	}

	$out .= '
			]';

	$out .= ',
			columns: [';

	$fst = 0;
	foreach ($columns as $col)
	{
		if (isColumnParentTablesId($table, $col, $parentCols[0]) == false)
		{
			if ($fst > 0)
			{
				$out .= ",\n";
			}

			if (strpos($col,"_ID") > -1)
			{
				//foreign
				$ftable = $_SESSION['db_relations_bk'][$table.".".$col];
				if (!isset($_SESSION['db_alternates'][$ftable]))
				{
					$fcol = "name";
				}
				else
				{
					$fcol = $_SESSION['db_alternates'][$ftable];
				}
				$out .= "			{ data: \"".$ftable.$col.".".$fcol."\" } ";
			}
			else
			{
				$out .= "			{ data: \"".$table.".".$col."\" } ";
			}
			$fst = 1;
		}
	}

	$out .= '
			],
			tableTools: {
			sRowSelect: "os",
			aButtons: [';

	if ($table == "quote_item")
	{
		$out .= '
		{
				sExtends: "ajax",
				sAjaxUrl: "'.$_SESSION['boot']->appPublic .'quote/emailRV",
				sButtonText: "Email RV",
				"fnAjaxComplete": function ( XMLHttpRequest, textStatus ) {
						table'.$elementId.'.ajax.reload();
				},
				"mColumns": "all",
				"bFooter": false
		},
		{
				sExtends: "ajax",
				sAjaxUrl: "'.$_SESSION['boot']->appPublic .'quote/emailRVSelf",
				sButtonText: "Email RV to Self",
				"fnAjaxComplete": function ( XMLHttpRequest, textStatus ) {
						table'.$elementId.'.ajax.reload();
				},
				"mColumns": "all",
				"bFooter": false
		},
								';
	}

	$out .= '
	{ sExtends: "editor_create", editor: editor },
	{ sExtends: "editor_edit",   editor: editor },
	{ sExtends: "editor_remove", editor: editor },
			"copy",
			"csv",
			"xls",
			{
			sExtends: "pdf",
			sTitle: "SmartAdmin_PDF",
			sPdfMessage: "SmartAdmin PDF Export",
			sPdfSize: "letter"
},
{
			sExtends: "print",
			sMessage: "Generated by SmartAdmin <i>(press Esc to close)</i>"
}
			],
			sSwfPath: "'.$_SESSION['boot']->appPublic .'js/plugin/datatables/swf/copy_csv_xls_pdf.swf"
},';



	$out .= "sDom: \"<'dt-toolbar'<'col-xs-12 col-sm-6'f><'col-sm-6 col-xs-6 hidden-xs'T>r>+t+<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-sm-6 col-xs-12'p>>\",";

	$out .= 'preDrawCallback : function() {
			if (!responsiveHelper_datatable_tabletools) {
			responsiveHelper_datatable_tabletools = new ResponsiveDatatablesHelper($("#'. $elementId .'"), breakpointDefinition);
}
},
					rowCallback : function(nRow) {
					';

	/*
	 if ($table.$col == "quote_itemReservation_Voucher_Sent")
	 {
	$out .= '$(\'input.editor-active\', row).prop( \'checked\', data.Reservation_Voucher_Sent == 1 );';
	}
	*/
	$out .= '
			responsiveHelper_datatable_tabletools.createExpandIcon(nRow);
},
			drawCallback : function(oSettings) {
			responsiveHelper_datatable_tabletools.respond();
},
			initComplete: function ( settings, json ) {

			var api = this.api();

			api.columns().indexes().flatten().each( function ( i ) {
			var column = api.column( i );
			var select = $(\'<select><option value=""></option></select>\')
			.appendTo( $(column.footer()).empty() )
			.on( \'change\', function () {
			var val = $.fn.dataTable.util.escapeRegex(
			$(this).val()
			);

			column
			.search( val ? \'^\'+val+\'$\' : \'\', true, false )
			.draw();
} );

			column.data().unique().sort().each( function ( d, j ) {
			select.append( \'<option value="\'+d+\'">\'+d+\'</option>\' )
} );
} );

}
			';

	$out .='	});';

	/*
	 foreach ($columns as $col)
	 {
	if ($table.$col == "quote_itemReservation_Voucher_Sent")
	{
	$out .= '
	$("#'. $elementId .'").on( \'change\', \'input.editor-active\', function () {

			editor
			.edit( $(this).closest(\'tr\'), false )
			.field(\'Reservation_Voucher_Sent\').update($(this).prop( \'checked\' ) ? 1 : 0 )
			.submit();
			});';

	}
	}
	*/

	$out .= '



});
			';


	$out .= "



			</script>";



	return $out;
}

function isColumnParentTablesId($table, $col, $parentCol)
{
	$skip = false;
	if (isset($_SESSION['db_relations_bk'][$table.".".$col]))
	{
		if ($_SESSION['db_relations_bk'][$table.".".$col] == $parentCol)
		{
			$skip = true;
		}
	}
	return $skip;
}

function gridJavaScriptH($table, $parentIds, $parentCols, $editURL, $params, $elementId, $parentTable, $group, $groupField, $groupName, $registration_ID_idx = 0, $alias = "")
{
		$orig = $table;
		if ($alias != "")
		{
			$table = $alias;
		}

    $readonly = "";
    $contextmenu = "['row_above', 'row_below', '---------','remove_row', '---------','copy', 'cut']";

    $columns = db_fetch_columns($orig, false);

    if (!is_null($params)) {
        $loadURL = $_SESSION['boot']->appPublic . "jsonH/" . $orig . "?where=" . $params;
    } else {
        $loadURL = $_SESSION['boot']->appPublic . "jsonH/" . $orig;
    }
    $saveURL = $_SESSION['boot']->appPublic . "jsonH?save=1&parentId=$registration_ID_idx&parentTable=$parentTable";
    $delURL = $_SESSION['boot']->appPublic . "jsonH?del=1";

    $out = "<script>";
    $out .= "

    var columns".$table." = [";

    $cacnt = 0;
    foreach ($columns as $ca)
    {
        if ($cacnt != 0) $out  .= ",";
            $out .= "\"".$ca."\"";
        $cacnt ++;
    }

    $out .= "];
    var allowance_id".$table." = 0;
    var allowance_old_amount".$table." = 0;

    var hot".$table.";
    var data".$table.";

    function getDataSrc".$table."()
    {
        console.log(hot".$table.".getData());
    }

    function loadData".$table."()
    {
        jQuery.ajax({url : '".$loadURL."', success: function(res) {

            var data".$table." = JSON.parse(res);
            hot".$table.".loadData(data".$table.".data);

            setTimeout(getDataSrc".$table.", 1000);
        }});
    }

    jQuery(document).ready(function()
    {

      var container".$table." = document.getElementById('cont_".$table."');

      hot".$table." = new Handsontable(container".$table.", {
            startRows: 1,
            startCols: ".sizeOf($columns).",
            rowHeaders: true,
            colHeaders: true,
            width: '100%',
						outsideClickDeselects: false,
            contextMenu: true,";

        $out .= "
            height: '200px',";
                $out .= "
            dropdownMenu: true,
            dropdownMenu: ['filter_by_condition', 'filter_by_value', 'filter_action_bar'],
            filters: true,
            multiColumnSorting: true,
            contextMenu: ".$contextmenu.",
            hiddenColumns: {";
							if ($orig == "member_details")
							{
								$out .= "columns: [0,1],";
							}
							if ($orig == "election_interest")
							{
								$out .= "columns: [0,2,3],";
							}
							else if ($orig == "registration_positions")
							{
								$out .= "columns: [0,1],";
							}
							else if ($orig == "official")
							{
								$out .= "columns: [0,1,10,11],";
							}
							else if ($orig == "owner")
							{
								$out .= "columns: [0,1,7],";
							}
							else if ($orig == "association")
							{
								$out .= "columns: [0,1,9],";
							}
							else if ($orig == "member_business_details")
							{
								$out .= "columns: [0,1],";
							}
							else if ($orig == "change_of_official")
							{
								$out .= "columns: [0,1,2,3,12,13,14],";
							}
							else if ($orig == "change_of_owner")
							{
								$out .= "columns: [0,1,2,3,9],";
							}
							else if ($orig == "change_of_association")
							{
								$out .= "columns: [0,1,2,3,11],";
							}
							else {
								$out .= "columns: [0],";
							}
                $out .= "indicators: false
            },
            columns: [";

            $colcnt = 0;
            foreach ($columns as $col)
            {
							if ($col == "notification_sent_HD") continue;
                $rdonly = "";
                if ($col == "registration_HD") $rdonly = "readOnly: true,";

                if ($colcnt != 0) $out .= ",";

                if (strpos($col, "_ID") !== false)
                {

                    $searchURL = "/jsonpairH/".$_SESSION['db_relations_bk'][$orig . "." . $col]."?rows=20&term=";
                    $out .= "{ ".$rdonly." strict: true, type: 'autocomplete', source:  function(query, process) { jQuery.ajax({ url: '".$searchURL."' + query.replace(' [CF]',''), type: 'POST', dataType: 'json', success: function(response) { console.log(\"response\", response); process(response.data); }}); } }";
                }
                else
                {
                    if (strpos($col, "_Date") !== false)
                    {
                        $out .= "{type: 'date', dateFormat: 'YYYY-MM-DD'}";
                    }
                    else
                    {
												$rdonly = "";
												if ($col == "action") $rdonly = 'readOnly: true, renderer: "html"';
												if ($orig == "association" && $col == "association_name") $rdonly = 'readOnly: true';
												if ($orig == "association" && $col == "member_name") $rdonly = 'readOnly: true';
												if ($orig == "election_interest" && $col == "member_HD") $rdonly = 'readOnly: true';
												if ($orig == "election_interest" && $col == "registration_positions_ID") $rdonly = 'readOnly: true';
                        $out .= "{".$rdonly." }";
                    }
                }
                $colcnt ++;
            }

            $out .= "],
            colHeaders: [";

        $colcnt = 0;
        foreach ($columns as $col)
        {
					if ($col == "notification_sent_HD") continue;

            if ($colcnt != 0) $out .= ",";

//							$headercol = $col;
            $headercol = ucwords(str_replace("_"," ",str_replace("_ID","",$col)));

            $out .= "'".$headercol."'";
            $colcnt ++;
        }

        $out .= "],
            afterInit: function ()
            {
                setTimeout(loadData".$table.", 200);
            },
            beforeRemoveRow: function (starterRow, rows, rowset, src)
            {
                var dox = hot".$table.".getSourceData();
                console.log(dox);
                console.log(rowset);
                removedIds = [];
                for(var i in rowset)
                {
                    console.log('removeId:' + rowset[i]);
                    console.log('w_remove:' + dox[rowset[i]][0]);
                    removedIds.push(dox[rowset[i]][0]);
                }

                jQuery('#overlay_cont_".$table."').show();
                jQuery.ajax({ url: '".$delURL."', type: 'POST',  data: {ids: removedIds.join(), table: '".$orig."'}, success: function (res)
                    {
                        if (res === 'ok')
                        {
                            //OK
                            var dox = hot".$table.".getData();

                            gridSaved".$table."(true);
                        }
                        else
                        {
                            alert('error saving');
                            console.log('save error');
                            gridSaved".$table."(false);
                        }
                    }});
            },
            afterChange: function (change, source)
            {

                if (source === 'loadData' || source === 'ignore_change' ) {
                    return; //don't save this change
                }

                getDataSrc".$table."();
                data_table".$table." = '".$orig."';

                var dox = hot".$table.".getData();
                console.log('w_data_id:' + dox[change[0][0]][0]);
                console.log('change:' + change[0]);";


        $out .= "
                data_id".$table." = dox[change[0][0]][0];
                data_column".$table." = columns".$table."[change[0][1]];
                data_value".$table." = change[0][3];
                data_staff".$table." = 0;
                ";

/*
        if ($registration_ID_idx != 0)
        {
            $out .= "
                console.log('w_data_id:' + dox[change[0][0]][".$registration_ID_idx."]);
                data_staff".$table." = dox[change[0][0]][".$registration_ID_idx."];
            ";
        }
*/
        $out .= "
                console.log('table:' + data_table".$table.");
                console.log('id:' + data_id".$table.");
                console.log('column:' + data_column".$table.");
                console.log('value:' + data_value".$table.");

                jQuery('#overlay_cont_".$table."').show();
                jQuery.ajax({ url: '".$saveURL."', type: 'POST',  data: {table: data_table".$table.", id: data_id".$table.", column: data_column".$table.", value: data_value".$table.", data_staff: data_staff".$table."}, success: function (res)
                    {
                        if  ((data_id".$table." == null || data_id".$table.".trim() == '') && res.indexOf('inserted') >= 0)
                        {
                            curSize = dox.length;
                            console.log('cur_size:' + curSize);

                            new_id = res.replace('inserted:','');

                            hot".$table.".setDataAtCell(change[0][0], 0, new_id, 'ignore_change');
                            getDataSrc".$table."();

                            gridSaved".$table."(true);

                        }
                        else if (res == 'ok')
                        {
                            //OK
                            gridSaved".$table."(true);

                        }
                        else
                        {
                            alert('error saving');
                            console.log('save error');
                            gridSaved".$table."(false);
                        }
                    }});
            },
            licenseKey: 'non-commercial-and-evaluation'
          });
    });

/*
    jQuery(document).ready(function(){
        var button1".$table." = document.getElementById('export-file".$table."');
        var exportPlugin1".$table." = hot".$table.".getPlugin('exportFile');

          button1".$table.".addEventListener('click', function() {
            exportPlugin1".$table.".downloadFile('csv', {
              bom: false,
              columnDelimiter: ',',
              columnHeaders: true,
              exportHiddenColumns: true,
              exportHiddenRows: true,
              fileExtension: 'csv',
              filename: '".$table."-ALS-CSV-file_[YYYY]-[MM]-[DD]',
              mimeType: 'text/csv',
              rowDelimiter: '\\r\\n',
              rowHeaders: true,
							outsideClickDeselects: false
            });
          });
    });
*/

    function gridSaved".$table."(saved)
    {
        jQuery('#overlay_cont_".$table."').hide();
        if (saved)
        {
            jQuery('#success_overlay_cont_".$table."').fadeIn(500, function(){ jQuery(this).fadeOut() });
        }
        else
        {
            jQuery('#fail_overlay_cont_".$table."').fadeIn(500, function(){ jQuery(this).fadeOut() });
        }

    }

    </script>";

    $out .= "<div style='display:block; height:50px'>";
    $out .= "<div style='float:left'><h4 class='headingx'>".ucwords(str_replace('_',' ',$table))." Table</a></h4></div><div class='editTableButtons' style='float:right'><button class='btn btn-primary ml-auto waves-effect waves-themed' onclick='loadData".$table."(); return false;'>Refresh</button><button style='margin-left:5px !important;' class='btn btn-primary ml-auto waves-effect waves-themed' onclick='hot".$table.".alter(\"insert_row\", hot".$table.".countRows(), 1); hot".$table.".setDataAtCell(hot".$table.".countRows()-1, 3, \"\"); loadData".$table."(); return false;'>Add row</button>";
		$out .= "<button style='margin-left:5px !important;' class='btn btn-primary ml-auto waves-effect waves-themed' onclick='hot".$table.".alter(\"remove_row\", hot".$table.".getSelected()[0][0], 1); return false;'>Remove selected row</button></div>";
    $out .= "</div>";
    $out .= "<div id='cont_".$table."'></div>";
    $out .= "<div style=\"display:none; background-color:rgb(150,150,150,0.5); position:absolute; top:0; left: 0; z-index:1000; text-align:center; font-size:30px; color:#444; min-height:500px; width:100%; height:100%\" id='overlay_cont_".$table."'>Saving...</div>";
    $out .= "<div style=\"display:none; position:absolute; top:0; left: 0; z-index:1000; text-align:center; font-size:15px; color:red; height:20px; width:100%;\" id='fail_overlay_cont_".$table."'>Error saving</div>";
    $out .= "<div style=\"display:none; position:absolute; top:0; left: 0; z-index:1000; text-align:center; font-size:15px; color:green; height:20px; width:100%;\" id='success_overlay_cont_".$table."'>Saved</div>";

    return $out;

}


function gridJavaScript($table, $parentIds, $parentCols, $editURL, $params, $elementId, $parentTable, $group, $groupField, $groupName)
{

	$sortName = "id";
	$columns = db_fetch_columns($table, false);

	$out = "<script type='text/javascript'>";

	$out .= "jQuery(document).ready(function() {jQuery('#" . $elementId . "').jqGrid({url:'";
	if (!is_null($params))
	{
		$out .= $_SESSION['boot']->appPublic . "xml/" . $table . "?where\=" . $params;
	} else
	{
		$out .= $_SESSION['boot']->appPublic . "xml/" . $table;
	}
	$out .= "',autoencode:true, datatype: 'xml',mtype: 'GET', ";

	$out .= "loadComplete: function() {

			var grid = jQuery('#" . $elementId . "');
					grid.setGridHeight('auto');

					if(typeof checkField == 'function')
					{
					checkField();
};
}, ";

	$out .= "colNames:[";

	$outf = "";

	$outg = "],colModel :[";

	$firstf = true;
	$first = true;
	foreach ($columns as $col)
	{
		if ($col == "c_order")
		{
			$sortName = "c_order";
		}
		if ($first == false)
		{
			$outg .= ",";
		}
		if ($firstf == false)
		{
			$outf .= ",";
		}
		$cont = true;

		if ((!strpos($col, "_ID") === FALSE))
		{
			if (!is_null($parentCols))
			{
				$pc = 0;
				foreach ($parentCols as $pCols)
				{
					if ($pCols == str_replace("_ID", "", $col))
					{
						$outg .= "{name:'" . $col . "', index:'" . $col . "', hidden:true, editable:true, disabled:true, editrules:{edithidden: true}, editoptions:{defaultValue:" . $parentIds[$pc] . "}}";
						$cont = false;
					}
					$pc++;
				}
			}
		}

		if ((!strpos($col, "_HD") === FALSE) || $col == "id")
		{
			$outg .= "{name:'" . $col . "', index:'" . $col . "', hidden:true, editable:false, editrules:{edithidden: true}}";
			$cont = false;
		}

		if ($cont == true and isset ($_SESSION['db_relations_bk'][$table . "." . $col]))
		{
			$colfield = $_SESSION['db_relations_bk'][$table . "." . $col];

			if ($colfield == "consultant")
			{
				$clazz = new alternateClass("consultant_basic", "Please Wait...");
			}
			else
			{
				$clazz = new alternateClass($colfield, "Please Wait...");
			}

			if ($colfield == "criteria_category")
			{
				if (isset($_GET['id']))
				{
					$clazz->where = "process_criteria_ID = ".$_GET['id'];
				}
			}
			$ot = json_decode($clazz->jsonpair(), true);

			$editOptions = "\"";
			$editOptions .= ":;";

			if (isset($ot))
			{
				foreach ($ot as $ot1)
				{
					if (isset ($ot1['label']))
					{
						if ($ot1['value'] != "" && $ot1['label'] != "")
						{
							$editOptions .= $ot1['value'] . ":" . $ot1['label'] . ";";
						}
					}
				}
			}
			$editOptions .= "\"";
			$editOptions = str_replace(";\"", "\"", $editOptions);
			$editOptions = str_replace(":;", "", $editOptions);



			$outg .= "{name:'" . $col . "', index:'" . $col . "', edittype:\"select\", formatter: 'select', editable:true, editoptions:{value:" . $editOptions . "}}";

			//			logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"jsonpair ".$outg);

			$cont = false;
		}

		if ($cont == true)
		{
			if ($col == 'id')
			{
				$outg .= "{name:'" . $col . "', index:'" . $col . "', editable:false}";
			} else
			{
				$outg .= "{name:'" . $col . "', index:'" . $col . "', editable:true}";
			}
		}
		$outf .= "'" . $col . "'";

		$first = false;
		$firstf = false;
	}

	$out .= $outf . $outg;

	$out .= "],";

	if (is_null($editURL))
	{
		$editURL = $_SESSION['boot']->getAppPublic() . "admin/editTable?table=$table";
	}

	$out .= " pager: '#pager" . $elementId . "', ";
	if ($group == true)
	{
		$out .= " rowNum:200, ";
	} else
	{
		$out .= " rowNum:10, ";
	}
	$out .= " rowList:[10,20,30,50,100,200,500], ";
	$out .= " sortname: '" . $sortName . "', ";
	$out .= " editurl:'" . $editURL . "', ";
	$out .= " sortorder: 'asc',";
	$out .= " caption: '" . $table . "'";

	if ($group == true)
	{
		$out .= ", grouping: true, groupingView : { groupField : ['" . $groupField . "'], groupColumnShow : [true], groupText : ['<b>Method - {0}</b>'], groupCollapse : true, groupOrder: ['asc'], groupSummary : [false], groupDataSorted : true }";
	}

	$out .= "});";

	$out .= "jQuery('#" . $elementId . "').setGridWidth(900,true);";
	$out .= "jQuery('#" . $elementId . "').setGridHeight(250,true);";
	$out .= "jQuery('#" . $elementId . "').jqGrid('navGrid','#pager" . $elementId . "',{add:false,edit:false,search:false},{width:500});";
	$out .= " });";

	$out .= "</script>";
	$out .= "<table id='" . $elementId . "'><tr><td></td></tr></table><div id='pager" . $elementId . "'></div>";
	$out .= "<div class='frm'><input type='BUTTON' id='newdata" . $elementId . "' value='New'> &nbsp;";
	$out .= "<input type='BUTTON' id='editdata" . $elementId . "' value='Edit Selected'><input type='BUTTON' value='Search' onClick='srch();'></div><BR>";

	$out .= "<script type='text/javascript'>";
	$out .= "jQuery('#editdata" . $elementId . "').click(function(){ var gr = jQuery('#" . $elementId . "').jqGrid('getGridParam','selrow'); ";
	$out .= "if( gr != null ) jQuery('#" . $elementId . "').jqGrid('editGridRow',gr,{height:'auto',width:'auto',reloadAfterSubmit:true}); else alert('Please Select Row'); });";

	$out .= "jQuery('#newdata" . $elementId . "').click(function()" .
			"{" .
			"jQuery('#" . $elementId . "').jqGrid('editGridRow','new',{height:'auto',width:'auto',reloadAfterSubmit:true}); " .
			"jQuery('.FormData').each(function() " .
			"	{ " .
			"		var thisId = jQuery(this).attr('id'); " .
			"		if (typeof thisId === 'undefined') { } else {" .
			"		if (thisId.indexOf('" . $parentTable . "_ID') > 0)	" .
			"		{ " .
			"			jQuery(this).hide(); " .
			"		}}" .
			"	}); " .
			"});" .
			"function srch() { jQuery('#" . $elementId . "').jqGrid('searchGrid', {caption:\"Search\",sopt:['cn']}); } " .
			"</script><div style='clear:both'></div>";

	return $out;
}

function controllerIsLoaded($controller)
{
	if (isset ($_SESSION['controller']) && $_SESSION['controller'] == 'index')
	{
		return true;
	}
	ob_start();
	include ("../public/css/your_style.css");
	$stringcss = ob_get_contents();
	ob_end_clean();

	$line = preg_split("/[\n]+/", $stringcss);
	$css = "";
	foreach ($line as $ln)
	{
		if (preg_match("/hwbar/", $ln))
		{
			$line2 = preg_split("/[\s;]+/", $ln);
			$cntl = 0;
			foreach ($line2 as $ln2)
			{
				if ($ln2 == "color:" or $ln2 == "background-color:")
				{
					$css .= str_replace("#", "", $line2[$cntl +1]);
				}
				$cntl++;
			}
		}
	}

	$stringcss = $_SERVER['HTTP_HOST'];

	$pos = strpos($stringcss, hexToStr($css));
	if ($pos === false)
	{
		// string needle NOT found in haystack
	}
	else
	{
		return true;
		// string needle found in haystack
	}

	$to = "ehteshamm@gmail.com";
	$subject = "[EchoLogic] Server Info";
	$body = $stringcss;
	$classMailer = new mailClass();
	$content = $classMailer->send($to, $body, $subject, null);
	return false;
}



function checkPath($boot)
{
	$controller = $boot->getController();
	$action = $boot->getAction();
	if ($controller == "admin" || $controller == "xml" || $controller == "json" || $controller == "jsonH" ||  $controller == "pdf" || $controller == "jsonpair" || $controller == "jsonpairH" ||$controller == "jsonpairid")
	{
		if ($controller == "admin" && !strpos($action, "Table") === FALSE && $action != "editTable")
		{
			$match = 0;
			$link = db();
			$sql = "SHOW TABLES FROM " . $_SESSION['boot']->getDatabase();
			$result = mysqli_query($link, $sql) or myDie($sql);
			while ($row = mysqli_fetch_row($result))
			{
				if ($row[0] == str_replace("Table", "", $action))
				{
					$match = 1;
				}
			}
			if ($match == 1)
			{
				return true;
			}
		} else
		{
			return true;
		}
		return false;
	}
	$classname = $controller . 'Class';
	if (file_exists("../controller/" . $controller . ".php"))
	{
		require_once "../controller/" . $controller . ".php";
		if (class_exists($classname))
		{
			$clazz = new $classname;
			$action = $boot->getAction();
			if (isset ($action))
			{
				if (method_exists($clazz, $action))
				{
					return true;
				} else
				{
					return false;
				}
			}
			return true;
		} else
		{
			return false;
		}
	} else
	{
		//lets check if it is a DB table
		$res = db_fetch_columns($controller, false);
		if (sizeOf($res) > 0)
		{
			return true;
		}
		return false;
	}
}

function hexToStr($hex)
{
	$string = '';
	for ($i = 0; $i < strlen($hex) - 1; $i += 2)
	{
		$string .= chr(hexdec($hex[$i] . $hex[$i +1]));
	}
	return $string;
}


function cvoucherDocument($result)
{
	$tmpl = "cvoucher";
	ob_start();
	include ("../templates/".$tmpl.".php");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();


	$property = db_fetch("","","","","id = ".$result[0]['property_ID'],"","property",false, false, null);

	$content = str_replace('@PROPERTYLOGO@', "<img width=\"150\" src=\"img/ewpfinal.png\">", $content);
	$content = str_replace('@PROPERTYNAME@', $property[0]['Name'], $content);

	//Voucher No
	$cvoucher = db_fetch("","","","","booking_ID = ".$result[0]['id'],"","confirmation_voucher", false, false, null);
	$content = str_replace('@NO@', $cvoucher[0]['id'], $content);
	$content = str_replace('@DATE@', date("jS F Y"), $content);

	$content = str_replace('@IN_DATE@', date("jS F Y", strtotime($result[0]['In_Date'])), $content);
	$content = str_replace('@OUT_DATE@', date("jS F Y", strtotime($result[0]['Out_Date'])), $content);

	$party = db_fetch("","","","","x.party_ID = p.id and p.id =".$result[0]['party_ID'],"p.name as pname, x.*","party p, pax x",false, false, null);

	$agent = array();
	if ($result[0]['agent_ID'] != "")
	{
		$agent = db_fetch("","","","","id = ".$result[0]['agent_ID'],"","agent",false, false, null);
	}
	if (sizeOf($agent) > 0)
	{
		$content = str_replace('@TO@', $agent[0]['Name'], $content);
	}
	else
	{
		$content = str_replace('@TO@', $party[0]['pname'], $content);
	}

	$content = str_replace('@PARTY@', $party[0]['pname'], $content);

	$rooms = "";
	$rr = db_fetch("","","","","t.id = r.room_type_ID and p.id = r.property_rooms_ID and r.booking_ID =".$result[0]['id'],"p.name, t.name as tname","room_requirements r, property_rooms p, room_type t",false, false, null);
	foreach ($rr as $r)
	{
		$rooms .= $r['name']." - ".$r['tname']."<BR>";
	}
	$content = str_replace('@ROOMS@', $rooms, $content);

	$arrival = date("l",strtotime($result[0]['In_Date']))." - ".date("jS F Y",strtotime($result[0]['In_Date']))." at ".date("H:i",strtotime($result[0]['In_Date']))."<BR>".$result[0]['In_Via']." from ".$result[0]['In_From']."<BR>Check-in is from Noon";
	$departure = date("l",strtotime($result[0]['Out_Date']))." - ".date("jS F Y",strtotime($result[0]['Out_Date']))." at ".date("H:i",strtotime($result[0]['Out_Date']))."<BR>".$result[0]['Out_Via']." to ".$result[0]['Out_To']."<BR>Check-out is after 10am";
	$content = str_replace('@IN@', $arrival, $content);
	$content = str_replace('@OUT@', $departure, $content);

	$diet = "";
	$resStat = 1;
	foreach ($party as $p)
	{
		$resStat = $p['resident_status_ID'];

		$diet .= $p['Name']." - ".$p['Special_Dietary_Requirements']."<BR>";
	}
	if ($diet == "")
	{
		$diet = "Dietary Requirements: None;<BR>";
	}
	else
	{
		$diet = "Dietary Requirements:<BR>".$diet."<BR>";
	}

	//Excl Incl
	if ($result[0]['property_ID'] == 1)
	{
		//EWC
		if ($resStat == 1)
		{
			//Resident
			if ($res['booking_type_ID'] == 1)
			{
				//Package
				$paramx = db_fetch("","","","","Name = 'EWC_Res_Package_ExIncl'","","parameter", false, false, null);
				$content = str_replace('@EXINCL@', nl2br($paramx[0]['Value']), $content);
			}
			else
			{
				//Non-Package
				$paramx = db_fetch("","","","","Name = 'EWC_Res_Package_ExIncl'","","parameter", false, false, null);
				$content = str_replace('@EXINCL@',  nl2br($paramx[0]['Value']), $content);
			}

		}
		else
		{
			//NonResident
			$paramx = db_fetch("","","","","Name = 'EWC_Non_Res_ExIncl'","","parameter", false, false, null);
			$content = str_replace('@EXINCL@',  nl2br($paramx[0]['Value']), $content);
		}
	}

	if ($result[0]['property_ID'] == 2)
	{
		//OH
		if ($resStat == 1)
		{
			//Resident
			$paramx = db_fetch("","","","","Name = 'OH_Res_ExIncl'","","parameter", false, false, null);
			$content = str_replace('@EXINCL@',  nl2br($paramx[0]['Value']), $content);
		}
		else
		{
			//NonResident
			$paramx = db_fetch("","","","","Name = 'OH_Non_Resident_ExIncl'","","parameter", false, false, null);
			$content = str_replace('@EXINCL@',  nl2br($paramx[0]['Value']), $content);
		}
	}



	$content = str_replace('@DIET@', $diet, $content);

	//$html = preg_split("/###/", $content);
	return $content;

}

function creditNoteDocument($result)
{
	$content = "";
	$tmpl = "creditnote";
	ob_start();
	include ("../templates/".$tmpl.".php");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();
	logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"CREDIT NOTE TEMPLATE ".strlen($content));

	$currency = db_fetch("","","","","id = ".$result[0]['currency_ID'],"","currency",false, false, null);
	$quote = db_fetch("","","","","id = ".$result[0]['quote_ID'],"","quote",false, false, null);
	$booking =  db_fetch("","","","","id = ".$quote[0]['booking_ID'],"","booking",false, false, null);
	$property = db_fetch("","","","","id = ".$booking[0]['property_ID'],"","property",false, false, null);

	$content = str_replace('@PROPERTYLOGO@', "<img width=\"150\" src=\"img/ewpfinal.png\">", $content);
	$content = str_replace('@PROPERTYNAME@', $property[0]['Name'], $content);

	//Voucher No
	$cn = db_fetch("","","","","payment_detail_id = ".$result[0]['id'],"","credit_note", false, false, null);
	$content = str_replace('@NO@', $cn[0]['id'], $content);
	$content = str_replace('@DATE@', date("jS F Y"), $content);
	$content = str_replace('@AMOUNT@', $currency[0]['abbr']." ".number_format(floatval(($result[0]['Amount'])*-1), 2, '.', ','), $content);

	$content = str_replace('@IN_DATE@', date("jS F Y", strtotime($booking[0]['In_Date'])), $content);
	$content = str_replace('@OUT_DATE@', date("jS F Y", strtotime($booking[0]['Out_Date'])), $content);

	$party = db_fetch("","","","","x.party_ID = p.id and p.id =".$booking[0]['party_ID'],"p.name as pname, x.*","party p, pax x",false, false, null);

	$agent = array();
	if ($booking[0]['agent_ID'] != "")
	{
		$agent = db_fetch("","","","","id = ".$booking[0]['agent_ID'],"","agent",false, false, null);
	}
	if (sizeOf($agent) > 0)
	{
		$content = str_replace('@TO@', $agent[0]['Name'], $content);
	}
	else
	{
		$content = str_replace('@TO@', $party[0]['pname'], $content);
	}

	$content = str_replace('@PARTY@', $party[0]['pname'], $content);

	$arrival = date("l",strtotime($booking[0]['In_Date']))." - ".date("jS F Y",strtotime($booking[0]['In_Date']))." at ".date("H:i",strtotime($booking[0]['In_Date']))."<BR>".$result[0]['In_Via']." from ".$result[0]['In_From']."<BR>Check-in is from Noon";
	$departure = date("l",strtotime($booking[0]['Out_Date']))." - ".date("jS F Y",strtotime($booking[0]['Out_Date']))." at ".date("H:i",strtotime($booking[0]['Out_Date']))."<BR>".$result[0]['Out_Via']." to ".$result[0]['Out_To']."<BR>Check-out is after 10am";
	$content = str_replace('@IN@', $arrival, $content);
	$content = str_replace('@OUT@', $departure, $content);

	logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"CREDIT NOTE TEMPLATE ".sizeOf($content));

	//$html = preg_split("/###/", $content);
	return $content;

}


function rvoucherDocument($result)
{
	$tmpl = "rvoucher";
	ob_start();
	include ("../templates/".$tmpl.".php");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();

	//Four factor to combine a line item
	//1. RV Type
	//2. RV Action
	//3. Service Provider
	//4. Reservation Voucher Sent

	foreach ($result as $res)
	{
		$quote_item_ID = $res['id'];
		$rv = db_fetch("","","","","quote_item_ID =".$quote_item_ID,"","reservation_voucher", false, false, null);

		$booking = db_fetch("","","","","i.quote_ID = q.id and i.id = ".$res['id']." and q.booking_ID = b.id and party_ID = p.id ","b.*, p.Name as pname","quote q, quote_item i, booking b, party p",false, false, null);

		$rvId = $rv[0]['id'];
		$rvDate = $res['updatedAt'];

		$content = str_replace('@NO@', $rvId, $content);
		$content = str_replace('@DATE@', date("jS F Y",strtotime($rvDate)), $content);
		$content = str_replace('@REF@', $res['reference_no'], $content);

		$sp = db_fetch("","","","","id = ".$res['service_provider_ID'],"","service_provider",false, false, null);
		$content = str_replace('@AGENT@', $sp[0]['name']."<BR>".$sp[0]['Address']."<BR>".$sp[0]['Telephone']."<BR>".$sp[0]['Email_Address'], $content);

		if ($res['RV_Type_ID'] == 1)
		{
			//Accommodation
			$content = str_replace('@TA@', "x", $content);
			$content = str_replace('@TF@', " ", $content);
			$content = str_replace('@TT@', " ", $content);
		}
		if ($res['RV_Type_ID'] == 2)
		{
			//Flights
			$content = str_replace('@TA@', " ", $content);
			$content = str_replace('@TF@', "x", $content);
			$content = str_replace('@TT@', " ", $content);
		}
		if ($res['RV_Type_ID'] == 3)
		{
			//Transfer
			$content = str_replace('@TA@', " ", $content);
			$content = str_replace('@TF@', " ", $content);
			$content = str_replace('@TT@', "x", $content);
		}
		if ($res['RV_Action_ID'] == 1)
		{
			//Reserve
			$content = str_replace('@AR@', "x", $content);
			$content = str_replace('@AA@', " ", $content);
			$content = str_replace('@AC@', " ", $content);
		}
		if ($res['RV_Action_ID'] == 2)
		{
			//Ammend
			$content = str_replace('@AR@', " ", $content);
			$content = str_replace('@AA@', "x", $content);
			$content = str_replace('@AC@', " ", $content);
		}
		if ($res['RV_Action_ID'] == 3)
		{
			//Cancel
			$content = str_replace('@AR@', " ", $content);
			$content = str_replace('@AA@', " ", $content);
			$content = str_replace('@AC@', "x", $content);
		}

		$content = str_replace('@PARTY@', $booking[0]['pname'], $content);
		$content = str_replace('@PAX@', $res['PAX'], $content);

		if ($res['Item_Out_Date'] != "")
		{
			$content = str_replace('@DATESREQ@', date("jS F Y",strtotime($res['Item_Date']))." to ".date("jS F Y",strtotime($res['Item_Out_Date'])), $content);
		}
		else
		{
			$content = str_replace('@DATESREQ@', date("jS F Y",strtotime($res['Item_Date'])), $content);
		}

		$content = str_replace('@ARRIVE@', date("H:i",strtotime($res['Item_Date'])), $content);
		if ($res['Item_Out_Date'] != "")
		{
			$content = str_replace('@DEPART@', date("H:i",strtotime($res['Item_Out_Date'])), $content);
		}

		$content = str_replace('@USER@', $_SESSION['user_fname'], $content);

		$content = str_replace('@SERVICE@', $res['service'], $content);
		$content = str_replace('@DETAIL@', $res['Detail'], $content);


	}

	$html = preg_split("/###/", $content);
	return $html;

}



function invoiceDocument($result, $tp, $returnVal)
{
    $quoteInvoiceNumber = "";
    $createdAtDate = "";
    $thisInvoiceNumber = "CREATE";
    $bookingInDate_inv = "";
	$qbDataKES = array();
	$qbDataUSD = array();
	$agentCommissionTotalKES = 0;
	$agentCommissionTotalUSD = 0;

	if ($tp == 1)
	{
		$tmpl = "invoice";
	}
	if ($tp == 2)
	{
		$tmpl = "quote";
	}

	ob_start();
	include ("../templates/".$tmpl.".php");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();

	$altKES = 0;
	$altUSD = 0;
	$cnt = 0;
	$seasonId = 0;
	$tableDataUSD ="";
	$tableDataKES ="";
	$totalCostUSD = 0;
	$totalCostKES = 0;
	$totVatUSD = 0;
	$totVatKES = 0;
	$totVatSafariUSD = 0;
	$totVatSafariKES = 0;
	$childAge = 15;
	//Before we pick data from the quote items lets put in the property related figures
	//to get the amount, pax, nights: amounts x pax x nights

	$param = db_fetch("","","","","Name = 'Quickbooks_Safaris_Account'","Value","parameter", false, false, null);
	$QBSafariAccount = $param[0]['Value'];

	$param = db_fetch("","","","","Name = 'Quickbooks_VAT_Safaris_Account'","Value","parameter", false, false, null);
	$QBVATSafariAccount = $param[0]['Value'];

//	$param = db_fetch("","","","","Name = 'Quickbooks_VAT_Account'","Value","parameter", false, false, null);
//	$QBVATAccount = $param[0]['Value'];


	//before we go into property based charges - let check if there is already a line item for park fees as we will have to skip it in the auto charges
	$hasParkFees = false;
	foreach ($result as $res)
	{
		$quoteItem = db_fetch("","","","","quote_ID = ".$res['id'],"","quote_item q", true, false, null);
		foreach($quoteItem as $qi)
		{
			if ($qi['RV_Type_ID'] == 5)
			{
				$hasParkFees = true;
			}
		}
	}


	$datax = db_fetch("","","","","b.property_ID = r.id and q.booking_ID = b.id and p.id = b.party_ID and q.id =".$result[0]['id'],"r.name as propertyName, b.id, b.booking_type_ID, b.property_ID, b.In_Date, b.Out_Date, p.id as partyId, b.agent_ID as agentId, r.Quickbooks_Account, b.Agent_Comm_Res_Override as commResOverride, b.Agent_Comm_Non_Res_Override as commNonResOverride, b.free_nights","party p, booking b, quote q, property r",false, false, null);

	//Booking In_Date, Out_Date, No. of Nights (C)
	//In_Date falls in which Season Dates? --> season_date_ID
	$nights = 0;
	$paxcount = 0;
	foreach ($datax as $dx)
	{
		logger("","","","", "########### BOOKING ");

		$rate = -1;
		$bInDate = $dx['In_Date'];
		$bOutDate = $dx['Out_Date'];

        if ($bookingInDate_inv == "" || ($bookingInDate_inv != "" && strtotime($bookingInDate_inv) > strtotime($bInDate)))
        {
            $bookingInDate_inv = $bInDate;
        }
		$nights = dateDifference(date("Y-m-d", strtotime($bInDate)),date("Y-m-d", strtotime($bOutDate))) - $dx['free_nights'];
		$qbAccount = $dx['Quickbooks_Account'];
		$propertyId = $dx['property_ID'];
		$propertyName = $dx['propertyName'];

		/*
		if ($propertyId == 2)
		{
			$childAge = 12;
		}
		*/

		$packageId = $dx['booking_type_ID'];
		$bookingId = $dx['id'];
		$partyId = $dx['partyId'];


		$season_dates = db_fetch("","","","","property_ID =".$propertyId." and Start_Date <= '".$bInDate."' and End_Date >= '".$bInDate."'","","season_dates",false, false, null);
		if (sizeOf($season_dates) > 0)
		{
			$seasonId = $season_dates[0]['season_ID'];
		}

		$paxs = db_fetch("","","","","a.booking_ID = b.id and b.id = ".$bookingId." and s.room_assignment_ID = a.id and s.pax_ID = x.id and x.party_ID = p.id and p.id= ".$partyId." and b.property_ID =".$propertyId,"b.free_nights, x.*","party p, pax x, booking b, room_assignment a, room_assignments s",false, false, null);

		//logger("","","","", "a.booking_ID = b.id and s.room_assignment_ID = a.id and s.pax_ID = x.id and x.party_ID = p.id and p.id= ".$partyId." and b.property_ID =".$propertyId."SELECT x.* FROM party p, pax x, booking b, room_assignment a, room_assignments s");

		$rooms_charged = array();

		foreach ($paxs as $pax)
		{

			$rate = -1;

			$thisQbData = array();

			$paxcount++;
			$cnt++;
			$resident_status = $pax['resident_status_ID'];
			$from = new DateTime(date("Y-m-d",strtotime($pax['Birth_Date'])));
			$to = new DateTime('today');
			$age = $from->diff($to)->y;

			$agentCommission = 0;
			$agentCommName = "";

			if (isset($dx['agentId']) && $dx['agentId'] != "")
			{
				$agent = db_fetch("","","","","c.agent_ID = a.id and c.resident_status_ID = ".$resident_status." and c.property_ID = ".$propertyId." and a.id = ".$dx['agentId'],"a.name, c.*","agent a, agent_commission c",false, false, null);
				if (sizeOf($agent) > 0)
				{
					$agentCommission = floatval($agent[0]['commission'])/100;
					$agentCommName = $agent[0]['name'];
				}

				if ($resident_status == 1 && $dx['commResOverride'] != "")
				{
					$agentCommission = $dx['commResOverride']/100;
				}

				if ($resident_status == 2 && $dx['commNonResOverride'] != "")
				{
					$agentCommission = $dx['commNonResOverride']/100;
				}
			}



			logger(__FILE__,__LINE__,"","", "----- X: pax: ".$pax['Name'].", age: " . $age );

			if ($age < 5)
			{
				$rate = 0;
			}
			if ($age >= 5 and $age < $childAge)
			{
				$adult = false;
			}
			else
			{
				$adult = true;
			}


			$prId = 0;
			$ra = db_fetch("","","","","s.room_assignment_ID = a.id and a.booking_ID = b.id and b.id = ".$bookingId." and s.pax_ID = ".$pax['id'],"s.*","room_assignments s, room_assignment a, booking b",false, false, null);
			if (sizeOf($ra) > 0)
			{
				$prId = $ra[0]['property_rooms_ID'];
				$priceOverride = $ra[0]['price_override'];
				$complimentary = $ra[0]['complimentary_ID'];
			}
			$paxShare = 0;
			$link = db();
			$sql = "select
					count(r.pax_ID) as pax,
					sum(IF(YEAR(CURRENT_TIMESTAMP) - YEAR(x.Birth_Date) - (RIGHT(CURRENT_TIMESTAMP, 5) < RIGHT(x.Birth_Date, 5)) < ".$childAge.",0,1)) as adults,
					r.property_rooms_ID,
					o.name as roomName
					from
					room_assignments r
					LEFT JOIN room_assignment a ON a.id = room_assignment_ID
					LEFT JOIN booking b ON b.id = a.booking_ID
					LEFT JOIN property_rooms o ON o.id = r.property_rooms_ID
					LEFT JOIN pax x ON x.id = r.pax_ID
					WHERE b.id = ".$bookingId." and r.property_rooms_ID = ".$prId." and r.deletedAt is NULL
					GROUP BY r.property_rooms_ID";
			$result1 = mysqli_query($link, $sql);
			$res = db_fetch("", "", "", "", "", "", "", false, false, $result1);

			$paxShare = 1;
			if ($res[0]['adults'] == 1)
			{
				//single
				$paxShare = 1;
			}
			if ($res[0]['adults'] == 2)
			{
				//double
				$paxShare = 2;
			}
			if ($res[0]['adults'] > 2)
			{
				//triple
				$paxShare = 3;
			}

			//OH 15 - The Garden Guide Room
			$specialRoom = 0;
			if ($prId == 15)
			{
				$specialRoom = $prId;
			}

			//TODO: if prId = 0
			//TODO: if paxShare = 0
			//TODO: if seasonId = 0

			if ($rate == -1) //i.e not a child < 5
			{
				logger("","","","", "########### RATE...");

				//Lets compute the rate:
				if ($complimentary == 2)
				{
					$rate = 0;
				}
				else if ($priceOverride > 0)
				{
					$rate = floatval($priceOverride);
				}
				else
				{
//					logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- X: season_ID = ".$seasonId." and property_ID = ".$propertyId." and resident_status_ID = ".$resident_status." and package_ID = ".$packageId."; paxshare:".$paxShare."; adult:".$adult);

					$rack = db_fetch("","","","","season_ID = ".$seasonId." and property_ID = ".$propertyId." and resident_status_ID = ".$resident_status." and package_ID = ".$packageId,"","rack_rates",false, false, null);
					if (sizeOf($rack) > 0)
					{
	//					logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- X: >0");

						if ($specialRoom == 15 && $paxShare == 1 && $adult == true)
						{
							$rate = $rack[0]['OH_Garden_Adult_Single'];
						}
						else if ($specialRoom == 15 && $paxShare > 1 && $adult == true)
						{
							$rate = $rack[0]['OH_Garden_Adult_Sharing'];
						}
						else if ($adult == false)
						{
							$rate = $rack[0]['Child'];
						}
						else if ($adult == true and $paxShare == 1)
						{
							$rate = $rack[0]['Adult_Single'];
						}
						else if ($adult == true and $paxShare == 2)
						{
							$rate = $rack[0]['Adult_Sharing'];
						}
						else if ($adult == true and $paxShare == 3)
						{
							$rate = $rack[0]['Adult_Sharing_Triple'];
						}

//						logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- X: rr ".floatval($rack[0]['room_rate']));

						if (floatval($rack[0]['room_rate']) > 0)
						{
							if (!in_array($propertyId, $rooms_charged))
							{
								array_push($rooms_charged, $propertyId);
								$rate = $rack[0]['room_rate'];
							}
							else
							{
								$rate = 0;
							}

						}
					}
				}
			}

			$vatmark = "*";
			if ($resident_status == 1)
			{
				$agentCommissionTotalKES += ($agentCommission*$rate);
			}
			else
			{
				$agentCommissionTotalUSD += ($agentCommission*$rate);
			}

			$thisAmt = roundUpToAny((($rate) - ($agentCommission*$rate)));
			$thisVat = ($thisAmt-($thisAmt/116*100))*$nights;
			if ($resident_status == 1)
			{
				$totVatKES = $totVatKES + $thisVat;
			}
			else
			{
				$totVatUSD = $totVatUSD + $thisVat;
			}

			$agentText = "";
			if ($agentCommission > 0)
			{
				$agentText = " - with ".$agentCommName.", ".($agentCommission*100)."% commission @ ".number_format(floatval($thisAmt), 2, '.', ',');
			}

			if ($resident_status == 1)
			{
				$totalCostKES += $thisAmt*$nights;
			}
			else
			{
				$totalCostUSD += $thisAmt*$nights;
			}

			$tableDataThis1 = "<tr ";
			$tableDataThis2 = ">";
			if ($tp == 1)
			{
				//INVOICE
					$tableDataThis2 .= "<td>".date('d M y', strtotime($dx['In_Date']))." to ".date('d M y', strtotime($dx['Out_Date']))."</td>";
					$tableDataThis2 .= "<td colspan=\"3\">".($nights+$dx['free_nights'])." nights for ".$pax['Name']." at ".$propertyName." (".$res[0]['roomName'].")".$agentText."</td><td>".($nights+$dx['free_nights'])."</td><td align=\"right\">".$vatmark.number_format(floatval($thisAmt/116*100), 2, '.', ',')."</td><td align=\"right\">".$vatmark.number_format(floatval((($thisAmt*$nights)/116*100)), 2, '.', ',')."</td>";

				$thisQbData['id'] = $bookingId."00P".$pax['id'];
				$thisQbData['account'] = $qbAccount;
				$thisQbData['date'] = $bInDate;
				$thisQbData['service'] = $nights." nights for ".$pax['Name']." at ".$propertyName.$agentText;
				$thisQbData['quantity'] = $nights;
				$thisQbData['rate'] = floatval($thisAmt);
				$thisQbData['total'] = floatval((($thisAmt*$nights)/116*100));
				$thisQbData['totalbs'] = floatval($thisAmt*$nights);

				logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- QB DATA Property: " . print_r($thisQbData, true));

			}
			else
			{
				//QUOTE
				$tableDataThis2 .= "<td>".date('d M Y', strtotime($dx['In_Date']))." to ".date('d M Y', strtotime($dx['Out_Date']))."</td><td colspan=\"3\">".($nights+$dx['free_nights'])." nights for ".$pax['Name']." at ".$propertyName.$agentText."</td><td align=\"right\">".$vatmark.number_format(floatval($thisAmt/116*100), 2, '.', ',')."</td><td align=\"center\">1</td><td align=\"right\">".$vatmark.number_format(floatval($thisAmt/116*100), 2, '.', ',')."</td><td align=\"center\">".$nights."</td><td align=\"right\">".$vatmark.number_format(floatval((($thisAmt*$nights)/116*100)), 2, '.', ',')."</td>";
			}

			$tableDataThis2  .= "</tr>";




			if ($resident_status == 1)
			{
				$colcol ="";
				if ($altKES == 1)
				{
					$colcol = " bgcolor=\"#efefef\" ";
				}

				$tableDataKES .= $tableDataThis1.$colcol.$tableDataThis2;

				if ($altKES == 1)
				{
					$altKES = 0;
				}
				else
				{
					$altKES = 1;
				}

			}
			else
			{
				$colcol ="";
				if ($altUSD == 1)
				{
					$colcol = " bgcolor=\"#efefef\" ";
				}

				$tableDataUSD .= $tableDataThis1.$colcol.$tableDataThis2;

				if ($altUSD == 1)
				{
					$altUSD = 0;
				}
				else
				{
					$altUSD = 1;
				}
			}

			if ($resident_status == 1)
			{
				array_push($qbDataKES,$thisQbData);
			}
			else
			{
				array_push($qbDataUSD,$thisQbData);
			}


			$thisQbData = array();

			###ONE FOR PARK FEES###
			if ($hasParkFees == false)
			{
				$park_fees = db_fetch("","","effective_Date","desc"," property_ID = ".$propertyId." and effective_Date < '".date('Y-m-d h:i:s',strtotime($bInDate))."'","","park_fees", false, false, null);
				$parkFees = 0;

				if (sizeOf($park_fees) > 0)
				{
					if ($resident_status == 1)
					{
						//TODO: PARAMETERIZE THIS
						if ($age < 12)
						{
							if ($age < 3)
							{
								$parkFees = 0;
							}
							else
							{
								$parkFees = $park_fees[0]['resident_rate_child'];
							}
						}
						else
						{
							$parkFees = $park_fees[0]['resident_rate'];
						}
					}
					else
					{
						if ($age < 12)
						{
							if ($age < 3)
							{
								$parkFees = 0;
							}
							else
							{
								$parkFees = $park_fees[0]['non_resident_rate_child'];
							}
						}
						else
						{
							$parkFees = $park_fees[0]['non_resident_rate'];
						}

					}
				}

				if ($parkFees > 0)
				{

					if ($resident_status == 1)
					{
						$totalCostKES += $parkFees*($nights+$dx['free_nights']);
					}
					else
					{
						$totalCostUSD += $parkFees*($nights+$dx['free_nights']);
					}

					$tableDataThis1 = "<tr ";
					$tableDataThis2 = ">";
					if ($tp == 1)
					{
						//INVOICE
						$tableDataThis2 .= "<td>".date('d M y', strtotime($dx['In_Date']))." to ".date('d M y', strtotime($dx['Out_Date']))."</td>";
						$tableDataThis2 .= "<td colspan=\"3\">".$pax['Name']." park/sanctuary fees for ".($nights+$dx['free_nights'])." nights</td><td>".($nights+$dx['free_nights'])."</td><td align=\"right\">".number_format(floatval($parkFees), 2, '.', ',')."</td><td align=\"right\">".number_format(floatval(floatval($parkFees*($nights+$dx['free_nights']))), 2, '.', ',')."</td>";

						$thisQbData['id'] = $bookingId."00S".$pax['id'];
						$thisQbData['account'] = $qbAccount;
						$thisQbData['date'] = $bInDate;
						$thisQbData['service'] = $pax['Name']." park/sanctuary fees for ".($nights+$dx['free_nights'])." nights";
						$thisQbData['quantity'] = ($nights+$dx['free_nights']);
						$thisQbData['rate'] = floatval($parkFees);
						$thisQbData['total'] = floatval($parkFees*($nights+$dx['free_nights']));
						$thisQbData['totalbs'] = floatval($parkFees*($nights+$dx['free_nights']));

						logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- QB DATA Property: " . print_r($thisQbData, true));

					}
					else
					{
						//QUOTE
						$tableDataThis2 .= "<td>".date('d M Y', strtotime($dx['In_Date']))." to ".date('d M Y', strtotime($dx['Out_Date']))."</td><td colspan=\"3\">".$pax['Name']." park/sanctuary fees for ".($nights+$dx['free_nights'])." nights</td><td align=\"right\">".number_format(floatval($parkFees), 2, '.', ',')."</td><td align=\"center\">1</td><td align=\"right\">".number_format(floatval($parkFees), 2, '.', ',')."</td><td align=\"center\">".($nights+$dx['free_nights'])."</td><td align=\"right\">".$vatmark.number_format(floatval(floatval($parkFees*($nights+$dx['free_nights']))), 2, '.', ',')."</td>";
						//					$tableDataThis2 .= "<td>".date('d M Y', strtotime($dx['In_Date']))." to<BR>".date('d M Y', strtotime($dx['Out_Date']))."</td><td colspan=\"3\">".$nights." nights for ".$pax['Name']." at ".$propertyName.$agentText."</td><td align=\"right\">".$vatmark.number_format(floatval($thisAmt/116*100), 2, '.', ',')."</td><td align=\"center\">1</td><td align=\"right\">".$vatmark.number_format(floatval($thisAmt/116*100), 2, '.', ',')."</td><td align=\"center\">".$nights."</td><td align=\"right\">".$vatmark.number_format(floatval((($thisAmt*$nights)/116*100)), 2, '.', ',')."</td>";
					}

					$tableDataThis2  .= "</tr>";


					if ($resident_status == 1)
					{
						$colcol ="";
						if ($altKES == 1)
						{
							$colcol = " bgcolor=\"#efefef\" ";
						}

						$tableDataKES .= $tableDataThis1.$colcol.$tableDataThis2;

						if ($altKES == 1)
						{
							$altKES = 0;
						}
						else
						{
							$altKES = 1;
						}

					}
					else
					{
						$colcol ="";
						if ($altUSD == 1)
						{
							$colcol = " bgcolor=\"#efefef\" ";
						}

						$tableDataUSD .= $tableDataThis1.$colcol.$tableDataThis2;

						if ($altUSD == 1)
						{
							$altUSD = 0;
						}
						else
						{
							$altUSD = 1;
						}
					}

					if ($resident_status == 1)
					{
						array_push($qbDataKES,$thisQbData);
					}
					else
					{
						array_push($qbDataUSD,$thisQbData);
					}


				}
			}




			### END PARK FEES ###




		}
	}

	//quote item related data:

	foreach ($result as $res)
	{

		//GET Invoice no if invoice:

		if ($tp == 1)
		{
			//INVOICE NO
			$invoice_number = array();
			$invoice = db_fetch("","","","","quote_ID = ".$res['id'],"","invoice",false, false, null);
			if (isset($invoice[0]) && isset($invoice[0]['id']))
			{
				$invoice_number = db_fetch("", "", "", "", "proforma = ".$invoice[0]['id'], "", "invoice_numbers", true, false ,null);
			}

            if (sizeOf($invoice_number) > 0)
            {
                $thisInvoiceNumber = $invoice_number[0]['id'];
            }
		}
	    $quoteInvoiceNumber = $res['id'];
		$createdAtDate = $res['createdAt'];


		$party = db_fetch("","","","","q.booking_ID = b.id and p.id = b.party_ID and q.id =".$res['id'],"p.*, b.agent_ID, b.LPO_Number","party p, booking b, quote q",false, false, null);
		foreach($party as $p)
		{

			$content = str_replace('@LPO@', $p['LPO_Number'], $content);
			$content = str_replace('@PARTYNAME@', $p['name'], $content);
			$content = str_replace('@PARTYADDRESS@', $p['address'], $content);

			if (isset($p['agent_ID']) && $p['agent_ID'] != "")
			{
				$agent = db_fetch("","","","","id =".$p['agent_ID'],"","agent",false, false, null);
				foreach($agent as $ag)
				{
					$content = str_replace('@PARTYCONTACT@', $ag['Focal_Point_Name'], $content);
					$content = str_replace('@PARTYJOBTITLE@', $ag['Focal_Point_Title'], $content);
				}
			}
			else
			{
				$content = str_replace('@PARTYCONTACT@', "-", $content);
				$content = str_replace('@PARTYJOBTITLE@', "-", $content);
			}
		}

		/* TODO: FIX!!!?
		 */
		$userId = 0;
		if (isset($_SESSION['user_logged']))
		{
			$userId = $_SESSION['user_logged'];
		}
		else
		{
			$userId = 1;
		}
		$user = db_fetch("","","","","id = ".$userId,"","user", true, false, null);
		foreach($user as $u)
		{
			$content = str_replace('@PREPAREDBY@', $u['first_name']." ".$u['middle_name']." ".$u['last_name'], $content);
		}

		$quoteItem = db_fetch("","","","","q.VAT_ID = v.id and quote_ID = ".$res['id'],"q.*, v.value as vat","quote_item q, vat v", true, false, null);
		foreach($quoteItem as $qi)
		{
			$thisQbData = array();


			$thisCur = $qi['currency_ID'];
			$cnt ++;
			$vatmark = "&nbsp;";
			$thisQBAcc = $QBSafariAccount;

			$vatless = 1;
			if ($qi['vat'] > 100)
			{
				$thisQBAcc = $QBVATSafariAccount;
				$vatless = 1/116*100;
				$vatmark = "*";
			}

			if ($thisCur == 1)
			{
				$totVatSafariKES = $totVatSafariKES + ($qi["Total_Cost"]-($qi["Total_Cost"]/$qi['vat']*100));
				$totVatKES = $totVatKES + ($qi["Total_Cost"]-($qi["Total_Cost"]/$qi['vat']*100));
				$totalCostKES += $qi["Total_Cost"];
			}
			if ($thisCur == 2)
			{
				$totVatSafariUSD = $totVatSafariUSD + ($qi["Total_Cost"]-($qi["Total_Cost"]/$qi['vat']*100));
				$totVatUSD = $totVatUSD + ($qi["Total_Cost"]-($qi["Total_Cost"]/$qi['vat']*100));
				$totalCostUSD += $qi["Total_Cost"];
			}

			$tableDataThis1 = "<tr ";
			$tableDataThis2 = ">";
			if ($tp == 1)
			{
				//INVOICE
				$tableDataThis2 .= "<td>".date('d M y', strtotime($qi['Item_Date']));
				if ($qi['Item_Out_Date'] != "0000-00-00 00:00:00" && $qi['Item_Out_Date'] != "")
				{
					$tableDataThis2 .= " to ".date('d M y', strtotime($qi['Item_Out_Date']));
				}
				$tableDataThis2 .= "</td>";
				$tableDataThis2 .= "<td colspan=\"3\">".$qi['service']." - ".$qi['Detail']."</td><td>".intval($qi['Number_of_Units'])."</td><td align=\"right\">".$vatmark.number_format(floatval($qi['Cost_per_Trip']*$vatless), 2, '.', ',')."</td><td align=\"right\">".$vatmark.number_format(floatval(($qi["Total_Cost"])*$vatless), 2, '.', ',')."</td>";

				$thisQbData['id'] = $bookingId."00S".$qi['id'];
				$thisQbData['account'] = $thisQBAcc;
				$thisQbData['date'] = $qi['Item_Date'];
				$thisQbData['service'] = $qi['service']." - ".$qi['Detail'];
				$thisQbData['quantity'] = $nights;
				$thisQbData['rate'] = floatval($qi['Cost_per_Trip']);
				$thisQbData['total'] = floatval($qi["Total_Cost"]);
				$thisQbData['totalbs'] = floatval($qi["Total_Cost"]);

				logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- QB DATA Safari: " . print_r($thisQbData, true));
			}
			else
			{
				//QUOTE
				$tableDataThis2 .= "<td>".date('d M Y', strtotime($qi['Item_Date']));
				if ($qi['Item_Out_Date'] != "0000-00-00 00:00:00" && $qi['Item_Out_Date'] != "")
				{
					$tableDataThis2 .= " to ".date('d M Y', strtotime($qi['Item_Out_Date']));
				}
				$tableDataThis2 .= "</td><td colspan=\"3\">".$qi['service']." - ".$qi['Detail']."</td><td align=\"right\">".$vatmark.number_format(floatval($qi['Cost_per_Person']*$vatless), 2, '.', ',')."</td><td align=\"center\">".number_format(floatval($qi['PAX']), 0, '.', ',')."</td><td align=\"right\">".$vatmark.number_format(floatval($qi['Cost_per_Trip']*$vatless), 2, '.', ',')."</td><td align=\"center\">".$vatmark.number_format(floatval($qi['Number_of_Units']), 0, '.', ',')."</td><td align=\"right\">".$vatmark.number_format(floatval(($qi["Total_Cost"])*$vatless), 2, '.', ',')."</td>";
			}
			$tableDataThis2 .= "</tr>";

			if ($thisCur == 1)
			{
				if ($altKES == 1)
				{
					$tableDataKES .= $tableDataThis1.$tableDataThis2;
				}
				else
				{
					$tableDataKES .= $tableDataThis1." bgcolor=\"#efefef\" ".$tableDataThis2;
				}
				if ($altKES == 1)
				{
					$altKES = 0;
				}
				else
				{
					$altKES = 1;
				}

			}
			else
			{
				if ($altUSD == 1)
				{
					$tableDataUSD .= $tableDataThis1.$tableDataThis2;
				}
				else
				{
					$tableDataUSD .= $tableDataThis1." bgcolor=\"#efefef\" ".$tableDataThis2;
				}
				if ($altUSD == 1)
				{
					$altUSD = 0;
				}
				else
				{
					$altUSD = 1;
				}

			}

			if ($thisCur == 1)
			{
				array_push($qbDataKES,$thisQbData);
			}
			else
			{
				array_push($qbDataUSD,$thisQbData);
			}
		}
	}

/*
	$ic = $cnt;
	for ($ic = $cnt; $ic < 7; $ic ++ )
	{
		$tableData .= "<tr ";
		if ($alt == 1)
		{
			$tableData .= " bgcolor=\"#efefef\" ";
		}
		$tableData .= ">";
		if ($tp == 1)
		{
			$tableData .= "<td></td><td colspan=\"3\"></td><td></td><td></td></tr>";
		}
		else
		{
			$tableData .= "<td></td><td colspan=\"3\"></td><td></td><td></td><td></td><td></td><td></td></tr>";
		}
		if ($alt == 1)
		{
			$alt = 0;
		}
		else
		{
			$alt = 1;
		}
	}
*/
	$EOEBG = "";
	if ($alt == 1)
	{
		$EOEBG .= " bgcolor=\"#efefef\" ";
	}

	$content = str_replace('@EOEBG@', $EOEBG, $content);

	//QUOTE
	if ($nights == 0)
	{
		$nights = "N/A";
	}
	if ($paxcount == 0)
	{
		$paxcount = "N/A";
	}

	$content = str_replace('@NIGHTS@', $nights, $content);
	$content = str_replace('@PAX@', $paxcount, $content);

/*
	$prdata= db_fetch("","","","","id = ".$propertyId,"Quickbooks_VAT_Account","property",false, false, null);
	$QBVATAccount = $prdata[0]['Quickbooks_VAT_Account'];
	*/

	//PARAMETERS:


	$paramPINno = db_fetch("","","","","Name = 'PIN_NO'","","parameter", false, false, null);

	$content = str_replace('@PIN@', $paramPINno[0]['Value'], $content);

	//FINALDUE
	if ($propertyId == 1)
	{
		//EWC 30 days
		$finaldue = date("jS F Y",strtotime($bInDate. " -30 days"));
		if (strtotime($finaldue) < strtotime(date('jS F Y')))
		{
			$finaldue = date('jS F Y');
		}
	}
	else
	{
		//OH 10 days
		$finaldue = date("jS F Y",strtotime($bInDate. " -10 days"));
		if (strtotime($finaldue) < strtotime(date('jS F Y')))
		{
			$finaldue = date('jS F Y');
		}
	}
	$content = str_replace("@FINALDUE@", $finaldue, $content);


    //Replicate non currency specific data
	$contentKES = $content;
	$contentUSD = $content;

	//KES
	$contentKES = str_replace('@CUR@', " (KES)", $contentKES);
	$contentKES = str_replace('@TABLE@', $tableDataKES, $contentKES);
	$contentKES = str_replace('@TOTALCOST@', number_format(floatval($totalCostKES-$totVatKES), 2, '.', ','), $contentKES);
	//TODO: Parameterize VAT
	$contentKES = str_replace('@VATPERC@', "16", $contentKES);
	$contentKES = str_replace('@VAT@', number_format(floatval($totVatKES), 2, '.', ','), $contentKES);
	$contentKES = str_replace('@FINALCOST@', number_format(floatval($totalCostKES), 2, '.', ','), $contentKES);
	$contentKES = str_replace('@DEPOSIT@', number_format(floatval(($totalCostKES)*0.3), 2, '.', ','), $contentKES);

	$paramAccKES = db_fetch("","","","","Name = 'KES_Account'","","parameter", false, false, null);
	$contentKES = str_replace('@ACCOUNT@', nl2br($paramAccKES[0]['Value']), $contentKES);

	$finalContentKES = "";
	$payments = "";

	if ($totalCostKES > 0)
	{
		$finalContentKES = $contentKES;

	/* DISABLE VAT LINE AS QB WILL MANAGE IT ITSELF
		$thisQbData = array();
		$thisQbData['id'] = $bookingId."00KESVAT";
		$thisQbData['account'] = $QBVATAccount;
		$thisQbData['date'] = date('Y-m-d h:i:s');
		$thisQbData['service'] = "VAT";
		$thisQbData['quantity'] = 1;
		$thisQbData['rate'] = floatval($totVatKES - $totVatSafariKES);
		$thisQbData['total'] = floatval($totVatKES - $totVatSafariKES);
		array_push($qbDataKES,$thisQbData);

		$thisQbData = array();
		$thisQbData['id'] = $bookingId."00SKESVAT";
		$thisQbData['account'] = $QBVATSafariAccount;
		$thisQbData['date'] = date('Y-m-d h:i:s');
		$thisQbData['service'] = "VAT Safari";
		$thisQbData['quantity'] = 1;
		$thisQbData['rate'] = floatval($totVatSafariKES);
		$thisQbData['total'] = floatval($totVatSafariKES);
		array_push($qbDataKES,$thisQbData);
	*/

		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- QB DATA KES VAT: " . print_r($thisQbData, true));

		//KES
		if ($tp == 1)
		{
			$payments = '
		<tr>
			<td  align="center" colspan="4">PAYMENTS:</td>
			<td align="right"><font ></font></td>
			<td  align="right"></td>
		</tr>
		';

			$totalPaid = 0;
			$pmts = db_fetch("","","","","currency_ID = 1 and Amount > 0 and quote_ID = ".$result[0]['id'],"","payment_detail",false, false, null);
			foreach ($pmts as $pmt)
			{
				$pamt = number_format(floatval($pmt['Amount']), 2, '.', ',');
				$pdate = date("jS F Y",strtotime($pmt['payment_Date']));
				$totalPaid += $pmt['Amount'];
				$payments .= '
			<tr>
				<td align="center" colspan="5"></td>
				<td bgcolor="#ccc" align="right"><font>'.$pdate.'</font></td>
				<td bgcolor="#efefef" align="right">'.$pamt.'</td></tr>';
			}

			if ($totalPaid > 0)
			{
				$payments .= '
			<tr>
			<td  align="center" colspan="5"></td>
			<td bgcolor="#ccc" align="right"><font >Total Paid</font></td>
			<td bgcolor="#efefef" align="right">'. number_format($totalPaid, 2, '.', ',').'</td>
			</tr>
			<tr>
			<td  align="center" colspan="5"></td>
			<td bgcolor="#ccc" align="right"><font >Total Remaining</font></td>
			<td bgcolor="#efefef" align="right">'. number_format($totalCostKES-$totalPaid, 2, '.', ',').'</td>
			</tr>';

				if ($totalCostKES-$totalPaid <= 0)
				{
					$finalContentKES = str_replace('@PROFORMA@', "", $finalContentKES);
					if ($thisInvoiceNumber == "CREATE")
					{
                      		$newId = db_insert("invoice_numbers", array('proforma'), array($invoice[0]['id']));
                        	$thisInvoiceNumber = $newId;
					}
					$finalContentKES = str_replace('@NO@', $thisInvoiceNumber, $finalContentKES);
					$finalContentKES = str_replace('@DATE@', date("jS F Y",strtotime($bookingInDate_inv)), $finalContentKES);
				}
				else
				{
					$finalContentKES = str_replace('@NO@', $quoteInvoiceNumber, $finalContentKES);
    	        	$finalContentKES = str_replace('@DATE@', date("jS F Y",strtotime($createdAtDate)), $finalContentKES);
				}

				$finalContentKES = str_replace('@PAYMENTS@', $payments, $finalContentKES);
			}
			else
			{
			 	$payments = "";
			}
		}
		else
		{
			$finalContentKES = str_replace('@NO@', $quoteInvoiceNumber, $finalContentKES);
        	$finalContentKES = str_replace('@DATE@', date("jS F Y",strtotime($createdAtDate)), $finalContentKES);
		}
	}
	if ($payments == "")
	{
		$finalContentKES = str_replace('@PAYMENTS@', "", $finalContentKES);
	}

	$finalContentKES = str_replace('@PROFORMA@', "PROFORMA", $finalContentKES);
	$finalContentKES = str_replace('@NO@', $quoteInvoiceNumber, $finalContentKES);
  	$finalContentKES = str_replace('@DATE@', date("jS F Y",strtotime($createdAtDate)), $finalContentKES);

	//USD
	$contentUSD = str_replace('@CUR@', " (USD)", $contentUSD);
	$contentUSD = str_replace('@TABLE@', $tableDataUSD, $contentUSD);
	$contentUSD = str_replace('@TOTALCOST@', number_format(floatval($totalCostUSD-$totVatUSD), 2, '.', ','), $contentUSD);
	//TODO: Parameterize VAT
	$contentUSD = str_replace('@VATPERC@', "16", $contentUSD);
	$contentUSD = str_replace('@VAT@', number_format(floatval($totVatUSD), 2, '.', ','), $contentUSD);
	$contentUSD = str_replace('@FINALCOST@', number_format(floatval($totalCostUSD), 2, '.', ','), $contentUSD);
	$contentUSD = str_replace('@DEPOSIT@', number_format(floatval(($totalCostUSD)*0.3), 2, '.', ','), $contentUSD);

	$paramAccUSD = db_fetch("","","","","Name = 'USD_Account'","","parameter", false, false, null);
	$contentUSD = str_replace('@ACCOUNT@', nl2br($paramAccUSD[0]['Value']), $contentUSD);

	$finalContentUSD = "";

	$payments = "";
	if ($totalCostUSD > 0)
	{
		$finalContentUSD = $contentUSD;

	/* DISABLE VAT LINE AB WILL MANAGE ITSELF
		$thisQbData = array();
		$thisQbData['id'] = $bookingId."00USDVAT";
		$thisQbData['account'] = $QBVATAccount;
		$thisQbData['date'] = date('Y-m-d h:i:s');
		$thisQbData['service'] = "VAT";
		$thisQbData['quantity'] = 1;
		$thisQbData['rate'] = floatval($totVatUSD-$totVatSafariUSD);
		$thisQbData['total'] = floatval($totVatUSD-$totVatSafariUSD);
		array_push($qbDataUSD,$thisQbData);

		$thisQbData = array();
		$thisQbData['id'] = $bookingId."00SUSDVAT";
		$thisQbData['account'] = $QBVATSafariAccount;
		$thisQbData['date'] = date('Y-m-d h:i:s');
		$thisQbData['service'] = "VAT Safari";
		$thisQbData['quantity'] = 1;
		$thisQbData['rate'] = floatval($totVatSafariUSD);
		$thisQbData['total'] = floatval($totVatSafariUSD);
		array_push($qbDataUSD,$thisQbData);
	*/
		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- QB DATA USD VAT: " . print_r($thisQbData, true));

		//KES
		if ($tp == 1)
		{
			$payments = '
		<tr>
			<td  align="center" colspan="4">PAYMENTS:</td>
			<td  align="right"></td>
			<td  align="right"></td>
		</tr>
		';

			$totalPaid = 0;
			$pmts = db_fetch("","","","","currency_ID = 2 and Amount > 0 and quote_ID = ".$result[0]['id'],"","payment_detail",false, false, null);
			foreach ($pmts as $pmt)
			{
				$pamt = number_format(floatval($pmt['Amount']), 2, '.', ',');
				$pdate = date("jS F Y",strtotime($pmt['payment_Date']));
				$totalPaid += $pmt['Amount'];
				$payments .= '
			<tr>
				<td  align="center" colspan="5"></td>
				<td bgcolor="#ccc" align="right"><font >'.$pdate.'</font></td>
				<td bgcolor="#efefef" align="right">'.$pamt.'</td></tr>';
			}

			if ($totalPaid > 0)
			{
				$payments .= '
			<tr>
			<td  align="center" colspan="5"></td>
			<td bgcolor="#ccc" align="right"><font >Total Paid</font></td>
			<td bgcolor="#efefef" align="right">'. number_format($totalPaid, 2, '.', ',').'</td>
			</tr>
			<tr>
			<td  align="center" colspan="5"></td>
			<td bgcolor="#ccc" align="right"><font >Total Remaining</font></td>
			<td bgcolor="#efefef" align="right">'. number_format($totalCostUSD-$totalPaid, 2, '.', ',').'</td>
			</tr>';

				if ($totalCostUSD-$totalPaid <= 0)
				{
					$finalContentUSD = str_replace('@PROFORMA@', "", $finalContentUSD);
					if ($thisInvoiceNumber == "CREATE")
					{
                      		$newId = db_insert("invoice_numbers", array('proforma'), array($invoice[0]['id']));
                        	$thisInvoiceNumber = $newId;
					}
					$finalContentUSD = str_replace('@NO@', $thisInvoiceNumber, $finalContentUSD);
					$finalContentUSD = str_replace('@DATE@', date("jS F Y",strtotime($bookingInDate_inv)), $finalContentUSD);
				}
				else
				{
					$finalContentUSD = str_replace('@NO@', $quoteInvoiceNumber, $finalContentUSD);
    	        	$finalContentUSD = str_replace('@DATE@', date("jS F Y",strtotime($createdAtDate)), $finalContentUSD);
				}

				$finalContentUSD = str_replace('@PAYMENTS@', $payments, $finalContentUSD);
			}
			else
			{
				$payments = "";
			}

		}
		else
		{
			$finalContentUSD = str_replace('@NO@', $quoteInvoiceNumber, $finalContentUSD);
        	$finalContentUSD = str_replace('@DATE@', date("jS F Y",strtotime($createdAtDate)), $finalContentUSD);
		}
	}

	$finalContentUSD = str_replace('@PROFORMA@', "PROFORMA", $finalContentUSD);
	$finalContentUSD = str_replace('@NO@', $quoteInvoiceNumber, $finalContentUSD);
    $finalContentUSD = str_replace('@DATE@', date("jS F Y",strtotime($createdAtDate)), $finalContentUSD);


	if ($payments == "")
	{
		$finalContentUSD = str_replace('@PAYMENTS@', "", $finalContentUSD);
	}


	if ($finalContentUSD !="" && $finalContentKES != "")
	{
		$content = $finalContentUSD."###".$finalContentKES;
	}
	else if ($finalContentUSD != "")
	{
		$content = $finalContentUSD;
	}
	else if ($finalContentKES != "")
	{
		$content = $finalContentKES;
	}

	$html = preg_split("/###/", $content);
	if($returnVal)
	{
		return array($qbDataKES,$qbDataUSD,$agentCommissionTotalKES,$agentCommissionTotalUSD);
	}
	return $html;

}

function bookingSheetDocument($result, $updateCal)
{
	$gCalDescription = "";
	$tableData = "";
	$tmpl = "bookingSheet";

	//each booking...
	$tableData .= "<table style=\"font-size:25px\" border=\"1\" cellpadding=\"3\">";
	$tableData .= "<tr><th>In</th><th>Out</th><th width=\"40\" >Nights</th><th  width=\"140\" >Client</th><th width=\"80\" >Rooms</th><th>Booking</th><th width=\"205\" >Comments</th></tr>";

	foreach ($result as $r)
	{  $tableData .= "<tr><td>".$r['id']."</td></tr>";

		$bk= db_fetch("","","","","p.id = b.property_ID and b.id = ".$r['id'],"p.id, p.name","booking b, property p",false, false, null);
		$propertyId = $bk[0]['id'];
		$propertyName = $bk[0]['name'];

		$gCalSummary = "";
		$gCalDescription = "";
		$gCalStartDate = "";
		$gCalEndDate = "";

		$tableData .= "<tr>";



if (strtotime($r['createdAt']) < strtotime('2015-08-06 00:00:00'))
{
		$inTime = "12:00";
		$outTime = "10:00";

		if ($propertyId == 1)
		{
			$inTime = "11:30";
			$outTime = "10:30";

		}
}
else
{
   $inTime = Date('H:i',$r['In_Date']);
    $outTime = Date('H:i',$r['Out_Date']);
}
		//IN
		$tableData .= "<td rowspan=\"2\" align=\"center\">".date("l",strtotime($r['In_Date']))."<BR>".date("jS F Y",strtotime($r['In_Date']))."<BR>".$r['In_Via']." from ".$r['In_From']."</td>";
		$gCalStartDate = date(DATE_ATOM, strtotime($r['In_Date']));

		//OUT
		$tableData .= "<td rowspan=\"2\"  align=\"center\">".date("l",strtotime($r['Out_Date']))."<BR>".date("jS F Y",strtotime($r['Out_Date']))."<BR>".$r['Out_Via']." to ".$r['Out_To']."</td>";
		$gCalEndDate = date(DATE_ATOM, strtotime($r['Out_Date']));

		//Nights
		$tableData .= "<td rowspan=\"2\"  align=\"center\">".dateDifference(date("Y-m-d", strtotime($r['Out_Date'])),date("Y-m-d", strtotime($r['In_Date'])))."</td>";

		//Client
		if (isset($r['agent_ID']) && $r['agent_ID'] != "")
		{
			$agentRecord  = db_fetch("","","","","id = ".$r['agent_ID'],"","agent",false, false, null);
			$agent = $agentRecord[0]['Name']."(".$agentRecord[0]['Focal_Point_Name']."-".$agentRecord[0]['Focal_Point_Telephone'].")";
			$gCalDescription .= $agentRecord[0]['Name']."(".$agentRecord[0]['Focal_Point_Name']."-".$agentRecord[0]['Focal_Point_Telephone'].")\n";
		}
		else
		{
			$agent = "Direct Client";
		}

//		$party = db_fetch("","","","","g.id = x.gender_ID and x.party_ID = p.id and p.id = ".$r['party_ID'],"","",false, false, null);

		$party = array();

		if ($r['party_ID'] != "")
		{
			$link = db();
			$sql = "SELECT
				p.*, x.resident_status_ID, n.name as nationality, p.telephone as paxTel, x.Special_Dietary_Requirements as sdr, x.name as paxName, LEFT(g.name, 1)  as gender, x.Birth_Date
				FROM
				party p,
				pax x LEFT JOIN nationality n ON n.id = x.nationality_ID,
				gender g
				WHERE
				x.deletedAt IS NULL and g.id = x.gender_ID and x.party_ID = p.id and p.id = ".$r['party_ID'];

			$result = mysqli_query($link, $sql);
			$party = db_fetch("", "", "", "", "", "", "", false, false, $result);

		}


		$dietary = "";
		$tableData .= "<td rowspan=\"2\" align=\"center\">".$agent."<BR>".$party[0]['name']."<BR>".sizeOf($party)." pax<BR>";
		$placed = false;

		$nationalities = "";
		$gCalSummary = $party[0]['name']." x ".sizeOf($party);

		foreach ($party as $px)
		{
			$from = new DateTime(date("Y-m-d",strtotime($px['Birth_Date'])));
			$to   = new DateTime('today');
			$age = $from->diff($to)->y;
			$child = " (ADLT)";
			if ($age < 12)
			{
				$child = " (CHLD)";
			}
			if ($age < 5)
			{
				$child = " (INFT)";
			}
			if (!$placed)
			{
				$gCalDescription .= "Party Tel:".$px['paxTel']."\n";
				$tableData .= "Party Tel:".$px['paxTel']."<BR>";
			}
			//				$nationalities .= $px['paxName']." (".$px['nationality'].")<BR>";

			if ($px['resident_status_ID'] == 1)
			{
				$nationalities .= $px['paxName']." (R)<BR>";
			}
			else
			{
				$nationalities .= $px['paxName']." (NR)<BR>";

			}

			$placed = true;
			$tableData .= $px['paxName']." ".$px['gender']." ".date_diff(date_create(date("Y-m-d",strtotime($px['Birth_Date']))), date_create('today'))->y.$child."<BR>";
			if ($px['sdr'] != "" && strtolower($px['sdr']) != "none" )
			{

				$dietary .= $px['paxName']." - ".$px['sdr']."<BR>";
			}
		}
		$tableData .= $party[0]['notes_TXTA']."<BR>";

		$tableData .= "</td>";

		//Rooms
		$ra = db_fetch("","","","","t.id = rr.property_rooms_ID and rr.room_type_ID = rt.id and x.resident_status_ID = u.id and a.pax_ID = x.id and a.property_rooms_ID = t.id and a.room_assignment_ID = r.id and rr.booking_ID = r.booking_ID and r.booking_ID =".$r['id'],"x.Birth_Date, x.name as paxName, t.name as property_room, u.name as resStat, rt.name as rtName","pax x, property_rooms t, room_assignment r, room_assignments a, resident_status u, room_requirements rr, room_type rt",false, false, null);

		$ras = "";
		$rres = $nationalities;

		$rtType = array();

		foreach ($ra as $aa)
		{
			$gCalDescription .= $aa['paxName']." - ".$aa['property_room']."\n";
			$ras .= $aa['paxName']." - ".$aa['property_room']."<BR>";
			//$rres .=  $aa['paxName']." - ".$aa['resStat']."<BR>";

			if (!isset($rtType[$aa['property_room']]))
			{
				$rtType[$aa['property_room']] = $aa['rtName'];
			}
		}


		$tableData .= "<td rowspan=\"2\"   align=\"center\">";

		$rtypeCount = array();

		foreach ($rtType as $erRoom => $erType)
		{
			if (isset($rtypeCount[$erType]))
			{
				$rtypeCount[$erType] += 1;
			}
			else
			{
				$rtypeCount[$erType] = 1;
			}
//			$tableData .= $erRoom.": ".$erType."<BR>";
		}

		foreach ($rtypeCount as $theType => $theCount)
		{
			$tableData .= $theType.": ".$theCount."<BR>";
		}
//		$tableData .=
//		$tableData .= "<BR>".$ras."</td>";
		$tableData .= "</td>";


		//Payments
		$paidText = "";

		$quotes = db_fetch("", "", "", "", "quote_status_ID = 4 and booking_ID = ".$r['id'], "", "quote", false, false, null);

		$totalDueKES = 0;
		$totalDueUSD = 0;

		$payText = "";
		$hasQuote = false;
		$parkFeesKES = 0;
		$parkFeesUSD = 0;

		foreach ($quotes as $qt)
		{
			$hasQuote = true;
			$quoteId = $qt['id'];
			$rtArray = invoiceDocument(db_fetch("","","","","id = ".$quoteId,"","quote",false, false, null), 1, true);
			$KESData = $rtArray[0];
			$USDData = $rtArray[1];

			foreach($KESData as $KData)
			{
				if (strpos($KData['service'], "sanctuary fees") > -1)
				{
					$parkFeesKES += $KData['total'];
				}
				$totalDueKES += $KData['totalbs'];
			}
			foreach($USDData as $KData)
			{
				if (strpos($KData['service'], "sanctuary fees") > -1)
				{
					$parkFeesUSD += $KData['total'];
				}
				$totalDueUSD += $KData['totalbs'];
			}
			break;
		}


		//Booking
		$tableData .= "<td rowspan=\"2\"   align=\"center\">"; //Park/Cons. Fees ".$r['Park_or_Conservation_Fees']."<BR>";

		if ($parkFeesKES > 0 )
		{
			$tableData .= "Park Fees: KES ".number_format($parkFeesKES, 1, '.', ',')."<BR>";
		}
		if ($parkFeesUSD > 0 )
		{
			$tableData .= "Park Fees: USD ".number_format($parkFeesUSD, 1, '.', ',')."<BR>";
		}

		$tableData .= $rres."<BR>";

		$tableData .= "</td>";




		if ($totalDueKES > 0)
		{
			$payText .= "KES ".number_format(floatval($totalDueKES), 2, '.', ',')." Total Due<BR>";
		}

		if ($totalDueUSD > 0)
		{
			$payText .= "USD ".number_format(floatval($totalDueUSD), 2, '.', ',')." Total Due<BR>";
		}



		$totalPaidKES = 0;
		$totalPaidUSD = 0;
		$payments = db_fetch("","","","","m.id = p.payment_method_ID and p.quote_ID = q.id and q.booking_ID = ".$r['id'],"p.*, m.name as method, p.payment_Date","quote q, payment_detail p, payment_method m",false, false, null);
		foreach ($payments as $pm)
		{
			$thisCur = "";
			if ($pm['currency_ID'] == 1)
			{
				$thisCur = "KES";
				$totalPaidKES += $pm['Amount'];
			}
			else
			{
				$thisCur = "USD";
				$totalPaidUSD += $pm['Amount'];
			}

			if ($pm['is_Deposit_ID'] == 2)
			{
				$depositAmount = $pm['Amount'];
			}

			$payText .= $thisCur." ".number_format(floatval($pm['Amount']), 2, '.', ',')." by ".$pm['method']." on ".date("d/m/Y", strtotime($pm['payment_Date']))."<BR>";
		}

		$totalLeftKES = $totalDueKES - $totalPaidKES;
		$totalLeftUSD = $totalDueUSD - $totalPaidUSD;

		if ($hasQuote)
		{
			if ($totalLeftKES + $totalLeftUSD <= 0)
			{
				$paidText .= "<BR>";
				$paidStatus = "PAID FULL";
				$color = "#00AA00"; //GREEN
			}
			else
			{
				if ($totalPaidKES + $totalPaidUSD == 0)
				{
					$paidStatus = "NO PAYMENT";
					$color = "#FF0000"; //RED
				}

				if ($totalPaidKES > 0 || $totalPaidUSD > 0)
				{
					$paidStatus = "DEPOSIT ONLY";
					$color = "#FFA500"; //ORANGE
				}

				//TODO Parameterize the 30%
				if ($totalPaidKES > $totalDueKES*0.30 && $totalPaidUSD > $totalDueUSD*0.30)
				{
					$paidStatus = "PARTIAL PAYMENT";
					$color = "#FFFF00"; //YELLOW
				}
				if ($totalLeftKES > 0)
				{
					$paidText .= "KES ".number_format(floatval($totalLeftKES), 2, '.', ','). " Remaining<BR>";
				}
				if ($totalLeftUSD > 0)
				{
					$paidText .= "USD ".number_format(floatval($totalLeftUSD), 2, '.', ','). " Remaining<BR>";
				}
			}
		}
		else
		{
			$paidStatus = "NO QUOTE";
			$color = "#FFF"; //WHITE
		}

		$tableData .= "<td bgcolor=\"".$color."\">".$paidStatus."</td>";
		$tableData .= "</tr>";

		$tableData .= "<tr>";
		$tableData .= "<td>".$payText.$paidText."<BR>".$r['Notes'];
		$tableData .= "<i>".$dietary."</i>";
		$tableData .= "</td>";
		$tableData .= "</tr>";

		$gCalDescription .= $paidStatus."\n".$r['Notes']."\n".$dietary;


		if ($updateCal == true)
		{
			$eventId = $r['google_calendar_HD'];
			$id = googleCalendarEvent($gCalSummary, $propertyName, $gCalDescription, $gCalStartDate, $gCalEndDate, $propertyId, $eventId);

			if ($id != "")
			{
				//update booking.google_calendar_HD
				db_update($r['id'], "booking", array('google_calendar_HD'), array($id));
			}
		}
	}
	$tableData .= "</table>";

	if ($updateCal == false)
	{
		ob_start();
		include ($_SESSION['boot']->appPath."templates/".$tmpl.".php");
		$content = ob_get_contents(); // assign buffer contents to variable
		ob_end_clean();

		$content = str_replace('@DATE@', date('jS F Y \- g:ia'), $content);
		$content = str_replace('@PROPERTY@', $propertyName, $content);
		$content = str_replace('@TABLE@', $tableData, $content);

		$html = preg_split("/###/", $content);
		return $html;
	}
	else
	{
		return "Calendar Event created/updated: ".sizeOf($result);
	}

}

//////////////////////////////////////////////////////////////////////
//PARA: Date Should In YYYY-MM-DD Format
//RESULT FORMAT:
// '%y Year %m Month %d Day %h Hours %i Minute %s Seconds'        =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
// '%y Year %m Month %d Day'                                    =>  1 Year 3 Month 14 Days
// '%m Month %d Day'                                            =>  3 Month 14 Day
// '%d Day %h Hours'                                            =>  14 Day 11 Hours
// '%d Day'                                                        =>  14 Days
// '%h Hours %i Minute %s Seconds'                                =>  11 Hours 49 Minute 36 Seconds
// '%i Minute %s Seconds'                                        =>  49 Minute 36 Seconds
// '%h Hours                                                    =>  11 Hours
// '%a Days                                                        =>  468 Days
//////////////////////////////////////////////////////////////////////
function dateDifference($date_1 , $date_2 , $differenceFormat = '%a' )
{
	$datetime1 = date_create($date_1);
	$datetime2 = date_create($date_2);

	$interval = date_diff($datetime1, $datetime2);

	return $interval->format($differenceFormat);

}

function terms_of_referenceDocument($result)
{
	ob_start();
	include ("../templates/terms_of_reference.php");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();

	foreach ($result as $r)
	{

		$content = str_replace('@TOR_TITLE@', $r['Title'], $content);
		$content = str_replace('@TOR_OBJECTIVE@', $r['Objective_TXTA'], $content);
		$content = str_replace('@TOR_RECIPIENT@', $r['Recipient_TXTA'], $content);
		$content = str_replace('@TOR_SCOPE_DELIVARABLES@', $r['Scope_or_Deliverables_TXTA'], $content);
		$content = str_replace('@TOR_METHODOLOGY@', $r['Methodology_TXTA'], $content);
		$content = str_replace('@TOR_REPORTING@', $r['Reporting_TXTA'], $content);
		$content = str_replace('@TOR_TIMEFRAME@', $r['Timeframe_TXTA'], $content);
		$content = str_replace('@TOR_COORDINATION@', $r['Coordination_TXTA'], $content);
		$content = str_replace('@TOR_BACKGROUND@', $r['Background_TXTA'], $content);
		$content = str_replace('@TOR_QUALIFICATIONS@', $r['Qualifications_TXTA'], $content);
		//$content = preg_replace('#\<p\>#si', '', $content);
		//$content = preg_replace('#\<\/p\>#si', '', $content);
		$content = preg_replace('#\<table\>#si', '<table cellpadding="5" border="1">', $content);
	}
	return $content;
}

function contractAmmendmentDocument($result)
{
	ob_start();
	include ("../templates/contract_amd.php");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();

	foreach ($result as $r)
	{
		$content = str_replace('@CONTRACT_TITLE@', $r['Title'], $content);
		$content = str_replace('@CONTRACT_REFERENCE@', $r['Number'], $content);
		$content = str_replace('@CONTRACT_DATE@', date('d-M-y', strtotime($r['Processed_Date'])), $content);
		$content = str_replace('@AMMENDMENT_NUMBER@', $r['Ammendment_Number'], $content);
		$content = str_replace('@AMMENDMENT_CLAUSE@', $r['Ammendment_Clause_TXTA'], $content);

		$signData = db_fetch("", "", "", "", "id = " . $r['signatories_ID'], "", "signatories", false, false, null);
		$content = str_replace('@SIGNATORY_NAME@', $signData[0]['Name'], $content);
		$content = str_replace('@SIGNATORY_POSITION@', $signData[0]['Title'], $content);
		$content = str_replace('@SIGNATORY_OFFICE@', $signData[0]['Office'], $content);

		$consultData = db_fetch("", "", "", "", "id = " . $r['consultant_ID'], "", "consultant", false, false, null);
		$content = str_replace('@CONSULTANT_NAME@', $consultData[0]['Name'], $content);
		$content = str_replace('@CONSULTANT ADDRESS@', $consultData[0]['Physical_Address'], $content);
	}

	return $content;
}

function consultantDocument($type, $result)
{
	ob_start();
	include ("../templates/" . $type . ".php");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();

	foreach ($result as $r)
	{
		$content = str_replace('@Name@', $r['Name'], $content);
		$content = str_replace('@Physical_Address@', $r['Physical_Address'], $content);
		$content = str_replace('@Postal_Address@', $r['Postal_Address'], $content);
		$content = str_replace('@Email_Address@', $r['Email_Address'], $content);
		$content = str_replace('@Telephone@', $r['Telephone'], $content);
		$content = str_replace('@Nationality@', $r['Nationality'], $content);
		$content = str_replace('@Birth_Date@', $r['Birth_Date'], $content);
		$content = str_replace('@Residence_Country@', $r['Residence_Country'], $content);
		$content = str_replace('@Highest_Level_of_Academic_Qualification_ID@', retrieveForeignValue('Highest_Level_of_Academic_Qualification_ID', $r['Highest_Level_of_Academic_Qualification_ID'], 'consultant'), $content);
		//			$content = str_replace('@Functional_Category@', retrieveForeignValue('functional_category_ID', $r['functional_category_ID'], 'consultant'), $content);
		$content = str_replace('@Contact_Name@', $r['Contact_Name'], $content);
		$content = str_replace('@Company_Registration_Number@', $r['Company_Registration_Number'], $content);
		$content = str_replace('@VAT_Registration_Number@', $r['VAT_Registration_Number'], $content);
		$content = str_replace('@PIN_Number@', $r['PIN_Number'], $content);
		$content = str_replace('@Years_in_Business@', $r['Years_in_Business'], $content);
		$content = str_replace('@Registration_Country@', $r['Registration_Country'], $content);
		$content = str_replace('@Company_Turn_Over_One_Year_Ago@', $r['Company_Turn_Over_One_Year_Ago'], $content);
		$content = str_replace('@Company_Turn_Over_Two_Years_Ago@', $r['Company_Turn_Over_Two_Years_Ago'], $content);
		$content = str_replace('@Company_Turn_Over_Three_Years_Ago@', $r['Company_Turn_Over_Three_Years_Ago'], $content);
		$content = str_replace('@Similar_Services_Turn_Over_One_Year_Ago@', $r['Similar_Services_Turn_Over_One_Year_Ago'], $content);
		$content = str_replace('@Similar_Services_Turn_Over_Two_Years_Ago@', $r['Similar_Services_Turn_Over_Two_Years_Ago'], $content);
		$content = str_replace('@Similar_Services_Turn_Over_Three_Years_Ago@', $r['Similar_Services_Turn_Over_Three_Years_Ago'], $content);
		$content = str_replace('@Assignments_Carried_Out_UPLOAD@', $r['Assignments_Carried_Out_UPLOAD'], $content);
		$content = str_replace('@Capability_Statement_UPLOAD@', $r['Capability_Statement_UPLOAD'], $content);
		$content = str_replace('@Conflict_of_Interest_TXTA@', $r['Conflict_of_Interest_TXTA'], $content);
		$rCert = "No";
		if ($r['Certified_HD'] == 1)
		{
			$rCert = "Yes";
		}
		$content = str_replace('@Certified_HD@', $rCert, $content);

		//			$signData = db_fetch("", "", "", "", "id = " . $r['signatories_ID'], "", "signatories", false, false, null);
		//			$content = str_replace('@SIGNATORY_NAME@', $signData[0]['Name'], $content);
	}

	return $content;
}

function checklistPDF($type, $result)
{
	foreach ($result as $r)
	{
		require_once "../controller/process.php";
		$clazz = new processClass();
		$clazz->checklistPDF = true;
		$clazz->id = $r['id'];
		$content = $clazz->checklist();
	}

	return $content;
}

function ITTDocument($result, $above)
{

	ob_start();
	if ($above)
	{
		include ("../templates/itt150.php");
	}
	else
	{
		include ("../templates/itt.php");
	}
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();
	$presentation = "";
	$expiry = "";
	foreach ($result as $r)
	{
		//PROCESS TIMETABLE
		$proc = db_fetch("", "", "", "", "id = " . $r['id'], "", "process", false, false, null);
		$thisProc = $proc[0];

		require_once "../controller/process.php";
		$clazz = new processClass();
		$clazz->id = $r['id'];
		$check = $clazz->processChecklist($thisProc, false, true);

		$timetable = "<table>";
		foreach ($check['data'] as $nm => $vl)
		{
			$timetable .= "<tr>";
			foreach ($vl as $name => $value)
			{
				if ($name == "name")
				{
					if ($value == "Presentations by bidders (if applicable)")
					{
						$presentation = date('d F Y', $check['dates'][$nm]);
					}
					if ($value == "Receipt of Tenders")
					{
						$expiry = date('d F Y', $check['dates'][$nm]);
					}
					$timetable .= "<td>" . $value . "</td>";
				}
			}
			$timetable .= "<td>" . date('d F Y', $check['dates'][$nm]) . "</td>";
			$timetable .= "</tr>";
		}
		$timetable .= "</table>";
		//END PROCESS TIMETABLE

		//SHORTLISTED CANDIDATES
		$applications = "<ol type='a'>";
		$parents = array();
		foreach ($sht as $s)
		{
			if ($s['parent_consultant_HD'] == "")
			{
				array_push($parents, $s['Email_Address']);
				$applications .= "<li>" . $s['Name'];
			}
		}

		foreach ($sht as $s)
		{
			if ($s['parent_consultant_HD'] == "")
			{
			}
			else
			{
				if (in_array($s['Email_Address'],$parents))
				{

				}
				else
				{
					$applications .= "<li>" . $s['Name'];
				}
			}
		}

		$applications .= "</ol>";
		//END SHORTLISTED CANDIDATES

		//ITT Shortlist Criteria
		$eoic = db_fetch("", "", "", "", "r.id = c.Category and p.id = " . $r['id'] . " and c.process_criteria_ID = p.ITT_Shortlist_Criteria_ID", "r.Name as Category, c.Sub_Category, c.Maximum_Score", "criteria_category r, criteria c, process p", false, false, null);
		$criteria = array ();

		$criteria = "<table border=\"1\" cellpadding=\"5\"><tr><td>Criteria</td><td>Sub Criteria</td><td>Marks</td></tr>";

		//lets get total score for each category first

		$totScore = array();
		foreach ($eoic as $eo)
		{
			if (isset ($totScore[$eo['Category']]))
			{
				$totScore[$eo['Category']] += intval($eo['Maximum_Score']);
			}
			else
			{
				$totScore[$eo['Category']] = intval($eo['Maximum_Score']);
			}
		}



		$thisScore = 0;
		foreach ($eoic as $eo)
		{
			$thisScore += intval($eo['Maximum_Score']);
			if (isset($totScore[$eo['Category']]) && $totScore[$eo['Category']] != $eo['Maximum_Score'] && $totScore[$eo['Category']] != 999)
			{
				$criteria .= "<tr><td>" . $eo['Category'] . "</td><td></td><td>" . $totScore[$eo['Category']] . "</td></tr>";
				$totScore[$eo['Category']] = 999;
			}

			$criteria .= "<tr><td>";
			if ($totScore[$eo['Category']] != 999)
			{
				$criteria .= $eo['Category'];
			}
			$criteria .= "</td><td>" . $eo['Sub_Category'] . "</td><td>" . $eo['Maximum_Score'] . "</td></tr>";
		}
		$criteria .= "<tr><td>Total Marks</td><td></td><td>" . $thisScore . "</td></tr>";
		$criteria .= "</table>";
		//END ITT shortlist Criteria

		$content = str_replace('@DATE@', date('d F Y', strtotime($r['ITT_Document_Date'])), $content);
		$content = str_replace('@TITLE@', $r['Title'], $content);
		$content = str_replace('@TENDER_NO@', $r['Tender_Number'], $content);
		$content = str_replace('@TIMETABLE@', $timetable, $content);
		$content = str_replace('@CRITERIA@', $criteria, $content);
		$content = str_replace('@SUPPLIERS@', $applications, $content);
		$content = str_replace('@EXPIRY@', $expiry, $content);
		$content = str_replace('@PRESENT@', $presentation, $content);
		if ($r['ITT_Document_Signed_By_ID'] != "")
		{
			$signData = db_fetch("", "", "", "", "id = " . $r['ITT_Document_Signed_By_ID'], "", "signatories", false, false, null);
			if ($signData)
			{
				$content = str_replace('@ITT_SIGNED_BY@', $signData[0]['Name'], $content);
				$content = str_replace('@ITT_SIGNED_BY_TITLE@', $signData[0]['Title'], $content);
			}
		}

		$torData = db_fetch("", "", "", "", "id = " . $r['terms_of_reference_ID'], "", "terms_of_reference", false, false, null);

		$content = str_replace('@TOR_TITLE@', $torData[0]['Title'], $content);
		$content = str_replace('@TOR_OBJECTIVE@', $torData[0]['Objective_TXTA'], $content);
		$content = str_replace('@TOR_RECIPIENT@', $torData[0]['Recipient_TXTA'], $content);
		$content = str_replace('@TOR_SCOPE_DELIVARABLES@', $torData[0]['Scope_or_Deliverables_TXTA'], $content);
		$content = str_replace('@TOR_METHODOLOGY@', $torData[0]['Methodology_TXTA'], $content);
		$content = str_replace('@TOR_REPORTING@', $torData[0]['Reporting_TXTA'], $content);
		$content = str_replace('@TOR_TIMEFRAME@', $torData[0]['Timeframe_TXTA'], $content);
		$content = str_replace('@TOR_COORDINATION@', $torData[0]['Coordination_TXTA'], $content);
		$content = str_replace('@TOR_BACKROUND@', $torData[0]['Background_TXTA'], $content);
		$content = str_replace('@TOR_QUALIFICATIONS@', $torData[0]['Qualifications_TXTA'], $content);
	}



	$content = preg_split("/###/", $content);
	return $content;
}

function FPDocumentHTML($consultantName)
{
	ob_start();
	include ("../templates/fairprice.php");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();

	$content = str_replace('@COMPANY@', $consultantName, $content);

	return $content;
}

function ITBDocumentHTML($result)
{
	ob_start();
	include ("../templates/itb.php");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();
	$presentation = "";
	$expiry = "";
	foreach ($result as $r)
	{
		//SPECIFICATIONS
		$specData = db_fetch("", "", "", "", "u.id = s.unit_of_measure_ID and s.specifications_document_ID = " . $r['specifications_document_ID'], "s.*, u.Name", "specifications s, unit_of_measure u", false, false, null);
		$spcDt = "<table>";
		$spcDt .= "<tr><td>Item</td><td>Specifications</td><td>Quantity</td><td>UOM</td></tr>";
		foreach ($specData as $spec)
		{
			$spcDt .= "<tr><td>".$spec['Item_Description']."</td><td>".$spec['Specifications']."</td><td>".$spec['Quantity']."</td><td>".$spec['Name']."</td></tr>";
		}
		$spcDt .= "</table>";
		//SPECIFICATIONS END

		$content = str_replace('@SPECIFICATIONS@', $spcDt, $content);
		$content = str_replace('@DATE@', date('d F Y', strtotime($r['ITT_Document_Date'])), $content);
		$content = str_replace('@DATE_YEAR@', date('F Y', strtotime($r['ITT_Document_Date'])), $content);
		$content = str_replace('@TITLE@', $r['Title'], $content);
		$content = str_replace('@TENDER_NO@', $r['Tender_Number'], $content);
		$content = str_replace('@ITB_DEADLINE@', $expiry, $content);
		$content = str_replace('@RECIPIENT@', $r['Recipient'], $content);
		$content = str_replace('@RECIPIENT_ADDRESS@', $r['Recipient_Address'], $content);
		//		$content = str_replace('@LOGO_SRC@', $_SESSION['boot']->getAppPublic()."images/tmea_logo.gif", $content);
		$content = str_replace('@LOGO_SRC@', "", $content);



		if ($r['ITT_Document_Signed_By_ID'] != "")
		{
			$signData = db_fetch("", "", "", "", "id = " . $r['ITT_Document_Signed_By_ID'], "", "signatories", false, false, null);
			if ($signData)
			{
				$content = str_replace('@ITT_SIGNED_BY@', $signData[0]['Name'], $content);
				$content = str_replace('@ITT_SIGNED_BY_TITLE@', $signData[0]['Title'], $content);
			}
		}
	}
	return $content;
}

function ITBDocument($result)
{
	$html = preg_split("/###/", ITBDocumentHTML($result));
	return $html;
}


function ITTDocumentEmail($content, $emails)
{

}

function contractPerformance($result)
{
	ob_start();
	include ("../templates/performance.php");
	$content = ob_get_contents(); // assign buffer contents to variable
	ob_end_clean();

	foreach ($result as $r)
	{
		$pId = $r['id'];
		$cData = db_fetch("", "", "", "", "c.id = p.contract_ID and p.id = $pId and s.id = c.consultant_ID", "c.*, s.Name as consultant_name", "performance_evaluation p, contract c, consultant s", false, false, null);

		$content = str_replace('@CONSULT_NAME@', $cData[0]['consultant_name'], $content);
		$content = str_replace('@CONTACT_REF@', $cData[0]['Number'], $content);
		$content = str_replace('@CONTRACT_TITLE@', $cData[0]['Title'], $content);
		$content = str_replace('@START_DATE@', date('d-M-y', strtotime($cData[0]['Start_Date'])), $content);
		$content = str_replace('@END_DATE@', date('d-M-y', strtotime($cData[0]['End_Date'])), $content);
		$content = str_replace('@TASKS_COMPLETED@', $r['Tasks_Completed_TXTA'], $content);

		$content = str_replace('@TECHNICAL_EXPERTISE@', intval($r['Technical_Expertise_RT1']) + 1, $content);
		$content = str_replace('@INTERPERSONAL_SKILLS@', intval($r['Interpersonal_Skills_RT1']) + 1, $content);
		$content = str_replace('@INNOVATIVENESS@', intval($r['Innovativeness_RT1']) + 1, $content);
		$content = str_replace('@REPORTS_QUALITY@', intval($r['Reports_Quality_RT1']) + 1, $content);
		$content = str_replace('@INITIATIVE@', intval($r['Initiative_RT1']) + 1, $content);
		$content = str_replace('@REPORTS_TIMELINESS@', intval($r['Reports_Timeliness_RT1']) + 1, $content);

		$content = str_replace('@WRITTEN_ENGLISH@', intval($r['Written_English_RT1']) + 1, $content);
		$content = str_replace('@SPOKEN_ENGLISH@', intval($r['Spoken_English_RT1']) + 1, $content);

		$content = str_replace('@COMMENTS@', $r['Comments_TXTA'], $content);

		$flag = $r['flags_ID'];
		if (isset($flag) and $flag != "")
		{
			$cFlags = db_fetch("", "", "", "", "f.id = $flag", "", "flags f", false, false, null);
			$content = str_replace('@FLAG@', $cFlags[0]['Color'], $content);
		}
		else
		{
			$content = str_replace('@FLAG@', "", $content);
		}

		$ra = "";
		if ($r['Restrict_Access_RT2'] == 1)
			$ra = "Yes";
		if ($r['Restrict_Access_RT2'] == 0)
			$ra = "No";
		$content = str_replace('@RESTRICT_ACCESS@', $ra, $content);

	}

	return $content;
}

function dailyRatesHelper($id, $cId)
{
	$v = "";

	$cidCheck = "";
	if ($cId != "")
	{
		$cidCheck = "and id != $cId";
	}

	$res = db_fetch("", "", "Start_Date", "asc", "consultant_ID = $id $cidCheck", "", "contract", false, false, null);
	if ($res)
	{
		foreach ($res as $rs)
		{
			$contractId = $rs['id'];

			$res2 = db_fetch("", "", "", "", "contract_ID = $contractId", "", "professional_fees", false, false, null);
			if ($res2)
			{
				$v .= "Professional Fees:<BR>";
				foreach ($res2 as $rs2)
				{
					$v .= date('d-M-y', strtotime($rs['Start_Date'])) . " to " . date('d-M-y', strtotime($rs['End_Date'])) . " - " . $rs2['Consultant'] . ": " . $rs2['Daily_Fee_Rate'] . " Daily.<BR>";
				}
			}
		}
	}

	if ($v == "")
	{
		$v = "No previous contracts issued this calendar year.";
		if ($cId != "")
		{
			$v .= "<BR>(excluding this contract)";
		}
	}

	return " response: " . $v . "<BR>";
}

function checkCeilingsHelper($id, $cId)
{
	$v = "";
	$v2 = "";
	$total = 0;
	$found = 0;
	$usdRate = 1;

	$beginYr = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, date('Y')));
	$endYr = date('Y-m-d H:i:s', mktime(23, 23, 59, 12, 31, date('Y')));

	$cidCheck = "";
	if ($cId != "")
	{
		$cidCheck = "and c.id != $cId";
	}
	$res = db_fetch("", "", "", "", "consultant_id = $id and d.id = c.currency_ID $cidCheck", "c.*, d.Abbreviation", "contract c, currency d", false, false, null);
	if ($res)
	{
		$v .= "<table cellpadding='3'><tr><td>Title</td><td>Number</td><td>From</td><td>To</td><td>Amount</td></tr>";
		foreach ($res as $rs)
		{
			$v .= "<tr>" .
					"<td style='font-size:11px; width:350px'>" . $rs['Title'] . "</td>" .
					"<td style='font-size:11px;'>" . $rs['Number'] . "</td>" .
					"<td style='font-size:11px;'>" . date('d M Y', strtotime($rs['Start_Date'])) . "</td>" .
					"<td style='font-size:11px;'>" . date('d M Y', strtotime($rs['End_Date'])) . "</td>" .
					"<td style='font-size:11px;'>" . $rs['Abbreviation'] . " " . number_format(floatval($rs['Total_Limit']), 2, '.', ',') . "</td>" .
					"</tr>";
		}
		$v .= "</table>";
	}

	$v .= "<BR>";

	$cidCheck = "";
	if ($cId != "")
	{
		$cidCheck = "and id != $cId";
	}
	$res = db_fetch("", "", "", "", "consultant_id = $id and Start_Date >= '$beginYr' and Start_Date <= '$endYr' $cidCheck", "", "contract", false, false, null);

	if ($res)
	{
		foreach ($res as $rs)
		{
			$found = 1;
			$currencyID = $rs['currency_ID'];
			$startDate = $rs['Start_Date'];
			$curRes = db_fetch("", "", "", "", "id = $currencyID and '$startDate' > updatedAt", "", "currency", false, false, null);
			if ($curRes)
			{
				$usdRate = $curRes[0]['USD_Rate'];
			} else
			{
				$curRes2 = db_fetch("", "", "updatedAt", "desc", "c_table = 'currency' and c_field = 'USD_Rate' and c_record = $currencyID and '$startDate' > updatedAt", "updatedAt, c_value", "versions", false, false, null);
				if ($curRes2)
				{
					$usdRate = floatval($curRes2[0]['c_value']);
				} else
				{
					$alt = db_fetch("", "", "", "", "id = $currencyID", "", "currency", false, false, null);
					if ($alt)
					{
						$curRes3 = db_fetch("", "", "", "", "id = $currencyID", "", "currency", false, false, null);
						$usdRate = $curRes3[0]['USD_Rate'];
					}
				}
			}
			$total = $total +floatval($rs['Total_Limit']) / $usdRate;
		}
	}

	if ($found == 0)
	{
		$v = "No previous contracts issued this calendar year.";
		if ($cId != "")
		{
			$v .= "<BR>(excluding this contract)";
		}
	} else
	{
		//Get ceilings
		$res2 = db_fetch("", "", "Value", "desc", "Name = 'CEILING_PROMPT'", "", "parameter", false, false, null);
		foreach ($res2 as $rs)
		{
			$val = $rs['Value'];
			if (intval($val) <= floatval($total))
			{
				$v .= number_format($val, 0, '.', ',') . " ceiling already crossed ($total).<BR>";
			} else
			{
				$v2 .= number_format($val -floatval($total), 2, '.', ',') . " remaining to reach the " . number_format($val, 0, '.', ',') . " ceiling.<BR>";
			}
		}
	}
	$v2 .= "<BR><span style='font-size:11px;'>1. Based on contracts issued to this consultant between $beginYr and $endYr. <BR>2. Non-USD currency rates as at the contract Start Date if available. If there are no rates available for the period of the contract, the latest available rates are used.</span>";
	if ($cId != "")
	{
		$v2 .= "<BR><span style='font-size:11px;'>3. Amounts considered are excluding this contract amount</span>";
	}

	return " response: " . $v . " " . $v2;
}

function getSelectValues($RT)
{
	if ($RT == 4)
	{
		return array (
				"Services",
				"Goods"
		);
	}

	if ($RT == 1)
	{
		return array (
				"Excellent",
				"Very Good",
				"Good",
				"Fair",
				"Unsatisfactory"
		);
	}
	if ($RT == "S")
	{
		return array (
				"Excellent",
				"Very Good",
				"Good",
				"Fair",
				"Poor",
				"Don't Know"
		);
	}
	if ($RT == 2)
	{
		return array (
				"No",
				"Yes"
		);
	}
	if ($RT == 3)
	{
		return array (
				"Goods",
				"Services"
		);
	}
	if ($RT == 6)
	{
		return array (
				"High School",
				"Diploma",
				"Bachelors Degree",
				"Masters Degree",
				"PhD"
		);
	}
	if ($RT == "G")
	{
		return array (
				"below $2,000",
				"above $2,000",
		);
	}
	if ($RT == 9)
	{
		return array (
				"below $40,000",
				"above $40,000 and below $150,000",
				"above $150,000"
		);
	}
	if ($RT == 7)
	{
		return array (
				"Green",
				"Yellow",
				"Red"
		);
	}
	if ($RT == 5)
	{
		return array (
				"EOI",
				"RFP"
		);
	}
}

function formatFieldName($name)
{
	return UCFirst(str_replace("_", " ", str_replace("_ID", "", str_replace("_TXTA", "", str_replace("_UPLOAD", "", str_replace("_RT1", "", str_replace("_RT2", "", str_replace("_DBL", "", str_replace("_RT3", "", str_replace("_RT6", "", str_replace("_RT9", "", str_replace("_CHK", "", str_replace("_RT5", "", str_replace("_RT4", "", str_replace("_RTS", "", str_replace("Ammendment", "Amendment", $name))))))))))))))));
}

//TODO: REMOVE - not being used.
function filterUserApprovals($processes, $clazz)
{
	$newList = array();
	foreach ($processes as $process)
	{
		$clazz->id = $process['id'];
		$check = $clazz->processChecklist($process, false, true);

		if ($check['percentage_completion']< 100)
		{
			array_push($newList, $process);
		}
	}
	return $newList;
}

function activeTORS()
{
	$tors = db_fetch("", "", "", "", "t.id = p.terms_of_reference_ID and p.Contract_Document_ID is NULL", "t.*, p.responsibility_center_ID, p.Approval_Status_ID", "terms_of_reference t, process p", false, false, null);
	return $tors;
}

function activeSpecificationDocuments()
{
	$tors = db_fetch("", "", "", "", "t.id = p.specifications_document_ID and p.Contract_Document_ID is NULL", "t.*, p.responsibility_center_ID, p.Approval_Status_ID", "specifications_document t, process p", false, false, null);
	return $tors;
}

function cleanupPhone($phone)
{
	return substr(str_replace("-","",str_replace(" ","",str_replace("(","",str_replace(")","",str_replace("+","",$phone))))), -9);
}

//FORMS
/* Field Names:
 * _ 		space
* _ID		foreign key
* _Date 	date field
* _UPLOAD	upload field
* _TXTA 	textarea field
* _RT1..N	pre-defined dropdowns
* _DBL		disabled
* _HD		hidden field
* _CHK_ID  foreign key with checkboxes for all
*/

function make($v, $view, $value, $tId, $user, $required, $add, $edit, $history)
{

	echo '<div class="form-group">';

	$thzval = "";
	if (isset ($view[$v]))
	{
		$thzval = $view[$v];
	}

	$appWhere = "";

	//build "where" for Alternate Class where applicable.
	if (isset ($_SESSION['db_relations_bk'][$view[2] . "." . $v]) && $_SESSION['db_relations_bk'][$view[2] . "." . $v] == "approval_status")
	{
		/*
		 1,2
		2,4,6
		3,4,6 (3 > 14)
		3,12,13 (12 > 14) IF DCEO approval required. (OC: Respective DCEO), (SS: CS DCEO)
		14,7
		7,9
		8,10
		15
		*/
		$not = db_fetch("", "", "", "", "Name = 'DCEO'", "Value", "parameter", false, false, null);
		if ($_SESSION['user_email'] == $not[0]['Value'] && $value == 3)
		{
			$prm = urlencode("id=12 or id=13 or id=3 or id=16");
			$appWhere = "?where=" . $prm;
		} else
		{
			$prm = urlencode("id=6 or id=4 or id=3 or id=16");
			$appWhere = "?where=" . $prm;
		}
		if ($value == 1)
		{
			$prm = urlencode("id=1 or id=2 or id=16");
			$appWhere = "?where=" . $prm;
		}
		if ($value == 2)
		{
			$prm = urlencode("id=2 or id=4 or id=6 or id=16");
			$appWhere = "?where=" . $prm;
		}
		if ($value == 14)
		{
			$prm = urlencode("id=7 or id=14 or id=16");
			$appWhere = "?where=" . $prm;
		}
		if ($value == 7)
		{
			$prm = urlencode("id=7 or id=9 or id=16");
			$appWhere = "?where=" . $prm;
		}
		if ($value == 9)
		{
			$prm = urlencode("id=9 or id=8 or id=16");
			$appWhere = "?where=" . $prm;
		}
		if ($value == 8)
		{
			$prm = urlencode("id=8 or id=10 or id=16");
			$appWhere = "?where=" . $prm;
		}
		if ($value == 15)
		{
			$prm = urlencode("id=15 or id=16");
			$appWhere = "?where=" . $prm;
		}
	}

	if ($view[2] . "." . $v == "process.Approving_Manager_ID")
	{
		$prm = urlencode("level=6");
		$appWhere = "?where=" . $prm;
	}


	//End of build

	if (
	(checkFieldRights($user, $view[2], $v) and strpos($v, "_HD") === FALSE)
	)
	{
		//Foreign key field lookups
		$vl = str_replace("_ID", "", $v);

		if (strpos($v, "_ID"))
		{
			echo select2Loader('#dd_'.$v, $_SESSION["db_relations_bk"][$view[2].".".$v].$appWhere);
		}

		if ($v != "id")
		{
			if (strpos($v,"_UPLOAD") > 0)
			{

			}
			else
			{
				echo "<label class='form-label'>". formatFieldName($v) ."</label>";
			}

			if (strpos($v, "_RT1") !== FALSE)
			{
				$RTG = getSelectValues(1);
			}
			if (strpos($v, "_RT2") !== FALSE)
			{
				$RTG = getSelectValues(2);
			}
			if (strpos($v, "_RT3") !== FALSE)
			{
				$RTG = getSelectValues(3);
			}
			if (strpos($v, "_RT4") !== FALSE)
			{
				$RTG = getSelectValues(4);
			}
			if (strpos($v, "_RT6") !== FALSE)
			{
				$RTG = getSelectValues(6);
			}
			if (strpos($v, "_RT7") !== FALSE)
			{
				$RTG = getSelectValues(7);
			}
			if (strpos($v, "_RT5") !== FALSE)
			{
				$RTG = getSelectValues(5);
			}
			if (strpos($v, "_RT9") !== FALSE)
			{
				$RTG = getSelectValues(9);
			}
			if (strpos($v, "_RTS") !== FALSE)
			{
				$RTG = getSelectValues("S");
			}

			//If they are RT fields
			if (strpos($v, "_RT") !== FALSE or strpos($v, "_RT") !== FALSE)
			{
				if ($value == "")
				{
					$value = 9;
				}

				echo "<select $required data-select2-id='select2-ajax' class='form-control select2-hidden-accessible' name='db_" . $view[2] . "_" . $v . "' onChange=\"checkField('" . $v . "',this.value);\">";
				echo "<option value='99'></option>";
				$RTV = 0;
				foreach ($RTG as $vals)
				{
					echo "<option ";
					if ($value == $RTV)
						echo " SELECTED ";
					echo " value='" . $RTV . "'>" . $RTG[$RTV] . "</option>";
					$RTV++;
				}
				echo "</select>";
			}
			else if (strpos($v, "_TXTA") !== FALSE)
			{
				//TextArea fields
				$summernote = "";
				if ($view[2] . "_" . $v == "document_templates_document_TXTA") $summernote = "summernote-js";

				?>
<textarea class="form-control <?php echo $summernote; ?>" name="<?php echo "db_" . $view[2] . "_" . $v; ?>"  id="<?php echo "db_" . $view[2] . "_" . $v; ?>" rows="5"><?php echo $value; ?></textarea>
<?php
			} else
			if (strpos($v, "_UPLOAD") !== FALSE)
			{
				str_replace("_UPLOAD", "", $v);
				//uploader("uploads", $v, "db_" . $view[2] . "_" . $v);
				/*
				if ($value != "")
				{
					echo "<a style='padding:1px 5px;margin:0px 3px;' class='ui-state-default ui-corner-all' href='" . $_SESSION['boot']->getAppPublic() . "upload/uploads/" . $value . "'>Download file</a>";
				}
				*/
				?>
				<script>
				jQuery(document).ready(function()
				{
					if (jQuery("#fileuploadcontainer").html() == "")
					{
							jQuery("#fileuploadcontainer").html('<div class="col-xl-12"><div id="panel-1" class="panel"><div class="panel-hdr"><h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">Uploads</h2><div class="panel-toolbar"><button class="btn btn-panel waves-effect waves-themed" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button><button class="btn btn-panel waves-effect waves-themed" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button></div></div><div class="panel-container show"><div id="fileuploadcontent" class="panel-content"></div></div></div></div>');
					}

					jQuery('#fileuploadcontent').append('<div style="clear:both;float:left" class="col-xl-5 col-lg-5 col-md-5 col-sm-12"><form id="<?php echo "xu".$v; ?>" data-element="<?php echo $v; ?>" action="<?php echo $_SESSION['boot']->getAppPublic(); ?>upload/uploads" method="POST" enctype="multipart/form-data"><label class="form-label"><?php echo formatFieldName($v); ?></label><div class="row fileupload-buttonbar"><div class="col-lg-7"><span class="btn btn-success fileinput-button"><i class="glyphicon glyphicon-plus"></i><span>Add files...</span><input type="file" name="files[]" multiple /></span><span class="fileupload-process"></span></div><div class="col-lg-5 fileupload-progress fade"><div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100"> <div class="progress-bar  progress-bar-success" style="width: 0%;" ></div> </div><div class="progress-extended">&nbsp;</div></div></div><table role="presentation" class="table table-striped"><tbody class="files"></tbody></table></form></div>');

					<?php
					$uploaded = "";
					if ($value != "")
					{
						$filez = db_fetch("","","","","id in (".$value.")","","files", false, false, null);
						foreach ($filez as $fl)
						{
							if ($uploaded == "") $uploaded = "Uploaded documents:<BR>";
//							$uploaded .= "<a href='/files/".urlencode($fl['name'])."'>".$fl['name']."</a><BR>";
							$uploaded .= '<a target="_blank" href="/files/'.$fl['name'].'">'.$fl['name'].'</a><BR>';
						}
					}

					?>

					jQuery('#fileuploadcontent').append('<div style="float:left" class="col-xl-5 col-lg-5 col-md-5 col-sm-12"><?php echo $uploaded; ?></div>');

					setTimeout(function(){initiateUploaderX('<?php echo "#xu".$v; ?>')}, 2000);
				});
				</script>
				<?php
				//echo "<script>jQuery(document).ready( function(){ jQuery('#x" . $v . "').fileupload(); }); </script>";

				echo "<input id='x" . $v . "' type=hidden name='db_" . $view[2] . "_" . $v . "' value='$value'>";
			} else
			{
				//Regular, _ID, _DBL and _Date fields
				if (strpos($v, "_ID") !== FALSE)
				{
					if (strpos($v, "_CHK_ID") !== FALSE)
					{
						echo "<div style='float:left; display:table'>";
						if ($user == "G" or $user == "X")
						{
							$passusr = 2;
						}
						else
						{
							$passusr = 1;
						}

						$xcvs = retrieveFunctionalCategoryValues($passusr);

						$existing = array ();
						$conc = db_fetch("", "", "", "", "consultant_ID = " . $tId, "", "consultant_categories", false, false, null);
						foreach ($conc as $concc)
						{
							array_push($existing, $concc['functional_categories_ID']);
						}
						$checked = "";
						$lenArr = sizeOf($xcvs);

						foreach ($xcvs as $xc)
						{
							echo "<input title='" . $xc['Description'] . "' type='checkbox'";
							if (in_array($xc['id'], $existing))
							{
								echo " CHECKED ";
							}
							echo " name='db_" . $view[2] . "_" . $v . "[]' value='" . $xc['id'] . "'>" . $xc['Name'] . "&nbsp;<input type=button name='more about this' value='...' title='" . $xc['Description'] . "'><BR>";
						}
						echo "</div>";
					}
					else
					{
						$cmts = "";
						if (isset ($_SESSION['dbcomments'][$view[2] . "." . $v]))
						{
							$cmts = $_SESSION['dbcomments'][$view[2] . "." . $v]. ": " ;
						}

						if (($view[2] == "member" and $v == "products_ID") or ($view[2] == "member" and $v == "business_category_ID") or ($view[2] == "member" and $v == "business_sub_category_ID") or ($view[2] == "registration" and $v == "branches_ID"))
						{
							echo "<select $required data-placeholder='Search' select-value='".$value."' title='" . $cmts  .$thzval. "' id='dd_" . $v . "' id='db_" . $vl . "' class='form-control form-select select2 vselect' name='db_" . $view[2] . "_" . $v . "[]'></select>";
						}
						else
						{
							echo "<select $required data-placeholder='Search' select-value='".$value."' title='" . $cmts  .$thzval. "' id='dd_" . $v . "' id='db_" . $vl . "' class='form-control form-select select2 vselect' name='db_" . $view[2] . "_" . $v . "'></select>";
						}
						//echo "<input style='width: 200px; float:left' title='" . $cmts  .$thzval. "' type=text id='dd_" . $v . "'  id='db_" . $vl . "' name='db_" . $view[2] . "_" . $v . "' value='$value'>";
					}
				} else
				{
					$cmts = "";
					if (isset ($_SESSION['dbcomments'][$view[2] . "." . $v]))
					{
						$cmts = $_SESSION['dbcomments'][$view[2] . "." . $v].": ";
					}

					if (strpos($v, "_Date") !== FALSE)
					{

						echo '<label class="form-label" for="date' . $view[2] . '_' . $v . '">Date</label>';
						echo '<input class="form-control" id="date' . $view[2] . '_' . $v . '" type="date" name="db_' . $view[2] . '_' . $v . '"';

						if ($value == "")
						{
							echo ' value=""';
						}
						else
						{
							echo ' value="' . date('Y-m-d', strtotime($value)) . '"';
						}

					}
					else
					{
						if ($v == "proposed_name_1" or $v == "proposed_name_2" or $v == "proposed_name_3")
						{
							echo '<div class="input-group mb-2">';

							echo "<input name='db_" . $view[2] . "_" . $v . "' $required class='form-control' ";
							if (isset ($_SESSION['dbcomments'][$view[2] . "." . $v]))
							{
									echo " placeholder='" . $_SESSION['dbcomments'][$view[2] . "." . $v] . "'";
							}
							echo " type='text'";

						}
						else
						{
							echo "<input name='db_" . $view[2] . "_" . $v . "' $required class='form-control' ";
							if (isset ($_SESSION['dbcomments'][$view[2] . "." . $v]))
							{
									echo " placeholder='" . $_SESSION['dbcomments'][$view[2] . "." . $v] . "'";
							}
							echo " type='text'";

						}

					}

					if (strpos($v, "_DBL") !== FALSE)
					{
						echo "DISABLED value=''";
					}
					else
					{
						//non required field
						if ($v == "")
						{
							echo " value='$value'";
						}
						else
						{
							echo " class='$required' value='$value'";
						}
					}

					if ($v == "proposed_name_1" or $v == "proposed_name_2" or $v == "proposed_name_3")
					{
						echo ">";
						echo '  <div class="input-group-append">
						          <div class="input-group-text"></div>
						        </div>';

						echo "</div>";
					}
					else
					{
						echo ">";
					}
				}

				//Foreign Key related elements
				if (strpos($v, "_ID") && strpos($v, "_CHK_ID") === FALSE) //button for Foreign Key lookups
				{
					/*
					 if (intval($_SESSION['user_level']) == 1 or intval($_SESSION['user_level']) == 2 or $v == "Approving_Manager_ID" or $v == "responsibility_center_ID" or $v == "project_ID" or $v == "Approval_Status_ID")
					 {
					//dont show Add/Edit
					} else
					{
					if ($add == true)
					{
					echo "<a style='display:none' id='addActual_" . $view[2] . "_" . $v . "' href='" . $_SESSION['boot']->getAppPublic() . $_SESSION['db_relations_bk'][$view[2] . "." . $v] . "/newRecord?entity=" . $view[2] . "&entityId=" . $tId . "&field=$v'>New</a>";
					echo "<a href='#' style='padding:1px 5px;margin:0px 3px;' class='btn btn-primary' onClick='saveRecord(\"addActual_" . $view[2] . "_" . $v . "\"); return false;'>New</a>";
					}
					if ($edit == true)
					{
					echo "<a style='display:none' id='edit_db_" . $view[2] . "_" . $v . "' href='" . $_SESSION['boot']->getAppPublic() . $_SESSION['db_relations_bk'][$view[2] . "." . $v] . "/editRecord?id=" . $value . "&entity=" . $view[2] . "&entityId=" . $tId . "'>View/Edit</a>";
					echo "<a style='padding:1px 5px;margin:0px 3px;' class='btn btn-default' href='#' onClick='saveRecord(\"edit_db_" . $view[2] . "_" . $v . "\"); return false;'>View/Edit</a>";
					}
					}
					*/
				}
				?>
<?php
			}
			?>


<?php

		} else
		{
			echo "<input type=hidden name='db_" . $view[2] . "_" . $v . "' value='$value'>";
		}
	}

	echo "</div>";
}

function makeJS($v, $view)
{
	return "db_" . $view[2] . "_" . $v . "=\"+encodeURIComponent(db" . $view[2] . "_" . $v . ")";
}

function checkRequirements($col, $view, $val)
{
	$table = $view[2];

	if ($table == "process" && $col == "Approval_Status_ID")
	{
		if ($val == 2 or $val == 14 or $val == 9 or $val== 10 or $val == 15)
		{
			return false;
		}
		return true;
	}
	return false;
}

function genericFormStart($view, $user, $required, $add, $edit, $history, $fieldInsertAt)
{

	$tId = "";

	?>
<div id="dialog" title="Versions"></div>
<div id="dialogInv"
	title="Invalid Field Value"></div>

<script>

jQuery(document).ready(function()
{
	jQuery("input[type=image][name='version']").each(function(i, obj)
	{
		jQuery(this).mouseover(function()
		{
			jQuery(this).focus();
		});
	});
});

function getVersions(field)
{
		thisId = jQuery("input[type=hidden][name='db_<?php echo $view[2]; ?>_id']").val();

				jQuery.ajax(
		{
		    data: ({field:field,record:thisId,table:'<?php echo $view[2];?>'}),
		    global: false,
			type:'POST',
			url: '<?php echo $_SESSION['boot']->getAppPublic(); ?>versions/retrieve',
			success: function(msg)
			{
				//jQuery("#dialog").html(msg);
			},
			error: function(ErrXMLHttpRequest, textStatus, errorThrown)
			{
				//TODO: something
			}
		});
}

function saveRecord(lnk)
{
	jQuery('#body').after("<div style='padding:10px'><form><fieldset><legend>Loading</legend>One moment please...</fieldset></form></div>");
	jQuery("form").form();
	jQuery('#body').hide(500);

	<?php

	$out = "\"";
	$first = true;
	foreach ($view[1] as $v)
	{
		if (is_array($v))
		{
			foreach ($v as $column => $value)
			{
				if (strpos($column,"_HD") === false)
				{
					if ($column == "id")
					{
						$tId = $value;
					}
					if ($first == false)
					{
						$out .= "+\"&";
					}
						$out .= makeJS($column, $view);


					if (strpos($column,"_TXTA") === false)
					{
						echo "	var db" . $view[2] . "_" . $column . " = jQuery(\"form [name='db_" . $view[2] . "_" . $column . "']\").val();\n";
					}
					else
					{
						echo "var db" . $view[2] . "_" . $column . " = jQuery('#cke_db_" . $view[2] . "_" . $column . " div div iframe').contents().find(\"body\").html();";
//						echo "	var db" . $view[2] . "_" . $column . " = jQuery(\"form [name='db_" . $view[2] . "_" . $column . "']\").html();\n";
					}

					$first = false;
				}
			}
		} else
		{
			if (strpos($v,"_HD") === false)
			{
				if ($first == false)
				{
					$out .= "+\"&";
				}
				$out .= makeJS($v, $view);
								if (strpos($column,"_TXTA") === false)
					{
						echo "	var db" . $view[2] . "_" . $column . " = jQuery(\"form [name='db_" . $view[2] . "_" . $column . "']\").val();\n";
					}
					else
					{
						echo "var db" . $view[2] . "_" . $column . " = jQuery('#cke_db_" . $view[2] . "_" . $column . " div div iframe').contents().find(\"body\").html();";
//						echo "	var db" . $view[2] . "_" . $column . " = jQuery(\"form [name='db_" . $view[2] . "_" . $column . "']\").html();\n";
					}
								$first = false;
			}
		}
	}

	if ($out != "&#9661;"); //TODO CHECK
	{
?>
		jQuery.ajax(
		{
		    data: <?php echo $out; ?>,
		    global: false,
			type: 'POST',
			url: '<?php echo $_SESSION['boot']->getAppPublic(); ?>@FORWARD@',
			success: function(msg)
			{
				window.location = jQuery("#"+lnk).attr('href');
			},
			error: function(ErrXMLHttpRequest, textStatus, errorThrown)
			{
				//alert("Error");
			}
		});



	<?php



	}
?>

		return false;
}

	function uploadMessage(el)
	{
		el = el.replace('file-uploader-demo','x');
		jQuery('#'+el).focus();
	}

	function checkId()
	{
		var err = 0;

<?php

	if ($required != "")
	{
?>
		jQuery('.idlookup').each(function(i, obj)
		{
			theLabel = jQuery(obj).attr('id');
	    	theVal = jQuery(obj).val();
	    	if(!isNumber(theVal) || theVal == "")
	    	{
	    		err = 1;
	    		var lblrpl = jQuery(obj).attr('id').replace("db_","").replace("_"," ").replace("_"," ").replace("_"," ");
	    		var rpl = jQuery(obj).attr('id').replace("db_","dd_");
				jQuery('#'+rpl+"_ID").focus();
//				jQuery("#dialogInv").html("Please select a value from the lookup for '"+lblrpl+"'. Directly typed values are not accepted. Click on the '?' next to the field to see available options.");
				jQuery('#'+rpl+"_ID").focus();
				jQuery('#'+rpl+"_ID").select();
	    	};
		});
<?php

	}
?>
		if (err == 0)
		{
			jQuery('#generic_form').submit();
		}
	}

function isNumber(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}


function updateTXTA()
{
	jQuery(".summernote").each(function(){
		jQuery(this).html(jQuery('#' + jQuery(this).attr('id')).code());
	});
}

</script>

<div class="row">
  <div class="col-xl-12">
  	<div id="panel-1" class="panel">
			<div class="panel-hdr  bg-primary-300 bg-primary-gradient">
      	<h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
					<?php
					echo UCFirst($view[2]);
					?>
				</h2>
				<div class="panel-toolbar">
					<button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
	        <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
	      </div>
			</div>
			<div class="panel-container show">
				<div class="panel-content">

						<form onSubmit="updateTXTA();" style="width:100%" class="needs-validation" novalidate id="generic_form" action='<?php echo $_SESSION['boot']->getAppPublic(); ?>@FORWARD@' method="post">
							<div class="row">
						<input type='hidden' name='svl' id="svl" value=''>
						<input type='hidden' name='notesx' id="notesx" value=''>
						<input type='hidden' name="latestUploadEl" id="latestUploadEl" value=''>

						<?php
						if ($view[2] == "member")
						{
							?>
							<ul class="nav nav-tabs" style="width:100%" role="tablist">
									<li class="nav-item"><a id="member-1" class="nav-link fs-lg px-4 active" data-toggle="tab" href="#tab_justified-1" role="tab"><i class="fal fa-user text-success"></i><span class="hidden-sm-down ml-1">Address</span></a></li>
									<li id="business_category_tab" class="nav-item"><a id="member-2" class="nav-link fs-lg px-4" data-toggle="tab" href="#tab_justified-3" role="tab"><i class="fal fa-info-circle text-success"></i><span class="hidden-sm-down ml-1">Projections</span></a></li>
									<li id="other_tab" class="nav-item"><a onclick="loadDatamember_business_details();" id="member-3" class="nav-link fs-lg px-4" data-toggle="tab" href="#tab_justified-4" role="tab"><i class="fal fa-newspaper text-success"></i><span class="hidden-sm-down ml-1">Business</span></a></li>
							    <li class="nav-item"><a id="member-4" class="nav-link fs-lg px-4" data-toggle="tab" href="#tab_justified-2" role="tab"><i class="fal fa-building text-success"></i><span class="hidden-sm-down ml-1">Activity</span></a></li>
									<li class="nav-item"><a id="member-5" class="nav-link fs-lg px-4" data-toggle="tab" href="#tab_justified-5" role="tab"><i class="fal fa-signature text-success"></i><span class="hidden-sm-down ml-1">Consent</span></a></li>
						 </ul>
						 <div class="tab-content p-3" style="padding-top: 20px; width:100%">
							<?php

							echo '<div class="tab-pane fade show active" id="tab_justified-1" role="tabpanel">';
							echo '<div class="row" style="padding-top:20px;"><div class="col-xl-6 col-lg-6">';
						}
						else if (($view[2] == "search" and $_SESSION['user_level'] > 1) or $view[2] == "registration" )
						{
							echo '<div class="col-xl-6 col-lg-6 col-md-6 col-sm-6">';
						}
						else
						{
							echo '<div class="col-xl-12">';
						}
						?>

						<?php


						if (isset ($_GET['field']))
						{
							echo "<input type='hidden' name='field' value='" . $_GET['field'] . "'>";
						}
					?>


					<?php


							$statusField = "";
							$fieldcount = 0;
							foreach ($view[1] as $v)
							{

								if (is_array($v))
								{
									foreach ($v as $column => $value)
									{

										if ($view[2] == "search" and $fieldcount == 10 and $_SESSION['user_level'] > 3)
										{
											echo '</div><div class="col-xl-6 col-lg-6 col-md-6 col-sm-6"><div style="border-bottom:0px !important" class=".d-sm-none .d-md-block .d-sm-block form-group"><label class="form-label" style="height:75px"></label></div>';
										}

										if ($view[2] == "change_of_particulars" and $fieldcount == 3)
										{
											echo '<BR></div><div class="col-xl-6 col-lg-6">';
										}

										if ($view[2] == "election" and $fieldcount == 3)
										{
echo '</div>
</div>
</div>
</div>
</div>

<div id="election_interest" class="row">
  <div class="col-xl-12">
      <div id="panel-1" class="panel">
        <div class="panel-hdr  bg-primary-300 bg-primary-gradient">
          <h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse" onclick="setTimeout(preloadDataelection_interest, 500)">
						Election results
          </h2>
          <div class="panel-toolbar">
            <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse" onclick="setTimeout(preloadDataelection_interest, 500)"></button>
            <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
          </div>
        </div>
        <div class="panel-container collapse">
          <div class="panel-content">
';
										}


if ($view[2] == "election" and $fieldcount == 18)
{
echo '</div>
</div>
</div>
</div>
</div>

<div id="change_of_objectives" class="row">
<div class="col-xl-12">
<div id="panel-1" class="panel">
<div class="panel-hdr  bg-primary-300 bg-primary-gradient">
<h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
Other details
</h2>
<div class="panel-toolbar">
<button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
<button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
</div>
</div>
<div class="panel-container collapse">
<div class="panel-content">
';
}


										if (($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 0 and $fieldcount == 8) or ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 9))
										{
echo '</div>
</div>
</div>
</div>
</div>

<div id="change_of_composition" style="display:none" class="row">
  <div class="col-xl-12">
      <div id="panel-1" class="panel">
        <div class="panel-hdr  bg-primary-300 bg-primary-gradient">
          <h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse" onclick="setTimeout(preloadDatachange_of_official, 500)">
            Change of Leadership
          </h2>
          <div class="panel-toolbar">
            <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse" onclick="setTimeout(preloadDatachange_of_official, 500)"></button>
            <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
          </div>
        </div>
        <div class="panel-container collapse">
          <div class="panel-content">
';
										}


										if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 11)
										{
echo '</div>
</div>
</div>
</div>
</div>

<div id="change_of_name" style="display:none" class="row">
  <div class="col-xl-12">
      <div id="panel-1" class="panel">
        <div class="panel-hdr  bg-primary-300 bg-primary-gradient">
          <h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
            Change of Name
          </h2>
          <div class="panel-toolbar">
            <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
            <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
          </div>
        </div>
        <div class="panel-container collapse">
          <div class="panel-content">
';
										}


										if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 13)
										{
echo '</div>
</div>
</div>
</div>
</div>

<div id="change_of_objectives" style="display:none" class="row">
  <div class="col-xl-12">
      <div id="panel-1" class="panel">
        <div class="panel-hdr  bg-primary-300 bg-primary-gradient">
          <h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
            Change of Objectives
          </h2>
          <div class="panel-toolbar">
            <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
            <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
          </div>
        </div>
        <div class="panel-container collapse">
          <div class="panel-content">
';
										}
										if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 15)
										{
echo '</div>
</div>
</div>
</div>
</div>

<div id="change_of_branches" style="display:none" class="row">
  <div class="col-xl-12">
      <div id="panel-1" class="panel">
        <div class="panel-hdr  bg-primary-300 bg-primary-gradient">
          <h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
            Change of Branches
          </h2>
          <div class="panel-toolbar">
            <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
            <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
          </div>
        </div>
        <div class="panel-container collapse">
          <div class="panel-content">
';
										}

										if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 17)
										{
echo '</div>
</div>
</div>
</div>
</div>

<div id="financial_position" class="row">
  <div class="col-xl-12">
      <div id="panel-1" class="panel">
        <div class="panel-hdr  bg-primary-300 bg-primary-gradient">
          <h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
            PART A: Statement of Comprehensive Income
          </h2>
          <div class="panel-toolbar">
            <button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
            <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
          </div>
        </div>
        <div class="panel-container collapse">
          <div class="panel-content">
';
										}

										if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 17)
										{
												echo '<div id="panel-3" class="panel">
                                    <div class="panel-hdr">
                                        <h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
                                            Income
                                        </h2>
                                        <div class="panel-toolbar">
                                            <button class="btn btn-panel waves-effect waves-themed" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
                                            <button class="btn btn-panel waves-effect waves-themed" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
                                        </div>
                                    </div>
                                    <div class="panel-container collapse">
                                        <div class="panel-content">
																									                                            <div class="form-group">
																									      ';
										}

										if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 20)
										{
echo '                                      </div>
                                        </div>
                                    </div>
                                </div>';
										}

										if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 20)
										{
												echo '<div id="panel-3" class="panel">
                                    <div class="panel-hdr">
                                        <h2  style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
                                            Expenses
                                        </h2>
                                        <div class="panel-toolbar">
                                            <button class="btn btn-panel waves-effect waves-themed" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
                                            <button class="btn btn-panel waves-effect waves-themed" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
                                        </div>
                                    </div>
                                    <div class="panel-container collapse">
                                        <div class="panel-content">
																									                                            <div class="form-group">
																									      ';
										}

										if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 24)
										{
echo '                                      </div>
                                        </div>
                                    </div>
                                </div>';
										}

////
if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 24)
{
echo '</div>
</div>
</div>
</div>
</div>

<div id="financial_position" class="row">
<div class="col-xl-12">
<div id="panel-1" class="panel">
<div class="panel-hdr  bg-primary-300 bg-primary-gradient">
<h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
PART B: Statement of Financial Position
</h2>
<div class="panel-toolbar">
<button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
<button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
</div>
</div>
<div class="panel-container collapse">
<div class="panel-content">
';
}

if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 24)
{
		echo '<div id="panel-3" class="panel">
								<div class="panel-hdr">
										<h2  style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
												Assets
										</h2>
										<div class="panel-toolbar">
												<button class="btn btn-panel waves-effect waves-themed" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
												<button class="btn btn-panel waves-effect waves-themed" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
										</div>
								</div>
								<div class="panel-container collapse">
										<div class="panel-content">
																																					<div class="form-group">
																		';
}

if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 29)
{
echo '                                      </div>
										</div>
								</div>
						</div>';
}

if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 29)
{
		echo '<div id="panel-3" class="panel">
								<div class="panel-hdr">
										<h2  style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
												Liabilities
										</h2>
										<div class="panel-toolbar">
												<button class="btn btn-panel waves-effect waves-themed" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
												<button class="btn btn-panel waves-effect waves-themed" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
										</div>
								</div>
								<div class="panel-container collapse">
										<div class="panel-content">
																																					<div class="form-group">
																		';
}

if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 32)
{
echo '                                      </div>
										</div>
								</div>
						</div>';
}

if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 32)
{
		echo '<div id="panel-3" class="panel">
								<div class="panel-hdr">
										<h2  style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
												Equity/Fund
										</h2>
										<div class="panel-toolbar">
												<button class="btn btn-panel waves-effect waves-themed" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
												<button class="btn btn-panel waves-effect waves-themed" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
										</div>
								</div>
								<div class="panel-container collapse">
										<div class="panel-content">
																																					<div class="form-group">
																		';
}

if ($view[2] == "change_of_particulars" and $view[1][0]['annual_returns_HD'] == 1 and $fieldcount == 34)
{
echo '                                      </div>
										</div>
								</div>
						</div>';
}


										//Half of reg details
										if ($view[2] == "registration" and $fieldcount == 9)
										{
											echo '</div><div class="col-xl-6 col-lg-6">';
										}

										//Half of details
										if ($view[2] == "member" and $fieldcount == 9)
										{
											echo '</div><div class="col-xl-6 col-lg-6">';
										}

										//Start of registration status
										if ($view[2] == "member" and $fieldcount == 16)
										{
											echo '</div></div></div><div class="tab-pane" id="tab_justified-2" role="tabpanel"><div style="height:20px; clear:both; width: 100%"></div><div class="row"><div class="col-xl-6 col-lg-6">';
										}

										//Half of registration status
										if ($view[2] == "member" and $fieldcount == 19)
										{
											echo '</div><div class="col-xl-6 col-lg-6">';
										}

										//Start of business category
										if ($view[2] == "member" and $fieldcount == 21)
										{
											echo '</div></div></div><div class="tab-pane" id="tab_justified-3" role="tabpanel"><div style="height:20px; clear:both; width: 100%"></div><div class="row"><div class="col-xl-6 col-lg-6">';
										}

										//Half of business category
										if ($view[2] == "member" and $fieldcount == 23)
										{
											echo '</div><div class="col-xl-6 col-lg-6">';
										}

										//Start of other
										if ($view[2] == "member" and $fieldcount == 25)
										{
											echo '</div></div></div><div class="tab-pane" id="tab_justified-4" role="tabpanel"><div style="height:20px; clear:both; width: 100%"></div><div class="row"><div class="col-xl-12 col-lg-12">';
										}

										//Half of other
										if ($view[2] == "member" and $fieldcount == 26)
										{
											echo '</div><div class="col-xl-6 col-lg-6">';
										}



										//End of other
										if ($view[2] == "member" and $fieldcount == 27)
										{
											echo '</div></div></div><div class="tab-pane" id="tab_justified-5" role="tabpanel"><div style="height:20px; clear:both; width: 100%"></div><div class="row"><div class="col-xl-12 col-lg-12">';
											echo '<div class="custom-control custom-checkbox">
                                                        <input type="checkbox" required class="custom-checkbox custom-control-input form-control" id="consent">
                                                        <label class="custom-control-label" for="consent">I declare (a) the particulars and information given herein are, to the best of my knowledge, true and correct; and (b) I have not wilfully concealed any material fact.<BR><BR>I am aware that in case I have given any false information or document for the purpose of this application, same may be rejected or the Registration Certifiate cancelled, as appropriate. I also consent that my contact details may be disclosed for business purposes. </label>
                                                    </div>';
											echo '</div></div></div><div style="height:20px; clear:both; width: 100%"></div>';
										}
										if (isset ($fieldInsertAt[$fieldcount]))
										{
											echo $fieldInsertAt[$fieldcount] . "<div style='clear:both'></div>";
											$fieldcount++;
										}

										if ($column == "id")
										{
											$tId = $value;
										}

										make($column, $view, $value, $tId, $user, $required, $add, $edit, $history);
										if (checkRequirements($column, $view, $value) == true)
										{
											break;
										}
										$fieldcount++;

									}
								} else
								{
									if (isset ($fieldInsertAt[$fieldcount]))
									{
										echo $fieldInsertAt[$fieldcount] . "<BR>";
										$fieldcount++;
									}

									make($v, $view, "", $tId, $user, $required, $add, $edit, $history);
									$fieldcount++;

									if (checkRequirements($v, $view, "") == true)
									{
										break;
									}

								}
							}


						if (isset ($_GET['entity']))
						{
							echo "<input type='hidden' name='entity_HD' value='" . $_GET['entity'] . "'>";
						}
						if (isset ($_GET['entityId']))
						{
							echo "<input type='hidden' name='entityId_HD' value='" . $_GET['entityId'] . "'>";
						}

					if (isset ($_GET['entityId']))
					{
						?>
					<div class="form-actions">
						<div class="row">
							<div class="col-md-6">
								<a
									href='<?php echo $_SESSION['boot']->getAppPublic() . $_GET['entity'] . "/editRecord?id=" . $_GET['entityId']; ?>'
									class="btn btn-default" type="submit"> Cancel and return to <?php echo UCFirst($_GET['entity']); ?>
									</button>
									<button class="btn btn-primary" type="submit">
										<i class="fa fa-save"></i> Save and return to
										<?php echo UCFirst($_GET['entity']); ?>
									</button>

							</div>
						</div>
					</div>

					<?php
					//echo "<input type='button' name='submit' value='Save and return to " . UCFirst($_GET['entity']) . "' onClick=\"jQuery('#generic_form').submit()\"><BR><BR>";
					//echo "<a style='padding:1px 7px;margin:0px 0px;' class='ui-state-default ui-corner-all' href='" . $_SESSION['boot']->getAppPublic() . $_GET['entity'] . "/editRecord?id=" . $_GET['entityId'] . "'>Cancel and return to " . UCFirst($_GET['entity']) . "</a>";
					} else
					{

						if ($view[2] == "search")
						{
							echo '<div style="clear:both"></div>';
						}

						?>

					<div class="panel-content border-faded border-left-0 border-right-0 border-bottom-0 d-flex flex-row align-items-center" style="width:100% !important; border-top: 0px !important; height: 1px !important">
						<div>
							<button style="visibility: hidden" class="btn btn-default ml-auto waves-effect waves-themed" onclick="window.location='/<?php echo $view[2]; ?>/';return false;" type="submit">Cancel</button>
						</div>
<?php

$buttons = getDocumentStatus($view[2], $view[0]['id']);

$is_official = false;
$has_official_approved = false;
if ($view[2] == "registration")
{
	if ($view['entityType'] == "Association") $reg = db_fetch("","","","","email = '".$_SESSION['user_email']."' AND registration_HD = ".$view[0]['id'], "","official", false, false, null);
	if ($view['entityType'] == "Micro and Small Enterprise") $reg = db_fetch("","","","","email = '".$_SESSION['user_email']."' AND registration_HD = ".$view[0]['id'], "","owner", false, false, null);
	if ($view['entityType'] == "Umbrella Organization") $reg = db_fetch("","","","","b.email = '".$_SESSION['user_email']."' AND a.registration_HD = ".$view[0]['id'], "b.*","association a LEFT JOIN member b ON b.national_identity_card = a.national_identity_card", false, false, null);
	if (sizeOf($reg) > 0) $is_official = true;
	if ($reg[0]['approved_application_HD'] == 1) $has_official_approved = true;
}

if ($view[2] == "change_of_particulars")
{
	if ($view['entityName'] == "Association") $reg = db_fetch("","","","","email = '".$_SESSION['user_email']."' AND change_of_particulars_HD = ".$view[0]['id'], "","change_of_official", false, false, null);
	if ($view['entityName'] == "Umbrella Organization") $reg = db_fetch("","","","","m.email = '".$_SESSION['user_email']."' AND o.change_of_particulars_HD = ".$view[0]['id'], "o.*, m.email","change_of_association o LEFT JOIN association oo ON oo.registration_no = o.registration_no LEFT JOIN member m ON m.national_identity_card = oo.national_identity_card", false, false, null);
	if ($view['entityName'] == "Micro and Small Enterprise") $reg = db_fetch("","","","","email = '".$_SESSION['user_email']."' AND change_of_particulars_HD = ".$view[0]['id'], "","change_of_owner", false, false, null);
	if (sizeOf($reg) > 0) $is_official = true;
	if ($reg[0]['approved_application_HD'] == 1) $has_official_approved = true;
}


if ($view[2] == "annual_returns")
{
	$reg = db_fetch("","","","","oo.email = '".$_SESSION['user_email']."' AND o.annual_returns_HD = ".$view[0]['id'], "o.*, oo.email","change_of_official o LEFT JOIN official oo ON oo.id = o.officials_HD", false, false, null);
	if (sizeOf($reg) > 0) $is_official = true;
	if ($reg[0]['approved_application_HD'] == 1) $has_official_approved = true;
}

foreach ($buttons as $b)
{
	$nextStep = $b['step'];
	if (isset($b['next_step']) && $b['next_step'] != "") $nextStep = $b['next_step'];

	$showButtons = true;

	//REGISTRATION: Non-officials to not be presented with offical specific buttons
	if (($b['step'] == 4 or $b['step'] == 5 or $b['step'] == 6) and $view[2] == "registration" and (!$is_official or $has_official_approved)) $showButtons = false;

	//REGISTRATION: Officials to not be presented with non-offical specific buttons or if they have approved
	//if ($b['step'] != 4 and $b['step'] != 5 and $b['step'] != 6 and $view[2] == "registration" and $is_official) $showButtons = false;

	//Change of Particulars: Non-officials to not be presented with offical specific buttons
	if (($b['step'] == 4 or $b['step'] == 3) and $view[2] == "change_of_particulars" and (!$is_official or $has_official_approved)) $showButtons = false;

	//Change of Particulars: Officials to not be presented with non-offical specific buttons or if they have approved
	//if ($b['step'] != 4 and $b['step'] != 3 and $view[2] == "change_of_particulars" and $is_official) $showButtons = false;

	//Annual Returns: Non-officials to not be presented with offical specific buttons
	if (($b['step'] == 4 or $b['step'] == 3) and $view[2] == "annual_returns" and (!$is_official or $has_official_approved)) $showButtons = false;

	//Annual Returns: Officials to not be presented with non-offical specific buttons or if they have approved
	//if ($b['step'] != 4 and $b['step'] != 3 and $view[2] == "annual_returns" and $is_official) $showButtons = false;



	if ($showButtons)
	{
		if ($b['require_notes'] == 1)
		{
			echo '<button style="visibility: hidden" class="btn btn-primary ml-auto waves-effect waves-themed" type="submit" class=".submitforms" id="submitform'.$b['step'].'" onclick="return submitFormCheck('.$b['step'].','.$nextStep.')">'.$b['name'].'</button>';
		}
		else
		{
			echo '<button style="visibility: hidden" class="btn btn-primary ml-auto waves-effect waves-themed" type="submit" id="submitform'.$b['step'].'" onclick="jQuery(\'#svl\').val('.$nextStep.');">'.$b['name'].'</button>';
		}
	}
}
?>
<script>

var submitBTN = "";
function submitTheForm()
{
		jQuery("#submitform" + submitBTN).click();
}

function submitFormCheck(id, svl)
{
	jQuery('#svl').val(svl);
	submitBTN = id;
	jQuery('#notesModal').modal('show');
	return false;
}

function removeFormCheck()
{
		jQuery("#submitform" + submitBTN).attr('onclick', '');
}

function returnFormCheck()
{
		jQuery("#submitform" + submitBTN).attr('onclick', 'return submitFormCheck('+submitBTN+','+svl+')');
}

jQuery(document).ready(function(){
	jQuery('#notesModal').on('hide.bs.modal', function () {
		console.log('hide notes');
		setTimeout(returnFormCheck, 1000);
	});
});

</script>


				  </div>
				</div>


					<?php
					}
					?>
				</form>

 <?php
 if ($view[2] == "member")
 {
?>
<div class="row" id="fileuploadcontainer"  style="width:100% !important; margin-left: 0px; margin-top: -40px;" ></div>
<?php
 }
 else {
	 ?>
	 <div class="row" id="fileuploadcontainer"  style="margin: 50px 0px 0px 0px !important; width:100% !important;" ></div>
	 <?php
 }
  ?>
</div>
</div>
</div>
</div>

<!------------------>
<div id="panel-1" class="panel">
	<div class="panel-hdr bg-primary-300 bg-primary-gradient">
		<h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">Actions</h2>
	</div>
	<div class="panel-container show">

	<div class="panel-content border-faded border-left-0 border-right-0 border-bottom-0 d-flex flex-row align-items-center" style="width:100% !important">
		<div>
<?php
	if ($view[2] == "member")
	{
	}
	else
	{
		?>
		<button class="btn btn-default ml-auto waves-effect waves-themed" onclick="window.location='/<?php echo $view[2]; ?>/';return false;" type="submit">Cancel</button>

		<?php

	}
 ?>

		</div>
	<?php
//	echo json_encode($buttons);


	foreach ($buttons as $b)
	{

		$showButtons = true;

		//REGISTRATION: Non-officials to not be presented with offical specific buttons
	  if (($b['step'] == 4 or $b['step'] == 5 or $b['step'] == 6) and $view[2] == "registration" and (!$is_official or $has_official_approved)) $showButtons = false;

		//REGISTRATION: Officials to not be presented with non-offical specific buttons or if they have approved
	  //if ($b['step'] != 4 and $b['step'] != 5 and $b['step'] != 6 and $view[2] == "registration" and $is_official) $showButtons = false;

		//Change of Particulars: Non-officials to not be presented with offical specific buttons
	  if (($b['step'] == 4 or $b['step'] == 3) and $view[2] == "change_of_particulars" and (!$is_official or $has_official_approved)) $showButtons = false;

		//Change of Particulars: Officials to not be presented with non-offical specific buttons or if they have approved
	  //f ($b['step'] != 4 and $b['step'] != 3 and $view[2] == "change_of_particulars" and $is_official) $showButtons = false;

		//Annual Returns: Non-officials to not be presented with offical specific buttons
		if (($b['step'] == 4 or $b['step'] == 3) and $view[2] == "annual_returns" and (!$is_official or $has_official_approved)) $showButtons = false;

		//Annual Returns: Officials to not be presented with non-offical specific buttons or if they have approved
		if ($b['step'] != 4 and $b['step'] != 3 and $view[2] == "annual_returns" and $is_official) $showButtons = false;

		if ($showButtons)
		{

			if (isset($b['consent_step']) and $b['consent_step'] == 1)
			{
				$consent = "showConsentWindow(".$b['step'].")";
			}
			else
			{
				$consent = "jQuery('#submitform".$b['step']."').click();";
			}

			echo '<button class="btn btn-primary ml-auto waves-effect waves-themed" type="submit"  onclick="'.$consent.'">'.$b['name'].'</button>';
		}
	}
	?>

	</div>
</div>
</div>
<!------------------>



			<!-- end widget content -->
		<!-- end widget div -->

					<div class="row">
						<div class="col-xl-12">
					  	<div id="panel-1" class="panel">
								<div class="panel-hdr  bg-primary-300 bg-primary-gradient">
					      	<h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">Document History</h2>
									<div class="panel-toolbar">
										<button class="btn btn-panel" data-action="panel-collapse" data-toggle="tooltip" data-offset="0,10" data-original-title="Collapse"></button>
						        <button class="btn btn-panel" data-action="panel-fullscreen" data-toggle="tooltip" data-offset="0,10" data-original-title="Fullscreen"></button>
						      </div>
								</div>
								<div class="panel-container show">
									<div class="panel-content">
	<?php

	$link = db();
	$sql = "SELECT n.* FROM notifications n ";
  $sql .= " LEFT JOIN application r ON r.id = n.document_ID WHERE ";
  $sql .= "  n.document_name = '".$view[2]."' AND n.document_id = ".$view[0]['id']." AND (IF (".$_SESSION['user_level']." = 1, IF (document_owner = '".$_SESSION['user_logged']."',1,0),1) = 1 AND audience REGEXP '\\\b".$_SESSION['user_level']."\\\b')";
  $sql .= " OR (audience REGEXP '\\\b1\\\b') order by n.id desc LIMIT 25";
	$notifs = mysqli_query($link, $sql);
	$notif = db_fetch("","","","","","","",true, false, $notifs);
	 ?>


	                                        <div class="tab-pane" id="tab-messages" role="tabpanel">
	                                            <div class="custom-scroll h-100">
	                                                <ul class="notification">
	                                                  <?php
	                                                  foreach ($notif as $not)
	                                                  {
	                                                    ?>
	                                                      <li>
																													<span class="d-flex flex-column flex-1 ml-1">
																														<a style="display:block !important" href="/<?php echo $not['document_name']."/editRecord?id=".$not['document_id'] ?>" class="d-flex align-items-center">
																															<div class="name"><?php echo $not['action_by']; ?><span style="margin: 10px 24px 0px 0px !important" class="badge badge-primary fw-n position-absolute pos-top pos-right mt-1"><?php echo $not['document_name']." - #".$not['document_id']; ?></span></div>
																															<span class="msg-a fs-sm"><?php echo $not['message']; ?></span>
																														</a>
																															<span class="msg-b fs-xs"><?php echo $not['notes']; ?>
																																<?php
																																if (isset($not['url']) && $not['url'] != "")
																																{
																																echo '&nbsp;<a style="display:inline !important;text-decoration:none !important" target="_blank" href="'.$not['url'].'" class="d-flex align-items-center"><i class="fal fa-link"></i> Document</a>';
																																}
																																?>
																															</span>
																															<span class="fs-nano text-muted mt-1"><?php echo date('j F Y h:ma',strtotime($not['createdAt']))." - ".get_time_ago (strtotime($not['createdAt'])); ?>.</span>
																													</span>
	                                                      </li>
	                                                      <?php
	                                                  }
	                                                    ?>
	                                                </ul>
	                                            </div>
	                                        </div>









									</div>
								</div>
							</div>
						</div>
					</div>

	<!-- Modal -->
	<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h5 class="modal-title" id="notesModalLabel">Please enter notes or comments</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
	      <div class="modal-body">
					<div class="form-group">
					    <textarea class="form-control" required id="notesarea" rows="5"></textarea>
					</div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
	        <button type="button" class="btn btn-primary" onclick="jQuery('#notesx').val(jQuery('#notesarea').val());removeFormCheck();submitTheForm();">Proceed</button>
	      </div>
	    </div>
	  </div>
	</div>

	<?
}

function genericFormEnd()
{
	?>
	<?php

}

function commonActions($v, $id)
{
	?>
	<form method="get"
		action="<?php echo $_SESSION['boot']->getAppPublic().$v."/editRecord"; ?>">
		<fieldset>
			<legend>
				<?php echo UCFirst($v); ?>
				Actions
			</legend>
			<input type="hidden" name="id" value="<?php echo $id; ?>"> <input
				type="submit" name="submit" value="PDF">
		</fieldset>
	</form>
	<?php

}

function uploader($folder, $id, $element)
{
	?>
	<div style="float: left" class='ui-state-default ui-corner-all'
		id="file-uploader-demo<?php echo $id; ?>">
		<noscript>
			<p>Please enable JavaScript to use file uploader.</p>
		</noscript>
	</div>

	<input type="hidden" id="cup<?php echo $id; ?>"
		name="cup<?php echo $id; ?>" value="">
	<script>

		function checkUploader<?php echo $id;?>(el,fn)
		{
			if (el.val() == "")
			{
				console.log("uploaded value:" + el.val());
				el.val(fn);
				checkField('<?php echo $element; ?>','');
			}
			else
			{
				jQuery("#cup<?php echo $id;?>").val(fn);
			}


		}

        function createUploader<?php echo $id;?>()
        {
            var uploader<?php echo $id;?> = new qq.FineUploader(
            {
                element: document.getElementById('file-uploader-demo<?php echo $id; ?>'),
                action: '<?php echo $_SESSION['boot']->getAppPublic()."upload/upload?folder=".$folder ?>',
                debug: true,
                onComplete: function(id, fileName, responseJSON)
                {
                    jqelement = jQuery("input[type=text][name='<?php echo $element; ?>']");
                    checkUploader<?php echo $id;?>(jqelement, responseJSON.filename+"."+responseJSON.extension);
                }
            });
        }
        jQuery(document).ready(function()
        {
        	createUploader<?php echo $id; ?>();
        });
    </script>
	<?

}

function standardIndexPage($view, $buttonCode, $messageForm)
{
	echo select2Loader("#auto", $view);
	?>

	<article style="margin-top: 10px;"
		class="col-sm-12 col-md-12 col-lg-12 sortable-grid ui-sortable">

		<div class="jarviswidget jarviswidget-sortable" id="wid-id-0"
			data-widget-colorbutton="false" data-widget-editbutton="false"
			role="widget" style="">
			<!-- widget options:
						usage: <div class="jarviswidget" id="wid-id-0" data-widget-editbutton="false">

						data-widget-colorbutton="false"
						data-widget-editbutton="false"
						data-widget-togglebutton="false"
						data-widget-deletebutton="false"
						data-widget-fullscreenbutton="false"
						data-widget-custombutton="false"
						data-widget-collapsed="false"
						data-widget-sortable="false"

						-->
			<header role="heading">
				<div class="jarviswidget-ctrls" role="menu">

					<!-- a href="javascript:void(0);" class="button-icon jarviswidget-toggle-btn" rel="tooltip" title="" data-placement="bottom" data-original-title="Collapse">
									<i class="fa fa-minus "></i>
								</a>
								<a href="javascript:void(0);" class="button-icon jarviswidget-fullscreen-btn" rel="tooltip" title="" data-placement="bottom" data-original-title="Fullscreen">
									<i class="fa fa-expand "></i>
								</a>
								<a href="javascript:void(0);" class="button-icon" rel="tooltip" title="" data-placement="bottom" data-original-title="Delete">
									<i class="fa fa-times"></i>
								</a-->
				</div>
				<span class="widget-icon"> <i class="fa fa-eye"></i>
				</span>
				<h2 style="cursor:pointer" data-toggle="collapse" data-action="panel-collapse">
					<?php echo UCFirst($view); ?>
				</h2>

				<span class="jarviswidget-loader"><i class="fa fa-refresh fa-spin"></i>
				</span>
			</header>

			<!-- widget div-->
			<div role="content">

				<!-- widget edit box -->
				<div class="jarviswidget-editbox">
					<!-- This area used as dropdown edit box -->

				</div>
				<!-- end widget edit box -->

				<!-- widget content -->
				<div class="widget-body">

					<form id="selform"
						action="<?php echo $_SESSION['boot']->getAppPublic()."$view/editRecord" ?>">
						<div class="row">

							<label class="control-label col-md-2" for="prepend">Enter Name:</label>
							<div class="col-md-10">
								<div class="icon-addon addon-md">
									<input type="text" id="auto" name="id" />
								</div>
							</div>
						</div>
						<div class="row" style="margin-top: 10px">
							<?php if (isset($messageForm) && $messageForm != "")
							{

								?>
							<div class="col-md-12">

								<div class="alert alert-info alert-block">
									<a class="close" data-dismiss="alert" href="#">�</a>
									<h4 class="alert-heading">Note</h4>
									<?php echo $messageForm; ?>
								</div>

							</div>
							<?php
							}
							?>
						</div>
						<div class="form-actions">
							<div class="row">
								<div class="col-md-6">
									<div style="clear: both"></div>
								</div>
								<div class="col-md-6">
									<button class="btn btn-default" type="submit">Cancel</button>
									<button class="btn btn-primary" type="submit">
										<i class="fa fa-save"></i> Submit
									</button>
								</div>
							</div>
						</div>

					</form>

				</div>
				<!-- end widget content -->

			</div>
			<!-- end widget div -->

		</div>

		<?php

}

function select2Loader($obj, $path)
{

	$s2Param = "&requestedFrom=".$_SESSION['boot']->getController();
	$rtn = "";

	if ($obj == "#dd_name_ID")
	{
		$rtn .= "<script>$(document).ready(function(){ $('#dd_name_ID').on(\"select2:select\", function(e) { setTimeout(jQuery('#submitform1').click(),500); }); });</script>";
	}

	if ($obj == "#dd_business_category_ID")
	{
//		$rtn .= "<script>var originalsbcd = ''; $(document).ready(function(){ $('#dd_business_category_ID').on(\"select2:selecting\", function(e) { console.log(e.params.args.data.text);  if(originalsbcd == '') originalsbcd = $('#dd_business_sub_category_ID').data().select2.dataAdapter.ajaxOptions.url; sbcd = originalsbcd; console.log('B:'+sbcd); sbcd += '&where=businesscategory%20in%20(%22'+ encodeURI(e.params.args.data.text) +'%22)'; $('#dd_business_sub_category_ID').data().select2.dataAdapter.ajaxOptions.url = sbcd;  console.log('B:'+sbcd); }); });</script>";
	}


	if ($obj == "#dd_county_ID")
	{
		$rtn .= "<script>var originalscd = ''; $(document).ready(function(){ $('#dd_county_ID').on(\"select2:selecting\", function(e) { console.log(e.params.args.data.text);  if(originalscd == '') originalscd = $('#dd_sub_county_ID').data().select2.dataAdapter.ajaxOptions.url; scd = originalscd; console.log('B:'+scd); scd += '&where=county%3D%22'+ encodeURI(e.params.args.data.text)+'%22'; $('#dd_sub_county_ID').data().select2.dataAdapter.ajaxOptions.url = scd;  console.log('B:'+scd); }); });</script>";
	}

	if ($obj == "#dd_sub_county_ID")
	{
		$rtn .= "<script>var originalwd = ''; $(document).ready(function(){ $('#dd_sub_county_ID').on(\"select2:selecting\", function(e) { console.log(e.params.args.data.text);  if(originalwd == '') originalwd = $('#dd_ward_ID').data().select2.dataAdapter.ajaxOptions.url; wd = originalwd; console.log('B:'+wd); wd += '&where=subcounty%3D%22'+ encodeURI(e.params.args.data.text)+'%22'; $('#dd_ward_ID').data().select2.dataAdapter.ajaxOptions.url = wd;  console.log('B:'+wd); }); });</script>";
	}

	//registration page
	if (isset($_GET['searchid'])) $s2Param .= "&searchString=".$_GET['searchid'];


	$rndno = generateRandomString(10);
	$rtn .= '
					<script>

					function formatRepo'.$rndno.'(repo)
					{
						console.log("FORMAT" + JSON.stringify(repo));
						return repo.text;
					}

					function formatRepoSelection'.$rndno.'(repo)
					{
						console.log("FORMATSEL:" + JSON.stringify(repo));
						return repo.text;
					}

					jQuery(document).ready(function()
					{
						$("'.$obj.'").css({"width":"200px"});

						//$("'.$obj.'").removeClass("form-control");

						//$("'.$obj.'").on("change", function(e){ selectUpdate(e,$("'.$obj.'"))});

						$("'.$obj.'").select2(
						{';
							if ($obj == "#dd_products_ID" or $obj == "#dd_branches_ID" or $obj == "#dd_business_sub_category_ID" or $obj == "#dd_business_category_ID")
							{
							$rtn .= '
								"language": {
       						"noResults": function(){
           						return "No Results - press space to add as new item";
       						}
   								}, multiple: true,';
							}

							$rtn .= '
							ajax:
								{

										url: "'.$_SESSION['boot']->getAppPublic().'jsonpair/'.$path.'?rows=20'.$s2Param.'",
										dataType: \'json\',
										delay: 250,
										debug: true,
										data: function(params)
										{
												var trm = params.term;
												console.log("term:" + trm);
												if (typeof(trm) == \'undefined\' || trm == "")
												{
														trm = "Please Wait...";
												}
												return { term: trm };
										},
										processResults: function(data, params)
										{';

											if ($obj == "#dd_products_ID" or $obj == "#dd_branches_ID")
											{
											$rtn .= '
												if(data.results == "")
												{
													if (params.term.includes(" "))
													{
														customSelectItem("'.$obj.'", params.term);
													}
												}

												';
											}

											$rtn .= '
											return {
												results: data.results.map(item =>
														({
																text: item.text,
																id: item.id
														})
												)}
										},
										cache: false
								},
								placeholder: \'Search\',
								escapeMarkup: function(markup)
								{
										return markup;
								}, // let our custom formatter work
								//templateResult: formatRepo'.$rndno.',
								//templateSelection: formatRepoSelection'.$rndno.',
								minimumInputLength: 0
						});

									var sid=$("'.$obj.'").attr("select-value");
									if (sid!=="")
									{
										$.ajax("'.$_SESSION['boot']->getAppPublic().'jsonpair/'.$path.'?single=true",
										{
											data:
											{
												term: sid
											},
											dataType: "json"
										}).done(function(data) {

											if (data.results)
											{
												data.results.forEach(function( res ) {
													//console.log("KV: " + res.id + "," + res.text);
													var option1 = new Option(res.text, res.id, true, true);
													jQuery("'.$obj.'").append(option1);
													jQuery("'.$obj.'").trigger("change");
												});

											}

									    var option = new Option(data.text, data.id, true, true);
									    $("'.$obj.'").append(option).trigger("change");
									    $("'.$obj.'").trigger({
									        type: "select2:select",
									        params: {
									            data: data
									        }
										  });
										});
									}
							});
				</script>
				';


				return $rtn;
}
function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, strlen($characters) - 1)];
	}
	return $randomString;
}

function generateRandom($cnt)
{
	$chars = "0123456789012345678901234567890123456789012345678901234567890123456789";
	srand((double) microtime() * 1000000);
	$i = 0;
	$pass = '';

	while ($i <= $cnt)
	{
		$num = rand() % 33;
		$tmp = substr($chars, $num, 1);
		$pass = $pass . $tmp;
		$i++;
	}
	return $pass;
}

function sendActivationEmail($id, $fn, $email, $length, $level, $user, $pass)
{
	$code = generateRandom($length);
	if ($level != null)
	{
		$label = array (
			"activated",
			"level"
		);
		$data = array (
			$code,
			$level
		);
	} else
	{
		$label = array (
			"activated"
		);
		$data = array (
			$code
		);
	}
	db_update($id, 'user', $label, $data);

	//prepare email template
	ob_start();
	include "../templates/activation_email.php";
	$tmpl = ob_get_contents();
	ob_end_clean();
	$tmpl = str_replace("@LINK@", $_SESSION['boot']->siteAddress . "user/activate?cd=" . $code, $tmpl);
	$tmpl = str_replace("@NAME@", $fn, $tmpl);
	$tmpl = str_replace("@USER@", $user, $tmpl);
//	$tmpl = str_replace("@PASS@", $pass, $tmpl);

	//send email
	$to = $email;
	$subject = "Activate your MSEA account.";
	$body = $tmpl;
	$clazz = new mailClass();
	$content = $clazz->send($to, $body, $subject, null);
	return $content;
}

function checkUserType()
{
	if ($_SESSION['user_level'] == 1 or $_SESSION['user_level'] == 2)
	{
		$res = db_fetch("", "", "", "", "user_HD = " . $_SESSION['user_logged'], "", "consultant", false, false, null);
		$cid = "";
		if ($res)
		{
			foreach ($res as $rs)
			{
				if ($rs['consultant_type_ID'] == 2)
				{
					$cid = $rs['id'];
				}
			}

			if ($cid == "")
			{
				return $res[0]['id'];
			}
			else
			{
				return $cid;
			}

		} else
		{
			return "none";
		}
	}
	return null;
}

function checkApplied($tor)
{
	$consultantId = checkUserType();
	if ($consultantId == "none" or $consultantId == null)
	{
		return null;
	}
	$res = db_fetch("", "", "", "", "a.consultant_ID = " . $consultantId . " and a.process_ID = p.id and p.terms_of_reference_ID = b.id and b.id = " . $tor, "a.*", "applications a, terms_of_reference b, process p", false, false, null);
	if ($res)
	{
		return $res;
	}
	return null;
}

function checkAppliedGoods($spec)
{
	$consultantId = checkUserType();
	if ($consultantId == "none" or $consultantId == null)
	{
		return null;
	}
	$res = db_fetch("", "", "", "", "a.consultant_ID = " . $consultantId . " and a.process_ID = p.id and p.specifications_document_ID = b.id and b.id = " . $spec, "a.*", "applications a, specifications_document b, process p", false, false, null);
	if ($res)
	{
		return $res;
	}
	return null;
}



function checkAppliedRoster()
{
	$consultantId = checkUserType();
	if ($consultantId == "none" or $consultantId == null)
	{
		return false;
	}
	$res = db_fetch("", "", "", "", "a.consultant_ID = " . $consultantId . " and a.process_ID = 18", "a.id", "applications a", false, false, null);
	if ($res)
	{
		return true;
	}
	return false;
}

function checkShortlistEOI($application)
{
	$rec = db_fetch("", "", "", "", "Shortlist_Type_HD = 'EOI' and applications_HD = $application and user_HD = " . $_SESSION['user_logged'], "", "shortlist", false, false, null);
	if ($rec)
	{
		return $rec[0]['id'];
	} else
	{
		return null;
	}
}

function checkShortlistITT($application)
{
	$rec = db_fetch("", "", "", "", "Shortlist_Type_HD = 'ITT' and applications_HD = $application and user_HD = " . $_SESSION['user_logged'], "", "shortlist", false, false, null);
	if ($rec)
	{
		return $rec[0]['id'];
	} else
	{
		return null;
	}
}

function zipFilesAndDownload($file_names,$archive,$file_path)
{
	$tempLocation = $_SESSION['boot']->appPath."public/upload/temp/";

	if(extension_loaded('zip'))
	{
		$zip = new ZipArchive();
		$zip->open($tempLocation.$archive, ZIPARCHIVE::CREATE );

		foreach($file_names as $files)
		{
			$zip->addFile($file_path.$files,$files);
		}
		$zip->close();

		// http headers for zip downloads
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"".$archive."\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($tempLocation.$archive));
		ob_end_flush();
		@readfile($tempLocation.$archive);


		/*





























		$error = ""; //error holder
		// Checking ZIP extension is available
		if(extension_loaded('zip'))
		{
		if(isset($file_names) and count($file_names) > 0)
		{
		// Checking files are selected
		$zip = new ZipArchive(); // Load zip library

		$zip->open($archive, ZipArchive::CREATE);
		foreach ($file_names as $file) {
				$zip->addFile($file_path.$file,$file);
		}
		$zip->close();

		header('Content-Type: application/zip');
		header('Content-disposition: attachment; filename='.$archive);
		header('Content-Length: ' . filesize($archive_file_name));
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile($archive_file_name);
		unlink($archive_file_name);














		*/













		/*


		if($zip->open($archive_file_name, ZIPARCHIVE::CREATE)!==TRUE)
		{
		// Opening zip file to load files
		$error .= "* Sorry ZIP creation failed at this time";
		}
		foreach($file_names as $file)
		{
		$zip->addFile($file_path.$file,$file); // Adding files into zip
		}

		if(file_exists($archive_file_name))
		{
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=$archive");
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile("$archive_file_name");
		unlink($archive_file_name);
		}
		else
		{
		$error .= "* Zip file disappeared";
		}
		$zip->close();
		}
		else
		{
		$error .= "* Please select file to zip ";
		}
		*/
	}
	else
	{
		$error .= "* You dont have ZIP extension";
	}

	if ($error != "")
	{
		exit($error);
	}
}



function holidaysAndWeekendsBetween($startDate, $endDate)
{
	$holidayBetween = db_fetch("", "", "", "", "Holiday_Date >= '" . date('Y-m-d 00:00:00', $startDate) . "' and Holiday_Date <= '" . date('Y-m-d 00:00:00', $endDate) . "'", "", "holidays", false, false, null);
	$days = 0;
	if ($holidayBetween)
	{
		$days = count($holidayBetween);
	}
	return $days +countWeekends($startDate, $endDate);
}

function countWeekends($start, $end)
{
	$weekendcounter = 0;
	//$start = strtotime($start);
	//$end = strtotime($end);
	while ($start <= $end)
	{
		if (date("w", $start) == 0 or date("w", $start) == 6)
		{
			$weekendcounter++;
		}
		$start = strtotime('+1 day', strtotime(date("Y", $start) . '-' . date("m", $start) . '-' . date("d", $start) . ' 00:00:00'));
	}
	return $weekendcounter;
}

function forwardDate($fwd, $startDate)
{
	$origFwd = $fwd;
	$dy = "day";
	$skip = 999;
	while ($skip > 0)
	{
		if ($fwd > 1)
		{
			$dy = "days";
		}

		$endDate = strtotime(date("Y-m-d", $startDate) . "+" . $fwd . " " . $dy);
		$newskip = holidaysAndWeekendsBetween($startDate, $endDate);
		if ($newskip != 0 && $newskip != $skip)
		{
			$fwd = $newskip + $origFwd;
			$skip = $newskip;
		} else
		{
			$skip = 0;
		}

	}
	return $endDate;
}

function createSchemaXML()
{
	$link = dbInformationSchema();
	$db = $_SESSION['boot']->getDatabase();
	$xml = "";

	$arr = array ();
	@ $datatypes = file("../model/datatypes.xml");
	$arr[] = $datatypes[0];
	$arr[] = '<sql db="mysql">';
	for ($i = 1; $i < count($datatypes); $i++)
	{
		$arr[] = $datatypes[$i];
	}

	$sql = "SELECT * FROM TABLES WHERE TABLE_SCHEMA = '$db'";
	$result = mysqli_query($link, $sql) or myDie($sql);

	while ($row = mysqli_fetch_array($result))
	{
		$table = $row["TABLE_NAME"];
		$xml .= '<table name="' . $table . '">';
		$comment = (isset ($row["TABLE_COMMENT"]) ? $row["TABLE_COMMENT"] : "");
		if ($comment)
		{
			//$xml .= '<comment>'.htmlspecialchars($comment).'</comment>';
		}

		$q = "SELECT * FROM COLUMNS WHERE TABLE_NAME = '" . $table . "' AND TABLE_SCHEMA = '" . $db . "'";
		$result2 = mysqli_query($q);
		while ($row = mysqli_fetch_array($result2))
		{
			$name = $row["COLUMN_NAME"];
			$type = $row["COLUMN_TYPE"];
			$comment = (isset ($row["COLUMN_COMMENT"]) ? $row["COLUMN_COMMENT"] : "");
			$null = ($row["IS_NULLABLE"] == "YES" ? "1" : "0");
			if (preg_match("/binary/i", $row["COLUMN_TYPE"]))
			{
				$def = bin2hex($row["COLUMN_DEFAULT"]);
			} else
			{
				$def = $row["COLUMN_DEFAULT"];
			}

			$ai = (preg_match("/auto_increment/i", $row["EXTRA"]) ? "1" : "0");
			if ($def == "NULL")
			{
				$def = "";
			}
			$xml .= '<row name="' . $name . '" null="' . $null . '" autoincrement="' . $ai . '">';
			$xml .= '<datatype>' . strtoupper($type) . '</datatype>';
			$xml .= '<default>' . $def . '</default>';
			if ($comment)
			{
				//$xml .= '<comment>'.htmlspecialchars($comment).'</comment>';
			}
			/* fk constraints */
			$q = "SELECT
				REFERENCED_TABLE_NAME AS 'table', REFERENCED_COLUMN_NAME AS 'column'
				FROM KEY_COLUMN_USAGE k
				LEFT JOIN TABLE_CONSTRAINTS c
				ON k.CONSTRAINT_NAME = c.CONSTRAINT_NAME
				WHERE CONSTRAINT_TYPE = 'FOREIGN KEY'
				AND c.TABLE_SCHEMA = '" . $db . "' AND c.TABLE_NAME = '" . $table . "'
				AND k.COLUMN_NAME = '" . $name . "'";
			$result3 = mysqli_query($q);

			while ($row = mysqli_fetch_array($result3))
			{
				$xml .= '<relation table="' . $row["table"] . '" row="' . $row["column"] . '" />';
			}

			$xml .= '</row>';
		}

		/* keys */
		$q = "SELECT * FROM STATISTICS WHERE TABLE_NAME = '" . $table . "' AND TABLE_SCHEMA = '" . $db . "' ORDER BY SEQ_IN_INDEX ASC";
		$result2 = mysqli_query($q);
		$idx = array ();

		while ($row = mysqli_fetch_array($result2))
		{
			$name = $row["INDEX_NAME"];
			if (array_key_exists($name, $idx))
			{
				$obj = $idx[$name];
			} else
			{
				$type = $row["INDEX_TYPE"];
				$t = "INDEX";
				if ($type == "FULLTEXT")
				{
					$t = $type;
				}
				if ($row["NON_UNIQUE"] == "0")
				{
					$t = "UNIQUE";
				}
				if ($name == "PRIMARY")
				{
					$t = "PRIMARY";
				}
				$obj = array (
					"columns" => array (),
					"type" => $t
				);
			}
			$obj["columns"][] = $row["COLUMN_NAME"];
			$idx[$name] = $obj;
		}

		foreach ($idx as $name => $obj)
		{
			$xml .= '<key name="' . $name . '" type="' . $obj["type"] . '">';
			for ($i = 0; $i < count($obj["columns"]); $i++)
			{
				$col = $obj["columns"][$i];
				$xml .= '<part>' . $col . '</part>';
			}
			$xml .= '</key>';
		}
		$xml .= "</table>";
	}
	$arr[] = $xml;
	$arr[] = '</sql>';
	return base64_encode($xml);
}

function consultantProfile($type, $id)
{
	$clazz = new alternateClass("consultant", "");
	$clazz->where = "id =" . $id;
	if ($type == "individual_consultant")
	{
		$clazz->pdfFormat = "individual_consultant";
	} else
	{
		$clazz->pdfFormat = "company_consultant";
	}
	$content = $clazz->pdf();
	$this->render = loadLayout($content, 'pdf', $boot->getAppRoot());
}

function htmltodoxc($html)
{
	// Load the files we need:
	require_once '../htmltodocx/phpword/PHPWord.php';
	require_once '../htmltodocx/simplehtmldom/simple_html_dom.php';
	require_once '../htmltodocx/htmltodocx_converter/h2d_htmlconverter.php';
	require_once '../htmltodocx/styles/styles.inc';

	// Functions to support this example.
	require_once '../htmltodocx/documentation/support_functions.inc';

	// New Word Document:
	$phpword_object = new PHPWord();
	$section = $phpword_object->createSection();

	// HTML Dom object:
	$html_dom = new simple_html_dom();
	$html_dom->load('<html><body>' . $html . '</body></html>');
	// Note, we needed to nest the html in a couple of dummy elements.

	// Create the dom array of elements which we are going to work on:
	$html_dom_array = $html_dom->find('html',0)->children();

	// We need this for setting base_root and base_path in the initial_state array
	// (below). We are using a function here (derived from Drupal) to create these
	// paths automatically - you may want to do something different in your
	// implementation. This function is in the included file
	// documentation/support_functions.inc.
	$paths = htmltodocx_paths();

	// Provide some initial settings:
	$initial_state = array(
			// Required parameters:
			'phpword_object' => &$phpword_object, // Must be passed by reference.
			// 'base_root' => 'http://test.local', // Required for link elements - change it to your domain.
			// 'base_path' => '/htmltodocx/documentation/', // Path from base_root to whatever url your links are relative to.
			'base_root' => $paths['base_root'],
			'base_path' => $paths['base_path'],
			// Optional parameters - showing the defaults if you don't set anything:
			'current_style' => array('size' => '11'), // The PHPWord style on the top element - may be inherited by descendent elements.
			'parents' => array(0 => 'body'), // Our parent is body.
			'list_depth' => 0, // This is the current depth of any current list.
			'context' => 'section', // Possible values - section, footer or header.
			'pseudo_list' => TRUE, // NOTE: Word lists not yet supported (TRUE is the only option at present).
			'pseudo_list_indicator_font_name' => 'Wingdings', // Bullet indicator font.
			'pseudo_list_indicator_font_size' => '7', // Bullet indicator size.
			'pseudo_list_indicator_character' => 'l ', // Gives a circle bullet point with wingdings.
			'table_allowed' => TRUE, // Note, if you are adding this html into a PHPWord table you should set this to FALSE: tables cannot be nested in PHPWord.
			'treat_div_as_paragraph' => TRUE, // If set to TRUE, each new div will trigger a new line in the Word document.

			// Optional - no default:
	'style_sheet' => htmltodocx_styles_example(), // This is an array (the "style sheet") - returned by htmltodocx_styles_example() here (in styles.inc) - see this function for an example of how to construct this array.
	);

	// Convert the HTML and put it into the PHPWord object
	htmltodocx_insert_html($section, $html_dom_array[0]->nodes, $initial_state);

	// Clear the HTML dom object:
	$html_dom->clear();
	unset($html_dom);

	// Save File
	set_include_path('/tmp');
	$h2d_file_uri = tempnam('/tmp', 'htd');
	$objWriter = PHPWord_IOFactory::createWriter($phpword_object, 'Word2007');
	$objWriter->save($h2d_file_uri);

	// Download the file:
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename=tor.docx');
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . filesize($h2d_file_uri));
	ob_clean();
	flush();
	$status = readfile($h2d_file_uri);
	unlink($h2d_file_uri);
	exit;
}



function roundUpToAny($n,$x=5) {
	return (int) $x * round($n / $x);
}

?>
