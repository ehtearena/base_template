<?php
require_once '../helper/QuickBooks.php';


class quickbooksClass extends formClass
{
	public $user = 'ehtearena';
	public $pass = "ejbatsair";
	
	public $map = array(
			QUICKBOOKS_ADD_INVOICE => array( '_quickbooks_add_invoice_request', '_quickbooks_add_invoice_response' ),
			QUICKBOOKS_ADD_CUSTOMER => array( '_quickbooks_add_customer_request', '_quickbooks_add_customer_response' ),
			QUICKBOOKS_ADD_RECEIVEPAYMENT => array( '_quickbooks_add_payment_request', '_quickbooks_add_payment_response' )
	);
	public $hooks = array(
			// There are many hooks defined which allow you to run your own functions/methods when certain events happen within the framework
			// QuickBooks_WebConnector_Handlers::HOOK_LOGINSUCCESS => '_quickbooks_hook_loginsuccess', 	// Run this function whenever a successful login occurs
	);
	
	//$log_level = QUICKBOOKS_LOG_NORMAL;
	//$log_level = QUICKBOOKS_LOG_VERBOSE;
	//$log_level = QUICKBOOKS_LOG_DEBUG;
	public $log_level = QUICKBOOKS_LOG_DEVELOP;
	
	public $soapserver = QUICKBOOKS_SOAPSERVER_BUILTIN;
	
	public $soap_options = array();
	
	public $handler_options = array(
			'deny_concurrent_logins' => false,
			'deny_reallyfast_logins' => false,
	);		// See the comments in the QuickBooks/Server/Handlers.php file
	
	public $driver_options = array(		// See the comments in the QuickBooks/Driver/<YOUR DRIVER HERE>.php file ( i.e. 'Mysql.php', etc. )
			//'max_log_history' => 1024,	// Limit the number of quickbooks_log entries to 1024
			//'max_queue_history' => 64, 	// Limit the number of *successfully processed* quickbooks_queue entries to 64
	);
	
	public $errmap = array();
	
	public $callback_options = array();
	
	public $dsn;

	public function __construct()
	{
		parent::__construct();
		$this->table = "quickbooks";
		$this->clazz = __CLASS__;

		$dsn = "mysql://".$_SESSION['boot']->getUser().':'.$_SESSION['boot']->getPassword().'@'.$_SESSION['boot']->getHost().'/'.$_SESSION['boot']->getDatabase();
		$this->dsn = $dsn;
		
		if (!QuickBooks_Utilities::initialized($dsn))
		{
			QuickBooks_Utilities::initialize($dsn);
			QuickBooks_Utilities::createUser($dsn, $this->user, $this->pass);
		}
	}

	public function index()
	{
		$view[1] = $this->table;
		return renderView(__CLASS__,__METHOD__,$view);
	}

	public function update()
	{
		return " response:".$this->updateItems();
	}
	
	public function updateItems()
	{
		$Queue = new QuickBooks_WebConnector_Queue($this->dsn);
		
		//$items = db_fetch("","","","","qb_imported = 0","","item",false, false, null);
		//$uoms = db_fetch("","","","","qb_imported = 0","","uom",false, false, null);

		logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- QUEUE ----");
		
		$agents = db_fetch("1","1","","","TRIM(Name) != '' and qb_imported_HD = 0","","agent",false, false, null);
		$partys = db_fetch("1","1","","","TRIM(name) != '' and name is not null and qb_imported_HD = 0","","party p",false, false, null);
                $reqs = db_fetch("1","1","","","q.quote_status_ID= 4 and b.property_ID = r.id and v.qb_imported_HD = 999 and v.quote_ID = q.id and q.booking_ID = b.id and b.party_ID = p.id","b.property_ID, q.id, b.agent_ID, p.name as pName, b.out_Date as TxnDate, b.In_Date, v.id as invoiceId, r.name as propertyName","quote q, booking b, party p, invoice v, property r",false, false, null);

		
		if (sizeOf($agents) > 0 or sizeOf($partys) > 0)
		{
			$Queue->enqueue(QUICKBOOKS_ADD_CUSTOMER, null, 0);
		}
		elseif (sizeOf($reqs) > 0)
		{
			$Queue->enqueue(QUICKBOOKS_ADD_INVOICE, null, 0);
		}
		else 
		{
			$Queue->enqueue(QUICKBOOKS_ADD_RECEIVEPAYMENT, null, 0);
		}
		
		$Server = new QuickBooks_WebConnector_Server($this->dsn, $this->map, $this->errmap, $this->hooks, $this->log_level, $this->soapserver, QUICKBOOKS_WSDL, $this->soap_options, $this->handler_options, $this->driver_options, $this->callback_options);
		$response = $Server->handle(true, true);
		return "";		
	}

}

