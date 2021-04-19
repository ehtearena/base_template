<?php
function db()
{
	$bt = $_SESSION['boot'];
	$link = mysqli_connect($bt->getHost(), $bt->getUser(), $bt->getPassword(), $bt->getDatabase()) or myDie(__FILE__.":".__LINE__.":".__CLASS__.":".__METHOD__."Could not connect to DB");
	return $link;
}

function dbInformationSchema()
{
	$bt = $_SESSION['boot'];
	$link = mysqli_connect($bt->getHost(), $bt->getUser(), $bt->getPassword(), "information_schema") or myDie(__FILE__.":".__LINE__.":".__CLASS__.":".__METHOD__."Could not connect to Information Schema DB");
	return $link;
}


function db_fetch_result($page, $limit, $sidx, $sord, $where, $select, $table, $includeDeleted)
{
	$link = db();
	if ($select == "")
	{
		$sql = "SELECT * ";
	}
	else
	{
		$sql = "SELECT " . $select;
	}

	$delClause = "deletedAt IS NULL";
	$rightsSelect = "c_rights";
	$dClause = "";
	$rightsSel = "";

	//ensure deletedAt is NULL is applied to all tables in a join.
	$ct = 0;
	if ($select != "")
	{
		$selArr = preg_split("/[\s,]+/", $table);
		foreach ($selArr as $sArr)
		{
			if (strlen($sArr) == 1 or strlen($sArr) == 2)
			{
				if ($sArr != "=" && $sArr != "ON")
				{
					$theClause = $sArr.".".$delClause;
					$rightsSel .= ", ".$sArr.".".$rightsSelect;
				}
				if (strpos($dClause,$theClause) == FALSE)
				{
					if ($ct == 0)
					{
						$dClause = $theClause;
					} else
					{
						$dClause .= " AND " . $theClause;
					}
					$ct++;
				}
			}
		}
		if ($ct > 0)
		{
			$delClause = $dClause;
			$sql .= $rightsSel;
		}
		else
		{
				if ($sArr != "=" && $sArr != "ON")
				{
					$sql .= ", ".$sArr.".".$rightsSelect;
				}
		}
	}

	//End stuff for Reports Generator

	$sql .= " FROM " . $table;

	$orderBy = "";
	$groupBy = "";

	if ($includeDeleted == false)
	{
		if ($where != "")
		{

			if (strpos($where,"GROUP BY"))
			{
				$wh = preg_split("/GROUP BY/",$where);
				$where = $wh[0];
				$groupBy = $wh[1];
			}

			if (strpos($where,"ORDER BY"))
			{
				$wh = preg_split("/ORDER BY/",$where);
				$where = $wh[0];
				$orderBy = $wh[1];
			}

			$sql .= " WHERE " . $where . " AND " . $delClause;
		} else
		{
			$sql .= " WHERE " . $delClause;
		}
	}
	else
	{
		if ($where != "")
		{
			$sql .= " WHERE " . $where;
		}
	}

	if ($orderBy != "")
	{
		$sql .= " ORDER BY ".$orderBy;
	}

	if ($groupBy != "")
	{
		$sql .= " GROUP BY ".$groupBy;
	}


	if ($sidx != "")
	{
		if ($sord == "")
		{
			$sord = "asc";
		}
		if (strpos($sidx, ",") > 0)
		{
			$sidxsplit = preg_split("/\,/",$sidx);
			$sordsplit = preg_split("/\,/",$sord);

			$tsl = "";
			$cnts = 0;
			foreach ($sidxsplit as $spl)
			{
				if ($cnts > 0)
				{
					$tsl .= ",";
				}
				$tsl .= $spl." ".$sordsplit[$cnts];
				$cnts ++;
			}
			$sql .= " ORDER BY ".$tsl;
		}
		else
		{
			$sql .= " ORDER BY " . $sidx . " " . $sord;
		}
	}

	if ($limit != "")
	{
		if ($page != "")
		{
			$start = $limit * $page - $limit;
			if ($start < 0)
				$start = 0;
			$sql .= " LIMIT $start, $limit";
		} else
		{
			$sql .= " LIMIT $limit";
		}
	}

	logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "########################## ".$sql);

	$result = mysqli_query($link, $sql) or myDie(__FILE__.":".__LINE__.":".__CLASS__.":".__METHOD__.$sql);
	return $result;
}

