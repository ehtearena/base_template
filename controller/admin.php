<?php

class adminClass
{
	public $table;

	public function __construct()
    {
    	if (isset($_GET['table']))
    	{
    		$this->table = $_GET['table'];
    	}
    }

    public function index()
    {
		$view = "";
		return renderView(__CLASS__,__METHOD__,$view);
    }

		public function editTable()
	     {
	     	$table = $this->table;
	     	if(isset($_POST['oper']))
	     	{
	 			return " response:".grid_edit($_POST,$table);
	     	}
	     	else
	     	{
 	        return renderView('index','default',gridJavaScriptH($table,null,null,null,null,"list",$table, false, null, null));
	     	}
	     }
		 }

?>