##ALL CALLBACK FUNCTIONS##

function _quickbooks_add_invoice_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale)
{

                $reqs = db_fetch("1","1","","","q.quote_status_ID= 4 and b.property_ID = r.id and v.qb_imported_HD = 999 and v.quote_ID = q.id and q.booking_ID = b.id and b.party_ID = p.id","b.property_ID, q.id, b.agent_ID, p.name as pName, b.out_Date as TxnDate, b.In_Date, v.id as invoiceId, r.name as propertyName","quote q, booking b, party p, invoice v, property r",false, false, null);

$xml = "";


	if (sizeOf($reqs) > 0)
	{
		foreach ($reqs as $req)
		{
			db_update($req['invoiceId'],'invoice',array('qb_request_HD'),array($requestID));
//			db_update($req['invoiceId'],'invoice',array('qb_imported_HD'),array(1));
			$dueDate = '';			
			
			if ($req['property_ID'] == 1)
			{
				//EWC - 30 days
				$dueDate = date('Y-m-d', strtotime($req['In_Date'] . ' -30 days'));
			}
			else if ($req['property_ID'] == 2)
			{
				//OH - 10 days
				$dueDate = date('Y-m-d', strtotime($req['In_Date'] . ' -10 days'));
			}
			
			$customerName = "";
			//Lets figure if the booking is by agent or not...
			if ($req['agent_ID'] != "")
			{
				//we have to use an agent "customer"
				$agents = db_fetch("","","","","id =".$req['agent_ID'],"","agent", false, false, null);
				$customerName = $agents[0]['Name'];
			}
			else 
			{
				//we use a party "customer"
				$customerName = $req['pName'];
			}

			$rtArray = invoiceDocument(db_fetch("","","","","id=".$req['id'],"","quote",false, false, null), 1, true);
			
			$KESData = $rtArray[0];
			$USDData = $rtArray[1];

		
			//EACH REQ
			$xml .= '<?xml version="1.0" encoding="utf-8"?>
<?qbxml version="7.0"?> 
<QBXML>
	<QBXMLMsgsRq onError="stopOnError">';
					
if (sizeOf($KESData)>0)
{
	$xml .= '
		<InvoiceAddRq>
			<InvoiceAdd> <!-- required -->
				<CustomerRef> <!-- required -->
					<FullName >'.$customerName.' KES</FullName> <!-- optional -->
				</CustomerRef>
				<TxnDate >'.date('Y-m-d', strtotime($req['TxnDate'])).'</TxnDate> <!-- optional -->
				<RefNumber >'.$req['invoiceId'].'</RefNumber> <!-- optional -->
				<DueDate >'.date('Y-m-d',strtotime($dueDate)).'</DueDate> <!-- Before Arrival: 10 days OH, 30 days EWC-->
				<Memo >'.$req['propertyName']." on ".date('d-m-Y',strtotime($req['In_Date'])).'</Memo> <!-- Property, Dates etc -->';
				foreach ($KESData as $KData)
				{
					$xml .='<InvoiceLineAdd defMacro="TxnID:RecvPmtQi'.$KData['id'].date('s').'">
										<ItemRef>
											<FullName>'.$KData['account'].'</FullName>
										</ItemRef>
										<Desc>'.$KData['service'].'</Desc>
										<Quantity>'.$KData['quantity'].'</Quantity>
										<Rate>'.number_format(floatval($KData['rate']), 2, '.', '').'</Rate>
										<Amount>'.number_format(floatval($KData['total']), 2, '.', '').'</Amount>
										<ServiceDate>'.date('Y-m-d',strtotime($KData['date'])).'</ServiceDate>
                                                                                <SalesTaxCodeRef><FullName >'.$KData['taxRef'].'</FullName></SalesTaxCodeRef>
							</InvoiceLineAdd>';
				}
		$xml .= '</InvoiceAdd>';
	$xml .= '</InvoiceAddRq>';
	
}
if (sizeOf($USDData)>0)
{
	$xml .= '
		<InvoiceAddRq>
			<InvoiceAdd> <!-- required -->
				<CustomerRef> <!-- required -->
					<FullName >'.$customerName.' USD</FullName> <!-- optional -->
				</CustomerRef>
				<TxnDate >'.date('Y-m-d', strtotime($req['TxnDate'])).'</TxnDate> <!-- optional -->
				<RefNumber >'.$req['invoiceId'].'</RefNumber> <!-- optional -->
				<DueDate >'.date('Y-m-d',strtotime($dueDate)).'</DueDate> <!-- Before Arrival: 10 days OH, 30 days EWC-->
				<Memo >'.$req['propertyName']." on ".date('d-m-Y',strtotime($req['In_Date'])).'</Memo> <!-- Property, Dates etc -->';
	foreach ($USDData as $KData)
	{
		$xml .='<InvoiceLineAdd defMacro="TxnID:RecvPmtQi'.$KData['id'].date('s').'">
										<ItemRef>
											<FullName>'.$KData['account'].'</FullName>
										</ItemRef>
										<Desc>'.$KData['service'].'</Desc>
										<Quantity>'.$KData['quantity'].'</Quantity>
										<Rate>'.number_format(floatval($KData['rate']), 2, '.', '').'</Rate>
										<Amount>'.number_format(floatval($KData['total']), 2, '.', '').'</Amount>
										<ServiceDate>'.date('Y-m-d',strtotime($KData['date'])).'</ServiceDate>
                                                                                <SalesTaxCodeRef><FullName >'.$KData['taxRef'].'</FullName></SalesTaxCodeRef>
							</InvoiceLineAdd>';
	}
	$xml .= '</InvoiceAdd>';
	$xml .= '</InvoiceAddRq>';

}


		
$xml .= '</QBXMLMsgsRq>
</QBXML>';
		}
	}
	return $xml;
}