/**
 * This function selects the required data from the table and hides the password by displaying [ENCRYPTED]
 */
function db_fetch($page, $limit, $sidx, $sord, $where, $select, $table, $atBy, $includeDeleted, $result)
{
	$res = array ();

	if (!$result)
	{
		$result = db_fetch_result($page, $limit, $sidx, $sord, $where, $select, $table, $includeDeleted);
	}

	if (!$result)
	{
		return array();
	}

	$numOfRows = mysqli_num_rows($result);

	$info = mysqli_fetch_assoc($result);

	if ($numOfRows > 0)
	{
		$cnt = 0;
		do
		{
			$inner = array ();
			$deleted = false;

			if (isset($_GET['single']) && $_GET['single'] == "true")
			{

			}
			else
			{
				if (isset($info["c_rights"]) and $info["c_rights"] != "" and $_SESSION['user_level'] != "")
				{
					if (strpos($info["c_rights"], $_SESSION['user_level']) === FALSE)
					{
						continue;
					}
				}
			}

			$cntx = 0;
			foreach ($info as $column => $value)
			{
				if ($column != "password")
				{
					if ((isset ($atBy) && $atBy == true) or ($column != "createdAt" and $column != "updatedAt" and $column != "updatedBy" and $column != "deletedAt" and $column != "c_rights"))
					{
						if (is_null($value))
						{
							$inner[$column] = "";

						}
						else
						{
							$inner[$column] = utf8_encode($value);
						}
					}
				}
				else
				{
					$inner[$column] = "[ENCRYPTED]";
				}
			}

			if (sizeof($inner) > 0)
			{
				$res[$cnt] = $inner;
				$cnt = $cnt +1;
			}
		} while ($info = mysqli_fetch_assoc($result));
	}

	mysqli_free_result($result);
	return $res;
}

/**
 * This function selects the required data from the table without hiding the password
 */
function db_fetch_pass($page, $limit, $sidx, $sord, $where, $select, $table, $atBy, $includeDeleted, $result)
{
	$res = array ();

	if (!$result)
	{
		$result = db_fetch_result($page, $limit, $sidx, $sord, $where, $select, $table, $includeDeleted);
	}

	if (!$result)
	{
		return array ();
	}

	$numOfRows = mysqli_num_rows($result);

	$info = mysqli_fetch_assoc($result);

	if ($numOfRows > 0)
	{
		$cnt = 0;
		do
		{
			$inner = array ();
			$deleted = false;
			foreach ($info as $column => $value)
			{
				if ((isset ($atBy) && $atBy == true) or ($column != "createdAt" and $column != "updatedAt" and $column != "updatedBy" and $column != "deletedAt"))
				{
					$inner[$column] = utf8_encode($value);
				}
			}

			if (sizeof($inner) > 0)
			{
				$res[$cnt] = $inner;
				$cnt = $cnt +1;
			}
		} while ($info = mysqli_fetch_assoc($result));
	}
	mysqli_free_result($result);
	return $res;
}


