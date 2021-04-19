<?php
class formClass
{
	public $id;
	public $table;
	public $clazz;
	public $referringEntity;
	public $referringEntityId;

	public function getSuccessMessage($msg, $buttonLink = "")
	{
		$message = '<div class="alert bg-fusion-400 border-0 fade show">
		                                                    <div class="d-flex align-items-center">
		                                                        <div class="alert-icon">
		                                                            <i class="fal fa-shield-check text-warning"></i>
		                                                        </div>
		                                                        <div class="flex-1">
		                                                            <span class="h5">Success</span>
		                                                            <br>
		                                                            	'.$msg.'
		                                                        </div>';

    if ($buttonLink != "")
		{
			$message.= '<a href="'.$buttonLink.'" class="btn btn-warning btn-w-m fw-500 btn-sm waves-effect waves-themed">Proceed</a>';
		}
		$message .= '</div></div>';

		return $message;
	}

	public function getWarningMessage($msg, $buttonLink = "")
	{
		$message = '<div class="alert bg-warning-500 alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true"><i class="fal fa-times"></i></span></button><div class="d-flex align-items-center"><div class="alert-icon"><span class="icon-stack icon-stack-sm"><i class="base-7 icon-stack-3x color-fusion-200"></i><i class="base-7 icon-stack-2x color-fusion-500"></i><i class="ni ni-graph icon-stack-1x text-white"></i></span></div><div class="flex-1"><span class="h5">Notice</span><br>'.$msg.'</div></div></div>';

		return $message;
	}


	public function getFailMessage($msg, $buttonLink = "")
	{
			$message = '<div class="alert bg-warning-500 alert-dismissible fade show">
                                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                        <span aria-hidden="true"><i class="fal fa-times"></i></span>
                                                    </button>
                                                    <div class="d-flex align-items-center">
                                                        <div class="alert-icon">
                                                            <span class="icon-stack icon-stack-sm">
                                                                <i class="base-7 icon-stack-3x color-fusion-200"></i>
                                                                <i class="base-7 icon-stack-2x color-fusion-500"></i>
                                                                <i class="ni ni-graph icon-stack-1x text-white"></i>
                                                            </span>
                                                        </div>
                                                        <div class="flex-1">
                                                            <span class="h5">We\'re sorry!</span>
                                                            <br>
																														'.$msg.'
                                                        </div>
                                                    </div>
                                                </div>';

			return $message;
		}

	public function __construct()
    {
    	if (isset($_GET['id']))
    	{
    		$this->id = $_GET['id'];
    	}
    	if (isset($_POST['entity_HD']))
    	{
    		$this->referringEntity = $_POST['entity_HD'];
    	}
    	if (isset($_POST['entityId_HD']))
    	{
    		$this->referringEntityId = $_POST['entityId_HD'];
    	}
    	$this->clazz = __CLASS__;
   		$this->table = $_SESSION['boot']->getController(); //usually gets overridden by the actual table
    }

    public function index()
    {
    	$view[1] = $this->table;
			return renderView($this->clazz,str_replace("formClass::","",__METHOD__),$view);
    }

    public function newRecord()
    {

		$view[2] = $this->table;
		$view[1] = db_fetch_columns($view[2], false);

		if (isset($_GET['entity']) && $_GET['entity'] == "process")
		{
			$pr = db_fetch("","","","","id = ".$_GET['entityId'],"",$_GET['entity'], false, false, null);
			$title = $pr[0]['Title'];

			if ($this->table == "terms_of_reference" or $this->table == "specifications_document")
			{
				$label = array('Title');
				$data= array($title);
			}
			else if ($this->table == "process_criteria" or $this->table == "process_panel")
			{
				$label = array('Name');
				$data= array($title);
			}
			else
			{
				$label = array();
				$data= array();
			}
		}
		else
		{
			$label = array();
			$data= array();
		}

		$this->id = db_insert($view[2],$label,$data);

		return $this->editRecord();
    }

