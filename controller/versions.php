<?php
class versionsClass
{
	public function __construct()
    {
    
    }
    
    public function index()
    {
    	$view = "";
		return renderView(__CLASS__,__METHOD__,$view);
    }
    
    public function retrieve()
    {
	   	$table = $_POST["table"];
	   	$field = $_POST["field"];
	   	$record = $_POST["record"];
	   	$res = db_fetch("","","a.updatedAt","desc","a.c_table = '".$table."' and a.c_field = '".$field."' and a.c_record = '".$record."' and b.id = a.updatedBy","a.*, b.username as Username","versions a, user b", true, false, null);
		$v = "";
		foreach ($res as $rs)
		{
			if (strpos($field,"_ID") === FALSE)
			{
				$thisVal = $rs['c_value'];	
			}
			else
			{
				$thisVal .= retrieveForeignValue($field, $rs['c_value'], $table);
			}
			if ($thisVal != "")
			{
				$v .= "on ";
				$v .= date('d-M-y', strtotime($rs['updatedAt']))." by ".$rs['Username']."<BR>";
				$v .= $thisVal."<BR><HR>";
			}
			
		}    	
		if ($v == "")
		{
			$v = "No history for this field.";			
		}
		
		return " response: ".$v;
    }
}

?>