function db_fetch_columns($table, $atBy)
{
	$rightTable = array();
	$rights = db_fetch("","","","","c_table = '".$table."'","","table_rights", false, false, null);
	foreach ($rights as $right)
	{
		$rightTable[$right['c_field']] = $right['user'];
	}

	$link = db();
	$res = array();
	$result = mysqli_query($link, "SHOW COLUMNS FROM ".$table);
	if ($result)
	{
		if (mysqli_num_rows($result) > 0)
		{
			$cnt = 0;
		    while ($row = mysqli_fetch_assoc($result))
		    {
					$fieldName = $row['Field'];
					if (($table == "member_details" and $fieldName == "membership_number") or ($table == "association" and $fieldName == "national_identity_card"))
					{
						$res[$cnt] = "action";
						$cnt++;
					}
					if (($table == "change_of_owner" and $fieldName == "full_name") or($table == "change_of_association" and $fieldName == "national_identity_card") or ($table == "change_of_official" and $fieldName == "title") or ($table == "official" and $fieldName == "title") or ($table == "owner" and $fieldName == "full_name"))
					{
						$res[$cnt] = "action";
						$cnt++;
					}


					if ($atBy == true)
					{
						$res[$cnt] = $fieldName;
						$cnt++;
					}
					else
					{
						$rightsExist = true;
						if (isset($rightTable[$fieldName]))
						{
							$rightsExist = false;
							if (strpos($rightTable[$fieldName],$_SESSION['user_level']) > -1)
							{
								$rightsExist = true;
							}
						}

						if ($rightsExist == true and $fieldName != "createdAt" and $fieldName != "updatedAt" and $fieldName != "updatedBy" and $fieldName != "deletedAt" and $fieldName != "c_rights")
						{
							$res[$cnt] = $fieldName;
							$cnt++;
						}
					}
		    }
		}
		mysqli_free_result($result);
	}

	return $res;
}

function db_insert($table, $label, $data)
{
	if (isset($_SESSION['user_logged']) && $_SESSION['user_logged'] != "")
	{
		$user = $_SESSION['user_logged'];
	}
	else
	{
		$user = 1;
	}

	$link = db();


	$sqll = "";
	$sqld = "";
	$cc = 0;
	foreach ($label as $lbl)
	{
		if ($lbl == "password" && $data[$cc] == "[ENCRYPTED]")
		{
			//skip passwords that were not changed
		}
		else
		{
				$sqll .= ",".$lbl;
				if ($data[$cc] == "NULL" or $data[$cc] == 'undefined' or $data[$cc] == "???")
				{
					$sqld .= ",NULL";
				}
				else
				{
					if ($lbl == "password")
					{
						$sqld .= ",'" . MD5(mysqli_real_escape_string($link, stripslashes($data[$cc]))) . "'";
					}
					else
					{
						$sqld .= ",'" .  mysqli_real_escape_string($link, stripslashes($data[$cc])) . "'";
					}
				}
		}
		$cc = $cc + 1;
	}

	$sql = "INSERT INTO " . $table . " (createdAt, updatedBy, updatedAt";

	$sql .= $sqll;
	$sql .= ") VALUES ('".date("Y-m-d H:i:s", time())."'," . $user . ",'".date("Y-m-d H:i:s", time())."'";

	$sql .= $sqld;
	$sql .= ")";

	logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "########################## ".$sql);

	$result = mysqli_query($link, $sql) or myDie(__FILE__.":".__LINE__.":".__CLASS__.":".__METHOD__.$sql);
	return mysqli_insert_id($link);
}

function db_update($record, $table, $label, $data)
{
	if (isset($_SESSION['user_logged']) && $_SESSION['user_logged'] != "")
	{
		$user = $_SESSION['user_logged'];
	}
	else
	{
		$user = 1;
	}
	$result = "";
	$records = db_fetch("", "", "", "", "id = " . $record, "", $table, true, false, null);

	foreach ($records as $rec)
	{
		$updatedBy = $rec['updatedBy'];
		if ($updatedBy == "")
		{
			$updatedBy = 20;
		}
		$updatedAt = $rec['updatedAt'];
		foreach ($rec as $column => $value)
		{
			$cnt = 0;
			foreach ($label as $lbl)
			{
				if ($column == $lbl)
				{
					$change = false;
					if ($data[$cnt] == "NULL")
					{
						if ($value != NULL)
						{
							$change = true;
						}
					}
					else
					{
						if ($data[$cnt] != $value)
						{
							$change = true;
						}
					}

					if ($change == true)
					{
						$link = db();
						//$sql = "INSERT INTO versions (updatedBy, updatedAt, c_table, c_record, c_field, c_value) values (" . $updatedBy . ",'" . $updatedAt . "','" . $table . "'," . $record . ",'" . $column . "','" . mysqli_real_escape_string($link, stripslashes($value)) . "');";
						//$result = mysqli_query($link, $sql) or myDie(__FILE__.":".__LINE__.":".__CLASS__.":".__METHOD__.$sql);
					};
				}
				$cnt++;
			}
		}
	}

	$link = db();
	$sql = "UPDATE " . $table . " SET updatedBy = " . $user . ", updatedAt = '" . date('Y-m-d H:i:s', time()) . "'";
	$cnt = 0;
	foreach ($label as $lbl)
	{
		$value = $data[$cnt];
		if ($value != "NULL" and $value != 'undefined')
		{

			if ($lbl == "password")
			{
				if ($value != "[ENCRYPTED]")
				{
					$sql .= ", " . $lbl . "='" . MD5($value) . "'";
				}
			}
			else
			{
				if ($value == "" or $value == "1970-01-01 00:00:00" or $value=="1970-01-01 03:00:00" or $value == "???")
				{
					$sql .= ", " . $lbl." = NULL";
				}
				else
				{
					$sql .= ", " . $lbl . "='" . mysqli_real_escape_string($link,stripslashes($value)) . "'";
				}
			}
		}
		$cnt = $cnt +1;
	}
	$sql .= " WHERE id =" . $record . ";";

	$result = mysqli_query($link, $sql) or myDie(__FILE__.":".__LINE__.":".__CLASS__.":".__METHOD__.$sql);
	return $result;
}

