<?php

class problemClass
{
	public function __construct()
    {
    
    }
    
    public function index()
    {
    	$view = "";
		return renderView(__CLASS__,__METHOD__,$view);
    }

    public function submit()
    {
		$clazz = new mailClass();
		$content = $clazz->send('ehteshamm@gmail.com',implode("<BR>",$_POST),"Report a Problem",null);
    	
    	$view[0]->message = "Thank you for reporting the problem you were facing.";
		return renderView('index','index',$view);
    }
}

?>