function _quickbooks_add_invoice_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents)
{
	if ($err == "")
	{
		$pd = db_fetch("","","","","qb_request_HD = ".$requestID,"","invoice", false, false, null);
		db_update($pd[0]['id'],'invoice',array('qb_imported_HD'),array(1));
	}
}

function _quickbooks_add_payment_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale)
{
	
	logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- PAYMENT REQ");
	
	$reqs = db_fetch("1","1","","","d.amount > 0 and d.quickbooks_accounts_ID = qb.id and d.qb_imported_HD = 999 and i.quote_ID = q.id and d.payment_method_ID = m.id and q.booking_ID = b.id and p.id = b.party_ID and d.quote_ID = q.id","d.*, b.agent_ID, p.name as pName, m.name as mname, i.id as invoiceId, qb.name as qbac","quote q, payment_detail d, booking b, party p, payment_method m, invoice i, quickbooks_accounts qb",false, false, null);
	
	logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- REQ SIZE".sizeOf($reqs) );
	
	if (sizeOf($reqs) > 0)
	{
		
		foreach ($reqs as $req)
		{
			$arAccount = $req['qbac'];
			
			$customerName = "";
			//Lets figure if the booking is by agent or not...
			if ($req['agent_ID'] != "")
			{
				//we have to use an agent "customer"
				$agents = db_fetch("","","","","id =".$req['agent_ID'],"","agent", false, false, null);
				$customerName = $agents[0]['Name'];
			}
			else
			{
				//we use a party "customer"
				$customerName = $req['pName'];
			}
			
				
			if ($req['currency_ID'] == 1)
			{
				$curr = " KES";
			}
			else 
			{
				$curr = " USD";
			}
			$arAccount = $arAccount . $curr;
				
			db_update($req['id'],'payment_detail',array('qb_request_HD'),array($requestID));
			$dueDate = '';
			logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- Sending ".$req['id'] );
		
			$xml = '
<?xml version="1.0" encoding="utf-8"?>
<?qbxml version="7.0"?>
			
<QBXML>
	<QBXMLMsgsRq onError="stopOnError">
		<ReceivePaymentAddRq requestID="'.$requestID.'">
			<ReceivePaymentAdd> <!-- required -->
				<CustomerRef > <!-- required -->
					<FullName >'.$customerName.$curr.'</FullName> <!-- optional -->
				</CustomerRef>
				<TxnDate >'.date('Y-m-d',strtotime($req['payment_Date'])).'</TxnDate> <!-- optional -->
				<RefNumber >'.$req['id'].'</RefNumber> <!-- optional -->
				<TotalAmount >'.$req['Amount'].'</TotalAmount> <!-- optional -->
				<Memo >'.$req['mname'].' ref:'.$req['Reference_Number'].'</Memo> <!-- optional -->
				<DepositToAccountRef> 
					<FullName >'.$arAccount.'</FullName> 
				</DepositToAccountRef>							
				<IsAutoApply >true</IsAutoApply>						
			</ReceivePaymentAdd>
		</ReceivePaymentAddRq>
	</QBXMLMsgsRq>
</QBXML>';

		}
	}

	logger(__FILE__,__LINE__,__CLASS__,__METHOD__, "----- XML".$xml );
	
	return $xml;
}

