<?php
class itemClass extends formClass
{
	public function __construct()
	{
		parent::__construct();
		$this->table = "item";
		$this->clazz = __CLASS__;
	}

	public function index()
	{
		$view[1] = $this->table;
		return renderView(__CLASS__,__METHOD__,$view);
	}

	public function notification()
	{
		$view[1] = $this->table;
		return renderView(__CLASS__,__METHOD__,$view);
	}
}
?>