    public function editRecord()
    {
    	if (!isset($_GET['id']))
    	{
    		$id = $this->id;
    	}
    	else
    	{
			$id = $_GET['id'];
    	}

    	if (isset($_GET['submit']) && $_GET['submit'] == "PDF")
    	{
				$clazz = new alternateClass($this->table,"");
				$clazz->where = "id =".$id;
				$clazz->pdfFormat = "list";
				$content = $clazz->pdf();
				$this->render =  loadLayout($content,'pdf',$boot->getAppRoot());
    	}
    	else
    	{
				$view = db_fetch("", "", "", "", "id =".$id, "", $this->table, false, false ,null);
				$view[1] = $view;
				$view[2] = $this->table;
				$view = fetchForeignValues($view, $this->table);
				return renderViewForward($this->clazz,'newRecord',$view,$this->table.'/updateRecord');
    	}
    }

    public function saveRecord()
    {
    	$view[1] = $this->table;
		db_insert_form($_POST,$this->table);

		if (UCFirst($this->table) == "Consultant")
		{
			$view[0]->message = "Profile Saved";
		}
		else
		{
			$view[0]->message = UCFirst($this->table)." Saved ";
		}

		if (isset($this->referringEntity) && isset($this->referringEntityId))
		{
			$classname=$this->referringEntity.'Class';
			require_once "../controller/".$this->referringEntity.".php";
			$clazz = new $classname;
			$clazz->id = $this->referringEntityId;
			$content = $clazz->editRecord();
			return $content;
		}
		else
		{
			return renderView($this->clazz,'index',$view);
		}
    }

    public function updateRecord()
    {
    	$view[1] = $this->table;
		db_update_form($_POST, $this->table);

		$view[0]->message = UCFirst($this->table)." Saved";
		if (isset($this->referringEntityId))
		{
			$entity = $this->referringEntity;
			require_once "../controller/$entity.php";
			$classname=$entity.'Class';
			$clazz = new $classname;
			$clazz->id = $this->referringEntityId;

			if (isset($_POST['field']))
			{

				foreach ($_POST as $key => $value)
				{
					if ((strpos($key, "_CHK_ID") === false)) //skip any chk values
					{
						if (!(strpos($key, "db_") === false))
						{
							$key = str_replace("db_" . $this->table . "_", "", $key);
							if ($key != 'id')
							{
							}
							else
							{
								$this->id = $value;
							}
						}
					}
				}

				db_update($this->referringEntityId,$entity,array($_POST['field']),array($this->id));
			}

			return $content = $clazz->editRecord();
		}

		if ($_SESSION['user_level'] == 0)
		{
			$_SESSION['layout'] = "single";
      $_POST['layout'] = "single";
			return renderView($this->clazz,'index',$view);

		}

		return renderView($this->clazz,'index',$view);
    }

	public function displayRecord()
	{
    	if (!isset($_GET['id']))
    	{
    		$id = $this->id;
    	}
    	else
    	{
			$id = $_GET['id'];
    	}

    	$view[1] = $this->table;

		$res = db_fetch("","","","","id = ".$this->id,"",$this->table, false,false,null);
		if ($res)
		{
			if (file_exists("../templates/" . $this->table. "_view.php"))
			{
				ob_start();
				include ("../templates/" . $this->table. "_view.php");
				$contents = ob_get_contents(); // assign buffer contents to variable
				ob_end_clean();

				foreach($res[0] as $col => $val)
				{
					$contents = str_replace('@'.$col.'@', $val, $contents);
				}
				return $contents;
			}
			else
			{
				$view[0]->message = "View for ".$this->table." does not exist.";
				return renderView('form','index',$view);
			}

		}
		else
		{
			$view[0]->message = "The requested record is not found.";
			return renderView('form','index',$view);
		}
	}
}
?>