function _quickbooks_add_payment_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents)
{
	if ($err == "")
	{
		$pd = db_fetch("","","","","qb_request_HD = ".$requestID,"","payment_detail", false, false, null);
		db_update($pd[0]['id'],'payment_detail',array('qb_imported_HD'),array(1));
	}
}

function _quickbooks_add_customer_request($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale)
{
	
	$agents = db_fetch("1","1","","","TRIM(Name) != '' and qb_imported_HD = 0","","agent",false, false, null);
	
	$xml = "";
	
	if (sizeOf($agents) > 0)
	{
		foreach ($agents as $agent)
		{
			db_update($agent['id'],'agent',array('qb_request_HD'),array($requestID));
				
		$xml .= '

<?xml version="1.0" encoding="utf-8"?>
	<?qbxml version="8.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="stopOnError">
				<CustomerAddRq requestID="'.$requestID.'">
					<CustomerAdd>
						<Name >'.$agent['Name'].' KES</Name>
						<CompanyName >'.$agent['Name'].'</CompanyName>
						<Phone >'.$agent['Focal_Point_Telephone'].'</Phone>
						<Email >'.$agent['Focal_Point_Email'].'</Email>
						<Contact >'.$agent['Address'].'</Contact>
						<CurrencyRef>
							<FullName >Kenyan Shilling</FullName>
						</CurrencyRef>
					</CustomerAdd>
				</CustomerAddRq>
				<CustomerAddRq>
					<CustomerAdd>
						<Name >'.$agent['Name'].' USD</Name>
						<CompanyName >'.$agent['Name'].'</CompanyName>
						<Phone >'.$agent['Focal_Point_Telephone'].'</Phone>
						<Email >'.$agent['Focal_Point_Email'].'</Email>
						<Contact >'.$agent['Address'].'</Contact>
						<CurrencyRef>
							<FullName >US Dollar</FullName>
						</CurrencyRef>
					</CustomerAdd>
				</CustomerAddRq>
								
			</QBXMLMsgsRq>
		</QBXML>';
		}
	}
	else
	{
		$partys = db_fetch("1","1","","","TRIM(name) != '' and name is not null and qb_imported_HD = 0","","party",false, false, null);
		
		$xml = "";
		
		if (sizeOf($partys) > 0)
		{
			foreach ($partys as $party)
			{
				db_update($party['id'],'party',array('qb_request_HD'),array($requestID));
				$xml .= '
<?xml version="1.0" encoding="utf-8"?>
	<?qbxml version="8.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="stopOnError">
				<CustomerAddRq requestID="'.$requestID.'">
					<CustomerAdd>
						<Name >'.$party['name'].' KES</Name>
						<Phone >'.$party['telephone'].'</Phone>
						<Email >'.$party['email'].'</Email>
						<Contact >'.$party['address'].'</Contact>
						<CurrencyRef>
							<FullName >Kenyan Shilling</FullName>
						</CurrencyRef>
					</CustomerAdd>
				</CustomerAddRq>
				<CustomerAddRq>
					<CustomerAdd>
						<Name >'.$party['name'].' USD</Name>
						<Phone >'.$party['telephone'].'</Phone>
						<Email >'.$party['email'].'</Email>
						<Contact >'.$party['address'].'</Contact>
						<CurrencyRef>
							<FullName >US Dollar</FullName>
						</CurrencyRef>
					</CustomerAdd>
				</CustomerAddRq>
								
			</QBXMLMsgsRq>
		</QBXML>';
						
				
			}
		}
	}
	
	return $xml;
}

function _quickbooks_add_customer_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents)
{
	if ($err == "")
	{
		$agents = db_fetch("1","1","","","TRIM(Name) != '' and qb_imported_HD = 0","","agent",false, false, null);
		
		if (sizeOf($agents) > 0)
		{
			$pd = db_fetch("","","","","qb_request_HD = ".$requestID,"","agent", false, false, null);
			db_update($pd[0]['id'],'agent',array('qb_imported_HD'),array(1));
		}		
		else
		{
			$pd = db_fetch("","","","","qb_request_HD = ".$requestID,"","party", false, false, null);
			db_update($pd[0]['id'],'party',array('qb_imported_HD'),array(1));
		}
	}
}



?>