function db_delete($record, $table)
{
	$link = db();
	$timenow = date('Y-m-d H:i:s', time());
	$data = array (
		$timenow
	);
	$label = array (
		'deletedAt'
	);
	$result = db_update($record, $table, $label, $data);
	return $result;
}

function db_insert_form($post, $table)
{
	$label = array ();
	$data = array ();

	foreach ($post as $key => $value)
	{
		if (!(strpos($key, "db_") === false))
		{
			$key = str_replace("db_" . $table . "_", "", $key);
			array_push($label, $key);
			if (strpos($key, "_Date") != FALSE)
			{
				$value = date("Y-m-d H:i:s", strtotime($value));
			}
			array_push($data, str_replace("'","\'",$value));
		}
	}

	return db_insert($table, $label, $data);
}

function db_update_form($post, $table)
{
	$label = array ();
	$data = array ();

	foreach ($post as $key => $value)
	{
		if ((strpos($key, "_CHK_ID") === false)) //skip any chk values
		{
			if (!(strpos($key, "db_") === false))
			{
				$key = str_replace("db_" . $table . "_", "", $key);
				if ($key != 'id')
				{
					if (strpos($key, "_HD") === FALSE)
					{
						array_push($label, $key);
						if ($key == "password" and $value != "[ENCRYPTED]")
						{
							$value = MD5($value);
						}
						if (strpos($key, "_Date") != FALSE)
						{
							$value = date("Y-m-d H:i:s", strtotime($value));
						}
//						logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "########################## ".$data." :".$value);

						array_push($data, $value);
					}
				}
				else
				{
					$record = $value;
				}
			}
		}
	}

	return db_update($record, $table, $label, $data);
}

