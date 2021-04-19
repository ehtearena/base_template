<?php
class alternateClass implements JsonSerializable
{
	public $search = '';
	public $current_id = '';
	public $nd = '';
	public $page = '';
	public $rows = '';
	public $sidx = '';
	public $sord = '';
	public $table = '';
	public $where = '';
	public $jsonP = true;
	public $jsonSingle = false;
	public $searchOper = ''; //only XML
	public $searchString = '';//only XML
	public $searchField = '';//only XML
	public $select= '';
	public $pdfFormat = 'list'; //list or contract
	public $pdfOrientation = 'L'; //Landscape L or Portait P
	public $term = '';
	public $reportName = '';
	public $outputFormat = 'I';
	public $params = array();
	public $customresult = null;

	public function __construct($table,$term)
    {
    	if (isset($_GET['callback']) && $term == "")
    	{
    		$term = "Please Wait...";
    	}

    	$this->table = $table;
			$this->term = $term;
    	$abbr = "";
		if (isset($_GET['_search'])) { $this->search = $_GET['_search']; }

		if (isset($_GET['searchOper'])) { $this->searchOper = $_GET['searchOper']; }
		if (isset($_GET['searchString'])) { $this->searchString =  $_GET['searchString']; }
		if (isset($_GET['searchField'])) { $this->searchField =  $_GET['searchField']; }
    	if (isset($_GET['single']) && $_GET['single']) { $this->jsonSingle = true; }

		if (isset($_GET['term'])) { $this->search = $_GET['term']; }

		if (isset($_GET['nd'])) { $this->nd = $_GET['nd']; }
		if (isset($_GET['page'])) { $this->page  = $_GET['page']; }
		if (isset($_GET['rows'])) { $this->rows = $_GET['rows']; }
		if (isset($_GET['sidx'])) { $this->sidx = $_GET['sidx']; }
		if (isset($_GET['sord'])) { $this->sord = $_GET['sord']; }
		if (isset($_GET['table'])) { $this->table = $_GET['table']; }
		if (isset($_GET['where'])) { $this->where = $_GET['where']; }
		if (isset($_GET['select'])) { $this->select = $_GET['select']; }
   	if (isset($_GET['current_id'])) { $this->current_id = $_GET['current_id']; }
		if (isset($_GET['reportName'])) { $this->reportName = $_GET['reportName']; }

		if ((isset($_GET['term']) and $_GET['term'] != "") or ($this->term != "") or strpos($_SERVER['REQUEST_URI'], "jsonpairH") > -1)
    	{
    		if (isset($_GET['term']))
    		{
	    		$trm = $_GET['term'];
	    		$this->term = $trm;
    		}
    		else
    		{
    			$trm = $this->term;
    		}

			if (strpos($trm,", ") === FALSE and strpos($trm,",") === FALSE)
			{
				//nothing as yet
			}
			else
			{
				if ($this->table != "products" and $this->table != "branches")
				{
					$trm = preg_split("/\,/", $trm);
					$trm = trim($trm[intval(sizeof($trm))-1]);
				}
			}

			$skipWhere = false;
			if ($trm == "Please Wait...")
			{
				$skipWhere = true;
			}

			if ($this->jsonSingle)
			{

					logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"JSONSingle:". $trm);

			    if ($this->table == "custom_registration_one")
			    {
							$link = db();
							$searchId = $this->searchString;
							$sql = "SELECT IF(proposed_name_1_status_ID,proposed_name_1,NULL) FROM `search` WHERE id = ".$searchId." UNION SELECT IF(proposed_name_2_status_ID,proposed_name_2,NULL) FROM `search` where id = ".$searchId." UNION SELECT IF(proposed_name_3_status_ID,proposed_name_3,NULL) FROM `search` where id = ".$searchId." LIMIT 1";
							$result = mysqli_query($link, $sql);
							$this->customresult = db_fetch("","","","","","","","","",$result);

			    }
			    else if ($this->table == "user")
			    {
			        $this->select = "id as value, First_Name as label";
			        if ($skipWhere == false)
			        {
			            if ($this->where != "") $this->where .= " and ";
			            $this->where .= " id = '".$trm."'";
			        }
			    }
					else if ($this->table == "member")
			    {
			        $this->select = "id as value, concat(member_name,' - ',email) as label";
							if ($skipWhere == false)
			        {
			            if ($this->where != "") $this->where .= " and ";
			            $this->where .= " id = '".$trm."'";
			        }
			    }
					else if ($this->table == "aded")
			    {
							$this->table = "user";
			        $this->select = "id as value, First_Name as label";
							if ($skipWhere == false)
			        {
			            if ($this->where != "") $this->where .= " and ";
			            $this->where .= " id = '".$trm."'";
			        }
							if ($this->where != "") $this->where .= " and ";
							$this->where .= " level = 3";
			    }
					else if ($this->table == "registration")
			    {
			        $this->select = "id as value, name_ID as label";
			        if ($skipWhere == false)
			        {
			            if ($this->where != "") $this->where .= " and ";
			            $this->where .= " id = '".$trm."'";
			        }
			    }
			    else
			    {
			        $this->select = "id as value, name as label";
			        if ($skipWhere == false)
			        {
			            if ($this->where != "") $this->where .= " and ";
									if (strpos($trm,",") > -1)
									{
										$this->jsonSingle = false;
										$this->where .= " id IN (".$trm.")";
									}
									else
									{
										$this->where .= " id = '".$trm."'";
									}
			        }
			    }

			}
			else
			{

				logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"JSONNOTSingle:". $this->table);

				if ($this->table == "custom_registration_one")
				{
						logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"JSONNOTSingle IN");
						$link = db();
						$searchId = $this->searchString;
						$sql = "SELECT IF(proposed_name_1_status_ID = 1,proposed_name_1,NULL) as value, IF(proposed_name_1_status_ID = 1,proposed_name_1,NULL) as label FROM search where id = ".$searchId." UNION  SELECT IF(proposed_name_2_status_ID = 1,proposed_name_2,NULL) as value, IF(proposed_name_2_status_ID = 1,proposed_name_2,NULL) as label FROM search where id = ".$searchId." UNION SELECT IF(proposed_name_3_status_ID = 1,proposed_name_3,NULL) as value, IF(proposed_name_3_status_ID =1,proposed_name_3,NULL) as label FROM search where id = ".$searchId. " LIMIT 1";
						$result = mysqli_query($link, $sql);
						logger(__FILE__,__LINE__,__CLASS__,__METHOD__,"JSONNOTSingle SQL".$sql);
						$this->customresult = $result;

				}
			    else if ($this->table == "user")
			    {
			        $this->select = "id as value, First_Name as label";
			        if ($skipWhere == false)
			        {
			            if ($this->where != "") $this->where .= " and ";
			            $this->where .= " First_Name like '%".$trm."%'";
			        }
			    }
					else if ($this->table == "member")
			    {
			        $this->select = "id as value, concat(member_name,' - ',email) as label";
			        if ($skipWhere == false)
			        {
			            if ($this->where != "") $this->where .= " and ";
			            $this->where .= " email like '%".$trm."%' or member_name like '%".$trm."%'";
			        }
			    }
					else if ($this->table == "aded")
			    {
							$this->table = "user";
			        $this->select = "id as value, First_Name as label";
			        if ($skipWhere == false)
			        {
			            if ($this->where != "") $this->where .= " and ";
			            $this->where .= " First_Name like '%".$trm."%'";
			        }
							if ($this->where != "") $this->where .= " and ";
							$this->where .= " level = 3";
			    }
					else if ($this->table == "registration")
			    {
			        $this->select = "id as value, name_ID as label";
			        if ($skipWhere == false)
			        {
			            if ($this->where != "") $this->where .= " and ";
			            $this->where .= " id = '".$trm."')";
			        }
			    }
			    else
			    {
			        $this->select = "id as value, name as label";
			        if ($skipWhere == false)
			        {
			            if ($this->where != "") $this->where .= " and ";
			            $this->where .= " 2=2 and (name like '%".$trm."%') ";
			        }
			    }
			}

   			if (!(strpos($trm, "id@") === FALSE))
   			{
   				if ($this->where != "") $this->where .= " and ";
					$this->where = $abbr."id = '".str_replace("id@","",$trm)."'";
   			}


   			if ($skipWhere == true && $this->current_id != "")
   			{
	   			if ($this->where != "") $this->where .= " and ";
	   			$this->where .= "id =".$this->current_id." or id = '".$trm."'" ;
   			}
    	}
    }

    public function index()
    {

    }

    public function xml()
    {
    	return renderXML($this->page, $this->rows, $this->sidx, $this->sord, $this->where, $this->select, $this->table, $this->searchOper, $this->searchString, $this->searchField);
    }

    public function excel()
    {
    	return renderExcel($this->page, $this->rows, $this->sidx, $this->sord, $this->where, $this->select, $this->table);
    }

    public function json()
    {
    	return renderJSON($this->page, $this->rows, $this->sidx, $this->sord, $this->where, $this->select, $this->table);
    }

    public function jsonIdOnly()
    {
        return renderJSONID($this->page, $this->rows, $this->sidx, $this->sord, $this->where, $this->select, $this->table);
    }

    public function jsonH()
    {
        if (isset($_GET['del']))
        {
            $ids = explode(",", $_POST['ids']);
            foreach ($ids as $id)
            {
                db_delete($id, $_POST['table']);
            }

            return "ok";
        }

        if (isset($_GET['save']))
        {

//OFFICIAL > MEMBER
if ($_POST['table'] == "official" and isset($_GET['parentTable']) and $_GET['parentTable'] = "registration_HD" and $_GET['parentId'] != "" and $_POST['column'] == "email")
{
	$existingmember = db_fetch("","","","","registration_HD = ".$_GET['parentId']." and email = '".$_POST['column']."'","","member", false, false, null);
	if (sizeOf($existingmember) == 0)
	{
		//add
		$official = db_fetch("","","","","id = ".$_POST['id'],"","official", false, false, null);
		db_insert("member", array('registration_HD','email','member_name'), array($_GET['parentId'], $_POST['value'], $official[0]['full_name']));
	}
}

//END OFFICIAL

//CORRECT TELEPHONE 0722471464
if (($_POST['table'] == "member_details" or $_POST['table'] == "owner") and $_POST['column'] == "telephone")
{
	$_POST['value'] = "0".cleanupPhone($_POST['value']);
}
//END CORRECT Telephone
$rowid = 0;

            if ($_POST['id'] == "null" or trim($_POST['id']) == "")
            {
                if (strpos($_POST['column'], "_ID") > 0)
                {
  									$table = $_SESSION['db_relations_bk'][$_POST['table'] . "." . $_POST['column']];
                    $_POST['value'] = $this->getForeignID($table, $_POST['value']);
                }
								if (!isset($_POST['column']))
								{
									$_POST['column'] = "name";
								}

                $label = array($_POST['column']);
                $data = array($_POST['value']);

								if ($_GET['parentId'] != 0)
								{
									array_push($label, $_GET['parentTable']);
									array_push($data, $_GET['parentId']);
								}

                $insertId = db_insert($_POST['table'], $label, $data);
								$rowid = $insertId;
                return "inserted:" .$insertId;
            }
            else
            {
                if (strpos($_POST['column'], "_ID") > 0)
                {
										$table = $_SESSION['db_relations_bk'][$_POST['table'] . "." . $_POST['column']];

										//TODO: assuming the references table has id,name as fields.
										$dtx = db_fetch("","","","","name = '".$_POST['value']."'", "", $table, false, false, null);
										db_update($_POST['id'], $_POST['table'], array($_POST['column']), array($dtx[0]['id']));
                }
								else
								{
									if ($_POST['value'] == null or $_POST['value'] == "null") $_POST['value'] = "";
	                db_update($_POST['id'], $_POST['table'], array($_POST['column']), array($_POST['value']));
								}

								$rowid = $_POST['id'];
            }

						//ASSOCIATION > Official
						if ($rowid != 0)
						{

							if (($_POST['table'] == "association" or $_POST['table'] == "change_of_association") and isset($_GET['parentTable']) and $_GET['parentTable'] = "registration_HD" and $_GET['parentId'] != "" and $_POST['column'] == "national_identity_card")
							{
								$existingmember = db_fetch("","","","","m.national_identity_card = '".$_POST['value']."'","m.*, t.name as title","member m left join titles t ON t.id = m.title_ID", false, false, null);
								if (sizeOf($existingmember) == 0)
								{
									 db_update($rowid, $_POST['table'], array("member_name"), array("NA"));
								}
								else
								{
									 db_update($rowid, $_POST['table'], array("member_name","title"), array($existingmember[0]['member_name'],$existingmember[0]['title']));
								}
							}

							if (($_POST['table'] == "association" or $_POST['table'] == "change_of_association") and isset($_GET['parentTable']) and $_GET['parentTable'] = "registration_HD" and $_GET['parentId'] != "" and $_POST['column'] == "registration_no")
							{
								$existingassoc = db_fetch("","","","","unique_HD = '".$_POST['value']."'","","registration", false, false, null);
								if (sizeOf($existingassoc) == 0)
								{
									 db_update($rowid, $_POST['table'], array("association_name"), array("NA"));
								}
								else
								{
									db_update($rowid, $_POST['table'], array("association_name"), array($existingassoc[0]['name_ID']));
								}

							}

						}

            return "ok";
        }


        return renderJSONH($this->page, $this->rows, $this->sidx, $this->sord, $this->where, $this->select, $this->table);
    }

    public function getForeignID($table, $value)
    {
        $alternateClass = new alternateClass($table, $value);
        $valjson = $alternateClass->jsonIdOnly();
        return $valjson;
    }

	//Select statement with two items, one named 'value' and the other names 'label'
    public function jsonpairid()
    {
    	if (isset($_GET['term']) or $this->term != "")
    	{
	    	return renderJSONPairId($this->page, $this->rows, $this->sidx, $this->sord, $this->where, $this->select, $this->table);
    	}
    	else
    	{
    		$view[0]->message = "This path requires 'term' as a parameter.";
			return renderView('index','default',$view);
    	}
    }

	//Select statement with two items, one named 'value' and the other names 'label'
    public function jsonpair()
    {
    	if ($this->term == "") $this->where = "";
    	//{
			//return " response:".$this->page."|".$this->rows."|".$this->sidx."|".$this->sord."|".$this->where."|".$this->select."|".$this->table."|".$this->jsonSingle."|".$this->jsonP."|".$this->customresult."<|";
	    return renderJSONPair($this->page, $this->rows, $this->sidx, $this->sord, $this->where, $this->select, $this->table, $this->jsonSingle, $this->jsonP, $this->customresult);
    	//}
    	//else
    	//{
    	//	$view[0]->message = "This path requires 'term' as a parameter!";
			//return renderView('index','default',$view);
    //	}
    }

    public function jsonpairH()
    {
				return renderJSONPairH($this->page, $this->rows, $this->sidx, $this->sord, $this->where, $this->select, $this->table, $this->jsonSingle, $this->jsonP);

    }

    public function pdf()
    {
    	return renderPDF($this->page, $this->rows, $this->sidx, $this->sord, $this->where, $this->select, $this->table, $this->pdfFormat, $this->pdfOrientation, $this->reportName, $this->params, $this->outputFormat);
    }

    public function doc()
    {
    	return renderDOC($this->page, $this->rows, $this->sidx, $this->sord, $this->where, $this->select, $this->table, $this->pdfFormat, $this->pdfOrientation, $this->reportName, $this->params, $this->outputFormat);
    }
    public function jsonSerialize()
    {}


}
