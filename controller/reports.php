<?php
class reportsClass
{
	public function __construct()
    {
    
    }
    
    public function index()
    {
		$usrLevel = $_SESSION['user_level'];
    	$view[1] = db_fetch('', '', '', '', 'c_rights like \'%'.$usrLevel.'%\'', '', 'reports', false, false, null);	
		return renderView(__CLASS__,__METHOD__,$view);
    }

    public function generator()
    {
    	$view = "";
		return renderView(__CLASS__,__METHOD__,$view);
    }
    
    public function generate()
    {
    	if (isset($_POST['db_reports_c_name']) && $_POST['db_reports_c_name'] != "")
    	{
			 $res = db_insert_form($_POST, 'reports');
    	}
    	
		$clazz = new alternateClass('contract',"");
		$clazz->select = $_POST['db_reports_c_select'];
		$clazz->table = $_POST['db_reports_c_table'];
		$clazz->where = $_POST['db_reports_c_where'];
		return $clazz->pdf();
    }
    
    public function generateExistingReport()
    {
    	$id = $_GET['id'];
    	$res = db_fetch('', '', '', '', 'id = '.$id, '', 'reports', true, false, null);	
    	$view[1] = array();
    	$view[2] = $id;
    	
    	if (strpos($res[0]['c_rights'],$_SESSION['user_level']) === FALSE)
    	{
			$view[0]->error = "You do not have rights to view this report.";	    	
	    	$view[1] = db_fetch('', '', '', '', 'c_rights like \'%'.$_SESSION['user_level'].'%\'', '', 'reports', false, false, null);	
			return renderView(__CLASS__,'index',$view);
    	}
    	else
    	{
			$tablenames = array();
    		$tns = preg_split("/\,/", $res[0]['table_names']);
			
			foreach ($tns as $tn)
			{
				$tnp = preg_split("/=/", $tn);
				$tablenames[$tnp[0]] = $tnp[1];
			}
    		
			$chosen = preg_split("/\,/",str_replace(" ","",$res[0]['c_select']));
			
			//get all column names from all tables for the view
			$values = array();
			$labels = array();
			$columns = array();
			$tables = array();
				
			$tbl = str_replace(", ",",",$res[0]['c_table']); 
			$tbl = preg_split("/,/",$tbl);

			foreach ($tbl as $tb)
			{
				$ts = preg_split("/\s/",$tb);					
				$c_table = $ts[0];
				$c_label = $ts[1];
				
				if ($c_table == "")
				{
					continue;
				}
				
				$cols = db_fetch_columns($c_table,false);
				foreach ($cols as $cl)
				{
					array_push($columns,$cl);
					array_push($labels,$c_label);
					array_push($values, $c_label.".".$cl);
					array_push($tables,$c_table);
				}
			}
			$view['labels'] = $labels;
			$view['tablenames'] = $tablenames;
			$view['values'] = $values;
			$view['columns'] = $columns;
			$view['tables'] = $tables;
			$view['chosen'] = $chosen;
			
			//Prepare conditional fields for the report
			$where = $res[0]['c_where'];
			
			if (strpos($where,"@") == TRUE)
			{
				$sel = preg_split("/@/",$where);
				foreach ($sel as $s)
				{
				
					if (strpos("@".$s,"{") == TRUE)
					{
						$s = str_replace("{","",str_replace("}","",$s));
						array_push($view[1],$s);
					}
				}
			}

			//finish
			return renderView(__CLASS__,__METHOD__,$view);
    	}
    }
    
