<?php
class applicationClass extends formClass {
    public function __construct() {
        parent::__construct();
        $this->table = "application";
        $this->clazz = __CLASS__;
    }
    public function index() {
        $view[1] = $this->table;

        if (isset($_GET['page']))
        {
            $page = intval($_GET['page']);
        }
        else {
            $page = 1;
        }
        $records = 20;

        switch ($_SESSION['user_level'])
        {
          case 1: //user
            $disp_data = db_fetch($page, $records, "updatedAt", "desc", "s.user_HD = ".$_SESSION['user_logged'],"s.*, s.id as regid, 'ph' as Full_Name","application s", true, false, null);
            $disp_count = db_fetch("", "", "updatedAt", "desc", "s.user_HD = ".$_SESSION['user_logged'],"s.*, s.id as regid, 'ph' as Full_Name","application s", true, false, null);
          break;
          case 3: //officer
            $disp_data = db_fetch($page, $records, "updatedAt", "desc", "","s.*, s.id as regid, 'ph' as Full_Name","application s", true, false, null);
            $disp_count = db_fetch("", "", "updatedAt", "desc", "","s.*, s.id as regid, 'ph' as Full_Name","application s", true, false, null);
          break;
          case 5: //asst-registrar
            $disp_data = db_fetch($page, $records, "updatedAt", "desc", "","s.*, s.id as regid, 'ph' as Full_Name","application s", true, false, null);
            $disp_count = db_fetch("", "", "updatedAt", "desc", "","s.*, s.id as regid, 'ph' as Full_Name","application s", true, false, null);
          break;
          case 6: //registrar
            $disp_data = db_fetch($page, $records, "updatedAt", "desc", "","s.*, s.id as regid, 'ph' as Full_Name","application s", true, false, null);
            $disp_count = db_fetch("", "", "updatedAt", "desc", "","s.*, s.id as regid, 'ph' as Full_Name","application s", true, false, null);
          break;
          case 9: //administrator
            $disp_data = db_fetch($page, $records, "updatedAt", "desc", "","s.*, s.id as regid, 'ph' as Full_Name","application s", true, false, null);
            $disp_count = db_fetch("", "", "updatedAt", "desc", "","s.*, s.id as regid, 'ph' as Full_Name","application s", true, false, null);
          break;
        }
        $view['disp'] = $disp_data;
        $view['page'] = $page;
        $view['records'] = $disp_count[0]['cnt'];
        $view['pages'] = ceil($disp_count[0]['cnt']/$records);
        $view['table'] = $this->table;

        return renderView(__CLASS__, __METHOD__, $view);
    }
    public function updateRecord() {
        $view[1] = $this->table;

        db_update_form($_POST, $this->table);

        db_insert("document_status", array('document_name','document_ID','status_ID','action_by_HD','notes'), array('search',$_POST['db_search_id'],$_POST['svl'],$_SESSION['user_logged'],$_POST['notesx']));
        sendNotifications($this->table, $_POST['db_search_id']);

        return renderView("index", "saved", $view);
    }

    public function newRecord()
    {
        $view[2] = $this->table;
        $view[1] = db_fetch_columns($view[2], false);

        $user = db_fetch("","","","","id = ".$_SESSION['user_logged'], "","user", false, false, null);

        $label = array();
        $data = array();

        $this->id = db_insert($view[2], $label, $data);
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
        if ($_SESSION['user_level'] == 1 and $view[0]['user_HD'] != $_SESSION['user_logged'])
        {
          $v[0]->error = "You do not have access to this document.";
          return renderView("index", "index", $v);
        }

        $view[1] = $view;
        $view[2] = $this->table;
        $view = fetchForeignValues($view, $this->table);

        return renderViewForward($this->clazz,'newRecord',$view,$this->table.'/updateRecord');
      }
    }
}