function grid_edit($POST, $table)
{
	$label = array ();
	$data = array ();

	foreach ($POST as $lbl => $dt)
	{
		if ($lbl != "id" && $lbl != "action")
		{
			if ($lbl == "data")
			{
				foreach ($dt as $dx)
				{
					foreach ($dx as $v => $d)
					{
						//logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "--- DB ENTRY 3: ".$v.":".$d);
						array_push($label, $v);
						array_push($data, $d);
					}
				}
			}
		}
	}
	if (isset($_GET['parent']) && $_GET['parent'] != "")
	{
		if (is_array($_SESSION['db_relations'][$_GET['parent']]))
		{
			foreach ($_SESSION['db_relations'][$_GET['parent']] as $prob)
			{
				//			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "--- DB PROB: ".$prob. " against [".$table.".]");
				if (strpos($prob,$table.".") > -1)
				{
							//	logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "--- DB PROB INS: ".str_replace($table.".","",$prob));
					array_push($label, str_replace($table.".","",$prob));
					array_push($data, $_GET['parentId']);
				}
			}
		}
		else
		{
			$prob = $_SESSION['db_relations'][$_GET['parent']];
			//		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "--- DB PROB: ".$prob. " against [".$table.".]");
			if (strpos($prob,$table.".") > -1)
			{
				//str_replace($table.".","",$prob)
				//logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "--- DB PROB INS: ".$prob);
				array_push($label, str_replace($table.".","",$prob));
				array_push($data, $_GET['parentId']);
			}
		}
	}

	if ($POST['action'] == "edit")
	{
		db_update($POST['id'], $table, $label, $data);
		$result = $_POST['id'];
	}

	if ($POST['action'] == "remove")
	{
		foreach ($POST['id'] as $id)
		{
			$result = db_delete($id, $table);
		}
	}

	if ($POST['action'] == "create")
	{
		$result = db_insert($table, $label, $data);
	}

	if ($POST['action'] == "remove")
	{
		return renderJSONDataTable(1, 1, "", "", "id = ".$result, "", $table);
	}
	else
	{
		return renderJSONDataTable(1, 1, "", "", "id = ".$result, "", $table);
	}
}

function fetchForeignValues($view, $table)
{
	if (isset($view[0]))
	{
		foreach ($view[0] as $col => $val)
		{
			$vl = retrieveForeignValue($col, $val, $table);
			if ($vl != "")
			{
				$view[$col] = $vl;
			}
		}
	}
	return $view;
}

function retrieveForeignValue($col, $val, $table)
{
	$vl = "";
	if (strpos($col,"_ID") !== FALSE and $val != "")
	{
		if ($table == "registration" and $col == "name_ID")
		{
			return $val;
		}
		//if ($table == "registration" and $col == "name_ID") return $vl;
		//$tbl = str_replace("_ID","",$col);

		$clazz = new alternateClass($_SESSION['db_relations_bk'][$table.".".$col],"id@$val");

		$ot = json_decode($clazz->jsonpair(),true);

		//echo json_encode($ot['results'][0]['text']);

		if (isset($ot['results'][0]['text']))
		{
			$vl = $ot['results'][0]['text'];
		}
		return $vl;
	}
	else
	{
		return $val;
	}
}

function retrieveFunctionalCategoryValues($proc_class)
{
	return db_fetch("","","","","procurement_class_ID = ".$proc_class,"id, Name, Description","functional_categories",false, false, null);
}


function myDie($sql='', $link = null)
{
//http_response_code(404);
//include('404.php');
//die();
   $details = "";
   if(!empty($sql))
   {
      $details .= "$sql";
   }

//    $details .= "<BR><BR>Backtrace:<BR>".debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	if ($link) echo @mysqli_error($link);
   /*
   $clazz = new mailClass();
*/
   $err = "";
       $err .= "<BR><table style='font-size:12px;'>";
   foreach(debug_backtrace() as $hd => $msg)
   {
       $err .= "<tr><td colspan=\"2\">##################################### ".$hd." #####################################</td></tr>";
   	foreach ($msg as $hd2 => $ms)
   	{
   	    if ($hd2 == "file" or $hd2 == "line" or $hd2 == "function" or $hd2 == "class")
   	    {
   	   		$err .= "<tr><td> ".$hd2." </td><td> ".print_r($ms,true)."</td></tr>";
   	    }
   	    else
   	    {
       		$err .= "<tr><td> ".$hd2." </td><td> <textarea style='font-size:10px;' rows=\"10\" cols=\"200\">".json_encode($ms, JSON_PRETTY_PRINT)."</textarea><BR></td></tr>";
   	    }
   	}

   }
   	$err .= "</table>";
   /*
	$sess ="";
    if (isset($_SESSION))
    {
		$sess = "<BR>SESSION:<BR>".print_r($_SESSION,true)."<BR>";
    }
   */
   //$content = $clazz->send($_SESSION['boot']->errorEmail,$details."<BR>".$err.$sess,"Database Error",null);

  die("There was a database error.<BR><BR>".$details.$err."<BR>");
 // die("There was a database error. The administrator has been informed.");
}
?>