    public function render()
    {
    	$params = array();

    	//History of changes on Process
    	if (isset($_GET['custom']) && $_GET['custom'] == "1005")
    	{
    		$app = db_fetch("","","","","","","approval_status", false, false, null);
    		$appStat = array();
    		foreach ($app as $ap)
    		{
    			$appStat[$ap['id']] = $ap['Name'];
    		}	

    		$pub = db_fetch("","","","","","","publish_status", false, false, null);
    		$pubStat = array();
    		foreach ($pub as $pb)
    		{
    			$pubStat[$pb['id']] = $pb['Name'];
    		}
    		
    		if (isset($_SESSION['user_level']) && $_SESSION['user_level'] > 2)
    		{
    			$pdf = null;
    			$orient = "L";
    			$reportName = "All Processes";
    			$outputFormat = "I";
    			
    			$link = db();
    			$sql = "SELECT
			    	p.*,
					v.c_field as Changed_Field,
			    	v.c_value as Changed_Value,
			    	v.updatedAt as Changed_Date,
			    	u.Full_Name as Changed_By,
			    	u2.Full_Name as LastUpdateBy 
			    	FROM
			    	process p
			    	LEFT JOIN versions v ON v.c_table = 'Process' and v.c_record = p.id and v.c_field not in ('Procurement_Officer_ID','Consultant_First_Contact_RT5','ITT_Document_Signed_By_ID','Publish_Status_ID','createdAt_HD','Review_Email_Sent_HD','Approval_Email_Sent_HD') 
			    	LEFT JOIN user u ON u.id = v.updatedBy
			    	LEFT JOIN user u2 ON u2.id = p.updatedBy
   					WHERE p.createdAt_HD >= '2014-01-01 00:00:00' and p.Title != '' and p.Approval_Submission_HD is not null and v.c_value != '' and v.updatedAt >= '2014-01-01 00:00:00';";
	   			$result = mysqli_query($link, $sql);
    			$res = db_fetch("", "", "", "", "", "", "", false, false, $result);
    			 
    	
    			$html = "<h2>Changes to Process Record</h2><BR><table border=\"0.3\" cellpadding=\"2\">";
    			$html .= "<tr><td>Title</td><td>Created</td><td>Changed Field</td><td>Last Value</td><td>Last Update By</td><td>Previous Value</td><td>Changed Date</td><td>Changed By</td></tr>";
    			$recs = 0;
    			foreach ($res as $rs)
    			{
    				$curval = preg_replace('/[^(\x20-\x7F)]*/','',$rs[$rs['Changed_Field']]);
    				$cval = preg_replace('/[^(\x20-\x7F)]*/','', $rs['Changed_Value']);

    				if ($rs['Changed_Field']== 'Approval_Status_ID')
    				{
    					$cval = $appStat[$cval];    					
    					$curval = $appStat[$curval];
    				}
/*    				if ($rs['Changed_Field']== 'Publish_Status_ID')
    				{
    					$cval = $pubStat[$cval];
    					$curval = $pubStat[$curval];
    				}
*/  				
    				$html .= "<tr><td>".$rs['Title']."</td><td>".$rs['createdAt_HD']."</td><td>".$rs['Changed_Field']."</td><td>".$curval."</td><td>".$rs['LastUpdateBy']."</td><td>".$cval."</td><td>".$rs['Changed_Date']."</td><td>".$rs['Changed_By']."</td></tr>";
    				$recs++;
    			}
    			$html .= "</table>";
    			 
    			$html .= "<BR><BR>Total Records: ".$recs;
    			return generatePDF($pdf, $orient, $html, $reportName, $outputFormat);
    		}
    		else
    		{
    			$view[0]->error = "You do not have rights to view this report.";
    			return renderView(__CLASS__,'index',$view);
    		}
    	}
    	 
    	
    	//All processes
    	if (isset($_GET['custom']) && $_GET['custom'] == "1001")
    	{
   	    	if (isset($_SESSION['user_level']) && $_SESSION['user_level'] > 2)
	    	{
	    		$pdf = null;
	    		$orient = "L";
	    		$reportName = "All Processes";
	    		$outputFormat = "I";

	    		$link = db();
	    		$sql = "SELECT p.Title, r.Name as rTitle, m.Procurement_Method as method, u.Full_Name FROM process p 
	    				LEFT JOIN responsibility_center r on r.id = p.responsibility_center_ID 
	    				LEFT JOIN procurement_method m on m.id = p.procurement_method_ID 
	    				LEFT JOIN user u on u.id = p.createdBy_HD 
	    				WHERE p.id != 18 and p.Title != ''";
	    		$result = mysqli_query($link, $sql);	    		 
				$res = db_fetch("", "", "", "", "", "", "", false, false, $result);
	    		
				
				$html = "<h2>All Processes</h2><BR><table border=\"0.3\" cellpadding=\"2\">";
				$html .= "<tr><td>Title</td><td>Resp. Center</td><td>Method</td><td>Initiated By</td></tr>";
				$recs = 0;
				foreach ($res as $rs)
				{
					$html .= "<tr><td>".$rs['Title']."</td><td>".$rs['rTitle']."</td><td>".$rs['method']."</td><td>".$rs['Full_Name']."</td></tr>";
					$recs++;
				}
				$html .= "</table>";
	    		
				$html .= "<BR><BR>Total Records: ".$recs;
				return generatePDF($pdf, $orient, $html, $reportName, $outputFormat);
	    	}
	    	else
	    	{
	    		$view[0]->error = "You do not have rights to view this report.";
	    		return renderView(__CLASS__,'index',$view);
	    	}
    	}
    	
    	if (isset($_GET['custom']) && $_GET['custom'] == "1003")
    	{
    		if (isset($_SESSION['user_level']) && $_SESSION['user_level'] > 2)
    		{
    			$pdf = null;
    			$orient = "L";
    			$reportName = "TMEA Advertisement Sources - by Process";
    			$outputFormat = "I";
    	
    			$link = db();
    			$sql = "SELECT p.title as Process_Title, a.referrer as Source FROM applications a, process p WHERE p.id = a.process_ID and a.referrer is not null;";
    			$result = mysqli_query($link, $sql);
    			$res = db_fetch("", "", "", "", "", "", "", false, false, $result);
    			 
    	
    			$html = "<h2>TMEA Advertisement Sources - by Process</h2><BR><table border=\"0.3\" cellpadding=\"2\">";
    			$html .= "<tr><td>Title</td><td>Source</td></tr>";
    			$recs = 0;
    			foreach ($res as $rs)
    			{
    				$html .= "<tr><td>".$rs['Process_Title']."</td><td>".$rs['Source']."</td></tr>";
    				$recs++;
    			}
    			$html .= "</table>";
    			 
    			$html .= "<BR><BR>Total Records: ".$recs;
    			return generatePDF($pdf, $orient, $html, $reportName, $outputFormat);
    		}
    		else
    		{
    			$view[0]->error = "You do not have rights to view this report.";
    			return renderView(__CLASS__,'index',$view);
    		}
    	}
    	 
		
    	if (isset($_GET['custom']) && $_GET['custom'] == "1004")
    	{
    		if (isset($_SESSION['user_level']) && $_SESSION['user_level'] > 2)
    		{
    			$pdf = null;
    			$orient = "L";
    			$reportName = "TMEA Advertisement Sources - by Referrer";
    			$outputFormat = "I";
    			 
    			$link = db();
    			$sql = "SELECT a.referrer as Referrer, count(a.referrer) as Frequency FROM applications a, process p WHERE p.id = a.process_ID and a.referrer is not null group by a.referrer;";
    			$result = mysqli_query($link, $sql);
    			$res = db_fetch("", "", "", "", "", "", "", false, false, $result);
    	
    			$html = "<h2>TMEA Advertisement Sources - by Referrer</h2><BR><table border=\"0.3\" cellpadding=\"2\">";
    			$html .= "<tr><td>Referrer</td><td>Frequency</td></tr>";
    			$recs = 0;
    			foreach ($res as $rs)
    			{
    				$html .= "<tr><td>".$rs['Referrer']."</td><td>".$rs['Frequency']."</td></tr>";
    				$recs++;
    			}
    			$html .= "</table>";
    	
    			$html .= "<BR><BR>Total Records: ".$recs;
    			return generatePDF($pdf, $orient, $html, $reportName, $outputFormat);
    		}
    		else
    		{
    			$view[0]->error = "You do not have rights to view this report.";
    			return renderView(__CLASS__,'index',$view);
    		}
    	}
    	
    	 
		//Evaluations
    	if (isset($_GET['custom']) && $_GET['custom'] == "1002")
    	{
    		if (isset($_SESSION['user_level']) && $_SESSION['user_level'] > 2)
    		{
    			$pdf = null;
    			$orient = "L";
    			$reportName = "Process and Contract Evaluations";
    			$outputFormat = "I";
    	
    			$link = db();
    			$sql = "SELECT p.Title, r.Name as rTitle, m.Procurement_Method as method, u.Full_Name, c.Number, c.Start_Date, c.End_Date, c.eval_sent as TMEA_Evals, c.perf_eval_sent as Performance_Evals, sce.id as sceid, pe.id as peid, s.Name as consultant_name FROM process p
	    				LEFT JOIN responsibility_center r on r.id = p.responsibility_center_ID
	    				LEFT JOIN procurement_method m on m.id = p.procurement_method_ID
    					LEFT JOIN contract c on c.terms_of_reference_ID = p.terms_of_reference_ID
    					LEFT JOIN consultant s on s.id = c.consultant_ID 
    					LEFT JOIN user u on u.id = p.Approving_Manager_ID
    					LEFT JOIN performance_evaluation pe on pe.contract_ID = c.id
    					LEFT JOIN supplier_contract_evaluation sce on sce.contract_HD = c.id
    					WHERE p.id != 18 and p.Title != '' and c.End_Date < '2014-08-01 00:00:00' and c.End_Date >= '2014-07-01 00:00:00' group by p.Title";
    			$result = mysqli_query($link, $sql);
    			$res = db_fetch("", "", "", "", "", "", "", false, false, $result);
    			 
    	
    			$html = "<h2>All Processes - 1 July 2014 - 31 July 2014</h2><BR><table border=\"0.3\" cellpadding=\"2\">";
    			$html .= "<tr><td>Title</td><td>Resp. Center</td><td>Method</td><td>Manager</td><td>PO Number</td><td>Consultant/Supplier Name</td><td>Start Date</td><td>End Date</td><td>Evaluations sent to Consultants</td><td>Evaluations by Consultants/Suppliers</td><td>Evaluations sent to Managers</td><td>Evaluations by Managers</td></tr>";
    			$recs = 0;
				$sEval = 0;
				$sces = 0;
				$pes = 0;
				$mEval = 0;    			
    			foreach ($res as $rs)
    			{
    				$thisSce = "0";
    				if ($rs['sceid'] != null && $rs['sceid'] != "")
    				{
    					$sces++;
    					$thisSce = "1";
    				}
    				
    				$thisPe = "0";
    				if ($rs['peid'] != null && $rs['peid'] != "")
    				{
    					$pes++;
    					$thisPe = "1";
    				}
    				
    				$sEval += intval($rs['TMEA_Evals']);
    				$mEval += intval($rs['Performance_Evals']);
    				$html .= "<tr><td>".$rs['Title']."</td><td>".$rs['rTitle']."</td><td>".$rs['method']."</td><td>".$rs['Full_Name']."</td><td>".$rs['Number']."</td><td>".$rs['consultant_name']."</td><td>".$rs['Start_Date']."</td><td>".$rs['End_Date']."</td><td>".$rs['TMEA_Evals']."</td><td>".$thisSce."</td><td>".$rs['Performance_Evals']."</td><td>".$thisPe."</td></tr>";
    				$recs++;
    			}
    			$html .= "</table>";
    			 
//    			$html .= "<BR><BR><BR>Total Records: ".$recs;
				$html .= "<BR>Total Evaluation Requests Sent to Suppliers: ".$sEval;
				$html .= "<BR>Total Evaluation by Suppliers: ".$sces;
				$html .= "<BR>Total Evaluation Requests Sent to TMEA Managers: ".$mEval;
				$html .= "<BR>Total Evaluation by Managers: ".$pes;
				return generatePDF($pdf, $orient, $html, $reportName, $outputFormat);
    		}
    		else
    		{
    			$view[0]->error = "You do not have rights to view this report.";
    			return renderView(__CLASS__,'index',$view);
    		}
    	}
    	 
    	$addWhere = "";
    	foreach ($_POST as $pst => $val)
    	{
			if (!is_array($pst) && strpos($pst,"filter") > 0)
			{
				//All select filter and filter values
				if (strpos($pst,"_filter") > 0)
				{
					if ($val != "")
					{
						if ($addWhere != "")
						{
							$addWhere .= " AND ";
						}
						$sht = str_replace("x_filter_","",$pst);
						if ($val == "LIKE")
						{
							$cnd[$sht] = urldecode($val)." '%".$_POST['x_valuefilter_'.$sht]."%'";
						}
						else
						{
							if (is_numeric($_POST['x_valuefilter_'.$sht]))
							{
								$cnd[$sht] = urldecode($val)." ".$_POST['x_valuefilter_'.$sht];
							}
							else
							{
								$cnd[$sht] = urldecode($val)." '".$_POST['x_valuefilter_'.$sht]."'";
							}
						}
						$addWhere .= str_replace("@",".",$sht)." ".$cnd[$sht];
						if (urldecode($val) == "<") { $val = "less than "; }
						if (urldecode($val) == "<=") { $val = "less than or equal to "; }
						if (urldecode($val) == ">=") { $val = "more than or equal to "; }
						if (urldecode($val) == ">") { $val = "more than "; }
						if (strpos($sht,"@") > 0)
						{
							$shta = preg_split("/@/",$sht);
							$shtx = $shta[1];
						}
						else
						{
							$shtx = $sht;	
						}
						$params[$shtx] = "'".$val." ".$_POST['x_valuefilter_'.$sht]."'";
					}
				}
			}
    	}



    	$type = $_POST['type'];
    	$id = $_POST['id'];
    	$res = db_fetch('', '', '', '', 'id = '.$id, '', 'reports', false, false, null);	
    	
		$select = "";
		$first = true;
		$colsarr = array();
		$cnt = 0;
		
		foreach ($_POST['cols'] as $cols)
		{
			$chcols = "";
			if (!strpos($cols,".") === FALSE)
			{
				$plt = preg_split("/\./",$cols);
				$chcols = $plt[1];
			}
			else
			{
				$chcols = $cols;
			}
			if ($chcols != "" && isset($colsarr[$chcols]))
			{
				$cols = $cols." as ".$chcols."_".$cnt;
				$cnt++; 
			}
			
			$colsarr[$chcols] = 1;
			if ($first == false) $select .= ", ";
			$select .= $cols;			
			$first = false;
		}

    	$table = $res[0]["c_table"];
    	$where = $res[0]["c_where"];

		if ($addWhere != "" && $where != "")
		{
			$where .= " AND ".$addWhere;	
		}
		else
		{
			$where .= $addWhere;	
		}

		foreach ($_POST as $col => $val)
		{
			if ($col != "id" and $col != "Run" and $col != "cols" and $col != "orientation" and $col != "type" and strpos($col, "filter_") === FALSE)
			{
				$params[$col] = $val;
				if (strpos($col,"_Date") == true)
				{
					$val =  date('Y-m-d H:i:s', strtotime($val));					
				}
		    	$where = str_replace('@{'.$col.'}@',$val,$where);
			}
		}
    	
		$clazz = new alternateClass("output","");
		$clazz->reportName = $res[0]["c_name"];
		if (sizeOf($params) > 0)
		{
			$clazz->params = $params;
		}
		$clazz->select = $select;
		$clazz->table = $table;
		$clazz->where = $where;
		$clazz->pdfOrientation = $_POST['orientation'];

		if ($type == "xml")
		{
			return " xml:".$clazz->$type();
		}
		if ($type == "excel")
		{
			return " excel:".$clazz->$type();
		}
		return $clazz->$type();
    }
}

?>