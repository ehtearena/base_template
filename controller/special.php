<?php

class specialClass
{
	public function __construct()
    {

    }

    public function index()
    {
    	$view = "";
		return renderView(__CLASS__,__METHOD__,$view);
    }


    public function sendBulkRegistration()
    {
    	//Manager - 5
    	//Director - 6

		$this->sendRegistration(array('Jose Maciel','jose.maciel@trademarkea.com',6, generateRandom(8),'jmaciel'));
		$this->sendRegistration(array('Elizabeth Murugi Nderitu','elizabeth.nderitu@trademarkea.com',5, generateRandom(8),'enderitu'));
		$this->sendRegistration(array('Lisa Karanja','lisa.karanja@trademarkea.com',6, generateRandom(8),'lkaranja'));
		$this->sendRegistration(array('Gloria Atuheirwe','gloria.atuheirwe@trademarkea.com',5, generateRandom(8),'gatuheirwe'));
		$this->sendRegistration(array('Donna Loveridge','donna.loveridge@trademarkea.com',6, generateRandom(8),'dloveridge'));
		$this->sendRegistration(array('Elizabeth Mwangi','elizabeth.mwangi@trademarkea.com',5, generateRandom(8),'emwangi'));
		$this->sendRegistration(array('Vandi Hill','vandi.hill@trademarkea.com',6, generateRandom(8),'vhill'));
		$this->sendRegistration(array('Nelson Karanja','nelson.karanja@trademarkea.com',5, generateRandom(8),'nkaranja'));
		$this->sendRegistration(array('Susan Mwangi','susan.mwangi@trademarkea.com',5, generateRandom(8),'smwangi'));
		$this->sendRegistration(array('Athman Mohamed','athman.mohamed@trademarkea.com',6, generateRandom(8),'amohamed'));
		$this->sendRegistration(array('Edward Ichungwa','edward.ichungwa@trademarkea.com',6, generateRandom(8),'eichungwa'));
		$this->sendRegistration(array('George Wolf','george.wolf@trademarkea.com',6, generateRandom(8),'gwolf'));
		$this->sendRegistration(array('Silas Kanamugire','silas.kanamugire@trademarkea.com',6, generateRandom(8),'skanamugire'));
		$this->sendRegistration(array('SjoerdVisser','sjoerd.visser@trademarkea.com',6, generateRandom(8),'svisser'));
		$this->sendRegistration(array('Njoki Mungai','njoki.mungai@trademarkea.com',5, generateRandom(8),'nmungai'));
		$this->sendRegistration(array('Peter Wanyoro','peter.wanyoro@trademarkea.com',5, generateRandom(8),'pwanyoro'));
		$this->sendRegistration(array('Theo Lyimo','theo.lyimo@trademarkea.com',6, generateRandom(8),'tlyimo'));
		$this->sendRegistration(array('Kiema Mwandia','kiema.mwandia@trademarkea.com',5, generateRandom(8),'kmwandia'));
		$this->sendRegistration(array('Charity Mureithi','charity.mureithi@trademarkea.com',6, generateRandom(8),'cmureithi'));
		$this->sendRegistration(array('George Mathenge','george.mathenge@trademarkea.com',5, generateRandom(8),'gmathenge'));
		$this->sendRegistration(array('Wairimu Kireri-Kinuthia','wairimu.kireri-kinuthia@trademarkea.com',5, generateRandom(8),'wkireri-kinuthia'));
		$this->sendRegistration(array('Abdul-Kadir Ally ','abdul.ally@trademarkea.com',5, generateRandom(8),'aally'));
		$this->sendRegistration(array('Clyde Castelino','clyde.castelino@trademarkea.com',5, generateRandom(8),'ccastelino'));
		$this->sendRegistration(array('Jason Kap-Kirwok','jason.kapkirwok@trademarkea.com',6, generateRandom(8),'jkapkirwok'));
		$this->sendRegistration(array('Joshua Mutunga','joshua.mutunga@trademarkea.com',5, generateRandom(8),'jmutunga'));
		$this->sendRegistration(array('Annette Mutaawe','annette.mutaawe@trademarkea.com',6, generateRandom(8),'amutaawe'));
		$this->sendRegistration(array('Moses Sabiiti','moses.sabiiti@trademarkea.com',5, generateRandom(8),'msabiiti'));
		$this->sendRegistration(array('Paulina Elago','paulina.elago@trademarkea.com',6, generateRandom(8),'pelago'));
		$this->sendRegistration(array('Andrew Mphuru','andrew.mphuru@trademarkea.com',5, generateRandom(8),'amphuru'));
		$this->sendRegistration(array('Mark Priestley','mark.priestley@trademarkea.com',6, generateRandom(8),'mpriestley'));
		$this->sendRegistration(array('John BoscoKalisa','john.kalisa@trademarkea.com',5, generateRandom(8),'jkalisa'));
		$this->sendRegistration(array('Anthe Vrijlandt','anthe.vrijlandt@trademarkea.com',6, generateRandom(8),'avrijlandt'));
		$this->sendRegistration(array('Carine Bigira','carine.bigira@trademarkea.com',5, generateRandom(8),'cbigira'));
		$this->sendRegistration(array('Penny Simba','penny.simba@trademarkea.com',6, generateRandom(8),'psimba'));
		$this->sendRegistration(array('Myra Deya','myra.deya@trademarkea.com',5, generateRandom(8),'mdeya'));
		$this->sendRegistration(array('Eugene Torero','eugene.torero@trademarkea.com',6, generateRandom(8),'etorero'));
   }
    public function sendRegistration($dt)
    {
    	$label = array("first_name", "emailAddress", "level", "password", "username");
		$data = $dt;
		$insertId = db_insert('user',$label,$data);
		$content = sendActivationEmail($insertId,$data[0],$data[1],15,$data[2], $data[4], $data[3]);
    }
}

?>
