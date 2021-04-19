<?php

class helpClass
{
	public function __construct()
    {
    
    }
    
    public function index()
    {
    	$view = "";
		return renderView(__CLASS__,__METHOD__,$view);
    }

    public function acronyms()
    {
    	$view = "";
		return renderView(__CLASS__,__METHOD__,$view);
    }

    public function admin()
    {
    	$view = "";
		return renderView(__CLASS__,__METHOD__,$view);
    }
}

?>